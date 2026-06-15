<?php

use App\Http\Controllers\AuthController;
use App\Http\Controllers\EmployeeController;
use App\Models\Company;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Route;

Route::redirect('/', '/login');

Route::get('/login', [AuthController::class, 'showLoginForm'])->name('login')->middleware('guest');
Route::post('/login', [AuthController::class, 'login'])->middleware('guest');
Route::post('/logout', [AuthController::class, 'logout'])->name('logout')->middleware('auth');

Route::get('/dashboard', function (Request $request) {
    if ($request->user()->role !== 'admin') {
        return redirect()->route('employee.welcome');
    }
    
    // Get search and sort parameters
    $sortBy = $request->input('sort_by', 'name'); // Default sort by name
    $search = $request->input('search');

    // جلب إحصائيات المهام لكل الشركات في استعلام (مشوار) واحد لتسريع الموقع
    $allStats = \Illuminate\Support\Facades\DB::table('task_user')
        ->join('users', 'task_user.user_id', '=', 'users.id')
        ->select('users.company_id')
        ->selectRaw('count(*) as total, sum(case when task_user.status = "completed" then 1 else 0 end) as completed')
        ->groupBy('users.company_id')
        ->get()
        ->keyBy('company_id');

    // جلب الشركات مع عدد الموظفين فيها
    $companyQuery = Company::withCount('users');

    // Apply search
    if ($search) {
        $companyQuery->where('name', 'like', "%{$search}%");
    }

    $companies = $companyQuery->get()->map(function ($company) use ($allStats) {
        $stats = $allStats->get($company->id);
        $company->total_tasks = $stats->total ?? 0;
        $company->completed_tasks = $stats->completed ?? 0;
        $company->progress = $company->total_tasks > 0 ? round(($company->completed_tasks / $company->total_tasks) * 100) : 0;
        return $company;
    });

    // Apply sorting on the collection
    if ($sortBy === 'progress') {
        $companies = $companies->sortByDesc('progress');
    } elseif ($sortBy === 'users_count') {
        $companies = $companies->sortByDesc('users_count');
    } else { // default to name
        $companies = $companies->sortBy('name');
    }

    $submissions = \App\Models\TaskSubmission::with(['user.company', 'task'])
        ->orderByRaw("CASE WHEN status = 'pending' THEN 1 ELSE 2 END")
        ->orderBy('created_at', 'desc')
        ->get();
    $pendingSubmissionsCount = $submissions->where('status', 'pending')->count();
    return view('dashboard', compact('companies', 'submissions', 'pendingSubmissionsCount', 'search', 'sortBy'));
})->middleware('auth')->name('dashboard');

Route::get('/employee/welcome', function (Request $request) {
    if ($request->user()->role !== 'employee') {
        return redirect()->route('dashboard');
    }
    
    $assignedTasks = $request->user()->tasks()->orderBy('created_at', 'desc')->get();
    $submittedTasks = $request->user()->submissions()->orderBy('created_at', 'desc')->get();
    $pendingTasksCount = $assignedTasks->where('pivot.status', 'pending')->count();
    
    return view('welcome', compact('assignedTasks', 'submittedTasks', 'pendingTasksCount'));
})->middleware('auth')->name('employee.welcome');

Route::get('/dashboard/company/{company}', function (Request $request, Company $company) {
    if ($request->user()->role !== 'admin') {
        return redirect()->route('employee.welcome');
    }
    
    // 1. حالة عرض الأرشيف (الموظفين المحذوفين)
    if ($request->has('trashed')) {
        $query = $company->users()->onlyTrashed();
        if ($request->filled('search')) {
            $search = $request->search;
            $query->where(function($q) use ($search) {
                $q->where('name', 'like', "%{$search}%")
                  ->orWhere('email', 'like', "%{$search}%");
            });
        }
        $users = $query->paginate(20)->withQueryString();
        return view('company', compact('company', 'users'));
    }

    // 2. حالة عرض الموظفين التابعين لقسم محدد
    if ($request->filled('department_id')) {
        $department = \App\Models\Department::where('company_id', $company->id)->findOrFail($request->department_id);
        $query = $department->users();
        if ($request->filled('search')) {
            $search = $request->search;
            $query->where(function($q) use ($search) {
                $q->where('name', 'like', "%{$search}%")
                  ->orWhere('email', 'like', "%{$search}%");
            });
        }
        $users = $query->paginate(20)->withQueryString();
        return view('company', compact('company', 'department', 'users'));
    }

    // 3. الحالة الافتراضية: عرض أقسام الشركة
    $departments = \App\Models\Department::where('company_id', $company->id)->withCount('users')->get();
    
    return view('company', compact('company', 'departments'));
})->middleware('auth')->name('company.show');

Route::post('/dashboard/employee/upload', function (Request $request) {
    if ($request->user()->role !== 'admin') {
        abort(403, 'غير مصرح لك باتخاذ هذا الإجراء');
    }
    return app(EmployeeController::class)->upload($request);
})->name('employee.upload')->middleware('auth');

Route::post('/dashboard/employee/manual', function (Request $request) {
    if ($request->user()->role !== 'admin') {
        abort(403, 'غير مصرح لك باتخاذ هذا الإجراء');
    }
    return app(EmployeeController::class)->storeManual($request);
})->name('employee.store_manual')->middleware('auth');

Route::delete('/dashboard/company/{company}/employees', function (Request $request, Company $company) {
    if ($request->user()->role !== 'admin') {
        abort(403, 'غير مصرح لك باتخاذ هذا الإجراء');
    }
    return app(EmployeeController::class)->destroyMultiple($request, $company);
})->name('employee.destroy_multiple')->middleware('auth');

Route::put('/dashboard/company/{company}', function (Request $request, Company $company) {
    if ($request->user()->role !== 'admin') {
        abort(403, 'غير مصرح لك باتخاذ هذا الإجراء');
    }
    $request->validate(['name' => 'required|string|max:255|unique:companies,name,' . $company->id]);
    $company->update(['name' => $request->name]);
    return back()->with('success', 'تم تحديث بيانات الشركة بنجاح.');
})->name('company.update')->middleware('auth');

Route::put('/dashboard/employee/{user}', function (Request $request, \App\Models\User $user) {
    if ($request->user()->role !== 'admin') {
        abort(403, 'غير مصرح لك باتخاذ هذا الإجراء');
    }
    $request->validate([
        'name' => 'required|string|max:255',
        'email' => 'required|email|max:255|unique:users,email,' . $user->id,
        'department_name' => 'nullable|string|max:255',
        'password' => 'nullable|string|min:6',
    ]);

    $data = [
        'name' => $request->name,
        'email' => $request->email,
    ];
    
    if ($request->filled('department_name')) {
        $department = \App\Models\Department::firstOrCreate([
            'name' => $request->department_name,
            'company_id' => $user->company_id
        ]);
        $data['department_id'] = $department->id;
    }

    if ($request->filled('password')) {
        $data['password'] = \Illuminate\Support\Facades\Hash::make($request->password);
    }
    $user->update($data);
    return back()->with('success', 'تم تحديث بيانات الموظف بنجاح.');
})->name('employee.update')->middleware('auth');

Route::post('/dashboard/employee/{id}/restore', [EmployeeController::class, 'restore'])
    ->name('employee.restore')->middleware('auth');

Route::post('/dashboard/company/{company}/tasks', function (Request $request, Company $company) {
    if ($request->user()->role !== 'admin') {
        abort(403, 'غير مصرح لك باتخاذ هذا الإجراء');
    }
    return app(EmployeeController::class)->assignTask($request, $company);
})->name('company.tasks.store')->middleware('auth');

Route::post('/employee/submit-task', [EmployeeController::class, 'submitTask'])->name('employee.submit_task')->middleware('auth');

Route::get('/download', [EmployeeController::class, 'download'])->name('download')->middleware('auth');

Route::put('/dashboard/submissions/{submission}/status', function (Request $request, \App\Models\TaskSubmission $submission) {
    if ($request->user()->role !== 'admin') {
        abort(403, 'غير مصرح لك باتخاذ هذا الإجراء');
    }
    $request->validate([
        'status' => 'required|in:approved,rejected,pending',
        'admin_feedback' => 'nullable|string'
    ]);
    $submission->update([
        'status' => $request->status,
        'admin_feedback' => $request->has('admin_feedback') ? $request->admin_feedback : $submission->admin_feedback
    ]);

    if ($submission->task_id) {
        $pivotStatus = $request->status === 'approved' ? 'completed' : 'pending';
        $submission->user->tasks()->updateExistingPivot($submission->task_id, ['status' => $pivotStatus]);
    }

    return back()->with('success', 'تم تحديث حالة المهمة بنجاح.');
})->name('dashboard.submissions.update')->middleware('auth');

// مسار تصدير مهام القسم إلى إكسل
Route::get('/dashboard/department/{department}/export-tasks', function (Request $request, \App\Models\Department $department) {
    if ($request->user()->role !== 'admin') {
        abort(403, 'غير مصرح لك باتخاذ هذا الإجراء');
    }
    return app(EmployeeController::class)->exportDepartmentTasks($request, $department);
})->name('department.export_tasks')->middleware('auth');

// مسار تصدير مهام الشركة بالكامل إلى إكسل
Route::get('/dashboard/company/{company}/export-tasks', function (Request $request, \App\Models\Company $company) {
    if ($request->user()->role !== 'admin') {
        abort(403, 'غير مصرح لك باتخاذ هذا الإجراء');
    }
    return app(EmployeeController::class)->exportCompanyTasks($request, $company);
})->name('company.export_tasks')->middleware('auth');

Route::get('/dashboard/backup', function (Request $request) {
    if ($request->user()->role !== 'admin') {
        abort(403, 'غير مصرح لك باتخاذ هذا الإجراء');
    }

    try {
        // تمديد وقت التنفيذ لتجنب انقطاع العملية في الاستضافات
        set_time_limit(300);
        
        // تشغيل عملية النسخ الاحتياطي لقاعدة البيانات فقط باستخدام حزمة Spatie
        \Illuminate\Support\Facades\Artisan::call('backup:run', ['--only-db' => true]);
        
        $diskName = config('backup.backup.destination.disks')[0] ?? 'local';
        $disk = \Illuminate\Support\Facades\Storage::disk($diskName);
        
        // جلب جميع ملفات النسخ الاحتياطي (ملفات zip)
        $files = collect($disk->allFiles())->filter(function ($file) {
            return str_ends_with($file, '.zip');
        });
        
        if ($files->isEmpty()) {
            abort(404, 'لم يتم العثور على ملفات نسخ احتياطي بعد التشغيل.');
        }
        
        // الحصول على أحدث ملف
        $latestBackup = $files->sortByDesc(function ($file) use ($disk) {
            return $disk->lastModified($file);
        })->first();

        // تنزيل الملف المضغوط
        return $disk->download($latestBackup);

    } catch (\Exception $e) {
        abort(500, 'فشل في إنشاء النسخة الاحتياطية. تفاصيل الخطأ: ' . $e->getMessage());
    }
})->name('admin.backup')->middleware('auth');
