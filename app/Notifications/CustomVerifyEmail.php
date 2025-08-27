<?php

namespace App\Notifications;

use Illuminate\Support\Carbon;
use Illuminate\Support\Facades\Config;
use Illuminate\Support\Facades\URL;
use Illuminate\Support\Facades\Cache;
use Illuminate\Auth\Notifications\VerifyEmail as VerifyEmailBase;
use Illuminate\Notifications\Messages\MailMessage;

class CustomVerifyEmail extends VerifyEmailBase
{
    /**
     * Get the verification URL for the given notifiable.
     *
     * @param  mixed  $notifiable
     * @return string
     */
    protected function verificationUrl($notifiable)
    {
        // Metode ini menghasilkan URL terenkripsi untuk verifikasi email.
        // Sama seperti bawaan Laravel, namun kita bisa kustomisasi jika perlu.
        return URL::temporarySignedRoute(
            'verification.verify',
            Carbon::now()->addMinutes(Config::get('auth.verification.expire', 60)),
            [
                'id' => $notifiable->getKey(),
                'hash' => sha1($notifiable->getEmailForVerification()),
            ]
        );
    }

    /**
     * Get the mail representation of the notification.
     *
     * @param  mixed  $notifiable
     * @return \Illuminate\Notifications\Messages\MailMessage
     */
    public function toMail($notifiable)
    {
        $verificationUrl = $this->verificationUrl($notifiable);
        $appName = config('app.name');
        $expireMinutes = config('auth.verification.expire', 60);

        // Ambil template dari cache/database, dengan fallback ke teks default.
        $settings = Cache::get('app_settings', []);

        // Ambil setiap bagian teks dari settings, atau gunakan default jika tidak ada.
        $subject = $settings['email_verify_subject'] ?? 'Verifikasi Alamat Email Anda untuk {appName}';
        $greeting = $settings['email_verify_greeting'] ?? 'Halo, {userName}!';
        $line1 = $settings['email_verify_line1'] ?? 'Terima kasih telah mendaftar di {appName}. Mohon klik tombol di bawah ini untuk memverifikasi alamat email Anda.';
        $actionText = $settings['email_verify_action'] ?? 'Verifikasi Email';
        $line2 = $settings['email_verify_line2'] ?? 'Tombol verifikasi ini akan kedaluwarsa dalam {expireMinutes} menit.';
        $line3 = $settings['email_verify_line3'] ?? 'Jika Anda tidak membuat akun ini, Anda tidak perlu melakukan tindakan apa pun.';
        $salutation = $settings['email_verify_salutation'] ?? 'Hormat kami, Tim {appName}';

        // Daftar placeholder yang bisa digunakan di admin dan nilainya.
        $placeholders = [
            '{appName}' => $appName,
            '{userName}' => $notifiable->name,
            '{expireMinutes}' => $expireMinutes,
        ];

        // Ganti semua placeholder dengan nilai sebenarnya.
        $subject = str_replace(array_keys($placeholders), array_values($placeholders), $subject);
        $greeting = str_replace(array_keys($placeholders), array_values($placeholders), $greeting);
        $line1 = str_replace(array_keys($placeholders), array_values($placeholders), $line1);
        $actionText = str_replace(array_keys($placeholders), array_values($placeholders), $actionText);
        $line2 = str_replace(array_keys($placeholders), array_values($placeholders), $line2);
        $line3 = str_replace(array_keys($placeholders), array_values($placeholders), $line3);
        $salutation = str_replace(array_keys($placeholders), array_values($placeholders), $salutation);

        // Bangun email menggunakan teks yang sudah dinamis.
        return (new MailMessage)
            ->subject($subject)
            ->greeting($greeting)
            ->line($line1)
            ->action($actionText, $verificationUrl)
            ->line($line2)
            ->line($line3)
            ->salutation($salutation);
    }
}
