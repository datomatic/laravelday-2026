<?php

namespace App\Services;

use App\Enums\CompanyRole;
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
            footer: $this->button("talk_{$talk->id}_button", "Aggiungi all'agenda", ['name' => 'add_to_agenda', 'context' => ['talk_id' => $talk->id]]),
        ));

        return $this->surface('talks-'.str()->ulid(), $cards);
    }

    /**
     * Each card opens a Modal with the booking form: its inputs are bound to the surface data model,
     * and the submit button sends the bound values in the action context.
     *
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
            footer: $this->bookingForm($workshop),
        ));

        $dataModel = $workshops->mapWithKeys(fn (Workshop $workshop) => [
            "workshop_{$workshop->id}" => ['company_role' => [], 'company_name' => ''],
        ])->all();

        return $this->surface('workshops-'.str()->ulid(), $cards, $dataModel);
    }

    /**
     * The same booking form as the workshop cards, shown on its own when the agent needs the details.
     *
     * @return list<array<string, mixed>>
     */
    public function bookingFormCard(Workshop $workshop): array
    {
        $key = "workshop_{$workshop->id}";

        return [
            ['version' => self::VERSION, 'createSurface' => [
                'surfaceId' => 'booking-'.str()->ulid(),
                'catalogId' => self::BASIC_CATALOG,
                'components' => [
                    ['id' => 'root', 'component' => 'Card', 'child' => "{$key}_form"],
                    ...$this->bookingFormFields($workshop),
                ],
                'dataModel' => [$key => ['company_role' => [], 'company_name' => '']],
            ]],
        ];
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
     * @param  array<string, mixed>  $dataModel
     * @return list<array<string, mixed>>
     */
    private function surface(string $surfaceId, Collection $cards, array $dataModel = []): array
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
                ...($dataModel ? ['dataModel' => $dataModel] : []),
            ]],
        ];
    }

    /**
     * @param  list<array<string, mixed>>  $footer  The components below the body: the first one is the footer itself.
     * @return list<array<string, mixed>>
     */
    private function card(string $key, string $title, string $meta, string $body, array $footer, ?string $badge = null): array
    {
        return [
            ['id' => $key, 'component' => 'Card', 'child' => "{$key}_content"],
            ['id' => "{$key}_content", 'component' => 'Column', 'children' => ["{$key}_title", "{$key}_meta", "{$key}_body", $footer[0]['id']]],
            // 1.0 Text only has the body and caption variants: the heading comes from Markdown.
            ['id' => "{$key}_title", 'component' => 'Text', 'text' => "#### {$title}"],
            ...$this->meta($key, $meta, $badge),
            ['id' => "{$key}_body", 'component' => 'Text', 'text' => $body],
            ...$footer,
        ];
    }

    /**
     * @param  array{name: string, context: array<string, mixed>}  $action
     * @param  list<array<string, mixed>>  $checks
     * @return list<array<string, mixed>>
     */
    private function button(string $id, string $label, array $action, array $checks = []): array
    {
        return [
            ['id' => $id, 'component' => 'Button', 'variant' => 'primary', 'child' => "{$id}_label", 'action' => ['event' => $action], ...($checks ? ['checks' => $checks] : [])],
            ['id' => "{$id}_label", 'component' => 'Text', 'text' => $label],
        ];
    }

    /**
     * @return list<array<string, mixed>>
     */
    private function bookingForm(Workshop $workshop): array
    {
        $key = "workshop_{$workshop->id}";

        return [
            ['id' => "{$key}_booking", 'component' => 'Modal', 'trigger' => "{$key}_open", 'content' => "{$key}_form"],
            ...$this->button("{$key}_open", 'Prenota un posto', ['name' => 'open_booking_form', 'context' => ['workshop_id' => $workshop->id]]),
            ...$this->bookingFormFields($workshop),
        ];
    }

    /**
     * @return list<array<string, mixed>>
     */
    private function bookingFormFields(Workshop $workshop): array
    {
        $key = "workshop_{$workshop->id}";
        $role = ['@path' => "/{$key}/company_role"];
        $company = ['@path' => "/{$key}/company_name"];

        return [
            ['id' => "{$key}_form", 'component' => 'Column', 'children' => ["{$key}_form_title", "{$key}_role", "{$key}_company", "{$key}_submit"]],
            ['id' => "{$key}_form_title", 'component' => 'Text', 'text' => "#### Prenota: {$workshop->title}"],
            [
                'id' => "{$key}_role",
                'component' => 'ChoicePicker',
                'label' => 'Ruolo in azienda',
                'variant' => 'mutuallyExclusive',
                'displayStyle' => 'chips',
                'options' => array_map(fn (CompanyRole $option) => ['label' => $option->label(), 'value' => $option->value], CompanyRole::cases()),
                'value' => $role,
            ],
            ['id' => "{$key}_company", 'component' => 'TextField', 'label' => 'Nome azienda', 'placeholder' => 'Es. Acme S.r.l.', 'value' => $company],
            ...$this->button(
                "{$key}_submit",
                'Conferma prenotazione',
                ['name' => 'book_workshop', 'context' => ['workshop_id' => $workshop->id, 'company_role' => $role, 'company_name' => $company]],
                // The renderer disables the button until both checks pass.
                checks: [
                    ['condition' => ['@call' => 'required', 'args' => ['value' => $role]], 'message' => 'Scegli il ruolo.'],
                    ['condition' => ['@call' => 'required', 'args' => ['value' => $company]], 'message' => "Scrivi il nome dell'azienda."],
                ],
            ),
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
