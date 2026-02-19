<?php

namespace App\Mail;

use Illuminate\Bus\Queueable;
use Illuminate\Mail\Mailable;
use Illuminate\Queue\SerializesModels;

class ReservationCreatedMail extends Mailable
{
    use Queueable, SerializesModels;

    public $user;
    public $date;
    public $reservations;

    public function __construct($user, $date, $reservations)
    {
        $this->user = $user;
        $this->date = $date;
        $this->reservations = $reservations;
    }

    public function build()
    {
        return $this->subject('Confirmación de reserva - Centre Villar')
            ->from('no-reply@centrevillar.com', 'Centre Villar')
            ->view('emails.reservation_created')
            ->with([
                'user' => $this->user,
                'date' => $this->date,
                'reservations' => $this->reservations
            ]);
    }
}
