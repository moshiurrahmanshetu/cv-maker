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
        // 1. Products Table
        if (!Schema::hasTable('products')) {
            Schema::create('products', function (Blueprint $table) {
                $table->id();
                $table->foreignId('cv_template_id')->nullable()->constrained('cv_templates')->onDelete('set null');
                $table->string('name');
                $table->string('slug')->unique();
                $table->text('description')->nullable();
                $table->decimal('price', 10, 2)->default(0.00);
                $table->string('currency', 3)->default('USD');
                $table->boolean('is_active')->default(true);
                $table->timestamps();

                $table->index(['cv_template_id', 'is_active']);
            });
        }

        // 2. Orders Table
        if (!Schema::hasTable('orders')) {
            Schema::create('orders', function (Blueprint $table) {
                $table->id();
                $table->string('order_number', 50)->unique();
                $table->foreignId('user_id')->constrained('users')->onDelete('cascade');
                $table->decimal('total_amount', 10, 2)->default(0.00);
                $table->string('currency', 3)->default('USD');
                $table->string('status', 30)->default('pending')->index(); // 'pending', 'paid', 'failed', 'cancelled', 'refunded'
                $table->string('payment_provider', 50)->default('mock'); // 'mock', 'stripe', 'paypal'
                $table->string('transaction_id', 255)->nullable()->index();
                $table->json('payment_details')->nullable();
                $table->timestamp('paid_at')->nullable();
                $table->timestamps();

                $table->index(['user_id', 'created_at']);
                $table->index(['user_id', 'status']);
            });
        }

        // 3. Order Items Table
        if (!Schema::hasTable('order_items')) {
            Schema::create('order_items', function (Blueprint $table) {
                $table->id();
                $table->foreignId('order_id')->constrained('orders')->onDelete('cascade');
                $table->foreignId('product_id')->nullable()->constrained('products')->onDelete('set null');
                $table->string('item_name');
                $table->decimal('price', 10, 2)->default(0.00);
                $table->unsignedSmallInteger('quantity')->default(1);
                $table->decimal('subtotal', 10, 2)->default(0.00);
                $table->timestamps();

                $table->index(['order_id', 'product_id']);
            });
        }

        // 4. User Entitlements Table (Grants & Access control)
        if (!Schema::hasTable('user_entitlements')) {
            Schema::create('user_entitlements', function (Blueprint $table) {
                $table->id();
                $table->foreignId('user_id')->constrained('users')->onDelete('cascade');
                $table->foreignId('product_id')->nullable()->constrained('products')->onDelete('set null');
                $table->foreignId('cv_template_id')->nullable()->constrained('cv_templates')->onDelete('set null');
                $table->foreignId('order_id')->nullable()->constrained('orders')->onDelete('set null');
                $table->string('entitlement_type', 50)->default('template_unlock'); // 'template_unlock', 'all_access'
                $table->string('status', 30)->default('active')->index(); // 'active', 'revoked', 'expired'
                $table->timestamp('granted_at')->useCurrent();
                $table->timestamp('expires_at')->nullable();
                $table->timestamps();

                $table->index(['user_id', 'cv_template_id', 'status'], 'user_template_entitlement_idx');
                $table->index(['user_id', 'status', 'expires_at'], 'user_active_entitlement_idx');
            });
        }
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('user_entitlements');
        Schema::dropIfExists('order_items');
        Schema::dropIfExists('orders');
        Schema::dropIfExists('products');
    }
};
