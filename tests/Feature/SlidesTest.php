<?php

use App\Enums\DemoStage;

test('guests can open the slides', function () {
    $this->get(route('slides'))
        ->assertOk()
        ->assertSee('Apri la tua app agli agenti')
        ->assertSeeInOrder(['MCP', 'AG-UI', 'A2UI', 'WebMCP']);
});

test('code samples are rendered verbatim', function () {
    $this->get(route('slides'))
        ->assertOk()
        ->assertSee("Mcp::web('/mcp/conference', ConferenceServer::class)", false)
        ->assertSee('Response::text("\\"{$talk->title}\\" aggiunto all\'agenda.");', false);
});

test('the demo slides link every stage in order, in the same window', function () {
    $links = collect(DemoStage::cases())->map(fn (DemoStage $stage) => 'href="'.$stage->url().'"');

    $html = $this->get(route('slides'))
        ->assertOk()
        ->assertSeeInOrder($links->all(), false)
        ->getContent();

    $links->each(fn (string $link) => expect($html)->not->toContain($link.' target="_blank"'));
});
