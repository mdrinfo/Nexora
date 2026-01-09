<?php

declare(strict_types=1);

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration {
    public function up(): void
    {
        Schema::create('tenants', function (Blueprint $table) {
            $table->id();
            $table->string('name');
            $table->string('slug')->unique();
            $table->string('default_currency', 3);
            $table->timestamps();
            $table->comment('Tenants (multi-tenant organizations)');
        });

        Schema::create('settings', function (Blueprint $table) {
            $table->id();
            $table->foreignId('tenant_id')->constrained('tenants')->cascadeOnDelete();
            $table->string('brand_name');
            $table->string('logo_path')->nullable();
            $table->string('primary_color_hex', 7);
            $table->string('secondary_color_hex', 7)->nullable();
            $table->string('accent_color_hex', 7)->nullable();
            $table->string('currency', 3);
            $table->timestamps();
            $table->comment('Branding and configuration per tenant');
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('settings');
        Schema::dropIfExists('tenants');
    }
};

