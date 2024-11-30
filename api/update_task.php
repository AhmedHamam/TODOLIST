<?php
session_start();
require_once '../config/database.php';

header('Content-Type: application/json');

if (!isset($_SESSION['user_id'])) {
    echo json_encode(['success' => false, 'message' => 'غير مصرح']);
    exit();
}

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $task_id = filter_var($_POST['task_id'], FILTER_VALIDATE_INT);
    $title = trim($_POST['title']);
    $description = trim($_POST['description'] ?? '');
    $due_date = $_POST['due_date'];
    $priority = $_POST['priority'];
    $user_id = $_SESSION['user_id'];

    // التحقق من صحة البيانات
    if (!$task_id || empty($title) || empty($due_date)) {
        echo json_encode(['success' => false, 'message' => 'جميع الحقول المطلوبة يجب ملؤها']);
        exit();
    }

    try {
        // التحقق من ملكية المهمة
        $check_stmt = $pdo->prepare("SELECT id FROM tasks WHERE id = ? AND user_id = ?");
        $check_stmt->execute([$task_id, $user_id]);
        
        if (!$check_stmt->fetch()) {
            echo json_encode(['success' => false, 'message' => 'غير مصرح بتعديل هذه المهمة']);
            exit();
        }

        // تحديث المهمة
        $stmt = $pdo->prepare("
            UPDATE tasks 
            SET title = ?, description = ?, due_date = ?, priority = ?
            WHERE id = ? AND user_id = ?
        ");
        
        $stmt->execute([$title, $description, $due_date, $priority, $task_id, $user_id]);
        
        if ($stmt->rowCount() > 0) {
            // إرجاع البيانات المحدثة
            $get_task = $pdo->prepare("SELECT * FROM tasks WHERE id = ?");
            $get_task->execute([$task_id]);
            $updated_task = $get_task->fetch(PDO::FETCH_ASSOC);
            
            echo json_encode([
                'success' => true,
                'task' => $updated_task
            ]);
        } else {
            echo json_encode(['success' => false, 'message' => 'لم يتم تحديث المهمة']);
        }
    } catch(PDOException $e) {
        echo json_encode(['success' => false, 'message' => 'حدث خطأ في قاعدة البيانات']);
    }
} else {
    echo json_encode(['success' => false, 'message' => 'طريقة طلب غير صحيحة']);
}
