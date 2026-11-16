<?php

namespace App\Enums;

/**
 * The read-only statistics of the conference: the admin agent picks one, then decides how to show it.
 */
enum StatisticsReport: string
{
    case Overview = 'overview';
    case WorkshopBookings = 'workshop_bookings';
    case TalkAgendas = 'talk_agendas';
    case Tracks = 'tracks';
    case CompanyRoles = 'company_roles';
    case Companies = 'companies';
    case DailyActivity = 'daily_activity';

    public function description(): string
    {
        return match ($this) {
            self::Overview => 'totali: partecipanti, talk, workshop, agende, prenotazioni e posti liberi',
            self::WorkshopBookings => 'per ogni workshop: capienza, prenotati e posti liberi',
            self::TalkAgendas => 'per ogni talk: track, sala, orario e quante persone lo hanno in agenda',
            self::Tracks => 'per ogni track: numero di talk e aggiunte alle agende',
            self::CompanyRoles => 'prenotazioni ai workshop per ruolo in azienda',
            self::Companies => 'le 10 aziende con più prenotazioni ai workshop',
            self::DailyActivity => 'per ogni giorno: talk aggiunti alle agende e prenotazioni ai workshop',
        };
    }
}
