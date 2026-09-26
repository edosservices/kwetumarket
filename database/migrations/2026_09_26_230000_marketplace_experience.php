<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('hero_slides', function (Blueprint $table) {
            $table->id();
            $table->string('placement')->index();
            $table->string('title');
            $table->string('subtitle')->nullable();
            $table->string('cta_label')->nullable();
            $table->string('cta_url')->nullable();
            $table->string('image')->nullable();
            $table->unsignedSmallInteger('sort_order')->default(0);
            $table->timestamp('starts_at')->nullable();
            $table->timestamp('ends_at')->nullable();
            $table->boolean('is_active')->default(true);
            $table->timestamps();
        });

        Schema::create('exchange_rates', function (Blueprint $table) {
            $table->id();
            $table->char('base', 3);
            $table->char('quote', 3);
            $table->unsignedBigInteger('minor_per_unit');
            $table->timestamp('quoted_at');
            $table->timestamps();

            $table->index(['base', 'quote', 'quoted_at']);
        });

        Schema::create('platform_settings', function (Blueprint $table) {
            $table->string('key')->primary();
            $table->string('value');
            $table->timestamps();
        });

        Schema::create('points_wallets', function (Blueprint $table) {
            $table->id();
            $table->foreignId('user_id')->unique()->constrained()->cascadeOnDelete();
            $table->unsignedInteger('balance')->default(0);
            $table->timestamps();
        });

        Schema::create('points_transactions', function (Blueprint $table) {
            $table->id();
            $table->foreignId('points_wallet_id')->constrained()->cascadeOnDelete();
            $table->integer('points');
            $table->string('type');
            $table->string('note')->nullable();
            $table->timestamps();

            $table->index(['points_wallet_id', 'created_at']);
        });

        Schema::create('referral_rewards', function (Blueprint $table) {
            $table->id();
            $table->foreignId('referral_id')->unique()->constrained()->cascadeOnDelete();
            $table->foreignId('user_id')->constrained()->cascadeOnDelete();
            $table->foreignId('order_id')->nullable()->constrained()->nullOnDelete();
            $table->unsignedInteger('points');
            $table->timestamp('reversed_at')->nullable();
            $table->timestamps();
        });

        Schema::create('analytics_events', function (Blueprint $table) {
            $table->id();
            $table->string('name')->index();
            $table->foreignId('user_id')->nullable()->constrained()->nullOnDelete();
            $table->nullableMorphs('subject');
            $table->timestamps();
        });

        Schema::create('review_photos', function (Blueprint $table) {
            $table->id();
            $table->foreignId('review_id')->constrained()->cascadeOnDelete();
            $table->string('disk');
            $table->string('path');
            $table->timestamps();
        });

        Schema::table('product_variants', function (Blueprint $table) {
            $table->string('color_name')->nullable()->after('name');
            $table->string('color_hex', 7)->nullable()->after('color_name');
            $table->string('size')->nullable()->after('color_hex');
            $table->unsignedBigInteger('promotional_price')->nullable()->after('price');
        });

        Schema::table('product_images', function (Blueprint $table) {
            $table->string('original_path')->nullable()->after('path');
            $table->string('thumb_path')->nullable()->after('original_path');
        });

        Schema::table('products', function (Blueprint $table) {
            $table->string('supplier_sku')->nullable()->after('supplier_name');
            $table->unsignedBigInteger('supplier_price')->nullable()->after('supplier_sku');
        });

        Schema::table('shops', function (Blueprint $table) {
            $table->string('timezone')->default('Africa/Kinshasa')->after('opening_hours');
            $table->json('weekly_hours')->nullable()->after('timezone');
        });

        Schema::table('carts', function (Blueprint $table) {
            $table->timestamp('abandoned_reminded_at')->nullable()->after('coupon_code');
        });

        Schema::table('referrals', function (Blueprint $table) {
            $table->timestamp('blocked_at')->nullable()->after('rewarded_at');
        });

        Schema::table('subscription_plans', function (Blueprint $table) {
            $table->unsignedInteger('price_usd_cents')->nullable()->after('price');
        });

        Schema::table('vendor_subscriptions', function (Blueprint $table) {
            $table->unsignedInteger('amount_minor')->nullable()->after('payment_reference');
            $table->unsignedInteger('points_spent')->default(0)->after('amount_minor');
            $table->unsignedBigInteger('rate_minor_per_unit')->nullable()->after('points_spent');
            $table->timestamp('rate_quoted_at')->nullable()->after('rate_minor_per_unit');
        });
    }

    public function down(): void
    {
        Schema::table('vendor_subscriptions', function (Blueprint $table) {
            $table->dropColumn(['amount_minor', 'points_spent', 'rate_minor_per_unit', 'rate_quoted_at']);
        });
        Schema::table('subscription_plans', function (Blueprint $table) {
            $table->dropColumn('price_usd_cents');
        });
        Schema::table('referrals', function (Blueprint $table) {
            $table->dropColumn('blocked_at');
        });
        Schema::table('carts', function (Blueprint $table) {
            $table->dropColumn('abandoned_reminded_at');
        });
        Schema::table('shops', function (Blueprint $table) {
            $table->dropColumn(['timezone', 'weekly_hours']);
        });
        Schema::table('products', function (Blueprint $table) {
            $table->dropColumn(['supplier_sku', 'supplier_price']);
        });
        Schema::table('product_images', function (Blueprint $table) {
            $table->dropColumn(['original_path', 'thumb_path']);
        });
        Schema::table('product_variants', function (Blueprint $table) {
            $table->dropColumn(['color_name', 'color_hex', 'size', 'promotional_price']);
        });

        Schema::dropIfExists('review_photos');
        Schema::dropIfExists('analytics_events');
        Schema::dropIfExists('referral_rewards');
        Schema::dropIfExists('points_transactions');
        Schema::dropIfExists('points_wallets');
        Schema::dropIfExists('platform_settings');
        Schema::dropIfExists('exchange_rates');
        Schema::dropIfExists('hero_slides');
    }
};
