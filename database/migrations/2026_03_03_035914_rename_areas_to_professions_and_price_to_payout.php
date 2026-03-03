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
        // 1. Rename Table task_areas to professions
        Schema::rename('task_areas', 'professions');

        // 2. Rename Columns in services
        Schema::table('services', function (Blueprint $table) {
            $table->renameColumn('price', 'payout');
            $table->renameColumn('task_area_id', 'profession_id');
        });

        // 3. Rename Columns in tasks
        Schema::table('tasks', function (Blueprint $table) {
            $table->renameColumn('task_area_id', 'profession_id');
        });

        // 4. Rename pivot table company_professional_area to company_professional_profession
        Schema::rename('company_professional_area', 'company_professional_profession');
        
        Schema::table('company_professional_profession', function (Blueprint $table) {
            $table->renameColumn('task_area_id', 'profession_id');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('company_professional_profession', function (Blueprint $table) {
            $table->renameColumn('profession_id', 'task_area_id');
        });
        Schema::rename('company_professional_profession', 'company_professional_area');

        Schema::table('tasks', function (Blueprint $table) {
            $table->renameColumn('profession_id', 'task_area_id');
        });

        Schema::table('services', function (Blueprint $table) {
            $table->renameColumn('profession_id', 'task_area_id');
            $table->renameColumn('payout', 'price');
        });

        Schema::rename('professions', 'task_areas');
    }
};
