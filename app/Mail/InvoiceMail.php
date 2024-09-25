<?php

namespace App\Mail;

use Illuminate\Mail\Mailable;

class InvoiceMail extends Mailable
{
    public $reglement;
    protected $pdf;

    public function __construct($reglement, $pdf)
    {
        $this->reglement = $reglement;
        $this->pdf = $pdf;
    }

    public function build()
    {
        return $this->view('emails.invoice')
                    ->subject('Votre facture')
                    ->attachData($this->pdf, 'facture.pdf', [
                        'mime' => 'application/pdf',
                    ]);
    }
}
