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
    <div x-data="{ openTask: null, openSubmit: null, confirmSubmit: false, openRejectReason: null, alertOpen: true }" class="min-h-screen bg-slate-50 p-6" dir="rtl">
        <header class="flex flex-col md:flex-row md:items-center justify-between mb-8 gap-4">
            <div>
                <h1 class="text-2xl font-bold text-slate-800">Tanqia Skills</h1>
                <p class="text-slate-500 mt-1">أهلاً بك يا {{ auth()->user()->name }} @if (auth()->user()->department)
                        <span class="text-indigo-500 font-medium">({{ auth()->user()->department->name }})</span>
                    @endif 👋</p>
            </div>
            <form method="POST" action="{{ route('logout') }}">
                @csrf
                <button
                    class="px-5 py-2.5 bg-white border border-slate-200 text-slate-700 rounded-xl hover:bg-slate-50 hover:border-slate-300 transition shadow-sm flex items-center gap-2">
                    <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                            d="M17 16l4-4m0 0l-4-4m4 4H7m6 4v1a3 3 0 01-3 3H6a3 3 0 01-3-3V7a3 3 0 013-3h4a3 3 0 013 3v1">
                        </path>
                    </svg>
                    تسجيل خروج
                </button>
            </form>
        </header>

        @if (session('success'))
            <div x-show="alertOpen" x-init="setTimeout(() => alertOpen = false, 4000)" x-transition
                class="mb-6 p-4 rounded-xl bg-emerald-50 border border-emerald-200 text-emerald-700 flex justify-between items-center shadow-sm">
                <span class="flex items-center gap-2"><svg class="w-5 h-5" fill="none" stroke="currentColor"
                        viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                            d="M9 12l2 2 4-4m6 2a9 9 0 11-18 0 9 9 0 0118 0z"></path>
                    </svg> {{ session('success') }}</span>
                <button @click="alertOpen = false" class="text-emerald-400 hover:text-emerald-600">&times;</button>
            </div>
        @endif
        @if ($errors->any())
            <div x-show="alertOpen" x-init="setTimeout(() => alertOpen = false, 4000)" x-transition
                class="mb-6 p-4 rounded-xl bg-rose-50 border border-rose-200 text-rose-700 flex justify-between items-center shadow-sm">
                <span class="flex items-center gap-2"><svg class="w-5 h-5" fill="none" stroke="currentColor"
                        viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                            d="M12 9v2m0 4h.01m-6.938 4h13.856c1.54 0 2.502-1.667 1.732-3L13.732 4c-.77-1.333-2.694-1.333-3.464 0L3.34 16c-.77 1.333.192 3 1.732 3z">
                        </path>
                    </svg> {{ $errors->first() }}</span>
                <button @click="alertOpen = false" class="text-rose-400 hover:text-rose-600">&times;</button>
            </div>
        @endif

        @if ($pendingTasksCount > 0)
            <div x-data="{ show: true }" x-show="show" x-transition
                class="mb-6 p-4 rounded-xl bg-amber-50 border border-amber-200 text-amber-800 flex justify-between items-center shadow-sm">
                <span class="flex items-center gap-2"><svg class="w-5 h-5 animate-pulse" fill="none"
                        stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                            d="M15 17h5l-1.405-1.405A2.032 2.032 0 0118 14.158V11a6.002 6.002 0 00-4-5.659V5a2 2 0 10-4 0v.341C7.67 6.165 6 8.388 6 11v3.159c0 .538-.214 1.055-.595 1.436L4 17h5m6 0v1a3 3 0 11-6 0v-1m6 0H9">
                        </path>
                    </svg> تنبيه: لديك ({{ $pendingTasksCount }}) مهام واردة قيد الانتظار، تأكد من إنجازها وتسليمها
                    للإدارة.</span>
                <button @click="show = false" class="text-amber-500 hover:text-amber-700 transition"
                    title="إخفاء التنبيه">&times;</button>
            </div>
        @endif

        <div class="grid md:grid-cols-2 gap-6">
            <section class="bg-white rounded-2xl shadow-sm border border-slate-200 p-6">
                <h2 class="text-lg font-bold text-slate-800 mb-4 flex items-center gap-2"><span
                        class="w-2 h-6 bg-indigo-500 rounded-full"></span> المهام الواردة من الإدارة</h2>
                @if ($assignedTasks->count() > 0)
                    <div class="space-y-4">
                        @foreach ($assignedTasks as $index => $task)
                            <div class="p-4 bg-slate-50 rounded-xl border border-slate-100 hover:shadow-md transition cursor-pointer group"
                                @click="openTask = {{ \Illuminate\Support\Js::from($task) }}">
                                <div class="flex justify-between items-start mb-2">
                                    <div class="flex items-center gap-2">
                                        <span
                                            class="text-xs font-medium bg-indigo-100 text-indigo-700 px-2 py-1 rounded-full">المهمة
                                            رقم {{ $assignedTasks->count() - $index }}</span>
                                        @if ($task->pivot->status === 'completed')
                                            <span
                                                class="text-xs font-medium bg-emerald-100 text-emerald-700 px-2 py-1 rounded-full">مكتملة</span>
                                        @else
                                            <span
                                                class="text-xs font-medium bg-amber-100 text-amber-700 px-2 py-1 rounded-full">بانتظار
                                                التسليم</span>
                                        @endif
                                    </div>
                                    <span class="text-xs text-slate-400">{{ $task->task_date ? $task->task_date : $task->created_at->format('Y-m-d') }}</span>
                                </div>
                                <p
                                    class="text-slate-700 font-medium line-clamp-2 group-hover:text-indigo-600 transition">
                                    {{ $task->description }}</p>
                            </div>
                        @endforeach
                    </div>
                @else
                    <div class="text-center py-10 text-slate-400">
                        <svg class="w-12 h-12 mx-auto mb-3 text-slate-300" fill="none" stroke="currentColor"
                            viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.5"
                                d="M20 13V6a2 2 0 00-2-2H6a2 2 0 00-2 2v7m16 0v5a2 2 0 01-2 2H6a2 2 0 01-2-2v-5m16 0h-2.586a1 1 0 00-.707.293l-2.414 2.414a1 1 0 01-.707.293h-3.172a1 1 0 01-.707-.293l-2.414-2.414A1 1 0 006.586 13H4">
                            </path>
                        </svg>
                        <p>لا توجد مهام واردة لك اليوم.</p>
                    </div>
                @endif
            </section>

            <section class="bg-white rounded-2xl shadow-sm border border-slate-200 p-6">
                <div class="flex justify-between items-center mb-4">
                    <h2 class="text-lg font-bold text-slate-800 flex items-center gap-2"><span
                            class="w-2 h-6 bg-emerald-500 rounded-full"></span> المهام التي قمت بإرسالها</h2>
                </div>

                @if ($submittedTasks->count() > 0)
                    <div class="space-y-3">
                        @foreach ($submittedTasks as $sub)
                            @php
                                $colors = match ($sub->status) {
                                    'approved' => [
                                        'bg' => 'bg-emerald-50',
                                        'border' => 'border-emerald-200',
                                        'text' => 'text-emerald-700',
                                        'status' => 'مقبولة بنجاح',
                                    ],
                                    'rejected' => [
                                        'bg' => 'bg-rose-50',
                                        'border' => 'border-rose-200',
                                        'text' => 'text-rose-700',
                                        'status' => 'مرفوضة (اضغط للسبب)',
                                    ],
                                    default => [
                                        'bg' => 'bg-amber-50',
                                        'border' => 'border-amber-200',
                                        'text' => 'text-amber-700',
                                        'status' => 'جاري التحليل',
                                    ],
                                };
                            @endphp
                            <div class="p-4 {{ $colors['bg'] }} border {{ $colors['border'] }} rounded-xl transition hover:shadow-sm flex justify-between items-center"
                                @if ($sub->status === 'rejected') @click="openRejectReason = {{ \Illuminate\Support\Js::from($sub->admin_feedback) }}" role="button" tabindex="0" @endif>
                                <div>
                                    <p class="font-medium text-slate-800">{{ Str::limit($sub->description, 60) }}</p>
                                    <span
                                        class="text-xs text-slate-500">{{ $sub->submission_date ? $sub->submission_date : $sub->created_at->format('Y-m-d') }}</span>
                                </div>
                                <span
                                    class="px-3 py-1 text-xs font-semibold rounded-full {{ $colors['text'] }} bg-white/50">{{ $colors['status'] }}</span>
                            </div>
                        @endforeach
                    </div>
                @else
                    <div class="text-center py-10 text-slate-400">
                        <svg class="w-12 h-12 mx-auto mb-3 text-slate-300" fill="none" stroke="currentColor"
                            viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.5"
                                d="M9 5H7a2 2 0 00-2 2v12a2 2 0 002 2h10a2 2 0 002-2V7a2 2 0 00-2-2h-2M9 5a2 2 0 002 2h2a2 2 0 002-2M9 5a2 2 0 012-2h2a2 2 0 012 2">
                            </path>
                        </svg>
                        <p>لم تقم بإرسال أي مهام بعد.</p>
                    </div>
                @endif
            </section>
        </div>

        <!-- Task Detail Modal -->
        <div x-show="openTask" x-transition:enter="transition ease-out duration-300"
            x-transition:enter-start="opacity-0 translate-y-4" x-transition:enter-end="opacity-100 translate-y-0"
            x-transition:leave="transition ease-in duration-200" x-transition:leave-start="opacity-100 translate-y-0"
            x-transition:leave-end="opacity-0 translate-y-4"
            class="fixed inset-0 z-50 flex items-center justify-center p-4 bg-slate-900/40 backdrop-blur-sm"
            @keydown.escape.window="openTask = null">
            <div @click.outside="openTask = null"
                class="bg-white rounded-2xl shadow-2xl w-full max-w-lg p-6 transform transition-all">
                <h3 class="text-xl font-bold text-slate-800 mb-4">تفاصيل المهمة</h3>
                <div class="bg-slate-50 p-4 rounded-xl border border-slate-200 text-slate-700 mb-6 leading-relaxed"
                    x-text="openTask?.description">
                </div>
                <div class="flex justify-end gap-3">
                    <template x-if="openTask?.file_path">
                        <a :href="'{{ route('download') }}?path=' + openTask.file_path" target="_blank"
                            class="px-5 py-2.5 bg-slate-100 hover:bg-slate-200 text-slate-700 rounded-xl transition flex items-center gap-2">
                            <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                    d="M4 16v1a3 3 0 003 3h10a3 3 0 003-3v-1m-4-4l-4 4m0 0l-4-4m4 4V4"></path>
                            </svg> تحميل المرفق
                        </a>
                    </template>
                    <template x-if="openTask?.pivot?.status !== 'completed'">
                        <button @click="openSubmit = openTask.id; openTask = null"
                            class="px-5 py-2.5 bg-emerald-600 hover:bg-emerald-700 text-white rounded-xl transition shadow-lg shadow-emerald-500/20">تسليم
                            الإنجاز</button>
                    </template>
                    <button @click="openTask = null"
                        class="px-5 py-2.5 bg-indigo-600 hover:bg-indigo-700 text-white rounded-xl transition shadow-lg shadow-indigo-500/20">إغلاق</button>
                </div>
            </div>
        </div>

        <!-- Submit Task Modal -->
        <div x-show="openSubmit !== null" x-transition style="display: none;"
            class="fixed inset-0 z-50 flex items-center justify-center p-4 bg-slate-900/40 backdrop-blur-sm"
            @keydown.escape.window="openSubmit = null">
            <div @click.outside="openSubmit = null" class="bg-white rounded-2xl shadow-2xl w-full max-w-md p-6">
                <h3 class="text-xl font-bold text-slate-800 mb-4">إرسال مهمة للإدارة</h3>
                <form id="submitTaskForm" method="POST" action="{{ route('employee.submit_task') }}"
                    enctype="multipart/form-data" class="space-y-4" @submit.prevent="if(!document.querySelector('input[name=file]').value) { if(!confirm('أنت على وشك تسليم المهمة بدون إرفاق ملف إنجاز. هل أنت متأكد من المتابعة؟')) return; } confirmSubmit = true">
                    @csrf
                    <input type="hidden" name="task_id" :value="openSubmit">
                    <div>
                        <label class="block text-sm font-medium text-slate-700 mb-1">وصف ما قمت بإنجازه *</label>
                        <textarea name="description" rows="3" required
                            class="w-full px-4 py-3 rounded-xl border border-slate-300 focus:border-indigo-500 focus:ring-4 focus:ring-indigo-500/20 outline-none transition resize-none"></textarea>
                    </div>
                    <div>
                        <label class="block text-sm font-medium text-slate-700 mb-1">تاريخ الإنجاز (التسليم) *</label>
                        <input type="date" name="submission_date" required value="{{ date('Y-m-d') }}" class="w-full px-4 py-3 rounded-xl border border-slate-300 focus:border-indigo-500 focus:ring-4 focus:ring-indigo-500/20 outline-none transition">
                    </div>
                    <div x-data="{ fileName: '' }">
                        <label class="block text-sm font-medium text-slate-700 mb-1">إرفاق ملف (اختياري)</label>
                        <div class="border-2 border-dashed border-slate-300 rounded-xl p-6 text-center hover:bg-slate-50 transition cursor-pointer relative">
                            <input type="file" name="file" class="absolute inset-0 w-full h-full opacity-0 cursor-pointer" @change="fileName = $event.target.files.length ? $event.target.files[0].name : ''">
                            <svg class="w-8 h-8 mx-auto text-slate-400 mb-2" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M7 16a4 4 0 01-.88-7.903A5 5 0 1115.9 6L16 6a5 5 0 011 9.9M15 13l-3-3m0 0l-3 3"></path>
                            </svg>
                            <p class="text-sm font-medium text-slate-700" x-text="fileName ? 'تم إرفاق: ' + fileName : 'اسحب الملف هنا أو اضغط للاختيار'"></p>
                            <p class="text-xs text-slate-400 mt-1">اختياري: يمكنك رفع ملف بصيغة (PDF, الصور، أو غيره).</p>
                        </div>
                    </div>
                    <div class="flex justify-end gap-3 mt-6">
                        <button type="button" @click="openSubmit = null"
                            class="px-5 py-2.5 bg-slate-100 hover:bg-slate-200 text-slate-700 rounded-xl transition">إلغاء</button>
                        <button type="submit"
                            class="px-5 py-2.5 bg-indigo-600 hover:bg-indigo-700 text-white rounded-xl transition shadow-lg shadow-indigo-500/20">إرسال</button>
                    </div>
                </form>
            </div>
        </div>

        <!-- Confirmation Modal -->
        <div x-show="confirmSubmit" x-transition style="display: none;"
            class="fixed inset-0 z-[60] flex items-center justify-center p-4 bg-slate-900/50 backdrop-blur-sm"
            @keydown.escape.window="confirmSubmit = false">
            <div @click.outside="confirmSubmit = false"
                class="bg-white rounded-2xl shadow-2xl w-full max-w-sm p-6 text-center transform transition-all">
                <div
                    class="w-16 h-16 mx-auto bg-indigo-50 rounded-full flex items-center justify-center mb-4 text-indigo-600">
                    <svg class="w-8 h-8 ml-1" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                            d="M12 19l9 2-9-18-9 18 9-2zm0 0v-8"></path>
                    </svg>
                </div>
                <h3 class="text-xl font-bold text-slate-800 mb-2">تأكيد الإرسال</h3>
                <p class="text-slate-500 text-sm mb-6">هل أنت متأكد من إرسال المهمة المنجزة للإدارة؟ لا يمكن التعديل
                    عليها لاحقاً.</p>
                <div class="flex justify-center gap-3">
                    <button type="button" @click="confirmSubmit = false"
                        class="px-5 py-2.5 bg-slate-100 hover:bg-slate-200 text-slate-700 font-medium rounded-xl transition">إلغاء</button>
                    <button type="button" @click="document.getElementById('submitTaskForm').submit()"
                        class="px-5 py-2.5 bg-indigo-600 hover:bg-indigo-700 text-white font-medium rounded-xl transition shadow-lg shadow-indigo-500/30">تأكيد
                        الإرسال</button>
                </div>
            </div>
        </div>

        <!-- Rejection Modal -->
        <div x-show="openRejectReason" x-transition style="display: none;"
            class="fixed inset-0 z-50 flex items-center justify-center p-4 bg-slate-900/40 backdrop-blur-sm"
            @keydown.escape.window="openRejectReason = null">
            <div @click.outside="openRejectReason = null"
                class="bg-white rounded-2xl shadow-2xl w-full max-w-md p-6 transform transition-all">
                <h3 class="text-xl font-bold text-rose-600 mb-4 flex items-center gap-2"><svg class="w-6 h-6"
                        fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                            d="M12 9v2m0 4h.01m-6.938 4h13.856c1.54 0 2.502-1.667 1.732-3L13.732 4c-.77-1.333-2.694-1.333-3.464 0L3.34 16c-.77 1.333.192 3 1.732 3z">
                        </path>
                    </svg> سبب رفض المهمة</h3>
                <div class="bg-rose-50 p-4 rounded-xl border border-rose-200 text-rose-700 mb-6 whitespace-pre-line leading-relaxed"
                    x-text="openRejectReason"></div>
                <div class="flex justify-end">
                    <button @click="openRejectReason = null"
                        class="px-5 py-2.5 bg-slate-100 hover:bg-slate-200 text-slate-700 rounded-xl transition font-medium">إغلاق</button>
                </div>
            </div>
        </div>
    </div>
</body>

</html>
