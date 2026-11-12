<?php

namespace App\Http\Controllers;

use App\Models\CompanySetting;
use App\Models\IndustryTemplate;
use App\Models\Proposal;
use App\Services\GeminiProposalService;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Str;
use Illuminate\View\View;

class ProposalDashboardController extends Controller
{
    protected GeminiProposalService $geminiService;

    public function __construct(GeminiProposalService $geminiService)
    {
        $this->geminiService = $geminiService;
    }

    /**
     * Dashboard Single Page Application main entry.
     */
    public function index(Request $request): View
    {
        $settings = CompanySetting::current();

        $stats = [
            'total' => Proposal::count(),
            'published' => Proposal::where('status', 'published')->count(),
            'accepted' => Proposal::where('status', 'accepted')->count(),
            'views' => Proposal::sum('views_count'),
        ];

        $proposals = Proposal::latest()->get();
        $templates = IndustryTemplate::all();

        $proposalsData = $proposals->map(function ($p) {
            return [
                'id' => $p->id,
                'uuid' => $p->uuid,
                'slug' => $p->slug,
                'title' => $p->title,
                'client_name' => $p->client_name,
                'client_company' => $p->client_company,
                'client_logo_url' => $p->client_logo_url,
                'brand_color' => $p->brand_color ?? '#1b83bf',
                'status' => $p->status,
                'views_count' => $p->views_count,
                'last_viewed_at' => $p->last_viewed_at ? $p->last_viewed_at->diffForHumans() : null,
                'created_at' => $p->created_at->format('Y-m-d'),
                'share_url' => $p->share_url,
                'whatsapp_text' => $p->whatsapp_share_text,
            ];
        });

        return view('dashboard.index', compact('settings', 'stats', 'proposals', 'proposalsData', 'templates'));
    }

    /**
     * Get all industry templates.
     */
    public function templates(): JsonResponse
    {
        return response()->json([
            'templates' => IndustryTemplate::all(),
        ]);
    }

    /**
     * Get JSON list of proposals (for reactive SPA updates).
     */
    public function list(): JsonResponse
    {
        $proposals = Proposal::latest()->get()->map(function ($p) {
            return [
                'id' => $p->id,
                'uuid' => $p->uuid,
                'slug' => $p->slug,
                'title' => $p->title,
                'client_name' => $p->client_name,
                'client_company' => $p->client_company,
                'status' => $p->status,
                'views_count' => $p->views_count,
                'last_viewed_at' => $p->last_viewed_at ? $p->last_viewed_at->diffForHumans() : null,
                'accepted_at' => $p->accepted_at ? $p->accepted_at->format('Y-m-d H:i') : null,
                'accepted_package' => $p->accepted_package_name,
                'created_at' => $p->created_at->format('Y-m-d'),
                'share_url' => $p->share_url,
                'whatsapp_text' => $p->whatsapp_share_text,
            ];
        });

        return response()->json([
            'proposals' => $proposals,
            'stats' => [
                'total' => Proposal::count(),
                'published' => Proposal::where('status', 'published')->count(),
                'accepted' => Proposal::where('status', 'accepted')->count(),
                'views' => Proposal::sum('views_count'),
            ],
        ]);
    }

    /**
     * Step 1: Analyze project idea and suggest pricing & questions.
     */
    public function analyzeIdea(Request $request): JsonResponse
    {
        $request->validate([
            'raw_idea' => 'required|string|min:10',
            'client_name' => 'nullable|string|max:255',
            'currency' => 'nullable|string|max:20',
            'model' => 'nullable|string|max:100',
        ]);

        $idea = $request->input('raw_idea');
        $clientName = $request->input('client_name', 'العميل');
        $currency = $request->input('currency', 'ريال');
        $model = $request->input('model');

        $analysis = $this->geminiService->analyzeAndSuggestQuestions($idea, $clientName, $currency, $model);

        return response()->json([
            'success' => true,
            'analysis' => $analysis,
            'active_model' => $this->geminiService->getModel($model),
            'has_api_key' => !empty($this->geminiService->getApiKey()),
        ]);
    }

    /**
     * Step 2: Generate complete proposal structure.
     */
    public function generateProposal(Request $request): JsonResponse
    {
        $request->validate([
            'client_name' => 'required|string|max:255',
            'raw_idea' => 'required|string|min:10',
            'model' => 'nullable|string|max:100',
        ]);

        $data = $request->all();
        $proposalData = $this->geminiService->generateFullProposal($data, $request->input('model'));

        return response()->json([
            'success' => true,
            'proposal' => $proposalData,
            'active_model' => $this->geminiService->getModel($request->input('model')),
        ]);
    }

    /**
     * Save generated / edited proposal.
     */
    public function store(Request $request): JsonResponse
    {
        $validated = $request->validate([
            'title' => 'required|string|max:255',
            'client_name' => 'required|string|max:255',
            'client_company' => 'nullable|string|max:255',
            'client_logo_url' => 'nullable|string|max:1000',
            'brand_color' => 'nullable|string|max:30',
            'client_email' => 'nullable|email|max:255',
            'client_phone' => 'nullable|string|max:50',
            'sales_rep_name' => 'nullable|string|max:255',
            'sales_phone' => 'nullable|string|max:50',
            'currency' => 'nullable|string|max:20',
            'status' => 'nullable|string|in:draft,published',
            'raw_idea' => 'nullable|string',
            'clarifications' => 'nullable|array',
            'executive_summary' => 'nullable|array',
            'problem_vs_solution' => 'nullable|array',
            'workflow_steps' => 'nullable|array',
            'system_architecture' => 'nullable|array',
            'modules' => 'nullable|array',
            'pricing_packages' => 'nullable|array',
            'payment_milestones' => 'nullable|array',
            'timeline_phases' => 'nullable|array',
            'deliverables_and_guarantees' => 'nullable|array',
            'terms_and_conditions' => 'nullable|array',
            'valid_until' => 'nullable|date',
        ]);

        $validated['uuid'] = (string) Str::uuid();
        $validated['slug'] = Str::slug($validated['title']) . '-' . Str::random(5);
        $validated['status'] = $validated['status'] ?? 'published';
        $validated['valid_until'] = $validated['valid_until'] ?? now()->addDays(14)->toDateString();

        $proposal = Proposal::create($validated);

        return response()->json([
            'success' => true,
            'message' => 'تم إنشاء العرض بنجاح وبشكل فوري!',
            'proposal' => $proposal,
            'share_url' => $proposal->share_url,
        ]);
    }

    /**
     * Get single proposal for viewing or editing in modal / SPA.
     */
    public function show(Proposal $proposal): JsonResponse
    {
        return response()->json([
            'success' => true,
            'proposal' => $proposal,
            'share_url' => $proposal->share_url,
            'whatsapp_text' => $proposal->whatsapp_share_text,
        ]);
    }

    /**
     * Update an existing proposal.
     */
    public function update(Request $request, Proposal $proposal): JsonResponse
    {
        $validated = $request->validate([
            'title' => 'required|string|max:255',
            'client_name' => 'required|string|max:255',
            'client_company' => 'nullable|string|max:255',
            'client_logo_url' => 'nullable|string|max:1000',
            'brand_color' => 'nullable|string|max:30',
            'client_email' => 'nullable|email|max:255',
            'client_phone' => 'nullable|string|max:50',
            'sales_rep_name' => 'nullable|string|max:255',
            'sales_phone' => 'nullable|string|max:50',
            'currency' => 'nullable|string|max:20',
            'status' => 'nullable|string|in:draft,published,accepted,declined',
            'raw_idea' => 'nullable|string',
            'clarifications' => 'nullable|array',
            'executive_summary' => 'nullable|array',
            'problem_vs_solution' => 'nullable|array',
            'workflow_steps' => 'nullable|array',
            'system_architecture' => 'nullable|array',
            'modules' => 'nullable|array',
            'pricing_packages' => 'nullable|array',
            'payment_milestones' => 'nullable|array',
            'timeline_phases' => 'nullable|array',
            'deliverables_and_guarantees' => 'nullable|array',
            'terms_and_conditions' => 'nullable|array',
            'valid_until' => 'nullable|date',
        ]);

        $proposal->update($validated);

        return response()->json([
            'success' => true,
            'message' => 'تم تحديث بيانات العرض بنجاح!',
            'proposal' => $proposal,
        ]);
    }

    /**
     * Delete a proposal.
     */
    public function destroy(Proposal $proposal): JsonResponse
    {
        $proposal->delete();

        return response()->json([
            'success' => true,
            'message' => 'تم حذف العرض بنجاح.',
        ]);
    }

    /**
     * Duplicate an existing proposal.
     */
    public function duplicate(Proposal $proposal): JsonResponse
    {
        $clone = $proposal->replicate();
        $clone->uuid = (string) Str::uuid();
        $clone->slug = Str::slug($clone->title) . '-copy-' . Str::random(4);
        $clone->title = $proposal->title . ' (نسخة جديدة)';
        $clone->views_count = 0;
        $clone->last_viewed_at = null;
        $clone->accepted_at = null;
        $clone->accepted_package_name = null;
        $clone->save();

        return response()->json([
            'success' => true,
            'message' => 'تم استنساخ العرض بنجاح!',
            'proposal' => $clone,
        ]);
    }

    /**
     * Update Company Settings (Branding, Logo, Social, Gemini API).
     */
    public function updateSettings(Request $request): JsonResponse
    {
        $settings = CompanySetting::current();

        $validated = $request->validate([
            'agency_name' => 'required|string|max:255',
            'agency_tagline' => 'nullable|string|max:255',
            'logo_url' => 'nullable|string|max:500',
            'phone' => 'nullable|string|max:50',
            'whatsapp' => 'nullable|string|max:50',
            'email' => 'nullable|email|max:255',
            'website' => 'nullable|string|max:255',
            'address' => 'nullable|string|max:255',
            'social_links' => 'nullable|array',
            'default_terms' => 'nullable|array',
            'default_guarantees' => 'nullable|array',
            'gemini_api_key' => 'nullable|string',
            'gemini_model' => 'nullable|string|max:100',
        ]);

        $settings->update($validated);

        return response()->json([
            'success' => true,
            'message' => 'تم حفظ إعدادات الوكالة وربط Gemini بنجاح!',
            'settings' => $settings,
        ]);
    }

    /**
     * Instant check for Gemini API Key.
     */
    public function testGemini(Request $request): JsonResponse
    {
        $apiKey = $request->input('api_key') ?: $this->geminiService->getApiKey();
        $model = $request->input('model') ?: $this->geminiService->getModel();

        if (empty($apiKey)) {
            return response()->json([
                'success' => false,
                'message' => 'يرجى إدخال الـ Gemini API Key أولاً.',
            ], 422);
        }

        try {
            $response = \Illuminate\Support\Facades\Http::timeout(10)->post("https://generativelanguage.googleapis.com/v1beta/models/{$model}:generateContent?key={$apiKey}", [
                'contents' => [
                    [
                        'parts' => [
                            ['text' => 'قل "مرحباً فكرتك الرقمية" في كلمة واحدة فقط للتأكد من عمل المفتاح.']
                        ]
                    ]
                ]
            ]);

            if ($response->successful()) {
                return response()->json([
                    'success' => true,
                    'message' => "تم الاتصال بنجاح مع موديل ({$model})! المفتاح نشط ومستعد لتوليد العروض الذكية فورياً.",
                ]);
            }

            return response()->json([
                'success' => false,
                'message' => 'فشل الاتصال بـ Gemini: ' . ($response->json('error.message') ?? $response->body()),
            ], 400);
        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'خطأ أثناء الاتصال: ' . $e->getMessage(),
            ], 500);
        }
    }
}
