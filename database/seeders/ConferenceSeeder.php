<?php

namespace Database\Seeders;

use App\Enums\Track;
use App\Models\Speaker;
use App\Models\Talk;
use App\Models\Workshop;
use Illuminate\Database\Seeder;

class ConferenceSeeder extends Seeder
{
    private const DAY = '2026-11-26';

    public function run(): void
    {
        foreach ($this->talks() as [$start, $end, $room, $track, $speaker, $company, $title, $abstract]) {
            Talk::create([
                'speaker_id' => $this->speaker($speaker, $company)->id,
                'title' => $title,
                'abstract' => $abstract,
                'track' => $track,
                'room' => $room,
                'starts_at' => self::DAY." {$start}",
                'ends_at' => self::DAY." {$end}",
            ]);
        }

        foreach ($this->workshops() as [$start, $end, $capacity, $speaker, $company, $title, $description]) {
            Workshop::create([
                'speaker_id' => $this->speaker($speaker, $company)->id,
                'title' => $title,
                'description' => $description,
                'capacity' => $capacity,
                'starts_at' => self::DAY." {$start}",
                'ends_at' => self::DAY." {$end}",
            ]);
        }
    }

    private function speaker(string $name, string $company): Speaker
    {
        return Speaker::firstOrCreate(['name' => $name], ['company' => $company]);
    }

    /**
     * @return list<array{string, string, string, Track, string, string, string, string}>
     */
    private function talks(): array
    {
        return [
            ['09:30', '10:10', 'Sala Arena', Track::Community, 'Giulia Ferraro', 'Studio Pixel', 'Dieci anni di Laravel in Italia', 'Come è cambiata la community italiana e cosa ci aspetta nei prossimi anni.'],
            ['10:20', '10:55', 'Sala Arena', Track::Ai, 'Marco Bellini', 'Nodo Digitale', 'MCP, AG-UI e A2UI: applicazioni Laravel pronte per gli agenti', 'Una sola app Laravel, più porte di ingresso per gli agenti AI: server MCP, chat con AG-UI e interfacce generate con A2UI.'],
            ['10:20', '10:55', 'Sala Verona', Track::DevOps, 'Sara Lombardi', 'Cloudfarm', 'Deploy senza downtime con Laravel Cloud', 'Strategie di rilascio, migrazioni sicure e rollback in produzione.'],
            ['11:20', '12:00', 'Sala Arena', Track::Backend, 'Davide Conti', 'Fattoria Codice', 'Code e job affidabili: oltre il retry', 'Idempotenza, backoff e monitoraggio dei job quando il volume cresce.'],
            ['11:20', '12:00', 'Sala Verona', Track::Frontend, 'Elena Moretti', 'Atelier Web', 'Livewire 4 nel mondo reale', 'Componenti, isole e prestazioni: cosa abbiamo imparato portando Livewire 4 in produzione.'],
            ['12:10', '12:50', 'Sala Arena', Track::Ai, 'Luca Marchetti', 'Vettore Lab', 'RAG con Laravel AI SDK e pgvector', 'Embeddings, vector store e reranking per rispondere con i dati della tua applicazione.'],
            ['14:20', '15:00', 'Sala Arena', Track::Backend, 'Chiara Rinaldi', 'Banca Aperta', 'Eloquent ad alte prestazioni', 'Indici, eager loading e query che scalano su tabelle da centinaia di milioni di righe.'],
            ['14:20', '15:00', 'Sala Verona', Track::Ai, 'Paolo Greco', 'Agenti Srl', 'Agenti in produzione: valutazioni e guardrail', 'Come misurare la qualità di un agente e limitare i danni quando sbaglia.'],
            ['15:10', '15:50', 'Sala Arena', Track::Frontend, 'Martina De Luca', 'Forma Studio', 'Tailwind 4 e design system condivisi', 'Token, temi e componenti Blade riusabili tra più applicazioni.'],
            ['16:20', '17:00', 'Sala Arena', Track::DevOps, 'Andrea Vitale', 'Osserva', 'Osservabilità per applicazioni Laravel', 'Log, metriche e tracing con Nightwatch e OpenTelemetry.'],
            ['17:10', '17:45', 'Sala Arena', Track::Community, 'Giulia Ferraro', 'Studio Pixel', 'Chiusura e saluti', 'Ringraziamenti, premi della community e appuntamento al prossimo anno.'],
        ];
    }

    /**
     * @return list<array{string, string, int, string, string, string, string}>
     */
    private function workshops(): array
    {
        return [
            ['14:00', '16:00', 20, 'Luca Marchetti', 'Vettore Lab', 'Costruisci il tuo primo server MCP', 'Dalla installazione di laravel/mcp a tool, resource e autenticazione, con un client reale.'],
            ['14:00', '16:00', 12, 'Elena Moretti', 'Atelier Web', 'Testing con Pest da zero', 'Feature test, dataset, architettura e browser testing su un progetto vero.'],
            ['16:15', '18:00', 2, 'Paolo Greco', 'Agenti Srl', 'Agenti con Laravel AI SDK', 'Tool, memoria delle conversazioni, streaming e approvazione umana delle azioni.'],
        ];
    }
}
