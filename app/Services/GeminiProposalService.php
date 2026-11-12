<?php

namespace App\Services;

use App\Models\CompanySetting;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Log;
use Exception;

class GeminiProposalService
{
    /**
     * Get active API key (from settings or .env).
     */
    public function getApiKey(): ?string
    {
        $settings = CompanySetting::current();
        if (!empty($settings->gemini_api_key)) {
            return trim($settings->gemini_api_key);
        }
        return env('GEMINI_API_KEY');
    }

    /**
     * Get configured Gemini Model with optional override.
     */
    public function getModel(?string $overrideModel = null): string
    {
        if (!empty($overrideModel)) {
            return trim($overrideModel);
        }
        $settings = CompanySetting::current();
        return !empty($settings->gemini_model) ? $settings->gemini_model : env('GEMINI_MODEL', 'gemini-3.8-flash');
    }

    /**
     * Ask Gemini to analyze the project idea and suggest clarifying questions & estimated pricing.
     */
    public function analyzeAndSuggestQuestions(string $rawIdea, ?string $clientName = null, ?string $currency = 'ريال', ?string $overrideModel = null): array
    {
        $apiKey = $this->getApiKey();
        $model = $this->getModel($overrideModel);

        if (empty($apiKey)) {
            return $this->getMockAnalysis($rawIdea, $clientName, $currency);
        }

        $prompt = <<<EOT
أنت كبير مهندسي الحلول الرقمية واستشاري مبيعات البرمجيات في "وكالة فكرتك الرقمية (Fkretk Agency)".
قام مسؤول المبيعات بإدخال فكرة مشروع لعميل محتمل:
- اسم العميل: {$clientName}
- فكرة المشروع والمتطلبات:
"{$rawIdea}"

مهمتك:
1. تقديم ملخص سريع للفكرة وإبراز القيمة الاستثمارية لها.
2. اقتراح مدى سعري واقعي ومنطقي (مثلاً بين 40,000 إلى 65,000 {$currency}).
3. اقتراح جدول زمني تقديري مناسب للتنفيذ (مثلاً 6 إلى 8 أسابيع).
4. اقتراح 3 إلى 4 أسئلة استيضاحية محددة يسألها النظام لمسؤول المبيعات قبل توليد العرض النهائي (مثل: نطاق المنصات، طرق الدفع والربط، نموذج العمل، سرعة التسليم)، مع وضع اختيارات متعددة وخيار موصى به لكل سؤال.

يجب أن يكون الرد بصيغة JSON حصرية وصحيحة 100% بدون أي كود ماركداون خارجي على الشكل التالي:
{
  "summary": "ملخص تحليلي احترافي للفكرة",
  "suggested_price_min": 45000,
  "suggested_price_max": 65000,
  "suggested_currency": "{$currency}",
  "estimated_weeks": "6 - 8 أسابيع",
  "questions": [
    {
      "id": "platforms",
      "question": "ما هي المنصات المستهدفة للإطلاق؟",
      "options": ["منصة ويب فقط", "منصة ويب + تطبيقات موبايل (iOS & Android)", "تطبيقات موبايل فقط مع لوحة تحكم"],
      "recommended": "منصة ويب + تطبيقات موبايل (iOS & Android)"
    },
    {
      "id": "integrations",
      "question": "ما هي عمليات الربط الخارجي المطلوبة؟",
      "options": ["بوابات دفع إلكتروني وخرائط GPS", "ربط مع أنظمة ERP ومخازن خارجية", "نظام دفع عند الاستلام وتحويلات بنكية فقط"],
      "recommended": "بوابات دفع إلكتروني وخرائط GPS"
    },
    {
      "id": "target_timeline",
      "question": "ما هو النطاق الزمني المرغوب به للعميل؟",
      "options": ["إطلاق قياسي مدروس (6 - 8 أسابيع)", "إطلاق سريع MVP (4 - 5 أسابيع)", "مشروع مرحلي ممتد (10 - 12 أسبوع)"],
      "recommended": "إطلاق قياسي مدروس (6 - 8 أسابيع)"
    }
  ]
}
EOT;

        try {
            $response = Http::timeout(30)->post("https://generativelanguage.googleapis.com/v1beta/models/{$model}:generateContent?key={$apiKey}", [
                'contents' => [
                    [
                        'parts' => [
                            ['text' => $prompt]
                        ]
                    ]
                ],
                'generationConfig' => [
                    'temperature' => 0.4,
                    'responseMimeType' => 'application/json',
                ]
            ]);

            if ($response->successful()) {
                $body = $response->json();
                $text = $body['candidates'][0]['content']['parts'][0]['text'] ?? null;
                if ($text) {
                    $decoded = json_decode($this->cleanJsonText($text), true);
                    if (is_array($decoded) && isset($decoded['questions'])) {
                        return $decoded;
                    }
                }
            }

            Log::warning('Gemini analysis failed or returned unexpected format: ' . $response->body());
        } catch (Exception $e) {
            Log::error('Gemini API Error: ' . $e->getMessage());
        }

        return $this->getMockAnalysis($rawIdea, $clientName, $currency);
    }

    /**
     * Generate the complete proposal JSON from the project idea and answered clarifications.
     */
    public function generateFullProposal(array $input, ?string $overrideModel = null): array
    {
        $apiKey = $this->getApiKey();
        $model = $this->getModel($overrideModel ?: ($input['model'] ?? null));

        $clientName = $input['client_name'] ?? 'العميل الكريم';
        $clientCompany = $input['client_company'] ?? '';
        $rawIdea = $input['raw_idea'] ?? '';
        $currency = $input['currency'] ?? 'ريال';
        $clarifications = isset($input['clarifications']) ? json_encode($input['clarifications'], JSON_UNESCAPED_UNICODE) : '{}';
        $suggestedPrice = $input['custom_price'] ?? null;

        if (empty($apiKey)) {
            return $this->getMockFullProposal($input);
        }

        $agency = CompanySetting::current();

        $prompt = <<<EOT
أنت كبير مهندسي الحلول الرقمية ورئيس قطاع المقترحات التجارية في "{$agency->agency_name}".
المطلوب صياغة وثيقة "عرض فني ومالي رسمي واستثماري شامل" لمشروع جديد بمستوى رفيع جداً من الاحترافية، الدقة، والإقناع التجاري، مطابقاً تماماً لهيكل عروضنا المعتمدة.

بيانات المشروع:
- العميل: {$clientName} {$clientCompany}
- فكرة ومتطلبات المشروع: "{$rawIdea}"
- إجابات أسئلة الاستيضاح مع السيلز: {$clarifications}
- العملة المعتمدة: {$currency}

المطلوب إنشاء ملف JSON متكامل 100% يحتوي على الأقسام التالية:
1. title: عنوان تسويقي وفني جذاب للعرض (مثال: "عرض فني ومالي: منصة توزيع مستحضرات التجميل والأدوية بالجملة").
2. executive_summary:
   - badge: وسم ترويجي مثل "منظومة رقمية ذكية" أو "حل متكامل B2B".
   - subtitle: شرح في سطرين عن القيمة الأساسية للمشروع.
   - key_stats: 4 بطاقات إحصائية بارزة (مثلاً: الباقة الأساسية، الباقة الشاملة، الجدول الزمني، فترة الضمان).
   - target_platforms: مصفوفة بأسماء المنصات المدعومة (مثال: ["ويب متجاوب", "تطبيق iOS", "تطبيق Android", "لوحة تحكم"]).
3. problem_vs_solution:
   - why_needed_title: عنوان لماذا يحتاج السوق لهذا النظام.
   - why_needed_desc: فقرة شارحة.
   - pillars: 3 ركائز رئيسية مع (num, title, desc, color).
   - challenges: مصفوفة بـ 4 سلبيات في الوضع التقليدي الحالي (تسبق بعلامة ❌).
   - solutions: مصفوفة بـ 4 حلول ومكاسب تحققها منظومتنا (تسبق بعلامة ✔️).
4. workflow_steps:
   - مصفوفة من 4 خطوات واضحة لدورة عمل النظام (step_number, title, desc, color).
5. system_architecture:
   - stack: مصفوفة بالتقنيات المقترحة (Laravel, Vue/React, Flutter, MySQL, Redis, AWS).
   - security: 3 نقاط تتعلق بالأمان وحماية البيانات والنسخ الاحتياطي.
6. modules:
   - 4 موديولات فنية رئيسية للمنظومة، كل موديول يحتوي على (id, name, tag, icon, color, features [4 نقاط تفصيلية]).
7. pricing_packages:
   - باقتان استثماريتان مدرستان:
     * الباقة الأولى: الباقة الأساسية (مثلاً منصة الويب فقط). تحتوي على: id, name, tag, subtitle, price, currency, price_note, is_recommended (false), features (6 نقاط)، missing_features (3 نقاط غير مشمولة).
     * الباقة الثانية: الباقة الشاملة الموصى بها (مثلاً الويب + تطبيقات الموبايل). تحتوي على نفس الحقول مع is_recommended (true)، وميزات إضافية قوية ومضاعفة.
8. payment_milestones:
   - 3 مراحل دفع تعاقدية مريحة (مثال: 40% مقدم، 30% بعد اعتماد الواجهات والنسخة التجريبية، 30% عند التسليم النهائي والإطلاق).
9. timeline_phases:
   - 4 مراحل زمنية تنفيذية مقسمة بالأسابيع (phase_number, duration, title, deliverables).
10. deliverables_and_guarantees:
    - 3 ضمانات رئيسية تمنحها وكالة فكرتك (سورس كود كامل، دعم فني مجاني، سرعة استجابة).
11. terms_and_conditions:
    - 3 إلى 4 شروط تعاقدية عامة (فترة الصلاحية، شروط الضمان، ملكية الملكية الفكرية).

يجب أن يكون الرد عبارة عن JSON صالح ومكتمل تماماً دون نصوص تمهيدية أو ختامية:
EOT;

        try {
            $response = Http::timeout(60)->post("https://generativelanguage.googleapis.com/v1beta/models/{$model}:generateContent?key={$apiKey}", [
                'contents' => [
                    [
                        'parts' => [
                            ['text' => $prompt]
                        ]
                    ]
                ],
                'generationConfig' => [
                    'temperature' => 0.4,
                    'responseMimeType' => 'application/json',
                ]
            ]);

            if ($response->successful()) {
                $body = $response->json();
                $text = $body['candidates'][0]['content']['parts'][0]['text'] ?? null;
                if ($text) {
                    $decoded = json_decode($this->cleanJsonText($text), true);
                    if (is_array($decoded) && isset($decoded['modules'], $decoded['pricing_packages'])) {
                        return $decoded;
                    }
                }
            }

            Log::warning('Gemini full proposal generation failed: ' . $response->body());
        } catch (Exception $e) {
            Log::error('Gemini Generation Error: ' . $e->getMessage());
        }

        return $this->getMockFullProposal($input);
    }

    /**
     * Clean potential markdown backticks from response.
     */
    protected function cleanJsonText(string $text): string
    {
        $text = trim($text);
        if (str_starts_with($text, '```json')) {
            $text = substr($text, 7);
        } elseif (str_starts_with($text, '```')) {
            $text = substr($text, 3);
        }
        if (str_ends_with($text, '```')) {
            $text = substr($text, 0, -3);
        }
        return trim($text);
    }

    /**
     * Fallback mock analysis when API key is missing or quota exceeded.
     */
    public function getMockAnalysis(string $rawIdea, ?string $clientName = null, ?string $currency = 'ريال'): array
    {
        return [
            'summary' => "تحليل أولي لفكرة المشروع: بناء منظومة ذكية متخصصة تلبي متطلبات {$clientName} لأتمتة العمليات وتسهيل وصول العملاء وزيادة المبيعات عبر تجربة رقمية متكاملة.",
            'suggested_price_min' => 45000,
            'suggested_price_max' => 65000,
            'suggested_currency' => $currency ?: 'ريال',
            'estimated_weeks' => '6 - 8 أسابيع',
            'questions' => [
                [
                    'id' => 'platforms',
                    'question' => 'ما هو نطاق المنصات المطلوب للمشروع؟',
                    'options' => [
                        'منصة ويب متكاملة + تطبيق موبايل (iOS & Android)',
                        'منصة ويب سحابية فقط مع لوحة تحكم إدارية',
                        'تطبيقات موبايل أصيلة فقط',
                    ],
                    'recommended' => 'منصة ويب متكاملة + تطبيق موبايل (iOS & Android)',
                ],
                [
                    'id' => 'target_budget',
                    'question' => 'ما هي الميزانية التقديرية المتوقعة من جانبكم أو من العميل؟',
                    'options' => [
                        'بين 45,000 إلى 65,000 ' . $currency . ' (النطاق القياسي المقترح)',
                        'أقل من 40,000 ' . $currency . ' (نطاق الـ MVP الأساسي)',
                        'أعلى من 75,000 ' . $currency . ' (نطاق المنظومات المتقدمة الموسعة)',
                    ],
                    'recommended' => 'بين 45,000 إلى 65,000 ' . $currency . ' (النطاق القياسي المقترح)',
                ],
                [
                    'id' => 'integrations',
                    'question' => 'ما هي عمليات الربط الخارجي الأساسية؟',
                    'options' => [
                        'بوابات دفع إلكتروني وخرائط GPS وإشعارات SMS/واتساب',
                        'ربط مع نظام محاسبي ومخازن ERP خارجي',
                        'نظام دفع نقدي وتحويلات بنكية مباشرة بدون بوابات وسيطة',
                    ],
                    'recommended' => 'بوابات دفع إلكتروني وخرائط GPS وإشعارات SMS/واتساب',
                ],
                [
                    'id' => 'timeline_preference',
                    'question' => 'ما هي أولوية وسرعة الإطلاق المطلوبة؟',
                    'options' => [
                        'إطلاق متقن ومتوازن خلال 6 إلى 8 أسابيع عمل',
                        'إطلاق سريع لنسخة تجريبية خلال 4 إلى 5 أسابيع',
                        'إطلاق مرحلي موسع على 3 دفعات',
                    ],
                    'recommended' => 'إطلاق متقن ومتوازن خلال 6 إلى 8 أسابيع عمل',
                ],
            ],
        ];
    }

    /**
     * Fallback full proposal tailored to project input when API key is not yet set.
     */
    public function getMockFullProposal(array $input): array
    {
        $clientName = $input['client_name'] ?? 'العميل الكريم';
        $clientCompany = $input['client_company'] ?? '';
        $rawIdea = $input['raw_idea'] ?? 'تطوير منصة وتطبيقات رقمية متكاملة';
        $currency = $input['currency'] ?? 'ريال';
        $priceBase = 45000;
        $priceFull = 60000;

        return [
            'title' => "عرض فني ومالي: " . (mb_strlen($rawIdea) > 60 ? mb_substr($rawIdea, 0, 57) . '...' : $rawIdea),
            'executive_summary' => [
                'badge' => 'حل رقمي متكامل وذكي',
                'subtitle' => "منظومة تقنية مصممة خصيصاً لـ {$clientName} لأتمتة العمليات وتقديم تجربة مستخدم استثنائية وزيادة العائد الاستثماري.",
                'key_stats' => [
                    ['label' => 'الباقة الأساسية (الويب)', 'value' => number_format($priceBase) . ' ' . $currency],
                    ['label' => 'الباقة الشاملة (الموصى بها)', 'value' => number_format($priceFull) . ' ' . $currency],
                    ['label' => 'مدة التنفيذ والإطلاق', 'value' => '6 - 8 أسابيع'],
                    ['label' => 'فترة الضمان والدعم', 'value' => '6 أشهر مجاناً'],
                ],
                'target_platforms' => ['منصة ويب تفاعلية', 'تطبيقات موبايل (iOS & Android)', 'لوحة تحكم إدارية ERP', 'واجهات برمجة التطبيقات (API)'],
            ],
            'problem_vs_solution' => [
                'why_needed_title' => 'لماذا يُعد هذا النظام خطوة تحول استراتيجية لمشروعكم؟',
                'why_needed_desc' => 'الاعتماد على الأنظمة التقليدية أو الحلول الجاهزة المحدودة يعيق التوسع ويسبب هدراً في الوقت والأرباح. هذا الحل يوفر بنية تحتية سريعة ومصممة خصيصاً لطبيعة أعمالكم.',
                'pillars' => [
                    [
                        'num' => '01',
                        'title' => 'أتمتة العمليات بنسبة 100%',
                        'desc' => 'إلغاء العمليات اليدوية المعقدة وربط جميع المراحل من لحظة تقديم الطلب حتى التسليم والتقارير المالية.',
                        'color' => '#1b83bf',
                    ],
                    [
                        'num' => '02',
                        'title' => 'تجربة مستخدم سريعة وسلسة',
                        'desc' => 'واجهات حديثة متوافقة مع جميع الأجهزة والمقاسات تضمن أعلى معدل تحويل مبيعات واحتفاظ بالعملاء.',
                        'color' => '#F28822',
                    ],
                    [
                        'num' => '03',
                        'title' => 'إدارة مركزية وتقارير استخباراتية',
                        'desc' => 'لوحة تحكم إدارية شاملة تمنح أصحاب القرار نظرة حية وفورية على الإيرادات والأداء والمخزون.',
                        'color' => '#10b981',
                    ],
                ],
                'challenges' => [
                    'تشتت العمليات والبيانات وصعوبة التتبع اللحظي.',
                    'ضعف التفاعل المباشر مع العملاء وتأخر الاستجابة للطلبات.',
                    'صعوبة إدارة التدفقات المالية والتحصيلات بدقة.',
                    'تكاليف تشغيلية متزايدة دون عائد استثماري مرن.',
                ],
                'solutions' => [
                    'منظومة رقمية موحدة تربط العميل وفريق العمل والإدارة في مكان واحد.',
                    'تجربة رقمية تفاعلية مع إشعارات لحظية تزيد من ارتباط العميل.',
                    'فواتير إلكترونية وتقارير تحليلية ومتابعة مالية دقيقة.',
                    'خفض النفقات التشغيلية بنسبة تتجاوز 40% من خلال الأتمتة الذكية.',
                ],
            ],
            'workflow_steps' => [
                [
                    'step_number' => '1',
                    'title' => 'تسجيل وتفعيل الحسابات',
                    'desc' => 'دخول سهل وآمن مع التحقق بخطوتين وتخصيص صلاحيات المستخدمين والعملاء.',
                    'color' => '#1b83bf',
                ],
                [
                    'step_number' => '2',
                    'title' => 'استعراض الخدمات والطلب الفوري',
                    'desc' => 'تصفح الكتالوج والخدمات، الفلاتر الذكية، والسلة التفاعلية مع دعم العروض والخصومات.',
                    'color' => '#F28822',
                ],
                [
                    'step_number' => '3',
                    'title' => 'المعالجة والدفع والربط اللوجستي',
                    'desc' => 'خيارات دفع متعددة وآمنة، تأكيد الطلب، وإسناد المهمة لفريق العمل أو التوصيل تلقائياً.',
                    'color' => '#9333ea',
                ],
                [
                    'step_number' => '4',
                    'title' => 'التسليم وتأكيد الإنجاز والتقييم',
                    'desc' => 'تتبع المسار، إشعار العميل عند الإتمام بكود تأكيد، وإصدار الفواتير وتقييم الخدمة.',
                    'color' => '#10b981',
                ],
            ],
            'system_architecture' => [
                'stack' => ['Laravel Framework 11', 'Tailwind CSS & Alpine.js / Vue', 'Flutter Cross-Platform Apps', 'MySQL Database', 'Redis Cache'],
                'security' => ['حماية مشفرة للبيانات والاتصالات عبر بروتوكول SSL/TLS', 'جدار حماية ضد هجمات DDoS واختراق البيانات', 'نسخ احتياطي دوري سحابي آلي'],
            ],
            'modules' => [
                [
                    'id' => 'core_portal',
                    'name' => 'بوابة المستخدمين والعملاء التفاعلية',
                    'tag' => 'واجهة سريعة ومتجاوبة لكافة الأجهزة',
                    'icon' => '🌐',
                    'color' => '#1b83bf',
                    'features' => [
                        'تصميم عصري متوافق بنسبة 100% مع الهواتف الذكية والأجهزة اللوحية.',
                        'كتالوج رقمي تفاعلي مع محرك بحث فوري وفلاتر ذكية متعددة.',
                        'سلة طلبات ديناميكية تدعم الحفظ التلقائي وتكرار الطلبات السابقة.',
                        'لوحة حساب العميل لمتابعة حالة الطلبات والفواتير والإشعارات.',
                    ],
                ],
                [
                    'id' => 'admin_erp',
                    'name' => 'لوحة التحكم الإدارية المتكاملة (Admin Dashboard)',
                    'tag' => 'إدارة شاملة لجميع أقسام المنظومة',
                    'icon' => '🏢',
                    'color' => '#F28822',
                    'features' => [
                        'إدارة المنتجات، الأسعار، العروض الترويجية، والتصنيفات بدقة وسلاسة.',
                        'إدارة المستخدمين والصلاحيات والأدوار (مدير، موظف، محاسب، مشرف).',
                        'نظام تقارير ورسوم بيانية تفاعلية لحجم المبيعات والإيرادات والأداء.',
                        'إدارة الفواتير والتحصيلات والضرائب وتصدير كشوف الحسابات.',
                    ],
                ],
                [
                    'id' => 'notifications_comms',
                    'name' => 'منظومة الإشعارات والتواصل التلقائي',
                    'tag' => 'تواصل مباشر وفوري مع العملاء',
                    'icon' => '🔔',
                    'color' => '#9333ea',
                    'features' => [
                        'إشعارات Push Notification فورية على الموبايل والمتصفح.',
                        'ربط رسائل واتساب ورسائل SMS لإشعار العميل بحالة طلبه.',
                        'تنبيهات إدارية تلقائية عند وصول طلبات جديدة أو نفاد كميات المخزون.',
                        'سجل تدقيق كامل لجميع النشاطات والعمليات داخل النظام.',
                    ],
                ],
                [
                    'id' => 'operations_delivery',
                    'name' => 'موديول العمليات والتنفيذ الميداني',
                    'tag' => 'إدارة خطوط السير والتسليم اللحظي',
                    'icon' => '🚀',
                    'color' => '#10b981',
                    'features' => [
                        'إسناد الطلبيات للمسؤولين أو السائقين حسب التوزيع الجغرافي.',
                        'تأكيد استلام الطلب برمز أمان أو توقيع إلكتروني لضمان الشفافية.',
                        'تتبع مباشر للعمليات ومؤشرات الأداء التشغيلي (SLA).',
                        'معالجة الطلبات الخاصة والمرتجعات بسلاسة واحترافية.',
                    ],
                ],
            ],
            'pricing_packages' => [
                [
                    'id' => 'web_edition',
                    'name' => 'باقة منصة الويب المتكاملة',
                    'tag' => 'Web Edition',
                    'subtitle' => 'الحل الرقمي الأساسي لإطلاق المشروع عبر المتصفح لجميع الشاشات والأجهزة',
                    'price' => number_format($priceBase),
                    'currency' => $currency,
                    'price_note' => 'دفعة تعاقدية لمرة واحدة تشمل التطوير والربط والتدريب',
                    'is_recommended' => false,
                    'features' => [
                        'بوابة ويب تفاعلية متوافقة مع الموبايل والتابلت والكمبيوتر.',
                        'لوحة تحكم إدارية ERP كاملة لإدارة العمليات والمستخدمين.',
                        'بوابة دفع إلكتروني وربط الفواتير والتقارير المالية.',
                        'نظام التنبيهات ورسائل البريد الإلكتروني التلقائية.',
                        'تهيئة تحسين محركات البحث الأساسية (SEO).',
                        'دعم فني وصيانة مجانية لمدة 3 أشهر وضمان كامل.',
                    ],
                    'missing_features' => [
                        'تطبيق الموبايل الأصلي (iOS & Android)',
                        'إشعارات Push Notification المباشرة على شاشة القفل',
                        'تطبيق العمليات والتوصيل الميداني المخصص',
                    ],
                ],
                [
                    'id' => 'full_ecosystem',
                    'name' => 'المنظومة الشاملة: ويب + تطبيقات موبايل',
                    'tag' => 'Full Ecosystem',
                    'subtitle' => 'الحل الأقوى والأشمل للانتشار السريع والوصول المباشر لجيب العميل على مدار الساعة',
                    'price' => number_format($priceFull),
                    'currency' => $currency,
                    'price_note' => 'أفضل قيمة استثمارية مع توفير تكلفة تطوير التطبيقات المنفصلة',
                    'is_recommended' => true,
                    'features' => [
                        'جميع مميزات باقة الويب المتكاملة المذكورة أعلاه.',
                        'تطبيق موبايل أصيل (iOS & Android) بأعلى معايير الأداء والسرعة.',
                        'إشعارات Push Notification فورية مجانية وغير محدودة.',
                        'نظام تشغيل وتتبع سريع مع إمكانية مسح الكاميرا والباركود.',
                        'رفع ونشر التطبيقات على متجري Google Play و Apple App Store.',
                        'لوحة إحصائيات متقدمة لتتبع سلوك مستخدمي التطبيقات.',
                        'دعم فني وصيانة مجانية موسعة لمدة 6 أشهر كاملة مع تدريب الفريق.',
                    ],
                    'missing_features' => [],
                ],
            ],
            'payment_milestones' => [
                ['phase' => 'الدفعة الأولى', 'percentage' => '40%', 'description' => 'مقدم التعاقد وبدء دراسة رحلة المستخدم وتصميم واجهات UI/UX.'],
                ['phase' => 'الدفعة الثانية', 'percentage' => '30%', 'description' => 'بعد اعتماد التصاميم والانتهاء من برمجة البنية التحتية ورفع النسخة التجريبية (Beta).'],
                ['phase' => 'الدفعة الثالثة', 'percentage' => '30%', 'description' => 'عند التسليم النهائي والتدريب الشامل والإطلاق الرسمي على السيرفرات والمتاجر.'],
            ],
            'timeline_phases' => [
                ['phase_number' => '1', 'duration' => 'أسبوع 1 - 2', 'title' => 'التحليل وتصميم الواجهات (UI/UX)', 'deliverables' => 'رسم مسار المستخدم وهندسة الشاشات وتصميم واجهات تفاعلية جذابة واعتمادها رسمياً.'],
                ['phase_number' => '2', 'duration' => 'أسبوع 3 - 4', 'title' => 'البنية التحتية والـ Backend', 'deliverables' => 'بناء قواعد البيانات، الربط مع واجهات API، برمجة منطق العمل واللوحة الإدارية.'],
                ['phase_number' => '3', 'duration' => 'أسبوع 5 - 6', 'title' => 'تطوير التطبيقات والتكامل', 'deliverables' => 'تطوير تطبيقات الموبايل، ربط بوابات الدفع والإشعارات، والاختبارات الشاملة (QA).'],
                ['phase_number' => '4', 'duration' => 'أسبوع 7 - 8', 'title' => 'الإطلاق والتدريب والتسليم', 'deliverables' => 'نشر النظام على السيرفرات السحابية والمتاجر، وتدريب فريق العمل وتسليم السورس كود.'],
            ],
            'deliverables_and_guarantees' => [
                'تسليم سورس كود كامل وموثق بدون أي قيود أو اشتراكات متكررة.',
                'سرعة استجابة تقنية على مدار الساعة خلال فترة الضمان والدعم الفني المجاني.',
                'ضمان سلامة واستقرار النظام وتطبيق أعلى معايير الحماية والتشفير.',
            ],
            'terms_and_conditions' => [
                'صلاحية هذا العرض سارية لمدة 14 يوماً من تاريخ إصداره.',
                'يشمل العرض جلسات تدريب تفاعلية لفريق العمل لإدارة النظام بسلاسة.',
                'يتم تقديم الدعم الفني والصيانة وفق المدة المحددة في الباقة المختارة.',
            ],
        ];
    }
}
