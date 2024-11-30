<?php
session_start();
require_once 'config/database.php';

// التحقق من تسجيل الدخول
if (!isset($_SESSION['user_id'])) {
    header('Location: login.php');
    exit();
}

// جلب المهام
$user_id = $_SESSION['user_id'];

// المهام القادمة مرتبة حسب الأولوية والتاريخ
$stmt = $pdo->prepare("
    SELECT * FROM tasks 
    WHERE user_id = ? AND status = 'pending'
    ORDER BY CASE priority
        WHEN 'high' THEN 1
        WHEN 'medium' THEN 2
        WHEN 'low' THEN 3
    END, due_date ASC
");
$stmt->execute([$user_id]);
$pending_tasks = $stmt->fetchAll();

// المهام المكتملة
$stmt = $pdo->prepare("
    SELECT * FROM tasks 
    WHERE user_id = ? AND status = 'completed'
    ORDER BY updated_at DESC
");
$stmt->execute([$user_id]);
$completed_tasks = $stmt->fetchAll();

// المهام المفضلة
$stmt = $pdo->prepare("
    SELECT * FROM tasks 
    WHERE user_id = ? AND is_favorite = true
    ORDER BY due_date ASC
");
$stmt->execute([$user_id]);
$favorite_tasks = $stmt->fetchAll();
?>

<!DOCTYPE html>
<html lang="ar" dir="rtl">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>مهامي - نظام إدارة المهام</title>
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.rtl.min.css">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0/css/all.min.css">
    <link rel="stylesheet" href="assets/css/style.css">
</head>
<body>
    <?php include 'includes/navbar.php'; ?>

    <div class="container py-4">
        <div class="d-flex justify-content-between align-items-center mb-4">
            <h1>مهامي</h1>
            <button type="button" class="btn btn-primary" data-bs-toggle="modal" data-bs-target="#addTaskModal">
                <i class="fas fa-plus"></i> مهمة جديدة
            </button>
        </div>

        <!-- المهام القادمة -->
        <div class="card mb-4">
            <div class="card-header bg-primary text-white">
                <h5 class="card-title mb-0">المهام القادمة</h5>
            </div>
            <div class="card-body">
                <?php if (empty($pending_tasks)): ?>
                    <p class="text-muted">لا توجد مهام قادمة</p>
                <?php else: ?>
                    <?php foreach ($pending_tasks as $task): ?>
                        <div class="task-card card mb-2 priority-<?php echo $task['priority']; ?>">
                            <div class="card-body">
                                <div class="d-flex justify-content-between align-items-center">
                                    <h5 class="card-title mb-0"><?php echo htmlspecialchars($task['title']); ?></h5>
                                    <div>
                                        <button class="btn btn-link favorite-star" data-task-id="<?php echo $task['id']; ?>">
                                            <i class="fas fa-star <?php echo $task['is_favorite'] ? 'text-warning' : 'text-muted'; ?>"></i>
                                        </button>
                                        <div class="dropdown d-inline">
                                            <button class="btn btn-link" data-bs-toggle="dropdown">
                                                <i class="fas fa-ellipsis-v"></i>
                                            </button>
                                            <ul class="dropdown-menu">
                                                <li><a class="dropdown-item" href="#" data-bs-toggle="modal" data-bs-target="#editTaskModal<?php echo $task['id']; ?>">تعديل</a></li>
                                                <li><a class="dropdown-item text-success complete-task" href="#" data-task-id="<?php echo $task['id']; ?>">إكمال</a></li>
                                                <li><hr class="dropdown-divider"></li>
                                                <li><a class="dropdown-item text-danger delete-task" href="#" data-task-id="<?php echo $task['id']; ?>">حذف</a></li>
                                            </ul>
                                        </div>
                                    </div>
                                </div>
                                <p class="card-text mt-2"><?php echo htmlspecialchars($task['description']); ?></p>
                                <div class="d-flex justify-content-between align-items-center mt-2">
                                    <small class="text-muted">
                                        <i class="far fa-calendar-alt"></i> 
                                        <?php echo date('Y/m/d', strtotime($task['due_date'])); ?>
                                    </small>
                                    <span class="badge bg-<?php 
                                        echo $task['priority'] === 'high' ? 'danger' : 
                                            ($task['priority'] === 'medium' ? 'warning' : 'success'); 
                                    ?>">
                                        <?php echo $task['priority'] === 'high' ? 'عالية' : 
                                            ($task['priority'] === 'medium' ? 'متوسطة' : 'منخفضة'); ?>
                                    </span>
                                </div>
                            </div>
                        </div>
                    <?php endforeach; ?>
                <?php endif; ?>
            </div>
        </div>

        <!-- المهام المفضلة -->
        <div class="card mb-4" id="favorites-section">
            <div class="card-header d-flex justify-content-between align-items-center">
                <h5 class="mb-0">
                    <i class="fas fa-star text-warning me-2"></i>
                    المهام المفضلة
                    <span class="badge bg-warning text-dark ms-2" id="favorite-tasks-count"><?php echo count($favorite_tasks); ?></span>
                </h5>
            </div>
            <div class="card-body" id="favorite-tasks-list">
                <?php if (count($favorite_tasks) > 0): ?>
                    <?php foreach ($favorite_tasks as $task): ?>
                        <div class="task-card card mb-2 <?php echo 'priority-' . $task['priority']; ?>">
                            <div class="card-body">
                                <div class="d-flex justify-content-between align-items-center">
                                    <h5 class="card-title mb-1"><?php echo htmlspecialchars($task['title']); ?></h5>
                                    <div>
                                        <button class="btn btn-link favorite-star" data-task-id="<?php echo $task['id']; ?>">
                                            <i class="fas fa-star <?php echo $task['is_favorite'] ? 'text-warning' : 'text-muted'; ?>"></i>
                                        </button>
                                    </div>
                                </div>
                                <p class="card-text text-muted small mb-2">
                                    <?php if ($task['due_date']): ?>
                                        <i class="fas fa-calendar-alt me-1"></i>
                                        <?php echo date('Y/m/d', strtotime($task['due_date'])); ?>
                                    <?php endif; ?>
                                </p>
                                <?php if ($task['description']): ?>
                                    <p class="card-text"><?php echo htmlspecialchars($task['description']); ?></p>
                                <?php endif; ?>
                            </div>
                        </div>
                    <?php endforeach; ?>
                <?php else: ?>
                    <p class="text-center text-muted no-tasks-message">لا توجد مهام مفضلة</p>
                <?php endif; ?>
            </div>
        </div>

        <!-- المهام المكتملة -->
        <div class="card">
            <div class="card-header bg-success text-white">
                <h5 class="card-title mb-0">المهام المكتملة</h5>
            </div>
            <div class="card-body">
                <?php if (empty($completed_tasks)): ?>
                    <p class="text-muted">لا توجد مهام مكتملة</p>
                <?php else: ?>
                    <?php foreach ($completed_tasks as $task): ?>
                        <div class="task-card card mb-2">
                            <div class="card-body">
                                <div class="d-flex justify-content-between align-items-center">
                                    <h5 class="card-title text-muted"><del><?php echo htmlspecialchars($task['title']); ?></del></h5>
                                    <small class="text-muted">
                                        تم الإكمال في <?php echo date('Y/m/d', strtotime($task['updated_at'])); ?>
                                    </small>
                                </div>
                            </div>
                        </div>
                    <?php endforeach; ?>
                <?php endif; ?>
            </div>
        </div>
    </div>

    <!-- Modal إضافة مهمة جديدة -->
    <div class="modal fade" id="addTaskModal" tabindex="-1">
        <div class="modal-dialog">
            <div class="modal-content">
                <div class="modal-header">
                    <h5 class="modal-title">إضافة مهمة جديدة</h5>
                    <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
                </div>
                <form id="addTaskForm">
                    <div class="modal-body">
                        <div class="mb-3">
                            <label for="title" class="form-label">عنوان المهمة</label>
                            <input type="text" class="form-control" id="title" name="title" required>
                        </div>
                        <div class="mb-3">
                            <label for="description" class="form-label">وصف المهمة</label>
                            <textarea class="form-control" id="description" name="description" rows="3"></textarea>
                        </div>
                        <div class="mb-3">
                            <label for="due_date" class="form-label">تاريخ الاستحقاق</label>
                            <input type="date" class="form-control" id="due_date" name="due_date" required>
                        </div>
                        <div class="mb-3">
                            <label for="priority" class="form-label">الأولوية</label>
                            <select class="form-select" id="priority" name="priority">
                                <option value="low">منخفضة</option>
                                <option value="medium" selected>متوسطة</option>
                                <option value="high">عالية</option>
                            </select>
                        </div>
                    </div>
                    <div class="modal-footer">
                        <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">إلغاء</button>
                        <button type="submit" class="btn btn-primary">إضافة المهمة</button>
                    </div>
                </form>
            </div>
        </div>
    </div>

    <?php include 'includes/footer.php'; ?>

    <script src="https://code.jquery.com/jquery-3.6.0.min.js"></script>
    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>
    <script src="assets/js/main.js"></script>
</body>
</html>
