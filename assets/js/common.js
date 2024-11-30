// الوظائف المشتركة
const TodoApp = {
    // عرض رسائل للمستخدم
    showAlert: function(message, type = 'success') {
        const alertDiv = $('<div>')
            .addClass(`alert alert-${type} alert-dismissible fade show`)
            .attr('role', 'alert')
            .html(`
                ${message}
                <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Close"></button>
            `);
        
        // إضافة التنبيه إلى الحاوية
        const $alertsContainer = $('#alerts-container');
        if ($alertsContainer.length === 0) {
            // إذا لم تكن حاوية التنبيهات موجودة، قم بإنشائها
            $('<div>').attr('id', 'alerts-container')
                      .addClass('mb-3')
                      .prependTo('.container');
        }
        
        $('#alerts-container').prepend(alertDiv);
        
        // تمرير التنبيه ببطء
        alertDiv.hide().slideDown();
        
        // إخفاء التنبيه تلقائياً بعد 5 ثواني
        setTimeout(() => {
            alertDiv.slideUp(() => alertDiv.remove());
        }, 5000);
    },

    // تحديث عدد المهام
    updateTaskCount: function(completedCount, totalCount) {
        $('#completedTasksCount').text(completedCount);
        $('#totalTasksCount').text(totalCount);
    },

    // تهيئة التوقيت العربي
    initializeArabicDateTime: function() {
        // تهيئة التاريخ باللغة العربية
        if (typeof moment !== 'undefined') {
            moment.locale('ar');
            $('.datetime-ar').each(function() {
                const timestamp = $(this).data('timestamp');
                $(this).text(moment(timestamp).format('LLLL'));
            });
        }
    },

    // تهيئة مكونات Bootstrap
    initializeBootstrapComponents: function() {
        // تهيئة التلميحات
        const tooltipTriggerList = [].slice.call(document.querySelectorAll('[data-bs-toggle="tooltip"]'));
        tooltipTriggerList.map(function (tooltipTriggerEl) {
            return new bootstrap.Tooltip(tooltipTriggerEl);
        });

        // تهيئة النوافذ المنبثقة
        const popoverTriggerList = [].slice.call(document.querySelectorAll('[data-bs-toggle="popover"]'));
        popoverTriggerList.map(function (popoverTriggerEl) {
            return new bootstrap.Popover(popoverTriggerEl);
        });
    }
};

// تهيئة عند تحميل الصفحة
$(document).ready(function() {
    TodoApp.initializeArabicDateTime();
    TodoApp.initializeBootstrapComponents();
});
