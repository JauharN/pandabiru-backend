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
       Schema::create('reports', function (Blueprint $table) {
            $table->id();
            $table->foreignId('user_id')->constrained()->onDelete('cascade');
            $table->string('context'); // attendance, availability, promo
            $table->string('client_uuid')->unique();
            $table->json('payload'); // data laporan
            $table->string('status')->default('pending'); // pending, synced
            $table->timestamp('synced_at')->nullable();
            $table->timestamps();

            $table->index(['context', 'created_at']);
            $table->index('user_id');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('reports');
    }
};
