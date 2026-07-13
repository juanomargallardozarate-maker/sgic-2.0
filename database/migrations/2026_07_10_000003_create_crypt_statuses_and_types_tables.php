<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('crypt_statuses', function (Blueprint $table) {
            $table->id();
            $table->string('code', 30)->unique(); // available, occupied, reserved, maintenance, decaying, blocked_debt
            $table->string('name', 50);
            $table->string('color', 7); // Hex color (#00FF00)
            $table->string('icon', 30)->nullable();
            $table->boolean('is_available_for_sale')->default(false);
            $table->boolean('is_operational')->default(true);
            $table->integer('order')->default(0);
            $table->timestamps();
        });

        Schema::create('crypt_types', function (Blueprint $table) {
            $table->id();
            $table->string('code', 30)->unique(); // crypt, niche, mausoleum, ossuary
            $table->string('name', 50);
            $table->text('description')->nullable();
            $table->integer('default_capacity')->default(1);
            $table->integer('max_capacity')->default(6);
            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('crypt_types');
        Schema::dropIfExists('crypt_statuses');
    }
};