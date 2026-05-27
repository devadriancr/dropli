<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('production_records', function (Blueprint $table) {
            $table->index(['part_number_id', 'record_type', 'created_at'], 'pr_partnumber_type_created_idx');
            $table->index(['record_type', 'created_at'], 'pr_type_created_idx');
            $table->index(['production_plan_id'], 'pr_production_plan_id_idx');
        });
    }

    public function down(): void
    {
        Schema::table('production_records', function (Blueprint $table) {
            $table->dropIndex('pr_partnumber_type_created_idx');
            $table->dropIndex('pr_type_created_idx');
            $table->dropIndex('pr_production_plan_id_idx');
        });
    }
};
