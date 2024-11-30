// وظائف خاصة بصفحات تسجيل الدخول والتسجيل
const AuthPage = {
    init: function() {
        this.bindEvents();
    },

    bindEvents: function() {
        // تسجيل الدخول
        $(document).on('submit', '#loginForm', function(e) {
            e.preventDefault();
            const $form = $(this);
            const $submitBtn = $form.find('button[type="submit"]');
            
            $submitBtn.prop('disabled', true);
            
            $.ajax({
                url: 'api/login.php',
                method: 'POST',
                data: $form.serialize(),
                success: function(response) {
                    if (response.success) {
                        window.location.href = 'tasks.php';
                    } else {
                        TodoApp.showAlert(response.message, 'danger');
                    }
                },
                error: function() {
                    TodoApp.showAlert('حدث خطأ أثناء تسجيل الدخول', 'danger');
                },
                complete: function() {
                    $submitBtn.prop('disabled', false);
                }
            });
        });

        // إنشاء حساب جديد
        $(document).on('submit', '#registerForm', function(e) {
            e.preventDefault();
            const $form = $(this);
            const $submitBtn = $form.find('button[type="submit"]');
            
            // التحقق من تطابق كلمات المرور
            const password = $form.find('input[name="password"]').val();
            const confirmPassword = $form.find('input[name="confirm_password"]').val();
            
            if (password !== confirmPassword) {
                TodoApp.showAlert('كلمات المرور غير متطابقة', 'danger');
                return;
            }
            
            $submitBtn.prop('disabled', true);
            
            $.ajax({
                url: 'api/register.php',
                method: 'POST',
                data: $form.serialize(),
                success: function(response) {
                    if (response.success) {
                        TodoApp.showAlert('تم إنشاء الحساب بنجاح، سيتم تحويلك لصفحة تسجيل الدخول');
                        setTimeout(() => {
                            window.location.href = 'login.php';
                        }, 2000);
                    } else {
                        TodoApp.showAlert(response.message, 'danger');
                    }
                },
                error: function() {
                    TodoApp.showAlert('حدث خطأ أثناء إنشاء الحساب', 'danger');
                },
                complete: function() {
                    $submitBtn.prop('disabled', false);
                }
            });
        });

        // إعادة تعيين كلمة المرور
        $(document).on('submit', '#resetPasswordForm', function(e) {
            e.preventDefault();
            const $form = $(this);
            const $submitBtn = $form.find('button[type="submit"]');
            
            $submitBtn.prop('disabled', true);
            
            $.ajax({
                url: 'api/reset_password.php',
                method: 'POST',
                data: $form.serialize(),
                success: function(response) {
                    if (response.success) {
                        TodoApp.showAlert('تم إرسال رابط إعادة تعيين كلمة المرور إلى بريدك الإلكتروني');
                        $form[0].reset();
                    } else {
                        TodoApp.showAlert(response.message, 'danger');
                    }
                },
                error: function() {
                    TodoApp.showAlert('حدث خطأ أثناء إرسال طلب إعادة تعيين كلمة المرور', 'danger');
                },
                complete: function() {
                    $submitBtn.prop('disabled', false);
                }
            });
        });
    }
};

// تهيئة الصفحة عند التحميل
$(document).ready(function() {
    AuthPage.init();
});
