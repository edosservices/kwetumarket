<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('shops', function (Blueprint $table) {
            $table->string('country', 80)->nullable()->after('location');
            $table->string('province', 80)->nullable()->after('country');
            $table->string('city', 80)->nullable()->after('province');
            $table->string('commune', 80)->nullable()->after('city');
            $table->string('quarter', 80)->nullable()->after('commune');
            $table->string('address_line', 180)->nullable()->after('quarter');
            $table->decimal('latitude', 10, 7)->nullable()->after('address_line');
            $table->decimal('longitude', 10, 7)->nullable()->after('latitude');
            $table->text('opening_hours')->nullable()->after('longitude');
            $table->boolean('publish_location')->default(true)->after('opening_hours');
            $table->boolean('publish_address')->default(true)->after('publish_location');
            $table->index(['latitude', 'longitude']);
        });

        Schema::table('users', function (Blueprint $table) {
            $table->string('first_name', 80)->nullable()->after('name');
            $table->string('last_name', 80)->nullable()->after('first_name');
            $table->string('whatsapp', 20)->nullable()->after('phone');
            $table->string('country', 80)->nullable()->after('currency');
            $table->string('province', 80)->nullable()->after('country');
            $table->string('city', 80)->nullable()->after('province');
            $table->string('commune', 80)->nullable()->after('city');
            $table->string('quarter', 80)->nullable()->after('commune');
            $table->string('address', 180)->nullable()->after('quarter');
            $table->string('avatar')->nullable()->after('address');
        });

        Schema::table('vendors', function (Blueprint $table) {
            $table->string('business_name', 140)->nullable()->after('user_id');
            $table->string('manager_name', 140)->nullable()->after('business_name');
        });

        Schema::create('vendor_social_links', function (Blueprint $table) {
            $table->id();
            $table->foreignId('vendor_id')->constrained()->cascadeOnDelete();
            $table->string('platform', 32);
            $table->string('username', 160)->nullable();
            $table->string('url', 255)->nullable();
            $table->timestamps();

            $table->unique(['vendor_id', 'platform']);
        });

        Schema::create('promotions', function (Blueprint $table) {
            $table->id();
            $table->foreignId('product_id')->constrained()->cascadeOnDelete();
            $table->unsignedBigInteger('promotional_price');
            $table->timestamp('starts_at')->nullable();
            $table->timestamp('ends_at')->nullable();
            $table->boolean('is_active')->default(true);
            $table->timestamps();

            $table->index(['is_active', 'ends_at']);
        });

        Schema::create('image_searches', function (Blueprint $table) {
            $table->id();
            $table->uuid('uuid')->unique();
            $table->foreignId('user_id')->nullable()->constrained()->nullOnDelete();
            $table->string('disk', 32);
            $table->string('image_path');
            $table->string('provider', 32);
            $table->boolean('limited')->default(true);
            $table->string('label')->nullable();
            $table->string('detected_category')->nullable();
            $table->string('detected_brand')->nullable();
            $table->text('detected_text')->nullable();
            $table->json('detected_attributes')->nullable();
            $table->decimal('confidence', 4, 3)->nullable();
            $table->timestamp('expires_at')->index();
            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('image_searches');
        Schema::dropIfExists('promotions');
        Schema::dropIfExists('vendor_social_links');

        Schema::table('vendors', function (Blueprint $table) {
            $table->dropColumn(['business_name', 'manager_name']);
        });

        Schema::table('users', function (Blueprint $table) {
            $table->dropColumn([
                'first_name', 'last_name', 'whatsapp', 'country', 'province',
                'city', 'commune', 'quarter', 'address', 'avatar',
            ]);
        });

        Schema::table('shops', function (Blueprint $table) {
            $table->dropIndex(['latitude', 'longitude']);
            $table->dropColumn([
                'country', 'province', 'city', 'commune', 'quarter', 'address_line',
                'latitude', 'longitude', 'opening_hours', 'publish_location', 'publish_address',
            ]);
        });
    }
};
