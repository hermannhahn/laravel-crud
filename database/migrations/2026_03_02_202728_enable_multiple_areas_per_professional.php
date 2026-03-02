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
        Schema::create('company_professional_area', function (Blueprint $table) {
            $table->id();
            $table->foreignId('company_id')->constrained('users')->cascadeOnDelete();
            $table->foreignId('professional_id')->constrained('users')->cascadeOnDelete();
            $table->foreignId('task_area_id')->constrained('task_areas')->cascadeOnDelete();
            $table->timestamps();

            $table->unique(['company_id', 'professional_id', 'task_area_id'], 'cp_area_unique');
        });

        Schema::table('company_professional', function (Blueprint $table) {
            $table->dropForeign(['task_area_id']);
            $table->dropColumn('task_area_id');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('company_professional', function (Blueprint $table) {
            $table->foreignId('task_area_id')->nullable()->after('professional_id')->constrained('task_areas')->nullOnDelete();
        });

        Schema::dropIfExists('company_professional_area');
    }
};
