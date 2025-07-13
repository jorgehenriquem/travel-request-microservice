<?php

namespace App\Notifications;

use App\Models\TravelRequest;
use Illuminate\Bus\Queueable;
use Illuminate\Notifications\Notification;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Notifications\Messages\MailMessage;

class TravelRequestStatusUpdated extends Notification implements ShouldQueue
{
    use Queueable;

    private TravelRequest $travelRequest;

    public function __construct(TravelRequest $travelRequest)
    {
        $this->travelRequest = $travelRequest;
    }

    public function via($notifiable): array
    {
        return ['mail', 'database'];
    }

    public function toMail($notifiable): MailMessage
    {
        $status = $this->travelRequest->status === 'approved' ? 'aprovado' : 'cancelado';
        
        return (new MailMessage)
            ->subject("Pedido de Viagem {$status}")
            ->greeting("Olá {$notifiable->name}!")
            ->line("Seu pedido de viagem para {$this->travelRequest->destination} foi {$status}.")
            ->when($this->travelRequest->status === 'cancelled', function ($message) {
                return $message->line("Motivo: {$this->travelRequest->cancellation_reason}");
            })
            ->line('Obrigado por usar nosso sistema!'); 
    }

    public function toArray($notifiable): array
    {
        return [
            'travel_request_id' => $this->travelRequest->id,
            'status' => $this->travelRequest->status,
            'destination' => $this->travelRequest->destination,
            'message' => "Pedido de viagem {$this->travelRequest->status}",
        ];
    }
}
