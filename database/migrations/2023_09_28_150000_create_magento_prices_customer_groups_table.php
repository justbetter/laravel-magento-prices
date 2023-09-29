<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('magento_prices_customer_groups', function (Blueprint $table): void {
            $table->id();
            $table->string('code')->index();
            $table->json('data');
            $table->timestamp('imported_at')->nullable();
            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('magento_prices_groups');
    }
};
