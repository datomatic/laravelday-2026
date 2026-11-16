<?php

namespace App\Services;

use App\Enums\CompanyRole;
use App\Enums\StatisticsReport;
use App\Enums\Track;
use App\Models\AgendaItem;
use App\Models\Talk;
use App\Models\User;
use App\Models\Workshop;
use App\Models\WorkshopBooking;

/**
 * Read-only aggregates for the agents: plain arrays, small enough to fit in the model context.
 */
class StatisticsService
{
    /**
     * @return array<string, mixed>|list<array<string, mixed>>
     */
    public function report(StatisticsReport $report): array
    {
        return match ($report) {
            StatisticsReport::Overview => $this->overview(),
            StatisticsReport::WorkshopBookings => $this->workshopBookings(),
            StatisticsReport::TalkAgendas => $this->talkAgendas(),
            StatisticsReport::Tracks => $this->tracks(),
            StatisticsReport::CompanyRoles => $this->companyRoles(),
            StatisticsReport::Companies => $this->companies(),
            StatisticsReport::DailyActivity => $this->dailyActivity(),
        };
    }

    /**
     * @return array<string, int>
     */
    private function overview(): array
    {
        $seats = (int) Workshop::sum('capacity');
        $bookings = WorkshopBooking::count();

        return [
            'attendees' => User::where('is_admin', false)->count(),
            'talks' => Talk::count(),
            'workshops' => Workshop::count(),
            'agenda_items' => AgendaItem::count(),
            'attendees_with_agenda' => AgendaItem::distinct()->count('user_id'),
            'workshop_bookings' => $bookings,
            'workshop_seats' => $seats,
            'workshop_seats_left' => $seats - $bookings,
        ];
    }

    /**
     * @return list<array<string, mixed>>
     */
    private function workshopBookings(): array
    {
        return Workshop::with('speaker')->withCount('bookings')->orderBy('starts_at')->get()
            ->map(fn (Workshop $workshop) => [
                'title' => $workshop->title,
                'speaker' => $workshop->speaker->name,
                'starts_at' => $workshop->starts_at->format('H:i'),
                'capacity' => $workshop->capacity,
                'booked' => $workshop->bookings_count,
                'seats_left' => $workshop->capacity - $workshop->bookings_count,
            ])
            ->all();
    }

    /**
     * @return list<array<string, mixed>>
     */
    private function talkAgendas(): array
    {
        return Talk::with('speaker')->withCount('agendaItems')->orderByDesc('agenda_items_count')->orderBy('starts_at')->get()
            ->map(fn (Talk $talk) => [
                'title' => $talk->title,
                'speaker' => $talk->speaker->name,
                'track' => $talk->track->label(),
                'room' => $talk->room,
                'starts_at' => $talk->starts_at->format('H:i'),
                'in_agendas' => $talk->agenda_items_count,
            ])
            ->all();
    }

    /**
     * @return list<array{track: string, talks: int, agenda_items: int}>
     */
    private function tracks(): array
    {
        $talks = Talk::withCount('agendaItems')->get()->groupBy(fn (Talk $talk) => $talk->track->value);

        return collect(Track::cases())
            ->map(fn (Track $track) => [
                'track' => $track->label(),
                'talks' => $talks->get($track->value)?->count() ?? 0,
                'agenda_items' => (int) $talks->get($track->value)?->sum('agenda_items_count'),
            ])
            ->all();
    }

    /**
     * @return list<array{role: string, bookings: int}>
     */
    private function companyRoles(): array
    {
        $counts = WorkshopBooking::selectRaw('company_role, count(*) as bookings')->groupBy('company_role')->pluck('bookings', 'company_role');

        return collect(CompanyRole::cases())
            ->map(fn (CompanyRole $role) => ['role' => $role->label(), 'bookings' => (int) ($counts[$role->value] ?? 0)])
            ->sortByDesc('bookings')
            ->values()
            ->all();
    }

    /**
     * @return list<array{company: string, bookings: int}>
     */
    private function companies(): array
    {
        return WorkshopBooking::selectRaw('company_name, count(*) as bookings')
            ->groupBy('company_name')
            ->orderByDesc('bookings')
            ->orderBy('company_name')
            ->limit(10)
            ->get()
            ->map(fn (WorkshopBooking $row) => ['company' => $row->company_name, 'bookings' => (int) $row->bookings])
            ->all();
    }

    /**
     * @return list<array{date: string, agenda_items: int, workshop_bookings: int}>
     */
    private function dailyActivity(): array
    {
        $agenda = AgendaItem::selectRaw('date(created_at) as day, count(*) as total')->groupBy('day')->pluck('total', 'day');
        $bookings = WorkshopBooking::selectRaw('date(created_at) as day, count(*) as total')->groupBy('day')->pluck('total', 'day');

        return $agenda->keys()->merge($bookings->keys())->unique()->sort()->values()
            ->map(fn (string $day) => [
                'date' => $day,
                'agenda_items' => (int) ($agenda[$day] ?? 0),
                'workshop_bookings' => (int) ($bookings[$day] ?? 0),
            ])
            ->all();
    }
}
