<?php
// Start session and connect to database
session_start();
include 'config.php'; // This sets up $mysqli

// Fetch user data (adjust query as needed)
$user_id = $_SESSION['user_id'] ?? 1; // Example: get user_id from session
$sql = "SELECT * FROM users WHERE id = $user_id";
$result = mysqli_query($mysqli, $sql);
$row = mysqli_fetch_assoc($result);
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <meta name="description" content="VidyaGuru College Profile Management - View, Edit, and Manage Your Profile Information">
    <meta name="keywords" content="profile, user management, VidyaGuru, student profile">
    <meta name="author" content="VidyaGuru College">
    <title>Profile Management - VidyaGuru College</title>
    <link rel="stylesheet" href="assets/fonts-local.css">
    <link rel="stylesheet" href="assets/fontawesome-local.css?v=2024-vertical-layout">
    <link rel="stylesheet" href="new.css?v=2024-vertical-layout">
    <link rel="stylesheet" href="assets/professional-enhancements.css">
    <link rel="stylesheet" href="assets/compact-design.css">
    <link rel="icon" type="image/svg+xml" href="cc_logo.svg">
</head>
<body>
    <!-- Header -->
    <header id="header">
        <div class="container header-container">
            <a href="index.html" class="logo">
                <div class="logo-icon">VG</div>
                <span>VidyaGuru</span>
            </a>
            <nav id="nav" role="navigation" aria-label="Main navigation">
                <ul>
                    <li><a href="index.html">Home</a></li>
                    <li><a href="index.html#programs">Programs</a></li>
                    <li><a href="index.html#features">Features</a></li>
                    <li><a href="index.html#testimonials">Reviews</a></li>
                    <li><a href="index.html#contact">Contact</a></li>
                    <li><a href="profile.php" class="active" aria-current="page">Profile</a></li>
                </ul>
            </nav>
            <button class="mobile-menu-btn" id="mobile-menu-btn" aria-label="Toggle mobile menu" aria-expanded="false">
                <i class="fas fa-bars"></i>
            </button>
        </div>
    </header>

    <!-- Profile Management Section -->
    <section class="profile-section">
        <div class="container">
            <!-- Profile Hero Header -->
            <div class="profile-hero">
                <div class="profile-hero-content">
                    <div class="profile-avatar">
                        <div class="avatar-circle" id="avatarCircle">
                            <img id="profileImage" src="<?php echo htmlspecialchars($row['profile_image']); ?>" alt="Profile Picture">
                            <i class="fas fa-user" id="defaultAvatar"></i>
                        </div>
                        <button class="avatar-edit-btn" id="avatarEditBtn" title="Change Profile Picture">
                            <i class="fas fa-camera"></i>
                        </button>
                        <input type="file" id="profilePictureInput" accept="image/*" style="display: none;">
                    </div>
                    <div class="profile-hero-info">
                        <h1 id="heroDisplayName">Welcome Back!</h1>
                        <p id="heroDisplayRole">Student Profile</p>
                        <div class="profile-stats">
                            <div class="stat-item">
                                <span class="stat-number" id="statDaysActive">0</span>
                                <span class="stat-label">Days Active</span>
                            </div>
                            <div class="stat-item">
                                <span class="stat-number" id="statCoursesEnrolled">0</span>
                                <span class="stat-label">Courses</span>
                            </div>
                            <div class="stat-item">
                                <span class="stat-number" id="statAchievements">0</span>
                                <span class="stat-label">Achievements</span>
                            </div>
                        </div>
                    </div>
                </div>
                <div class="profile-hero-actions">
                    <button id="quickEditBtn" class="btn btn-primary">
                        <i class="fas fa-edit"></i> Quick Edit
                    </button>
                    <button id="settingsBtn" class="btn btn-secondary">
                        <i class="fas fa-cog"></i> Settings
                    </button>
                </div>
            </div>
            <!-- ...existing code... -->
        </div>
    </section>
    <!-- ...existing code... -->
    <script src="auth.js?v=2024"></script>
    <script src="profile.js?v=2024"></script>
</body>
</html>
