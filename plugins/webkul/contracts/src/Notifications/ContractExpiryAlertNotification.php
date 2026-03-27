<?php

namespace Webkul\Contracts\Notifications;

use Illuminate\Bus\Queueable;
use Illuminate\Notifications\Messages\MailMessage;
use Illuminate\Notifications\Notification;
use Webkul\Contracts\Models\Contract;

class ContractExpiryAlertNotification extends Notification
{
    use Queueable;

    public function __construct(
        protected Contract $contract,
        protected int $daysRemaining
    ) {}

    public function via(object $notifiable): array
    {
        return ['database'];
    }

    public function toArray(object $notifiable): array
    {
        return [
            'title'          => 'Contract expiry reminder',
            'contract_id'    => $this->contract->getKey(),
            'employee_id'    => $this->contract->employee_id,
            'days_remaining' => $this->daysRemaining,
            'end_date'       => optional($this->contract->end_date)?->toDateString(),
            'message'        => sprintf(
                'Contract %s expires in %d days.',
                $this->contract->reference ?: '#'.$this->contract->getKey(),
                $this->daysRemaining
            ),
        ];
    }

    public function toMail(object $notifiable): MailMessage
    {
        return (new MailMessage())
            ->subject('Contract expiry reminder')
            ->line($this->toArray($notifiable)['message']);
    }
}
