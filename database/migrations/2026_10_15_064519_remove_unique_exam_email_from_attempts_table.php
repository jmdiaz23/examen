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
        try {
            Schema::disableForeignKeyConstraints();
            Schema::table('attempts', function (Blueprint $table) {
                $table->dropUnique('attempts_exam_id_email_unique');
            });
            Schema::enableForeignKeyConstraints();
        } catch (\Throwable $e) {
            Schema::enableForeignKeyConstraints();
        }
        Schema::table('attempts', function (Blueprint $table) {
            if (! Schema::hasIndex('attempts', ['exam_id', 'email'])) {
                $table->index(['exam_id', 'email']);
            }
        });
    }

    public function down(): void
    {
        Schema::table('attempts', function (Blueprint $table) {
            try {
                $table->dropIndex(['exam_id', 'email']);
            } catch (\Throwable $e) {
                //
            }
            try {
                $table->unique(['exam_id', 'email']);
            } catch (\Throwable $e) {
                //
            }
        });
    }
};
