<?php

namespace App\Neuron\Agents;

use App\Neuron\Tools\ShowBookingForm;
use App\Neuron\Tools\ShowChart;
use App\Neuron\Tools\ShowSpeakerCard;
use App\Neuron\Tools\ShowTalkCards;
use App\Neuron\Tools\ShowWorkshopCards;

/**
 * The same agent with the A2UI tools: their results are declarative components the chat renders.
 */
class GenerativeAssistant extends ConferenceAssistant
{
    protected function instructions(): string
    {
        return <<<'TEXT'
        Sei l'assistente della conferenza. Aiuti l'utente autenticato a orientarsi nel programma,
        a costruire la sua agenda personale e a prenotare i workshop. Rispondi in italiano, in modo breve.

        - Per presentare dei talk usa show_talk_cards con gli id trovati con search_talks, invece di elencarli a testo.
        - Per presentare i workshop usa show_workshop_cards.
        - Se l'utente chiede chi è uno speaker, trova il suo speaker_id con search_talks e mostra show_speaker_card.
        - Per prenotare un workshop servono il ruolo in azienda e il nome dell'azienda.
          Se l'utente li ha già detti usa book_workshop; altrimenti mostra il modulo con show_booking_form,
          senza chiederli a testo. Non inventarli.
        - Per grafici e statistiche usa show_chart: non disegnare grafici a testo e non usare show_talk_cards.
        - Dopo aver mostrato delle schede aggiungi solo una frase di commento: non ripetere il contenuto delle schede.
        - Non inventare talk, orari o id: usa sempre i tool.
        TEXT;
    }

    protected function tools(): array
    {
        return [
            ...parent::tools(),
            app(ShowTalkCards::class),
            app(ShowWorkshopCards::class),
            app(ShowBookingForm::class),
            app(ShowSpeakerCard::class),
            app(ShowChart::class),
        ];
    }
}
