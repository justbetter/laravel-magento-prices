<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::dropColumns('magento_prices', ['in_magento']);
    }

    public function down(): void
    {
        Schema::table('magento_prices', function (Blueprint $table): void {
            $table->boolean('in_magento')->default(false)->after('sku');
        });
    }
};
