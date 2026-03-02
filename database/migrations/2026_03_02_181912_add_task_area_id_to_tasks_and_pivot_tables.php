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
        Schema::table('tasks', function (Blueprint $table) {
            $table->foreignId('task_area_id')->nullable()->after('company_id')->constrained('task_areas')->nullOnDelete();
        });

        Schema::table('company_professional', function (Blueprint $table) {
            $table->foreignId('task_area_id')->nullable()->after('professional_id')->constrained('task_areas')->nullOnDelete();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('company_professional', function (Blueprint $table) {
            $table->dropForeign(['task_area_id']);
            $table->dropColumn('task_area_id');
        });

        Schema::table('tasks', function (Blueprint $table) {
            $table->dropForeign(['task_area_id']);
            $table->dropColumn('task_area_id');
        });
    }
};
