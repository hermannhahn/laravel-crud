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
            // company_id will replace the existing user_id logic for ownership
            $table->foreignId('company_id')->nullable()->after('user_id')->constrained('users')->cascadeOnDelete();
            // attributed professional
            $table->foreignId('professional_id')->nullable()->after('company_id')->constrained('users')->nullOnDelete();
        });

        Schema::create('task_responses', function (Blueprint $table) {
            $table->id();
            $table->foreignId('task_id')->constrained()->cascadeOnDelete();
            $table->foreignId('user_id')->constrained()->cascadeOnDelete(); // The professional
            $table->text('message');
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('task_responses');
        Schema::table('tasks', function (Blueprint $table) {
            $table->dropForeign(['company_id']);
            $table->dropForeign(['professional_id']);
            $table->dropColumn(['company_id', 'professional_id']);
        });
    }
};
