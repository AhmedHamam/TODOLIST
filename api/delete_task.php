<?php
session_start();
require_once '../config/database.php';

header('Content-Type: application/json');

if (!isset($_SESSION['user_id'])) {
    echo json_encode(['success' => false, 'message' => 'غير مصرح']);
    exit();
}

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $task_id = $_POST['task_id'];
    $user_id = $_SESSION['user_id'];

    try {
        $stmt = $pdo->prepare("
            DELETE FROM tasks 
            WHERE id = ? AND user_id = ?
        ");
        
        $stmt->execute([$task_id, $user_id]);
        
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
