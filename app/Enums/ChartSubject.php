<?php

namespace App\Enums;

enum ChartSubject: string
{
    case WorkshopBookings = 'workshop_bookings';
    case TalkAgendas = 'talk_agendas';

    public function title(): string
    {
        return match ($this) {
            self::WorkshopBookings => 'Partecipanti per workshop',
            self::TalkAgendas => 'Talk messi in agenda',
        };
    }
}
