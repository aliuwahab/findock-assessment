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
        Schema::table('csv_uploads', function (Blueprint $table) {
            $table->enum('status', ['pending', 'processing', 'completed', 'failed'])
                  ->default('pending')
                  ->after('field_mapping');
            $table->integer('total_rows')->nullable()->after('status');
            $table->integer('processed_rows')->default(0)->after('total_rows');
            $table->timestamp('processing_started_at')->nullable()->after('processed_rows');
            $table->timestamp('processing_completed_at')->nullable()->after('processing_started_at');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('csv_uploads', function (Blueprint $table) {
            $table->dropColumn([
                'status',
                'total_rows',
                'processed_rows',
                'processing_started_at',
                'processing_completed_at',
            ]);
        });
    }
};
