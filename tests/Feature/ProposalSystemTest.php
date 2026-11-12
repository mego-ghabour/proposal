<?php

namespace Tests\Feature;

use App\Models\CompanySetting;
use App\Models\Proposal;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class ProposalSystemTest extends TestCase
{
    use RefreshDatabase;

    protected User $user;

    protected function setUp(): void
    {
        parent::setUp();
        $this->seed();
        $this->user = User::where('email', 'admin@fkretk.com')->first();
    }

    public function test_root_redirects_to_dashboard(): void
    {
        $response = $this->get('/');
        $response->assertRedirect('/dashboard');
    }

    public function test_guest_is_redirected_to_login_when_accessing_dashboard(): void
    {
        $response = $this->get('/dashboard');
        $response->assertRedirect('/login');
    }

    public function test_login_page_loads_successfully(): void
    {
        $response = $this->get('/login');
        $response->assertStatus(200);
        $response->assertSee('تسجيل الدخول للوحة التحكم');
    }

    public function test_user_can_login_with_correct_credentials(): void
    {
        $response = $this->post('/login', [
            'email' => 'admin@fkretk.com',
            'password' => 'fkretk@2026',
        ]);

        $response->assertRedirect('/dashboard');
        $this->assertAuthenticatedAs($this->user);
    }

    public function test_user_cannot_login_with_incorrect_password(): void
    {
        $response = $this->post('/login', [
            'email' => 'admin@fkretk.com',
            'password' => 'wrong_password',
        ]);

        $response->assertSessionHasErrors('email');
        $this->assertGuest();
    }

    public function test_authenticated_user_can_access_dashboard(): void
    {
        $response = $this->actingAs($this->user)->get('/dashboard');
        $response->assertStatus(200);
        $response->assertSee('وكالة فكرتك الرقمية');
    }

    public function test_public_proposal_view_loads_without_login_and_increments_views(): void
    {
        $proposal = Proposal::first();
        $initialViews = $proposal->views_count;

        // Public access without actingAs
        $response = $this->get('/p/' . $proposal->uuid);
        $response->assertStatus(200);
        $response->assertSee($proposal->title);

        $this->assertDatabaseHas('proposals', [
            'id' => $proposal->id,
            'views_count' => $initialViews + 1,
        ]);
    }

    public function test_api_analyze_idea_returns_questions_and_pricing(): void
    {
        $response = $this->actingAs($this->user)->postJson('/dashboard/api/analyze-idea', [
            'raw_idea' => 'منصة وتطبيق لتوصيل وتوزيع مستحضرات التجميل والأدوية بالجملة للصيدليات',
            'client_name' => 'شركة الرعاية الحديثة',
            'currency' => 'ريال',
        ]);

        $response->assertStatus(200);
        $response->assertJsonStructure([
            'success',
            'analysis' => [
                'summary',
                'suggested_price_min',
                'suggested_price_max',
                'questions',
            ],
        ]);
    }

    public function test_api_generate_full_proposal_returns_structured_data(): void
    {
        $response = $this->actingAs($this->user)->postJson('/dashboard/api/generate-proposal', [
            'client_name' => 'شركة النخبة',
            'client_company' => 'مجموعة النخبة الطبية',
            'raw_idea' => 'نظام لإدارة العيادات الطبية وحجز المواعيد مع الدفع الإلكتروني',
            'currency' => 'ريال',
            'clarifications' => [
                'platforms' => 'منصة ويب + تطبيقات موبايل (iOS & Android)',
            ],
        ]);

        $response->assertStatus(200);
        $response->assertJsonStructure([
            'success',
            'proposal' => [
                'title',
                'executive_summary',
                'problem_vs_solution',
                'workflow_steps',
                'modules',
                'pricing_packages',
                'timeline_phases',
            ],
        ]);
    }

    public function test_client_can_accept_proposal_without_login(): void
    {
        $proposal = Proposal::first();

        $response = $this->postJson('/p/' . $proposal->uuid . '/accept', [
            'client_name' => 'م. خالد العتيبي',
            'package_name' => 'الباقة الشاملة',
            'client_phone' => '+966512345678',
            'notes' => 'جاهزون لتوقيع العقد غداً',
        ]);

        $response->assertStatus(200);
        $response->assertJson([
            'success' => true,
        ]);

        $this->assertDatabaseHas('proposals', [
            'id' => $proposal->id,
            'status' => 'accepted',
            'accepted_package_name' => 'الباقة الشاملة',
        ]);
    }

    public function test_update_company_settings(): void
    {
        $response = $this->actingAs($this->user)->postJson('/dashboard/api/settings', [
            'agency_name' => 'وكالة فكرتك الرقمية للتطوير',
            'agency_tagline' => 'شريكك التقني لتطوير المنظومات',
            'whatsapp' => '966599999999',
            'gemini_api_key' => 'AIzaSyTestMockKey123',
            'gemini_model' => 'gemini-2.0-flash',
        ]);

        $response->assertStatus(200);
        $this->assertDatabaseHas('company_settings', [
            'agency_name' => 'وكالة فكرتك الرقمية للتطوير',
            'whatsapp' => '966599999999',
            'gemini_api_key' => 'AIzaSyTestMockKey123',
        ]);
    }

    public function test_authenticated_user_can_change_password(): void
    {
        $response = $this->actingAs($this->user)->postJson('/dashboard/api/change-password', [
            'current_password' => 'fkretk@2026',
            'new_password' => 'newSecretPass123',
            'new_password_confirmation' => 'newSecretPass123',
        ]);

        $response->assertStatus(200);
        $response->assertJson([
            'success' => true,
        ]);
    }

    public function test_gemini_3_8_flash_model_can_be_configured_and_used(): void
    {
        // 1. Update company settings to gemini-3.8-flash
        $response = $this->actingAs($this->user)->postJson('/dashboard/api/settings', [
            'agency_name' => 'وكالة فكرتك',
            'whatsapp' => '966500000000',
            'gemini_model' => 'gemini-3.8-flash',
        ]);
        $response->assertStatus(200);
        $this->assertDatabaseHas('company_settings', [
            'gemini_model' => 'gemini-3.8-flash',
        ]);

        // 2. Analyze idea with gemini-3.8-flash
        $analyzeRes = $this->actingAs($this->user)->postJson('/dashboard/api/analyze-idea', [
            'raw_idea' => 'منصة بيع وتوزيع أدوية بيطرية ومستلزمات عيادات ومزارع مع تتبع المناديب',
            'client_name' => 'شركة المزارع الحديثة',
            'model' => 'gemini-3.8-flash',
        ]);
        $analyzeRes->assertStatus(200);
        $analyzeRes->assertJson([
            'success' => true,
            'active_model' => 'gemini-3.8-flash',
        ]);

        // 3. Generate full proposal with custom model
        $generateRes = $this->actingAs($this->user)->postJson('/dashboard/api/generate-proposal', [
            'raw_idea' => 'منصة بيع وتوزيع أدوية بيطرية ومستلزمات عيادات ومزارع مع تتبع المناديب',
            'client_name' => 'شركة المزارع الحديثة',
            'model' => 'gemini-3.8-flash',
        ]);
        $generateRes->assertStatus(200);
        $generateRes->assertJson([
            'success' => true,
            'active_model' => 'gemini-3.8-flash',
        ]);
    }
}
