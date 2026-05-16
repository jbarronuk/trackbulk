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
        Schema::create('accounts', function (Blueprint $table) {
            $table->id();
            $table->integer('type')->default(0);
            $table->timestamps();
        });
        Schema::table('users', function (Blueprint $table) {
            $table->bigInteger('account_id')->unsigned()->after('remember_token');
            $table->integer('type')->default(2);

            $table->foreign('account_id')->references('id')->on('accounts')->onDelete('cascade');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('users', function (Blueprint $table) {
            $table->dropForeign(['account_id']);

            $table->dropColumn([
                'account_id',
                'type',
            ]);
        });
        Schema::dropIfExists('accounts');
    }
};
