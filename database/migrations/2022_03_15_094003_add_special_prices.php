<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('magento_prices', function (Blueprint $table): void {
            $table->json('special_prices')->nullable()->after('tier_prices');
        });
    }

    public function down(): void
    {
        Schema::table('magento_prices', function (Blueprint $table): void {
            $table->dropColumn('special_prices');
        });
    }
};
