<?php

namespace App\Ai\Tools;

use App\Enums\ChartSubject;
use App\Models\Talk;
use App\Models\Workshop;
use App\Services\A2uiSurfaceBuilder;
use Illuminate\Contracts\JsonSchema\JsonSchema;
use Illuminate\Support\Collection;
use Illuminate\Validation\Rule;
use Laravel\Ai\Contracts\Tool;
use Laravel\Ai\Tools\Request;
use Stringable;

class ShowChart implements Tool
{
    public const NAME = 'show_chart';

    public function __construct(private A2uiSurfaceBuilder $surfaces) {}

    public function name(): string
    {
        return self::NAME;
    }

    public function description(): Stringable|string
    {
        return "Mostra all'utente un grafico a barre: i partecipanti di ogni workshop (workshop_bookings) oppure quante persone hanno messo in agenda ogni talk (talk_agendas). Usalo quando l'utente chiede un grafico o delle statistiche.";
    }

    public function handle(Request $request): Stringable|string
    {
        $subject = ChartSubject::from($request->validate([
            'subject' => ['required', Rule::enum(ChartSubject::class)],
        ])['subject']);

        return json_encode(['a2ui' => $this->surfaces->barChart($subject->title(), $this->bars($subject))], JSON_UNESCAPED_UNICODE | JSON_UNESCAPED_SLASHES);
    }

    public function schema(JsonSchema $schema): array
    {
        return [
            'subject' => $schema->string()->enum(array_column(ChartSubject::cases(), 'value'))->description('I dati da mostrare nel grafico.')->required(),
        ];
    }

    /**
     * @return Collection<int, array{label: string, value: int, max?: int}>
     */
    private function bars(ChartSubject $subject): Collection
    {
        return match ($subject) {
            ChartSubject::WorkshopBookings => Workshop::withCount('bookings')->orderBy('starts_at')->get()
                ->map(fn (Workshop $workshop) => ['label' => $workshop->title, 'value' => $workshop->bookings_count, 'max' => $workshop->capacity]),
            ChartSubject::TalkAgendas => Talk::withCount('agendaItems')->orderByDesc('agenda_items_count')->orderBy('starts_at')->get()
                ->map(fn (Talk $talk) => ['label' => $talk->title, 'value' => $talk->agenda_items_count]),
        };
    }
}
