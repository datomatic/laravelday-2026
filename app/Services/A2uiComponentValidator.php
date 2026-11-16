<?php

namespace App\Services;

use Closure;
use Illuminate\Support\Collection;
use Illuminate\Validation\Rule;

/**
 * Checks a component tree written by the model before it reaches the browser.
 * The errors go back to the model as the tool result, so it can fix the tree and try again.
 */
class A2uiComponentValidator
{
    /**
     * Basic catalog layout and text components, plus the custom ones in resources/js/a2ui/conference-catalog.js.
     */
    public const COMPONENTS = ['Text', 'Row', 'Column', 'List', 'Card', 'Tabs', 'Divider', 'Stat', 'Chart', 'DataTable'];

    public const CHART_TYPES = ['bar', 'line', 'area', 'pie', 'doughnut'];

    /**
     * The browser validates the custom components strictly: any other property would break the surface.
     *
     * @var array<string, list<string>>
     */
    private const CUSTOM_PROPERTIES = [
        'Stat' => ['label', 'value', 'caption', 'trend'],
        'Chart' => ['title', 'type', 'labels', 'datasets', 'unit'],
        'DataTable' => ['title', 'columns', 'rows'],
    ];

    /**
     * @param  array<int, mixed>  $components
     * @return list<string>
     */
    public function errors(array $components): array
    {
        $list = validator(['components' => $components], [
            'components' => ['required', 'list'],
            'components.*' => ['array'],
            'components.*.id' => ['required', 'string', 'distinct'],
            'components.*.component' => ['required', Rule::in(self::COMPONENTS)],
        ], [
            'components.*.component.in' => ':attribute deve essere uno di: '.implode(', ', self::COMPONENTS).'.',
        ]);

        if ($list->fails()) {
            return $list->errors()->all();
        }

        $components = collect($components)->keyBy('id');

        return [
            ...($components->has('root') ? [] : ['Manca il componente con id "root".']),
            ...$components->flatMap(fn (array $component, string $id) => $this->componentErrors($id, $component)),
            ...$this->treeErrors($components),
        ];
    }

    /**
     * @param  array<string, mixed>  $component
     * @return list<string>
     */
    private function componentErrors(string $id, array $component): array
    {
        $unknown = array_diff(array_keys($component), ['id', 'component', 'weight', ...self::CUSTOM_PROPERTIES[$component['component']] ?? []]);

        if (isset(self::CUSTOM_PROPERTIES[$component['component']]) && $unknown !== []) {
            return ["{$id}: proprietà non previste: ".implode(', ', $unknown).'.'];
        }

        return array_map(
            fn (string $error) => "{$id}: {$error}",
            validator($component, $this->rules($component), [
                'type.in' => 'type deve essere uno di: '.implode(', ', self::CHART_TYPES).'.',
            ])->errors()->all(),
        );
    }

    /**
     * @param  array<string, mixed>  $component
     * @return array<string, mixed>
     */
    private function rules(array $component): array
    {
        $ids = ['required', 'list', 'min:1'];

        return match ($component['component']) {
            'Text' => ['text' => ['required', 'string'], 'variant' => [Rule::in(['body', 'caption'])]],
            'Row', 'Column', 'List' => ['children' => $ids, 'children.*' => ['string']],
            'Card' => ['child' => ['required', 'string']],
            'Tabs' => ['tabs' => $ids, 'tabs.*.title' => ['required', 'string'], 'tabs.*.child' => ['required', 'string']],
            'Divider' => [],
            'Stat' => ['label' => ['required', 'string'], 'value' => ['required', $this->textOrNumber()], 'caption' => ['string'], 'trend' => ['string']],
            'Chart' => [
                'title' => ['required', 'string'],
                'type' => ['required', Rule::in(self::CHART_TYPES)],
                'labels' => $ids,
                'labels.*' => ['string'],
                'datasets' => $ids,
                'datasets.*.label' => ['required', 'string'],
                // One value for each label.
                'datasets.*.data' => ['required', 'list', 'size:'.count((array) ($component['labels'] ?? []))],
                'datasets.*.data.*' => ['numeric'],
                'unit' => ['string'],
            ],
            'DataTable' => [
                'title' => ['string'],
                'columns' => $ids,
                'columns.*' => ['string'],
                'rows' => ['present', 'list'],
                // One cell for each column.
                'rows.*' => ['list', 'size:'.count((array) ($component['columns'] ?? []))],
                'rows.*.*' => [$this->textOrNumber()],
            ],
        };
    }

    /**
     * Every child must exist and have a single parent: the tree has no cycles and nothing renders twice.
     *
     * @param  Collection<string, array<string, mixed>>  $components
     * @return list<string>
     */
    private function treeErrors(Collection $components): array
    {
        $errors = [];
        $parents = [];

        foreach ($components as $id => $component) {
            foreach ($this->children($component) as $child) {
                if (! $components->has($child)) {
                    $errors[] = "{$id}: il figlio \"{$child}\" non esiste.";
                } elseif ($child === 'root' || isset($parents[$child])) {
                    $errors[] = "{$id}: \"{$child}\" ha già un genitore.";
                }

                $parents[$child] = $id;
            }
        }

        return $errors;
    }

    /**
     * @param  array<string, mixed>  $component
     * @return list<string>
     */
    private function children(array $component): array
    {
        $children = match ($component['component']) {
            'Row', 'Column', 'List' => $component['children'] ?? [],
            'Card' => [$component['child'] ?? null],
            'Tabs' => array_column((array) ($component['tabs'] ?? []), 'child'),
            default => [],
        };

        return array_values(array_filter((array) $children, 'is_string'));
    }

    private function textOrNumber(): Closure
    {
        return function (string $attribute, mixed $value, Closure $fail): void {
            if (! is_string($value) && ! is_int($value) && ! is_float($value)) {
                $fail("{$attribute} deve essere un testo o un numero.");
            }
        };
    }
}
