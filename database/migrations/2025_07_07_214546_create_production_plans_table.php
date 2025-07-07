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
        Schema::create('production_plans', function (Blueprint $table) {
            $table->id();
            $table->string('shop_order_number')->nullable();
            $table->foreignId('part_number_id')->constrained('part_numbers');
            $table->integer('planned_quantity')->default(0);
            $table->integer('produced_quantity')->default(0);
            $table->integer('scrap_quantity')->default(0);
            $table->date('planned_date');
            $table->foreignId('shift_id')->nullable()->constrained('shifts');
            $table->foreignId('status_id')->default(1)->constrained('statuses');
            $table->boolean('synced_to_infor')->default(false);
            $table->timestamp('synced_at')->nullable();
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('production_plans');
    }
};
