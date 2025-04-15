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
        Schema::create('appointments', function (Blueprint $table) {
            $table->id();
            $table->uuid('uuid')->unique();
            $table->foreignId('service_id')->constrained()->onDelete('cascade');
            $table->string('client_first_name');
            $table->string('client_last_name');
            $table->string('client_email');
            $table->string('client_phone')->nullable();
            $table->text('notes')->nullable();
            $table->dateTime('start_time');
            $table->dateTime('end_time');
            $table->foreignId('brand_id')->nullable()->constrained('brands')->onDelete('set null');
            $table->text('issue_description')->nullable();
            $table->string('equipment_photo_path')->nullable();
            $table->text('address')->nullable();
            $table->string('status')->default('Pending'); // pending, confirmed, cancelled, completed
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('appointments');
    }
};
