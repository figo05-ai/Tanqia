<?php

namespace App\Http\Controllers;

use App\Models\Company;
use App\Models\Task;
use App\Models\User;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Storage;
use PhpOffice\PhpSpreadsheet\IOFactory;

class EmployeeController extends Controller
{
    public function upload(Request $request)
    {
        $request->validate([
            'file' => ['required', 'file', 'mimes:csv,txt,xlsx,xls', 'max:5120'], // الحد الأقصى 5 ميجابايت
        ], [
            'file.mimes' => 'عفواً، يجب أن يكون الملف بصيغة Excel أو CSV فقط.',
            'file.max' => 'حجم الملف يجب ألا يتجاوز 5 ميجابايت.'
        ]);

        // تغيير اسم الملف ليكون مميزاً بناءً على وقت الرفع
        $fileName = time() . '_' . $request->file('file')->getClientOriginalName();
        $path = $request->file('file')->storeAs('uploads/employees', $fileName);

        $fullPath = storage_path('app/private/' . $path);
        
        try {
            // استخدام مكتبة PhpSpreadsheet لقراءة ملفات Excel و CSV
            $spreadsheet = IOFactory::load($fullPath);
            $worksheet = $spreadsheet->getActiveSheet();
            $rows = $worksheet->toArray();
            
            // تخطي السطر الأول (عناوين الأعمدة)
            array_shift($rows);
            
            foreach ($rows as $data) {
                if (count($data) < 4) continue; // التأكد من وجود البيانات الأربعة

                $name = trim((string) ($data[0] ?? ''));
                $email = trim((string) ($data[1] ?? ''));
                $password = trim((string) ($data[2] ?? ''));
                $companyName = trim((string) ($data[3] ?? ''));
                $departmentName = trim((string) ($data[4] ?? 'قسم عام')); // قراءة العمود الخامس للقسم

                if (!$name || !$email || !$password || !$companyName) continue;

                $company = Company::firstOrCreate(['name' => $companyName]);
                $department = \App\Models\Department::firstOrCreate(['name' => $departmentName, 'company_id' => $company->id]);

                User::firstOrCreate(
                    ['email' => $email], 
                    [
                        'name' => $name,
                        'department_id' => $department->id,
                        'password' => Hash::make($password),
                        'company_id' => $company->id,
                        'role' => 'employee',
                    ]
                );
            }
        } catch (\Exception $e) {
            $errorMessage = 'حدث خطأ أثناء قراءة الملف. تأكد من أنه بصيغة Excel أو CSV صحيحة.';
            if (config('app.debug')) {
                $errorMessage .= ' تفاصيل الخطأ: ' . $e->getMessage();
            }
            if ($request->wantsJson()) {
                return response()->json(['errors' => ['file' => [$errorMessage]]], 422);
            }
            return back()->withErrors(['file' => $errorMessage]);
        }

        if ($request->wantsJson()) {
            return response()->json(['message' => 'تم رفع الملف بنجاح وحفظه في النظام.']);
        }
        return back()->with('success', 'تم رفع الملف بنجاح وحفظه في النظام.');
    }

    public function storeManual(Request $request)
    {
        $request->validate([
            'name' => ['required', 'string', 'max:255'],
            'email' => ['required', 'string', 'email', 'max:255', 'unique:users,email'],
            'password' => ['required', 'string', 'min:6'],
            'company_name' => ['required', 'string', 'max:255'],
            'department_name' => ['nullable', 'string', 'max:255'],
        ], [
            'email.unique' => 'البريد الإلكتروني مستخدم بالفعل لموظف آخر.',
            'password.min' => 'كلمة المرور يجب أن تكون 6 أحرف على الأقل.'
        ]);

        $company = Company::firstOrCreate(['name' => trim($request->company_name)]);
        $departmentName = trim($request->input('department_name', 'قسم عام')) ?: 'قسم عام';
        $department = \App\Models\Department::firstOrCreate([
            'name' => $departmentName, 
            'company_id' => $company->id
        ]);

        User::create([
            'name' => trim($request->name),
            'email' => trim($request->email),
            'password' => Hash::make($request->password),
            'company_id' => $company->id,
            'department_id' => $department->id,
            'role' => 'employee',
        ]);

        return back()->with('success', 'تم إضافة الموظف يدوياً بنجاح.');
    }

    public function destroyMultiple(Request $request, Company $company)
    {
        // التحقق مما إذا كان قد تم تحديد "حذف الكل"
        if ($request->input('select_all_company') === '1') {
            if ($request->filled('department_id')) {
                $company->users()->where('department_id', $request->input('department_id'))->delete();
                $message = 'تم حذف جميع الموظفين في هذا القسم بنجاح.';
            } else {
                $company->users()->delete();
                $message = 'تم حذف جميع الموظفين التابعين للشركة بنجاح.';
            }
        } else {
            // حذف الموظفين المحددين في الصفحة فقط
            $request->validate([
                'employee_ids' => ['required', 'array'],
                'employee_ids.*' => ['exists:users,id'],
            ], [
                'employee_ids.required' => 'يرجى تحديد موظف واحد على الأقل للحذف.'
            ]);

            $company->users()->whereIn('id', $request->input('employee_ids'))->delete();
            $message = 'تم حذف الموظفين المحددين بنجاح.';
        }

        return back()->with('success', $message);
    }

    public function restore(Request $request, $id)
    {
        $user = User::onlyTrashed()->findOrFail($id);
        $user->restore();
        return back()->with('success', 'تم إرجاع الموظف بنجاح.');
    }

    public function assignTask(Request $request, Company $company)
    {
        $request->validate([
            'description' => ['required', 'string'],
            'task_date' => ['required', 'date'],
            'file' => ['nullable', 'file', 'max:20480'], // الحد الأقصى 20 ميجابايت (بدون تحديد نوع معين)
        ], [
            'description.required' => 'يرجى كتابة شرح للمهمة.',
            'task_date.required' => 'يرجى تحديد تاريخ المهمة.',
            'file.max' => 'حجم الملف يجب ألا يتجاوز 20 ميجابايت.'
        ]);

        // 1. تحديد الموظفين المستهدفين
        $userIds = [];
        if ($request->input('select_all_company') === '1') {
            if ($request->filled('department_id')) {
                $userIds = $company->users()->where('department_id', $request->input('department_id'))->pluck('id')->toArray();
            } else {
                $userIds = $company->users()->pluck('id')->toArray();
            }
        } else {
            $request->validate([
                'employee_ids' => ['required', 'array'],
                'employee_ids.*' => ['exists:users,id'],
            ], [
                'employee_ids.required' => 'يرجى تحديد موظف واحد على الأقل لإرسال المهمة.'
            ]);
            $userIds = $request->input('employee_ids');
        }

        // 2. رفع الملف المرفق (إن وُجد)
        $filePath = null;
        if ($request->hasFile('file')) {
            $fileName = time() . '_' . $request->file('file')->getClientOriginalName();
            $filePath = $request->file('file')->storeAs('uploads/tasks', $fileName);
        }

        // 3. حفظ المهمة وربطها بالموظفين
        $task = Task::create([
            'company_id' => $company->id,
            'description' => $request->input('description'),
            'task_date' => $request->input('task_date'),
            'file_path' => $filePath,
        ]);
        $task->users()->attach($userIds);

        return back()->with('success', 'تم إرسال المهمة للموظفين المحددين بنجاح.');
    }

    public function submitTask(Request $request)
    {
        $request->validate([
            'task_id' => ['required', 'exists:tasks,id'],
            'description' => ['required', 'string'],
            'submission_date' => ['required', 'date'],
            'file' => ['nullable', 'file', 'max:20480'],
        ]);

        $filePath = null;
        if ($request->hasFile('file')) {
            $fileName = time() . '_' . $request->file('file')->getClientOriginalName();
            $filePath = $request->file('file')->storeAs('uploads/submissions', $fileName);
        }

        $request->user()->submissions()->create([
            'task_id' => $request->input('task_id'),
            'description' => $request->input('description'),
            'submission_date' => $request->input('submission_date'),
            'file_path' => $filePath,
            'status' => 'pending',
        ]);

        // تحديث حالة المهمة لتصبح "مكتملة"
        $request->user()->tasks()->updateExistingPivot($request->input('task_id'), ['status' => 'completed']);

        return back()->with('success', 'تم إرسال المهمة بنجاح وهي الآن قيد التحليل.');
    }

    public function download(Request $request)
    {
        $path = $request->query('path');
        if (!$path || !Storage::exists($path)) {
            abort(404, 'الملف غير موجود.');
        }

        $user = $request->user();

        // التحقق من الصلاحيات: المد,ير له حق الوصول الكامل، أما الموظف فله حق الوصول لملفاته فقط
        if ($user->role !== 'admin') {
            $belongsToTask = $user->tasks()->where('file_path', $path)->exists();
            $belongsToSubmission = $user->submissions()->where('file_path', $path)->exists();

            if (!$belongsToTask && !$belongsToSubmission) {
                abort(403, 'غير مصرح لك بتحميل أو الوصول إلى هذا الملف.');
            }
        }

        return Storage::download($path);
    }

    public function exportDepartmentTasks(Request $request, \App\Models\Department $department)
    {
        // جلب جميع تسليمات المهام للموظفين التابعين لهذا القسم
        $query = \App\Models\TaskSubmission::with(['user.company', 'user.department', 'task'])
            ->whereHas('user', function ($q) use ($department) {
                $q->where('department_id', $department->id);
            });

        // فلترة المهام بناءً على اختيار المستخدم (المقبولة فقط)
        if ($request->input('status') === 'approved') {
            $query->where('status', 'approved');
        }

        $submissions = $query->orderBy('created_at', 'desc')->get();

        $spreadsheet = new \PhpOffice\PhpSpreadsheet\Spreadsheet();
        $sheet = $spreadsheet->getActiveSheet();
        
        // جعل اتجاه الشيت من اليمين لليسار (للغة العربية)
        $sheet->setRightToLeft(true);

        // كتابة العناوين الرئيسية
        $sheet->setCellValue('A1', 'اسم الموظف');
        $sheet->setCellValue('B1', 'اسم الشركة');
        $sheet->setCellValue('C1', 'القسم / المسمى الوظيفي');
        $sheet->setCellValue('D1', 'المهمة');
        $sheet->setCellValue('E1', 'حالة المهمة');
        $sheet->setCellValue('F1', 'تاريخ المهمة');
        $sheet->setCellValue('G1', 'تاريخ التسليم');

        // تنسيق العناوين (خط عريض وخلفية ملونة)
        $headerStyle = [
            'font' => ['bold' => true],
            'fill' => [
                'fillType' => \PhpOffice\PhpSpreadsheet\Style\Fill::FILL_SOLID,
                'startColor' => ['rgb' => 'E2EFDA'],
            ],
        ];
        $sheet->getStyle('A1:G1')->applyFromArray($headerStyle);

        $row = 2;
        foreach ($submissions as $submission) {
            // ترجمة حالة المهمة للعربية
            $status = match($submission->status) {
                'approved' => 'مقبولة',
                'rejected' => 'مرفوضة',
                'pending' => 'قيد المراجعة',
                default => $submission->status,
            };

            // استخدام وصف المهمة الأساسي، أو وصف التسليم إذا كانت المهمة غير مربوطة بشكل مباشر
            $taskDescription = $submission->task ? $submission->task->description : $submission->description;

            $taskDate = $submission->task && $submission->task->task_date ? $submission->task->task_date : $submission->created_at->format('Y-m-d');
            $submissionDate = $submission->submission_date ? $submission->submission_date : $submission->created_at->format('Y-m-d');

            $sheet->setCellValue('A' . $row, $submission->user->name ?? 'غير معروف');
            $sheet->setCellValue('B' . $row, $submission->user->company->name ?? 'غير معروف');
            $sheet->setCellValue('C' . $row, $submission->user->department->name ?? 'غير معروف');
            $sheet->setCellValue('D' . $row, $taskDescription);
            $sheet->setCellValue('E' . $row, $status);
            $sheet->setCellValue('F' . $row, $taskDate);
            $sheet->setCellValue('G' . $row, $submissionDate);
            $row++;
        }

        // ضبط عرض الأعمدة تلقائياً لتناسب النصوص
        foreach (range('A', 'G') as $col) {
            $sheet->getColumnDimension($col)->setAutoSize(true);
        }

        $fileName = 'Tasks_' . preg_replace('/\s+/', '_', $department->name) . '_' . date('Y-m-d') . '.xlsx';

        $writer = new \PhpOffice\PhpSpreadsheet\Writer\Xlsx($spreadsheet);
        
        $response = new \Symfony\Component\HttpFoundation\StreamedResponse(function () use ($writer) {
            $writer->save('php://output');
        });

        $response->headers->set('Content-Type', 'application/vnd.openxmlformats-officedocument.spreadsheetml.sheet');
        $response->headers->set('Content-Disposition', 'attachment;filename="' . $fileName . '"');
        $response->headers->set('Cache-Control', 'max-age=0');

        return $response;
    }

    public function exportCompanyTasks(Request $request, \App\Models\Company $company)
    {
        // جلب جميع تسليمات المهام للموظفين التابعين للشركة بالكامل
        $query = \App\Models\TaskSubmission::with(['user.company', 'user.department', 'task'])
            ->whereHas('user', function ($q) use ($company) {
                $q->where('company_id', $company->id);
            });

        // فلترة المهام بناءً على اختيار المستخدم (المقبولة فقط)
        if ($request->input('status') === 'approved') {
            $query->where('status', 'approved');
        }

        $submissions = $query->orderBy('created_at', 'desc')->get();

        $spreadsheet = new \PhpOffice\PhpSpreadsheet\Spreadsheet();
        $sheet = $spreadsheet->getActiveSheet();
        
        $sheet->setRightToLeft(true);

        $sheet->setCellValue('A1', 'اسم الموظف');
        $sheet->setCellValue('B1', 'اسم الشركة');
        $sheet->setCellValue('C1', 'القسم');
        $sheet->setCellValue('D1', 'المهمة');
        $sheet->setCellValue('E1', 'حالة المهمة');
        $sheet->setCellValue('F1', 'تاريخ المهمة');
        $sheet->setCellValue('G1', 'تاريخ التسليم');

        $headerStyle = [
            'font' => ['bold' => true],
            'fill' => [
                'fillType' => \PhpOffice\PhpSpreadsheet\Style\Fill::FILL_SOLID,
                'startColor' => ['rgb' => 'E2EFDA'],
            ],
        ];
        $sheet->getStyle('A1:G1')->applyFromArray($headerStyle);

        $row = 2;
        foreach ($submissions as $submission) {
            $status = match($submission->status) {
                'approved' => 'مقبولة',
                'rejected' => 'مرفوضة',
                'pending' => 'قيد المراجعة',
                default => $submission->status,
            };

            $taskDescription = $submission->task ? $submission->task->description : $submission->description;

            $taskDate = $submission->task && $submission->task->task_date ? $submission->task->task_date : $submission->created_at->format('Y-m-d');
            $submissionDate = $submission->submission_date ? $submission->submission_date : $submission->created_at->format('Y-m-d');

            $sheet->setCellValue('A' . $row, $submission->user->name ?? 'غير معروف');
            $sheet->setCellValue('B' . $row, $submission->user->company->name ?? 'غير معروف');
            $sheet->setCellValue('C' . $row, $submission->user->department->name ?? 'غير معروف');
            $sheet->setCellValue('D' . $row, $taskDescription);
            $sheet->setCellValue('E' . $row, $status);
            $sheet->setCellValue('F' . $row, $taskDate);
            $sheet->setCellValue('G' . $row, $submissionDate);
            $row++;
        }

        foreach (range('A', 'G') as $col) {
            $sheet->getColumnDimension($col)->setAutoSize(true);
        }

        $fileName = 'Tasks_Company_' . preg_replace('/\s+/', '_', $company->name) . '_' . date('Y-m-d') . '.xlsx';
        $writer = new \PhpOffice\PhpSpreadsheet\Writer\Xlsx($spreadsheet);
        
        $response = new \Symfony\Component\HttpFoundation\StreamedResponse(function () use ($writer) {
            $writer->save('php://output');
        });

        $response->headers->set('Content-Type', 'application/vnd.openxmlformats-officedocument.spreadsheetml.sheet');
        $response->headers->set('Content-Disposition', 'attachment;filename="' . $fileName . '"');
        $response->headers->set('Cache-Control', 'max-age=0');

        return $response;
    }
}