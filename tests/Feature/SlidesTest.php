<?php

use App\Enums\DemoStage;

test('guests can open the slides', function () {
    $this->get(route('slides'))
        ->assertOk()
        ->assertSee('Apri la tua app agli agenti')
        ->assertSeeInOrder(['MCP', 'AG-UI', 'A2UI', 'WebMCP']);
});

test('the demo slides link every chat stage in order', function () {
    $this->get(route('slides'))
        ->assertOk()
        ->assertSeeInOrder([
            route('schedule', DemoStage::Classic),
            route('schedule', DemoStage::HomemadeChat),
            route('schedule', DemoStage::TextChat),
            route('schedule', DemoStage::Generative),
        ]);
});

test('code samples are rendered verbatim', function () {
    $this->get(route('slides'))
        ->assertOk()
        ->assertSee("Mcp::web('/mcp/conference', ConferenceServer::class)", false)
        ->assertSee('"{$key}_content"', false);
});

test('the demo links open in the same window', function () {
    $html = $this->get(route('slides'))->assertOk()->getContent();

    foreach (DemoStage::cases() as $stage) {
        expect($html)->toContain('href="'.$stage->url().'"')
            ->not->toContain('href="'.$stage->url().'" target="_blank"');
    }
});
