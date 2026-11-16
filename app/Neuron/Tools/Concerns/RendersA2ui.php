<?php

namespace App\Neuron\Tools\Concerns;

/**
 * The tool result the chat renders: A2UI messages under the "a2ui" key, instead of text for the model to summarise.
 */
trait RendersA2ui
{
    /**
     * @param  list<array<string, mixed>>  $messages
     */
    protected function a2ui(array $messages): string
    {
        return json_encode(['a2ui' => $messages], JSON_UNESCAPED_UNICODE | JSON_UNESCAPED_SLASHES);
    }
}
