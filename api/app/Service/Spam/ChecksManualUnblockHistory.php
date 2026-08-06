<?php

namespace App\Service\Spam;

use App\Models\User;
use Carbon\Carbon;

trait ChecksManualUnblockHistory
{
    private function wasRecentlyManuallyUnblocked(User $user, int $days = 7): bool
    {
        $history = $user->meta['blocking_history'] ?? [];
        if (empty($history)) {
            return false;
        }

        $recentDate = Carbon::now()->subDays($days);

        foreach (array_reverse($history) as $block) {
            if (
                !is_null($block['unblocked_by']) &&
                !is_null($block['unblocked_at']) &&
                Carbon::parse($block['unblocked_at'])->isAfter($recentDate)
            ) {
                return true;
            }
        }

        return false;
    }
}
