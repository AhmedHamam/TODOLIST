<?php
session_start();
require_once '../config/database.php';

header('Content-Type: application/json');

if (!isset($_SESSION['user_id'])) {
    echo json_encode(['success' => false, 'message' => 'غير مصرح']);
    exit();
}

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $user_id = $_SESSION['user_id'];
    $title = $_POST['title'];
    $description = $_POST['description'] ?? '';
    $due_date = $_POST['due_date'];
    $priority = $_POST['priority'];

    try {
        $stmt = $pdo->prepare("
            INSERT INTO tasks (user_id, title, description, due_date, priority)
            VALUES (?, ?, ?, ?, ?)
        ");
        
        $stmt->execute([$user_id, $title, $description, $due_date, $priority]);
        
        echo json_encode(['success' => true]);
    } catch(PDOException $e) {
        echo json_encode(['success' => false, 'message' => 'حدث خطأ في قاعدة البيانات']);
    }
} else {
    echo json_encode(['success' => false, 'message' => 'طريقة طلب غير صحيحة']);
}
