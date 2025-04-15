<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('delivery_locations', function (Blueprint $table) {
            $table->id();
            $table->string('country', 2)->index();
            $table->string('courier')->index();
            $table->string('location_id');
            $table->string('place');
            $table->string('postal_code');
            $table->string('street');
            $table->string('house_number')->nullable();
            $table->decimal('lon', 15, 8)->nullable();
            $table->decimal('lat', 15, 8)->nullable();
            $table->string('name')->nullable();
            $table->string('type')->nullable()->index();
            $table->text('description')->nullable();
            $table->string('phone')->nullable();
            $table->boolean('active')->default(true)->index();
            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('delivery_locations');
    }
};
