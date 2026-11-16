<?php

namespace App\Neuron\Tools;

use App\Enums\ChartSubject;
use App\Enums\StatisticsReport;
use App\Neuron\Tools\Concerns\RendersA2ui;
use App\Services\A2uiSurfaceBuilder;
use App\Services\StatisticsService;
use NeuronAI\Tools\PropertyType;
use NeuronAI\Tools\Tool;
use NeuronAI\Tools\ToolProperty;

/**
 * A chart chosen by the code: the model only picks the subject, the layout is fixed.
 * The admin agent reads the same statistics and composes its own charts with render_surface.
 */
class ShowChart extends Tool
{
    use RendersA2ui;

    public const NAME = 'show_chart';

    protected string $name = self::NAME;

    protected ?string $description = "Mostra all'utente un grafico: i partecipanti di ogni workshop (workshop_bookings) oppure quante persone hanno messo in agenda ogni talk (talk_agendas). Usalo quando l'utente chiede un grafico o delle statistiche.";

    public function __construct(
        private A2uiSurfaceBuilder $surfaces,
        private StatisticsService $statistics,
    ) {}

    protected function properties(): array
    {
        return [
            new ToolProperty(
                name: 'subject',
                type: PropertyType::STRING,
                description: 'I dati da mostrare nel grafico.',
                required: true,
                enum: array_column(ChartSubject::cases(), 'value'),
            ),
        ];
    }

    public function __invoke(string $subject): string
    {
        $subject = ChartSubject::from($subject);

        return $this->a2ui($this->surfaces->chart($subject->title(), 'bar', ...$this->series($subject)));
    }

    /**
     * The workshops show the booked seats next to the capacity; the talks how many agendas include them.
     *
     * @return array{labels: list<string>, datasets: list<array{label: string, data: list<int>}>}
     */
    private function series(ChartSubject $subject): array
    {
        $rows = collect($this->statistics->report(match ($subject) {
            ChartSubject::WorkshopBookings => StatisticsReport::WorkshopBookings,
            ChartSubject::TalkAgendas => StatisticsReport::TalkAgendas,
        }));

        $datasets = match ($subject) {
            ChartSubject::WorkshopBookings => ['Prenotati' => 'booked', 'Capienza' => 'capacity'],
            ChartSubject::TalkAgendas => ['In agenda' => 'in_agendas'],
        };

        return [
            'labels' => $rows->pluck('title')->all(),
            'datasets' => collect($datasets)->map(fn (string $column, string $label) => ['label' => $label, 'data' => $rows->pluck($column)->all()])->values()->all(),
        ];
    }
}
