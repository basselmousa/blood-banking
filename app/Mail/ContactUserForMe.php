<?php

namespace App\Mail;

use App\Models\Donor;
use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Mail\Mailable;
use Illuminate\Queue\SerializesModels;

class ContactUserForMe extends Mailable
{
    use Queueable, SerializesModels;

    public $blood;
    public $user;
    public $email;
    public $requesterName;
    public $requesterEmail;
    public $phone;


    /**
     * Create a new message instance.
     *
     * @return void
     */
    public function __construct(Donor $donor)
    {
        //
        $this->blood = $donor->blood_group;
        $this->user = $donor->full_name;
        $this->email = $donor->email;
        $this->phone = $donor->phone_number;
        $this->requesterEmail = auth()->user()->email;
        $this->requesterName = auth()->user()->full_name;
    }

    /**
     * Build the message.
     *
     * @return $this
     */
    public function build()
    {
        return $this->markdown('contact-user-for-me');
    }
}
