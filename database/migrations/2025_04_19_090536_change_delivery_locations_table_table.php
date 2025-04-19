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
        Schema::table('delivery_locations', function (Blueprint $table) {
            $table->dropColumn(['country', 'courier']);
            $table->foreignId('header_id')->constrained('delivery_location_headers');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('delivery_locations', function (Blueprint $table) {
            $table->string('country', 2);
            $table->string('courier', 50);
            $table->dropForeign(['header_id']);
            $table->dropColumn('header_id');
        });
    }
};
