<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('magento_prices', function (Blueprint $table): void {
            $table->boolean('has_special')->default(false)->after('special_prices');
            $table->boolean('has_tier')->default(false)->after('tier_prices');
            $table->boolean('in_magento')->default(false)->after('sku');
        });
    }

    public function down(): void
    {
        Schema::table('magento_prices', function (Blueprint $table): void {
            $table->dropColumn('has_special');
            $table->dropColumn('has_tier');
            $table->dropColumn('in_magento');
        });
    }
};
