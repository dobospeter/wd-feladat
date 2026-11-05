<?php

declare(strict_types=1);

use Illuminate\Support\Facades\Schema;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

return new class() extends Migration
{
    public function up(): void
    {
        Schema::create('parties', function (Blueprint $table): void {
            $table->uuid('id')->primary();
            $table->string('name');
            $table->string('tax_number', 32)->nullable()->unique();
            $table->string('address', 500)->nullable();
            $table->string('email')->nullable();
            $table->string('phone', 50)->nullable();
            $table->timestamps();

            $table->index('name');
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('parties');
    }
};
