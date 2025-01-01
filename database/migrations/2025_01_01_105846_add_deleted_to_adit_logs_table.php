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
        Schema::table('adit_logs', function (Blueprint $table) {
            Schema::table('adit_logs', function (Blueprint $table) {
                $table->boolean('deleted')->default(false)->after('status'); // `status` の後に追加
            });
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('adit_logs', function (Blueprint $table) {
            $table->dropColumn('deleted');
        });
    }
};
