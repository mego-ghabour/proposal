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
        Schema::create('proposals', function (Blueprint $table) {
            $table->id();
            $table->uuid('uuid')->unique();
            $table->string('slug')->unique();
            $table->string('title');
            $table->string('client_name');
            $table->string('client_company')->nullable();
            $table->string('client_email')->nullable();
            $table->string('client_phone')->nullable();
            $table->string('sales_rep_name')->nullable()->default('وكالة فكرتك الرقمية');
            $table->string('sales_phone')->nullable();
            $table->string('currency')->default('ريال');
            $table->string('status')->default('published'); // draft, published, viewed, accepted, declined
            $table->longText('raw_idea')->nullable();
            $table->json('clarifications')->nullable();
            $table->json('executive_summary')->nullable();
            $table->json('problem_vs_solution')->nullable();
            $table->json('workflow_steps')->nullable();
            $table->json('system_architecture')->nullable();
            $table->json('modules')->nullable();
            $table->json('pricing_packages')->nullable();
            $table->json('payment_milestones')->nullable();
            $table->json('timeline_phases')->nullable();
            $table->json('deliverables_and_guarantees')->nullable();
            $table->json('terms_and_conditions')->nullable();
            $table->unsignedInteger('views_count')->default(0);
            $table->timestamp('last_viewed_at')->nullable();
            $table->timestamp('accepted_at')->nullable();
            $table->string('accepted_package_name')->nullable();
            $table->text('client_notes')->nullable();
            $table->date('valid_until')->nullable();
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('proposals');
    }
};
