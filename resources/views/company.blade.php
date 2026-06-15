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
    <div x-data="{ openAssign: false, openEdit: false, editEmployee: null, selectedUsers: [], selectAllCompany: false, confirmDelete: false, isDeleting: false, isAssigning: false }" class="min-h-screen bg-slate-50 p-6" dir="rtl">
        <header class="flex flex-col md:flex-row justify-between items-start md:items-center mb-6 gap-4">
            <div class="flex items-center gap-3">
                <a href="{{ route('dashboard') }}"
                    class="p-2 bg-white rounded-xl border border-slate-200 hover:bg-slate-50 transition shadow-sm">
                    <svg class="w-5 h-5 text-slate-600" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                            d="M10 19l-7-7m0 0l7-7m2 14h12"></path>
                    </svg>
                </a>
                <div>
                    <h1 class="text-2xl font-bold text-slate-800">Tanqia Skills</h1>
                    <p class="text-slate-500 text-sm flex items-center gap-2 mt-1">
                        {{ now()->format('d / m / Y') }} | شركة: {{ $company->name }}
                        @if (isset($department))
                            <span class="text-slate-300">/</span> قسم: {{ $department->name }}
                        @elseif(request('trashed'))
                            <span class="text-slate-300">/</span> الأرشيف
                        @endif
                        <button @click="openEdit = true"
                            class="text-indigo-500 hover:text-indigo-700 transition bg-indigo-50 p-1 rounded"
                            title="تعديل اسم الشركة">
                            <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                    d="M11 5H6a2 2 0 00-2 2v11a2 2 0 002 2h11a2 2 0 002-2v-5m-1.414-9.414a2 2 0 112.828 2.828L11.828 15H9v-2.828l8.586-8.586z">
                                </path>
                            </svg>
                        </button>
                    </p>
                </div>
            </div>
            <div class="flex gap-3">
                @if (isset($departments))
                <button
                    @click="openAssign = true; selectAllCompany = true;"
                    class="px-4 py-2 bg-indigo-600 hover:bg-indigo-700 text-white rounded-xl transition shadow-md shadow-indigo-500/20">إرسال مهمة للشركة</button>
                <div x-data="{ exportCompanyOpen: false }" class="relative">
                    <button @click="exportCompanyOpen = !exportCompanyOpen"
                        class="px-4 py-2 bg-emerald-600 hover:bg-emerald-700 text-white rounded-xl transition shadow-md shadow-emerald-500/20 flex items-center gap-2">
                        <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 10v6m0 0l-3-3m3 3l3-3m2 8H7a2 2 0 01-2-2V5a2 2 0 012-2h5.586a1 1 0 01.707.293l5.414 5.414a1 1 0 01.293.707V19a2 2 0 01-2 2z"></path></svg>
                        تحميل مهام الشركة <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 9l-7 7-7-7"></path></svg>
                    </button>
                    <div x-show="exportCompanyOpen" @click.outside="exportCompanyOpen = false" x-transition style="display: none;"
                        class="absolute top-full right-0 mt-2 w-48 bg-white rounded-xl shadow-lg border border-slate-100 z-50 overflow-hidden">
                        <a href="{{ route('company.export_tasks', $company->id) }}?status=all" class="block px-4 py-3 text-sm text-slate-700 hover:bg-slate-50 border-b border-slate-100">جميع المهام المنجزة</a>
                        <a href="{{ route('company.export_tasks', $company->id) }}?status=approved" class="block px-4 py-3 text-sm text-emerald-600 hover:bg-emerald-50 font-medium">المهام المقبولة فقط</a>
                    </div>
                </div>
                    <a href="{{ route('company.show', ['company' => $company->id, 'trashed' => 1]) }}"
                        class="px-4 py-2 bg-white border border-slate-200 text-slate-700 rounded-xl hover:bg-slate-50 transition shadow-sm">الأرشيف
                        (المحذوفين)</a>
                @elseif(request('trashed'))
                    <a href="{{ route('company.show', $company->id) }}"
                        class="px-4 py-2 bg-white border border-slate-200 text-slate-700 rounded-xl hover:bg-slate-50 transition shadow-sm">العودة
                        للأقسام</a>
                @elseif(isset($department))
                <a href="{{ route('company.show', $company->id) }}"
                    class="px-4 py-2 bg-white border border-slate-200 text-slate-700 rounded-xl hover:bg-slate-50 transition shadow-sm">العودة
                    للأقسام</a>
                <div x-data="{ exportDeptOpen: false }" class="relative">
                    <button @click="exportDeptOpen = !exportDeptOpen"
                        class="px-4 py-2 bg-emerald-600 hover:bg-emerald-700 text-white rounded-xl transition shadow-md shadow-emerald-500/20 flex items-center gap-2">
                        <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 10v6m0 0l-3-3m3 3l3-3m2 8H7a2 2 0 01-2-2V5a2 2 0 012-2h5.586a1 1 0 01.707.293l5.414 5.414a1 1 0 01.293.707V19a2 2 0 01-2 2z"></path></svg>
                        تحميل المهام <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 9l-7 7-7-7"></path></svg>
                    </button>
                    <div x-show="exportDeptOpen" @click.outside="exportDeptOpen = false" x-transition style="display: none;"
                        class="absolute top-full right-0 mt-2 w-48 bg-white rounded-xl shadow-lg border border-slate-100 z-50 overflow-hidden">
                        <a href="{{ route('department.export_tasks', $department->id) }}?status=all" class="block px-4 py-3 text-sm text-slate-700 hover:bg-slate-50 border-b border-slate-100">جميع المهام المنجزة</a>
                        <a href="{{ route('department.export_tasks', $department->id) }}?status=approved" class="block px-4 py-3 text-sm text-emerald-600 hover:bg-emerald-50 font-medium">المهام المقبولة فقط</a>
                    </div>
                </div>
                    <button
                        @click="(selectedUsers.length > 0 || selectAllCompany) ? openAssign = true : alert('يرجى تحديد موظف واحد على الأقل')"
                        class="px-4 py-2 bg-indigo-600 hover:bg-indigo-700 text-white rounded-xl transition shadow-md shadow-indigo-500/20">إرسال
                        مهمة</button>
                    <button type="button"
                        @click="if(selectedUsers.length === 0 && !selectAllCompany) { alert('يرجى تحديد موظف واحد على الأقل'); } else { confirmDelete = true; }"
                        class="px-4 py-2 bg-rose-50 hover:bg-rose-100 text-rose-600 border border-rose-200 rounded-xl transition">حذف
                        المحدد</button>
                @endif
            </div>
        </header>

        @if (isset($departments))
            <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-3 gap-6">
                @if ($departments->count() > 0)
                    @foreach ($departments as $dept)
                        <a href="{{ route('company.show', ['company' => $company->id, 'department_id' => $dept->id]) }}"
                            class="bg-white rounded-2xl shadow-sm border border-slate-200 p-6 block hover:shadow-md hover:border-indigo-300 transition group">
                            <div class="flex items-center gap-4">
                                <div
                                    class="w-14 h-14 rounded-xl bg-indigo-50 flex items-center justify-center text-indigo-500 group-hover:scale-110 transition-transform">
                                    <svg class="w-7 h-7" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                            d="M19 21V5a2 2 0 00-2-2H7a2 2 0 00-2 2v16m14 0h2m-2 0h-5m-9 0H3m2 0h5M9 7h1m-1 4h1m4-4h1m-1 4h1m-5 10v-5a1 1 0 011-1h2a1 1 0 011 1v5m-4 0h4">
                                        </path>
                                    </svg>
                                </div>
                                <div>
                                    <h3 class="text-xl font-bold text-slate-800">{{ $dept->name }}</h3>
                                    <p class="text-sm text-slate-500 mt-1">عدد الموظفين: <span
                                            class="font-bold text-indigo-600">{{ $dept->users_count }}</span></p>
                                </div>
                            </div>
                        </a>
                    @endforeach
                @else
                    <div
                        class="col-span-full text-center py-12 text-slate-400 bg-white rounded-2xl shadow-sm border border-slate-200">
                        <p class="text-lg font-medium">لا توجد أقسام مسجلة في هذه الشركة.</p>
                    </div>
                @endif
            </div>
        @else
            <div class="bg-white rounded-2xl shadow-sm border border-slate-200 p-4 mb-6">
                <div class="flex flex-col md:flex-row gap-4 items-center justify-between">
                    <form method="GET" action="{{ route('company.show', $company->id) }}"
                        class="relative w-full md:w-96">
                        @if (request('trashed'))
                            <input type="hidden" name="trashed" value="1">
                        @endif
                        @if (isset($department))
                            <input type="hidden" name="department_id" value="{{ $department->id }}">
                        @endif
                        <input type="text" name="search" value="{{ request('search') }}"
                            placeholder="بحث بالاسم أو البريد..."
                            class="w-full pl-10 pr-4 py-2.5 rounded-xl border border-slate-300 focus:border-indigo-500 focus:ring-4 focus:ring-indigo-500/20 outline-none transition">
                        <button type="submit"
                            class="absolute left-3 top-3 text-slate-400 hover:text-indigo-600 transition">
                            <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                    d="M21 21l-6-6m2-5a7 7 0 11-14 0 7 7 0 0114 0z"></path>
                            </svg>
                        </button>
                    </form>
                    @if (!request('trashed') && $users->total() > 0)
                        <div class="flex items-center gap-2 bg-indigo-50 px-4 py-2.5 rounded-xl border border-indigo-100 transition"
                            :class="selectAllCompany ? 'bg-indigo-100 border-indigo-200' : ''">
                            <input type="checkbox" id="selectAllCompany" x-model="selectAllCompany"
                                @change="if(selectAllCompany) selectedUsers = []"
                                class="rounded border-indigo-300 text-indigo-600 focus:ring-indigo-500 w-4 h-4 cursor-pointer">
                            <label for="selectAllCompany"
                                class="text-sm font-semibold text-indigo-800 cursor-pointer select-none">تحديد جميع
                                الموظفين ({{ $users->total() }})</label>
                        </div>
                    @endif
                    @if (session('success'))
                        <div x-data="{ show: true }" x-show="show" x-init="setTimeout(() => show = false, 4000)" x-transition
                            class="p-3 rounded-xl bg-emerald-50 border border-emerald-200 text-emerald-700 text-sm">
                            {{ session('success') }}</div>
                    @endif
                    @if ($errors->any())
                        <div x-data="{ show: true }" x-show="show" x-init="setTimeout(() => show = false, 4000)" x-transition
                            class="p-3 rounded-xl bg-rose-50 border border-rose-200 text-rose-700 text-sm">
                            {{ $errors->first() }}</div>
                    @endif
                </div>
            </div>

            <div class="bg-white rounded-2xl shadow-sm border border-slate-200 overflow-hidden">
                @if ($users->count() > 0)
                    @if (!request('trashed'))
                        <form id="bulkDeleteForm" method="POST"
                            action="{{ route('employee.destroy_multiple', $company->id) }}"
                            class="p-4 border-b border-slate-100 bg-slate-50/50">
                            @csrf @method('DELETE')
                            <input type="hidden" name="select_all_company" :value="selectAllCompany ? '1' : '0'">
                            @if (isset($department))
                                <input type="hidden" name="department_id" value="{{ $department->id }}">
                            @endif
                            <div class="overflow-x-auto custom-scrollbar pb-2">
                                <table class="w-full text-right min-w-[800px]">
                                    <thead class="bg-slate-50 text-slate-600 text-sm font-medium">
                                        <tr>
                                            <th class="p-4 border-b border-slate-200 w-10"><input type="checkbox"
                                                    :disabled="selectAllCompany"
                                                    @change="selectedUsers = $event.target.checked ? [{{ $users->pluck('id')->map(fn($id) => "'$id'")->join(',') }}] : []"
                                                    :checked="selectedUsers.length > 0 && selectedUsers.length ===
                                                        {{ $users->count() }}"
                                                    class="rounded border-slate-300 text-indigo-600 focus:ring-indigo-500 disabled:opacity-50 disabled:cursor-not-allowed">
                                            </th>
                                            <th class="p-4 border-b border-slate-200">#</th>
                                            <th class="p-4 border-b border-slate-200">الاسم</th>
                                            <th class="p-4 border-b border-slate-200">القسم</th>
                                            <th class="p-4 border-b border-slate-200">البريد الإلكتروني</th>
                                            <th class="p-4 border-b border-slate-200">تاريخ الإضافة</th>
                                            <th class="p-4 border-b border-slate-200 w-20">إجراء</th>
                                        </tr>
                                    </thead>
                                    <tbody class="divide-y divide-slate-100">
                                        @foreach ($users as $index => $user)
                                            <tr class="hover:bg-slate-50/80 transition group">
                                                <td class="p-4"><input type="checkbox" name="employee_ids[]"
                                                        value="{{ $user->id }}" x-model="selectedUsers"
                                                        :disabled="selectAllCompany"
                                                        class="rounded border-slate-300 text-indigo-600 focus:ring-indigo-500 disabled:opacity-50 disabled:cursor-not-allowed">
                                                </td>
                                                <td class="p-4 text-slate-500">{{ $users->firstItem() + $index }}</td>
                                                <td class="p-4 font-medium text-slate-800">{{ $user->name }}</td>
                                                <td class="p-4 text-slate-600">{{ $user->department->name ?? '-' }}
                                                </td>
                                                <td class="p-4 text-slate-600">{{ $user->email }}</td>
                                                <td class="p-4 text-slate-500 text-sm">
                                                    {{ $user->created_at->format('Y-m-d') }}</td>
                                                <td class="p-4 flex items-center gap-1">
                                                    <button type="button"
                                                        @click.prevent="editEmployee = { id: {{ $user->id }}, name: '{{ addslashes($user->name) }}', email: '{{ addslashes($user->email) }}', department_name: '{{ addslashes($user->department->name ?? '') }}' }"
                                                        class="p-2 text-indigo-500 hover:bg-indigo-50 rounded-lg transition opacity-70 group-hover:opacity-100"
                                                        title="تعديل">
                                                        <svg class="w-5 h-5" fill="none" stroke="currentColor"
                                                            viewBox="0 0 24 24">
                                                            <path stroke-linecap="round" stroke-linejoin="round"
                                                                stroke-width="2"
                                                                d="M11 5H6a2 2 0 00-2 2v11a2 2 0 002 2h11a2 2 0 002-2v-5m-1.414-9.414a2 2 0 112.828 2.828L11.828 15H9v-2.828l8.586-8.586z">
                                                            </path>
                                                        </svg>
                                                    </button>
                                                    <button type="button"
                                                        @click.prevent="selectedUsers = [{{ $user->id }}]; selectAllCompany = false; confirmDelete = true;"
                                                        title="حذف"
                                                        class="p-2 text-rose-500 hover:bg-rose-50 rounded-lg transition opacity-70 group-hover:opacity-100">
                                                        <svg class="w-5 h-5" fill="none" stroke="currentColor"
                                                            viewBox="0 0 24 24">
                                                            <path stroke-linecap="round" stroke-linejoin="round"
                                                                stroke-width="2"
                                                                d="M19 7l-.867 12.142A2 2 0 0116.138 21H7.862a2 2 0 01-1.995-1.858L5 7m5 4v6m4-6v6m1-10V4a1 1 0 00-1-1h-4a1 1 0 00-1 1v3M4 7h16">
                                                            </path>
                                                        </svg>
                                                    </button>
                                                </td>
                                            </tr>
                                        @endforeach
                                    </tbody>
                                </table>
                            </div>
                        </form>
                    @else
                        <div class="overflow-x-auto custom-scrollbar">
                            <table class="w-full text-right min-w-[800px]">
                                <thead class="bg-slate-50 text-slate-600 text-sm font-medium">
                                    <tr>
                                        <th class="p-4 border-b">#</th>
                                        <th class="p-4 border-b">الاسم</th>
                                        <th class="p-4 border-b">القسم</th>
                                        <th class="p-4 border-b">البريد</th>
                                        <th class="p-4 border-b">تاريخ الإضافة</th>
                                        <th class="p-4 border-b">إجراء</th>
                                    </tr>
                                </thead>
                                <tbody class="divide-y divide-slate-100">
                                    @foreach ($users as $index => $user)
                                        <tr class="hover:bg-slate-50/80 transition">
                                            <td class="p-4 text-slate-500">{{ $users->firstItem() + $index }}</td>
                                            <td class="p-4 font-medium text-slate-800">{{ $user->name }}</td>
                                            <td class="p-4 text-slate-600">{{ $user->department->name ?? '-' }}</td>
                                            <td class="p-4 text-slate-600">{{ $user->email }}</td>
                                            <td class="p-4 text-slate-500 text-sm">
                                                {{ $user->created_at->format('Y-m-d') }}</td>
                                            <td class="p-4">
                                                <form method="POST"
                                                    action="{{ route('employee.restore', $user->id) }}">
                                                    @csrf
                                                    <button
                                                        class="px-3 py-1.5 bg-emerald-50 hover:bg-emerald-100 text-emerald-700 text-sm font-medium rounded-lg transition border border-emerald-200">استرجاع</button>
                                                </form>
                                            </td>
                                        </tr>
                                    @endforeach
                                </tbody>
                            </table>
                        </div>
                    @endif
                    <div class="p-4 bg-slate-50/50 border-t border-slate-100">
                        {{ $users->links() }}
                    </div>
                @else
                    <div class="text-center py-12 text-slate-400">
                        <svg class="w-16 h-16 mx-auto mb-4 text-slate-300" fill="none" stroke="currentColor"
                            viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.5"
                                d="M12 4.354a4 4 0 110 5.292M15 21H3v-1a6 6 0 0112 0v1zm0 0h6v-1a6 6 0 00-9-5.197M13 7a4 4 0 11-8 0 4 4 0 018 0z">
                            </path>
                        </svg>
                        <p class="text-lg font-medium">لا يوجد موظفين مسجلين أو مطابقين لبحثك في هذه الشركة.</p>
                    </div>
                @endif
            </div>
        @endif

        <!-- Edit Company Modal -->
        <div x-show="openEdit" x-transition
            class="fixed inset-0 z-50 flex items-center justify-center p-4 bg-slate-900/40 backdrop-blur-sm"
            @keydown.escape.window="openEdit = false">
            <div @click.outside="openEdit = false" class="bg-white rounded-2xl shadow-2xl w-full max-w-md p-6">
                <h3 class="text-xl font-bold text-slate-800 mb-4">تعديل بيانات الشركة</h3>
                <form method="POST" action="{{ route('company.update', $company->id) }}">
                    @csrf @method('PUT')
                    <input type="text" name="name" value="{{ old('name', $company->name) }}" required
                        class="w-full px-4 py-3 rounded-xl border border-slate-300 focus:border-indigo-500 focus:ring-4 focus:ring-indigo-500/20 outline-none transition mb-4">
                    <div class="flex justify-end gap-3">
                        <button type="button" @click="openEdit = false"
                            class="px-5 py-2.5 bg-slate-100 hover:bg-slate-200 text-slate-700 rounded-xl transition">إلغاء</button>
                        <button type="submit"
                            class="px-5 py-2.5 bg-indigo-600 hover:bg-indigo-700 text-white rounded-xl transition shadow-lg shadow-indigo-500/20">حفظ
                            التعديلات</button>
                    </div>
                </form>
            </div>
        </div>

        <!-- Assign Task Modal -->
        <div x-show="openAssign" x-transition
            class="fixed inset-0 z-50 flex items-center justify-center p-4 bg-slate-900/40 backdrop-blur-sm"
            @keydown.escape.window="openAssign = false">
            <div @click.outside="openAssign = false" class="bg-white rounded-2xl shadow-2xl w-full max-w-lg p-6">
                <h3 class="text-xl font-bold text-slate-800 mb-4">إرسال مهمة للموظفين المحددين</h3>
                <form method="POST" action="{{ route('company.tasks.store', $company->id) }}"
                    enctype="multipart/form-data" class="space-y-4" @submit="isAssigning = true">
                    @csrf
                    <input type="hidden" name="select_all_company" :value="selectAllCompany ? '1' : '0'">
                    @if (isset($department))
                        <input type="hidden" name="department_id" value="{{ $department->id }}">
                    @endif
                    <template x-for="user in selectedUsers">
                        <input type="hidden" name="employee_ids[]" :value="user">
                    </template>
                    <textarea name="description" rows="3" required placeholder="شرح المهمة *"
                        class="w-full px-4 py-3 rounded-xl border border-slate-300 focus:border-indigo-500 focus:ring-4 focus:ring-indigo-500/20 outline-none transition resize-none"></textarea>
                    <div>
                        <label class="block text-sm font-medium text-slate-700 mb-1">تاريخ المهمة *</label>
                        <input type="date" name="task_date" required value="{{ date('Y-m-d') }}" class="w-full px-4 py-3 rounded-xl border border-slate-300 focus:border-indigo-500 focus:ring-4 focus:ring-indigo-500/20 outline-none transition">
                    </div>
                    <div
                        x-data="{ fileName: '' }" class="border-2 border-dashed border-slate-300 rounded-xl p-6 text-center hover:bg-slate-50 transition cursor-pointer relative">
                        <input type="file" name="file"
                            class="absolute inset-0 w-full h-full opacity-0 cursor-pointer" @change="fileName = $event.target.files.length ? $event.target.files[0].name : ''">
                        <svg class="w-8 h-8 mx-auto text-slate-400 mb-2" fill="none" stroke="currentColor"
                            viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                d="M7 16a4 4 0 01-.88-7.903A5 5 0 1115.9 6L16 6a5 5 0 011 9.9M15 13l-3-3m0 0l-3 3">
                            </path>
                        </svg>
                        <p class="text-sm font-medium text-slate-700" x-text="fileName ? 'تم اختيار: ' + fileName : 'اسحب الملف هنا أو اضغط للاختيار'"></p>
                        <p class="text-xs text-slate-400 mt-1">يسمح برفع أي نوع من الملفات (بحد أقصى 20 ميجابايت).</p>
                    </div>
                    <div class="flex justify-end gap-3 mt-4">
                        <button type="button" @click="openAssign = false"
                            class="px-5 py-2.5 bg-slate-100 hover:bg-slate-200 text-slate-700 rounded-xl transition">إلغاء</button>
                        <button type="submit" :disabled="isAssigning"
                            class="px-5 py-2.5 bg-indigo-600 hover:bg-indigo-700 text-white rounded-xl transition shadow-lg shadow-indigo-500/20 disabled:opacity-70 disabled:cursor-not-allowed flex items-center justify-center gap-2">
                            <span x-show="!isAssigning">إرسال</span>
                            <span x-show="isAssigning" class="flex items-center gap-2">
                                <svg class="animate-spin w-4 h-4 text-white" xmlns="http://www.w3.org/2000/svg"
                                    fill="none" viewBox="0 0 24 24">
                                    <circle class="opacity-25" cx="12" cy="12" r="10"
                                        stroke="currentColor" stroke-width="4"></circle>
                                    <path class="opacity-75" fill="currentColor"
                                        d="M4 12a8 8 0 018-8V0C5.373 0 0 5.373 0 12h4z"></path>
                                </svg>
                                جاري الإرسال...
                            </span>
                        </button>
                    </div>
                </form>
            </div>
        </div>

        <!-- Edit Employee Modal -->
        <div x-show="editEmployee" x-transition style="display: none;"
            class="fixed inset-0 z-50 flex items-center justify-center p-4 bg-slate-900/40 backdrop-blur-sm"
            @keydown.escape.window="editEmployee = null">
            <div @click.outside="editEmployee = null" class="bg-white rounded-2xl shadow-2xl w-full max-w-md p-6">
                <h3 class="text-xl font-bold text-slate-800 mb-4">تعديل بيانات الموظف</h3>
                <form method="POST" :action="`/dashboard/employee/${editEmployee?.id}`">
                    @csrf @method('PUT')
                    <div class="space-y-4 mb-6">
                        <div>
                            <label class="block text-sm font-medium text-slate-700 mb-1">الاسم *</label>
                            <input type="text" name="name" :value="editEmployee?.name" required
                                class="w-full px-4 py-3 rounded-xl border border-slate-300 focus:border-indigo-500 focus:ring-4 focus:ring-indigo-500/20 outline-none transition">
                        </div>
                        <div>
                            <label class="block text-sm font-medium text-slate-700 mb-1">البريد الإلكتروني *</label>
                            <input type="email" name="email" :value="editEmployee?.email" required
                                class="w-full px-4 py-3 rounded-xl border border-slate-300 focus:border-indigo-500 focus:ring-4 focus:ring-indigo-500/20 outline-none transition"
                                dir="ltr">
                        </div>
                        <div>
                            <label class="block text-sm font-medium text-slate-700 mb-1">القسم</label>
                            <input type="text" name="department_name" :value="editEmployee?.department_name"
                                class="w-full px-4 py-3 rounded-xl border border-slate-300 focus:border-indigo-500 focus:ring-4 focus:ring-indigo-500/20 outline-none transition">
                        </div>
                        <div>
                            <label class="block text-sm font-medium text-slate-700 mb-1">كلمة المرور الجديدة
                                (اختياري)</label>
                            <input type="password" name="password"
                                class="w-full px-4 py-3 rounded-xl border border-slate-300 focus:border-indigo-500 focus:ring-4 focus:ring-indigo-500/20 outline-none transition"
                                dir="ltr" placeholder="اترك الحقل فارغاً إذا لم ترد التغيير">
                        </div>
                    </div>
                    <div class="flex justify-end gap-3">
                        <button type="button" @click="editEmployee = null"
                            class="px-5 py-2.5 bg-slate-100 hover:bg-slate-200 text-slate-700 rounded-xl transition">إلغاء</button>
                        <button type="submit"
                            class="px-5 py-2.5 bg-indigo-600 hover:bg-indigo-700 text-white rounded-xl transition shadow-lg shadow-indigo-500/20">حفظ
                            التعديلات</button>
                    </div>
                </form>
            </div>
        </div>

        <!-- Delete Confirmation Modal -->
        <div x-show="confirmDelete" x-transition style="display: none;"
            class="fixed inset-0 z-[60] flex items-center justify-center p-4 bg-slate-900/50 backdrop-blur-sm"
            @keydown.escape.window="confirmDelete = false">
            <div @click.outside="confirmDelete = false"
                class="bg-white rounded-2xl shadow-2xl w-full max-w-sm p-6 text-center transform transition-all">
                <div
                    class="w-16 h-16 mx-auto bg-rose-50 rounded-full flex items-center justify-center mb-4 text-rose-600">
                    <svg class="w-8 h-8" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                            d="M19 7l-.867 12.142A2 2 0 0116.138 21H7.862a2 2 0 01-1.995-1.858L5 7m5 4v6m4-6v6m1-10V4a1 1 0 00-1-1h-4a1 1 0 00-1 1v3M4 7h16">
                        </path>
                    </svg>
                </div>
                <h3 class="text-xl font-bold text-slate-800 mb-2">تأكيد الحذف</h3>
                <p class="text-slate-500 text-sm mb-6">هل أنت متأكد من حذف الموظف(ين) المحدد(ين)؟ سيتم نقلهم إلى
                    الأرشيف.</p>
                <div class="flex justify-center gap-3">
                    <button type="button" @click="confirmDelete = false"
                        class="px-5 py-2.5 bg-slate-100 hover:bg-slate-200 text-slate-700 font-medium rounded-xl transition">إلغاء</button>
                    <button type="button"
                        @click="isDeleting = true; document.getElementById('bulkDeleteForm').submit()"
                        :disabled="isDeleting"
                        class="px-5 py-2.5 bg-rose-600 hover:bg-rose-700 text-white font-medium rounded-xl transition shadow-lg shadow-rose-500/30 disabled:opacity-70 disabled:cursor-not-allowed flex items-center justify-center gap-2">
                        <span x-show="!isDeleting">تأكيد الحذف</span>
                        <span x-show="isDeleting" class="flex items-center gap-2">
                            <svg class="animate-spin w-4 h-4 text-white" xmlns="http://www.w3.org/2000/svg"
                                fill="none" viewBox="0 0 24 24">
                                <circle class="opacity-25" cx="12" cy="12" r="10"
                                    stroke="currentColor" stroke-width="4"></circle>
                                <path class="opacity-75" fill="currentColor"
                                    d="M4 12a8 8 0 018-8V0C5.373 0 0 5.373 0 12h4z"></path>
                            </svg>
                            جاري الحذف...
                        </span>
                    </button>
                </div>
            </div>
        </div>
    </div>

</body>

</html>
