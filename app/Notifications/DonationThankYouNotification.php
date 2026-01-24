<?php

namespace App\Notifications;

use App\Models\Donor;
use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Notifications\Messages\MailMessage;
use Illuminate\Notifications\Notification;

class DonationThankYouNotification extends Notification implements ShouldQueue
{
    use Queueable;

    public $donor;
    public $donationVolume;
    public $nextEligibleDate;

    /**
     * Create a new notification instance.
     */
    public function __construct(Donor $donor, int $donationVolume, ?\DateTime $nextEligibleDate)
    {
        $this->donor = $donor;
        $this->donationVolume = $donationVolume;
        $this->nextEligibleDate = $nextEligibleDate;
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
        return (new MailMessage)
            ->subject('Thank You For Your Blood Donation!')
            ->greeting("Hello {$this->donor->full_name},")
            ->line("Thank you for donating blood!")
            ->line("**Donation Details:**")
            ->line("- Volume: {$this->donationVolume} ml")
            ->line("- Date: " . now()->format('F d, Y'))
            ->line("You can donate again on: **{$this->nextEligibleDate->format('F d, Y')}**")
            ->line("Your generous contribution helps save lives!")
            ->action('View Your Profile', route('home'))
            ->line('Best regards,')
            ->line('Blood Banking System');
    }
}
