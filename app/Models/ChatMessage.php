<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

/**
 * A message of an assistant conversation, written by Neuron's EloquentMessageStore.
 */
class ChatMessage extends Model
{
    protected $fillable = ['thread_id', 'message_id', 'role', 'content', 'meta'];

    protected function casts(): array
    {
        return [
            'content' => 'array',
            'meta' => 'array',
        ];
    }
}
