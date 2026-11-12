<?php

use App\Enums\Track;
use App\Mcp\Resources\ScheduleResource;
use App\Mcp\Servers\ConferenceServer;
use App\Mcp\Tools\AddToAgendaTool;
use App\Mcp\Tools\BookWorkshopTool;
use App\Mcp\Tools\ClearAgendaTool;
use App\Mcp\Tools\ListWorkshopsTool;
use App\Mcp\Tools\SearchTalksTool;
use App\Mcp\Tools\ShowAgendaTool;
use App\Models\AgendaItem;
use App\Models\Talk;
use App\Models\User;
use App\Models\Workshop;
use App\Models\WorkshopBooking;
use Illuminate\Testing\Fluent\AssertableJson;

test('search talks filters by text and track', function () {
    Talk::factory()->create(['title' => 'RAG con pgvector', 'track' => Track::Ai]);
    Talk::factory()->create(['title' => 'Code affidabili', 'track' => Track::Backend]);

    ConferenceServer::tool(SearchTalksTool::class, ['query' => 'pgvector'])
        ->assertOk()
        ->assertSee('RAG con pgvector')
        ->assertDontSee('Code affidabili');

    ConferenceServer::tool(SearchTalksTool::class, ['track' => Track::Backend->value])
        ->assertOk()
        ->assertSee('Code affidabili')
        ->assertDontSee('RAG con pgvector');
});

test('search talks rejects an unknown track', function () {
    ConferenceServer::tool(SearchTalksTool::class, ['track' => 'cucina'])
        ->assertHasErrors();
});

test('add to agenda stores the talk for the authenticated user only once', function () {
    $user = User::factory()->create();
    $talk = Talk::factory()->create();

    ConferenceServer::actingAs($user)->tool(AddToAgendaTool::class, ['talk_id' => $talk->id])->assertOk()->assertSee($talk->title);
    ConferenceServer::actingAs($user)->tool(AddToAgendaTool::class, ['talk_id' => $talk->id])->assertOk();

    expect(AgendaItem::where('user_id', $user->id)->where('talk_id', $talk->id)->count())->toBe(1);
});

test('add to agenda rejects a missing talk', function () {
    ConferenceServer::actingAs(User::factory()->create())
        ->tool(AddToAgendaTool::class, ['talk_id' => 999])
        ->assertHasErrors();

    expect(AgendaItem::count())->toBe(0);
});

test('show agenda lists only the talks of the authenticated user', function () {
    $user = User::factory()->create();
    $mine = AgendaItem::factory()->for($user)->create();
    $other = AgendaItem::factory()->create();

    ConferenceServer::actingAs($user)->tool(ShowAgendaTool::class)
        ->assertOk()
        ->assertSee($mine->talk->title)
        ->assertDontSee($other->talk->title);
});

test('clear agenda removes only the talks of the authenticated user', function () {
    $user = User::factory()->create();
    AgendaItem::factory()->for($user)->count(2)->create();
    AgendaItem::factory()->create();

    ConferenceServer::actingAs($user)->tool(ClearAgendaTool::class)->assertOk()->assertSee('2 talk rimossi');

    expect(AgendaItem::count())->toBe(1);
});

test('list workshops reports the seats left', function () {
    $workshop = Workshop::factory()->create(['capacity' => 3]);
    WorkshopBooking::factory()->for($workshop)->create();

    ConferenceServer::tool(ListWorkshopsTool::class)
        ->assertOk()
        ->assertStructuredContent(fn (AssertableJson $json) => $json->where('workshops.0.seats_left', 2)->etc());
});

test('book workshop reserves a seat', function () {
    $user = User::factory()->create();
    $workshop = Workshop::factory()->create();

    ConferenceServer::actingAs($user)->tool(BookWorkshopTool::class, ['workshop_id' => $workshop->id])->assertOk();

    expect(WorkshopBooking::where('user_id', $user->id)->where('workshop_id', $workshop->id)->exists())->toBeTrue();
});

test('book workshop fails when the workshop is full', function () {
    $user = User::factory()->create();
    $workshop = Workshop::factory()->create(['capacity' => 1]);
    WorkshopBooking::factory()->for($workshop)->create();

    ConferenceServer::actingAs($user)->tool(BookWorkshopTool::class, ['workshop_id' => $workshop->id])
        ->assertHasErrors(['al completo']);

    expect(WorkshopBooking::where('user_id', $user->id)->exists())->toBeFalse();
});

test('schedule resource returns the programme', function () {
    $talk = Talk::factory()->create();

    ConferenceServer::resource(ScheduleResource::class)->assertOk()->assertSee($talk->title);
});

test('the mcp endpoint requires a token', function () {
    $this->postJson('/mcp/conference', ['jsonrpc' => '2.0', 'id' => 1, 'method' => 'tools/list'])
        ->assertUnauthorized();
});
