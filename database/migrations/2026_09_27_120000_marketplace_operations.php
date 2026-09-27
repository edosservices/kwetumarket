<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('addresses', function (Blueprint $table) {
            $table->decimal('latitude', 10, 7)->nullable()->after('address');
            $table->decimal('longitude', 10, 7)->nullable()->after('latitude');
        });

        Schema::table('orders', function (Blueprint $table) {
            $table->boolean('stock_committed')->default(true)->after('wallet_credited');
        });

        Schema::table('products', function (Blueprint $table) {
            $table->text('short_description')->nullable()->after('description');
            $table->foreignId('subcategory_id')->nullable()->after('category_id')->constrained('categories')->nullOnDelete();
            $table->string('dimensions', 40)->nullable()->after('weight');
            $table->boolean('stock_sync')->default(false)->after('supplier_price');
            $table->foreignId('supplier_offer_id')->nullable()->after('stock_sync');
        });

        Schema::table('product_variants', function (Blueprint $table) {
            $table->unsignedInteger('weight')->nullable()->after('size');
            $table->string('material', 80)->nullable()->after('weight');
            $table->string('model', 80)->nullable()->after('material');
            $table->string('capacity', 40)->nullable()->after('model');
            $table->string('version', 40)->nullable()->after('capacity');
        });

        Schema::table('reviews', function (Blueprint $table) {
            $table->text('vendor_reply')->nullable()->after('body');
            $table->timestamp('vendor_replied_at')->nullable()->after('vendor_reply');
        });

        Schema::table('messages', function (Blueprint $table) {
            $table->string('attachment_disk')->nullable()->after('body');
            $table->string('attachment_path')->nullable()->after('attachment_disk');
        });

        Schema::table('refunds', function (Blueprint $table) {
            $table->foreignId('payment_id')->nullable()->after('order_id')->constrained()->nullOnDelete();
        });

        Schema::table('points_transactions', function (Blueprint $table) {
            $table->unsignedInteger('remaining')->nullable()->after('points');
            $table->timestamp('expires_at')->nullable()->after('note');
        });

        DB::table('points_transactions')->where('points', '>', 0)->update([
            'remaining' => DB::raw('points'),
        ]);

        Schema::create('price_changes', function (Blueprint $table) {
            $table->id();
            $table->foreignId('product_id')->constrained()->cascadeOnDelete();
            $table->foreignId('product_variant_id')->nullable()->constrained()->cascadeOnDelete();
            $table->foreignId('user_id')->nullable()->constrained()->nullOnDelete();
            $table->string('field', 40);
            $table->unsignedBigInteger('amount_before')->nullable();
            $table->unsignedBigInteger('amount_after')->nullable();
            $table->string('currency', 3);
            $table->timestamp('created_at')->useCurrent();

            $table->index(['product_id', 'created_at']);
        });

        Schema::create('supplier_offers', function (Blueprint $table) {
            $table->id();
            $table->foreignId('vendor_id')->constrained()->cascadeOnDelete();
            $table->string('name');
            $table->string('sku')->unique();
            $table->text('description');
            $table->unsignedBigInteger('price');
            $table->string('currency', 3)->default('CDF');
            $table->unsignedInteger('stock')->default(0);
            $table->unsignedInteger('lead_days')->default(0);
            $table->unsignedInteger('moq')->default(1);
            $table->boolean('is_active')->default(true);
            $table->string('image_disk')->nullable();
            $table->string('image_path')->nullable();
            $table->timestamps();
        });

        Schema::table('products', function (Blueprint $table) {
            $table->foreign('supplier_offer_id')->references('id')->on('supplier_offers')->nullOnDelete();
        });

        Schema::create('return_requests', function (Blueprint $table) {
            $table->id();
            $table->foreignId('order_id')->constrained()->cascadeOnDelete();
            $table->foreignId('user_id')->constrained()->cascadeOnDelete();
            $table->foreignId('payment_id')->nullable()->constrained()->nullOnDelete();
            $table->text('reason');
            $table->string('status')->index();
            $table->string('evidence_disk')->nullable();
            $table->string('evidence_path')->nullable();
            $table->text('vendor_note')->nullable();
            $table->text('admin_note')->nullable();
            $table->timestamps();
        });

        Schema::create('courier_profiles', function (Blueprint $table) {
            $table->id();
            $table->foreignId('user_id')->unique()->constrained()->cascadeOnDelete();
            $table->string('vehicle_type', 40)->nullable();
            $table->string('vehicle_plate', 32)->nullable();
            $table->string('availability', 32)->default('offline')->index();
            $table->string('document_disk')->nullable();
            $table->string('document_path')->nullable();
            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('courier_profiles');
        Schema::dropIfExists('return_requests');
        Schema::table('products', function (Blueprint $table) {
            $table->dropForeign(['supplier_offer_id']);
        });
        Schema::dropIfExists('supplier_offers');
        Schema::dropIfExists('price_changes');
        Schema::table('points_transactions', function (Blueprint $table) {
            $table->dropColumn(['remaining', 'expires_at']);
        });
        Schema::table('refunds', function (Blueprint $table) {
            $table->dropConstrainedForeignId('payment_id');
        });
        Schema::table('messages', function (Blueprint $table) {
            $table->dropColumn(['attachment_disk', 'attachment_path']);
        });
        Schema::table('reviews', function (Blueprint $table) {
            $table->dropColumn(['vendor_reply', 'vendor_replied_at']);
        });
        Schema::table('product_variants', function (Blueprint $table) {
            $table->dropColumn(['weight', 'material', 'model', 'capacity', 'version']);
        });
        Schema::table('products', function (Blueprint $table) {
            $table->dropConstrainedForeignId('subcategory_id');
            $table->dropColumn(['short_description', 'dimensions', 'stock_sync', 'supplier_offer_id']);
        });
        Schema::table('orders', function (Blueprint $table) {
            $table->dropColumn('stock_committed');
        });
        Schema::table('addresses', function (Blueprint $table) {
            $table->dropColumn(['latitude', 'longitude']);
        });
    }
};
