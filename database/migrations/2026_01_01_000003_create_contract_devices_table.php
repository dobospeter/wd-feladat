<?php

declare(strict_types=1);

use Illuminate\Support\Facades\Schema;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('contract_devices', function (Blueprint $table): void {
            $table->uuid('id')->primary();
            $table->foreignUuid('contract_id')->constrained('contracts')->cascadeOnDelete();
            $table->foreignUuid('device_id')->constrained('devices')->restrictOnDelete();
            $table->string('serial_number', 100);
            $table->timestamps();

            // A physical unit (device + serial number) can be on one contract only.
            $table->unique(['device_id', 'serial_number'], 'contract_devices_device_serial_unique');
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('contract_devices');
    }
};
