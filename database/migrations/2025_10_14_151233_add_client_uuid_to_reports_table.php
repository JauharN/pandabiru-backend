<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('reports', function (Blueprint $table) {
            if (!Schema::hasColumn('reports', 'client_uuid')) {
                $table->string('client_uuid')->unique()->after('context');
            }
            if (!Schema::hasColumn('reports', 'status')) {
                $table->string('status')->default('pending')->after('payload');
            }
            if (!Schema::hasColumn('reports', 'synced_at')) {
                $table->timestamp('synced_at')->nullable()->after('status');
            }
        });
    }

    public function down(): void
    {
        Schema::table('reports', function (Blueprint $table) {
            $table->dropColumn(['client_uuid', 'status', 'synced_at']);
        });
    }
};
