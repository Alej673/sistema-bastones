<?php

namespace App\Mail;

use Illuminate\Bus\Queueable;
use Illuminate\Mail\Mailable;
use Illuminate\Mail\Mailables\Content;
use Illuminate\Mail\Mailables\Envelope;
use Illuminate\Queue\SerializesModels;
use Illuminate\Mail\Mailables\Attachment;
use Barryvdh\DomPDF\Facade\Pdf;
use App\Models\Pedido;

class NotaVentaMailable extends Mailable
{
    use Queueable, SerializesModels;

    public $pedido;

    /**
     * Recibimos el pedido completo desde el controlador
     */
    public function __construct(Pedido $pedido)
    {
        $this->pedido = $pedido;
    }

    /**
     * Configuramos el Asunto del correo
     */
    public function envelope(): Envelope
    {
        return new Envelope(
            subject: 'Nota de Venta Oficial - Pedido #' . $this->pedido->id,
        );
    }

    /**
     * Configuramos la vista HTML que será el "cuerpo" del correo
     */
    public function content(): Content
    {
        return new Content(
            view: 'emails.nota_venta',
        );
    }

    /**
     * Aquí generamos el PDF On-the-Fly y lo adjuntamos
     */
    public function attachments(): array
    {
        // 1. Renderizamos el PDF en la memoria usando tu vista comercial
        $pdf = Pdf::loadView('reportes.nota', ['pedido' => $this->pedido]);

        // 2. Lo adjuntamos directamente como un archivo al correo
        return [
            Attachment::fromData(fn () => $pdf->output(), 'Nota_Venta_' . $this->pedido->id . '.pdf')
                    ->withMime('application/pdf'),
        ];
    }
}
