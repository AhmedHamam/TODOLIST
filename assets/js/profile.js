// وظائف خاصة بصفحة الملف الشخصي
const ProfilePage = {
    init: function() {
        this.bindEvents();
    },

    bindEvents: function() {
        // تحديث الملف الشخصي
        $(document).on('submit', '#updateProfileForm', function(e) {
            e.preventDefault();
            const $form = $(this);
            const $submitBtn = $form.find('button[type="submit"]');
            const $feedback = $form.find('.feedback');
            
            // تعطيل زر الإرسال أثناء المعالجة
            $submitBtn.prop('disabled', true);
            
            $.ajax({
                url: 'api/update_profile.php',
                method: 'POST',
                data: $form.serialize(),
                success: function(response) {
                    if (response.success) {
                        // تحديث اسم المستخدم في الواجهة
                        $('.user-name').text(response.user.name);
                        
                        // إظهار رسالة النجاح
                        TodoApp.showAlert(response.message);
                        
                        // مسح حقول كلمة المرور
                        $form.find('input[type="password"]').val('');
                    } else {
                        TodoApp.showAlert(response.message, 'danger');
                    }
                },
                error: function(xhr) {
                    let errorMessage = 'حدث خطأ أثناء تحديث الملف الشخصي';
                    try {
                        const response = JSON.parse(xhr.responseText);
                        if (response.message) {
                            errorMessage = response.message;
                        }
                    } catch (e) {}
                    
                    TodoApp.showAlert(errorMessage, 'danger');
                },
                complete: function() {
                    // إعادة تفعيل زر الإرسال
                    $submitBtn.prop('disabled', false);
                }
            });
        });

        // تغيير كلمة المرور
        $(document).on('submit', '#changePasswordForm', function(e) {
            e.preventDefault();
            const $form = $(this);
            const $submitBtn = $form.find('button[type="submit"]');
            
            // التحقق من تطابق كلمة المرور الجديدة
            const newPassword = $form.find('input[name="new_password"]').val();
            const confirmPassword = $form.find('input[name="confirm_password"]').val();
            
            if (newPassword !== confirmPassword) {
                TodoApp.showAlert('كلمة المرور الجديدة غير متطابقة مع تأكيد كلمة المرور', 'danger');
                return;
            }
            
            // التحقق من طول كلمة المرور
            if (newPassword.length < 8) {
                TodoApp.showAlert('يجب أن تكون كلمة المرور الجديدة 8 أحرف على الأقل', 'danger');
                return;
            }
            
            // التحقق من تعقيد كلمة المرور
            if (!/^(?=.*[a-z])(?=.*[A-Z])(?=.*\d).+$/.test(newPassword)) {
                TodoApp.showAlert('يجب أن تحتوي كلمة المرور على حروف كبيرة وصغيرة وأرقام', 'danger');
                return;
            }
            
            $submitBtn.prop('disabled', true);
            
            $.ajax({
                url: 'api/change_password.php',
                method: 'POST',
                data: $form.serialize(),
                success: function(response) {
                    if (response.success) {
                        TodoApp.showAlert(response.message);
                        $form[0].reset();
                    } else {
                        TodoApp.showAlert(response.message, 'danger');
                    }
                },
                error: function(xhr) {
                    let errorMessage = 'حدث خطأ أثناء تغيير كلمة المرور';
                    try {
                        const response = JSON.parse(xhr.responseText);
                        if (response.message) {
                            errorMessage = response.message;
                        }
                    } catch (e) {}
                    
                    TodoApp.showAlert(errorMessage, 'danger');
                },
                complete: function() {
                    $submitBtn.prop('disabled', false);
                }
            });
        });

        // تحميل الصورة الشخصية
        $(document).on('change', '#profileImage', function() {
            const file = this.files[0];
            const $form = $(this).closest('form');
            
            if (file) {
                const formData = new FormData();
                formData.append('profile_image', file);
                
                $.ajax({
                    url: 'api/upload_profile_image.php',
                    method: 'POST',
                    data: formData,
                    processData: false,
                    contentType: false,
                    success: function(response) {
                        if (response.success) {
                            // تحديث صورة الملف الشخصي
                            $('.profile-image').attr('src', response.image_url);
                            TodoApp.showAlert('تم تحديث الصورة الشخصية بنجاح');
                        } else {
                            TodoApp.showAlert(response.message, 'danger');
                        }
                    },
                    error: function() {
                        TodoApp.showAlert('حدث خطأ أثناء تحميل الصورة', 'danger');
                    }
                });
            }
        });
    }
};

// تهيئة الصفحة عند التحميل
$(document).ready(function() {
    ProfilePage.init();
});
