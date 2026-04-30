<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('magento_prices', function (Blueprint $table): void {
            $table->boolean('has_tier')->default(false)->after('has_special');
        });
    }

    public function down(): void
    {
        Schema::table('magento_prices', function (Blueprint $table): void {
            $table->dropColumn('has_tier');
        });
    }
};
