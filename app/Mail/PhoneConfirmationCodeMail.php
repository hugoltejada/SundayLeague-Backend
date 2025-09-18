<?php

namespace App\Mail;

use App\Models\Phone;
use App\Models\Config;
use Illuminate\Bus\Queueable;
use Illuminate\Mail\Mailable;
use Illuminate\Queue\SerializesModels;

class PhoneConfirmationCodeMail extends Mailable
{
    use Queueable, SerializesModels;

    public function __construct(public Phone $phone) {}

    public function build()
    {
        // valores desde tabla config con fallback a config/app.php
        $appName   = Config::where('key', 'app_name')->value('value') ?? config('app.name');
        $fromEmail = Config::where('key', 'mail_from_address')->value('value') ?? config('mail.from.address');
        $fromName  = Config::where('key', 'mail_from_name')->value('value') ?? $appName;

        return $this->subject('Your verification code | ' . $appName)
            ->markdown('emails.phone-confirmation', ['phone' => $this->phone])
            ->from($fromEmail, $fromName);
    }
}
