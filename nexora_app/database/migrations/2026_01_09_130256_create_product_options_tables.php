<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class CreateProductOptionsTables extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        // 1. Groups of options (e.g., "Cooking Level", "Sides", "Toppings")
        Schema::create('product_option_groups', function (Blueprint $table) {
            $table->id();
            $table->foreignId('tenant_id')->constrained('tenants')->cascadeOnDelete();
            $table->string('name'); // e.g., "Cuisson", "Accompagnements"
            $table->enum('type', ['radio', 'checkbox'])->default('radio'); // radio = single select, checkbox = multi select
            $table->boolean('is_required')->default(false);
            $table->integer('min_selection')->default(0); // For validation
            $table->integer('max_selection')->default(1); // For validation (1 for radio)
            $table->integer('sort_order')->default(0);
            $table->timestamps();
        });

        // 2. Individual options within a group (e.g., "Rare", "Medium", "Fries", "Rice")
        Schema::create('product_options', function (Blueprint $table) {
            $table->id();
            $table->foreignId('product_option_group_id')->constrained('product_option_groups')->cascadeOnDelete();
            $table->string('name'); // e.g., "Saignant", "Riz"
            $table->decimal('price_adjustment', 10, 2)->default(0); // e.g., +2.00 for premium side
            $table->integer('sort_order')->default(0);
            $table->timestamps();
        });

        // 3. Pivot table to attach groups to products
        Schema::create('product_product_option_group', function (Blueprint $table) {
            $table->id();
            $table->foreignId('product_id')->constrained('products')->cascadeOnDelete();
            $table->foreignId('product_option_group_id')->constrained('product_option_groups')->cascadeOnDelete();
            $table->timestamps();
            
            $table->unique(['product_id', 'product_option_group_id'], 'prod_opt_grp_unique');
        });

        // 4. Store selected options for a specific order item
        Schema::create('order_item_options', function (Blueprint $table) {
            $table->id();
            $table->foreignId('order_item_id')->constrained('order_items')->cascadeOnDelete();
            $table->foreignId('product_option_id')->nullable()->constrained('product_options')->nullOnDelete();
            $table->string('product_option_group_name'); // Snapshot of group name
            $table->string('product_option_name'); // Snapshot of option name
            $table->decimal('price', 10, 2)->default(0); // Price at the time of order
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::dropIfExists('order_item_options');
        Schema::dropIfExists('product_product_option_group');
        Schema::dropIfExists('product_options');
        Schema::dropIfExists('product_option_groups');
    }
}
