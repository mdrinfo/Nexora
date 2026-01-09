<?php

declare(strict_types=1);

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration {
    public function up(): void
    {
        Schema::table('order_items', function (Blueprint $table) {
            $table->foreignId('waiter_id')->nullable()->constrained('users')->nullOnDelete();
            $table->text('special_notes')->nullable();
            $table->enum('status', ['pending', 'preparing', 'ready', 'served'])->default('pending');
            $table->timestamp('waiter_notified_at')->nullable();
            $table->index(['status']);
        });
    }

    public function down(): void
    {
        Schema::table('order_items', function (Blueprint $table) {
            $table->dropIndex(['status']);
            $table->dropColumn(['waiter_id', 'special_notes', 'status', 'waiter_notified_at']);
        });
    }
};

