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
    $db = new PDO("mysql:host=$host;dbname=$dbname;charset=utf8mb4", $username, $password);
    $db->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);

    // التحقق من البيانات المدخلة
    $userId = $_SESSION['user_id'];
    $name = filter_input(INPUT_POST, 'name', FILTER_SANITIZE_STRING);
    $email = filter_input(INPUT_POST, 'email', FILTER_SANITIZE_EMAIL);
    $currentPassword = $_POST['current_password'] ?? '';
    $newPassword = $_POST['new_password'] ?? '';

    // التحقق من صحة البريد الإلكتروني
    if (!filter_var($email, FILTER_VALIDATE_EMAIL)) {
        throw new Exception('البريد الإلكتروني غير صالح');
    }

    // التحقق من أن البريد الإلكتروني غير مستخدم من قبل مستخدم آخر
    $stmt = $db->prepare("SELECT id FROM users WHERE email = ? AND id != ?");
    $stmt->execute([$email, $userId]);
    if ($stmt->rowCount() > 0) {
        throw new Exception('البريد الإلكتروني مستخدم بالفعل');
    }

    // التحقق من كلمة المرور الحالية إذا تم تقديم كلمة مرور جديدة
    if (!empty($newPassword)) {
        $stmt = $db->prepare("SELECT password FROM users WHERE id = ?");
        $stmt->execute([$userId]);
        $user = $stmt->fetch(PDO::FETCH_ASSOC);

        if (!password_verify($currentPassword, $user['password'])) {
            throw new Exception('كلمة المرور الحالية غير صحيحة');
        }

        // التحقق من قوة كلمة المرور الجديدة
        if (strlen($newPassword) < 8) {
            throw new Exception('يجب أن تكون كلمة المرور الجديدة 8 أحرف على الأقل');
        }
    }

    // تحديث معلومات المستخدم
    if (!empty($newPassword)) {
        $hashedPassword = password_hash($newPassword, PASSWORD_DEFAULT);
        $stmt = $db->prepare("UPDATE users SET name = ?, email = ?, password = ? WHERE id = ?");
        $stmt->execute([$name, $email, $hashedPassword, $userId]);
    } else {
        $stmt = $db->prepare("UPDATE users SET name = ?, email = ? WHERE id = ?");
        $stmt->execute([$name, $email, $userId]);
    }

    // تحديث بيانات الجلسة
    $_SESSION['user_name'] = $name;
    $_SESSION['user_email'] = $email;
    $_SESSION['success_message'] = 'تم تحديث الملف الشخصي بنجاح';

    echo json_encode([
        'success' => true,
        'message' => 'تم تحديث الملف الشخصي بنجاح',
        'user' => [
            'name' => $name,
            'email' => $email
        ]
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
