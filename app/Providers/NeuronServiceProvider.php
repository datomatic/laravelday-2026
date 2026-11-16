<?php

namespace App\Providers;

use App\Models\ChatMessage;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\ServiceProvider;
use NeuronAI\Chat\History\EloquentMessageStore;
use NeuronAI\Chat\History\MessageStoreInterface;
use NeuronAI\Workflow\Persistence\DatabasePersistence;
use NeuronAI\Workflow\Persistence\PersistenceInterface;

class NeuronServiceProvider extends ServiceProvider
{
    /**
     * The conversation and the paused runs live in the database: an approval resumes in a later request.
     */
    public function register(): void
    {
        $this->app->singleton(MessageStoreInterface::class, fn () => new EloquentMessageStore(ChatMessage::class));

        // Bound, not singleton: every resolution takes Laravel's current PDO.
        $this->app->bind(PersistenceInterface::class, fn () => new DatabasePersistence(DB::connection()->getPdo()));
    }
}
