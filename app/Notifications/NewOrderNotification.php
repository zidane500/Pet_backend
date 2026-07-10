<?php

namespace App\Notifications;

use App\Models\Order;
use Illuminate\Bus\Queueable;
use Illuminate\Notifications\Notification;

class NewOrderNotification extends Notification
{
    use Queueable;

    public function __construct(protected Order $order)
    {
    }

    /**
     * ← Pour l'instant uniquement 'database' : la notification apparaît
     * dans la clochette de notifications de l'admin (déjà consultée
     * toutes les 30s via le polling existant). Le temps réel
     * (broadcast WebSocket) n'est pas câblé côté backend actuellement
     * (aucun événement ShouldBroadcast n'existe encore dans le projet),
     * donc on reste sur du "database" simple et fiable pour l'instant.
     */
    public function via(object $notifiable): array
    {
        return ['database'];
    }

    public function toDatabase(object $notifiable): array
    {
        return [
            'title' => 'Nouvelle commande',
            'body' => sprintf(
                'Commande #%d de %s — %s DT',
                $this->order->id,
                $this->order->shipping_name,
                number_format((float) $this->order->total_amount, 2),
            ),
            'category' => 'systeme',
            'action_type' => 'none',
            'action_id' => $this->order->id,
            'icon' => '🛒',
        ];
    }
}