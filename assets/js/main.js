$(document).ready(function() {
    // إضافة مهمة جديدة
    $('#addTaskForm').on('submit', function(e) {
        e.preventDefault();
        
        $.ajax({
            url: 'api/add_task.php',
            method: 'POST',
            data: $(this).serialize(),
            success: function(response) {
                if (response.success) {
                    location.reload();
                } else {
                    alert('حدث خطأ أثناء إضافة المهمة');
                }
            },
            error: function() {
                alert('حدث خطأ في الاتصال بالخادم');
            }
        });
    });

    // تحديث حالة المهمة (مكتملة/غير مكتملة)
    $('.complete-task').on('click', function(e) {
        e.preventDefault();
        const taskId = $(this).data('task-id');
        
        $.ajax({
            url: 'api/update_task_status.php',
            method: 'POST',
            data: { task_id: taskId, status: 'completed' },
            success: function(response) {
                if (response.success) {
                    location.reload();
                } else {
                    alert('حدث خطأ أثناء تحديث حالة المهمة');
                }
            }
        });
    });

    // إضافة/إزالة من المفضلة
    $(document).on('click', '.favorite-star', function(e) {
        e.preventDefault();
        const $button = $(this);
        const taskId = $button.data('task-id');
        const $star = $button.find('i');
        const isFavorite = $star.hasClass('text-warning');
        const $taskCard = $button.closest('.task-card');
        const $favoritesList = $('#favorite-tasks-list');
        const $favoritesSection = $('#favorites-section');
        
        $.ajax({
            url: 'api/toggle_favorite.php',
            method: 'POST',
            data: { task_id: taskId, is_favorite: !isFavorite },
            success: function(response) {
                if (response.success) {
                    // تحديث حالة النجمة في جميع أماكن ظهور المهمة
                    $(`.favorite-star[data-task-id="${taskId}"] i`).toggleClass('text-warning text-muted');
                    
                    if (!isFavorite) {
                        // إضافة إلى المفضلة
                        if ($favoritesList.length > 0) {
                            const $clonedCard = $taskCard.clone(true);  // clone with events
                            if ($favoritesList.find('.no-tasks-message').length > 0) {
                                $favoritesList.empty();
                            }
                            $favoritesList.append($clonedCard);
                            $clonedCard.hide().fadeIn(300);
                            
                            // تحديث عداد المهام المفضلة
                            const currentCount = parseInt($('#favorite-tasks-count').text()) || 0;
                            $('#favorite-tasks-count').text(currentCount + 1);
                        }
                    } else {
                        // إزالة من المفضلة
                        const $favoriteCard = $favoritesList.find(`.task-card:has([data-task-id="${taskId}"])`);
                        if ($favoriteCard.length > 0) {
                            $favoriteCard.fadeOut(300, function() {
                                $(this).remove();
                                if ($favoritesList.find('.task-card').length === 0) {
                                    $favoritesList.html('<p class="text-center text-muted no-tasks-message">لا توجد مهام مفضلة</p>');
                                }
                            });
                            
                            // تحديث عداد المهام المفضلة
                            const currentCount = parseInt($('#favorite-tasks-count').text()) || 0;
                            if (currentCount > 0) {
                                $('#favorite-tasks-count').text(currentCount - 1);
                            }
                        }
                    }
                } else {
                    alert('حدث خطأ أثناء تحديث المفضلة');
                }
            },
            error: function() {
                alert('حدث خطأ في الاتصال بالخادم');
            }
        });
    });

    // حذف مهمة
    $('.delete-task').on('click', function(e) {
        e.preventDefault();
        if (!confirm('هل أنت متأكد من حذف هذه المهمة؟')) {
            return;
        }
        
        const taskId = $(this).data('task-id');
        
        $.ajax({
            url: 'api/delete_task.php',
            method: 'POST',
            data: { task_id: taskId },
            success: function(response) {
                if (response.success) {
                    location.reload();
                } else {
                    alert('حدث خطأ أثناء حذف المهمة');
                }
            }
        });
    });

    // تحديث المهمة
    $(document).on('submit', '.edit-task-form', function(e) {
        e.preventDefault();
        const $form = $(this);
        const taskId = $form.find('input[name="task_id"]').val();
        const $modal = $form.closest('.modal');
        const $taskCard = $(`.task-card:has([data-task-id="${taskId}"])`);

        $.ajax({
            url: 'api/update_task.php',
            method: 'POST',
            data: $form.serialize(),
            success: function(response) {
                if (response.success) {
                    const task = response.task;
                    
                    // تحديث عنوان المهمة
                    $taskCard.find('.card-title').text(task.title);
                    
                    // تحديث الوصف
                    if (task.description) {
                        $taskCard.find('.card-text').text(task.description);
                    }
                    
                    // تحديث التاريخ
                    if (task.due_date) {
                        const formattedDate = new Date(task.due_date).toLocaleDateString('ar-SA');
                        $taskCard.find('.text-muted i.fa-calendar-alt').parent().text(formattedDate);
                    }
                    
                    // تحديث الأولوية
                    const priorityClass = `priority-${task.priority}`;
                    $taskCard.removeClass('priority-high priority-medium priority-low').addClass(priorityClass);
                    
                    const priorityText = task.priority === 'high' ? 'عالية' : 
                                       (task.priority === 'medium' ? 'متوسطة' : 'منخفضة');
                    const priorityBadgeClass = task.priority === 'high' ? 'danger' : 
                                             (task.priority === 'medium' ? 'warning' : 'success');
                    $taskCard.find('.badge').removeClass('bg-danger bg-warning bg-success')
                            .addClass(`bg-${priorityBadgeClass}`).text(priorityText);
                    
                    // إغلاق النافذة المنبثقة
                    $modal.modal('hide');
                } else {
                    alert(response.message || 'حدث خطأ أثناء تحديث المهمة');
                }
            },
            error: function() {
                alert('حدث خطأ في الاتصال بالخادم');
            }
        });
    });

    // تغيير كلمة المرور
    $('#changePasswordForm').on('submit', function(e) {
        e.preventDefault();
        
        if ($('#new_password').val() !== $('#confirm_password').val()) {
            alert('كلمات المرور غير متطابقة');
            return;
        }
        
        $.ajax({
            url: 'api/change_password.php',
            method: 'POST',
            data: $(this).serialize(),
            success: function(response) {
                if (response.success) {
                    alert('تم تغيير كلمة المرور بنجاح');
                    $('#changePasswordModal').modal('hide');
                } else {
                    alert(response.message || 'حدث خطأ أثناء تغيير كلمة المرور');
                }
            }
        });
    });

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
                    $feedback.removeClass('alert-danger').addClass('alert-success')
                        .text(response.message).show();
                    
                    // مسح حقول كلمة المرور
                    $form.find('input[type="password"]').val('');
                } else {
                    // إظهار رسالة الخطأ
                    $feedback.removeClass('alert-success').addClass('alert-danger')
                        .text(response.message).show();
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
                
                $feedback.removeClass('alert-success').addClass('alert-danger')
                    .text(errorMessage).show();
            },
            complete: function() {
                // إعادة تفعيل زر الإرسال
                $submitBtn.prop('disabled', false);
                
                // إخفاء رسالة التنبيه بعد 5 ثواني
                setTimeout(() => {
                    $feedback.fadeOut();
                }, 5000);
            }
        });
    });
});
