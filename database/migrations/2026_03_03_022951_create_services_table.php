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
        Schema::create('services', function (Blueprint $row) {
            $row->id();
            $row->foreignId('company_id')->constrained('users')->onDelete('cascade');
            $row->foreignId('task_area_id')->constrained('task_areas')->onDelete('cascade');
            $row->string('title');
            $row->text('description')->nullable();
            $row->decimal('price', 10, 2);
            $row->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('services');
    }
};
