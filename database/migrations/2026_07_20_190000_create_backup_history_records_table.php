<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('backup_history_records', function (Blueprint $table) {
            $table->id();
            $table->uuid('uuid')->unique();
            $table->string('external_id')->unique();
            $table->string('disk');
            $table->string('path');
            $table->string('filename');
            $table->unsignedBigInteger('size_bytes')->default(0);
            $table->timestamp('backup_created_at');
            $table->timestamp('file_deleted_at')->nullable();
            $table->string('deletion_type')->nullable();
            $table->foreignId('deleted_by_user_id')->nullable()->constrained('users')->nullOnDelete();
            $table->timestamp('audit_retention_expires_at')->nullable();
            $table->timestamp('anonymized_at')->nullable();
            $table->timestamps();
            $table->softDeletes();

            $table->index(['disk', 'path']);
            $table->index('file_deleted_at');
            $table->index('audit_retention_expires_at');
            $table->index('anonymized_at');
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('backup_history_records');
    }
};
