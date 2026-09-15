<?php
// user-profile.php
session_start();
include "config.php";
if (!isset($_SESSION['user_id'])) {
    header("Location: user-login.php");
    exit();
}
$user_id = $_SESSION['user_id'];
$query = mysqli_query($conn, "SELECT username, email, phone, address, profile_picture FROM users WHERE user_id = $user_id");
if (!$query || mysqli_num_rows($query) === 0) {
    echo "<div class='alert alert-danger'>User not found.</div>";
    exit();
}
$user = mysqli_fetch_assoc($query);
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>User Profile</title>
    
    <!-- Bootstrap 5 CSS -->
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
    <!-- Bootstrap Icons -->
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.10.0/font/bootstrap-icons.css">
    
    <style>
        :root {
            --primary-color: #4361ee;
            --secondary-color: #3f37c9;
            --light-bg: #f8f9fa;
            --card-shadow: 0 4px 12px rgba(0,0,0,0.08);
        }
        
        body {
            background-color: var(--light-bg);
            font-family: 'Segoe UI', Roboto, 'Helvetica Neue', Arial, sans-serif;
            color: #333;
            min-height: 100vh;
            padding: 20px 0;
        }
        
        .profile-card {
            max-width: 700px;
            margin: 0 auto;
            border-radius: 16px;
            overflow: hidden;
            box-shadow: var(--card-shadow);
            transition: transform 0.3s ease;
        }
        
        .profile-card:hover {
            transform: translateY(-5px);
        }
        
        .profile-header {
            background: linear-gradient(135deg, var(--primary-color), var(--secondary-color));
            padding: 40px 20px;
            text-align: center;
            color: white;
            position: relative;
        }
        
        .profile-pic-container {
            position: relative;
            width: 150px;
            height: 150px;
            margin: 0 auto 20px;
        }
        
        .profile-pic {
            width: 100%;
            height: 100%;
            border-radius: 50%;
            object-fit: cover;
            border: 5px solid white;
            box-shadow: 0 4px 8px rgba(0,0,0,0.2);
            transition: transform 0.3s ease;
        }
        
        .profile-pic:hover {
            transform: scale(1.05);
        }
        
        .profile-body {
            padding: 30px;
            background-color: white;
        }
        
        .info-item {
            padding: 15px 0;
            border-bottom: 1px solid #eee;
            transition: background-color 0.2s ease;
        }
        
        .info-item:hover {
            background-color: #f8f9fa;
            margin: 0 -15px;
            padding-left: 15px;
            padding-right: 15px;
        }
        
        .info-item:last-child {
            border-bottom: none;
        }
        
        .info-label {
            font-weight: 600;
            color: #6c757d;
            font-size: 0.9rem;
            text-transform: uppercase;
            letter-spacing: 0.5px;
            margin-bottom: 5px;
        }
        
        .info-value {
            font-size: 1.1rem;
            color: #333;
        }
        
        .btn-edit {
            background: linear-gradient(135deg, var(--primary-color), var(--secondary-color));
            border: none;
            border-radius: 50px;
            padding: 12px 30px;
            font-weight: 500;
            transition: all 0.3s ease;
            box-shadow: 0 4px 15px rgba(67, 97, 238, 0.3);
        }
        
        .btn-edit:hover {
            transform: translateY(-2px);
            box-shadow: 0 6px 20px rgba(67, 97, 238, 0.4);
            background: linear-gradient(135deg, var(--secondary-color), var(--primary-color));
        }
        
        .btn-back {
            background-color: rgba(255,255,255,0.2);
            border: 1px solid rgba(255,255,255,0.3);
            color: white;
            border-radius: 50px;
            padding: 8px 20px;
            transition: all 0.3s ease;
        }
        
        .btn-back:hover {
            background-color: rgba(255,255,255,0.3);
            color: white;
            transform: translateY(-2px);
        }
        
        .profile-username {
            font-size: 1.8rem;
            font-weight: 600;
            margin-bottom: 5px;
        }
        
        .profile-meta {
            font-size: 0.9rem;
            opacity: 0.8;
        }
        
        /* Responsive adjustments */
        @media (max-width: 576px) {
            .profile-header {
                padding: 30px 15px;
            }
            
            .profile-pic-container {
                width: 120px;
                height: 120px;
            }
            
            .profile-body {
                padding: 20px;
            }
        }
    </style>
</head>
<body>
    <div class="container">
        <div class="profile-card">
            <!-- Profile Header -->
            <div class="profile-header">
                <a href="user-dashboard.php" class="btn btn-back position-absolute top-0 start-0 m-3">
                    <i class="bi bi-arrow-left me-2"></i> Dashboard
                </a>
                
                <div class="profile-pic-container">
                    <?php if ($user['profile_picture']) {
                        echo '<img class="profile-pic" src="data:image/jpeg;base64,' . base64_encode($user['profile_picture']) . '" alt="Profile Picture" />';
                    } else {
                        echo '<img class="profile-pic" src="https://ui-avatars.com/api/?name=' . urlencode($user['username']) . '&background=4361ee&color=fff&size=150" alt="Default Avatar" />';
                    } ?>
                </div>
                
                <h2 class="profile-username"><?php echo htmlspecialchars($user['username']); ?></h2>
                <p class="profile-meta">Member since <?php echo date('F Y', strtotime($user['created_at'] ?? 'now')); ?></p>
            </div>
            
            <!-- Profile Body -->
            <div class="profile-body">
                <div class="row">
                    <div class="col-md-6">
                        <div class="info-item">
                            <div class="info-label">
                                <i class="bi bi-envelope me-2"></i> Email Address
                            </div>
                            <div class="info-value"><?php echo htmlspecialchars($user['email']); ?></div>
                        </div>
                        
                        <div class="info-item">
                            <div class="info-label">
                                <i class="bi bi-telephone me-2"></i> Phone Number
                            </div>
                            <div class="info-value"><?php echo htmlspecialchars($user['phone'] ?: 'Not provided'); ?></div>
                        </div>
                    </div>
                    
                    <div class="col-md-6">
                        <div class="info-item">
                            <div class="info-label">
                                <i class="bi bi-geo-alt me-2"></i> Address
                            </div>
                            <div class="info-value"><?php echo htmlspecialchars($user['address'] ?: 'Not provided'); ?></div>
                        </div>
                        
                        <div class="info-item">
                            <div class="info-label">
                                <i class="bi bi-person-badge me-2"></i> Account Status
                            </div>
                            <div class="info-value">
                                <span class="badge bg-success">Active</span>
                            </div>
                        </div>
                    </div>
                </div>
                
                <div class="text-center mt-4">
                    <a href="edit-user-profile.php" class="btn btn-edit">
                        <i class="bi bi-pencil-square me-2"></i> Edit Profile
                    </a>
                </div>
            </div>
        </div>
    </div>

    <!-- Bootstrap JS Bundle -->
    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>
</body>
</html>