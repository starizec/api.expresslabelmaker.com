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
        Schema::create('licences', function (Blueprint $table) {
            $table->id();
            $table->foreignId('user_id');
            $table->foreignId('domain_id');
            $table->string("licence_uid", 36)->unique();
            $table->date('valid_from');
            $table->date('valid_until')->nullable()->default(null);
            $table->integer('usage')->default(0);
            $table->integer('usage_limit');
            $table->foreignId('licence_type_id');
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
