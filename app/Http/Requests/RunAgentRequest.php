<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Support\Arr;

/**
 * The AG-UI RunAgentInput posted by the HttpAgent of the chat.
 */
class RunAgentRequest extends FormRequest
{
    /**
     * The page names each conversation "user-{id}-{uuid}": a user can only talk in their own threads.
     */
    public function authorize(): bool
    {
        return str_starts_with($this->threadId(), "user-{$this->user()->id}-");
    }

    /**
     * @return array<string, array<int, mixed>>
     */
    public function rules(): array
    {
        return [
            'threadId' => ['required', 'string', 'max:255'],
            'runId' => ['required', 'string', 'max:255'],
            'messages' => ['present', 'array'],
            'messages.*.id' => ['required', 'string'],
            'resume' => ['nullable', 'array'],
        ];
    }

    public function threadId(): string
    {
        return is_string($id = $this->input('threadId')) ? $id : '';
    }

    /**
     * The answers to the approval interrupts continue the paused run instead of starting a new turn.
     */
    public function isResume(): bool
    {
        return $this->array('resume') !== [];
    }

    /**
     * Neuron keeps the history on the server: a new turn only needs the last user message.
     */
    public function prompt(): ?string
    {
        return Arr::last($this->array('messages'), fn (array $message) => ($message['role'] ?? null) === 'user')['content'] ?? null;
    }
}
