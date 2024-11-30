// وظائف خاصة بصفحة المهام
const TasksPage = {
    init: function() {
        this.bindEvents();
    },

    bindEvents: function() {
        // إضافة مهمة جديدة
        $(document).on('submit', '#addTaskForm', function(e) {
            e.preventDefault();
            const $form = $(this);
            const $submitBtn = $form.find('button[type="submit"]');
            
            $submitBtn.prop('disabled', true);
            
            $.ajax({
                url: 'api/add_task.php',
                method: 'POST',
                data: $form.serialize(),
                success: function(response) {
                    if (response.success) {
                        TodoApp.showAlert('تمت إضافة المهمة بنجاح');
                        location.reload();
                    } else {
                        TodoApp.showAlert(response.message, 'danger');
                    }
                },
                error: function() {
                    TodoApp.showAlert('حدث خطأ أثناء إضافة المهمة', 'danger');
                },
                complete: function() {
                    $submitBtn.prop('disabled', false);
                }
            });
        });

        // تحديث حالة المهمة
        $(document).on('change', '.task-status-checkbox', function() {
            const taskId = $(this).data('task-id');
            const isCompleted = $(this).prop('checked');
            const $taskCard = $(this).closest('.task-card');
            
            $.ajax({
                url: 'api/update_task_status.php',
                method: 'POST',
                data: {
                    task_id: taskId,
                    status: isCompleted ? 1 : 0
                },
                success: function(response) {
                    if (response.success) {
                        $taskCard.toggleClass('completed', isCompleted);
                        TodoApp.updateTaskCount(response.completed_count, response.total_count);
                    } else {
                        TodoApp.showAlert(response.message, 'danger');
                    }
                },
                error: function() {
                    TodoApp.showAlert('حدث خطأ أثناء تحديث حالة المهمة', 'danger');
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
                        TodoApp.showAlert('تم تحديث المهمة بنجاح');
                    } else {
                        TodoApp.showAlert(response.message, 'danger');
                    }
                },
                error: function() {
                    TodoApp.showAlert('حدث خطأ أثناء تحديث المهمة', 'danger');
                }
            });
        });

        // حذف المهمة
        $(document).on('click', '.delete-task', function() {
            const taskId = $(this).data('task-id');
            const $taskCard = $(this).closest('.task-card');
            
            if (confirm('هل أنت متأكد من حذف هذه المهمة؟')) {
                $.ajax({
                    url: 'api/delete_task.php',
                    method: 'POST',
                    data: { task_id: taskId },
                    success: function(response) {
                        if (response.success) {
                            $taskCard.fadeOut(function() {
                                $(this).remove();
                                TodoApp.updateTaskCount(response.completed_count, response.total_count);
                            });
                            TodoApp.showAlert('تم حذف المهمة بنجاح');
                        } else {
                            TodoApp.showAlert(response.message, 'danger');
                        }
                    },
                    error: function() {
                        TodoApp.showAlert('حدث خطأ أثناء حذف المهمة', 'danger');
                    }
                });
            }
        });

        // تبديل المفضلة
        $(document).on('click', '.toggle-favorite', function() {
            const $btn = $(this);
            const taskId = $btn.data('task-id');
            const $icon = $btn.find('i');
            const isFavorite = $icon.hasClass('fas');
            
            $.ajax({
                url: 'api/toggle_favorite.php',
                method: 'POST',
                data: { task_id: taskId },
                success: function(response) {
                    if (response.success) {
                        $icon.toggleClass('far fas');
                        const favoriteCount = response.favorite_count;
                        $('#favoriteTasksCount').text(favoriteCount);
                        
                        TodoApp.showAlert(
                            isFavorite ? 'تمت إزالة المهمة من المفضلة' : 'تمت إضافة المهمة للمفضلة'
                        );
                    } else {
                        TodoApp.showAlert(response.message, 'danger');
                    }
                },
                error: function() {
                    TodoApp.showAlert('حدث خطأ أثناء تحديث المفضلة', 'danger');
                }
            });
        });
    }
};

// تهيئة الصفحة عند التحميل
$(document).ready(function() {
    TasksPage.init();
});