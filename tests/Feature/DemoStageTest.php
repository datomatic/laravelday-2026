<?php

use App\Enums\DemoStage;
use App\Models\User;
use App\Models\Workshop;

test('the home links every stage of the demo in order', function () {
    $this->actingAs(User::factory()->create())->get(route('home'))
        ->assertOk()
        ->assertSeeInOrder([
            route('schedule', DemoStage::Classic), 'Pagina classica',
            route('schedule', DemoStage::HomemadeChat), 'Chat fatta in casa',
            route('schedule', DemoStage::TextChat), 'Chat agentica con AG-UI',
            route('schedule', DemoStage::Generative), 'Chat con interfacce generate',
        ]);
});

test('the classic page has no chat', function () {
    $this->actingAs(User::factory()->create())->get(route('schedule', DemoStage::Classic))
        ->assertOk()
        ->assertDontSee('id="chat"', false);
});

test('the AG-UI chat stages talk to their own agent and show the event log', function (DemoStage $stage) {
    $this->actingAs(User::factory()->create())->get(route('schedule', $stage))
        ->assertOk()
        ->assertSee('data-protocol="ag-ui"', false)
        ->assertSee('data-agent-url="'.route('agent', $stage).'"', false)
        ->assertSee($stage->protocols())
        ->assertSee('Eventi AG-UI');
})->with([DemoStage::TextChat, DemoStage::Generative]);

test('the homemade chat talks to its own endpoint and logs its own events', function () {
    $this->actingAs(User::factory()->create())->get(route('schedule', DemoStage::HomemadeChat))
        ->assertOk()
        ->assertSee('data-protocol="homemade"', false)
        ->assertSee('data-agent-url="'.route('homemade-chat').'"', false)
        ->assertSee('id="chat-events"', false)
        ->assertSee('Eventi SSE fatti in casa')
        ->assertDontSee('Eventi AG-UI');
});

test('an unknown stage is not found', function () {
    $this->actingAs(User::factory()->create())->get('/programma/sconosciuta')->assertNotFound();
});

test('the workshop booking form opens in a modal', function () {
    $workshop = Workshop::factory()->create(['capacity' => 5]);

    $this->actingAs(User::factory()->create())->get(route('schedule', DemoStage::Classic))
        ->assertOk()
        ->assertSee('commandfor="booking-'.$workshop->id.'" command="show-modal"', false)
        ->assertSee('<dialog id="booking-'.$workshop->id.'"', false);
});

test('the demo links back to the slide it was opened from', function () {
    $user = User::factory()->create();

    $this->actingAs($user)->get(route('schedule', DemoStage::Classic))
        ->assertOk()
        ->assertSee('href="'.route('slides').'"', false);

    $this->get(route('schedule', [DemoStage::HomemadeChat, 'slide' => '#/8/0/1']))
        ->assertOk()
        ->assertSee('href="'.route('slides').'#/8/0/1"', false);

    // Later pages of the demo, opened without the parameter, still go back to the same slide.
    $this->get(route('schedule', DemoStage::TextChat))
        ->assertSee('href="'.route('slides').'#/8/0/1"', false);
});

test('a slide reference that is not a slide hash is ignored', function () {
    $this->actingAs(User::factory()->create())
        ->get(route('schedule', [DemoStage::Classic, 'slide' => '"><script>']))
        ->assertOk()
        ->assertSessionMissing('slides.return')
        ->assertSee('href="'.route('slides').'"', false);
});
