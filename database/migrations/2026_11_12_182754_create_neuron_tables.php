<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * The conversations (EloquentMessageStore) and the durable runs (DatabasePersistence) of the Neuron agent.
     */
    public function up(): void
    {
        Schema::create('chat_messages', function (Blueprint $table) {
            $table->id();
            $table->string('thread_id');
            $table->string('message_id', 64);
            $table->string('role', 32);
            $table->longText('content')->nullable();
            $table->longText('meta')->nullable();
            $table->timestamp('archived_at')->nullable();
            $table->timestamps();
            $table->unique(['thread_id', 'message_id']);
        });

        Schema::create('workflow_store', function (Blueprint $table) {
            $table->string('partition', 510);
            $table->string('key', 510);
            $table->longText('value');
            $table->timestamp('updated_at')->useCurrent();
            $table->primary(['partition', 'key']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('workflow_store');
        Schema::dropIfExists('chat_messages');
    }
};
