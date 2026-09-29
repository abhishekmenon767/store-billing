<?php

namespace App\Mail;

use App\Actions\GenerateOrderBillAction;
use App\Models\Order;
use Illuminate\Bus\Queueable;
use Illuminate\Mail\Attachment;
use Illuminate\Mail\Mailable;
use Illuminate\Mail\Mailables\Content;
use Illuminate\Mail\Mailables\Envelope;

class OrderConfirmationMail extends Mailable
{
    use Queueable;

    public function __construct(public Order $order) {}

    public function envelope(): Envelope
    {
        return new Envelope(subject: "Your bill {$this->order->order_number}");
    }

    public function content(): Content
    {
        return new Content(markdown: 'mail.orders.confirmation');
    }

    public function attachments(): array
    {
        return [
            Attachment::fromData(
                fn () => app(GenerateOrderBillAction::class)->execute($this->order),
                "{$this->order->order_number}.pdf",
            )->withMime('application/pdf'),
        ];
    }
}
