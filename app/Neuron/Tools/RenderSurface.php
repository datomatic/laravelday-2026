<?php

namespace App\Neuron\Tools;

use App\Neuron\Tools\Concerns\RendersA2ui;
use App\Services\A2uiComponentValidator;
use App\Services\A2uiSurfaceBuilder;
use NeuronAI\Tools\PropertyType;
use NeuronAI\Tools\Tool;
use NeuronAI\Tools\ToolOutput;
use NeuronAI\Tools\ToolProperty;

/**
 * The model writes the whole component tree: layout, components and the data inside them.
 * An invalid tree goes back to the model as an error listing what is wrong, and the model tries again.
 */
class RenderSurface extends Tool
{
    use RendersA2ui;

    public const NAME = 'render_surface';

    protected string $name = self::NAME;

    protected ?string $description = "Mostra all'utente un'interfaccia A2UI composta da te: una lista piatta di componenti che si riferiscono tra loro per id, a partire da quello con id \"root\".";

    public function __construct(
        private A2uiComponentValidator $validator,
        private A2uiSurfaceBuilder $surfaces,
    ) {}

    protected function properties(): array
    {
        return [
            new ToolProperty('components', PropertyType::STRING, 'La lista dei componenti, come array JSON.', required: true),
        ];
    }

    public function __invoke(string $components): ToolOutput
    {
        $components = json_decode($components, true);

        if (! is_array($components)) {
            return ToolOutput::error('components non è un array JSON valido. Correggilo e richiama render_surface.');
        }

        $errors = $this->validator->errors($components);

        if ($errors !== []) {
            return ToolOutput::error("L'interfaccia non è valida. Correggila e richiama render_surface.\n- ".implode("\n- ", $errors));
        }

        return ToolOutput::text($this->a2ui($this->surfaces->generated($components)));
    }
}
