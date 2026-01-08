<?php
require_once 'includes/config.php';
// Fetch some sample programmes and lessons for showcase
$programmes = $pdo->query('SELECT * FROM courses LIMIT 3')->fetchAll(PDO::FETCH_ASSOC);
$lessons = $pdo->query('SELECT l.*, c.title AS course_title FROM lessons l JOIN courses c ON l.course_id = c.id LIMIT 3')->fetchAll(PDO::FETCH_ASSOC);
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Elimu Yetu | Student Progress Tracker</title>
    <link rel="stylesheet" href="assets/css/style.css">
    <link rel="stylesheet" href="assets/css/bootstrap-5.3.7-dist/css/bootstrap.min.css">
    <style>
        body {
            min-height: 100vh;
            background: linear-gradient(120deg, #e3f2fd 0%, #f8fafc 100%), url('assets/images/education_bg.jpg') center/cover no-repeat;
            overflow-x: hidden;
            width: 100%;
        }
        .user-header.fixed-top {
            top: 0 !important;
            left: 0;
            width: 100%;
            z-index: 1030;
        }
        .hero {
            padding: 120px 0 60px 0;
            text-align: center;
            color: #1976d2;
            background: rgba(255,255,255,0.85);
            border-radius: 18px;
            box-shadow: 0 4px 32px rgba(25, 118, 210, 0.08);
            margin-bottom: 32px;
            transition: transform 0.4s cubic-bezier(.4,0,.2,1), opacity 0.4s cubic-bezier(.4,0,.2,1);
    }
    @media (max-width: 768px) {
            .user-header.fixed-top {
                top: 0 !important;
            }
            .hero {
                padding: 70px 6px 32px 6px !important;
                font-size: 1.1rem;
            }
    }
    @media (max-width: 576px) {
            .user-header.fixed-top {
                top: 0 !important;
            }
            .hero {
                padding: 48px 6px 24px 6px !important;
                font-size: 1rem;
            }
        }
        .hero.scrolled {
            opacity: 0.5;
            transform: scale(0.96) translateY(-30px);
        }
        .hero h1 {
            font-size: 3rem;
            font-weight: 700;
            margin-bottom: 18px;
        }
        .hero .lead {
            font-size: 1.35rem;
            margin-bottom: 28px;
        }
        .login-btn {
            font-size: 1.3rem;
            padding: 16px 40px;
            border-radius: 30px;
            box-shadow: 0 2px 12px rgba(25,118,210,0.10);
            font-weight: 600;
        }
        .showcase-section {
            background: rgba(255,255,255,0.98);
            border-radius: 18px;
            box-shadow: 0 2px 16px rgba(0,0,0,0.07);
            margin-bottom: 40px;
            padding: 32px 18px;
        }
        .showcase-title {
            color: #1976d2;
            font-weight: bold;
            font-size: 1.35rem;
            margin-bottom: 18px;
        }
        .list-group-item {
            font-size: 1.08rem;
            display: flex;
            align-items: center;
            gap: 12px;
        }
        .list-group-item i {
            color: #1976d2;
            font-size: 1.3rem;
        }
        .footer {
            background: #1976d2;
            color: #fff;
            padding: 24px 0 12px 0;
            text-align: center;
            font-size: 1.1rem;
            margin-top: 48px;
            border-radius: 12px 12px 0 0;
        }
    </style>
</head>
<body>
    <nav class="navbar navbar-expand-lg navbar-light bg-white shadow-sm py-3 user-header fixed-top" id="mainHeader">
        <div class="container">
            <a class="navbar-brand fw-bold text-primary" href="index.php" style="font-size:1.7rem; letter-spacing:1px;">
                <img src="assets/images/logo.jpg" alt="" style="height:38px;vertical-align:middle;margin-right:10px;"> Elimu Yetu Development Organization
            </a>
            <!-- Hamburger button removed for small devices -->
            <div class="collapse navbar-collapse" id="mainNavbar">
                <ul class="navbar-nav ms-auto align-items-center">
                    <li class="nav-item">
                        <a class="btn btn-primary px-4 py-2" href="auth.php">
                            <i class="bi bi-person-circle"></i> Login / Register
                        </a>
                    </li>
                </ul>
            </div>
        </div>
    </nav>
    <div class="container">
        <div class="hero">
            <h1>Welcome to Elimu Yetu</h1>
            <p class="lead">Track your learning progress, earn achievements, and unlock your potential!</p>
            <a href="auth.php" class="btn btn-primary login-btn shadow">Get Started</a>
        </div>
        <div class="row showcase-section mb-5">
            <div class="col-md-6 mb-4 mb-md-0">
                    <h3 class="showcase-title"><i class="bi bi-journal-bookmark"></i> Featured Programmes</h3>
                    <div class="table-responsive">
                <ul class="list-group">
                    <?php foreach ($programmes as $course): ?>
                        <li class="list-group-item">
                            <i class="bi bi-bookmark-star"></i>
                            <div>
                                <strong><?php echo htmlspecialchars($course['title']); ?></strong><br>
                                <span class="text-muted"><?php echo htmlspecialchars($course['description']); ?></span>
                            </div>
                        </li>
                    <?php endforeach; ?>
                </ul>
                    </div>
            </div>
            <div class="col-md-6">
                <h3 class="showcase-title"><i class="bi bi-lightbulb"></i> Sample Lessons</h3>
                    <div class="table-responsive">
                <ul class="list-group">
                    <?php foreach ($lessons as $lesson): ?>
                        <li class="list-group-item">
                            <i class="bi bi-journal-text"></i>
                            <div>
                                <strong><?php echo htmlspecialchars($lesson['title']); ?></strong> <span class="badge bg-info ms-2"><?php echo htmlspecialchars($lesson['course_title']); ?></span><br>
                                <span class="text-muted"><?php echo isset($lesson['content']) ? htmlspecialchars(substr($lesson['content'],0,60)).'...' : 'No content.'; ?></span>
                            </div>
                        </li>
                    <?php endforeach; ?>
                </ul>
                    </div>
            </div>
        </div>
        <div class="text-center mb-5">
            <p class="text-muted">Join Elimu Yetu today and start your journey to success!</p>
        </div>
        <footer class="footer-social mt-5" style="width:100vw;position:relative;left:50%;transform:translateX(-50%);">
            <div class="d-flex justify-content-center align-items-center gap-3 py-2" style="background:#fff;border-radius:18px 18px 0 0;box-shadow:0 2px 12px rgba(25,118,210,0.07);min-height:44px;">
                <a href="mailto:info@elimu.org" target="_blank" rel="noopener" class="social-btn" style="background:#ea4335;color:#fff;"><i class="bi bi-envelope-fill"></i></a>
                <a href="https://instagram.com/" target="_blank" rel="noopener" class="social-btn" style="background:#e1306c;color:#fff;"><i class="bi bi-instagram"></i></a>
                <a href="https://youtube.com/" target="_blank" rel="noopener" class="social-btn" style="background:#ff0000;color:#fff;"><i class="bi bi-youtube"></i></a>
                <a href="https://tiktok.com/" target="_blank" rel="noopener" class="social-btn" style="background:#000;color:#fff;"><i class="bi bi-tiktok"></i></a>
            </div>
        </footer>
        <style>
            .social-btn {
                display: inline-flex;
                align-items: center;
                justify-content: center;
                width: 32px;
                height: 32px;
                border-radius: 50%;
                font-size: 1.1rem;
                margin: 0 4px;
                box-shadow: 0 2px 8px rgba(25,118,210,0.10);
                transition: transform 0.2s, box-shadow 0.2s, background 0.2s;
                border: none;
                text-decoration: none;
            }
            .social-btn:hover {
                transform: scale(1.12);
                box-shadow: 0 4px 16px rgba(25,118,210,0.18);
                background: #1976d2 !important;
                color: #fff !important;
            }
        </style>
    </div>
    <!-- Optionally download Bootstrap Icons for offline use, or remove if not needed -->
    <link rel="stylesheet" href="assets/css/bootstrap-5.3.7-dist/css/bootstrap.min.css">
    <script src="assets/css/bootstrap-5.3.7-dist/js/bootstrap.bundle.min.js"></script>
    <script>
    window.addEventListener('scroll', function() {
        var header = document.getElementById('mainHeader');
        var scrollY = window.scrollY;
        if (header) {
            if(scrollY > 0) {
                header.classList.add('fixed-top');
                header.classList.add('shadow');
            } else {
                header.classList.remove('fixed-top');
                header.classList.remove('shadow');
            }
        }
        // Removed hero animation for smooth scrolling
    });
    </script>
</body>
</html>
