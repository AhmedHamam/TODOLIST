<?php
session_start();
require_once 'config/database.php';

$error = '';
$success = '';
$valid_token = false;
$token = $_GET['token'] ?? '';

if ($token) {
    // التحقق من صلاحية الرمز
    $stmt = $pdo->prepare("
        SELECT email 
        FROM password_resets 
        WHERE token = ? AND expires_at > NOW() 
        ORDER BY created_at DESC 
        LIMIT 1
    ");
    $stmt->execute([$token]);
    $reset = $stmt->fetch();

    if ($reset) {
        $valid_token = true;
    } else {
        $error = 'رابط استعادة كلمة المرور غير صالح أو منتهي الصلاحية';
    }
}

if ($_SERVER['REQUEST_METHOD'] == 'POST' && $valid_token) {
    $password = $_POST['password'];
    $confirm_password = $_POST['confirm_password'];
    
    if ($password !== $confirm_password) {
        $error = 'كلمات المرور غير متطابقة';
    } else {
        try {
            // تحديث كلمة المرور
            $hashed_password = password_hash($password, PASSWORD_DEFAULT);
            $stmt = $pdo->prepare("UPDATE users SET password = ? WHERE email = ?");
            $stmt->execute([$hashed_password, $reset['email']]);
            
            // حذف رمز الاستعادة
            $stmt = $pdo->prepare("DELETE FROM password_resets WHERE token = ?");
            $stmt->execute([$token]);
            
            $success = 'تم تغيير كلمة المرور بنجاح. يمكنك الآن تسجيل الدخول';
        } catch(PDOException $e) {
            $error = 'حدث خطأ أثناء تحديث كلمة المرور';
        }
    }
}
?>
<!DOCTYPE html>
<html lang="ar" dir="rtl">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>تعيين كلمة المرور الجديدة - نظام إدارة المهام</title>
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.rtl.min.css">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0/css/all.min.css">
    <link rel="stylesheet" href="assets/css/style.css">
</head>
<body>
    <?php include 'includes/navbar.php'; ?>

    <div class="container">
        <div class="auth-form">
            <h2 class="text-center mb-4">تعيين كلمة المرور الجديدة</h2>
            
            <?php if ($error): ?>
                <div class="alert alert-danger"><?php echo $error; ?></div>
            <?php endif; ?>

            <?php if ($success): ?>
                <div class="alert alert-success">
                    <?php echo $success; ?>
                    <br>
                    <a href="login.php" class="alert-link">انتقل إلى صفحة تسجيل الدخول</a>
                </div>
            <?php elseif ($valid_token): ?>
                <form method="POST" action="">
                    <div class="mb-3">
                        <label for="password" class="form-label">كلمة المرور الجديدة</label>
                        <input type="password" class="form-control" id="password" name="password" required>
                    </div>
                    <div class="mb-3">
                        <label for="confirm_password" class="form-label">تأكيد كلمة المرور الجديدة</label>
                        <input type="password" class="form-control" id="confirm_password" name="confirm_password" required>
                    </div>
                    <button type="submit" class="btn btn-primary w-100">تعيين كلمة المرور</button>
                </form>
            <?php endif; ?>
            
            <div class="text-center mt-3">
                <a href="login.php">العودة إلى تسجيل الدخول</a>
            </div>
        </div>
    </div>

    <?php include 'includes/footer.php'; ?>

    <script src="https://code.jquery.com/jquery-3.6.0.min.js"></script>
    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>
</body>
</html>
