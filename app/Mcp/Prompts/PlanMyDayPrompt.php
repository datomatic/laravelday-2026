<?php

namespace App\Mcp\Prompts;

use Laravel\Mcp\Request;
use Laravel\Mcp\Response;
use Laravel\Mcp\Server\Attributes\Description;
use Laravel\Mcp\Server\Attributes\Name;
use Laravel\Mcp\Server\Attributes\Title;
use Laravel\Mcp\Server\Prompt;
use Laravel\Mcp\Server\Prompts\Argument;

#[Name('plan_my_day')]
#[Title('Pianifica la mia giornata')]
#[Description("Propone un'agenda della conferenza in base agli interessi dell'utente e, dopo la sua conferma, la salva.")]
class PlanMyDayPrompt extends Prompt
{
    public function handle(Request $request): Response
    {
        $validated = $request->validate([
            'interests' => ['required', 'string', 'max:200'],
        ]);

        return Response::text(<<<PROMPT
            I miei interessi sono: {$validated['interests']}.

            Pianifica la mia giornata alla conferenza:
            1. Guarda la mia agenda con show_agenda e il programma con search_talks.
            2. Per ogni fascia oraria scegli al massimo un talk vicino ai miei interessi, senza sovrapposizioni con quelli che ho già.
            3. Mostrami la proposta in una tabella con orario, sala, titolo e speaker, e spiega in una riga perché hai scelto ogni talk.
            4. Aggiungi i talk con add_to_agenda solo dopo che ho confermato.
            PROMPT);
    }

    /**
     * @return array<int, Argument>
     */
    public function arguments(): array
    {
        return [
            new Argument(
                name: 'interests',
                description: 'Gli argomenti che interessano, per esempio "AI e testing".',
                required: true,
            ),
        ];
    }
}
