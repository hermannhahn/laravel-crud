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
        Schema::create('company_professional', function (Blueprint $table) {
            $table->id();
            $table->foreignId('company_id')->constrained('users')->cascadeOnDelete();
            $table->foreignId('professional_id')->constrained('users')->cascadeOnDelete();
            
            // Permissions given by Company to Professional
            $table->boolean('can_view_tasks')->default(true);
            $table->boolean('can_respond_tasks')->default(true);
            $table->boolean('can_edit_tasks')->default(false);
            
            $table->timestamps();

            $table->unique(['company_id', 'professional_id']);
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('company_professional');
    }
};
