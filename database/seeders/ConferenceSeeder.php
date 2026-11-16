<?php

namespace Database\Seeders;

use App\Enums\Track;
use App\Models\Speaker;
use App\Models\Talk;
use App\Models\Workshop;
use Illuminate\Database\Seeder;

class ConferenceSeeder extends Seeder
{
    private const DAY = '2026-11-18';

    /**
     * Workshops run the day after the talks, so they never clash with the agenda.
     */
    private const WORKSHOP_DAY = '2026-11-19';

    public function run(): void
    {
        $speakers = collect($this->speakers())->map(fn (array $speaker, string $name) => Speaker::create([
            'name' => $name,
            'company' => $speaker[0],
            'photo' => 'images/speakers/'.str($name)->slug().'.jpg',
            'bio' => $speaker[1],
        ]));

        foreach ($this->talks() as [$start, $end, $room, $track, $speaker, $title, $abstract]) {
            Talk::create([
                'speaker_id' => $speakers[$speaker]->id,
                'title' => $title,
                'abstract' => $abstract,
                'track' => $track,
                'room' => $room,
                'starts_at' => self::DAY." {$start}",
                'ends_at' => self::DAY." {$end}",
            ]);
        }

        foreach ($this->workshops() as [$start, $end, $capacity, $speaker, $title, $description]) {
            Workshop::create([
                'speaker_id' => $speakers[$speaker]->id,
                'title' => $title,
                'description' => $description,
                'capacity' => $capacity,
                'starts_at' => self::WORKSHOP_DAY." {$start}",
                'ends_at' => self::WORKSHOP_DAY." {$end}",
            ]);
        }
    }

    /**
     * The photo of each speaker is public/images/speakers/{slug of the name}.jpg.
     *
     * @return array<string, array{string, string}> Name => [company, bio].
     */
    private function speakers(): array
    {
        return [
            'Giulia Ferraro' => ['Studio Pixel', 'Organizza meetup Laravel dal 2016 e coordina la community italiana. Ama i progetti open source e le slide con poche parole.'],
            'Marco Bellini' => ['Nodo Digitale', 'Sviluppatore Laravel da dieci anni, oggi costruisce integrazioni tra applicazioni web e agenti AI con MCP, AG-UI e A2UI.'],
            'Sara Lombardi' => ['Cloudfarm', 'Platform engineer: porta in produzione applicazioni Laravel senza downtime e scrive di pipeline di rilascio.'],
            'Davide Conti' => ['Fattoria Codice', 'Backend developer appassionato di code, job e sistemi distribuiti. Ha perso il conto dei retry in produzione.'],
            'Elena Moretti' => ['Atelier Web', 'Full-stack developer e contributor Livewire. Insegna testing con Pest nei workshop della community.'],
            'Luca Marchetti' => ['Vettore Lab', 'Si occupa di AI applicata: RAG, embeddings e ricerca semantica dentro applicazioni Laravel.'],
            'Federico Sala' => ['Pixel Forge', 'Frontend engineer, usa Inertia dalla prima versione e costruisce SPA che restano semplici da mantenere.'],
            'Chiara Rinaldi' => ['Banca Aperta', 'Database engineer nel settore fintech: indici, query e tabelle da centinaia di milioni di righe.'],
            'Paolo Greco' => ['Agenti Srl', 'Costruisce agenti AI per aziende e studia come valutarli e metterli in sicurezza in produzione.'],
            'Martina De Luca' => ['Forma Studio', 'Design engineer: progetta design system con Tailwind e componenti Blade condivisi tra più prodotti.'],
            'Valentina Costa' => ['Testa Bene', 'QA engineer convertita al codice: scrive test end-to-end con Pest e li fa girare in ogni pull request.'],
            'Andrea Vitale' => ['Osserva', 'SRE e consulente di osservabilità: log, metriche e tracing per capire cosa fanno davvero le applicazioni.'],
            'Simone Ricci' => ['Neurone', 'Sviluppatore PHP e autore di workflow agentici con neuron-ai. Crede che gli agenti si possano scrivere anche in PHP.'],
        ];
    }

    /**
     * @return list<array{string, string, string, Track, string, string, string}>
     */
    private function talks(): array
    {
        return [
            ['09:30', '10:10', 'Sala Arena', Track::Community, 'Giulia Ferraro', 'Dieci anni di Laravel in Italia', 'Come è cambiata la community italiana e cosa ci aspetta nei prossimi anni.'],
            ['10:20', '10:55', 'Sala Arena', Track::Ai, 'Marco Bellini', 'MCP, AG-UI e A2UI: applicazioni Laravel pronte per gli agenti', 'Una sola app Laravel, più porte di ingresso per gli agenti AI: server MCP, chat con AG-UI e interfacce generate con A2UI.'],
            ['10:20', '10:55', 'Sala Verona', Track::DevOps, 'Sara Lombardi', 'Deploy senza downtime con Laravel Cloud', 'Strategie di rilascio, migrazioni sicure e rollback in produzione.'],
            ['11:20', '12:00', 'Sala Arena', Track::Backend, 'Davide Conti', 'Code e job affidabili: oltre il retry', 'Idempotenza, backoff e monitoraggio dei job quando il volume cresce.'],
            ['11:20', '12:00', 'Sala Verona', Track::Frontend, 'Elena Moretti', 'Livewire 4 nel mondo reale', 'Componenti, isole e prestazioni: cosa abbiamo imparato portando Livewire 4 in produzione.'],
            ['12:10', '12:50', 'Sala Arena', Track::Ai, 'Luca Marchetti', 'RAG con Laravel AI SDK e pgvector', 'Embeddings, vector store e reranking per rispondere con i dati della tua applicazione.'],
            ['12:10', '12:50', 'Sala Verona', Track::Frontend, 'Federico Sala', 'Inertia 3 senza paura', 'Routing, form e prefetch: costruire SPA moderne restando dentro Laravel.'],
            ['14:20', '15:00', 'Sala Arena', Track::Backend, 'Chiara Rinaldi', 'Eloquent ad alte prestazioni', 'Indici, eager loading e query che scalano su tabelle da centinaia di milioni di righe.'],
            ['14:20', '15:00', 'Sala Verona', Track::Ai, 'Paolo Greco', 'Agenti in produzione: valutazioni e guardrail', 'Come misurare la qualità di un agente e limitare i danni quando sbaglia.'],
            ['15:10', '15:50', 'Sala Arena', Track::Frontend, 'Martina De Luca', 'Tailwind 4 e design system condivisi', 'Token, temi e componenti Blade riusabili tra più applicazioni.'],
            ['15:10', '15:50', 'Sala Verona', Track::Backend, 'Valentina Costa', 'Browser testing con Pest 4', 'Test end-to-end veloci e leggibili, dal login al checkout, senza lasciare PHP.'],
            ['16:20', '17:00', 'Sala Arena', Track::DevOps, 'Andrea Vitale', 'Osservabilità per applicazioni Laravel', 'Log, metriche e tracing con Nightwatch e OpenTelemetry.'],
            ['16:20', '17:00', 'Sala Verona', Track::Ai, 'Simone Ricci', 'Agenti PHP con neuron-ai', 'Workflow multi-step, memoria e approvazione umana: agenti AI scritti interamente in PHP.'],
            ['17:10', '17:45', 'Sala Arena', Track::Community, 'Giulia Ferraro', 'Chiusura e saluti', 'Ringraziamenti, premi della community e appuntamento al prossimo anno.'],
        ];
    }

    /**
     * @return list<array{string, string, int, string, string, string}>
     */
    private function workshops(): array
    {
        return [
            ['14:00', '16:00', 20, 'Luca Marchetti', 'Costruisci il tuo primo server MCP', 'Dalla installazione di laravel/mcp a tool, resource e autenticazione, con un client reale.'],
            ['14:00', '16:00', 12, 'Elena Moretti', 'Testing con Pest da zero', 'Feature test, dataset, architettura e browser testing su un progetto vero.'],
            ['16:15', '18:00', 20, 'Paolo Greco', 'Agenti con Laravel AI SDK', 'Tool, memoria delle conversazioni, streaming e approvazione umana delle azioni.'],
        ];
    }
}
