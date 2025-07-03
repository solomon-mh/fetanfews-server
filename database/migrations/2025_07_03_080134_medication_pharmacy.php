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
    Schema::create('medication_pharmacy', function (Blueprint $table) {
        $table->id();
        $table->foreignId('medication_id')->constrained()->onDelete('cascade');
        $table->foreignId('pharmacy_id')->constrained()->onDelete('cascade');
        $table->decimal('price', 8, 2)->nullable();         // e.g., 5.00
        $table->integer('stock_quantity')->default(0);      // e.g., 100 units
        $table->boolean('stock_status')->default(true);
        $table->integer('quantity_available');
        $table->string('manufacturer');
        $table->timestamps();
    });

    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        //
    }
};
