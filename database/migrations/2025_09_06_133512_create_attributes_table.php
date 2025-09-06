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
        Schema::create('attributes', function (Blueprint $table) {
            $table->id();
            $table->morphs('attributable');
            $table->string('attribute_key');
            $table->text('attribute_value');
            $table->enum('data_type', ['string', 'integer', 'double', 'boolean']);
            $table->timestamps();

            $table->unique(['attributable_id', 'attributable_type', 'attribute_key']);
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('attributes');
    }
};
