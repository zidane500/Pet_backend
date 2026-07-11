<?php

namespace App\Notifications;

use Illuminate\Bus\Queueable;
use Illuminate\Notifications\Messages\MailMessage;
use Illuminate\Notifications\Notification;

class ResetPasswordNotification extends Notification
{
    use Queueable;

    public function __construct(protected string $token)
    {
    }

    public function via(object $notifiable): array
    {
        return ['mail'];
    }

    /**
     * ← C'est LA raison d'être de cette classe : la notification
     * intégrée de Laravel (Illuminate\Auth\Notifications\ResetPassword)
     * essaie de générer un lien vers une route nommée "password.reset"
     * qui n'existe pas dans une app API-only comme celle-ci — ça
     * planterait. Ici, on construit nous-mêmes le lien vers la page
     * /reset-password du FRONTEND (React), avec le token et l'email en
     * paramètres d'URL.
     */
    public function toMail(object $notifiable): MailMessage
    {
        $url = sprintf(
            '%s/reset-password?token=%s&email=%s',
            rtrim(config('app.frontend_url'), '/'),
            $this->token,
            urlencode($notifiable->getEmailForPasswordReset()),
        );

        return (new MailMessage)
            ->subject('Réinitialisation de votre mot de passe — Animali.tn')
            ->greeting('Bonjour ' . $notifiable->name . ',')
            ->line('Vous recevez cet email car une demande de réinitialisation de mot de passe a été faite pour votre compte.')
            ->action('Réinitialiser mon mot de passe', $url)
            ->line('Ce lien expirera dans 60 minutes.')
            ->line("Si vous n'êtes pas à l'origine de cette demande, vous pouvez ignorer cet email en toute sécurité.");
    }
}