<?php
// ...existing code...
?>
<nav class="navbar navbar-expand-lg navbar-light bg-white shadow-sm py-3 user-header fixed-top w-100" id="mainHeader" style="left:0;top:0;">
    <div class="container-fluid px-2 px-sm-3">
        <a class="navbar-brand fw-bold text-primary" href="index.php" style="font-size:1.7rem; letter-spacing:1px;">
            <img src="assets/images/logo.jpg" alt="" style="height:38px;vertical-align:middle;margin-right:10px;"> Elimu Yetu Development Organization
        </a>
        <button class="navbar-toggler" type="button" data-bs-toggle="collapse" data-bs-target="#mainNavbar" aria-controls="mainNavbar" aria-expanded="false" aria-label="Toggle navigation">
            <span class="navbar-toggler-icon"></span>
        </button>
        <div class="collapse navbar-collapse" id="mainNavbar">
            <ul class="navbar-nav ms-auto align-items-center">
                <li class="nav-item"><a class="nav-link px-3" href="dashboard.php">Dashboard</a></li>
                <?php if (isset($_SESSION['admin_id'])): ?>
                <li class="nav-item"><a class="nav-link px-3" href="roles.php">User Roles</a></li>
                <?php endif; ?>
                <li class="nav-item"><a class="nav-link px-3" href="courses.php">Courses</a></li>
                <li class="nav-item"><a class="nav-link px-3" href="achievements.php">Achievements</a></li>
                <li class="nav-item"><a class="nav-link px-3" href="logout.php">Logout</a></li>
            </ul>
        </div>
    </div>
</nav>
<?php
// ...existing code...
?>