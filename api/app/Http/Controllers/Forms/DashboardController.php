<?php

namespace App\Http\Controllers\Forms;

use App\Http\Controllers\Controller;
use App\Models\Forms\Form;
use App\Models\Forms\FormSubmission;
use App\Models\Workspace;
use Carbon\Carbon;
use Carbon\CarbonPeriod;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;

class DashboardController extends Controller
{
    public function __construct()
    {
        $this->middleware('auth');
    }

    public function getWorkspaceDashboard(Request $request, Workspace $workspace)
    {
        $this->authorize('view', $workspace);

        $days = (int) $request->get('days', 7);
        if (!in_array($days, [7, 30, 90])) {
            $days = 7;
        }

        $today = Carbon::today();
        $currentPeriodStart = $today->copy()->subDays($days - 1)->startOfDay();
        $previousPeriodStart = $currentPeriodStart->copy()->subDays($days);
        $previousPeriodEnd = $currentPeriodStart->copy()->subDay()->endOfDay();

        // Get all forms for this workspace (single query)
        $allForms = $workspace->forms()->get();
        $formIds = $allForms->pluck('id');

        if ($formIds->isEmpty()) {
            return $this->buildEmptyResponse($days);
        }

        // Current period: daily views from form_views (individual records)
        $currentViewsByDay = $this->getDailyViewCounts($formIds, $currentPeriodStart, $today);
        // Previous period: daily views
        $previousViewsByDay = $this->getDailyViewCounts($formIds, $previousPeriodStart, $previousPeriodEnd);

        // Current period: daily submissions (completed only)
        $currentSubmissionsByDay = $this->getDailySubmissionCounts($formIds, $currentPeriodStart, $today);
        // Previous period: daily submissions
        $previousSubmissionsByDay = $this->getDailySubmissionCounts($formIds, $previousPeriodStart, $previousPeriodEnd);

        // Aggregate totals
        $currentTotalViews = array_sum($currentViewsByDay);
        $previousTotalViews = array_sum($previousViewsByDay);
        $currentTotalSubmissions = array_sum($currentSubmissionsByDay);
        $previousTotalSubmissions = array_sum($previousSubmissionsByDay);

        // Trend percentages
        $viewsTrend = $this->calculateTrend($currentTotalViews, $previousTotalViews);
        $submissionsTrend = $this->calculateTrend($currentTotalSubmissions, $previousTotalSubmissions);

        // Conversion rates
        $currentConversionRate = $currentTotalViews > 0
            ? round(($currentTotalSubmissions / $currentTotalViews) * 100, 1)
            : 0;
        $previousConversionRate = $previousTotalViews > 0
            ? round(($previousTotalSubmissions / $previousTotalViews) * 100, 1)
            : 0;
        $conversionTrend = $this->calculateTrend($currentConversionRate, $previousConversionRate);

        // Build daily chart data (current period)
        $chartData = [];
        foreach (CarbonPeriod::create($currentPeriodStart, $today) as $date) {
            $dateKey = $date->toDateString();
            $chartData[] = [
                'date' => $date->format('Y-m-d'),
                'label' => $date->format('D'),
                'views' => $currentViewsByDay[$dateKey] ?? 0,
                'submissions' => $currentSubmissionsByDay[$dateKey] ?? 0,
            ];
        }

        // Live forms count (reuse $allForms from above)
        $liveFormsCount = $allForms->filter(function ($form) {
            return $form->visibility === 'public' && !$form->is_closed;
        })->count();

        // Top forms by submissions in current period
        $topForms = $this->getTopForms($workspace, $formIds, $currentPeriodStart, $today);

        return [
            'period' => [
                'days' => $days,
                'current_start' => $currentPeriodStart->toDateString(),
                'current_end' => $today->toDateString(),
                'previous_start' => $previousPeriodStart->toDateString(),
                'previous_end' => $previousPeriodEnd->toDateString(),
            ],
            'views' => [
                'current' => $currentTotalViews,
                'previous' => $previousTotalViews,
                'trend' => $viewsTrend,
            ],
            'submissions' => [
                'current' => $currentTotalSubmissions,
                'previous' => $previousTotalSubmissions,
                'trend' => $submissionsTrend,
            ],
            'conversion_rate' => [
                'current' => $currentConversionRate,
                'previous' => $previousConversionRate,
                'trend' => $conversionTrend,
            ],
            'live_forms' => [
                'count' => $liveFormsCount,
                'total' => $formIds->count(),
            ],
            'chart' => $chartData,
            'top_forms' => $topForms,
        ];
    }

    private function getDailyViewCounts($formIds, Carbon $from, Carbon $to): array
    {
        $isMySQL = config('database.default') === 'mysql';
        $viewsExpression = $isMySQL
            ? 'SUM(CAST(JSON_EXTRACT(data, \'$.views\') AS SIGNED))'
            : "SUM(CAST(data->>'views' AS INTEGER))";

        // Count from form_views table (individual view records)
        $viewCounts = DB::table('form_views')
            ->whereIn('form_id', $formIds)
            ->whereBetween('created_at', [$from->startOfDay(), $to->endOfDay()])
            ->selectRaw('DATE(created_at) as date, COUNT(*) as count')
            ->groupBy('date')
            ->pluck('count', 'date')
            ->toArray();

        // Also add from form_statistics table (pre-aggregated daily views)
        $statsCounts = DB::table('form_statistics')
            ->whereIn('form_id', $formIds)
            ->whereBetween('date', [$from->toDateString(), $to->toDateString()])
            ->selectRaw("date, {$viewsExpression} as count")
            ->groupBy('date')
            ->pluck('count', 'date')
            ->toArray();

        // Merge both sources
        $merged = [];
        $allDates = array_unique(array_merge(array_keys($viewCounts), array_keys($statsCounts)));
        foreach ($allDates as $date) {
            $merged[$date] = ($viewCounts[$date] ?? 0) + ($statsCounts[$date] ?? 0);
        }

        return $merged;
    }

    private function getDailySubmissionCounts($formIds, Carbon $from, Carbon $to): array
    {
        return DB::table('form_submissions')
            ->whereIn('form_id', $formIds)
            ->where('status', FormSubmission::STATUS_COMPLETED)
            ->whereBetween('created_at', [$from->startOfDay(), $to->endOfDay()])
            ->selectRaw('DATE(created_at) as date, COUNT(*) as count')
            ->groupBy('date')
            ->pluck('count', 'date')
            ->toArray();
    }

    private function calculateTrend(float $current, float $previous): array
    {
        if ($previous == 0) {
            return [
                'percentage' => $current > 0 ? 100 : 0,
                'direction' => $current > 0 ? 'up' : 'neutral',
            ];
        }

        $change = (($current - $previous) / $previous) * 100;
        $percentage = round(abs($change), 1);

        return [
            'percentage' => $percentage,
            'direction' => $change > 0 ? 'up' : ($change < 0 ? 'down' : 'neutral'),
        ];
    }

    private function getTopForms(Workspace $workspace, $formIds, Carbon $from, Carbon $to): array
    {
        $topFormIds = DB::table('form_submissions')
            ->whereIn('form_id', $formIds)
            ->where('status', FormSubmission::STATUS_COMPLETED)
            ->whereBetween('created_at', [$from->startOfDay(), $to->endOfDay()])
            ->selectRaw('form_id, COUNT(*) as submission_count')
            ->groupBy('form_id')
            ->orderByDesc('submission_count')
            ->limit(4)
            ->pluck('submission_count', 'form_id')
            ->toArray();

        if (empty($topFormIds)) {
            return [];
        }

        $forms = Form::whereIn('id', array_keys($topFormIds))->get();

        return $forms->map(function ($form) use ($topFormIds) {
            return [
                'id' => $form->id,
                'title' => $form->title,
                'slug' => $form->slug,
                'submissions_count' => $topFormIds[$form->id] ?? 0,
                'views_count' => $form->views_count,
                'visibility' => $form->visibility,
            ];
        })->toArray();
    }

    private function buildEmptyResponse(int $days): array
    {
        return [
            'period' => [
                'days' => $days,
                'current_start' => Carbon::today()->subDays($days - 1)->toDateString(),
                'current_end' => Carbon::today()->toDateString(),
                'previous_start' => Carbon::today()->subDays($days * 2 - 1)->toDateString(),
                'previous_end' => Carbon::today()->subDays($days)->toDateString(),
            ],
            'views' => ['current' => 0, 'previous' => 0, 'trend' => ['percentage' => 0, 'direction' => 'neutral']],
            'submissions' => ['current' => 0, 'previous' => 0, 'trend' => ['percentage' => 0, 'direction' => 'neutral']],
            'conversion_rate' => ['current' => 0, 'previous' => 0, 'trend' => ['percentage' => 0, 'direction' => 'neutral']],
            'live_forms' => ['count' => 0, 'total' => 0],
            'chart' => [],
            'top_forms' => [],
        ];
    }
}
