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
        Schema::create('orders', function (Blueprint $table) {
            $table->id();
            $table->uuid();
            $table->foreignId('user_id')->constrained('users')->onDelete('cascade')->onUpdate('cascade');
            $table->foreignId('order_status_id')->constrained('order_statuses')->onDelete('cascade')->onUpdate('cascade');
            $table->string('payment_id')->nullable(); # placeholder only for payment. real table should be foreignId to payment table. wont be use in this task
            $table->json('products')->nullable();
            $table->json('address')->nullable();
            $table->double('delivery_fee', 8, 2)->nullable();
            $table->double('amount', 12, 2);
            $table->timestamps();
            $table->timestamp('shipped_at')->nullable();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('orders');
    }
};
