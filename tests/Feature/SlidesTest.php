<?php

use App\Enums\DemoStage;
use App\Models\User;

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

test('pages without the back button link the first slide from the header', function () {
    $link = 'href="'.route('slides').'" class="btn-ghost text-sm">Slide del talk';

    $this->get(route('login'))->assertOk()->assertSee($link, false);

    $this->actingAs(User::factory()->create())
        ->withSession(['slides.return' => '#/12'])
        ->get(route('home'))
        ->assertOk()
        ->assertSee($link, false);
});

test('demo pages show only the back button to the slides', function () {
    $this->actingAs(User::factory()->create())
        ->get(DemoStage::Classic->url())
        ->assertOk()
        ->assertSee('Torna alle slide')
        ->assertDontSee('Slide del talk');
});
