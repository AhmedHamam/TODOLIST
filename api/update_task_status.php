<?php
session_start();
require_once '../config/database.php';

header('Content-Type: application/json');

if (!isset($_SESSION['user_id'])) {
    http_response_code(401);
    echo json_encode(['success' => false, 'message' => 'غير مصرح']);
    exit();
}

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $task_id = filter_var($_POST['task_id'], FILTER_VALIDATE_INT);
    $status = $_POST['status'] ? 'completed' : 'pending';
    $user_id = $_SESSION['user_id'];

    try {
        // التحقق من ملكية المهمة
        $check_stmt = $pdo->prepare("SELECT id FROM tasks WHERE id = ? AND user_id = ?");
        $check_stmt->execute([$task_id, $user_id]);
        
        if (!$check_stmt->fetch()) {
            http_response_code(403);
            echo json_encode(['success' => false, 'message' => 'غير مصرح بتعديل هذه المهمة']);
            exit();
        }

        // تحديث حالة المهمة
        $stmt = $pdo->prepare("
            UPDATE tasks 
            SET status = ?, updated_at = NOW()
            WHERE id = ? AND user_id = ?
        ");
        
        $stmt->execute([$status, $task_id, $user_id]);
        
        // جلب إحصائيات المهام المحدثة
        $stats_stmt = $pdo->prepare("
            SELECT 
                COUNT(*) as total_count,
                SUM(CASE WHEN status = 'completed' THEN 1 ELSE 0 END) as completed_count
            FROM tasks 
            WHERE user_id = ?
        ");
        $stats_stmt->execute([$user_id]);
        $stats = $stats_stmt->fetch(PDO::FETCH_ASSOC);
        
        echo json_encode([
            'success' => true,
            'message' => $status === 'completed' ? 'تم إكمال المهمة' : 'تم إعادة فتح المهمة',
            'status' => $status,
            'completed_count' => (int)$stats['completed_count'],
            'total_count' => (int)$stats['total_count']
        ]);
        
    } catch(PDOException $e) {
        http_response_code(500);
        echo json_encode(['success' => false, 'message' => 'حدث خطأ في قاعدة البيانات']);
    }
} else {
    http_response_code(405);
    echo json_encode(['success' => false, 'message' => 'طريقة طلب غير صحيحة']);
}
