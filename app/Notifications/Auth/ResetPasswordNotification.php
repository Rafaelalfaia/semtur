<?php

namespace App\Notifications\Auth;

use Illuminate\Auth\Notifications\ResetPassword as BaseResetPassword;
use Illuminate\Bus\Queueable;
use Illuminate\Notifications\Messages\MailMessage;

class ResetPasswordNotification extends BaseResetPassword
{
    use Queueable;

    public function toMail($notifiable): MailMessage
    {
        $resetUrl = $this->resetUrl($notifiable);
        $expire = (int) config('auth.passwords.'.config('auth.defaults.passwords').'.expire', 60);
        $appName = (string) config('app.name', 'Semtur');

        return (new MailMessage())
            ->subject('Redefinição de senha - '.$appName)
            ->greeting('Olá!')
            ->line('Recebemos uma solicitação para redefinir a senha da sua conta.')
            ->action('Redefinir senha', $resetUrl)
            ->line('Este link temporário expira em '.$expire.' minuto(s).')
            ->line('Se você não solicitou a redefinição, pode ignorar este e-mail com segurança.');
    }
}
