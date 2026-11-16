<?php

use App\Services\A2uiComponentValidator;

function componentErrors(array $components): array
{
    return app(A2uiComponentValidator::class)->errors($components);
}

test('a dashboard with kpis, tabs, a chart and a table is valid', function () {
    expect(componentErrors([
        ['id' => 'root', 'component' => 'Column', 'children' => ['title', 'kpis', 'tabs']],
        ['id' => 'title', 'component' => 'Text', 'text' => '#### Workshop'],
        ['id' => 'kpis', 'component' => 'Row', 'children' => ['booked', 'free']],
        ['id' => 'booked', 'component' => 'Stat', 'label' => 'Prenotati', 'value' => 42, 'trend' => '+12%', 'weight' => 1],
        ['id' => 'free', 'component' => 'Stat', 'label' => 'Posti liberi', 'value' => '8', 'caption' => 'su 50', 'weight' => 1],
        ['id' => 'tabs', 'component' => 'Tabs', 'tabs' => [['title' => 'Grafico', 'child' => 'chart'], ['title' => 'Tabella', 'child' => 'table']]],
        ['id' => 'chart', 'component' => 'Chart', 'title' => 'Prenotati', 'type' => 'bar', 'labels' => ['Pest', 'MCP'], 'datasets' => [['label' => 'Prenotati', 'data' => [12, 8]], ['label' => 'Capienza', 'data' => [12, 20]]], 'unit' => 'posti'],
        ['id' => 'table', 'component' => 'DataTable', 'columns' => ['Workshop', 'Prenotati'], 'rows' => [['Pest', 12]]],
    ]))->toBe([]);
});

test('the tree needs a root and unique ids', function () {
    expect(componentErrors([]))->toBe(['The components field is required.'])
        ->and(componentErrors([['id' => 'a', 'component' => 'Divider']]))->toContain('Manca il componente con id "root".')
        ->and(componentErrors([['id' => 'root', 'component' => 'Divider'], ['id' => 'root', 'component' => 'Divider']]))->toBe([
            'The components.0.id field has a duplicate value.',
            'The components.1.id field has a duplicate value.',
        ]);
});

test('children must exist and have a single parent', function () {
    expect(componentErrors([
        ['id' => 'root', 'component' => 'Column', 'children' => ['text', 'text', 'ghost']],
        ['id' => 'text', 'component' => 'Text', 'text' => 'Ciao'],
    ]))->toBe([
        'root: "text" ha già un genitore.',
        'root: il figlio "ghost" non esiste.',
    ]);
});

test('a cycle back to the root is rejected', function () {
    expect(componentErrors([
        ['id' => 'root', 'component' => 'Card', 'child' => 'inner'],
        ['id' => 'inner', 'component' => 'Card', 'child' => 'root'],
    ]))->toBe(['inner: "root" ha già un genitore.']);
});

test('an unknown component lists the supported ones', function () {
    expect(componentErrors([['id' => 'root', 'component' => 'PieChart']]))
        ->toBe(['components.0.component deve essere uno di: '.implode(', ', A2uiComponentValidator::COMPONENTS).'.']);
});

test('wrong properties are reported with the id of the component', function (array $component, array $errors) {
    expect(componentErrors([['id' => 'root', ...$component]]))->toBe($errors);
})->with([
    'text without text' => [['component' => 'Text'], ['root: The text field is required.']],
    'stat without value' => [['component' => 'Stat', 'label' => 'Prenotati'], ['root: The value field is required.']],
    'stat with an object as value' => [['component' => 'Stat', 'label' => 'Prenotati', 'value' => ['n' => 1]], ['root: value deve essere un testo o un numero.']],
    'extra property on a custom component' => [['component' => 'Stat', 'label' => 'A', 'value' => 1, 'color' => 'red'], ['root: proprietà non previste: color.']],
    'unknown chart type' => [['component' => 'Chart', 'title' => 'A', 'type' => 'radar', 'labels' => ['B'], 'datasets' => [['label' => 'C', 'data' => [1]]]], ['root: type deve essere uno di: bar, line, area, pie, doughnut.']],
    'dataset shorter than the labels' => [['component' => 'Chart', 'title' => 'A', 'type' => 'line', 'labels' => ['B', 'C'], 'datasets' => [['label' => 'D', 'data' => [1]]]], ['root: The datasets.0.data field must contain 2 items.']],
    'table row too short' => [['component' => 'DataTable', 'columns' => ['A', 'B'], 'rows' => [['solo una']]], ['root: The rows.0 field must contain 2 items.']],
]);
