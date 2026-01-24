<?php

namespace App\Notifications;

use App\Models\Donor;
use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Notifications\Messages\MailMessage;
use Illuminate\Notifications\Notification;

class DonorDeferralNotification extends Notification implements ShouldQueue
{
    use Queueable;

    public $donor;
    public $reason;
    public $deferralType;
    public $eligibleAfter;

    /**
     * Create a new notification instance.
     */
    public function __construct(Donor $donor, string $reason, string $deferralType, ?\DateTime $eligibleAfter)
    {
        $this->donor = $donor;
        $this->reason = $reason;
        $this->deferralType = $deferralType;
        $this->eligibleAfter = $eligibleAfter;
    }

    /**
     * Get the notification's delivery channels.
     */
    public function via(object $notifiable): array
    {
        return ['mail'];
    }

    /**
     * Get the mail representation of the notification.
     */
    public function toMail(object $notifiable): MailMessage
    {
        $message = (new MailMessage)
            ->subject('Blood Donation Deferral Notice')
            ->greeting("Hello {$this->donor->full_name},")
            ->line("Your blood donation has been deferred for the following reason:")
            ->line("**Reason:** {$this->reason}")
            ->line("**Type:** " . ucfirst($this->deferralType) . " Deferral");

        if ($this->eligibleAfter) {
            $message->line("You will be eligible to donate again on: **{$this->eligibleAfter->format('F d, Y')}**");
        } else {
            $message->warning("This is a permanent deferral. You will not be eligible to donate.");
        }

        return $message
            ->line('If you have any questions, please contact us.')
            ->action('View Your Profile', route('home'))
            ->line('Thank you for your support!');
    }
}
