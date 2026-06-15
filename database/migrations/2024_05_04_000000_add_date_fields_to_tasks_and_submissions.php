<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('tasks', function (Blueprint $table) {
            $table->date('task_date')->nullable()->after('description');
        });

        Schema::table('task_submissions', function (Blueprint $table) {
            $table->date('submission_date')->nullable()->after('description');
        });
    }

    public function down(): void
    {
        Schema::table('tasks', function (Blueprint $table) {
            $table->dropColumn('task_date');
        });

        Schema::table('task_submissions', function (Blueprint $table) {
            $table->dropColumn('submission_date');
        });
    }
};