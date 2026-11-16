<?php

namespace App\Enums;

use App\Neuron\Agents\AdminAssistant;
use App\Neuron\Agents\ConferenceAssistant;
use App\Neuron\Agents\GenerativeAssistant;
use App\Neuron\Agents\HomemadeAssistant;

/**
 * The steps of the talk: the same schedule page, opened to agents one layer at a time.
 */
enum DemoStage: string
{
    case Classic = 'classica';
    case HomemadeChat = 'chat-fatta-in-casa';
    case TextChat = 'chat-testuale';
    case Generative = 'chat-generativa';
    case Admin = 'admin';

    public function step(): int
    {
        return array_search($this, self::cases(), true) + 1;
    }

    public function label(): string
    {
        return match ($this) {
            self::Classic => 'Pagina classica',
            self::HomemadeChat => 'Chat fatta in casa',
            self::TextChat => 'Chat agentica con AG-UI',
            self::Generative => 'Chat con interfacce generate',
            self::Admin => 'Dashboard generata',
        };
    }

    public function description(): string
    {
        return match ($this) {
            self::Classic => "Nessun agente: l'utente gestisce agenda e workshop a mano, con form e bottoni.",
            self::HomemadeChat => "Una chat come tante: l'agente usa i tool dell'app e risponde in streaming con un protocollo SSE inventato da noi.",
            self::TextChat => "Una chat AG-UI affiancata alla pagina: l'agente usa gli stessi tool e risponde solo con testo in Markdown.",
            self::Generative => "L'agente risponde anche con componenti A2UI: schede, moduli e grafici con cui interagire.",
            self::Admin => "Per l'organizzatore: l'agente legge le statistiche e compone da solo la dashboard, con KPI, grafici e tabelle.",
        };
    }

    public function protocols(): string
    {
        return match ($this) {
            self::Classic => 'Laravel',
            self::HomemadeChat => 'SSE fatto in casa',
            self::TextChat => 'AG-UI',
            self::Generative => 'AG-UI + A2UI',
            self::Admin => 'AG-UI + A2UI libero',
        };
    }

    /**
     * The admin dashboard is its own page, for the organiser only: the other stages open the schedule.
     */
    public function url(): string
    {
        return $this === self::Admin ? route('admin') : route('schedule', $this);
    }

    /**
     * The homemade chat speaks its own SSE protocol: only the later stages talk AG-UI.
     */
    public function usesAgUi(): bool
    {
        return in_array($this, [self::TextChat, self::Generative, self::Admin], true);
    }

    /**
     * @return class-string<ConferenceAssistant>|null
     */
    public function agent(): ?string
    {
        return match ($this) {
            self::Classic => null,
            self::HomemadeChat => HomemadeAssistant::class,
            self::TextChat => ConferenceAssistant::class,
            self::Generative => GenerativeAssistant::class,
            self::Admin => AdminAssistant::class,
        };
    }
}
