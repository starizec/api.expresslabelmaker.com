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
        Schema::create('licenses', function (Blueprint $table) {
            $table->id();
            $table->foreignId('user_id');
            $table->foreignId('domain_id');
            $table->string("licence_id", 36)->unique();
            $table->float('amount', 8, 2);
            $table->foreignId('currency_id');
            $table->date('payed_at');
            $table->string("invoice_number", 100);
            $table->text('note')->nullable();
            $table->date('valid_from');
            $table->date('valid_until');
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('licences');
    }
};
