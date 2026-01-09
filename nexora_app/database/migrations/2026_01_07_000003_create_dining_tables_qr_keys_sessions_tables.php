<?php

declare(strict_types=1);

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration {
    public function up(): void
    {
        Schema::create('dining_tables', function (Blueprint $table) {
            $table->id();
            $table->foreignId('tenant_id')->constrained('tenants')->cascadeOnDelete();
            $table->string('label');
            $table->unsignedInteger('capacity')->default(0);
            $table->string('location')->nullable();
            $table->timestamps();
            $table->unique(['tenant_id', 'label']);
        });

        Schema::create('qr_keys', function (Blueprint $table) {
            $table->id();
            $table->foreignId('tenant_id')->constrained('tenants')->cascadeOnDelete();
            $table->foreignId('dining_table_id')->nullable()->constrained('dining_tables')->nullOnDelete();
            $table->string('token')->unique();
            $table->enum('status', ['available', 'active'])->default('available');
            $table->timestamp('assigned_at')->nullable();
            $table->timestamps();
        });

        Schema::create('sessions', function (Blueprint $table) {
            $table->id();
            $table->foreignId('tenant_id')->constrained('tenants')->cascadeOnDelete();
            $table->foreignId('dining_table_id')->constrained('dining_tables')->cascadeOnDelete();
            $table->foreignId('qr_key_id')->constrained('qr_keys')->cascadeOnDelete();
            $table->enum('status', ['open', 'closed', 'cancelled'])->default('open');
            $table->string('currency', 3);
            $table->decimal('total_amount', 10, 2)->default(0);
            $table->timestamp('opened_at')->nullable();
            $table->timestamp('closed_at')->nullable();
            $table->timestamps();
            $table->index(['tenant_id', 'dining_table_id', 'status']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('sessions');
        Schema::dropIfExists('qr_keys');
        Schema::dropIfExists('dining_tables');
    }
};
