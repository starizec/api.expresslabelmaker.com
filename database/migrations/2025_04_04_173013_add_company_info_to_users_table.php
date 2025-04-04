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
        Schema::table('users', function (Blueprint $table) {
            $table->string('first_name')->nullable()->after('name');
            $table->string('last_name')->nullable()->after('first_name');
            $table->string('company_name')->nullable()->after('email');
            $table->string('company_address')->nullable()->after('company_name');
            $table->string('town')->nullable()->after('company_address');
            $table->string('country', 2)->nullable()->after('town');
            $table->string('vat_number')->nullable()->after('country');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('users', function (Blueprint $table) {
            $table->dropColumn([
                'first_name',
                'last_name',
                'company_name',
                'company_address',
                'town',
                'country',
                'vat_number'
            ]);
        });
    }
};
