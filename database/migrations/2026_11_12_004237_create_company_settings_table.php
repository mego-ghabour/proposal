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
        Schema::create('company_settings', function (Blueprint $table) {
            $table->id();
            $table->string('agency_name')->default('وكالة فكرتك الرقمية - Fkretk Agency');
            $table->string('agency_tagline')->default('شريكك التقني لتطوير المنظومات والتطبيقات المبتكرة');
            $table->string('logo_url')->default('/assets/logo_fkretk.png');
            $table->string('icon_url')->default('/assets/fkretk_icon_512.png');
            $table->string('phone')->nullable()->default('+966500000000');
            $table->string('whatsapp')->nullable()->default('+966500000000');
            $table->string('email')->nullable()->default('info@fkretk.com');
            $table->string('website')->nullable()->default('https://fkretk.com');
            $table->string('address')->nullable()->default('الرياض - القاهرة');
            $table->json('social_links')->nullable();
            $table->json('default_terms')->nullable();
            $table->json('default_guarantees')->nullable();
            $table->text('gemini_api_key')->nullable();
            $table->string('gemini_model')->default('gemini-2.0-flash');
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('company_settings');
    }
};
