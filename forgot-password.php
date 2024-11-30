<?php
session_start();
require_once 'config/database.php';

$error = '';
$success = '';

if ($_SERVER['REQUEST_METHOD'] == 'POST') {
    $email = $_POST['email'];
    
    // التحقق من وجود البريد الإلكتروني
    $stmt = $pdo->prepare("SELECT id FROM users WHERE email = ?");
    $stmt->execute([$email]);
    $user = $stmt->fetch();

    if ($user) {
        // إنشاء رمز استعادة كلمة المرور
        $token = bin2hex(random_bytes(32));
        $expires = date('Y-m-d H:i:s', strtotime('+1 hour'));
        
        $stmt = $pdo->prepare("INSERT INTO password_resets (email, token, expires_at) VALUES (?, ?, ?)");
        $stmt->execute([$email, $token, $expires]);
        
        // إرسال رابط استعادة كلمة المرور (في بيئة الإنتاج، يجب إرسال بريد إلكتروني حقيقي)
        $reset_link = "http://{$_SERVER['HTTP_HOST']}/reset-password.php?token=" . $token;
        $success = 'تم إرسال رابط استعادة كلمة المرور إلى بريدك الإلكتروني';
        
        // في بيئة التطوير، نعرض الرابط مباشرة
        $success .= "<br>رابط الاستعادة: <a href='$reset_link'>$reset_link</a>";
    } else {
        $error = 'البريد الإلكتروني غير مسجل في النظام';
    }
}
?>
<!DOCTYPE html>
<html lang="ar" dir="rtl">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>استعادة كلمة المرور - نظام إدارة المهام</title>
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.rtl.min.css">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0/css/all.min.css">
    <link rel="stylesheet" href="assets/css/style.css">
</head>
<body>
    <?php include 'includes/navbar.php'; ?>

    <div class="container">
        <div class="auth-form">
            <h2 class="text-center mb-4">استعادة كلمة المرور</h2>
            
            <?php if ($error): ?>
                <div class="alert alert-danger"><?php echo $error; ?></div>
            <?php endif; ?>

            <?php if ($success): ?>
                <div class="alert alert-success"><?php echo $success; ?></div>
            <?php endif; ?>

            <form method="POST" action="">
                <div class="mb-3">
                    <label for="email" class="form-label">البريد الإلكتروني</label>
                    <input type="email" class="form-control" id="email" name="email" required>
                </div>
                <button type="submit" class="btn btn-primary w-100">إرسال رابط الاستعادة</button>
            </form>
            
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
