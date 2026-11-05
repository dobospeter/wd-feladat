<?php

declare(strict_types=1);

use Illuminate\Support\Facades\Schema;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('api_logs', function (Blueprint $table): void {
            $table->id();
            $table->string('request_id', 64)->nullable()->index();
            $table->string('method', 10);
            $table->string('path');
            $table->text('uri');
            $table->string('ip', 45)->nullable();
            $table->string('user_agent', 512)->nullable();
            $table->json('request_headers');
            $table->mediumText('request_body')->nullable();
            $table->unsignedSmallInteger('response_status')->index();
            $table->json('response_headers');
            $table->mediumText('response_body')->nullable();
            $table->unsignedInteger('duration_ms');
            $table->timestamp('created_at', 6)->useCurrent()->index();

            $table->index(['path', 'method']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('api_logs');
    }
};
