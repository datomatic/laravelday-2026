<?php

namespace App\Services;

use App\Models\Talk;
use App\Models\Workshop;
use Illuminate\Support\Collection;

/**
 * Builds A2UI 1.0 messages using the basic catalog: one createSurface carrying its components inline.
 *
 * @see https://a2ui.org/specification/v1_0/
 */
class A2uiSurfaceBuilder
{
    public const VERSION = '1.0';

    public const BASIC_CATALOG = 'https://a2ui.org/specification/v1_0/catalogs/basic/catalog.json';

    /**
     * The basic catalog plus the BarChart component registered in resources/js/a2ui/conference-catalog.js.
     */
    public const CONFERENCE_CATALOG = 'urn:laravelday:a2ui:conference';

    /**
     * @param  Collection<int, Talk>  $talks
     * @return list<array<string, mixed>>
     */
    public function talkCards(Collection $talks): array
    {
        $cards = $talks->map(fn (Talk $talk) => $this->card(
            key: "talk_{$talk->id}",
            title: $talk->title,
            meta: "{$talk->starts_at->format('H:i')}–{$talk->ends_at->format('H:i')} · {$talk->room}",
            badge: $talk->track->label(),
            body: "{$talk->speaker->name} — {$talk->abstract}",
            buttonLabel: "Aggiungi all'agenda",
            action: ['name' => 'add_to_agenda', 'context' => ['talk_id' => $talk->id]],
        ));

        return $this->surface('talks-'.str()->ulid(), $cards);
    }

    /**
     * @param  Collection<int, Workshop>  $workshops
     * @return list<array<string, mixed>>
     */
    public function workshopCards(Collection $workshops): array
    {
        $cards = $workshops->map(fn (Workshop $workshop) => $this->card(
            key: "workshop_{$workshop->id}",
            title: $workshop->title,
            meta: "{$workshop->starts_at->format('H:i')}–{$workshop->ends_at->format('H:i')} · posti rimasti: {$workshop->seatsLeft()}",
            body: "{$workshop->speaker->name} — {$workshop->description}",
            buttonLabel: 'Prenota un posto',
            action: ['name' => 'book_workshop', 'context' => ['workshop_id' => $workshop->id]],
        ));

        return $this->surface('workshops-'.str()->ulid(), $cards);
    }

    /**
     * @param  Collection<int, array{label: string, value: int, max?: int}>  $bars
     * @return list<array<string, mixed>>
     */
    public function barChart(string $title, Collection $bars): array
    {
        return [
            ['version' => self::VERSION, 'createSurface' => [
                'surfaceId' => 'chart-'.str()->ulid(),
                'catalogId' => self::CONFERENCE_CATALOG,
                'components' => [
                    ['id' => 'root', 'component' => 'Card', 'child' => 'chart'],
                    ['id' => 'chart', 'component' => 'BarChart', 'title' => $title, 'bars' => $bars->values()->all()],
                ],
            ]],
        ];
    }

    /**
     * @param  Collection<int, list<array<string, mixed>>>  $cards
     * @return list<array<string, mixed>>
     */
    private function surface(string $surfaceId, Collection $cards): array
    {
        $root = [
            'id' => 'root',
            'component' => 'Column',
            'children' => $cards->map(fn (array $components) => $components[0]['id'])->values()->all(),
        ];

        return [
            ['version' => self::VERSION, 'createSurface' => [
                'surfaceId' => $surfaceId,
                'catalogId' => self::BASIC_CATALOG,
                'components' => [$root, ...$cards->flatten(1)->all()],
            ]],
        ];
    }

    /**
     * @param  array{name: string, context: array<string, mixed>}  $action
     * @return list<array<string, mixed>>
     */
    private function card(string $key, string $title, string $meta, string $body, string $buttonLabel, array $action, ?string $badge = null): array
    {
        return [
            ['id' => $key, 'component' => 'Card', 'child' => "{$key}_content"],
            ['id' => "{$key}_content", 'component' => 'Column', 'children' => ["{$key}_title", "{$key}_meta", "{$key}_body", "{$key}_button"]],
            // 1.0 Text only has the body and caption variants: the heading comes from Markdown.
            ['id' => "{$key}_title", 'component' => 'Text', 'text' => "#### {$title}"],
            ...$this->meta($key, $meta, $badge),
            ['id' => "{$key}_body", 'component' => 'Text', 'text' => $body],
            ['id' => "{$key}_button", 'component' => 'Button', 'variant' => 'primary', 'child' => "{$key}_button_label", 'action' => ['event' => $action]],
            ['id' => "{$key}_button_label", 'component' => 'Text', 'text' => $buttonLabel],
        ];
    }

    /**
     * The time sits on the left and the optional badge on the right of a Row.
     * The badge is labelled for screen readers, and the stylesheet styles it through that aria-label.
     *
     * @return list<array<string, mixed>>
     */
    private function meta(string $key, string $meta, ?string $badge): array
    {
        if ($badge === null) {
            return [['id' => "{$key}_meta", 'component' => 'Text', 'variant' => 'caption', 'text' => $meta]];
        }

        return [
            ['id' => "{$key}_meta", 'component' => 'Row', 'justify' => 'spaceBetween', 'align' => 'center', 'children' => ["{$key}_time", "{$key}_badge"]],
            ['id' => "{$key}_time", 'component' => 'Text', 'variant' => 'caption', 'text' => $meta],
            ['id' => "{$key}_badge", 'component' => 'Text', 'variant' => 'caption', 'text' => $badge, 'accessibility' => ['label' => "Track {$badge}"]],
        ];
    }
}
