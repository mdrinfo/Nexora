<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        if (!Schema::hasTable('floors')) {
            Schema::create('floors', function (Blueprint $table) {
                $table->id();
                $table->foreignId('tenant_id')->constrained('tenants')->cascadeOnDelete();
                $table->string('name');
                $table->string('image_path')->nullable();
                $table->integer('level')->default(1);
                $table->timestamps();
            });
        }

        if (Schema::hasTable('dining_tables')) {
            Schema::table('dining_tables', function (Blueprint $table) {
                if (!Schema::hasColumn('dining_tables', 'floor_id')) {
                    $table->foreignId('floor_id')->nullable()->constrained('floors')->nullOnDelete();
                }
                if (!Schema::hasColumn('dining_tables', 'shape')) {
                    $table->string('shape')->default('square'); // square, round, rectangle
                }
                if (!Schema::hasColumn('dining_tables', 'x_position')) {
                    $table->float('x_position')->default(0);
                }
                if (!Schema::hasColumn('dining_tables', 'y_position')) {
                    $table->float('y_position')->default(0);
                }
                if (!Schema::hasColumn('dining_tables', 'width')) {
                    $table->float('width')->default(80);
                }
                if (!Schema::hasColumn('dining_tables', 'height')) {
                    $table->float('height')->default(80);
                }
                if (!Schema::hasColumn('dining_tables', 'rotation')) {
                    $table->float('rotation')->default(0);
                }
            });
        }

        if (Schema::hasTable('reservations')) {
            Schema::table('reservations', function (Blueprint $table) {
                if (!Schema::hasColumn('reservations', 'dining_table_id')) {
                    $table->foreignId('dining_table_id')->nullable()->constrained('dining_tables')->nullOnDelete();
                }
                // We will use existing event_date and start_time
            });
        } else {
            Schema::create('reservations', function (Blueprint $table) {
                $table->id();
                $table->foreignId('tenant_id')->constrained('tenants')->cascadeOnDelete();
                $table->foreignId('dining_table_id')->nullable()->constrained('dining_tables')->nullOnDelete();
                $table->string('customer_name');
                $table->string('customer_phone')->nullable();
                $table->date('event_date');
                $table->time('start_time')->nullable();
                $table->time('end_time')->nullable();
                $table->unsignedInteger('guest_count')->default(2);
                $table->string('status')->default('pending'); // simplified for sqlite
                $table->text('notes')->nullable();
                $table->timestamps();
            });
        }
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        // Careful with down as we are modifying existing tables
        if (Schema::hasColumn('reservations', 'dining_table_id')) {
            Schema::table('reservations', function (Blueprint $table) {
                $table->dropForeign(['dining_table_id']);
                $table->dropColumn('dining_table_id');
            });
        }

        if (Schema::hasColumn('dining_tables', 'floor_id')) {
            Schema::table('dining_tables', function (Blueprint $table) {
                $table->dropForeign(['floor_id']);
                $table->dropColumn(['floor_id', 'shape', 'x_position', 'y_position', 'width', 'height', 'rotation']);
            });
        }

        Schema::dropIfExists('floors');
    }
};
