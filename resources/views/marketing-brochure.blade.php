<!DOCTYPE html>
<html lang="ar" dir="rtl">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>دليل ومميزات وباقات نظام ERP السحابي لإدارة المبيعات والمخزون</title>
    
    <!-- Google Fonts: Cairo & Tajawal -->
    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
    <link href="https://fonts.googleapis.com/css2?family=Cairo:wght@400;600;700;800;900&family=Tajawal:wght@400;500;700;800;900&display=swap" rel="stylesheet">

    <!-- Tailwind CSS CDN -->
    <script src="https://cdn.tailwindcss.com"></script>
    <script>
        tailwind.config = {
            theme: {
                extend: {
                    fontFamily: {
                        sans: ['Cairo', 'Tajawal', 'sans-serif'],
                        tajawal: ['Tajawal', 'sans-serif'],
                    },
                    colors: {
                        brand: {
                            50: '#fffbeb',
                            100: '#fef3c7',
                            500: '#f59e0b',
                            600: '#d97706',
                            700: '#b45309',
                            900: '#78350f',
                        }
                    }
                }
            }
        }
    </script>

    <style>
        body {
            font-family: 'Cairo', 'Tajawal', sans-serif;
            background-color: #f8fafc;
            color: #0f172a;
            -webkit-font-smoothing: antialiased;
        }

        @media print {
            @page {
                size: A4 portrait;
                margin: 8mm 8mm 10mm 8mm;
            }
            body {
                background: #ffffff !important;
                color: #000000 !important;
                font-size: 9pt !important;
            }
            .no-print {
                display: none !important;
            }
            .page-break {
                page-break-before: always !important;
            }
            .avoid-break {
                page-break-inside: avoid !important;
            }
            .card-shadow {
                box-shadow: none !important;
                border: 1px solid #cbd5e1 !important;
            }
            * {
                -webkit-print-color-adjust: exact !important;
                print-color-adjust: exact !important;
            }
        }

        .gradient-hero {
            background: linear-gradient(135deg, #0f172a 0%, #1e293b 50%, #0f172a 100%);
        }

        .gradient-card {
            background: linear-gradient(180deg, rgba(255, 255, 255, 0.9) 0%, rgba(248, 250, 252, 0.95) 100%);
        }
    </style>
</head>
<body class="p-0 sm:p-6 md:p-8">

    <!-- Floating Print / Save PDF Bar -->
    <div class="no-print fixed bottom-6 left-1/2 -translate-x-1/2 z-50 bg-slate-900/90 backdrop-blur-md text-white px-6 py-3.5 rounded-full shadow-2xl border border-slate-700 flex items-center gap-4">
        <span class="text-xs font-bold text-amber-400">📄 دليل عروض الأسعار والمميزات للعملاء</span>
        <button onclick="window.print()" class="px-5 py-2 bg-gradient-to-r from-amber-500 to-amber-600 hover:from-amber-400 hover:to-amber-500 text-slate-950 font-black text-xs rounded-full shadow-lg transition-transform active:scale-95 flex items-center gap-2 cursor-pointer">
            <span>🖨️ طباعة أو حفظ كملف PDF</span>
        </button>
    </div>

    <!-- Main Container (A4 Printable Layout) -->
    <div class="max-w-5xl mx-auto space-y-6">

        <!-- ========================================== -->
        <!-- 🌟 SECTION 1: HEADER & HERO BANNER        -->
        <!-- ========================================== -->
        <div class="gradient-hero text-white rounded-3xl p-6 sm:p-8 relative overflow-hidden shadow-xl border border-slate-800 avoid-break">
            <div class="absolute -right-20 -top-20 w-80 h-80 bg-amber-500/10 rounded-full blur-3xl pointer-events-none"></div>
            <div class="absolute -left-20 -bottom-20 w-80 h-80 bg-emerald-500/10 rounded-full blur-3xl pointer-events-none"></div>

            <div class="relative z-10 flex flex-col md:flex-row justify-between items-start md:items-center gap-6">
                <div>
                    <div class="inline-flex items-center gap-2 px-3.5 py-1.5 rounded-full bg-amber-500/20 border border-amber-500/40 text-amber-300 text-xs font-bold mb-3">
                        <span>✨ الجيل القادم من أنظمة إدارة الأعمال السحابية</span>
                    </div>
                    <h1 class="text-2xl sm:text-4xl font-black tracking-tight text-white mb-2">
                        منظومة <span class="text-amber-400">ERP المتطورة</span> للمبيعات والمخزون
                    </h1>
                    <p class="text-slate-300 text-xs sm:text-sm max-w-2xl leading-relaxed">
                        نظام متكامل وسريع مصمم خصيصاً للمحلات التجارية، المحامص والمطاحن، شركات التوزيع، وتجار الجملة والتجزئة. تحكم كامل في مبيعاتك، مخازنك، خزائنك، وعرباتك من أي جهاز وفي أي وقت.
                    </p>
                </div>

                <div class="bg-white/10 backdrop-blur-md p-4 rounded-2xl border border-white/20 text-center shrink-0 w-full md:w-auto">
                    <div class="text-[11px] text-amber-300 font-bold uppercase tracking-wider">جاهز للعمل على</div>
                    <div class="text-sm font-black text-white mt-1 flex items-center justify-center gap-3">
                        <span>💻 الكمبيوتر</span>
                        <span>📱 الموبايل</span>
                        <span>📟 أجهزة POS</span>
                    </div>
                    <div class="mt-2 text-[10px] text-slate-300 bg-white/10 px-2 py-0.5 rounded-lg">PWA • يعمل بدون تثبيت معقد</div>
                </div>
            </div>
        </div>

        <!-- ========================================================== -->
        <!-- 💡 SECTION 2: WHAT WILL THE SYSTEM ADD TO YOUR BUSINESS?   -->
        <!-- ========================================================== -->
        <div class="avoid-break bg-white rounded-3xl p-6 sm:p-8 shadow-sm border border-slate-200 card-shadow">
            <div class="text-center max-w-2xl mx-auto mb-6">
                <span class="text-xs font-black text-amber-600 uppercase tracking-wider">القيمة المضافة والعائد على الاستثمار</span>
                <h2 class="text-xl sm:text-2xl font-black text-slate-900 mt-1">ماذا سيضيف هذا النظام لنشاطك التجاري؟</h2>
                <p class="text-xs text-slate-500 mt-1">حلول عملية مصممة للقضاء على أبرز المشاكل اليومية التي تواجه أصحاب الأعمال</p>
            </div>

            <div class="grid grid-cols-1 md:grid-cols-3 gap-4">
                <!-- Value 1 -->
                <div class="p-4 rounded-2xl bg-slate-50 border border-slate-200/80 space-y-2">
                    <div class="w-10 h-10 rounded-xl bg-rose-500/10 text-rose-600 flex items-center justify-center text-xl font-bold">
                        🔒
                    </div>
                    <h3 class="text-sm font-black text-slate-900">منع العجز وسرقات الخزينة</h3>
                    <p class="text-xs text-slate-600 leading-relaxed">
                        نظام دقيق لإغلاق ورديات الكاشير وجرد النقدية بالدرج، مع مقارنة فورية بين النقدية الفعلية والمتوقعة، وإرسال تنبيه فوري للإدارة عند وجود أي عجز.
                    </p>
                </div>

                <!-- Value 2 -->
                <div class="p-4 rounded-2xl bg-slate-50 border border-slate-200/80 space-y-2">
                    <div class="w-10 h-10 rounded-xl bg-amber-500/10 text-amber-600 flex items-center justify-center text-xl font-bold">
                        ⚡
                    </div>
                    <h3 class="text-sm font-black text-slate-900">سرعة قياسية في نقطة البيع</h3>
                    <p class="text-xs text-slate-600 leading-relaxed">
                        إصدار الفاتورة في أقل من 3 ثوانٍ بالباركود أو اللمس، مع دعم الدفع النقدي والآجل والمجزء، وطباعة إيصالات فورية على كافة الطابعات الحرارية و A4.
                    </p>
                </div>

                <!-- Value 3 -->
                <div class="p-4 rounded-2xl bg-slate-50 border border-slate-200/80 space-y-2">
                    <div class="w-10 h-10 rounded-xl bg-emerald-500/10 text-emerald-600 flex items-center justify-center text-xl font-bold">
                        📈
                    </div>
                    <h3 class="text-sm font-black text-slate-900">معرفة الأرباح الصافية الحقيقية</h3>
                    <p class="text-xs text-slate-600 leading-relaxed">
                        حساب تلقائي لهوامش ربح كل صنف، ومتابعة فورية للأرباح الصافية بعد خصم المصروفات ومسحوبات الخزينة وتكلفة البضاعة المباعة بدقة متناهية.
                    </p>
                </div>

                <!-- Value 4 -->
                <div class="p-4 rounded-2xl bg-slate-50 border border-slate-200/80 space-y-2">
                    <div class="w-10 h-10 rounded-xl bg-blue-500/10 text-blue-600 flex items-center justify-center text-xl font-bold">
                        🚚
                    </div>
                    <h3 class="text-sm font-black text-slate-900">إحكام الرقابة على سيارات التوزيع</h3>
                    <p class="text-xs text-slate-600 leading-relaxed">
                        تحويل البضاعة من المخزن الرئيسي لسيارات المندوبين، والبيع والتحصيل مباشرة من خط السير، مع جرد عهدة كل سيارة بنهاية كل يوم.
                    </p>
                </div>

                <!-- Value 5 -->
                <div class="p-4 rounded-2xl bg-slate-50 border border-slate-200/80 space-y-2">
                    <div class="w-10 h-10 rounded-xl bg-indigo-500/10 text-indigo-600 flex items-center justify-center text-xl font-bold">
                        📑
                    </div>
                    <h3 class="text-sm font-black text-slate-900">ضبط مديونيات العملاء والموردين</h3>
                    <p class="text-xs text-slate-600 leading-relaxed">
                        كشوف حسابات تفصيلية متحركة لكل عميل ومورد، مع سندات قبض وصرف مسجلة لمنع أي خلاف حسابي ومتابعة مواعيد التحصيل والآجل.
                    </p>
                </div>

                <!-- Value 6 -->
                <div class="p-4 rounded-2xl bg-slate-50 border border-slate-200/80 space-y-2">
                    <div class="w-10 h-10 rounded-xl bg-teal-500/10 text-teal-600 flex items-center justify-center text-xl font-bold">
                        ✈️
                    </div>
                    <h3 class="text-sm font-black text-slate-900">متابعة لحظية ونسخ احتياطي بالتليجرام</h3>
                    <p class="text-xs text-slate-600 leading-relaxed">
                        استلام تقرير الإغلاق اليومي وتنبيهات النواقص والعجز على هاتفك مباشرة، مع وصول ملف نسخ احتياطي يومي مشفر لقاعدة بياناتك تلقائياً.
                    </p>
                </div>
            </div>
        </div>

        <!-- ========================================== -->
        <!-- ⚙️ SECTION 3: KEY SYSTEM MODULES           -->
        <!-- ========================================== -->
        <div class="avoid-break bg-white rounded-3xl p-6 sm:p-8 shadow-sm border border-slate-200 card-shadow">
            <div class="flex flex-col sm:flex-row justify-between items-start sm:items-center gap-2 mb-6 border-b border-slate-100 pb-4">
                <div>
                    <h2 class="text-xl font-black text-slate-900">الوحدات والأنظمة الفرعية المتوفرة (Modules)</h2>
                    <p class="text-xs text-slate-500">نظام شامل يغطي كافة جوانب دورتك المستندية من المشتريات وحتى التحصيل النهائي</p>
                </div>
                <span class="px-3 py-1 bg-slate-100 text-slate-700 text-xs font-bold rounded-xl">8 أنظمة مدمجة</span>
            </div>

            <div class="grid grid-cols-2 sm:grid-cols-4 gap-3 text-xs">
                <div class="p-3 rounded-2xl bg-amber-500/5 border border-amber-500/20 font-bold text-slate-800 flex items-center gap-2">
                    <span>🧾</span> نقاط البيع والكاشير POS
                </div>
                <div class="p-3 rounded-2xl bg-amber-500/5 border border-amber-500/20 font-bold text-slate-800 flex items-center gap-2">
                    <span>🏢</span> المخازن المتعددة والفروع
                </div>
                <div class="p-3 rounded-2xl bg-amber-500/5 border border-amber-500/20 font-bold text-slate-800 flex items-center gap-2">
                    <span>🚚</span> سيارات التوزيع والمندوبين
                </div>
                <div class="p-3 rounded-2xl bg-amber-500/5 border border-amber-500/20 font-bold text-slate-800 flex items-center gap-2">
                    <span>📦</span> المشتريات وفواتير الموردين
                </div>
                <div class="p-3 rounded-2xl bg-amber-500/5 border border-amber-500/20 font-bold text-slate-800 flex items-center gap-2">
                    <span>👥</span> حسابات العملاء والآجل
                </div>
                <div class="p-3 rounded-2xl bg-amber-500/5 border border-amber-500/20 font-bold text-slate-800 flex items-center gap-2">
                    <span>💰</span> الخزينة والمصروفات واليومية
                </div>
                <div class="p-3 rounded-2xl bg-amber-500/5 border border-amber-500/20 font-bold text-slate-800 flex items-center gap-2">
                    <span>📊</span> تقارير الأرباح والتحليلات
                </div>
                <div class="p-3 rounded-2xl bg-amber-500/5 border border-amber-500/20 font-bold text-slate-800 flex items-center gap-2">
                    <span>🤖</span> إشعارات وتنبيهات التليجرام
                </div>
            </div>
        </div>

        <div class="page-break"></div>

        <!-- ========================================== -->
        <!-- 💰 SECTION 4: PRICING PLANS & PACKAGES     -->
        <!-- ========================================== -->
        <div class="avoid-break space-y-4">
            <div class="text-center max-w-2xl mx-auto">
                <span class="text-xs font-black text-amber-600 uppercase tracking-wider">خطط الاشتراك المرنة</span>
                <h2 class="text-xl sm:text-2xl font-black text-slate-900 mt-1">باقات تناسب حجم أعمالك وطموحك</h2>
                <p class="text-xs text-slate-500 mt-1">اختر الباقة المناسبة مع إمكانية الترقية أو إضافة فروع ومستخدمين في أي وقت</p>
            </div>

            <!-- Pricing Grid -->
            <div class="grid grid-cols-1 md:grid-cols-4 gap-4 items-stretch">
                
                <!-- Plan 1: Starter -->
                <div class="bg-white rounded-3xl p-5 border border-slate-200 card-shadow flex flex-col justify-between relative">
                    <div class="space-y-3">
                        <div class="flex justify-between items-start">
                            <div>
                                <span class="text-[10px] font-black uppercase tracking-wider text-slate-500">للمحلات الفردية</span>
                                <h3 class="text-base font-black text-slate-900">البداية (Starter)</h3>
                            </div>
                            <span class="p-2 rounded-xl bg-slate-100 text-sm">🏬</span>
                        </div>

                        <div class="pt-2 border-t border-slate-100">
                            <div class="text-2xl font-black text-slate-900 font-mono">299 <span class="text-xs font-bold text-slate-500">ج.م / شهر</span></div>
                            <div class="text-[11px] text-emerald-600 font-bold mt-0.5 font-mono">2,990 ج.م سنوياً (شهرين مجاناً)</div>
                            <div class="text-[10px] text-slate-400 font-mono mt-0.5">دولي: $19 / شهر ($190 سنوي)</div>
                        </div>

                        <div class="space-y-2 pt-3 border-t border-slate-100 text-xs text-slate-700 font-medium">
                            <div class="flex items-center gap-1.5"><span class="text-emerald-500 font-bold">✓</span> <strong>فرع واحد</strong> (نقطة بيع واحدة)</div>
                            <div class="flex items-center gap-1.5"><span class="text-emerald-500 font-bold">✓</span> <strong>2 مستخدمين</strong> (أدمن + كاشير)</div>
                            <div class="flex items-center gap-1.5"><span class="text-emerald-500 font-bold">✓</span> نقاط البيع POS وفواتير الباركود</div>
                            <div class="flex items-center gap-1.5"><span class="text-emerald-500 font-bold">✓</span> إدارة الأصناف والمخزون والتنبيهات</div>
                            <div class="flex items-center gap-1.5"><span class="text-emerald-500 font-bold">✓</span> تقفيل الورديات وجرد النقدية</div>
                            <div class="flex items-center gap-1.5 text-slate-400"><span class="text-slate-300">✕</span> التحويلات بين الفروع</div>
                            <div class="flex items-center gap-1.5 text-slate-400"><span class="text-slate-300">✕</span> سيارات التوزيع والمندوبين</div>
                        </div>
                    </div>

                    <div class="mt-5 pt-3 border-t border-slate-100">
                        <div class="text-center text-[11px] font-bold text-slate-500">مثالي للمتاجر الصغيرة ونقاط البيع المفردة</div>
                    </div>
                </div>

                <!-- Plan 2: Pro (Featured) -->
                <div class="bg-gradient-to-b from-slate-900 to-slate-950 text-white rounded-3xl p-5 border-2 border-amber-500 shadow-xl flex flex-col justify-between relative">
                    <div class="absolute -top-3 left-1/2 -translate-x-1/2 bg-amber-500 text-slate-950 text-[10px] font-black uppercase px-3 py-0.5 rounded-full shadow-md">
                        ⭐ الأكثر طلباً وشعبية
                    </div>

                    <div class="space-y-3">
                        <div class="flex justify-between items-start">
                            <div>
                                <span class="text-[10px] font-black uppercase tracking-wider text-amber-400">للمتاجر المتوسطة</span>
                                <h3 class="text-base font-black text-white">النمو (Pro)</h3>
                            </div>
                            <span class="p-2 rounded-xl bg-white/10 text-sm">🚀</span>
                        </div>

                        <div class="pt-2 border-t border-slate-800">
                            <div class="text-2xl font-black text-amber-400 font-mono">699 <span class="text-xs font-bold text-slate-300">ج.م / شهر</span></div>
                            <div class="text-[11px] text-emerald-400 font-bold mt-0.5 font-mono">6,990 ج.م سنوياً (شهرين مجاناً)</div>
                            <div class="text-[10px] text-slate-400 font-mono mt-0.5">دولي: $49 / شهر ($490 سنوي)</div>
                        </div>

                        <div class="space-y-2 pt-3 border-t border-slate-800 text-xs text-slate-200 font-medium">
                            <div class="flex items-center gap-1.5"><span class="text-amber-400 font-bold">✓</span> <strong>حتى 3 فروع / نقاط بيع</strong></div>
                            <div class="flex items-center gap-1.5"><span class="text-amber-400 font-bold">✓</span> <strong>6 مستخدمين</strong> بصلاحيات مخصصة</div>
                            <div class="flex items-center gap-1.5"><span class="text-amber-400 font-bold">✓</span> التحويلات المخزنية الفورية بين الفروع</div>
                            <div class="flex items-center gap-1.5"><span class="text-amber-400 font-bold">✓</span> كشوف حسابات العملاء والموردين</div>
                            <div class="flex items-center gap-1.5"><span class="text-amber-400 font-bold">✓</span> تقارير الأرباح واليومية الشاملة</div>
                            <div class="flex items-center gap-1.5"><span class="text-amber-400 font-bold">✓</span> <strong>إشعارات ونسخ احتياطي يومي بالتليجرام</strong></div>
                            <div class="flex items-center gap-1.5 text-slate-400"><span class="text-slate-600">✕</span> سيارات التوزيع المتجولة</div>
                        </div>
                    </div>

                    <div class="mt-5 pt-3 border-t border-slate-800">
                        <div class="text-center text-[11px] font-bold text-amber-400">الخيار الأمثل لسلاسل الفروع وتجار الجملة</div>
                    </div>
                </div>

                <!-- Plan 3: Van Distribution -->
                <div class="bg-white rounded-3xl p-5 border border-slate-200 card-shadow flex flex-col justify-between relative">
                    <div class="space-y-3">
                        <div class="flex justify-between items-start">
                            <div>
                                <span class="text-[10px] font-black uppercase tracking-wider text-slate-500">لشركات الشحن والتوزيع</span>
                                <h3 class="text-base font-black text-slate-900">التوزيع والجملة (Vans)</h3>
                            </div>
                            <span class="p-2 rounded-xl bg-slate-100 text-sm">🚚</span>
                        </div>

                        <div class="pt-2 border-t border-slate-100">
                            <div class="text-2xl font-black text-slate-900 font-mono">899 <span class="text-xs font-bold text-slate-500">ج.م / شهر</span></div>
                            <div class="text-[11px] text-emerald-600 font-bold mt-0.5 font-mono">8,990 ج.م سنوياً (شهرين مجاناً)</div>
                            <div class="text-[10px] text-slate-400 font-mono mt-0.5">دولي: $59 / شهر ($590 سنوي)</div>
                        </div>

                        <div class="space-y-2 pt-3 border-t border-slate-100 text-xs text-slate-700 font-medium">
                            <div class="flex items-center gap-1.5"><span class="text-emerald-500 font-bold">✓</span> <strong>مخزن رئيسي + 3 عربيات توزيع</strong></div>
                            <div class="flex items-center gap-1.5"><span class="text-emerald-500 font-bold">✓</span> <strong>4 مستخدمين</strong> (أدمن + 3 مناديب)</div>
                            <div class="flex items-center gap-1.5"><span class="text-emerald-500 font-bold">✓</span> شحن وتحميل البضاعة للسيارات</div>
                            <div class="flex items-center gap-1.5"><span class="text-emerald-500 font-bold">✓</span> فواتير وتحصيل من خطوط السير</div>
                            <div class="flex items-center gap-1.5"><span class="text-emerald-500 font-bold">✓</span> جرد وتقفيل عهدة كل سيارة يومياً</div>
                            <div class="flex items-center gap-1.5"><span class="text-emerald-500 font-bold">✓</span> تحصيل مديونيات العملاء على الطريق</div>
                            <div class="flex items-center gap-1.5"><span class="text-emerald-500 font-bold">✓</span> نسخ احتياطي يومي سحابي</div>
                        </div>
                    </div>

                    <div class="mt-5 pt-3 border-t border-slate-100">
                        <div class="text-center text-[11px] font-bold text-slate-500">مصمم للموزعين ومناديب المبيعات المتجولين</div>
                    </div>
                </div>

                <!-- Plan 4: Enterprise -->
                <div class="bg-white rounded-3xl p-5 border border-slate-200 card-shadow flex flex-col justify-between relative">
                    <div class="space-y-3">
                        <div class="flex justify-between items-start">
                            <div>
                                <span class="text-[10px] font-black uppercase tracking-wider text-slate-500">للشركات والمصانع الكبرى</span>
                                <h3 class="text-base font-black text-slate-900">المؤسسات (Enterprise)</h3>
                            </div>
                            <span class="p-2 rounded-xl bg-slate-100 text-sm">🏢</span>
                        </div>

                        <div class="pt-2 border-t border-slate-100">
                            <div class="text-2xl font-black text-slate-900 font-mono">1,499 <span class="text-xs font-bold text-slate-500">ج.م / شهر</span></div>
                            <div class="text-[11px] text-emerald-600 font-bold mt-0.5 font-mono">14,990 ج.م سنوياً (شهرين مجاناً)</div>
                            <div class="text-[10px] text-slate-400 font-mono mt-0.5">دولي: $99 / شهر ($990 سنوي)</div>
                        </div>

                        <div class="space-y-2 pt-3 border-t border-slate-100 text-xs text-slate-700 font-medium">
                            <div class="flex items-center gap-1.5"><span class="text-emerald-500 font-bold">✓</span> <strong>فروع ومخازن غير محدودة</strong></div>
                            <div class="flex items-center gap-1.5"><span class="text-emerald-500 font-bold">✓</span> <strong>مستخدمين غير محدودين</strong></div>
                            <div class="flex items-center gap-1.5"><span class="text-emerald-500 font-bold">✓</span> كافة ميزات النظام بالكامل</div>
                            <div class="flex items-center gap-1.5"><span class="text-emerald-500 font-bold">✓</span> جاهزية للربط مع <strong>الفاتورة الإلكترونية</strong></div>
                            <div class="flex items-center gap-1.5"><span class="text-emerald-500 font-bold">✓</span> نطاق خاص باسم شركتك (Domain)</div>
                            <div class="flex items-center gap-1.5"><span class="text-emerald-500 font-bold">✓</span> دعم فني ذو أولوية قصوى 24/7</div>
                            <div class="flex items-center gap-1.5"><span class="text-emerald-500 font-bold">✓</span> نسخ احتياطي لحظي وسحابي</div>
                        </div>
                    </div>

                    <div class="mt-5 pt-3 border-t border-slate-100">
                        <div class="text-center text-[11px] font-bold text-slate-500">للشركات ذات الأنشطة المتعددة والضخمة</div>
                    </div>
                </div>

            </div>
        </div>

        <!-- ========================================== -->
        <!-- 🧩 SECTION 5: ADD-ONS & HARDWARE BUNDLES   -->
        <!-- ========================================== -->
        <div class="avoid-break grid grid-cols-1 md:grid-cols-2 gap-4">
            
            <!-- Add-ons Box -->
            <div class="bg-white rounded-3xl p-6 shadow-sm border border-slate-200 card-shadow space-y-3">
                <h3 class="text-sm font-black text-slate-900 flex items-center gap-2">
                    <span>🧩 الإضافات المرنة حسب الطلب (Add-ons)</span>
                </h3>
                <p class="text-[11px] text-slate-500">يمكنك ترقية أي جزئية في باقتك دون الحاجة لتغيير الباقة بالكامل:</p>

                <div class="space-y-2 text-xs">
                    <div class="flex justify-between items-center p-2.5 rounded-xl bg-slate-50 border border-slate-200/60">
                        <span class="font-bold text-slate-800">🏬 إضافة فرع أو مخزن إضافي:</span>
                        <span class="font-black text-amber-600 font-mono">+100 ج.م / شهر</span>
                    </div>
                    <div class="flex justify-between items-center p-2.5 rounded-xl bg-slate-50 border border-slate-200/60">
                        <span class="font-bold text-slate-800">👤 إضافة كاشير / موظف إضافي:</span>
                        <span class="font-black text-amber-600 font-mono">+40 ج.م / شهر</span>
                    </div>
                    <div class="flex justify-between items-center p-2.5 rounded-xl bg-slate-50 border border-slate-200/60">
                        <span class="font-bold text-slate-800">📱 باقة فواتير وإشعارات واتساب (500 رسالة):</span>
                        <span class="font-black text-emerald-600 font-mono">100 ج.م</span>
                    </div>
                </div>
            </div>

            <!-- Hardware & Guarantee Box -->
            <div class="bg-white rounded-3xl p-6 shadow-sm border border-slate-200 card-shadow space-y-3">
                <h3 class="text-sm font-black text-slate-900 flex items-center gap-2">
                    <span>🎁 باقة المحل الجاهز والضمان (Turnkey Bundle)</span>
                </h3>
                <p class="text-[11px] text-slate-500">نوفر لك أجهزة الكاشير والتشغيل بالكامل مع تدريب فريقك:</p>

                <div class="space-y-2 text-xs text-slate-700">
                    <div class="flex items-center gap-2">
                        <span class="text-emerald-500 font-bold">✓</span>
                        <span><strong>أجهزة الكاشير:</strong> طابعات حرارية (USB/Bluetooth/LAN) وقوارئ باركود ليزر سريعة.</span>
                    </div>
                    <div class="flex items-center gap-2">
                        <span class="text-emerald-500 font-bold">✓</span>
                        <span><strong>إدخال البيانات:</strong> رفع شيتات الأصناف وقوائم الأسعار وتجهيز النظام فوراً.</span>
                    </div>
                    <div class="flex items-center gap-2">
                        <span class="text-emerald-500 font-bold">✓</span>
                        <span><strong>فترة تجريبية مجانية:</strong> 14 يوماً بكامل الميزات دون أي التزام بالدفع.</span>
                    </div>
                </div>
            </div>

        </div>

        <!-- ========================================== -->
        <!-- 📞 SECTION 6: FOOTER & CALL TO ACTION      -->
        <!-- ========================================== -->
        <div class="gradient-hero text-white rounded-3xl p-6 sm:p-8 text-center space-y-3 avoid-break">
            <h2 class="text-xl sm:text-2xl font-black text-amber-400">جاهز لنقل إدارة أعمالك وتجارتك إلى مستوى أعلى؟</h2>
            <p class="text-xs text-slate-300 max-w-xl mx-auto">
                تواصل معنا اليوم لبدء فترتك التجريبية المجانية (14 يوماً) أو لطلب زيارة توضيحية وعرض حي للنظام (Live Demo).
            </p>
            <div class="pt-2 flex flex-wrap items-center justify-center gap-6 text-xs font-bold text-slate-200">
                <div>📞 <strong>هاتف / واتساب:</strong> 01000000000</div>
                <div>🌐 <strong>الموقع الإلكتروني:</strong> https://sroor.baraa-solutions.com</div>
                <div>✉️ <strong>البريد الإلكتروني:</strong> sales@baraa-solutions.com</div>
            </div>
        </div>

    </div>

</body>
</html>
