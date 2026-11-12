<!DOCTYPE html>
<html lang="ar" dir="rtl">
<head>
  <meta charset="UTF-8">
  <meta name="viewport" content="width=device-width, initial-scale=1.0">
  <meta name="csrf-token" content="{{ csrf_token() }}">
  <title>تسجيل الدخول | {{ $settings->agency_name ?? 'وكالة فكرتك الرقمية' }}</title>

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
              orange: '#F28822',
              blue: '#1b83bf',
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
    body {
      font-family: 'Cairo', system-ui, -apple-system, sans-serif;
      background: radial-gradient(circle at top right, #173854, #12263A 60%, #0a1724);
      min-height: 100vh;
      color: #1e293b;
    }
    h1, h2, h3, h4, .font-heading {
      font-family: 'Thmanyah', 'Cairo', sans-serif;
    }
  </style>
</head>
<body class="flex items-center justify-center p-4 min-h-screen">

  <div class="w-full max-w-md">
    
    <!-- Brand Header -->
    <div class="text-center mb-6">
      <div class="inline-block bg-white p-2.5 rounded-2xl shadow-xl border border-white/20 mb-3">
        <img src="{{ $settings->logo_url ?? '/assets/logo_fkretk.png' }}" alt="{{ $settings->agency_name }}" class="h-10 w-auto object-contain mx-auto">
      </div>
      <h2 class="text-xl sm:text-2xl font-black text-white tracking-wide">{{ $settings->agency_name }}</h2>
      <p class="text-xs text-slate-300 mt-1">منظومة توليد العروض الفنية والمالية بالذكاء الاصطناعي</p>
    </div>

    <!-- Login Card -->
    <div class="bg-white/95 backdrop-blur-xl rounded-3xl p-6 sm:p-8 shadow-2xl border border-white/30 space-y-5">
      
      <div class="text-center pb-2 border-b border-slate-100">
        <h3 class="text-base font-black text-[#12263A]">تسجيل الدخول للوحة التحكم</h3>
        <p class="text-xs text-slate-500 mt-0.5">أدخل بيانات حسابك للوصول إلى إدارة العروض والذكاء الاصطناعي</p>
      </div>

      <!-- Errors Alert -->
      @if ($errors->any())
        <div class="p-3.5 rounded-xl bg-red-50 border border-red-200 text-red-700 text-xs space-y-1">
          @foreach ($errors->all() as $error)
            <div class="flex items-center gap-1.5 font-bold">
              <span>⚠️</span>
              <span>{{ $error }}</span>
            </div>
          @endforeach
        </div>
      @endif

      <form action="{{ route('login.submit') }}" method="POST" class="space-y-4 text-xs">
        @csrf

        <!-- Email -->
        <div>
          <label class="block font-bold text-[#12263A] mb-1.5">البريد الإلكتروني:</label>
          <div class="relative">
            <input type="email" name="email" value="{{ old('email', 'admin@fkretk.com') }}" required autofocus
                   placeholder="admin@fkretk.com"
                   class="w-full pl-3 pr-10 py-2.5 bg-slate-50 border border-slate-200 rounded-xl focus:bg-white focus:outline-none focus:border-[#1b83bf] transition-all text-xs font-semibold">
            <span class="absolute right-3 top-3 text-slate-400">📧</span>
          </div>
        </div>

        <!-- Password -->
        <div>
          <div class="flex items-center justify-between mb-1.5">
            <label class="block font-bold text-[#12263A]">كلمة المرور:</label>
            <button type="button" onclick="togglePasswordVisibility()" class="text-[11px] text-[#1b83bf] hover:underline font-semibold" id="togglePassBtn">
              إظهار
            </button>
          </div>
          <div class="relative">
            <input type="password" name="password" id="passwordInput" value="fkretk@2026" required
                   placeholder="••••••••"
                   class="w-full pl-3 pr-10 py-2.5 bg-slate-50 border border-slate-200 rounded-xl focus:bg-white focus:outline-none focus:border-[#1b83bf] transition-all text-xs font-semibold">
            <span class="absolute right-3 top-3 text-slate-400">🔒</span>
          </div>
        </div>

        <!-- Remember Me -->
        <div class="flex items-center justify-between pt-1">
          <label class="flex items-center gap-2 cursor-pointer text-slate-600 font-semibold">
            <input type="checkbox" name="remember" checked class="rounded text-[#F28822] focus:ring-[#F28822]">
            <span>تذكر تسجيل دخولي على هذا الجهاز</span>
          </label>
        </div>

        <!-- Submit Button -->
        <div class="pt-2">
          <button type="submit" class="w-full py-3 rounded-xl bg-gradient-to-r from-[#F28822] to-[#d97316] hover:brightness-110 active:brightness-95 text-white font-bold text-xs transition-all shadow-lg flex items-center justify-center gap-2">
            <span>🚀</span>
            <span>دخول إلى لوحة التحكم</span>
          </button>
        </div>

      </form>

      <!-- Default Access Credentials Badge -->
      <div class="p-3 rounded-xl bg-blue-50/70 border border-blue-100 text-[11px] text-slate-600 space-y-1">
        <div class="font-bold text-[#1b83bf] flex items-center gap-1">
          <span>ℹ️</span> بيانات الدخول الافتراضية للوحة:
        </div>
        <div class="flex justify-between items-center text-slate-500">
          <span>البريد: <strong class="text-slate-700">admin@fkretk.com</strong></span>
          <span>كلمة المرور: <strong class="text-slate-700">fkretk@2026</strong></span>
        </div>
      </div>

    </div>

    <!-- Footer Copyright -->
    <div class="text-center mt-6 text-[11px] text-slate-400">
      جميع الحقوق محفوظة © {{ date('Y') }} - {{ $settings->agency_name ?? 'وكالة فكرتك الرقمية' }}
    </div>

  </div>

  <script>
    function togglePasswordVisibility() {
      const pass = document.getElementById('passwordInput');
      const btn = document.getElementById('togglePassBtn');
      if (pass.type === 'password') {
        pass.type = 'text';
        btn.innerText = 'إخفاء';
      } else {
        pass.type = 'password';
        btn.innerText = 'إظهار';
      }
    }
  </script>

</body>
</html>
