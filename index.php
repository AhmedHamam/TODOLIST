<?php
session_start();
?>
<!DOCTYPE html>
<html lang="ar" dir="rtl">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>نظام إدارة المهام</title>
    <!-- Bootstrap RTL CSS -->
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.rtl.min.css">
    <!-- Font Awesome -->
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0/css/all.min.css">
    <!-- Custom CSS -->
    <link rel="stylesheet" href="assets/css/style.css">
</head>
<body>
    <!-- Navbar -->
    <nav class="navbar navbar-expand-lg navbar-dark bg-primary">
        <div class="container">
            <a class="navbar-brand" href="index.php">نظام إدارة المهام</a>
            <button class="navbar-toggler" type="button" data-bs-toggle="collapse" data-bs-target="#navbarNav">
                <span class="navbar-toggler-icon"></span>
            </button>
            <div class="collapse navbar-collapse" id="navbarNav">
                <ul class="navbar-nav me-auto">
                    <?php if(isset($_SESSION['user_id'])): ?>
                        <li class="nav-item">
                            <a class="nav-link" href="tasks.php">مهامي</a>
                        </li>
                        <li class="nav-item">
                            <a class="nav-link" href="profile.php">الملف الشخصي</a>
                        </li>
                        <li class="nav-item">
                            <a class="nav-link" href="logout.php">تسجيل الخروج</a>
                        </li>
                    <?php else: ?>
                        <li class="nav-item">
                            <a class="nav-link" href="login.php">تسجيل الدخول</a>
                        </li>
                        <li class="nav-item">
                            <a class="nav-link" href="register.php">التسجيل</a>
                        </li>
                    <?php endif; ?>
                </ul>
            </div>
        </div>
    </nav>

    <!-- Hero Section -->
    <div class="hero-section text-center py-5">
        <div class="container">
            <h1 class="display-4 mb-4">نظام إدارة المهام الذكي</h1>
            <p class="lead mb-4">نظام متكامل لإدارة مهامك اليومية بكل سهولة وفعالية</p>
            <?php if(!isset($_SESSION['user_id'])): ?>
                <div class="mt-4">
                    <a href="register.php" class="btn btn-primary btn-lg mx-2">سجل الآن</a>
                    <a href="login.php" class="btn btn-outline-primary btn-lg mx-2">تسجيل الدخول</a>
                </div>
            <?php endif; ?>
        </div>
    </div>

    <!-- Features Section -->
    <div class="features-section py-5">
        <div class="container">
            <div class="row">
                <div class="col-md-4 mb-4">
                    <div class="card h-100">
                        <div class="card-body text-center">
                            <i class="fas fa-tasks fa-3x mb-3 text-primary"></i>
                            <h3 class="card-title">إدارة المهام</h3>
                            <p class="card-text">إدارة مهامك بسهولة مع إمكانية تحديد الأولويات والمواعيد النهائية</p>
                        </div>
                    </div>
                </div>
                <div class="col-md-4 mb-4">
                    <div class="card h-100">
                        <div class="card-body text-center">
                            <i class="fas fa-star fa-3x mb-3 text-primary"></i>
                            <h3 class="card-title">المهام المفضلة</h3>
                            <p class="card-text">حدد المهام المهمة وأضفها إلى المفضلة للوصول السريع إليها</p>
                        </div>
                    </div>
                </div>
                <div class="col-md-4 mb-4">
                    <div class="card h-100">
                        <div class="card-body text-center">
                            <i class="fas fa-check-circle fa-3x mb-3 text-primary"></i>
                            <h3 class="card-title">تتبع الإنجاز</h3>
                            <p class="card-text">تتبع المهام المنجزة وقياس مستوى إنتاجيتك</p>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <!-- Footer -->
    <footer class="bg-dark text-light py-4">
        <div class="container text-center">
            <p>&copy; 2024 نظام إدارة المهام. جميع الحقوق محفوظة</p>
        </div>
    </footer>

    <!-- Scripts -->
    <script src="https://code.jquery.com/jquery-3.6.0.min.js"></script>
    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>
    <script src="assets/js/main.js"></script>
</body>
</html>
