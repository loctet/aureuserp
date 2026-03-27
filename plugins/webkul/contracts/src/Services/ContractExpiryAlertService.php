<?php

namespace Webkul\Contracts\Services;

use Carbon\Carbon;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\Notification;
use Webkul\Contracts\Models\Contract;
use Webkul\Contracts\Notifications\ContractExpiryAlertNotification;
use Webkul\Security\Models\User;

class ContractExpiryAlertService
{
    /**
    * @var int[]
    */
    protected array $thresholds = [90, 60, 30];

    public function registerModelEvents(): void
    {
        Contract::saved(function (Contract $contract): void {
            $this->sendAlertsForContract($contract);
        });
    }

    public function sendScheduledAlerts(): void
    {
        Contract::query()
            ->whereNotNull('end_date')
            ->where('status', 'active')
            ->chunkById(100, function ($contracts): void {
                foreach ($contracts as $contract) {
                    $this->sendAlertsForContract($contract);
                }
            });
    }

    public function sendAlertsForContract(Contract $contract): void
    {
        if (! $contract->end_date) {
            return;
        }

        $daysRemaining = Carbon::now()->startOfDay()->diffInDays(
            Carbon::parse($contract->end_date)->startOfDay(),
            false
        );

        if (! in_array($daysRemaining, $this->thresholds, true)) {
            return;
        }

        $cacheKey = sprintf('contracts:expiry-alert:%d:%d', $contract->getKey(), $daysRemaining);

        if (Cache::has($cacheKey)) {
            return;
        }

        $users = User::query()
            ->whereIn('id', array_filter([$contract->creator_id, $contract->employee?->user_id]))
            ->get();

        if ($users->isNotEmpty()) {
            Notification::send($users, new ContractExpiryAlertNotification($contract, $daysRemaining));
        }

        Cache::put($cacheKey, true, Carbon::parse($contract->end_date)->endOfDay());
    }
}
