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
        Schema::create('nexus_plugin_dcb_periods', function (Blueprint $table) {
            $table->id();
            $table->string('period_code', 20)->unique()->comment('Period code (YYYYMMDDNN)');
            $table->tinyInteger('status')->default(0)->index()->comment('0=Open, 1=Closed, 2=Drawn');
            $table->json('red_balls')->nullable()->comment('Winning red balls');
            $table->json('blue_balls')->nullable()->comment('Winning blue balls');
            $table->string('block_hash', 128)->nullable()->comment('Bitcoin block hash for provably fair');
            $table->bigInteger('block_height')->nullable()->comment('Bitcoin block height');
            $table->decimal('prize_pool', 20, 2)->default(0)->comment('Total prize pool');
            $table->json('win_details')->nullable()->comment('Winning statistics by level');
            $table->timestamp('opened_at')->nullable()->comment('Draw execution time');
            $table->timestamps();

            // Indexes
            $table->index('created_at');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('nexus_plugin_dcb_periods');
    }
};
