<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        Schema::create('part_number_sequences', function (Blueprint $table) {
            $table->id();
            $table->foreignId('current_part_number_id')->constrained('part_numbers');
            $table->foreignId('next_part_number_id')->constrained('part_numbers');
            $table->integer('sequence_order')->default(1);
            $table->integer('lead_time_hours')->nullable();
            $table->boolean('is_active')->default(true);
            $table->timestamps();

            $table->index(['current_part_number_id', 'sequence_order']);
            $table->unique(['current_part_number_id', 'next_part_number_id']);
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('part_number_sequences');
    }
};
