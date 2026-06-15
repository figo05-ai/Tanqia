<!DOCTYPE html>
<html lang="ar" dir="rtl">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>لوحة التحكم | Tanqia Skills</title>
    <link href="https://fonts.googleapis.com/css2?family=Cairo:wght@400;500;600;700&display=swap" rel="stylesheet">
    <script src="https://cdn.tailwindcss.com"></script>
    <script defer src="https://cdn.jsdelivr.net/npm/alpinejs@3.x.x/dist/cdn.min.js"></script>
</head>

<body class="bg-slate-50" style="font-family: 'Cairo', sans-serif;">
    <div x-data="{ openAddModal: false, addType: null, openReject: null, alertOpen: true, isUploading: false }" class="min-h-screen p-6">
        <header class="flex flex-col md:flex-row justify-between items-start md:items-center mb-8 gap-4">
            <h1 class="text-2xl font-bold text-slate-800">Tanqia Skills - لوحة التحكم</h1>
            <div class="flex items-center gap-3">
                <div x-data="{ isBackingUp: false }">
                    <a href="{{ route('admin.backup') }}" 
                        @click="isBackingUp = true; setTimeout(() => isBackingUp = false, 5000)"
                        class="px-5 py-2.5 bg-emerald-600 border border-emerald-600 text-white rounded-xl hover:bg-emerald-700 transition shadow-sm flex items-center gap-2">
                        <svg x-show="!isBackingUp" class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 16v1a3 3 0 003 3h10a3 3 0 003-3v-1m-4-4l-4 4m0 0l-4-4m4 4V4"></path>
                        </svg>
                        <svg x-show="isBackingUp" class="animate-spin w-4 h-4 text-white" style="display: none;" xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24">
                            <circle class="opacity-25" cx="12" cy="12" r="10" stroke="currentColor" stroke-width="4"></circle>
                            <path class="opacity-75" fill="currentColor" d="M4 12a8 8 0 018-8V0C5.373 0 0 5.373 0 12h4z"></path>
                        </svg>
                        <span x-text="isBackingUp ? 'جاري التحميل...' : 'تحميل نسخة احتياطية'"></span>
                    </a>
                </div>
                <form method="POST" action="{{ route('logout') }}">
                    @csrf
                    <button
                        class="px-5 py-2.5 bg-white border border-slate-200 text-slate-700 rounded-xl hover:bg-slate-50 transition shadow-sm flex items-center gap-2">
                        <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                d="M17 16l4-4m0 0l-4-4m4 4H7m6 4v1a3 3 0 01-3 3H6a3 3 0 01-3-3V7a3 3 0 013-3h4a3 3 0 013 3v1">
                            </path>
                        </svg>
                        تسجيل خروج
                    </button>
                </form>
            </div>
        </header>

        @if (session('success'))
            <div x-show="alertOpen" x-init="setTimeout(() => alertOpen = false, 4000)" x-transition
                class="mb-6 p-4 rounded-xl bg-emerald-50 border border-emerald-200 text-emerald-700 flex justify-between items-center shadow-sm">
                <span>{{ session('success') }}</span>
                <button @click="alertOpen = false" class="text-emerald-400 hover:text-emerald-600">&times;</button>
            </div>
        @endif
        @if ($errors->any())
            <div x-show="alertOpen" x-init="setTimeout(() => alertOpen = false, 4000)" x-transition
                class="mb-6 p-4 rounded-xl bg-rose-50 border border-rose-200 text-rose-700 flex justify-between items-center shadow-sm">
                <span>{{ $errors->first() }}</span>
                <button @click="alertOpen = false" class="text-rose-400 hover:text-rose-600">&times;</button>
            </div>
        @endif

        @if (isset($pendingSubmissionsCount) && $pendingSubmissionsCount > 0)
            <div x-data="{ show: true }" x-show="show" x-transition
                class="mb-6 p-4 rounded-xl bg-amber-50 border border-amber-200 text-amber-800 flex justify-between items-center shadow-sm">
                <span class="flex items-center gap-2"><svg class="w-5 h-5 animate-pulse" fill="none"
                        stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                            d="M15 17h5l-1.405-1.405A2.032 2.032 0 0118 14.158V11a6.002 6.002 0 00-4-5.659V5a2 2 0 10-4 0v.341C7.67 6.165 6 8.388 6 11v3.159c0 .538-.214 1.055-.595 1.436L4 17h5m6 0v1a3 3 0 11-6 0v-1m6 0H9">
                        </path>
                    </svg> تنبيه: يوجد لديك ({{ $pendingSubmissionsCount }}) إنجازات جديدة بانتظار المراجعة.</span>
                <button @click="show = false" class="text-amber-500 hover:text-amber-700 transition"
                    title="إخفاء التنبيه">&times;</button>
            </div>
        @endif

        <div class="grid lg:grid-cols-3 gap-6">
            <div class="lg:col-span-1 space-y-6">
                <div class="bg-white rounded-2xl shadow-sm border border-slate-200 p-6">
                    <div class="flex justify-between items-center mb-4">
                        <h2 class="text-lg font-bold text-slate-800">الشركات</h2>
                        <button @click="openAddModal = true; addType = null"
                            class="text-sm text-indigo-600 hover:text-indigo-700 font-medium">+ اضافة موظف</button>
                    </div>

                    <form method="GET" action="{{ route('dashboard') }}" class="mb-4 flex flex-col sm:flex-row gap-3">
                        <div class="relative flex-grow">
                            <input type="text" name="search" value="{{ $search ?? '' }}" placeholder="ابحث عن شركة..."
                                class="w-full pl-10 pr-4 py-2 rounded-xl border border-slate-300 focus:border-indigo-500 focus:ring-2 focus:ring-indigo-500/20 outline-none transition">
                            <button type="submit" class="absolute left-3 top-2.5 text-slate-400 hover:text-indigo-600 transition">
                                <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M21 21l-6-6m2-5a7 7 0 11-14 0 7 7 0 0114 0z"></path></svg>
                            </button>
                        </div>
                        <div class="relative">
                            <select name="sort_by" onchange="this.form.submit()"
                                class="appearance-none w-full sm:w-48 bg-white pl-10 pr-4 py-2 rounded-xl border border-slate-300 focus:border-indigo-500 focus:ring-2 focus:ring-indigo-500/20 outline-none transition cursor-pointer">
                                <option value="name" @if(($sortBy ?? 'name') == 'name') selected @endif>ترتيب أبجدي</option>
                                <option value="progress" @if(($sortBy ?? '') == 'progress') selected @endif>الأعلى إنجازاً</option>
                                <option value="users_count" @if(($sortBy ?? '') == 'users_count') selected @endif>الأكثر موظفين</option>
                            </select>
                            <div class="absolute left-3 top-2.5 text-slate-400 pointer-events-none">
                                <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M8 9l4-4 4 4m0 6l-4 4-4-4"></path></svg>
                            </div>
                        </div>
                    </form>

                    @if ($companies->count() > 0)
                        <div class="space-y-4 max-h-[22rem] overflow-y-auto pr-2 -mr-2 custom-scrollbar">
                            @foreach ($companies as $company)
                                <a href="{{ route('company.show', $company->id) }}"
                                    class="block p-4 bg-slate-50 rounded-2xl border border-slate-200 hover:shadow-md hover:border-indigo-300 transition group">
                                    <div class="flex items-center gap-3">
                                        <div class="w-10 h-10 rounded-lg bg-indigo-100 flex items-center justify-center text-indigo-500 group-hover:bg-indigo-500 group-hover:text-white transition-colors">
                                            <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 21V5a2 2 0 00-2-2H7a2 2 0 00-2 2v16m14 0h2m-2 0h-5m-9 0H3m2 0h5M9 7h1m-1 4h1m4-4h1m-1 4h1m-5 10v-5a1 1 0 011-1h2a1 1 0 011 1v5m-4 0h4"></path></svg>
                                        </div>
                                        <div class="flex-1">
                                            <div class="font-bold text-slate-800 group-hover:text-indigo-600 transition-colors">{{ $company->name }}</div>
                                            <div class="text-sm text-slate-500">
                                                {{ $company->users_count }} موظف
                                            </div>
                                        </div>
                                    </div>
                                    <div class="mt-4">
                                        <div class="flex justify-between items-center text-xs mb-1.5">
                                            <span class="text-slate-600 font-medium">إنجاز المهام
                                                ({{ $company->completed_tasks }}/{{ $company->total_tasks }})</span>
                                            <span class="text-indigo-600 font-bold">{{ $company->progress }}%</span>
                                        </div>
                                        <div class="w-full bg-slate-200 rounded-full h-2 overflow-hidden">
                                            <div class="bg-gradient-to-r from-indigo-400 to-indigo-600 h-2 rounded-full transition-all duration-500"
                                                style="width: {{ $company->progress }}%"></div>
                                        </div>
                                    </div>
                                </a>
                            @endforeach
                        </div>
                    @else
                        <div class="text-center py-10 text-slate-400 text-sm">
                            @if(isset($search) && $search)
                                لا توجد شركات تطابق بحثك.
                            @else
                                لا توجد شركات مسجلة حتى الآن.
                            @endif
                        </div>
                    @endif
                </div>

                <!-- Add Employee Modal -->
                <div x-show="openAddModal" x-transition style="display: none;"
                    class="fixed inset-0 z-50 flex items-center justify-center p-4 bg-slate-900/40 backdrop-blur-sm"
                    @keydown.escape.window="openAddModal = false">
                    <div @click.outside="openAddModal = false"
                        class="bg-white rounded-2xl shadow-2xl w-full max-w-md p-6">
                        
                        <!-- Step 1: Choose Add Method -->
                        <div x-show="addType === null" x-transition:enter="transition ease-out duration-300">
                            <h3 class="text-xl font-bold text-slate-800 mb-6 text-center">اختر طريقة إضافة الموظف</h3>
                            <div class="grid grid-cols-2 gap-4">
                                <button @click="addType = 'manual'" class="flex flex-col items-center justify-center gap-3 p-6 border-2 border-slate-200 rounded-2xl hover:border-indigo-500 hover:bg-indigo-50 transition group">
                                    <div class="w-12 h-12 bg-indigo-100 text-indigo-600 rounded-full flex items-center justify-center group-hover:scale-110 transition-transform">
                                        <svg class="w-6 h-6" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M16 7a4 4 0 11-8 0 4 4 0 018 0zM12 14a7 7 0 00-7 7h14a7 7 0 00-7-7z"></path></svg>
                                    </div>
                                    <span class="font-bold text-slate-700">إضافة يدوية</span>
                                </button>
                                <button @click="addType = 'file'" class="flex flex-col items-center justify-center gap-3 p-6 border-2 border-slate-200 rounded-2xl hover:border-emerald-500 hover:bg-emerald-50 transition group">
                                    <div class="w-12 h-12 bg-emerald-100 text-emerald-600 rounded-full flex items-center justify-center group-hover:scale-110 transition-transform">
                                        <svg class="w-6 h-6" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12h6m-6 4h6m2 5H7a2 2 0 01-2-2V5a2 2 0 012-2h5.586a1 1 0 01.707.293l5.414 5.414a1 1 0 01.293.707V19a2 2 0 01-2 2z"></path></svg>
                                    </div>
                                    <span class="font-bold text-slate-700">رفع ملف (Excel)</span>
                                </button>
                            </div>
                            <div class="mt-6 text-center">
                                <button @click="openAddModal = false" class="text-sm text-slate-500 hover:text-slate-800">إلغاء وإغلاق</button>
                            </div>
                        </div>

                        <!-- Step 2A: Upload File Form -->
                        <form x-show="addType === 'file'" style="display: none;" method="POST" action="{{ route('employee.upload') }}" enctype="multipart/form-data"
                            class="space-y-4" @submit="isUploading = true">
                            <h3 class="text-xl font-bold text-slate-800 mb-4 flex justify-between items-center">رفع ملف الموظفين <button type="button" @click="addType = null" class="text-sm font-normal text-indigo-600 hover:underline">الرجوع للاختيار</button></h3>
                            @csrf
                            <div
                                x-data="{ fileName: '' }" class="border-2 border-dashed border-slate-300 rounded-xl p-8 text-center hover:bg-slate-50 transition cursor-pointer relative">
                                <input type="file" name="file" accept=".xlsx,.csv" required @change="fileName = $event.target.files.length ? $event.target.files[0].name : ''"
                                    class="absolute inset-0 w-full h-full opacity-0 cursor-pointer">
                                <svg class="w-10 h-10 mx-auto text-indigo-400 mb-3" fill="none" stroke="currentColor"
                                    viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                        d="M9 13h6m-3-3v6m-9 1V7a2 2 0 012-2h6l2 2h6a2 2 0 012 2v8a2 2 0 01-2 2H5a2 2 0 01-2-2z">
                                    </path>
                                </svg>
                                <p class="text-sm font-medium text-slate-700" x-text="fileName ? 'تم اختيار: ' + fileName : 'اختر الملف (Excel أو CSV)'"></p>
                                <p class="text-xs text-slate-400 mt-1">ترتيب الأعمدة: الاسم، البريد، كلمة المرور،
                                    الشركة، القسم (اختياري)</p>
                            </div>
                            <div class="flex justify-end gap-3">
                                <button type="button" @click="openAddModal = false"
                                    class="px-5 py-2.5 bg-slate-100 hover:bg-slate-200 text-slate-700 rounded-xl transition">إلغاء</button>
                                <button type="submit" :disabled="isUploading"
                                    class="px-5 py-2.5 bg-indigo-600 hover:bg-indigo-700 text-white rounded-xl transition shadow-lg shadow-indigo-500/20 disabled:opacity-70 disabled:cursor-not-allowed flex items-center justify-center gap-2">
                                    <span x-show="!isUploading">رفع الملف</span>
                                    <span x-show="isUploading" class="flex items-center gap-2">
                                        <svg class="animate-spin w-4 h-4 text-white"
                                            xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24">
                                            <circle class="opacity-25" cx="12" cy="12" r="10"
                                                stroke="currentColor" stroke-width="4"></circle>
                                            <path class="opacity-75" fill="currentColor"
                                                d="M4 12a8 8 0 018-8V0C5.373 0 0 5.373 0 12h4z"></path>
                                        </svg>
                                        جاري الرفع...
                                    </span>
                                </button>
                            </div>
                        </form>

                        <!-- Step 2B: Manual Add Form -->
                        <form x-show="addType === 'manual'" style="display: none;" method="POST" action="{{ route('employee.store_manual') }}" class="space-y-4">
                            <h3 class="text-xl font-bold text-slate-800 mb-4 flex justify-between items-center">إضافة موظف يدوياً <button type="button" @click="addType = null" class="text-sm font-normal text-indigo-600 hover:underline">الرجوع للاختيار</button></h3>
                            @csrf
                            <div class="grid grid-cols-1 gap-3 max-h-[60vh] overflow-y-auto custom-scrollbar px-1">
                                <div>
                                    <label class="block text-sm font-medium text-slate-700 mb-1">الاسم *</label>
                                    <input type="text" name="name" required class="w-full px-4 py-2.5 rounded-xl border border-slate-300 focus:border-indigo-500 focus:ring-4 focus:ring-indigo-500/20 outline-none transition">
                                </div>
                                <div>
                                    <label class="block text-sm font-medium text-slate-700 mb-1">البريد الإلكتروني *</label>
                                    <input type="email" name="email" required dir="ltr" class="w-full px-4 py-2.5 rounded-xl border border-slate-300 focus:border-indigo-500 focus:ring-4 focus:ring-indigo-500/20 outline-none transition text-left">
                                </div>
                                <div>
                                    <label class="block text-sm font-medium text-slate-700 mb-1">كلمة المرور *</label>
                                    <input type="password" name="password" required dir="ltr" class="w-full px-4 py-2.5 rounded-xl border border-slate-300 focus:border-indigo-500 focus:ring-4 focus:ring-indigo-500/20 outline-none transition text-left">
                                </div>
                                <div>
                                    <label class="block text-sm font-medium text-slate-700 mb-1">الشركة *</label>
                                    <input type="text" name="company_name" list="companies_list" required placeholder="اختر أو اكتب اسم الشركة" class="w-full px-4 py-2.5 rounded-xl border border-slate-300 focus:border-indigo-500 focus:ring-4 focus:ring-indigo-500/20 outline-none transition">
                                    <datalist id="companies_list">
                                        @foreach($companies as $comp)
                                            <option value="{{ $comp->name }}">
                                        @endforeach
                                    </datalist>
                                </div>
                                <div>
                                    <label class="block text-sm font-medium text-slate-700 mb-1">القسم / المسمى الوظيفي</label>
                                    <input type="text" name="department_name" placeholder="اختياري (الافتراضي: قسم عام)" class="w-full px-4 py-2.5 rounded-xl border border-slate-300 focus:border-indigo-500 focus:ring-4 focus:ring-indigo-500/20 outline-none transition">
                                </div>
                            </div>
                            <div class="flex justify-end gap-3 pt-3">
                                <button type="button" @click="openAddModal = false"
                                    class="px-5 py-2.5 bg-slate-100 hover:bg-slate-200 text-slate-700 rounded-xl transition">إلغاء</button>
                                <button type="submit"
                                    class="px-5 py-2.5 bg-indigo-600 hover:bg-indigo-700 text-white rounded-xl transition shadow-lg shadow-indigo-500/20">حفظ الموظف</button>
                            </div>
                        </form>

                    </div>
                </div>
            </div>

            <div class="lg:col-span-2 bg-white rounded-2xl shadow-sm border border-slate-200 p-6">
                <h2 class="text-lg font-bold text-slate-800 mb-4 flex items-center gap-2">
                    <span class="w-2 h-6 bg-emerald-500 rounded-full"></span> مراجعة المهام المنجزة من الموظفين
                    @if (isset($pendingSubmissionsCount) && $pendingSubmissionsCount > 0)
                        <span
                            class="bg-rose-500 text-white text-xs font-bold px-2 py-0.5 rounded-full animate-pulse">{{ $pendingSubmissionsCount }}
                            جديد</span>
                    @endif
                </h2>

                @if (isset($submissions) && $submissions->count() > 0)
                    <div class="space-y-4">
                        @foreach ($submissions as $sub)
                            <div class="p-4 bg-slate-50 rounded-xl border border-slate-100 hover:shadow-md transition">
                                <div
                                    class="flex flex-col md:flex-row justify-between items-start md:items-center gap-3 mb-3">
                                    <div>
                                        <span
                                            class="font-bold text-slate-800">{{ $sub->user->name ?? 'موظف محذوف' }}</span>
                                        <span
                                            class="text-slate-400 text-sm">({{ $sub->user->company->name ?? 'شركة غير معروفة' }})</span>
                                    </div>
                                    <div class="flex items-center gap-2">
                                        <span
                                            class="px-2 py-1 text-xs font-semibold rounded-full {{ match ($sub->status) {'pending' => 'bg-amber-100 text-amber-700','approved' => 'bg-emerald-100 text-emerald-700',default => 'bg-rose-100 text-rose-700'} }}">
                                            {{ match ($sub->status) {'pending' => 'قيد المراجعة','approved' => 'مقبولة',default => 'مرفوضة'} }}
                                        </span>
                                        <span
                                            class="text-xs text-slate-500">تاريخ الإنجاز: {{ $sub->submission_date ? $sub->submission_date : $sub->created_at->format('Y-m-d') }}</span>
                                    </div>
                                </div>
                                @if ($sub->task)
                                    <div
                                        class="mb-3 p-3 bg-indigo-50 border border-indigo-100 rounded-lg text-sm text-indigo-800">
                                        <span class="font-bold">رداً على مهمة:</span>
                                        {{ Str::limit($sub->task->description, 100) }}
                                    </div>
                                @endif
                                <p class="text-slate-700 mb-3">{{ $sub->description }}</p>
                                @if ($sub->file_path)
                                    <a href="{{ route('download', ['path' => $sub->file_path]) }}"
                                        class="inline-flex items-center gap-2 px-3 py-1.5 bg-white border border-slate-200 text-slate-700 rounded-lg hover:bg-slate-50 transition text-sm mb-3">
                                        <svg class="w-4 h-4" fill="none" stroke="currentColor"
                                            viewBox="0 0 24 24">
                                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                                d="M4 16v1a3 3 0 003 3h10a3 3 0 003-3v-1m-4-4l-4 4m0 0l-4-4m4 4V4">
                                            </path>
                                        </svg>
                                        تحميل المرفق
                                    </a>
                                @endif

                                @if ($sub->status === 'pending')
                                    <div class="flex gap-2 mt-2">
                                        <form method="POST"
                                            action="{{ route('dashboard.submissions.update', $sub->id) }}"
                                            class="flex-1">
                                            @csrf @method('PUT')
                                            <input type="hidden" name="status" value="approved">
                                            <button
                                                class="w-full py-2 bg-emerald-600 hover:bg-emerald-700 text-white text-sm font-medium rounded-xl transition shadow-sm">قبول</button>
                                        </form>
                                        <button @click="openReject = {{ $sub->id }}"
                                            class="flex-1 py-2 bg-rose-600 hover:bg-rose-700 text-white text-sm font-medium rounded-xl transition shadow-sm">رفض</button>
                                    </div>
                                @elseif($sub->status === 'rejected')
                                    <div
                                        class="mt-2 p-3 bg-rose-50 border border-rose-200 rounded-lg text-rose-700 text-sm">
                                        <span class="font-bold">سبب الرفض:</span> {{ $sub->admin_feedback }}
                                    </div>
                                    <form method="POST"
                                        action="{{ route('dashboard.submissions.update', $sub->id) }}"
                                        class="mt-3">
                                        @csrf @method('PUT')
                                        <input type="hidden" name="status" value="pending">
                                        <button
                                            class="px-4 py-2 bg-slate-100 hover:bg-slate-200 text-slate-700 text-sm font-medium rounded-xl transition shadow-sm border border-slate-200 w-full md:w-auto">تغيير
                                            الحالة إلى قيد المراجعة</button>
                                    </form>
                                @endif
                            </div>
                        @endforeach
                    </div>
                @else
                    <div class="text-center py-12 text-slate-400">
                        <svg class="w-12 h-12 mx-auto mb-3 text-slate-300" fill="none" stroke="currentColor"
                            viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.5"
                                d="M9 5H7a2 2 0 00-2 2v12a2 2 0 002 2h10a2 2 0 002-2V7a2 2 0 00-2-2h-2M9 5a2 2 0 002 2h2a2 2 0 002-2M9 5a2 2 0 012-2h2a2 2 0 012 2">
                            </path>
                        </svg>
                        <p>لا يوجد أي مهام منجزة (مُرسلة) حتى الآن.</p>
                    </div>
                @endif
            </div>
        </div>

        <!-- Reject Modal -->
        <div x-show="openReject !== null" x-transition
            class="fixed inset-0 z-50 flex items-center justify-center p-4 bg-slate-900/40 backdrop-blur-sm"
            @keydown.escape.window="openReject = null">
            <div @click.outside="openReject = null" class="bg-white rounded-2xl shadow-2xl w-full max-w-md p-6">
                <h3 class="text-xl font-bold text-rose-600 mb-4">رفض المهمة المنجزة</h3>
                <form :action="'{{ url('/dashboard/submissions') }}/' + openReject + '/status'" method="POST">
                    @csrf @method('PUT')
                    <input type="hidden" name="status" value="rejected">
                    <textarea name="admin_feedback" rows="3" required placeholder="السبب / الملاحظات (سيظهر للموظف) *"
                        class="w-full px-4 py-3 rounded-xl border border-slate-300 focus:border-rose-500 focus:ring-4 focus:ring-rose-500/20 outline-none transition resize-none mb-4"></textarea>
                    <div class="flex justify-end gap-3">
                        <button type="button" @click="openReject = null"
                            class="px-5 py-2.5 bg-slate-100 hover:bg-slate-200 text-slate-700 rounded-xl transition">إلغاء</button>
                        <button type="submit"
                            class="px-5 py-2.5 bg-rose-600 hover:bg-rose-700 text-white rounded-xl transition shadow-lg shadow-rose-500/20">تأكيد
                            الرفض</button>
                    </div>
                </form>
            </div>
        </div>
    </div>

    <style>
        .custom-scrollbar::-webkit-scrollbar {
            width: 6px;
        }

        .custom-scrollbar::-webkit-scrollbar-track {
            background: #f1f5f9;
            border-radius: 4px;
        }

        .custom-scrollbar::-webkit-scrollbar-thumb {
            background: #cbd5e1;
            border-radius: 4px;
        }

        .custom-scrollbar::-webkit-scrollbar-thumb:hover {
            background: #94a3b8;
        }
    </style>
</body>

</html>
