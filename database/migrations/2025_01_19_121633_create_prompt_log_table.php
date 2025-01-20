<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class () extends Migration {
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        Schema::create('prompt_log', function (Blueprint $table) {
            $table->id();
            $table->text('prompt');
            $table->text('response');
            $table->unsignedTinyInteger('relevance')->nullable();
            $table->unsignedTinyInteger('clarity')->nullable();
            $table->unsignedTinyInteger('tone')->nullable();
            $table->decimal('average_score', 5, 2)->nullable();
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('prompt_log');
    }
};
