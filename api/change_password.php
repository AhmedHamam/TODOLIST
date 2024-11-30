<?php
session_start();
header('Content-Type: application/json');

// تأكد من تسجيل دخول المستخدم
if (!isset($_SESSION['user_id'])) {
    http_response_code(401);
    echo json_encode(['success' => false, 'message' => 'يجب تسجيل الدخول أولاً']);
    exit;
}

require_once '../config/database.php';

try {
    // التحقق من وجود البيانات المطلوبة
    if (!isset($_POST['current_password']) || !isset($_POST['new_password']) || !isset($_POST['confirm_password'])) {
        throw new Exception('جميع الحقول مطلوبة');
    }

    $currentPassword = $_POST['current_password'];
    $newPassword = $_POST['new_password'];
    $confirmPassword = $_POST['confirm_password'];
    $userId = $_SESSION['user_id'];

    // التحقق من تطابق كلمة المرور الجديدة
    if ($newPassword !== $confirmPassword) {
        throw new Exception('كلمة المرور الجديدة غير متطابقة مع تأكيد كلمة المرور');
    }

    // التحقق من طول كلمة المرور الجديدة
    if (strlen($newPassword) < 8) {
        throw new Exception('يجب أن تكون كلمة المرور الجديدة 8 أحرف على الأقل');
    }

    // التحقق من تعقيد كلمة المرور
    if (!preg_match('/^(?=.*[a-z])(?=.*[A-Z])(?=.*\d).+$/', $newPassword)) {
        throw new Exception('يجب أن تحتوي كلمة المرور على حروف كبيرة وصغيرة وأرقام');
    }

    $db = new PDO("mysql:host=$host;dbname=$dbname;charset=utf8mb4", $username, $password);
    $db->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);

    // التحقق من كلمة المرور الحالية
    $stmt = $db->prepare("SELECT password FROM users WHERE id = ?");
    $stmt->execute([$userId]);
    $user = $stmt->fetch(PDO::FETCH_ASSOC);

    if (!$user || !password_verify($currentPassword, $user['password'])) {
        throw new Exception('كلمة المرور الحالية غير صحيحة');
    }

    // التحقق من أن كلمة المرور الجديدة مختلفة عن الحالية
    if (password_verify($newPassword, $user['password'])) {
        throw new Exception('كلمة المرور الجديدة يجب أن تكون مختلفة عن كلمة المرور الحالية');
    }

    // تحديث كلمة المرور
    $hashedPassword = password_hash($newPassword, PASSWORD_DEFAULT);
    $stmt = $db->prepare("UPDATE users SET password = ?, password_changed_at = NOW() WHERE id = ?");
    $stmt->execute([$hashedPassword, $userId]);

    // تسجيل عملية تغيير كلمة المرور في سجل النظام
    $stmt = $db->prepare("INSERT INTO password_change_log (user_id, changed_at, ip_address) VALUES (?, NOW(), ?)");
    $stmt->execute([$userId, $_SERVER['REMOTE_ADDR']]);

    $_SESSION['success_message'] = 'تم تغيير كلمة المرور بنجاح';

    echo json_encode([
        'success' => true,
        'message' => 'تم تغيير كلمة المرور بنجاح'
    ]);

} catch (Exception $e) {
    $_SESSION['error_message'] = $e->getMessage();
    http_response_code(400);
    echo json_encode([
        'success' => false,
        'message' => $e->getMessage()
    ]);
} catch (PDOException $e) {
    $_SESSION['error_message'] = 'حدث خطأ في قاعدة البيانات';
    http_response_code(500);
    echo json_encode([
        'success' => false,
        'message' => 'حدث خطأ في قاعدة البيانات'
    ]);
}
