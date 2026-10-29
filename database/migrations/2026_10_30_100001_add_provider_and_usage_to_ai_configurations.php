<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

/**
 * A configuration now names its provider (Claude, OpenAI, Gemini, Groq, ...)
 * and keeps a running token tally, so a company can see what each key is
 * actually costing it. Existing configurations are all Claude keys.
 */
return new class extends Migration
{
    public function up(): void
    {
        Schema::table('ai_configurations', function (Blueprint $table) {
            $table->string('provider', 30)->default('anthropic')->after('name');
            $table->unsignedBigInteger('requests_count')->default(0);
            $table->unsignedBigInteger('input_tokens')->default(0);
            $table->unsignedBigInteger('output_tokens')->default(0);
        });
    }

    public function down(): void
    {
        Schema::table('ai_configurations', function (Blueprint $table) {
            $table->dropColumn(['provider', 'requests_count', 'input_tokens', 'output_tokens']);
        });
    }
};
