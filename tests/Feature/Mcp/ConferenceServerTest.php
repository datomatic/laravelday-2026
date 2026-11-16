<?php

use App\Enums\CompanyRole;
use App\Enums\Track;
use App\Mcp\Prompts\PlanMyDayPrompt;
use App\Mcp\Resources\ScheduleResource;
use App\Mcp\Resources\WorkshopsApp;
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

test('search talks returns the speaker id so the agent can show the speaker card', function () {
    $talk = Talk::factory()->create(['title' => 'RAG con pgvector']);

    ConferenceServer::tool(SearchTalksTool::class, ['query' => 'pgvector'])
        ->assertOk()
        ->assertStructuredContent(fn (AssertableJson $json) => $json
            ->where('talks.0.speaker_id', $talk->speaker_id)
            ->etc());
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

test('add to agenda fails when the talk overlaps one already in the agenda', function () {
    $user = User::factory()->create();
    AgendaItem::factory()->for($user)->for(Talk::factory()->state(['starts_at' => '2026-11-18 10:00', 'ends_at' => '2026-11-18 10:40']))->create();
    $overlapping = Talk::factory()->create(['starts_at' => '2026-11-18 10:00', 'ends_at' => '2026-11-18 10:40']);

    ConferenceServer::actingAs($user)->tool(AddToAgendaTool::class, ['talk_id' => $overlapping->id])
        ->assertHasErrors(['hai già in agenda']);

    expect(AgendaItem::where('talk_id', $overlapping->id)->exists())->toBeFalse();
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

test('book workshop reserves a seat with role and company', function () {
    $user = User::factory()->create();
    $workshop = Workshop::factory()->create();

    ConferenceServer::actingAs($user)
        ->tool(BookWorkshopTool::class, ['workshop_id' => $workshop->id, 'company_role' => CompanyRole::Cto->value, 'company_name' => 'Acme'])
        ->assertOk();

    expect(WorkshopBooking::where('user_id', $user->id)->where('workshop_id', $workshop->id)->sole())
        ->company_role->toBe(CompanyRole::Cto)
        ->company_name->toBe('Acme');
});

test('book workshop asks for role and company', function () {
    $user = User::factory()->create();
    $workshop = Workshop::factory()->create();

    ConferenceServer::actingAs($user)->tool(BookWorkshopTool::class, ['workshop_id' => $workshop->id])
        ->assertHasErrors(['company role', 'company name']);

    expect(WorkshopBooking::count())->toBe(0);
});

test('book workshop fails when the workshop is full', function () {
    $user = User::factory()->create();
    $workshop = Workshop::factory()->create(['capacity' => 1]);
    WorkshopBooking::factory()->for($workshop)->create();

    ConferenceServer::actingAs($user)
        ->tool(BookWorkshopTool::class, ['workshop_id' => $workshop->id, 'company_role' => CompanyRole::Developer->value, 'company_name' => 'Acme'])
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

test('each tool tells the client what it does with annotations', function (string $tool, array $annotations) {
    expect((new $tool)->toArray()['annotations'])->toMatchArray($annotations);
})->with([
    'search talks' => [SearchTalksTool::class, ['readOnlyHint' => true, 'openWorldHint' => false]],
    'show agenda' => [ShowAgendaTool::class, ['readOnlyHint' => true, 'openWorldHint' => false]],
    'list workshops' => [ListWorkshopsTool::class, ['readOnlyHint' => true, 'openWorldHint' => false]],
    'add to agenda' => [AddToAgendaTool::class, ['destructiveHint' => false, 'idempotentHint' => true, 'openWorldHint' => false]],
    'clear agenda' => [ClearAgendaTool::class, ['destructiveHint' => true, 'idempotentHint' => true, 'openWorldHint' => false]],
    'book workshop' => [BookWorkshopTool::class, ['destructiveHint' => false, 'idempotentHint' => true, 'openWorldHint' => false]],
]);

test('read tools declare the shape of the data they return', function (string $tool, string $key, string $model) {
    $summary = $model::factory()->create()->toSummary();
    $itemSchema = (new $tool)->toArray()['outputSchema']['properties'][$key]['items'];

    expect($itemSchema['required'])->toEqualCanonicalizing(array_keys($summary))
        ->and(array_keys($itemSchema['properties']))->toEqualCanonicalizing(array_keys($summary));
})->with([
    'search talks' => [SearchTalksTool::class, 'talks', Talk::class],
    'show agenda' => [ShowAgendaTool::class, 'talks', Talk::class],
    'list workshops' => [ListWorkshopsTool::class, 'workshops', Workshop::class],
]);

test('plan my day prompt asks the model to plan around the user interests with the existing tools', function () {
    ConferenceServer::prompt(PlanMyDayPrompt::class, ['interests' => 'AI e testing'])
        ->assertOk()
        ->assertSee(['AI e testing', 'search_talks', 'show_agenda', 'add_to_agenda']);
});

test('plan my day prompt requires the interests', function () {
    ConferenceServer::prompt(PlanMyDayPrompt::class)
        ->assertHasErrors();
});

test('list workshops renders the workshops app in the client', function () {
    $meta = (new ListWorkshopsTool)->toArray()['_meta']['ui'];

    expect($meta['resourceUri'])->toBe((new WorkshopsApp)->uri())
        ->and($meta['resourceUri'])->toStartWith('ui://');
});

test('workshops app is an html page that books through the book workshop tool', function () {
    expect((new WorkshopsApp)->toArray()['mimeType'])->toBe('text/html;profile=mcp-app')
        ->and((new WorkshopsApp)->resolvedAppMeta())->not->toHaveKey('domain');

    ConferenceServer::resource(WorkshopsApp::class)
        ->assertOk()
        ->assertSee(['createMcpApp', 'book_workshop', CompanyRole::TechLead->label()]);
});
