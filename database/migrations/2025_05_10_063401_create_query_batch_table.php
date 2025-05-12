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
        Schema::create('tracking_batch', function (Blueprint $table) {
            $table->id();
            $table->bigInteger('account_id')->unsigned();
            $table->foreign('account_id')->references('id')->on('accounts')->onDelete('cascade');

            $table->timestamps();
        });
        Schema::table('tracking', function (Blueprint $table) {
            $table->bigInteger('tracking_batch_id')->unsigned()->nullable();
        
            $table->foreign('tracking_batch_id')->references('id')->on('tracking_batch');
        });
        
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('tracking', function (Blueprint $table) {
            $table->dropForeign(['tracking_batch_id']);
            
            $table->dropColumn([
                'tracking_batch_id',
            ]);
        });

        Schema::dropIfExists('tracking_batch');
    }
};
