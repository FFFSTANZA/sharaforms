<?php

namespace App\Http\Controllers\Content;

use App\Http\Controllers\Controller;
use Illuminate\Support\Facades\Cache;

class PlansController extends Controller
{
    public function index()
    {
        $plans = Cache::remember('plans_catalog', 3600, function () {
            return [
                'tiers' => config('plans.tiers', []),
                'features' => config('plans.features', []),
                'form_features' => config('plans.form_features', []),
                'limits' => config('plans.limits', []),
            ];
        });

        return response()->json($plans);
    }
}
