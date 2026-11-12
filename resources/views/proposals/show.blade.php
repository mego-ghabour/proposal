<!DOCTYPE html>
<html lang="ar" dir="rtl" class="scroll-smooth">
<head>
  <meta charset="UTF-8">
  <meta name="viewport" content="width=device-width, initial-scale=1.0, maximum-scale=1.0, user-scalable=no">
  <meta name="csrf-token" content="{{ csrf_token() }}">

  <!-- WhatsApp & Social Media Preview (Open Graph) -->
  <meta property="og:site_name" content="{{ $settings->agency_name ?? 'وكالة فكرتك الرقمية' }}">
  <meta property="og:title" content="{{ $proposal->title }}">
  <meta property="og:description" content="{{ $proposal->executive_summary['subtitle'] ?? 'عرض فني ومالي مفصل من وكالة فكرتك الرقمية.' }}">
  <meta property="og:image" content="{{ url($settings->logo_url ?? '/assets/logo_fkretk.png') }}">
  <meta property="og:type" content="website">
  <meta name="twitter:card" content="summary_large_image">
  <meta name="twitter:title" content="{{ $proposal->title }}">
  <meta name="twitter:description" content="{{ $proposal->executive_summary['subtitle'] ?? 'عرض فني ومالي من وكالة فكرتك الرقمية' }}">

  <title>{{ $proposal->title }} | {{ $settings->agency_name ?? 'فكرتك الرقمية' }}</title>

  <!-- Tailwind CSS CDN -->
  <script src="https://cdn.tailwindcss.com"></script>
  <script>
    tailwind.config = {
      theme: {
        extend: {
          colors: {
            brand: {
              navy: '#12263A',
              orange: '#F28822',
              blue: '#1b83bf',
              gold: '#fbbc04',
            }
          }
        }
      }
    }
  </script>

  <!-- Google Fonts: Cairo & Tajawal -->
  <link rel="preconnect" href="https://fonts.googleapis.com">
  <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
  <link href="https://fonts.googleapis.com/css2?family=Cairo:wght@400;500;600;700;800;900&family=Tajawal:wght@400;500;700;800&display=swap" rel="stylesheet">

  <style>
    @font-face {
      font-family: 'Thmanyah';
      src: url('/assets/fonts/thmanyahsans-Bold.woff2') format('woff2');
      font-weight: 700;
      font-style: normal;
      font-display: swap;
    }
    @font-face {
      font-family: 'Thmanyah';
      src: url('/assets/fonts/thmanyahsans-Regular.woff2') format('woff2');
      font-weight: 400;
      font-style: normal;
      font-display: swap;
    }

    body {
      font-family: 'Cairo', system-ui, -apple-system, sans-serif;
      background-color: #f8fafc;
      color: #1e293b;
      -webkit-font-smoothing: antialiased;
      overflow-x: hidden;
    }

    h1, h2, h3, h4, .font-heading {
      font-family: 'Thmanyah', 'Cairo', sans-serif;
    }

    .glass-card {
      background: rgba(255, 255, 255, 0.95);
      backdrop-filter: blur(12px);
      border: 1px solid rgba(226, 232, 240, 0.8);
      box-shadow: 0 4px 20px -2px rgba(0, 0, 0, 0.05);
      transition: all 0.25s ease;
    }
    .glass-card:hover {
      box-shadow: 0 10px 25px -3px rgba(0, 0, 0, 0.08);
    }

    .gradient-header {
      background: linear-gradient(135deg, #12263A 0%, #173854 45%, {{ $proposal->brand_color ?: '#1b83bf' }} 100%);
    }

    .pulse-glow {
      box-shadow: 0 0 0 0 rgba(242, 136, 34, 0.7);
      animation: pulse 2s infinite;
    }
    @keyframes pulse {
      0% { box-shadow: 0 0 0 0 rgba(242, 136, 34, 0.6); }
      70% { box-shadow: 0 0 0 12px rgba(242, 136, 34, 0); }
      100% { box-shadow: 0 0 0 0 rgba(242, 136, 34, 0); }
    }

    @media print {
      .no-print { display: none !important; }
      body { background: white; color: black; }
      .glass-card { box-shadow: none; border: 1px solid #ccc; break-inside: avoid; }
      header { background: #12263A !important; -webkit-print-color-adjust: exact; print-color-adjust: exact; }
    }
  </style>
</head>
<body class="selection:bg-[#F28822] selection:text-white">

  <!-- Scroll Progress Indicator -->
  <div id="progressBar" class="fixed top-0 left-0 right-0 h-1 bg-gradient-to-r from-[#1b83bf] via-[#F28822] to-[#12263A] z-50 transition-all duration-75 no-print" style="width: 0%;"></div>

  <!-- Floating Direct Action CTA (Mobile / Desktop) -->
  <div class="fixed bottom-4 left-4 z-40 no-print flex items-center gap-2">
    <a href="#section-pricing" class="px-4 py-2.5 sm:px-5 sm:py-3.5 rounded-full bg-gradient-to-r from-[#F28822] to-[#d97316] text-white font-bold text-[11px] sm:text-xs shadow-2xl flex items-center gap-2 hover:scale-105 active:scale-95 transition-all pulse-glow border border-white/20">
      <span>🚀</span>
      <span>باقات الاستثمار والأسعار</span>
    </a>
    <button onclick="openAcceptModal()" class="px-3.5 py-2.5 sm:px-4 sm:py-3.5 rounded-full bg-[#1b83bf] text-white font-bold text-[11px] sm:text-xs shadow-xl flex items-center gap-1.5 hover:scale-105 active:scale-95 transition-all border border-white/20">
      <span>✍️</span>
      <span class="hidden sm:inline">اعتماد العرض</span>
    </button>
  </div>

  <!-- Top Announcement Bar -->
  <div class="bg-[#12263A] text-white/90 text-[11px] sm:text-xs py-2 px-3 sm:px-6 border-b border-white/10 flex justify-between items-center no-print">
    <div class="flex items-center gap-2">
      <span class="inline-flex items-center gap-1 px-2 py-0.5 rounded-full text-[10px] font-bold bg-[#F28822]/20 text-[#F28822] border border-[#F28822]/40 whitespace-nowrap">
        وثيقة رسمية
      </span>
      <span class="truncate max-w-[200px] sm:max-w-md">موجهة إلى: <strong>{{ $proposal->client_name }}</strong> {{ $proposal->client_company ? "({$proposal->client_company})" : '' }}</span>
    </div>
    <div class="flex items-center gap-3">
      @if($proposal->status === 'accepted')
        <span class="px-2 py-0.5 rounded-full bg-emerald-500/20 text-emerald-400 font-bold border border-emerald-500/30">
          ✓ تم اعتماد العرض
        </span>
      @endif
      <span class="text-white/60 hidden sm:inline">صلاحية العرض: {{ $proposal->valid_until ? $proposal->valid_until->format('Y/m/d') : '14 يوماً' }}</span>
      <a href="https://wa.me/{{ preg_replace('/[^0-9]/', '', $proposal->sales_phone ?: $settings->whatsapp) }}" target="_blank" class="text-[#F28822] hover:underline flex items-center gap-1">
        <span>💬</span> تواصل واتساب
      </a>
    </div>
  </div>

  <!-- Header / Hero Section -->
  <header class="gradient-header text-white pt-6 pb-12 sm:pt-8 sm:pb-16 px-4 sm:px-8 shadow-xl">
    <div class="max-w-6xl mx-auto">
      
      <!-- Top Brand Row -->
      <div class="flex flex-wrap items-center justify-between gap-4 pb-6 border-b border-white/15">
        <div class="flex items-center gap-3 sm:gap-4">
          <div class="bg-white p-2 rounded-xl shadow-md border border-white/20 flex items-center justify-center shrink-0">
            <img src="{{ $settings->logo_url ?? '/assets/logo_fkretk.png' }}" alt="{{ $settings->agency_name }}" class="h-8 sm:h-10 w-auto object-contain">
          </div>

          @if(!empty($proposal->client_logo_url))
            <div class="text-white/60 font-black text-sm hidden sm:block">✕</div>
            <div class="bg-white p-1.5 rounded-xl shadow-md border border-white/20 flex items-center justify-center shrink-0">
              <img src="{{ $proposal->client_logo_url }}" alt="{{ $proposal->client_name }}" class="h-7 sm:h-9 w-auto object-contain max-w-[120px]">
            </div>
          @endif

          <div>
            <div class="flex items-center gap-2">
              <h2 class="text-base sm:text-lg font-black tracking-wide">{{ $settings->agency_name }}</h2>
              @if(!empty($proposal->client_logo_url))
                <span class="text-[10px] sm:text-xs font-bold px-2 py-0.5 rounded bg-white/20 text-[#fbbc04]">شراكة حصرية مع {{ $proposal->client_name }}</span>
              @else
                <span class="text-[10px] sm:text-xs font-normal px-2 py-0.5 rounded bg-[#1b83bf] text-white">Fkretk Agency</span>
              @endif
            </div>
            <p class="text-[11px] sm:text-xs text-slate-300 mt-0.5">{{ $settings->agency_tagline }}</p>
          </div>
        </div>

        <!-- Action Buttons -->
        <div class="flex items-center gap-2 sm:gap-3 w-full sm:w-auto no-print">
          <button onclick="window.print()" class="flex-1 sm:flex-none px-3.5 py-2 rounded-lg bg-white/10 hover:bg-white/20 active:bg-white/30 text-white text-xs font-semibold backdrop-blur border border-white/20 transition-all flex items-center justify-center gap-1.5 shadow-sm">
            <span>📄</span>
            <span>تحميل PDF / طباعة</span>
          </button>
          <button onclick="openAcceptModal()" class="flex-1 sm:flex-none px-4 py-2 rounded-lg bg-gradient-to-r from-[#F28822] to-[#d97316] hover:brightness-110 active:brightness-95 text-white text-xs font-bold transition-all shadow-md flex items-center justify-center gap-1.5">
            <span>✍️</span>
            <span>اعتماد العرض</span>
          </button>
        </div>
      </div>

      <!-- Main Proposal Banner Title -->
      <div class="mt-6 sm:mt-8 grid grid-cols-1 lg:grid-cols-12 gap-6 items-center">
        <div class="lg:col-span-8 space-y-3">
          <div class="inline-flex items-center gap-1.5 px-3 py-1 rounded-full text-[11px] sm:text-xs font-semibold bg-[#F28822]/20 text-[#fbbc04] border border-[#F28822]/30 mb-1">
            <span class="w-2 h-2 rounded-full bg-[#F28822] animate-ping"></span>
            <span>{{ $proposal->executive_summary['badge'] ?? 'عرض فني واستثماري معتمد' }}</span>
          </div>

          <h1 class="text-xl sm:text-3xl lg:text-4xl font-black leading-tight sm:leading-snug text-white">
            {{ $proposal->title }}
          </h1>

          <p class="text-xs sm:text-sm text-slate-200 leading-relaxed max-w-2xl">
            {{ $proposal->executive_summary['subtitle'] ?? 'منظومة رقمية شاملة مصممة خصيصاً لتحقيق أعلى درجات الأتمتة والكفاءة التشغيلية ومضاعفة المبيعات.' }}
          </p>

          <!-- Platforms Pill Badges -->
          @if(!empty($proposal->executive_summary['target_platforms']))
          <div class="flex flex-wrap gap-2 pt-2 text-xs">
            @foreach($proposal->executive_summary['target_platforms'] as $platform)
              <span class="px-2.5 py-1 rounded-lg bg-white/10 backdrop-blur border border-white/15 text-slate-100 flex items-center gap-1">
                <span>📱</span> {{ $platform }}
              </span>
            @endforeach
          </div>
          @endif
        </div>

        <!-- Summary Highlights Card -->
        <div class="lg:col-span-4 bg-white/10 backdrop-blur-md rounded-2xl p-4 sm:p-5 border border-white/20 text-white">
          <div class="text-xs font-bold text-[#fbbc04] mb-3 flex items-center justify-between pb-2 border-b border-white/10">
            <span>موجز العرض المالي والتنفيذي</span>
            <span class="text-[10px] bg-white/20 px-2 py-0.5 rounded text-white font-normal">وثيقة تعاقد</span>
          </div>

          <div class="space-y-2.5 text-xs">
            @if(!empty($proposal->executive_summary['key_stats']))
              @foreach($proposal->executive_summary['key_stats'] as $stat)
                <div class="flex justify-between items-center py-1 border-b border-white/10">
                  <span class="text-slate-300">{{ $stat['label'] }}:</span>
                  <span class="font-black text-[#fbbc04] text-xs sm:text-sm">{{ $stat['value'] }}</span>
                </div>
              @endforeach
            @else
              @foreach($proposal->pricing_packages ?? [] as $pkg)
                <div class="flex justify-between items-center py-1 border-b border-white/10">
                  <span class="text-slate-300">{{ $pkg['name'] }}:</span>
                  <span class="font-black text-[#fbbc04]">{{ $pkg['price'] }} {{ $pkg['currency'] ?? $proposal->currency }}</span>
                </div>
              @endforeach
            @endif

            <div class="pt-2 flex justify-between items-center text-[11px] text-slate-300">
              <span>مسؤول المبيعات:</span>
              <span class="font-semibold text-white">{{ $proposal->sales_rep_name ?? $settings->agency_name }}</span>
            </div>
          </div>
        </div>

      </div>

    </div>
  </header>

  <!-- Sticky Sub-Navigation (Scrollspy) -->
  <nav class="sticky top-0 z-30 bg-white/95 backdrop-blur-md border-b border-slate-200 shadow-sm no-print">
    <div class="max-w-6xl mx-auto px-4 sm:px-8">
      <div class="flex items-center justify-between overflow-x-auto py-2.5 gap-2 text-xs font-bold no-scrollbar">
        <a href="#section-solution" id="link-solution" class="nav-link active px-3 py-1.5 sm:px-4 sm:py-2 rounded-lg transition-all flex items-center gap-1 whitespace-nowrap text-slate-700 hover:bg-slate-100">
          <span>💡</span> نظرة عامة والحل
        </a>
        <a href="#section-workflow" id="link-workflow" class="nav-link px-3 py-1.5 sm:px-4 sm:py-2 rounded-lg transition-all flex items-center gap-1 whitespace-nowrap text-slate-700 hover:bg-slate-100">
          <span>🔄</span> دورة عمل المنظومة
        </a>
        <a href="#section-modules" id="link-modules" class="nav-link px-3 py-1.5 sm:px-4 sm:py-2 rounded-lg transition-all flex items-center gap-1 whitespace-nowrap text-slate-700 hover:bg-slate-100">
          <span>⚙️</span> الموديولات والخصائص
        </a>
        <a href="#section-pricing" id="link-pricing" class="nav-link px-3 py-1.5 sm:px-4 sm:py-2 rounded-lg transition-all flex items-center gap-1 whitespace-nowrap text-[#1b83bf] bg-blue-50/70 hover:bg-blue-100">
          <span>💰</span> العرض المالي والباقات
        </a>
        <a href="#section-timeline" id="link-timeline" class="nav-link px-3 py-1.5 sm:px-4 sm:py-2 rounded-lg transition-all flex items-center gap-1 whitespace-nowrap text-slate-700 hover:bg-slate-100">
          <span>📅</span> مراحل التنفيذ والضمانات
        </a>
      </div>
    </div>
  </nav>

  <!-- Main Content Container -->
  <main class="max-w-6xl mx-auto px-4 sm:px-8 py-8 sm:py-12 space-y-12 sm:space-y-16">

    <!-- ==================== SECTION 1: Overview & Problem vs Solution ==================== -->
    <section id="section-solution" class="scroll-mt-20 space-y-6">
      
      <div class="flex items-center gap-2.5">
        <span class="px-2.5 py-0.5 rounded-full text-[11px] font-black bg-[#1b83bf]/10 text-[#1b83bf] border border-[#1b83bf]/20">
          01
        </span>
        <h3 class="text-xl sm:text-2xl font-black text-[#12263A]">
          {{ $proposal->problem_vs_solution['why_needed_title'] ?? 'لماذا يحتاج هذا المشروع إلى هذه المنظومة الذكية؟' }}
        </h3>
      </div>
      <p class="text-xs sm:text-sm text-slate-600 leading-relaxed max-w-4xl">
        {{ $proposal->problem_vs_solution['why_needed_desc'] ?? 'تعتمد الكثير من الأعمال على الأساليب اليدوية أو الأنظمة المجزأة، مما يسبب هدراً في الأرباح وتراكماً للأخطاء. هذا النظام صُمم خصيصاً لحل هذه المشكلات وإطلاق العمليات بقوة.' }}
      </p>

      <!-- 3 Pillars Cards -->
      @if(!empty($proposal->problem_vs_solution['pillars']))
      <div class="grid grid-cols-1 md:grid-cols-3 gap-4 sm:gap-6">
        @foreach($proposal->problem_vs_solution['pillars'] as $pillar)
        <div class="glass-card rounded-2xl p-5 sm:p-6 border-t-4" style="border-top-color: {{ $pillar['color'] ?? '#1b83bf' }}">
          <div class="w-10 h-10 rounded-xl bg-slate-100 flex items-center justify-center font-bold text-base mb-3" style="color: {{ $pillar['color'] ?? '#1b83bf' }}">
            {{ $pillar['num'] ?? '0' . ($loop->iteration) }}
          </div>
          <h4 class="text-base sm:text-lg font-bold text-[#12263A] mb-1.5">{{ $pillar['title'] }}</h4>
          <p class="text-xs text-slate-600 leading-relaxed">
            {{ $pillar['desc'] }}
          </p>
        </div>
        @endforeach
      </div>
      @endif

      <!-- Problem vs Solution Direct Comparison -->
      <div class="glass-card rounded-2xl p-5 sm:p-7">
        <h4 class="text-base sm:text-lg font-bold text-[#12263A] mb-4 flex items-center gap-2">
          <span>⚖️</span> المقارنة المباشرة: الوضع التقليدي الحالي مقابل حل وكالة فكرتك الذكي
        </h4>
        <div class="grid grid-cols-1 md:grid-cols-2 gap-4 sm:gap-6">
          
          <!-- Challenges / Negatives -->
          <div class="bg-red-50/70 border border-red-200/80 rounded-xl p-4 sm:p-5">
            <div class="font-bold text-red-700 text-xs sm:text-sm mb-2.5 flex items-center gap-1.5">
              <span class="w-2 h-2 rounded-full bg-red-500"></span> الوضع التقليدي الحالي (السلبيات والقصور)
            </div>
            <ul class="space-y-2 text-xs text-red-900/80">
              @foreach($proposal->problem_vs_solution['challenges'] ?? [] as $challenge)
                <li class="flex items-start gap-1.5">
                  <span class="text-red-500 shrink-0">❌</span>
                  <span>{{ ltrim($challenge, '❌ ') }}</span>
                </li>
              @endforeach
            </ul>
          </div>

          <!-- Solutions / Gains -->
          <div class="bg-emerald-50/70 border border-emerald-200/80 rounded-xl p-4 sm:p-5">
            <div class="font-bold text-emerald-800 text-xs sm:text-sm mb-2.5 flex items-center gap-1.5">
              <span class="w-2 h-2 rounded-full bg-emerald-600"></span> حل منظومة وكالة فكرتك الرقمية (المكاسب والنتائج)
            </div>
            <ul class="space-y-2 text-xs text-emerald-950/80">
              @foreach($proposal->problem_vs_solution['solutions'] ?? [] as $solution)
                <li class="flex items-start gap-1.5">
                  <span class="text-emerald-600 shrink-0">✔️</span>
                  <span>{{ ltrim($solution, '✔️ ') }}</span>
                </li>
              @endforeach
            </ul>
          </div>

        </div>
      </div>

    </section>

    <!-- Divider -->
    <div class="flex items-center justify-center gap-3 text-slate-300 my-4">
      <span class="h-px bg-slate-200 flex-1"></span>
      <span class="text-xs font-semibold text-slate-400">•••</span>
      <span class="h-px bg-slate-200 flex-1"></span>
    </div>

    <!-- ==================== SECTION 2: Workflow ==================== -->
    <section id="section-workflow" class="scroll-mt-20 space-y-6">
      
      <div class="flex items-center gap-2.5">
        <span class="px-2.5 py-0.5 rounded-full text-[11px] font-black bg-[#F28822]/10 text-[#F28822] border border-[#F28822]/20">
          02
        </span>
        <h3 class="text-xl sm:text-2xl font-black text-[#12263A]">دورة عمل المنظومة والتشغيل التلقائي</h3>
      </div>
      <p class="text-xs sm:text-sm text-slate-600 leading-relaxed max-w-4xl">
        كيف تعمل المنظومة خطوة بخطوة من لحظة دخول المستخدم حتى إتمام الطلب والتحصيل وإصدار التقارير:
      </p>

      <div class="grid grid-cols-1 sm:grid-cols-2 lg:grid-cols-4 gap-3 sm:gap-4">
        @foreach($proposal->workflow_steps ?? [] as $step)
        <div class="glass-card rounded-2xl p-4 sm:p-5 text-center border-b-4" style="border-bottom-color: {{ $step['color'] ?? '#1b83bf' }}">
          <div class="w-8 h-8 rounded-full text-white flex items-center justify-center font-bold text-xs mx-auto mb-2.5 shadow-sm" style="background-color: {{ $step['color'] ?? '#1b83bf' }}">
            {{ $step['step_number'] ?? $loop->iteration }}
          </div>
          <h5 class="text-sm sm:text-base font-bold text-[#12263A] mb-1">{{ $step['title'] }}</h5>
          <p class="text-[11px] sm:text-xs text-slate-500 leading-relaxed">
            {{ $step['desc'] }}
          </p>
        </div>
        @endforeach
      </div>

      <!-- Architecture & Security Badge -->
      @if(!empty($proposal->system_architecture))
      <div class="bg-gradient-to-br from-[#12263A] to-[#1a3854] text-white rounded-2xl p-5 sm:p-7 shadow-lg">
        <div class="flex flex-col md:flex-row md:items-center justify-between gap-4">
          <div>
            <span class="text-[10px] sm:text-xs uppercase font-bold text-[#F28822] tracking-wider">البنية الهندسية والأمان</span>
            <h4 class="text-base sm:text-xl font-bold mt-0.5">معايير برمجية عالمية تضمن سرعة واستقرار 99.9%</h4>
            <p class="text-xs text-slate-300 mt-1 max-w-xl">
              تعتمد منظومتنا على تقنيات برمجية فائقة السرعة مع تشفير كامل للبيانات وعزل أمني يضمن حماية التعاملات المالية.
            </p>
          </div>
          <div class="flex flex-wrap gap-2 text-[11px]">
            @foreach($proposal->system_architecture['stack'] ?? [] as $tech)
              <span class="px-2.5 py-1 rounded-lg bg-white/10 border border-white/20 text-white font-medium">⚡ {{ $tech }}</span>
            @endforeach
          </div>
        </div>
      </div>
      @endif

    </section>

    <!-- Divider -->
    <div class="flex items-center justify-center gap-3 text-slate-300 my-4">
      <span class="h-px bg-slate-200 flex-1"></span>
      <span class="text-xs font-semibold text-slate-400">•••</span>
      <span class="h-px bg-slate-200 flex-1"></span>
    </div>

    <!-- ==================== SECTION 3: Modules & Features ==================== -->
    <section id="section-modules" class="scroll-mt-20 space-y-6">
      
      <div class="flex items-center gap-2.5">
        <span class="px-2.5 py-0.5 rounded-full text-[11px] font-black bg-purple-600/10 text-purple-600 border border-purple-600/20">
          03
        </span>
        <h3 class="text-xl sm:text-2xl font-black text-[#12263A]">الموديولات الفنية والخصائص المتطورة</h3>
      </div>
      <p class="text-xs sm:text-sm text-slate-600 leading-relaxed max-w-4xl">
        خصائص هندسية مصممة خصيصاً لدعم طبيعة عملياتكم ومضاعفة المبيعات وتسهيل إدارة الفريق والعملاء:
      </p>

      <div class="grid grid-cols-1 md:grid-cols-2 gap-4 sm:gap-6">
        @foreach($proposal->modules ?? [] as $module)
        <div class="glass-card rounded-2xl p-5 sm:p-6">
          <div class="flex items-center gap-3 mb-3.5">
            <span class="p-2.5 rounded-xl bg-slate-100 font-bold text-base">{{ $module['icon'] ?? '⚙️' }}</span>
            <div>
              <h4 class="text-base sm:text-lg font-bold text-[#12263A]">{{ $module['name'] }}</h4>
              <p class="text-[11px] sm:text-xs text-slate-500">{{ $module['tag'] ?? '' }}</p>
            </div>
          </div>
          <ul class="space-y-2 text-xs text-slate-600">
            @foreach($module['features'] ?? [] as $feat)
              <li class="flex items-start gap-1.5">
                <span class="text-emerald-600 font-bold">✔️</span>
                <span>{{ $feat }}</span>
              </li>
            @endforeach
          </ul>
        </div>
        @endforeach
      </div>

    </section>

    <!-- Divider -->
    <div class="flex items-center justify-center gap-3 text-slate-300 my-4">
      <span class="h-px bg-slate-200 flex-1"></span>
      <span class="text-xs font-semibold text-slate-400">•••</span>
      <span class="h-px bg-slate-200 flex-1"></span>
    </div>

    <!-- ==================== SECTION 4: Pricing Packages ==================== -->
    <section id="section-pricing" class="scroll-mt-20 space-y-6">
      
      <div class="text-center max-w-3xl mx-auto">
        <div class="inline-flex items-center gap-1.5 px-3 py-1 rounded-full text-[11px] sm:text-xs font-black bg-[#1b83bf]/10 text-[#1b83bf] border border-[#1b83bf]/25 mb-2.5">
          04 • باقات الاستثمار والعرض المالي الرسمي
        </div>
        <h3 class="text-2xl sm:text-3xl font-black text-[#12263A]">خيارات وباقات تنفيذ المنظومة الذكية</h3>
        <p class="mt-1.5 text-xs sm:text-sm text-slate-500">
          اختر الباقة الأنسب لحجم خطتكم وتطلعاتكم، مع مرونة تامة في الترقية والدعم الفني المباشر.
        </p>
      </div>

      <!-- Pricing Cards Grid -->
      <div class="grid grid-cols-1 lg:grid-cols-2 gap-6 sm:gap-8 items-stretch pt-2">
        @foreach($proposal->pricing_packages ?? [] as $pkg)
          @php
            $isRec = !empty($pkg['is_recommended']);
          @endphp
          <div class="glass-card rounded-2xl sm:rounded-3xl p-5 sm:p-8 relative flex flex-col justify-between border-2 {{ $isRec ? 'border-[#1b83bf] shadow-xl bg-gradient-to-b from-white via-white to-blue-50/30' : 'border-slate-200' }}">
            
            @if($isRec)
              <div class="absolute -top-3 left-4 sm:left-8 px-3 py-0.5 sm:px-4 sm:py-1 rounded-full text-[10px] sm:text-xs font-black bg-gradient-to-r from-[#F28822] to-[#d97316] text-white shadow-md flex items-center gap-1">
                <span>⭐</span> الباقة الموصى بها - المنظومة الشاملة
              </div>
            @endif

            <div>
              <div class="flex justify-between items-start mb-3 {{ $isRec ? 'mt-1 sm:mt-0' : '' }}">
                <div>
                  <span class="text-[11px] font-bold {{ $isRec ? 'text-[#1b83bf]' : 'text-slate-500' }} uppercase tracking-wider">
                    {{ $loop->first ? 'الباقة الأساسية' : 'الباقة الموسعة' }}
                  </span>
                  <h4 class="text-lg sm:text-2xl font-black text-[#12263A] mt-0.5">{{ $pkg['name'] }}</h4>
                  <p class="text-xs text-slate-500 mt-0.5">{{ $pkg['subtitle'] ?? '' }}</p>
                </div>
                <span class="px-2.5 py-1 rounded-full text-[10px] sm:text-xs font-bold {{ $isRec ? 'bg-[#1b83bf]/10 text-[#1b83bf]' : 'bg-slate-100 text-slate-700' }} whitespace-nowrap">
                  {{ $pkg['tag'] ?? 'Package' }}
                </span>
              </div>

              <!-- Price Display -->
              <div class="my-4 sm:my-6 pb-4 sm:pb-6 border-b {{ $isRec ? 'border-blue-100' : 'border-slate-100' }}">
                <div class="flex items-baseline gap-2">
                  <span class="text-3xl sm:text-4xl font-extrabold {{ $isRec ? 'text-[#1b83bf]' : 'text-[#12263A]' }}">{{ $pkg['price'] }}</span>
                  <span class="text-xs sm:text-sm font-bold text-slate-500">{{ $pkg['currency'] ?? $proposal->currency }}</span>
                </div>
                <p class="text-[11px] {{ $isRec ? 'text-[#F28822] font-semibold' : 'text-slate-400' }} mt-1">
                  {{ $pkg['price_note'] ?? 'دفعة تعاقدية لمرة واحدة شاملة التطوير والربط والتدريب' }}
                </p>
              </div>

              <!-- Included Features -->
              <div class="space-y-2.5 text-xs text-slate-700 mb-6">
                <div class="font-bold text-[#12263A] text-xs mb-1.5">يشمل في هذه الباقة:</div>
                @foreach($pkg['features'] ?? [] as $feat)
                  <div class="flex items-start gap-2">
                    <svg class="w-4 h-4 text-emerald-500 shrink-0 mt-0.5" fill="currentColor" viewBox="0 0 20 20"><path fill-rule="evenodd" d="M16.707 5.293a1 1 0 010 1.414l-8 8a1 1 0 01-1.414 0l-4-4a1 1 0 011.414-1.414L8 12.586l7.293-7.293a1 1 0 011.414 0z" clip-rule="evenodd"/></svg>
                    <span>{{ $feat }}</span>
                  </div>
                @endforeach

                <!-- Missing Features (if any) -->
                @if(!empty($pkg['missing_features']))
                  @foreach($pkg['missing_features'] as $mFeat)
                    <div class="flex items-start gap-2 text-slate-400 line-through">
                      <span class="w-4 text-center shrink-0 mt-0.5">✕</span>
                      <span>{{ $mFeat }}</span>
                    </div>
                  @endforeach
                @endif
              </div>
            </div>

            <!-- Action Button -->
            <div class="pt-3 border-t {{ $isRec ? 'border-blue-100' : 'border-slate-100' }}">
              <button onclick="openAcceptModal('{{ $pkg['name'] }} ({{ $pkg['price'] }} {{ $pkg['currency'] ?? $proposal->currency }})')" class="w-full py-3 sm:py-3.5 rounded-xl font-bold text-xs transition-all flex items-center justify-center gap-2 {{ $isRec ? 'bg-gradient-to-r from-[#1b83bf] to-[#146ca0] hover:brightness-110 active:brightness-95 text-white shadow-md' : 'bg-slate-100 hover:bg-slate-200 active:bg-slate-300 text-[#12263A]' }}">
                <span>{{ $isRec ? '🚀' : '✔️' }}</span>
                <span>طلب اعتماد {{ $pkg['name'] }} ({{ $pkg['price'] }} {{ $pkg['currency'] ?? $proposal->currency }})</span>
              </button>
            </div>

          </div>
        @endforeach
      </div>

    </section>

    <!-- Divider -->
    <div class="flex items-center justify-center gap-3 text-slate-300 my-4">
      <span class="h-px bg-slate-200 flex-1"></span>
      <span class="text-xs font-semibold text-slate-400">•••</span>
      <span class="h-px bg-slate-200 flex-1"></span>
    </div>

    <!-- ==================== SECTION 5: Timeline & Guarantees ==================== -->
    <section id="section-timeline" class="scroll-mt-20 space-y-6">
      
      <div class="flex items-center gap-2.5">
        <span class="px-2.5 py-0.5 rounded-full text-[11px] font-black bg-emerald-600/10 text-emerald-600 border border-emerald-600/20">
          05
        </span>
        <h3 class="text-xl sm:text-2xl font-black text-[#12263A]">الجدول الزمني ومراحل التسليم والضمانات</h3>
      </div>
      <p class="text-xs sm:text-sm text-slate-600 leading-relaxed max-w-4xl">
        خطة تنفيذ هندسية دقيقة مقسمة على مراحل واضحة لضمان أعلى درجات الجودة، مع شروط دفع تعاقدية مريحة وشفافة:
      </p>

      <!-- Timeline Cards -->
      <div class="grid grid-cols-1 sm:grid-cols-2 lg:grid-cols-4 gap-3 sm:gap-4">
        @php
          $timelineColors = ['#12263A', '#1b83bf', '#F28822', '#10b981'];
        @endphp
        @foreach($proposal->timeline_phases ?? [] as $phase)
        <div class="glass-card rounded-2xl p-4 sm:p-5 border-t-4" style="border-top-color: {{ $timelineColors[$loop->index % 4] }}">
          <span class="text-[11px] font-bold text-slate-400">المرحلة {{ $phase['phase_number'] ?? $loop->iteration }} ({{ $phase['duration'] ?? '' }})</span>
          <h5 class="text-sm sm:text-base font-bold text-[#12263A] mt-1 mb-1.5">{{ $phase['title'] }}</h5>
          <p class="text-[11px] sm:text-xs text-slate-600 leading-relaxed">
            {{ $phase['deliverables'] }}
          </p>
        </div>
        @endforeach
      </div>

      <!-- Financial Milestones & Guarantees -->
      <div class="grid grid-cols-1 md:grid-cols-2 gap-4 sm:gap-6">
        
        <!-- Payment Milestones -->
        <div class="glass-card rounded-2xl p-4 sm:p-6">
          <h4 class="text-sm sm:text-base font-bold text-[#12263A] mb-2.5 flex items-center gap-1.5">
            <span>💳</span> آلية ونظام الدفعات المالية
          </h4>
          <div class="space-y-2 text-xs text-slate-600">
            @foreach($proposal->payment_milestones ?? [] as $milestone)
              <div class="flex justify-between py-1.5 border-b border-slate-100 last:border-b-0">
                <span class="font-medium">{{ $milestone['phase'] }} ({{ $milestone['description'] }}):</span>
                <span class="font-bold text-[#12263A] shrink-0 mr-2">{{ $milestone['percentage'] }}</span>
              </div>
            @endforeach
          </div>
        </div>

        <!-- Guarantees -->
        <div class="glass-card rounded-2xl p-4 sm:p-6">
          <h4 class="text-sm sm:text-base font-bold text-[#12263A] mb-2.5 flex items-center gap-1.5">
            <span>🤝</span> ضمانات وكالة فكرتك الرقمية (Fkretk)
          </h4>
          <ul class="space-y-2 text-xs text-slate-600">
            @foreach($proposal->deliverables_and_guarantees ?? $settings->default_guarantees ?? [] as $guarantee)
              <li class="flex items-start gap-1.5">
                <span class="text-emerald-600">✔️</span>
                <span>{{ $guarantee }}</span>
              </li>
            @endforeach
          </ul>
        </div>

      </div>

    </section>

  </main>

  <!-- Bottom Action / CTA Bar -->
  <section class="bg-[#12263A] text-white py-10 sm:py-14 px-4 sm:px-8 mt-12 border-t border-white/10 no-print">
    <div class="max-w-4xl mx-auto text-center space-y-4 sm:space-y-6">
      <div class="inline-block p-2.5 rounded-2xl bg-white/5 border border-white/10">
        <img src="{{ $settings->logo_url ?? '/assets/logo_fkretk.png' }}" alt="{{ $settings->agency_name }}" class="h-8 sm:h-10 mx-auto brightness-200">
      </div>
      <h3 class="text-xl sm:text-3xl font-black">جاهزون لبدء تحويل رؤيتكم إلى واقع رقمي رائد؟</h3>
      <p class="text-xs sm:text-sm text-slate-300 max-w-xl mx-auto leading-relaxed">
        فريق وكالة فكرتك الرقمية جاهز لعقد جلسة عمل تفصيلية لتوقيع عقد المشروع والبدء في تنفيذ الواجهات المخصصة لمنصتكم فوراً.
      </p>

      <div class="flex flex-wrap justify-center gap-2.5 sm:gap-4 pt-1">
        <button onclick="openAcceptModal()" class="px-6 py-3 rounded-xl bg-gradient-to-r from-[#F28822] to-[#d97316] text-white font-bold text-xs hover:brightness-110 active:brightness-95 transition-all shadow-md flex items-center gap-2">
          <span>✍️</span> طلب اعتماد العرض والبدء
        </button>
        <a href="https://wa.me/{{ preg_replace('/[^0-9]/', '', $proposal->sales_phone ?: $settings->whatsapp) }}?text={{ urlencode('مرحباً، أود مناقشة تفاصيل عرض: ' . $proposal->title) }}" target="_blank" class="px-6 py-3 rounded-xl bg-[#25D366] text-white font-bold text-xs hover:bg-[#20ba59] transition-all shadow-md flex items-center gap-2">
          <span>💬</span> مناقشة عبر واتساب مباشرة
        </a>
        <a href="{{ $settings->website ?? 'https://fkretk.com' }}" target="_blank" class="px-5 py-3 rounded-xl bg-white/10 text-white font-bold text-xs hover:bg-white/20 transition-all border border-white/20">
          زيارة موقع وكالة فكرتك
        </a>
      </div>

      <div class="text-[10px] sm:text-[11px] text-slate-400 pt-5 border-t border-white/10 flex flex-col sm:flex-row justify-between items-center gap-2">
        <span>جميع الحقوق محفوظة © {{ date('Y') }} - {{ $settings->agency_name }}</span>
        <span>وثيقة تجارية موجهة إلى: {{ $proposal->client_name }}</span>
      </div>
    </div>
  </section>

  <!-- Modal: Accept Proposal Form -->
  <div id="acceptModal" class="fixed inset-0 z-50 flex items-center justify-center p-4 bg-black/60 backdrop-blur-sm hidden no-print">
    <div class="bg-white rounded-2xl max-w-md w-full p-6 text-slate-800 shadow-2xl border border-slate-100 space-y-4">
      <div class="flex items-center justify-between pb-3 border-b border-slate-100">
        <h4 class="text-base sm:text-lg font-black text-[#12263A] flex items-center gap-2">
          <span>✍️</span> طلب اعتماد وتوقيع العرض
        </h4>
        <button onclick="closeAcceptModal()" class="text-slate-400 hover:text-slate-600 text-lg font-bold">✕</button>
      </div>

      <p class="text-xs text-slate-500 leading-relaxed">
        يسعدنا اختياركم لوكالة فكرتك الرقمية. يرجى تأكيد بياناتكم أدناه وسيتواصل معكم مسؤول الحساب فوراً لتنسيق التعاقد الرسمي:
      </p>

      <form id="acceptForm" onsubmit="submitAcceptance(event)" class="space-y-3 text-xs">
        <div>
          <label class="block font-bold text-[#12263A] mb-1">اسم المسؤول / ممثل الجهة:</label>
          <input type="text" id="acc_name" value="{{ $proposal->client_name }}" required class="w-full px-3 py-2 border border-slate-200 rounded-lg focus:outline-none focus:border-[#1b83bf]">
        </div>

        <div>
          <label class="block font-bold text-[#12263A] mb-1">الباقة المرغوب اعتمادها:</label>
          <select id="acc_package" required class="w-full px-3 py-2 border border-slate-200 rounded-lg focus:outline-none focus:border-[#1b83bf]">
            @foreach($proposal->pricing_packages ?? [] as $pkg)
              <option value="{{ $pkg['name'] }}" {{ !empty($pkg['is_recommended']) ? 'selected' : '' }}>
                {{ $pkg['name'] }} ({{ $pkg['price'] }} {{ $pkg['currency'] ?? $proposal->currency }})
              </option>
            @endforeach
          </select>
        </div>

        <div>
          <label class="block font-bold text-[#12263A] mb-1">رقم الهاتف / واتساب للتواصل:</label>
          <input type="tel" id="acc_phone" value="{{ $proposal->client_phone }}" placeholder="مثال: +966500000000" class="w-full px-3 py-2 border border-slate-200 rounded-lg focus:outline-none focus:border-[#1b83bf]">
        </div>

        <div>
          <label class="block font-bold text-[#12263A] mb-1">ملاحظات أو مواعيد مفضلة للاجتماع (اختياري):</label>
          <textarea id="acc_notes" rows="2" placeholder="أية ملاحظات إضافية ترغبون في إرفاقها..." class="w-full px-3 py-2 border border-slate-200 rounded-lg focus:outline-none focus:border-[#1b83bf]"></textarea>
        </div>

        <div class="pt-2 flex gap-2">
          <button type="submit" id="btnSubmitAccept" class="flex-1 py-2.5 rounded-lg bg-gradient-to-r from-[#F28822] to-[#d97316] text-white font-bold text-xs hover:brightness-110 active:brightness-95 transition-all shadow-md">
            تأكيد الاعتماد والمتابعة على واتساب
          </button>
          <button type="button" onclick="closeAcceptModal()" class="px-4 py-2.5 rounded-lg bg-slate-100 text-slate-700 font-bold hover:bg-slate-200 text-xs">
            إلغاء
          </button>
        </div>
      </form>
    </div>
  </div>

  <!-- Real-time Scrollspy & Actions Logic -->
  <script>
    // Scroll progress bar
    window.addEventListener('scroll', () => {
      const winScroll = document.documentElement.scrollTop || document.body.scrollTop;
      const height = document.documentElement.scrollHeight - document.documentElement.clientHeight;
      const scrolled = (winScroll / height) * 100;
      const bar = document.getElementById('progressBar');
      if (bar) bar.style.width = scrolled + '%';
    }, { passive: true });

    // Scrollspy active tab highlight
    const sections = [
      document.getElementById('section-solution'),
      document.getElementById('section-workflow'),
      document.getElementById('section-modules'),
      document.getElementById('section-pricing'),
      document.getElementById('section-timeline')
    ];

    const navLinks = {
      'section-solution': document.getElementById('link-solution'),
      'section-workflow': document.getElementById('link-workflow'),
      'section-modules': document.getElementById('link-modules'),
      'section-pricing': document.getElementById('link-pricing'),
      'section-timeline': document.getElementById('link-timeline')
    };

    window.addEventListener('scroll', () => {
      let current = '';
      const scrollPosition = window.scrollY + 140;

      sections.forEach(section => {
        if (section) {
          const sectionTop = section.offsetTop;
          const sectionHeight = section.offsetHeight;
          if (scrollPosition >= sectionTop && scrollPosition < sectionTop + sectionHeight) {
            current = section.getAttribute('id');
          }
        }
      });

      Object.keys(navLinks).forEach(id => {
        const link = navLinks[id];
        if (link) {
          link.classList.remove('active', 'bg-[#1b83bf]', 'text-white');
          link.classList.add('text-slate-700');
          if (id === current) {
            link.classList.add('active', 'bg-[#1b83bf]', 'text-white');
            link.classList.remove('text-slate-700');
          }
        }
      });
    }, { passive: true });

    // Accept Modal Controls
    function openAcceptModal(defaultPkg = null) {
      const modal = document.getElementById('acceptModal');
      if (modal) modal.classList.remove('hidden');
      if (defaultPkg) {
        const select = document.getElementById('acc_package');
        if (select) {
          for (let i = 0; i < select.options.length; i++) {
            if (select.options[i].text.includes(defaultPkg) || defaultPkg.includes(select.options[i].value)) {
              select.selectedIndex = i;
              break;
            }
          }
        }
      }
    }

    function closeAcceptModal() {
      const modal = document.getElementById('acceptModal');
      if (modal) modal.classList.add('hidden');
    }

    async function submitAcceptance(e) {
      e.preventDefault();
      const btn = document.getElementById('btnSubmitAccept');
      btn.disabled = true;
      btn.innerText = 'جاري تسجيل الاعتماد...';

      const payload = {
        client_name: document.getElementById('acc_name').value,
        package_name: document.getElementById('acc_package').value,
        client_phone: document.getElementById('acc_phone').value,
        notes: document.getElementById('acc_notes').value,
      };

      try {
        const res = await fetch("{{ route('proposals.accept', $proposal->uuid) }}", {
          method: 'POST',
          headers: {
            'Content-Type': 'application/json',
            'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').getAttribute('content'),
          },
          body: JSON.stringify(payload)
        });
        const data = await res.json();
        if (data.success) {
          alert(data.message);
          closeAcceptModal();
          if (data.whatsapp_url) {
            window.open(data.whatsapp_url, '_blank');
          }
          window.location.reload();
        } else {
          alert('حدث خطأ: ' + (data.message || 'يرجى المحاولة مجدداً'));
        }
      } catch (err) {
        alert('حدث خطأ أثناء إرسال التأكيد. يرجى التواصل عبر واتساب مباشرة.');
      } finally {
        btn.disabled = false;
        btn.innerText = 'تأكيد الاعتماد والمتابعة على واتساب';
      }
    }
  </script>

</body>
</html>
