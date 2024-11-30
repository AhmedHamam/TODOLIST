<?php
session_start();
require_once '../config/database.php';

header('Content-Type: application/json');

if (!isset($_SESSION['user_id'])) {
    echo json_encode(['success' => false, 'message' => 'غير مصرح']);
    exit();
}

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    // التحقق من وجود البيانات المطلوبة
    if (!isset($_POST['task_id']) || !isset($_POST['is_favorite'])) {
        echo json_encode(['success' => false, 'message' => 'البيانات المطلوبة غير مكتملة']);
        exit();
    }

    $task_id = filter_var($_POST['task_id'], FILTER_VALIDATE_INT);
    $is_favorite = filter_var($_POST['is_favorite'], FILTER_VALIDATE_BOOLEAN);
    $user_id = $_SESSION['user_id'];

    // التحقق من صحة معرف المهمة
    if ($task_id === false) {
        echo json_encode(['success' => false, 'message' => 'معرف المهمة غير صالح']);
        exit();
    }

    try {
        $stmt = $pdo->prepare("
            UPDATE tasks 
            SET is_favorite = ?
            WHERE id = ? AND user_id = ?
        ");
        
        $stmt->execute([$is_favorite ? 1 : 0, $task_id, $user_id]);
        
        if ($stmt->rowCount() > 0) {
            echo json_encode(['success' => true]);
        } else {
            echo json_encode(['success' => false, 'message' => 'المهمة غير موجودة أو غير مصرح بها']);
        }
    } catch(PDOException $e) {
        echo json_encode(['success' => false, 'message' => 'حدث خطأ في قاعدة البيانات']);
    }
} else {
    echo json_encode(['success' => false, 'message' => 'طريقة طلب غير صحيحة']);
}
