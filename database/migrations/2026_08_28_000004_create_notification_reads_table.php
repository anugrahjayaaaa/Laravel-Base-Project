<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        // ponytail: read-state layer over the existing activity_log; no duplicated feed data
        Schema::create('notification_reads', function (Blueprint $table) {
            $table->id();
            $table->foreignId('user_id')->constrained()->cascadeOnDelete();
            $table->unsignedBigInteger('activity_id');
            $table->timestamp('read_at')->nullable();
            $table->unique(['user_id', 'activity_id']);
            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('notification_reads');
    }
};
