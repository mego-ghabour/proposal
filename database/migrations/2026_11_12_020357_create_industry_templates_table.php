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
        Schema::create('industry_templates', function (Blueprint $table) {
            $table->id();
            $table->string('name');
            $table->string('slug')->unique();
            $table->string('icon')->default('⚡');
            $table->string('badge')->nullable();
            $table->text('description')->nullable();
            $table->longText('raw_idea');
            $table->unsignedInteger('suggested_price_min')->default(40000);
            $table->unsignedInteger('suggested_price_max')->default(65000);
            $table->string('currency')->default('ريال');
            $table->string('estimated_weeks')->default('6 - 8 أسابيع');
            $table->json('target_platforms')->nullable();
            $table->json('suggested_modules')->nullable();
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('industry_templates');
    }
};
