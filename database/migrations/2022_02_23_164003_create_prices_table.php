<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class CreatePricesTable extends Migration
{
    public function up(): void
    {
        Schema::create('magento_prices', function (Blueprint $table): void {
            $table->id();
            $table->string('sku');

            $table->boolean('sync')->default(true);

            $table->json('base_prices')->nullable();
            $table->json('tier_prices')->nullable();

            $table->boolean('retrieve')->default(false);
            $table->boolean('update')->default(false);

            $table->dateTime('last_retrieved')->nullable();
            $table->dateTime('last_updated')->nullable();

            $table->integer('fail_count')->default(0);
            $table->dateTime('last_failed')->nullable();

            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('magento_prices');
    }
}
