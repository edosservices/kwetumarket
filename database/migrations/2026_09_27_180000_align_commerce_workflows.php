<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('orders', function (Blueprint $table) {
            $table->string('payment_status', 20)->default('unpaid')->after('status');
            $table->string('payment_method', 20)->nullable()->after('payment_status');
        });

        Schema::table('deliveries', function (Blueprint $table) {
            $table->timestamp('eta_at')->nullable()->after('status');
            $table->text('notes')->nullable()->after('proof_note');
        });

        Schema::create('courier_profiles', function (Blueprint $table) {
            $table->id();
            $table->foreignId('user_id')->unique()->constrained()->cascadeOnDelete();
            $table->string('vehicle_type', 40)->nullable();
            $table->string('vehicle_plate', 32)->nullable();
            $table->string('availability', 20)->default('offline');
            $table->string('document_disk')->nullable();
            $table->string('document_path')->nullable();
            $table->timestamps();
        });

        Schema::create('return_requests', function (Blueprint $table) {
            $table->id();
            $table->foreignId('order_id')->constrained()->cascadeOnDelete();
            $table->foreignId('user_id')->constrained()->cascadeOnDelete();
            $table->foreignId('payment_id')->nullable()->constrained()->nullOnDelete();
            $table->text('reason');
            $table->string('status', 30)->default('requested')->index();
            $table->string('evidence_disk')->nullable();
            $table->string('evidence_path')->nullable();
            $table->text('vendor_note')->nullable();
            $table->text('admin_note')->nullable();
            $table->timestamps();
        });

        Schema::table('refunds', function (Blueprint $table) {
            $table->foreignId('return_request_id')->nullable()->after('order_id')->constrained()->nullOnDelete();
        });

        Schema::table('reviews', function (Blueprint $table) {
            $table->foreignId('order_id')->nullable()->after('product_id')->constrained()->nullOnDelete();
            $table->string('status', 20)->default('published')->after('body');
            $table->text('vendor_reply')->nullable()->after('status');
            $table->timestamp('vendor_replied_at')->nullable()->after('vendor_reply');
            $table->index('status');
        });

        Schema::create('review_reports', function (Blueprint $table) {
            $table->id();
            $table->foreignId('review_id')->constrained()->cascadeOnDelete();
            $table->foreignId('user_id')->constrained()->cascadeOnDelete();
            $table->string('reason')->nullable();
            $table->timestamps();
            $table->unique(['review_id', 'user_id']);
        });

        DB::table('orders')->where('status', 'pending')->update(['status' => 'confirmed']);
        DB::table('orders')->where('status', 'paid')->update([
            'status' => 'confirmed',
            'payment_status' => 'paid',
        ]);
        DB::table('orders')->where('status', 'processing')->update(['status' => 'preparing']);

        DB::table('deliveries')->where('status', 'picked_up')->update(['status' => 'departed']);
        DB::table('deliveries')->where('status', 'in_transit')->update(['status' => 'en_route']);
        DB::table('deliveries')->whereIn('status', ['declined', 'failed'])->update(['status' => 'cancelled']);
    }

    public function down(): void
    {
        Schema::dropIfExists('review_reports');

        Schema::table('reviews', function (Blueprint $table) {
            $table->dropConstrainedForeignId('order_id');
            $table->dropColumn(['status', 'vendor_reply', 'vendor_replied_at']);
        });

        Schema::table('refunds', function (Blueprint $table) {
            $table->dropConstrainedForeignId('return_request_id');
        });

        Schema::dropIfExists('return_requests');
        Schema::dropIfExists('courier_profiles');

        Schema::table('deliveries', function (Blueprint $table) {
            $table->dropColumn(['eta_at', 'notes']);
        });

        Schema::table('orders', function (Blueprint $table) {
            $table->dropColumn(['payment_status', 'payment_method']);
        });
    }
};
