<?php

declare(strict_types=1);

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration {
    public function up(): void
    {
        // 1. Create suppliers table
        Schema::create('suppliers', function (Blueprint $table) {
            $table->id();
            $table->foreignId('tenant_id')->constrained('tenants')->cascadeOnDelete();
            $table->string('name');
            $table->string('contact_name')->nullable();
            $table->string('phone')->nullable();
            $table->string('email')->nullable();
            $table->timestamps();
        });

        // 2. Enhance inventory_items table
        Schema::table('inventory_items', function (Blueprint $table) {
            $table->foreignId('supplier_id')->nullable()->constrained('suppliers')->nullOnDelete();
            $table->string('category')->default('food')->index(); // food, drink, cleaning, etc.
            $table->decimal('cost_price', 10, 2)->default(0);
            $table->string('image_path')->nullable();
            $table->timestamp('last_audited_at')->nullable();
        });

        // 3. Create shopping_list_items table
        Schema::create('shopping_list_items', function (Blueprint $table) {
            $table->id();
            $table->foreignId('tenant_id')->constrained('tenants')->cascadeOnDelete();
            $table->foreignId('inventory_item_id')->constrained('inventory_items')->cascadeOnDelete();
            $table->decimal('quantity_needed', 18, 6)->default(0);
            $table->boolean('is_checked')->default(false); // For mobile checklist
            $table->boolean('is_manual')->default(false); // Distinguish auto vs manual
            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('shopping_list_items');
        
        Schema::table('inventory_items', function (Blueprint $table) {
            $table->dropForeign(['supplier_id']);
            $table->dropColumn(['supplier_id', 'category', 'cost_price', 'image_path', 'last_audited_at']);
        });

        Schema::dropIfExists('suppliers');
    }
};
