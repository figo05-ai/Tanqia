<!DOCTYPE html>
<html lang="ar" dir="rtl">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>تسجيل الدخول | Tanqia Skills</title>
    <link href="https://fonts.googleapis.com/css2?family=Cairo:wght@400;500;600;700&display=swap" rel="stylesheet">
    <script src="https://cdn.tailwindcss.com"></script>
    <script defer src="https://cdn.jsdelivr.net/npm/alpinejs@3.x.x/dist/cdn.min.js"></script>
    <style>
        body {
            font-family: 'Cairo', sans-serif;
        }

        @keyframes fadeInUp {
            from {
                opacity: 0;
                transform: translateY(20px);
            }

            to {
                opacity: 1;
                transform: translateY(0);
            }
        }

        .animate-fade-in-up {
            animation: fadeInUp 0.6s ease-out forwards;
        }
    </style>
</head>

<body class="bg-gradient-to-br from-slate-50 to-indigo-50 min-h-screen flex items-center justify-center p-4">

    <div x-data="{ loading: false }" class="w-full max-w-md animate-fade-in-up">
        <div class="bg-white/80 backdrop-blur-xl rounded-2xl shadow-xl border border-slate-200 p-8">
            <div class="text-center mb-8">
                <h1 class="text-3xl font-bold text-slate-800 mb-2">Tanqia Skills</h1>
                <p class="text-slate-500 text-sm">سجّل دخولك للمتابعة إلى لوحة التحكم</p>
            </div>

            <?php if($errors->any()): ?>
                <div x-data="{ show: true }" x-show="show" x-init="setTimeout(() => show = false, 4000)" x-transition
                    class="mb-6 p-4 rounded-xl bg-rose-50 border border-rose-200 text-rose-700 text-sm flex items-start justify-between animate-fade-in-up">
                    <span><?php echo e($errors->first()); ?></span>
                    <button @click="show = false" class="text-rose-400 hover:text-rose-600 transition">&times;</button>
                </div>
            <?php endif; ?>

            <form method="POST" action="<?php echo e(route('login')); ?>" class="space-y-5" @submit="loading = true">
                <?php echo csrf_field(); ?>
                <div>
                    <label class="block text-sm font-medium text-slate-700 mb-2">البريد الإلكتروني</label>
                    <input type="email" name="email" value="<?php echo e(old('email')); ?>" required
                        class="w-full px-4 py-3 rounded-xl border border-slate-300 focus:border-indigo-500 focus:ring-4 focus:ring-indigo-500/20 outline-none transition-all bg-white/50"
                        placeholder="name@company.com">
                    <?php $__errorArgs = ['email'];
$__bag = $errors->getBag($__errorArgs[1] ?? 'default');
if ($__bag->has($__errorArgs[0])) :
if (isset($message)) { $__messageOriginal = $message; }
$message = $__bag->first($__errorArgs[0]); ?>
                        <p class="mt-1 text-xs text-rose-500"><?php echo e($message); ?></p>
                    <?php unset($message);
if (isset($__messageOriginal)) { $message = $__messageOriginal; }
endif;
unset($__errorArgs, $__bag); ?>
                </div>

                <div>
                    <label class="block text-sm font-medium text-slate-700 mb-2">كلمة المرور</label>
                    <input type="password" name="password" required
                        class="w-full px-4 py-3 rounded-xl border border-slate-300 focus:border-indigo-500 focus:ring-4 focus:ring-indigo-500/20 outline-none transition-all bg-white/50"
                        placeholder="••••••••">
                </div>

                <button type="submit" :disabled="loading"
                    class="w-full py-3 px-4 bg-indigo-600 hover:bg-indigo-700 text-white font-semibold rounded-xl shadow-lg shadow-indigo-500/30 transition-all transform hover:scale-[1.02] active:scale-[0.98] disabled:opacity-70 disabled:cursor-not-allowed flex items-center justify-center gap-2">
                    <span x-show="!loading">دخول</span>
                    <span x-show="loading" class="flex items-center gap-2">
                        <svg class="animate-spin h-5 w-5 text-white" xmlns="http://www.w3.org/2000/svg" fill="none"
                            viewBox="0 0 24 24">
                            <circle class="opacity-25" cx="12" cy="12" r="10" stroke="currentColor"
                                stroke-width="4"></circle>
                            <path class="opacity-75" fill="currentColor"
                                d="M4 12a8 8 0 018-8V0C5.373 0 0 5.373 0 12h4z"></path>
                        </svg>
                        جاري الدخول...
                    </span>
                </button>
            </form>
        </div>
    </div>
</body>

</html>
<?php /**PATH /var/www/html/resources/views/login.blade.php ENDPATH**/ ?>