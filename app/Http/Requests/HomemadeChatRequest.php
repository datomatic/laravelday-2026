<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;

/**
 * Our own request format: the thread and the text the user typed, nothing else.
 */
class HomemadeChatRequest extends FormRequest
{
    /**
     * The page names each conversation "user-{id}-{uuid}": a user can only talk in their own threads.
     */
    public function authorize(): bool
    {
        return str_starts_with((string) $this->input('thread_id'), "user-{$this->user()->id}-");
    }

    /**
     * @return array<string, array<int, string>>
     */
    public function rules(): array
    {
        return [
            'thread_id' => ['required', 'string', 'max:255'],
            'message' => ['required', 'string', 'max:2000'],
        ];
    }
}
