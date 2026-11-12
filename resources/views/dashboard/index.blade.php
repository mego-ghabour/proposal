<!DOCTYPE html>
<html lang="ar" dir="rtl">
<head>
  <meta charset="UTF-8">
  <meta name="viewport" content="width=device-width, initial-scale=1.0">
  <meta name="csrf-token" content="{{ csrf_token() }}">
  <title>لوحة تحكم العروض الذكية بالذكاء الاصطناعي | {{ $settings->agency_name ?? 'وكالة فكرتك الرقمية' }}</title>

  <!-- Tailwind CSS CDN -->
  <script src="https://cdn.tailwindcss.com"></script>
  <script>
    tailwind.config = {
      theme: {
        extend: {
          colors: {
            brand: {
              navy: '#12263A',
              navyDark: '#0b1926',
              navyLight: '#1b3854',
              orange: '#F28822',
              orangeHover: '#d97316',
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

  <!-- Alpine.js 3 -->
  <script defer src="https://cdn.jsdelivr.net/npm/alpinejs@3.14.8/dist/cdn.min.js"></script>

  <style>
    @font-face {
      font-family: 'Thmanyah';
      src: url('/assets/fonts/thmanyahsans-Bold.woff2') format('woff2');
      font-weight: 700;
      font-style: normal;
      font-display: swap;
    }
    body {
      font-family: 'Cairo', system-ui, -apple-system, sans-serif;
      background-color: #f8fafc;
      color: #1e293b;
    }
    h1, h2, h3, h4, .font-heading {
      font-family: 'Thmanyah', 'Cairo', sans-serif;
    }
    [x-cloak] { display: none !important; }
  </style>
</head>
<body class="bg-slate-100 min-h-screen text-slate-800" x-data="proposalDashboard()" x-init="initDashboard()">

  <!-- Toast Notification -->
  <div x-cloak x-show="toast.show" 
       x-transition:enter="transition ease-out duration-300"
       x-transition:enter-start="opacity-0 transform translate-y-4"
       x-transition:enter-end="opacity-100 transform translate-y-0"
       x-transition:leave="transition ease-in duration-200"
       x-transition:leave-start="opacity-100 transform translate-y-0"
       x-transition:leave-end="opacity-0 transform translate-y-4"
       class="fixed bottom-6 right-6 z-50 flex items-center gap-3 px-5 py-3 rounded-xl shadow-2xl text-xs font-bold text-white"
       :class="toast.type === 'error' ? 'bg-red-600' : (toast.type === 'info' ? 'bg-[#1b83bf]' : 'bg-[#12263A]')">
    <span x-text="toast.type === 'error' ? '⚠️' : '✓'"></span>
    <span x-text="toast.message"></span>
  </div>

  <!-- App Wrapper (Sidebar + Main Content Area) -->
  <div class="min-h-screen flex flex-col lg:flex-row">

    <!-- Mobile Header Bar -->
    <header class="lg:hidden bg-[#12263A] text-white p-4 flex items-center justify-between sticky top-0 z-40 border-b border-white/10 shadow-md">
      <div class="flex items-center gap-2.5">
        <div class="bg-white p-1 rounded-lg">
          <img src="{{ $settings->logo_url ?? '/assets/logo_fkretk.png' }}" alt="{{ $settings->agency_name }}" class="h-6 w-auto object-contain">
        </div>
        <span class="font-black text-sm">{{ $settings->agency_name }}</span>
      </div>
      <button @click="mobileSidebarOpen = !mobileSidebarOpen" class="p-2 rounded-lg bg-white/10 text-white text-base">
        ☰
      </button>
    </header>

    <!-- Sidebar Backdrop on Mobile -->
    <div x-show="mobileSidebarOpen" @click="mobileSidebarOpen = false" 
         class="fixed inset-0 bg-black/60 z-40 lg:hidden" x-cloak></div>

    <!-- ======================================================== -->
    <!-- SIDEBAR NAVIGATION -->
    <!-- ======================================================== -->
    <aside :class="mobileSidebarOpen ? 'translate-x-0' : 'translate-x-full lg:translate-x-0'"
           class="fixed lg:sticky top-0 right-0 h-screen w-72 bg-[#12263A] text-white flex flex-col justify-between p-5 z-50 transition-transform duration-300 ease-in-out shadow-2xl border-l border-white/10">
      
      <!-- Top Brand Section -->
      <div class="space-y-6">
        
        <!-- Logo & Agency Info -->
        <div class="flex items-center justify-between pb-4 border-b border-white/10">
          <div class="flex items-center gap-3">
            <div class="bg-white p-2 rounded-xl shadow-md border border-white/20 flex items-center justify-center shrink-0">
              <img src="{{ $settings->logo_url ?? '/assets/logo_fkretk.png' }}" alt="{{ $settings->agency_name }}" class="h-8 w-auto object-contain">
            </div>
            <div>
              <div class="flex items-center gap-1.5">
                <h2 class="font-black text-xs sm:text-sm tracking-wide">{{ $settings->agency_name }}</h2>
              </div>
              <span class="inline-block mt-0.5 text-[9px] font-bold px-1.5 py-0.2 rounded bg-gradient-to-r from-[#F28822] to-[#d97316] text-white">
                AI Proposal Engine
              </span>
            </div>
          </div>
          <button @click="mobileSidebarOpen = false" class="lg:hidden text-slate-400 hover:text-white text-sm">✕</button>
        </div>

        <!-- Quick AI Generation Action Button -->
        <button @click="startNewWizard(); mobileSidebarOpen = false" 
                class="w-full py-3 px-4 rounded-xl bg-gradient-to-r from-[#F28822] to-[#d97316] hover:brightness-110 active:brightness-95 text-white font-bold text-xs transition-all shadow-lg flex items-center justify-center gap-2 group">
          <span class="text-base group-hover:rotate-12 transition-transform">✨</span>
          <span>إنشاء بروبوزل بالذكاء الاصطناعي</span>
        </button>

        <!-- Main Navigation Links -->
        <nav class="space-y-1.5 text-xs font-bold">
          
          <!-- 1. All Proposals -->
          <button @click="currentTab = 'overview'; mobileSidebarOpen = false" 
                  :class="currentTab === 'overview' ? 'bg-[#1b83bf] text-white shadow-md' : 'text-slate-300 hover:bg-white/10 hover:text-white'"
                  class="w-full flex items-center justify-between px-3.5 py-2.5 rounded-xl transition-all">
            <div class="flex items-center gap-2.5">
              <span class="text-base">📊</span>
              <span>لوحة العروض والمشاريع</span>
            </div>
            <span class="px-2 py-0.5 rounded-full text-[10px] bg-white/20 text-white font-black" x-text="proposals.length"></span>
          </button>

          <!-- 2. Wizard Link -->
          <button @click="startNewWizard(); mobileSidebarOpen = false" 
                  :class="currentTab === 'wizard' ? 'bg-[#1b83bf] text-white shadow-md' : 'text-slate-300 hover:bg-white/10 hover:text-white'"
                  class="w-full flex items-center justify-between px-3.5 py-2.5 rounded-xl transition-all">
            <div class="flex items-center gap-2.5">
              <span class="text-base">🤖</span>
              <span>معالج التوليد الذكي (Wizard)</span>
            </div>
            <span class="text-[10px] text-[#F28822] font-black">AI 3.0</span>
          </button>

          <!-- 3. Industry Templates -->
          <button @click="currentTab = 'templates'; mobileSidebarOpen = false" 
                  :class="currentTab === 'templates' ? 'bg-[#1b83bf] text-white shadow-md' : 'text-slate-300 hover:bg-white/10 hover:text-white'"
                  class="w-full flex items-center justify-between px-3.5 py-2.5 rounded-xl transition-all">
            <div class="flex items-center gap-2.5">
              <span class="text-base">⚡</span>
              <span>القوالب الجاهزة المتخصصة</span>
            </div>
            <span class="px-1.5 py-0.5 rounded bg-emerald-500/20 text-emerald-300 text-[10px] font-bold" x-text="templates.length + ' قوالب'"></span>
          </button>

          <!-- 4. Company Settings & Gemini -->
          <button @click="currentTab = 'settings'; mobileSidebarOpen = false" 
                  :class="currentTab === 'settings' ? 'bg-[#1b83bf] text-white shadow-md' : 'text-slate-300 hover:bg-white/10 hover:text-white'"
                  class="w-full flex items-center justify-between px-3.5 py-2.5 rounded-xl transition-all">
            <div class="flex items-center gap-2.5">
              <span class="text-base">⚙️</span>
              <span>إعدادات الوكالة و Gemini</span>
            </div>
          </button>

        </nav>

      </div>

      <!-- Bottom User Profile & Logout -->
      <div class="pt-4 border-t border-white/10 space-y-3">
        <div class="flex items-center justify-between">
          <div class="flex items-center gap-2.5">
            <div class="w-8 h-8 rounded-full bg-gradient-to-tr from-[#1b83bf] to-[#F28822] text-white flex items-center justify-center font-bold text-xs shadow-md">
              {{ mb_substr(Auth::user()->name ?? 'م', 0, 1) }}
            </div>
            <div class="text-right">
              <div class="text-xs font-bold text-white">{{ Auth::user()->name ?? 'المدير' }}</div>
              <div class="text-[10px] text-slate-400">مسؤول النظام</div>
            </div>
          </div>

          <!-- Logout Form -->
          <form action="{{ route('logout') }}" method="POST" class="inline">
            @csrf
            <button type="submit" title="تسجيل الخروج" 
                    class="p-2 rounded-xl bg-red-500/10 hover:bg-red-500/25 text-red-300 hover:text-red-200 text-xs transition-all border border-red-500/20">
              🚪
            </button>
          </form>
        </div>

        <div class="text-[10px] text-slate-500 text-center">
          وكالة فكرتك الرقمية © {{ date('Y') }}
        </div>
      </div>

    </aside>

    <!-- ======================================================== -->
    <!-- MAIN CONTENT AREA -->
    <!-- ======================================================== -->
    <main class="flex-1 p-4 sm:p-6 lg:p-8 max-w-7xl w-full">

      <!-- ========================================== -->
      <!-- TAB 1: OVERVIEW & PROPOSALS LIST -->
      <!-- ========================================== -->
      <div x-show="currentTab === 'overview'" x-transition class="space-y-6">
        
        <!-- Header Title Bar -->
        <div class="flex flex-col sm:flex-row justify-between items-start sm:items-center gap-3">
          <div>
            <h2 class="text-xl sm:text-2xl font-black text-[#12263A]">لوحة العروض والمشاريع</h2>
            <p class="text-xs text-slate-500 mt-0.5">إدارة ومتابعة جميع العروض الفنية والمالية ونسب التفاعل ومبيعات العملاء.</p>
          </div>

          <button @click="startNewWizard()" class="px-4 py-2.5 rounded-xl bg-gradient-to-r from-[#F28822] to-[#d97316] text-white text-xs font-bold hover:brightness-110 active:brightness-95 transition-all shadow-md flex items-center gap-1.5">
            <span>✨</span>
            <span>إنشاء عرض جديد بالـ AI</span>
          </button>
        </div>

        <!-- Stats Summary Grid -->
        <div class="grid grid-cols-2 lg:grid-cols-4 gap-3 sm:gap-4">
          
          <div class="bg-white rounded-2xl p-4 sm:p-5 border border-slate-200/80 shadow-sm flex items-center justify-between">
            <div>
              <span class="text-[11px] font-bold text-slate-500">إجمالي العروض</span>
              <h3 class="text-xl sm:text-2xl font-black text-[#12263A] mt-1" x-text="stats.total"></h3>
            </div>
            <div class="w-10 h-10 rounded-xl bg-blue-50 text-[#1b83bf] flex items-center justify-center text-lg font-bold">
              📁
            </div>
          </div>

          <div class="bg-white rounded-2xl p-4 sm:p-5 border border-slate-200/80 shadow-sm flex items-center justify-between">
            <div>
              <span class="text-[11px] font-bold text-slate-500">عروض منشورة ونشطة</span>
              <h3 class="text-xl sm:text-2xl font-black text-[#1b83bf] mt-1" x-text="stats.published"></h3>
            </div>
            <div class="w-10 h-10 rounded-xl bg-blue-50 text-[#1b83bf] flex items-center justify-center text-lg font-bold">
              🌐
            </div>
          </div>

          <div class="bg-white rounded-2xl p-4 sm:p-5 border border-slate-200/80 shadow-sm flex items-center justify-between">
            <div>
              <span class="text-[11px] font-bold text-slate-500">عروض تم اعتمادها (أرباح)</span>
              <h3 class="text-xl sm:text-2xl font-black text-emerald-600 mt-1" x-text="stats.accepted"></h3>
            </div>
            <div class="w-10 h-10 rounded-xl bg-emerald-50 text-emerald-600 flex items-center justify-center text-lg font-bold">
              🎉
            </div>
          </div>

          <div class="bg-white rounded-2xl p-4 sm:p-5 border border-slate-200/80 shadow-sm flex items-center justify-between">
            <div>
              <span class="text-[11px] font-bold text-slate-500">مشاهدات العملاء للروابط</span>
              <h3 class="text-xl sm:text-2xl font-black text-[#F28822] mt-1" x-text="stats.views"></h3>
            </div>
            <div class="w-10 h-10 rounded-xl bg-orange-50 text-[#F28822] flex items-center justify-center text-lg font-bold">
              👁️
            </div>
          </div>

        </div>

        <!-- Controls & Search Bar -->
        <div class="bg-white rounded-2xl p-4 border border-slate-200/80 shadow-sm flex flex-col sm:flex-row justify-between items-center gap-3">
          <div class="flex items-center gap-2 w-full sm:w-auto">
            <input type="text" x-model="searchQuery" placeholder="بحث باسم العميل أو عنوان المشروع..." 
                   class="w-full sm:w-80 px-3.5 py-2 text-xs border border-slate-200 rounded-xl focus:outline-none focus:border-[#1b83bf]">
            <select x-model="filterStatus" class="px-3 py-2 text-xs border border-slate-200 rounded-xl focus:outline-none focus:border-[#1b83bf] bg-white">
              <option value="all">جميع الحالات</option>
              <option value="published">منشور</option>
              <option value="accepted">معتمد من العميل</option>
              <option value="draft">مسودة</option>
            </select>
          </div>
        </div>

        <!-- Proposals Cards Grid -->
        <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-3 gap-4">
          
          <template x-for="proposal in filteredProposals" :key="proposal.id">
            <div class="bg-white rounded-2xl p-5 border border-slate-200/80 shadow-sm hover:shadow-md transition-all flex flex-col justify-between space-y-4 relative overflow-hidden">
              
              <!-- Subtle Brand Color Accent Border -->
              <div class="absolute top-0 left-0 right-0 h-1.5" :style="'background-color: ' + (proposal.brand_color || '#1b83bf')"></div>

              <div class="space-y-2 pt-1">
                <div class="flex items-start justify-between gap-2">
                  <span class="px-2.5 py-0.5 rounded-full text-[10px] font-bold"
                        :class="proposal.status === 'accepted' ? 'bg-emerald-100 text-emerald-700' : 'bg-blue-100 text-[#1b83bf]'"
                        x-text="proposal.status === 'accepted' ? '✓ تم اعتماد العرض' : 'منشور للعميل'">
                  </span>
                  <div class="flex items-center gap-1 text-[11px] text-slate-400">
                    <span>👁️</span>
                    <span x-text="proposal.views_count + ' مشاهدة'"></span>
                  </div>
                </div>

                <div class="flex items-center gap-2">
                  <template x-if="proposal.client_logo_url">
                    <img :src="proposal.client_logo_url" class="h-6 w-auto max-w-[60px] object-contain rounded border p-0.5 bg-white">
                  </template>
                  <h4 class="font-bold text-[#12263A] text-sm leading-snug line-clamp-2" x-text="proposal.title"></h4>
                </div>
                
                <div class="text-xs text-slate-500 space-y-1 pt-1">
                  <div class="flex items-center gap-1.5">
                    <span class="text-slate-400">العميل:</span>
                    <strong class="text-slate-700" x-text="proposal.client_name"></strong>
                    <span x-show="proposal.client_company" class="text-slate-400 text-[10px]" x-text="'(' + proposal.client_company + ')'"></span>
                  </div>
                  <div class="flex items-center justify-between text-[11px] text-slate-400">
                    <span x-text="'تاريخ الإصدار: ' + proposal.created_at"></span>
                    <span x-show="proposal.last_viewed_at" x-text="'آخر زيارة: ' + proposal.last_viewed_at"></span>
                  </div>
                </div>
              </div>

              <!-- Actions Row -->
              <div class="pt-3 border-t border-slate-100 flex flex-wrap items-center justify-between gap-2">
                <div class="flex items-center gap-1">
                  <button @click="copyShareLink(proposal.share_url)" 
                          title="نسخ الرابط المباشر للعميل" 
                          class="px-2.5 py-1.5 rounded-lg bg-slate-100 hover:bg-slate-200 active:bg-slate-300 text-slate-700 text-xs font-semibold flex items-center gap-1 transition-all">
                    <span>🔗</span>
                    <span>نسخ الرابط</span>
                  </button>

                  <a :href="'https://wa.me/?text=' + encodeURIComponent(proposal.whatsapp_text)" 
                     target="_blank"
                     title="إرسال عبر واتساب"
                     class="px-2.5 py-1.5 rounded-lg bg-[#25D366]/10 hover:bg-[#25D366]/20 text-[#128C7E] text-xs font-semibold flex items-center gap-1 transition-all">
                    <span>💬</span>
                    <span>واتساب</span>
                  </a>
                </div>

                <div class="flex items-center gap-1">
                  <a :href="proposal.share_url" target="_blank" title="معاينة العرض كعميل" class="p-1.5 rounded-lg text-slate-500 hover:text-[#1b83bf] hover:bg-slate-100">
                    👁️
                  </a>
                  <button @click="duplicateProposal(proposal.id)" title="استنساخ العرض" class="p-1.5 rounded-lg text-slate-500 hover:text-[#1b83bf] hover:bg-slate-100">
                    📑
                  </button>
                  <button @click="deleteProposal(proposal.id)" title="حذف العرض" class="p-1.5 rounded-lg text-slate-400 hover:text-red-600 hover:bg-red-50">
                    🗑️
                  </button>
                </div>
              </div>

            </div>
          </template>

        </div>

        <!-- Empty State -->
        <div x-show="filteredProposals.length === 0" class="text-center py-12 bg-white rounded-2xl border border-dashed border-slate-300 p-8 space-y-3">
          <div class="text-4xl">📂</div>
          <h4 class="font-bold text-base text-[#12263A]">لا توجد عروض مطابقة للبحث</h4>
          <p class="text-xs text-slate-500">ابدأ الآن في صياغة أول عرض فني ومالي مدعوم بـ Gemini AI بنقرة زر واحدة.</p>
          <button @click="startNewWizard()" class="px-5 py-2.5 rounded-xl bg-gradient-to-r from-[#F28822] to-[#d97316] text-white text-xs font-bold shadow-md">
            إنشاء عرض جديد الآن
          </button>
        </div>

      </div>

      <!-- ========================================== -->
      <!-- TAB 2: INDUSTRY TEMPLATES BROWSER -->
      <!-- ========================================== -->
      <div x-show="currentTab === 'templates'" x-transition class="space-y-6">
        <div>
          <h2 class="text-xl sm:text-2xl font-black text-[#12263A]">القوالب الجاهزة حسب تخصص المشاريع</h2>
          <p class="text-xs text-slate-500 mt-0.5">
            نماذج فنية واستثمارية مدروسة لأشهر مجالات وخدمات وكالة فكرتك الرقمية. اختر قالباً للانطلاق فوراً وتخصيصه بالـ AI في ثوانٍ.
          </p>
        </div>

        <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-3 gap-5">
          <template x-for="tmpl in templates" :key="tmpl.id">
            <div class="bg-white rounded-2xl p-5 border border-slate-200/80 shadow-sm hover:shadow-md transition-all flex flex-col justify-between space-y-4">
              <div class="space-y-3">
                <div class="flex items-center justify-between">
                  <span class="text-3xl p-2.5 rounded-2xl bg-slate-50 border" x-text="tmpl.icon"></span>
                  <span class="px-2.5 py-0.5 rounded-full text-[10px] font-bold bg-[#1b83bf]/10 text-[#1b83bf]" x-text="tmpl.badge"></span>
                </div>

                <h3 class="font-black text-base text-[#12263A]" x-text="tmpl.name"></h3>
                <p class="text-xs text-slate-500 leading-relaxed" x-text="tmpl.description"></p>

                <!-- Pricing & Timeline Tags -->
                <div class="flex items-center justify-between pt-2 border-t border-slate-100 text-xs">
                  <span class="text-slate-500">المدى التقديري:</span>
                  <strong class="text-[#F28822] font-black" x-text="tmpl.suggested_price_min.toLocaleString() + ' - ' + tmpl.suggested_price_max.toLocaleString() + ' ' + tmpl.currency"></strong>
                </div>

                <div class="flex items-center justify-between text-xs">
                  <span class="text-slate-500">الجدول الزمني:</span>
                  <strong class="text-slate-700" x-text="tmpl.estimated_weeks"></strong>
                </div>

                <!-- Suggested Modules Tags -->
                <div class="flex flex-wrap gap-1.5 pt-2">
                  <template x-for="mod in tmpl.suggested_modules" :key="mod">
                    <span class="px-2 py-0.5 bg-slate-100 rounded text-[10px] text-slate-600 font-medium" x-text="mod"></span>
                  </template>
                </div>
              </div>

              <button @click="applyTemplate(tmpl)" 
                      class="w-full py-2.5 rounded-xl bg-gradient-to-r from-[#12263A] to-[#1b83bf] hover:brightness-110 active:brightness-95 text-white font-bold text-xs transition-all shadow-md flex items-center justify-center gap-1.5">
                <span>🚀</span>
                <span>استخدام هذا القالب وتخصيصه بالـ AI</span>
              </button>
            </div>
          </template>
        </div>
      </div>

      <!-- ========================================== -->
      <!-- TAB 3: AI PROPOSAL WIZARD (3 STEPS) -->
      <!-- ========================================== -->
      <div x-show="currentTab === 'wizard'" x-transition class="space-y-6">
        
        <!-- Wizard Step Header Indicator -->
        <div class="bg-white rounded-2xl p-4 sm:p-5 border border-slate-200/80 shadow-sm flex flex-col sm:flex-row justify-between items-start sm:items-center gap-3">
          <div class="flex items-center gap-2 overflow-x-auto no-scrollbar w-full sm:w-auto">
            <span class="text-xs font-black px-3 py-1 rounded-full whitespace-nowrap"
                  :class="wizardStep === 1 ? 'bg-[#F28822] text-white' : 'bg-slate-100 text-slate-600'">1. فكرة المشروع وتخصيص الهوية</span>
            <span class="text-slate-300">←</span>
            <span class="text-xs font-black px-3 py-1 rounded-full whitespace-nowrap"
                  :class="wizardStep === 2 ? 'bg-[#F28822] text-white' : 'bg-slate-100 text-slate-600'">2. الاستيضاح الذكي وتقدير السعر</span>
            <span class="text-slate-300">←</span>
            <span class="text-xs font-black px-3 py-1 rounded-full whitespace-nowrap"
                  :class="wizardStep === 3 ? 'bg-[#1b83bf] text-white' : 'bg-slate-100 text-slate-600'">3. مراجعة وتخصيص العرض الكامل</span>
          </div>

          <button @click="currentTab = 'overview'" class="text-xs text-slate-400 hover:text-slate-600 font-bold shrink-0">
            إلغاء والعودة ✕
          </button>
        </div>

        <!-- WIZARD STEP 1: Input Idea, Co-Branding & Templates -->
        <div x-show="wizardStep === 1" class="space-y-5">
          
          <!-- QUICK TEMPLATE SELECTOR CAROUSEL/GRID -->
          <div class="bg-white rounded-2xl p-5 border border-slate-200/80 shadow-sm space-y-3">
            <div class="flex items-center justify-between">
              <div class="flex items-center gap-2">
                <span class="text-base">⚡</span>
                <h4 class="font-black text-xs sm:text-sm text-[#12263A]">قوالب سريعة جاهزة حسب تخصص المشاريع (اختياري)</h4>
              </div>
              <span class="text-[10px] text-slate-400">انقر على أي تخصص لتعبئة الفكرة ونطاق العمل فوراً</span>
            </div>

            <div class="grid grid-cols-2 sm:grid-cols-3 lg:grid-cols-5 gap-2.5">
              <template x-for="tmpl in templates" :key="tmpl.id">
                <button type="button" @click="applyTemplate(tmpl)" 
                        class="p-3 rounded-xl border text-right transition-all flex flex-col justify-between gap-1 hover:border-[#F28822] hover:bg-orange-50/30 group"
                        :class="selectedTemplateSlug === tmpl.slug ? 'border-[#F28822] bg-orange-50/60 ring-2 ring-[#F28822]/20' : 'border-slate-200 bg-slate-50/50'">
                  <div class="flex items-center justify-between w-full">
                    <span class="text-xl" x-text="tmpl.icon"></span>
                    <span class="text-[9px] font-bold px-1 rounded bg-white text-slate-500" x-text="tmpl.currency"></span>
                  </div>
                  <span class="font-bold text-xs text-[#12263A] group-hover:text-[#F28822] line-clamp-1" x-text="tmpl.name"></span>
                  <span class="text-[10px] text-slate-400 line-clamp-1" x-text="tmpl.badge"></span>
                </button>
              </template>
            </div>
          </div>

          <!-- CLIENT INFO & CO-BRANDING CARD -->
          <div class="bg-white rounded-2xl p-6 border border-slate-200/80 shadow-sm space-y-5">
            <div>
              <h3 class="text-base font-black text-[#12263A] flex items-center gap-2">
                <span>🏢</span> بيانات العميل والتخصيص المشترك لهويته (Co-Branding)
              </h3>
              <p class="text-xs text-slate-500 mt-0.5">
                يمكنك رفع شعار العميل واختيار اللون الرئيسي لهويته ليظهر العرض بشكل مخصص ومبهر حصرياً له.
              </p>
            </div>

            <!-- Client & Sales Fields -->
            <div class="grid grid-cols-1 sm:grid-cols-2 lg:grid-cols-4 gap-4 text-xs">
              <div>
                <label class="block font-bold text-[#12263A] mb-1">اسم العميل / متخذ القرار: *</label>
                <input type="text" x-model="wizardData.client_name" placeholder="مثال: شركة الرعاية والجمال" class="w-full px-3 py-2 border border-slate-200 rounded-lg focus:outline-none focus:border-[#1b83bf]">
              </div>

              <div>
                <label class="block font-bold text-[#12263A] mb-1">الجهة / اسم الشركة:</label>
                <input type="text" x-model="wizardData.client_company" placeholder="مثال: للتجارة والتوزيع المحدودة" class="w-full px-3 py-2 border border-slate-200 rounded-lg focus:outline-none focus:border-[#1b83bf]">
              </div>

              <div>
                <label class="block font-bold text-[#12263A] mb-1">عملة العرض:</label>
                <select x-model="wizardData.currency" class="w-full px-3 py-2 border border-slate-200 rounded-lg focus:outline-none focus:border-[#1b83bf] bg-white font-bold">
                  <option value="ريال">ريال سعودي (SAR)</option>
                  <option value="جنيه مصري">جنيه مصري (EGP)</option>
                  <option value="درهم إماراتي">درهم إماراتي (AED)</option>
                  <option value="دولار أمريكي">دولار أمريكي (USD)</option>
                  <option value="دينار كويتي">دينار كويتي (KWD)</option>
                </select>
              </div>

              <div>
                <label class="block font-bold text-[#12263A] mb-1">مسؤول المبيعات المتابع:</label>
                <input type="text" x-model="wizardData.sales_rep_name" placeholder="اسمك أو فريق المبيعات" class="w-full px-3 py-2 border border-slate-200 rounded-lg focus:outline-none focus:border-[#1b83bf]">
              </div>
            </div>

            <!-- Co-Branding Fields: Client Logo & Client Color Palette -->
            <div class="p-4 rounded-xl bg-slate-50/70 border border-slate-200 space-y-3">
              <div class="text-xs font-bold text-[#12263A] flex items-center gap-2">
                <span>🎨</span> تخصيص الهوية المشتركة (شعار العميل + اللون المميز)
              </div>

              <div class="grid grid-cols-1 sm:grid-cols-12 gap-3 text-xs items-center">
                <!-- Client Logo Input -->
                <div class="sm:col-span-7">
                  <label class="block font-bold text-slate-700 mb-1">رابط أو مسار شعار العميل (Client Logo):</label>
                  <div class="flex items-center gap-2">
                    <input type="text" x-model="wizardData.client_logo_url" placeholder="https://example.com/logo.png" 
                           class="w-full px-3 py-2 border border-slate-200 rounded-lg bg-white focus:outline-none focus:border-[#1b83bf]">
                    <template x-if="wizardData.client_logo_url">
                      <div class="w-9 h-9 rounded-lg bg-white border p-1 shrink-0 flex items-center justify-center shadow-sm">
                        <img :src="wizardData.client_logo_url" class="max-h-full max-w-full object-contain">
                      </div>
                    </template>
                  </div>
                </div>

                <!-- Client Brand Color Picker & Quick Palettes -->
                <div class="sm:col-span-5">
                  <label class="block font-bold text-slate-700 mb-1">اللون الرئيسي المميز للعميل:</label>
                  <div class="flex items-center gap-2">
                    <input type="color" x-model="wizardData.brand_color" class="w-9 h-9 p-0.5 rounded-lg border border-slate-200 cursor-pointer shrink-0">
                    <input type="text" x-model="wizardData.brand_color" class="w-24 px-2 py-2 border border-slate-200 rounded-lg bg-white text-center font-mono font-bold text-xs">
                    
                    <!-- Quick Preset Dots -->
                    <div class="flex items-center gap-1.5 pr-1">
                      <button type="button" @click="wizardData.brand_color = '#1b83bf'" class="w-5 h-5 rounded-full bg-[#1b83bf] ring-1 ring-slate-300" title="أزرق فكرتك"></button>
                      <button type="button" @click="wizardData.brand_color = '#10b981'" class="w-5 h-5 rounded-full bg-[#10b981] ring-1 ring-slate-300" title="أخضر زمردي"></button>
                      <button type="button" @click="wizardData.brand_color = '#8b5cf6'" class="w-5 h-5 rounded-full bg-[#8b5cf6] ring-1 ring-slate-300" title="بنفسجي"></button>
                      <button type="button" @click="wizardData.brand_color = '#F28822'" class="w-5 h-5 rounded-full bg-[#F28822] ring-1 ring-slate-300" title="برتقالي"></button>
                      <button type="button" @click="wizardData.brand_color = '#e11d48'" class="w-5 h-5 rounded-full bg-[#e11d48] ring-1 ring-slate-300" title="قرمزي"></button>
                    </div>
                  </div>
                </div>
              </div>
            </div>

            <!-- Project Raw Idea Textarea -->
            <div>
              <label class="block font-bold text-[#12263A] text-xs mb-1">فكرة المشروع والمتطلبات التي طلبها العميل: *</label>
              <textarea x-model="wizardData.raw_idea" rows="4" 
                        placeholder="اكتب فكرة المشروع بالتفصيل أو اختر أحد القوالب الجاهزة أعلاه... مثال: العميل يريد تطبيق ومنصة لتوصيل وتوزيع مستحضرات التجميل والأدوية بالجملة للصيدليات مع نظام اعتماد تراخيص الصيدليات وعروض الدرزن والتسترات المجانية وربط المناديب مع التحصيل." 
                        class="w-full p-3 text-xs border border-slate-200 rounded-xl focus:outline-none focus:border-[#1b83bf] leading-relaxed"></textarea>
            </div>

            <!-- Action Row -->
            <div class="flex flex-col sm:flex-row items-center justify-between gap-3 pt-2">
              <div class="flex flex-wrap items-center gap-4">
                <div class="flex items-center gap-2">
                  <input type="checkbox" id="chkQuickMode" x-model="wizardData.quick_mode" class="rounded text-[#F28822]">
                  <label for="chkQuickMode" class="text-xs text-slate-600 cursor-pointer">
                    توليد فوري مباشر (تخطي خطوة الأسئلة)
                  </label>
                </div>

                <!-- AI Model Selector for Wizard -->
                <div class="flex items-center gap-2 text-xs">
                  <span class="text-slate-500 font-semibold flex items-center gap-1">
                    <span>⚡</span> موديل الـ AI:
                  </span>
                  <select x-model="wizardData.model" class="px-2.5 py-1.5 border border-slate-200 rounded-lg text-xs bg-white text-slate-700 font-bold focus:outline-none focus:border-[#1b83bf]">
                    <option value="">تلقائي من الإعدادات (<span x-text="settingsData.gemini_model"></span>)</option>
                    <option value="gemini-3.8-flash">⚡ Gemini 3.8 Flash (الأحدث)</option>
                    <option value="gemini-2.0-flash">Gemini 2.0 Flash</option>
                    <option value="gemini-1.5-flash-8b">Gemini 1.5 Flash-8B</option>
                    <option value="gemini-1.5-flash">Gemini 1.5 Flash</option>
                    <option value="gemini-2.5-flash">Gemini 2.5 Flash</option>
                    <option value="gemini-1.5-pro">Gemini 1.5 Pro</option>
                  </select>
                </div>
              </div>

              <button @click="runAnalysisStep()" 
                      :disabled="loading.analyzing || !wizardData.raw_idea" 
                      class="w-full sm:w-auto px-6 py-2.5 rounded-xl bg-gradient-to-r from-[#F28822] to-[#d97316] text-white text-xs font-bold hover:brightness-110 active:brightness-95 transition-all shadow-md disabled:opacity-50 flex items-center justify-center gap-2">
                <span x-show="!loading.analyzing">🤖 تحليل الفكرة واقتراح الأسعار بالـ AI</span>
                <span x-show="loading.analyzing">جاري التحليل عبر Gemini...</span>
                <div x-show="loading.analyzing" class="w-4 h-4 border-2 border-white border-t-transparent rounded-full animate-spin"></div>
              </button>
            </div>
          </div>

        </div>

        <!-- WIZARD STEP 2: Smart Clarifications & AI Pricing -->
        <div x-show="wizardStep === 2" class="space-y-5">
          
          <!-- Gemini Analysis Banner -->
          <div class="bg-gradient-to-br from-[#12263A] to-[#1b83bf] text-white rounded-2xl p-5 shadow-lg space-y-3">
            <div class="flex items-center justify-between">
              <div class="flex items-center gap-2">
                <span class="text-xl">✨</span>
                <h4 class="font-bold text-sm sm:text-base">تحليل Gemini الذكي لمشروع: <span x-text="wizardData.client_name"></span></h4>
              </div>
              <span class="text-[10px] px-2.5 py-0.5 rounded-full bg-white/20 text-white font-bold" x-text="'موديل: ' + (activeModelUsed || settingsData.gemini_model)"></span>
            </div>
            
            <p class="text-xs text-slate-200 leading-relaxed max-w-3xl" x-text="analysisResult.summary"></p>

            <div class="grid grid-cols-1 sm:grid-cols-3 gap-3 pt-2 text-xs">
              <div class="bg-white/10 rounded-xl p-3 border border-white/15">
                <span class="text-[10px] text-slate-300">المدى السعري المقترح من AI:</span>
                <div class="font-black text-sm text-[#fbbc04] mt-0.5" 
                     x-text="analysisResult.suggested_price_min + ' - ' + analysisResult.suggested_price_max + ' ' + wizardData.currency"></div>
              </div>
              <div class="bg-white/10 rounded-xl p-3 border border-white/15">
                <span class="text-[10px] text-slate-300">الجدول الزمني التقديري للإطلاق:</span>
                <div class="font-black text-sm text-white mt-0.5" x-text="analysisResult.estimated_weeks || '6 - 8 أسابيع'"></div>
              </div>
              <div class="bg-white/10 rounded-xl p-3 border border-white/15">
                <span class="text-[10px] text-slate-300">تعديل السعر الأساسي المقترح:</span>
                <input type="number" x-model="wizardData.custom_price" :placeholder="analysisResult.suggested_price_min" 
                       class="w-full mt-1 px-2 py-1 bg-white/20 text-white rounded text-xs font-bold border border-white/20 focus:outline-none">
              </div>
            </div>
          </div>

          <!-- Clarifying Questions Form -->
          <div class="bg-white rounded-2xl p-6 border border-slate-200/80 shadow-sm space-y-5">
            <div>
              <h4 class="text-base font-black text-[#12263A] flex items-center gap-2">
                <span>❓</span> أسئلة الاستيضاح الذكية لمسؤول المبيعات
              </h4>
              <p class="text-xs text-slate-500 mt-0.5">
                بناءً على فكرة المشروع، تم إعداد هذه الأسئلة لضبط نطاق العمل بدقة. يمكنك اختيار المقترح أو كتابة تخصيص:
              </p>
            </div>

            <div class="space-y-4">
              <template x-for="(q, idx) in analysisResult.questions" :key="q.id || idx">
                <div class="p-4 rounded-xl border border-slate-200 bg-slate-50/50 space-y-2.5">
                  <div class="flex items-center gap-2">
                    <span class="w-6 h-6 rounded-full bg-[#1b83bf] text-white flex items-center justify-center text-[10px] font-bold" x-text="idx + 1"></span>
                    <label class="font-bold text-xs text-[#12263A]" x-text="q.question"></label>
                  </div>

                  <div class="grid grid-cols-1 sm:grid-cols-3 gap-2 text-xs pt-1">
                    <template x-for="opt in q.options" :key="opt">
                      <label class="flex items-center gap-2 p-2.5 rounded-lg border cursor-pointer transition-all text-xs"
                             :class="clarificationAnswers[q.id] === opt ? 'bg-orange-50 border-[#F28822] text-[#F28822] font-bold shadow-sm' : 'bg-white border-slate-200 text-slate-700 hover:bg-slate-50'">
                        <input type="radio" :name="'q_' + q.id" :value="opt" x-model="clarificationAnswers[q.id]" class="text-[#F28822]">
                        <span x-text="opt"></span>
                      </label>
                    </template>
                  </div>
                </div>
              </template>
            </div>

            <!-- Action Buttons -->
            <div class="flex items-center justify-between pt-2 border-t border-slate-100">
              <button @click="wizardStep = 1" class="px-4 py-2 rounded-xl bg-slate-100 text-slate-700 text-xs font-bold hover:bg-slate-200">
                ← الرجوع لتعديل الفكرة
              </button>

              <button @click="generateFullProposal()" 
                      :disabled="loading.generating"
                      class="px-6 py-2.5 rounded-xl bg-gradient-to-r from-[#1b83bf] to-[#12263A] text-white text-xs font-bold hover:brightness-110 active:brightness-95 transition-all shadow-md flex items-center gap-2">
                <span x-show="!loading.generating">🚀 توليد العرض الفني والمالي الكامل الآن</span>
                <span x-show="loading.generating">جاري بناء العرض بـ Gemini (يرجى الانتظار ثوانٍ)...</span>
                <div x-show="loading.generating" class="w-4 h-4 border-2 border-white border-t-transparent rounded-full animate-spin"></div>
              </button>
            </div>

          </div>

        </div>

        <!-- WIZARD STEP 3: Review, Customize & Save Full Proposal -->
        <div x-show="wizardStep === 3" class="space-y-6">
          
          <!-- Success Banner & Direct Links -->
          <div class="bg-emerald-50 border border-emerald-200 rounded-2xl p-5 flex flex-col sm:flex-row justify-between items-center gap-4">
            <div class="flex items-center gap-3">
              <div class="w-10 h-10 rounded-full bg-emerald-500 text-white flex items-center justify-center text-xl shrink-0">
                ✓
              </div>
              <div>
                <h4 class="font-black text-[#12263A] text-sm sm:text-base">تم تجهيز العرض بالكامل ومراجعته بنجاح!</h4>
                <p class="text-xs text-slate-600 mt-0.5">يمكنك حفظ العرض ونشر الرابط للعميل مباشرة، أو تعديل أي موديول أو سعر أدناه.</p>
              </div>
            </div>

            <div class="flex items-center gap-2 w-full sm:w-auto">
              <button @click="saveAndPublishProposal()" 
                      :disabled="loading.saving"
                      class="w-full sm:w-auto px-6 py-2.5 rounded-xl bg-gradient-to-r from-[#F28822] to-[#d97316] text-white font-bold text-xs hover:brightness-110 active:brightness-95 transition-all shadow-md flex items-center justify-center gap-2">
                <span x-show="!loading.saving">💾 حفظ ونشر الرابط للعميل</span>
                <span x-show="loading.saving">جاري الحفظ...</span>
              </button>
            </div>
          </div>

          <!-- Proposal Review Tabs -->
          <div class="bg-white rounded-2xl border border-slate-200/80 shadow-sm overflow-hidden">
            
            <div class="flex items-center border-b border-slate-200 bg-slate-50/70 px-4 overflow-x-auto text-xs font-bold no-scrollbar">
              <button @click="reviewTab = 'modules'" :class="reviewTab === 'modules' ? 'border-b-2 border-[#1b83bf] text-[#1b83bf] bg-white' : 'text-slate-600 hover:text-slate-900'" class="px-4 py-3 transition-all whitespace-nowrap">
                ⚙️ الموديولات والمميزات
              </button>
              <button @click="reviewTab = 'pricing'" :class="reviewTab === 'pricing' ? 'border-b-2 border-[#1b83bf] text-[#1b83bf] bg-white' : 'text-slate-600 hover:text-slate-900'" class="px-4 py-3 transition-all whitespace-nowrap">
                💰 الباقات والأسعار
              </button>
              <button @click="reviewTab = 'solution'" :class="reviewTab === 'solution' ? 'border-b-2 border-[#1b83bf] text-[#1b83bf] bg-white' : 'text-slate-600 hover:text-slate-900'" class="px-4 py-3 transition-all whitespace-nowrap">
                💡 المشكلة والحل المقترح
              </button>
              <button @click="reviewTab = 'workflow'" :class="reviewTab === 'workflow' ? 'border-b-2 border-[#1b83bf] text-[#1b83bf] bg-white' : 'text-slate-600 hover:text-slate-900'" class="px-4 py-3 transition-all whitespace-nowrap">
                🔄 دورة العمل
              </button>
              <button @click="reviewTab = 'timeline'" :class="reviewTab === 'timeline' ? 'border-b-2 border-[#1b83bf] text-[#1b83bf] bg-white' : 'text-slate-600 hover:text-slate-900'" class="px-4 py-3 transition-all whitespace-nowrap">
                📅 مراحل التنفيذ والدفعات
              </button>
            </div>

            <div class="p-6">
              
              <!-- REVIEW: Modules -->
              <div x-show="reviewTab === 'modules'" class="space-y-4">
                <h4 class="font-bold text-xs text-[#12263A] mb-3">موديولات المنظومة التقنية (يمكنك تعديل مسمياتها ومميزاتها):</h4>
                <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
                  <template x-for="(mod, mIdx) in activeProposal.modules" :key="mIdx">
                    <div class="p-4 rounded-xl border border-slate-200 bg-slate-50/50 space-y-3">
                      <div class="flex items-center gap-2">
                        <input type="text" x-model="mod.icon" class="w-10 text-center p-1.5 border border-slate-200 rounded-lg text-sm bg-white">
                        <input type="text" x-model="mod.name" class="flex-1 font-bold text-xs px-2.5 py-1.5 border border-slate-200 rounded-lg bg-white">
                      </div>
                      <input type="text" x-model="mod.tag" placeholder="وصف فرعي للموديول" class="w-full text-[11px] px-2 py-1 border border-slate-200 rounded-lg bg-white text-slate-500">
                      <div class="space-y-1.5 text-xs">
                        <template x-for="(feat, fIdx) in mod.features" :key="fIdx">
                          <div class="flex items-center gap-1.5">
                            <span class="text-emerald-500 text-xs">✔️</span>
                            <input type="text" x-model="mod.features[fIdx]" class="flex-1 text-[11px] px-2 py-1 border border-slate-200 rounded bg-white">
                          </div>
                        </template>
                      </div>
                    </div>
                  </template>
                </div>
              </div>

              <!-- REVIEW: Pricing Packages -->
              <div x-show="reviewTab === 'pricing'" class="space-y-4">
                <h4 class="font-bold text-xs text-[#12263A] mb-3">باقات الاستثمار والأسعار:</h4>
                <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
                  <template x-for="(pkg, pIdx) in activeProposal.pricing_packages" :key="pIdx">
                    <div class="p-5 rounded-xl border-2 space-y-3"
                         :class="pkg.is_recommended ? 'border-[#1b83bf] bg-blue-50/20' : 'border-slate-200 bg-white'">
                      <div class="flex justify-between items-center">
                        <input type="text" x-model="pkg.name" class="font-bold text-sm px-2 py-1 border border-slate-200 rounded bg-white">
                        <label class="text-[11px] font-bold flex items-center gap-1 text-[#F28822]">
                          <input type="checkbox" x-model="pkg.is_recommended" class="rounded">
                          الباقة الموصى بها
                        </label>
                      </div>
                      <div class="flex items-center gap-2">
                        <label class="text-xs text-slate-500">السعر:</label>
                        <input type="text" x-model="pkg.price" class="font-black text-lg px-2 py-1 border border-slate-200 rounded bg-white w-32 text-[#1b83bf]">
                        <input type="text" x-model="pkg.currency" class="text-xs px-2 py-1 border border-slate-200 rounded bg-white w-24">
                      </div>
                      <div class="space-y-1.5 pt-2">
                        <span class="text-[11px] font-bold text-slate-600 block">المميزات المشمولة:</span>
                        <template x-for="(feat, pfIdx) in pkg.features" :key="pfIdx">
                          <input type="text" x-model="pkg.features[pfIdx]" class="w-full text-[11px] px-2 py-1 border border-slate-200 rounded bg-white mb-1">
                        </template>
                      </div>
                    </div>
                  </template>
                </div>
              </div>

              <!-- REVIEW: Problem vs Solution -->
              <div x-show="reviewTab === 'solution'" class="space-y-4 text-xs">
                <div>
                  <label class="block font-bold text-[#12263A] mb-1">عنوان قسم لماذا يحتاج السوق للمشروع:</label>
                  <input type="text" x-model="activeProposal.problem_vs_solution.why_needed_title" class="w-full p-2 border border-slate-200 rounded-lg">
                </div>
                <div>
                  <label class="block font-bold text-[#12263A] mb-1">الشرح التفصيلي:</label>
                  <textarea rows="2" x-model="activeProposal.problem_vs_solution.why_needed_desc" class="w-full p-2 border border-slate-200 rounded-lg"></textarea>
                </div>
                <div class="grid grid-cols-1 md:grid-cols-2 gap-4 pt-2">
                  <div class="p-4 rounded-xl bg-red-50/70 border border-red-200 space-y-2">
                    <span class="font-bold text-red-700 block">الوضع التقليدي (السلبيات):</span>
                    <template x-for="(c, cIdx) in activeProposal.problem_vs_solution.challenges" :key="cIdx">
                      <input type="text" x-model="activeProposal.problem_vs_solution.challenges[cIdx]" class="w-full text-xs p-1.5 border border-red-200 rounded bg-white">
                    </template>
                  </div>
                  <div class="p-4 rounded-xl bg-emerald-50/70 border border-emerald-200 space-y-2">
                    <span class="font-bold text-emerald-800 block">حل وكالة فكرتك (المكاسب):</span>
                    <template x-for="(s, sIdx) in activeProposal.problem_vs_solution.solutions" :key="sIdx">
                      <input type="text" x-model="activeProposal.problem_vs_solution.solutions[sIdx]" class="w-full text-xs p-1.5 border border-emerald-200 rounded bg-white">
                    </template>
                  </div>
                </div>
              </div>

              <!-- REVIEW: Workflow -->
              <div x-show="reviewTab === 'workflow'" class="space-y-3">
                <h4 class="font-bold text-xs text-[#12263A]">خطوات دورة عمل النظام:</h4>
                <div class="grid grid-cols-1 sm:grid-cols-2 lg:grid-cols-4 gap-3">
                  <template x-for="(step, sIdx) in activeProposal.workflow_steps" :key="sIdx">
                    <div class="p-3 rounded-xl border border-slate-200 bg-slate-50 space-y-2">
                      <input type="text" x-model="step.title" class="w-full font-bold text-xs p-1.5 border border-slate-200 rounded bg-white">
                      <textarea rows="3" x-model="step.desc" class="w-full text-[11px] p-1.5 border border-slate-200 rounded bg-white"></textarea>
                    </div>
                  </template>
                </div>
              </div>

              <!-- REVIEW: Timeline & Milestones -->
              <div x-show="reviewTab === 'timeline'" class="space-y-4 text-xs">
                <h4 class="font-bold text-xs text-[#12263A]">مراحل التنفيذ:</h4>
                <div class="grid grid-cols-1 sm:grid-cols-2 lg:grid-cols-4 gap-3">
                  <template x-for="(phase, phIdx) in activeProposal.timeline_phases" :key="phIdx">
                    <div class="p-3 rounded-xl border border-slate-200 bg-slate-50 space-y-2">
                      <input type="text" x-model="phase.duration" class="w-full text-[10px] p-1 border border-slate-200 rounded bg-white font-bold text-slate-500">
                      <input type="text" x-model="phase.title" class="w-full font-bold text-xs p-1.5 border border-slate-200 rounded bg-white">
                      <textarea rows="3" x-model="phase.deliverables" class="w-full text-[11px] p-1.5 border border-slate-200 rounded bg-white"></textarea>
                    </div>
                  </template>
                </div>
              </div>

            </div>

            <!-- Bottom Publish Action -->
            <div class="p-4 bg-slate-50 border-t border-slate-200 flex justify-between items-center">
              <button @click="wizardStep = 2" class="px-4 py-2 rounded-xl bg-slate-200 text-slate-700 text-xs font-bold hover:bg-slate-300">
                ← الرجوع لتعديل الأسئلة
              </button>

              <button @click="saveAndPublishProposal()" 
                      :disabled="loading.saving"
                      class="px-6 py-2.5 rounded-xl bg-gradient-to-r from-[#F28822] to-[#d97316] text-white font-bold text-xs hover:brightness-110 active:brightness-95 transition-all shadow-md flex items-center gap-2">
                <span x-show="!loading.saving">💾 اعتماد العرض ونشر الرابط الرسمي فوراً</span>
                <span x-show="loading.saving">جاري حفظ العرض...</span>
              </button>
            </div>

          </div>

        </div>

      </div>

      <!-- ========================================== -->
      <!-- TAB 4: COMPANY SETTINGS & GEMINI API -->
      <!-- ========================================== -->
      <div x-show="currentTab === 'settings'" x-transition class="space-y-6">
        
        <div>
          <h2 class="text-xl sm:text-2xl font-black text-[#12263A]">إعدادات الوكالة وربط Gemini</h2>
          <p class="text-xs text-slate-500 mt-0.5">تخصيص الهوية الرسمية للوكالة، أرقام التواصل، مفتاح الذكاء الاصطناعي، وأمان الحساب.</p>
        </div>

        <!-- Gemini AI Integration Card -->
        <div class="bg-gradient-to-br from-[#12263A] via-[#1a3854] to-[#1b83bf] text-white rounded-2xl p-6 shadow-xl space-y-4">
          <div class="flex items-center justify-between">
            <div class="flex items-center gap-3">
              <span class="text-2xl">🤖</span>
              <div>
                <h3 class="text-base sm:text-lg font-black">ربط الذكاء الاصطناعي (Google Gemini API)</h3>
                <p class="text-xs text-slate-300">ضع الـ API Key الخاص بك لتوليد العروض الفنية والمالية بدقة فائقة وبشكل فوري.</p>
              </div>
            </div>
            <span class="px-3 py-1 rounded-full text-xs font-bold"
                  :class="settingsData.gemini_api_key ? 'bg-emerald-500/20 text-emerald-300 border border-emerald-500/30' : 'bg-orange-500/20 text-orange-300 border border-orange-500/30'">
              <span x-text="settingsData.gemini_api_key ? '● المفتاح متصل' : '○ لم يتم وضع المفتاح بعد'"></span>
            </span>
          </div>

          <div class="grid grid-cols-1 sm:grid-cols-12 gap-3 pt-2 text-xs">
            <div class="sm:col-span-8">
              <label class="block font-bold text-slate-200 mb-1">Gemini API Key:</label>
              <div class="relative">
                <input :type="showApiKey ? 'text' : 'password'" x-model="settingsData.gemini_api_key" placeholder="AIzaSy..." 
                       class="w-full px-3 py-2.5 bg-white/10 text-white rounded-xl border border-white/20 focus:outline-none focus:border-[#F28822] text-xs font-mono">
                <button type="button" @click="showApiKey = !showApiKey" class="absolute left-3 top-2.5 text-slate-300 hover:text-white text-xs">
                  <span x-text="showApiKey ? 'إخفاء' : 'إظهار'"></span>
                </button>
              </div>
            </div>

            <div class="sm:col-span-4" x-data="{ 
              isCustom: !['gemini-3.8-flash', 'gemini-2.0-flash', 'gemini-1.5-flash-8b', 'gemini-1.5-flash', 'gemini-2.5-flash', 'gemini-1.5-pro'].includes(settingsData.gemini_model),
              syncModelChoice(val) {
                if (val === 'custom') {
                  this.isCustom = true;
                  this.settingsData.gemini_model = '';
                } else {
                  this.isCustom = false;
                  this.settingsData.gemini_model = val;
                }
              }
            }">
              <label class="block font-bold text-slate-200 mb-1">موديل Gemini المعتمد:</label>
              <select :value="isCustom ? 'custom' : settingsData.gemini_model" 
                      @change="syncModelChoice($event.target.value)"
                      class="w-full px-3 py-2.5 bg-[#12263A] text-white rounded-xl border border-white/20 focus:outline-none focus:border-[#F28822] text-xs font-semibold">
                <option value="gemini-3.8-flash">⚡ Gemini 3.8 Flash (الجيل الأحدث فائق الذكاء والسرعة)</option>
                <option value="gemini-2.0-flash">Gemini 2.0 Flash (فائق السرعة والموصى به)</option>
                <option value="gemini-1.5-flash-8b">Gemini 1.5 Flash-8B (فائق الخفة واقتصادي)</option>
                <option value="gemini-1.5-flash">Gemini 1.5 Flash</option>
                <option value="gemini-2.5-flash">Gemini 2.5 Flash</option>
                <option value="gemini-1.5-pro">Gemini 1.5 Pro (تحليلي عالي العمق)</option>
                <option value="custom">✏️ كتابة اسم موديل مخصص يدوياً (Custom Model)...</option>
              </select>

              <div x-show="isCustom" class="mt-2" x-transition>
                <input type="text" x-model="settingsData.gemini_model" placeholder="أدخل اسم الموديل مثل: gemini-3.8-flash أو gemini-exp" 
                       class="w-full px-3 py-2 bg-white/10 text-white placeholder-slate-400 rounded-xl border border-[#F28822]/60 focus:outline-none focus:border-[#F28822] text-xs font-mono">
                <p class="text-[10px] text-[#F28822] mt-1 font-sans">
                  💡 يمكنك استخدام أي موديل جديد أو تجريبي تصدره Google بإدخال معرّفه التقني هنا مباشرة.
                </p>
              </div>
            </div>
          </div>

          <div class="flex items-center justify-between pt-2">
            <button @click="testGeminiConnection()" 
                    :disabled="loading.testingGemini || !settingsData.gemini_api_key"
                    class="px-4 py-2 rounded-xl bg-white/15 hover:bg-white/25 text-white font-bold text-xs transition-all flex items-center gap-1.5 border border-white/20 disabled:opacity-50">
              <span>⚡</span>
              <span x-show="!loading.testingGemini">فحص واختبار عمل المفتاح</span>
              <span x-show="loading.testingGemini">جاري الفحص المباشر...</span>
            </button>

            <span class="text-[11px] text-slate-300">
              يمكنك الحصول على مفتاح مجاني من <a href="https://aistudio.google.com/app/apikey" target="_blank" class="text-[#F28822] underline font-bold">Google AI Studio</a>
            </span>
          </div>
        </div>

        <!-- Company Profile & Branding Form -->
        <div class="bg-white rounded-2xl p-6 border border-slate-200/80 shadow-sm space-y-5">
          <div>
            <h3 class="text-base sm:text-lg font-black text-[#12263A] flex items-center gap-2">
              <span>🏢</span> بيانات وهوية الوكالة الرسمية (Seeder / Defaults)
            </h3>
            <p class="text-xs text-slate-500 mt-0.5">
              تظهر هذه البيانات وشعار الوكالة تلقائياً في جميع العروض المرسلة للعملاء، وتتيح للعميل التواصل واعتماد العرض فوراً.
            </p>
          </div>

          <div class="grid grid-cols-1 sm:grid-cols-2 lg:grid-cols-3 gap-4 text-xs">
            <div>
              <label class="block font-bold text-[#12263A] mb-1">اسم الوكالة / الشركة: *</label>
              <input type="text" x-model="settingsData.agency_name" class="w-full px-3 py-2 border border-slate-200 rounded-lg focus:outline-none focus:border-[#1b83bf]">
            </div>

            <div>
              <label class="block font-bold text-[#12263A] mb-1">الشعار اللفظي (Tagline):</label>
              <input type="text" x-model="settingsData.agency_tagline" class="w-full px-3 py-2 border border-slate-200 rounded-lg focus:outline-none focus:border-[#1b83bf]">
            </div>

            <div>
              <label class="block font-bold text-[#12263A] mb-1">مسار أو رابط اللوجو:</label>
              <input type="text" x-model="settingsData.logo_url" class="w-full px-3 py-2 border border-slate-200 rounded-lg focus:outline-none focus:border-[#1b83bf]">
            </div>

            <div>
              <label class="block font-bold text-[#12263A] mb-1">رقم الهاتف:</label>
              <input type="text" x-model="settingsData.phone" class="w-full px-3 py-2 border border-slate-200 rounded-lg focus:outline-none focus:border-[#1b83bf]">
            </div>

            <div>
              <label class="block font-bold text-[#12263A] mb-1">رقم الواتساب (لتحويل العملاء واعتماد العروض): *</label>
              <input type="text" x-model="settingsData.whatsapp" placeholder="مثال: 966500000000" class="w-full px-3 py-2 border border-slate-200 rounded-lg focus:outline-none focus:border-[#1b83bf]">
            </div>

            <div>
              <label class="block font-bold text-[#12263A] mb-1">البريد الإلكتروني الرسمي:</label>
              <input type="email" x-model="settingsData.email" class="w-full px-3 py-2 border border-slate-200 rounded-lg focus:outline-none focus:border-[#1b83bf]">
            </div>

            <div>
              <label class="block font-bold text-[#12263A] mb-1">رابط الموقع الإلكتروني:</label>
              <input type="url" x-model="settingsData.website" class="w-full px-3 py-2 border border-slate-200 rounded-lg focus:outline-none focus:border-[#1b83bf]">
            </div>

            <div class="sm:col-span-2">
              <label class="block font-bold text-[#12263A] mb-1">العنوان / المقر:</label>
              <input type="text" x-model="settingsData.address" class="w-full px-3 py-2 border border-slate-200 rounded-lg focus:outline-none focus:border-[#1b83bf]">
            </div>
          </div>

          <div class="pt-4 border-t border-slate-100 flex justify-end">
            <button @click="saveCompanySettings()" 
                    :disabled="loading.savingSettings"
                    class="px-6 py-2.5 rounded-xl bg-gradient-to-r from-[#1b83bf] to-[#12263A] text-white font-bold text-xs hover:brightness-110 active:brightness-95 transition-all shadow-md flex items-center gap-2">
              <span x-show="!loading.savingSettings">💾 حفظ إعدادات الوكالة والمفتاح</span>
              <span x-show="loading.savingSettings">جاري الحفظ...</span>
            </button>
          </div>

        </div>

        <!-- Password & Security Card -->
        <div class="bg-white rounded-2xl p-6 border border-slate-200/80 shadow-sm space-y-4">
          <div>
            <h3 class="text-base sm:text-lg font-black text-[#12263A] flex items-center gap-2">
              <span>🔒</span> أمان الحساب وتغيير كلمة المرور
            </h3>
            <p class="text-xs text-slate-500 mt-0.5">
              لحماية لوحة التحكم عند رفعها على سيرفر عام، يمكنك تغيير كلمة المرور في أي وقت.
            </p>
          </div>

          <form @submit.prevent="changePassword()" class="grid grid-cols-1 sm:grid-cols-3 gap-4 text-xs">
            <div>
              <label class="block font-bold text-[#12263A] mb-1">كلمة المرور الحالية: *</label>
              <input type="password" x-model="passwordForm.current_password" required placeholder="••••••••" 
                     class="w-full px-3 py-2 border border-slate-200 rounded-lg focus:outline-none focus:border-[#1b83bf]">
            </div>

            <div>
              <label class="block font-bold text-[#12263A] mb-1">كلمة المرور الجديدة: *</label>
              <input type="password" x-model="passwordForm.new_password" required placeholder="6 خانات على الأقل" 
                     class="w-full px-3 py-2 border border-slate-200 rounded-lg focus:outline-none focus:border-[#1b83bf]">
            </div>

            <div>
              <label class="block font-bold text-[#12263A] mb-1">تأكيد كلمة المرور الجديدة: *</label>
              <input type="password" x-model="passwordForm.new_password_confirmation" required placeholder="تأكيد الكلمة" 
                     class="w-full px-3 py-2 border border-slate-200 rounded-lg focus:outline-none focus:border-[#1b83bf]">
            </div>

            <div class="sm:col-span-3 pt-2 flex justify-end">
              <button type="submit" 
                      :disabled="loading.changingPassword"
                      class="px-5 py-2.5 rounded-xl bg-[#12263A] hover:bg-[#1a3854] text-white font-bold text-xs transition-all shadow-md flex items-center gap-2">
                <span x-show="!loading.changingPassword">🔐 تحديث كلمة المرور</span>
                <span x-show="loading.changingPassword">جاري التحديث...</span>
              </button>
            </div>
          </form>
        </div>

      </div>

    </main>

  </div>

  <!-- Single Page Application Logic (Alpine.js) -->
  <script>
    function proposalDashboard() {
      return {
        currentTab: 'overview',
        mobileSidebarOpen: false,
        wizardStep: 1,
        reviewTab: 'modules',
        searchQuery: '',
        filterStatus: 'all',
        showApiKey: false,
        selectedTemplateSlug: null,

        // Notifications
        toast: { show: false, message: '', type: 'success' },
        showToast(msg, type = 'success') {
          this.toast = { show: true, message: msg, type: type };
          setTimeout(() => { this.toast.show = false; }, 4000);
        },

        // Loading states
        loading: {
          analyzing: false,
          generating: false,
          saving: false,
          testingGemini: false,
          savingSettings: false,
          changingPassword: false,
        },

        passwordForm: {
          current_password: '',
          new_password: '',
          new_password_confirmation: '',
        },

        // Proposals Data & Stats
        proposals: @json($proposalsData),
        stats: @json($stats),

        // Industry Templates
        templates: @json($templates),

        // Company Settings Data
        settingsData: @json($settings),

        // Wizard Input Data
        wizardData: {
          client_name: '',
          client_company: '',
          client_logo_url: '',
          brand_color: '#1b83bf',
          currency: 'ريال',
          sales_rep_name: '{{ $settings->agency_name }}',
          raw_idea: '',
          custom_price: '',
          quick_mode: false,
          model: '',
        },
        activeModelUsed: '',

        // AI Generated Clarifications & Analysis
        analysisResult: {
          summary: '',
          suggested_price_min: 45000,
          suggested_price_max: 65000,
          estimated_weeks: '6 - 8 أسابيع',
          questions: [],
        },
        clarificationAnswers: {},

        // Full Proposal in review/edit mode
        activeProposal: {},

        initDashboard() {
          console.log('Fkretk Proposal Dashboard initialized with Sidebar & Co-Branding.');
        },

        get filteredProposals() {
          return this.proposals.filter(p => {
            const matchesSearch = !this.searchQuery || 
              p.title.toLowerCase().includes(this.searchQuery.toLowerCase()) ||
              p.client_name.toLowerCase().includes(this.searchQuery.toLowerCase()) ||
              (p.client_company && p.client_company.toLowerCase().includes(this.searchQuery.toLowerCase()));
            const matchesStatus = this.filterStatus === 'all' || p.status === this.filterStatus;
            return matchesSearch && matchesStatus;
          });
        },

        startNewWizard() {
          this.wizardStep = 1;
          this.wizardData.client_name = '';
          this.wizardData.client_company = '';
          this.wizardData.client_logo_url = '';
          this.wizardData.brand_color = '#1b83bf';
          this.wizardData.raw_idea = '';
          this.wizardData.custom_price = '';
          this.selectedTemplateSlug = null;
          this.clarificationAnswers = {};
          this.currentTab = 'wizard';
        },

        applyTemplate(tmpl) {
          this.selectedTemplateSlug = tmpl.slug;
          this.wizardData.raw_idea = tmpl.raw_idea;
          this.wizardData.currency = tmpl.currency || this.wizardData.currency;
          this.wizardData.custom_price = tmpl.suggested_price_min;
          this.currentTab = 'wizard';
          this.wizardStep = 1;
          this.showToast('تم تحميل بيانات: ' + tmpl.name + ' بنجاح! يمكنك تخصيص العميل وتوليد العرض بالـ AI.');
        },

        async runAnalysisStep() {
          if (!this.wizardData.raw_idea || this.wizardData.raw_idea.length < 5) {
            this.showToast('يرجى كتابة فكرة أو متطلبات المشروع أولاً.', 'error');
            return;
          }

          this.loading.analyzing = true;
          const chosenModel = this.wizardData.model || this.settingsData.gemini_model;
          try {
            const res = await fetch('/dashboard/api/analyze-idea', {
              method: 'POST',
              headers: {
                'Content-Type': 'application/json',
                'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').getAttribute('content'),
              },
              body: JSON.stringify({
                raw_idea: this.wizardData.raw_idea,
                client_name: this.wizardData.client_name,
                currency: this.wizardData.currency,
                model: chosenModel,
              })
            });
            const data = await res.json();
            if (data.success) {
              this.analysisResult = data.analysis;
              this.activeModelUsed = data.active_model || chosenModel;
              this.wizardData.custom_price = data.analysis.suggested_price_min || 45000;

              // Pre-select recommended answers
              if (data.analysis.questions) {
                data.analysis.questions.forEach(q => {
                  this.clarificationAnswers[q.id] = q.recommended || (q.options ? q.options[0] : '');
                });
              }

              if (this.wizardData.quick_mode) {
                await this.generateFullProposal();
              } else {
                this.wizardStep = 2;
                this.showToast('تم تحليل الفكرة وتجهيز الأسئلة الاستيضاحية بنجاح!');
              }
            } else {
              this.showToast(data.message || 'حدث خطأ أثناء تحليل الفكرة', 'error');
            }
          } catch (err) {
            this.showToast('فشل الاتصال بخدمة التحليل الذكي.', 'error');
          } finally {
            this.loading.analyzing = false;
          }
        },

        async generateFullProposal() {
          this.loading.generating = true;
          const chosenModel = this.wizardData.model || this.settingsData.gemini_model;
          try {
            const payload = {
              client_name: this.wizardData.client_name || 'العميل',
              client_company: this.wizardData.client_company,
              client_logo_url: this.wizardData.client_logo_url,
              brand_color: this.wizardData.brand_color,
              currency: this.wizardData.currency,
              raw_idea: this.wizardData.raw_idea,
              clarifications: this.clarificationAnswers,
              custom_price: this.wizardData.custom_price,
              model: chosenModel,
            };

            const res = await fetch('/dashboard/api/generate-proposal', {
              method: 'POST',
              headers: {
                'Content-Type': 'application/json',
                'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').getAttribute('content'),
              },
              body: JSON.stringify(payload)
            });
            const data = await res.json();
            if (data.success) {
              this.activeProposal = data.proposal;
              this.activeModelUsed = data.active_model || chosenModel;
              this.wizardStep = 3;
              this.showToast('تم توليد العرض الفني والمالي الكامل بنجاح!');
            } else {
              this.showToast(data.message || 'حدث خطأ أثناء توليد العرض', 'error');
            }
          } catch (err) {
            this.showToast('فشل توليد العرض الكامل.', 'error');
          } finally {
            this.loading.generating = false;
          }
        },

        async saveAndPublishProposal() {
          this.loading.saving = true;
          try {
            const payload = {
              title: this.activeProposal.title || ('عرض فني ومالي: ' + (this.wizardData.client_name || 'مشروع جديد')),
              client_name: this.wizardData.client_name || 'العميل',
              client_company: this.wizardData.client_company,
              client_logo_url: this.wizardData.client_logo_url,
              brand_color: this.wizardData.brand_color || '#1b83bf',
              sales_rep_name: this.wizardData.sales_rep_name,
              currency: this.wizardData.currency,
              raw_idea: this.wizardData.raw_idea,
              clarifications: this.clarificationAnswers,
              executive_summary: this.activeProposal.executive_summary,
              problem_vs_solution: this.activeProposal.problem_vs_solution,
              workflow_steps: this.activeProposal.workflow_steps,
              system_architecture: this.activeProposal.system_architecture,
              modules: this.activeProposal.modules,
              pricing_packages: this.activeProposal.pricing_packages,
              payment_milestones: this.activeProposal.payment_milestones,
              timeline_phases: this.activeProposal.timeline_phases,
              deliverables_and_guarantees: this.activeProposal.deliverables_and_guarantees,
              terms_and_conditions: this.activeProposal.terms_and_conditions,
              status: 'published',
            };

            const res = await fetch('/dashboard/api/proposals', {
              method: 'POST',
              headers: {
                'Content-Type': 'application/json',
                'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').getAttribute('content'),
              },
              body: JSON.stringify(payload)
            });
            const data = await res.json();
            if (data.success) {
              this.showToast('🎉 تم حفظ ونشر العرض بنجاح! تم نسخ الرابط.');
              await this.refreshProposalsList();
              this.currentTab = 'overview';
              this.copyShareLink(data.share_url);
            } else {
              this.showToast(data.message || 'فشل حفظ العرض', 'error');
            }
          } catch (err) {
            this.showToast('حدث خطأ أثناء حفظ العرض.', 'error');
          } finally {
            this.loading.saving = false;
          }
        },

        async refreshProposalsList() {
          try {
            const res = await fetch('/dashboard/api/proposals');
            const data = await res.json();
            this.proposals = data.proposals;
            this.stats = data.stats;
          } catch (err) {
            console.error('Failed to reload list:', err);
          }
        },

        copyShareLink(url) {
          navigator.clipboard.writeText(url).then(() => {
            this.showToast('تم نسخ رابط العرض إلى الحافظة بنجاح!');
          }).catch(() => {
            prompt('انسخ الرابط التالي:', url);
          });
        },

        async duplicateProposal(id) {
          if (!confirm('هل تريد إنشاء نسخة مطابقة من هذا العرض؟')) return;
          try {
            const res = await fetch('/dashboard/api/proposals/' + id + '/duplicate', {
              method: 'POST',
              headers: {
                'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').getAttribute('content'),
              }
            });
            const data = await res.json();
            if (data.success) {
              this.showToast('تم استنساخ العرض بنجاح!');
              await this.refreshProposalsList();
            }
          } catch (err) {
            this.showToast('فشل استنساخ العرض.', 'error');
          }
        },

        async deleteProposal(id) {
          if (!confirm('هل أنت متأكد من رغبتك في حذف هذا العرض نهائياً؟')) return;
          try {
            const res = await fetch('/dashboard/api/proposals/' + id, {
              method: 'DELETE',
              headers: {
                'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').getAttribute('content'),
              }
            });
            const data = await res.json();
            if (data.success) {
              this.showToast('تم حذف العرض بنجاح.');
              await this.refreshProposalsList();
            }
          } catch (err) {
            this.showToast('فشل حذف العرض.', 'error');
          }
        },

        async testGeminiConnection() {
          this.loading.testingGemini = true;
          try {
            const res = await fetch('/dashboard/api/test-gemini', {
              method: 'POST',
              headers: {
                'Content-Type': 'application/json',
                'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').getAttribute('content'),
              },
              body: JSON.stringify({
                api_key: this.settingsData.gemini_api_key,
                model: this.settingsData.gemini_model,
              })
            });
            const data = await res.json();
            if (data.success) {
              this.showToast(data.message, 'success');
            } else {
              this.showToast(data.message, 'error');
            }
          } catch (err) {
            this.showToast('حدث خطأ أثناء فحص اتصال Gemini.', 'error');
          } finally {
            this.loading.testingGemini = false;
          }
        },

        async saveCompanySettings() {
          this.loading.savingSettings = true;
          try {
            const res = await fetch('/dashboard/api/settings', {
              method: 'POST',
              headers: {
                'Content-Type': 'application/json',
                'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').getAttribute('content'),
              },
              body: JSON.stringify(this.settingsData)
            });
            const data = await res.json();
            if (data.success) {
              this.showToast('تم حفظ إعدادات الوكالة ومفتاح Gemini بنجاح!');
            } else {
              this.showToast(data.message || 'فشل حفظ الإعدادات', 'error');
            }
          } catch (err) {
            this.showToast('حدث خطأ أثناء حفظ الإعدادات.', 'error');
          } finally {
            this.loading.savingSettings = false;
          }
        },

        async changePassword() {
          if (this.passwordForm.new_password !== this.passwordForm.new_password_confirmation) {
            this.showToast('تأكيد كلمة المرور الجديدة غير متطابق!', 'error');
            return;
          }

          this.loading.changingPassword = true;
          try {
            const res = await fetch('/dashboard/api/change-password', {
              method: 'POST',
              headers: {
                'Content-Type': 'application/json',
                'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').getAttribute('content'),
              },
              body: JSON.stringify(this.passwordForm)
            });
            const data = await res.json();
            if (data.success) {
              this.showToast(data.message, 'success');
              this.passwordForm.current_password = '';
              this.passwordForm.new_password = '';
              this.passwordForm.new_password_confirmation = '';
            } else {
              this.showToast(data.message || 'فشل تحديث كلمة المرور', 'error');
            }
          } catch (err) {
            this.showToast('حدث خطأ أثناء تحديث كلمة المرور.', 'error');
          } finally {
            this.loading.changingPassword = false;
          }
        }
      }
    }
  </script>

</body>
</html>
