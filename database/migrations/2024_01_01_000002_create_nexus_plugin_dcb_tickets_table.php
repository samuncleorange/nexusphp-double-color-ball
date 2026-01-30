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
        Schema::create('nexus_plugin_dcb_tickets', function (Blueprint $table) {
            $table->id();
            $table->unsignedBigInteger('period_id')->comment('Period ID');
            $table->unsignedBigInteger('user_id')->comment('User ID');
            $table->json('red_balls')->comment('Selected red balls');
            $table->json('blue_balls')->comment('Selected blue balls');
            $table->decimal('cost', 20, 2)->comment('Ticket cost in magic points');
            $table->tinyInteger('win_level')->default(0)->comment('Winning level (0=no win)');
            $table->decimal('win_bonus', 20, 2)->default(0)->comment('Prize amount');
            $table->timestamps();

            // Foreign keys
            $table->foreign('period_id')
                ->references('id')
                ->on('nexus_plugin_dcb_periods')
                ->onDelete('cascade');

            $table->foreign('user_id')
                ->references('id')
                ->on('users')
                ->onDelete('cascade');

            // Indexes
            $table->index('period_id');
            $table->index('user_id');
            $table->index('win_level');
            $table->index('created_at');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('nexus_plugin_dcb_tickets');
    }
};
