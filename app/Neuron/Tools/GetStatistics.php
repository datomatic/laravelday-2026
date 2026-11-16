<?php

namespace App\Neuron\Tools;

use App\Enums\StatisticsReport;
use App\Services\StatisticsService;
use NeuronAI\Tools\PropertyType;
use NeuronAI\Tools\Tool;
use NeuronAI\Tools\ToolProperty;

class GetStatistics extends Tool
{
    public const NAME = 'get_statistics';

    protected string $name = self::NAME;

    protected ?string $description = 'Legge in sola lettura le statistiche della conferenza e le restituisce in JSON. Puoi chiamarlo più volte con report diversi.';

    public function __construct(private StatisticsService $statistics) {}

    protected function properties(): array
    {
        return [
            new ToolProperty(
                name: 'report',
                type: PropertyType::STRING,
                description: collect(StatisticsReport::cases())->map(fn (StatisticsReport $report) => "{$report->value}: {$report->description()}")->implode('; '),
                required: true,
                enum: array_column(StatisticsReport::cases(), 'value'),
            ),
        ];
    }

    public function __invoke(string $report): string
    {
        return json_encode($this->statistics->report(StatisticsReport::from($report)), JSON_UNESCAPED_UNICODE | JSON_UNESCAPED_SLASHES);
    }
}
