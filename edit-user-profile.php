<?php
session_start();
include "config.php";
if (!isset($_SESSION['user_id'])) {
    header("Location: user-login.php");
    exit();
}
$user_id = $_SESSION['user_id'];
$message = "";
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $username = mysqli_real_escape_string($conn, $_POST['username'] ?? '');
    $email = mysqli_real_escape_string($conn, $_POST['email'] ?? '');
    $phone = mysqli_real_escape_string($conn, $_POST['phone'] ?? '');
    $address = mysqli_real_escape_string($conn, $_POST['address'] ?? '');
    $new_password = $_POST['password'] ?? '';
    $profile_picture = null;
    if (!empty($_FILES['profile_picture']['tmp_name'])) {
        $profile_picture = addslashes(file_get_contents($_FILES['profile_picture']['tmp_name']));
    }
    $updateQuery = "UPDATE users SET 
        username = '$username', 
        email = '$email', 
        phone = '$phone', 
        address = '$address'";
    if (!empty($new_password)) {
        $hashedPassword = password_hash($new_password, PASSWORD_DEFAULT);
        $updateQuery .= ", password = '$hashedPassword'";
    }
    if ($profile_picture) {
        $updateQuery .= ", profile_picture = '$profile_picture'";
    }
    $updateQuery .= " WHERE user_id = $user_id";
    if (mysqli_query($conn, $updateQuery)) {
        $message = "Profile updated successfully!";
    } else {
        $message = "Error updating profile: " . mysqli_error($conn);
    }
}
$userQuery = mysqli_query($conn, "SELECT * FROM users WHERE user_id = $user_id");
$user = mysqli_fetch_assoc($userQuery);
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Edit Profile</title>
    
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
        
        .edit-profile-card {
            max-width: 800px;
            margin: 0 auto;
            border-radius: 16px;
            overflow: hidden;
            box-shadow: var(--card-shadow);
            background-color: white;
        }
        
        .card-header {
            background: linear-gradient(135deg, var(--primary-color), var(--secondary-color));
            color: white;
            padding: 25px 30px;
            position: relative;
        }
        
        .card-title {
            font-weight: 600;
            margin: 0;
            font-size: 1.8rem;
        }
        
        .card-body {
            padding: 30px;
        }
        
        .profile-pic-container {
            position: relative;
            width: 150px;
            height: 150px;
            margin: 0 auto 25px;
            border-radius: 50%;
            overflow: hidden;
            border: 5px solid #e9ecef;
            box-shadow: 0 4px 8px rgba(0,0,0,0.1);
            transition: all 0.3s ease;
        }
        
        .profile-pic-container:hover {
            transform: scale(1.03);
            box-shadow: 0 6px 12px rgba(0,0,0,0.15);
        }
        
        .profile-pic {
            width: 100%;
            height: 100%;
            object-fit: cover;
        }
        
        .form-label {
            font-weight: 600;
            color: #495057;
            margin-bottom: 8px;
            font-size: 0.95rem;
        }
        
        .form-control, .form-select {
            border-radius: 8px;
            border: 1px solid #ced4da;
            padding: 12px 15px;
            transition: all 0.3s ease;
        }
        
        .form-control:focus, .form-select:focus {
            border-color: var(--primary-color);
            box-shadow: 0 0 0 0.25rem rgba(67, 97, 238, 0.25);
        }
        
        .btn-update {
            background: linear-gradient(135deg, var(--primary-color), var(--secondary-color));
            border: none;
            border-radius: 50px;
            padding: 12px 30px;
            font-weight: 500;
            color: white;
            transition: all 0.3s ease;
            box-shadow: 0 4px 15px rgba(67, 97, 238, 0.3);
        }
        
        .btn-update:hover {
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
        
        .file-upload-wrapper {
            position: relative;
            overflow: hidden;
            display: inline-block;
            cursor: pointer;
        }
        
        .file-upload-wrapper input[type=file] {
            position: absolute;
            left: 0;
            top: 0;
            opacity: 0;
            cursor: pointer;
            width: 100%;
            height: 100%;
        }
        
        .file-upload-label {
            display: inline-block;
            padding: 10px 20px;
            background-color: #f8f9fa;
            border: 2px dashed #dee2e6;
            border-radius: 8px;
            color: #6c757d;
            cursor: pointer;
            transition: all 0.3s ease;
            width: 100%;
            text-align: center;
        }
        
        .file-upload-wrapper:hover .file-upload-label {
            background-color: #e9ecef;
            border-color: var(--primary-color);
            color: var(--primary-color);
        }
        
        .alert {
            border-radius: 10px;
            border: none;
            padding: 15px 20px;
        }
        
        .alert-success {
            background-color: #d1e7dd;
            color: #0f5132;
        }
        
        .alert-danger {
            background-color: #f8d7da;
            color: #842029;
        }
        
        .form-section {
            margin-bottom: 25px;
        }
        
        .section-title {
            font-size: 1.2rem;
            font-weight: 600;
            color: var(--primary-color);
            margin-bottom: 20px;
            padding-bottom: 10px;
            border-bottom: 2px solid #e9ecef;
        }
        
        .password-strength {
            height: 5px;
            margin-top: 8px;
            border-radius: 5px;
            background-color: #e9ecef;
            overflow: hidden;
        }
        
        .password-strength-meter {
            height: 100%;
            width: 0;
            transition: width 0.3s ease, background-color 0.3s ease;
        }
        
        .strength-weak {
            background-color: #dc3545;
            width: 33%;
        }
        
        .strength-medium {
            background-color: #ffc107;
            width: 66%;
        }
        
        .strength-strong {
            background-color: #198754;
            width: 100%;
        }
        
        /* Responsive adjustments */
        @media (max-width: 768px) {
            .card-body {
                padding: 20px;
            }
            
            .profile-pic-container {
                width: 120px;
                height: 120px;
            }
        }
    </style>
</head>
<body>
    <div class="container">
        <div class="edit-profile-card">
            <!-- Card Header -->
            <div class="card-header">
                <a href="user-dashboard.php" class="btn btn-back position-absolute top-0 start-0 m-3">
                    <i class="bi bi-arrow-left me-2"></i> Dashboard
                </a>
                <h2 class="card-title">Edit Profile</h2>
            </div>
            
            <!-- Card Body -->
            <div class="card-body">
                <?php if (!empty($message)): ?>
                    <div class="alert <?php echo strpos($message, 'success') !== false ? 'alert-success' : 'alert-danger'; ?> alert-dismissible fade show" role="alert">
                        <?php echo $message; ?>
                        <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Close"></button>
                    </div>
                <?php endif; ?>
                
                <form method="POST" enctype="multipart/form-data">
                    <!-- Profile Picture Section -->
                    <div class="text-center mb-4">
                        <div class="profile-pic-container">
                            <?php if (!empty($user['profile_picture'])): ?>
                                <img src="data:image/jpeg;base64,<?php echo base64_encode($user['profile_picture']); ?>" alt="Profile Picture" class="profile-pic">
                            <?php else: ?>
                                <img src="https://ui-avatars.com/api/?name=<?php echo urlencode($user['username']); ?>&background=4361ee&color=fff&size=150" alt="Default Avatar" class="profile-pic">
                            <?php endif; ?>
                        </div>
                        
                        <div class="file-upload-wrapper mt-3">
                            <label for="profile_picture" class="file-upload-label">
                                <i class="bi bi-camera me-2"></i> Change Profile Picture
                            </label>
                            <input type="file" name="profile_picture" id="profile_picture" accept="image/*" />
                        </div>
                    </div>
                    
                    <!-- Personal Information Section -->
                    <div class="form-section">
                        <h3 class="section-title"><i class="bi bi-person-circle me-2"></i> Personal Information</h3>
                        
                        <div class="row">
                            <div class="col-md-6 mb-3">
                                <label for="username" class="form-label">Username</label>
                                <div class="input-group">
                                    <span class="input-group-text"><i class="bi bi-person"></i></span>
                                    <input type="text" class="form-control" id="username" name="username" value="<?php echo htmlspecialchars($user['username']); ?>" required>
                                </div>
                            </div>
                            
                            <div class="col-md-6 mb-3">
                                <label for="email" class="form-label">Email Address</label>
                                <div class="input-group">
                                    <span class="input-group-text"><i class="bi bi-envelope"></i></span>
                                    <input type="email" class="form-control" id="email" name="email" value="<?php echo htmlspecialchars($user['email']); ?>" required>
                                </div>
                            </div>
                        </div>
                        
                        <div class="row">
                            <div class="col-md-6 mb-3">
                                <label for="phone" class="form-label">Phone Number</label>
                                <div class="input-group">
                                    <span class="input-group-text"><i class="bi bi-telephone"></i></span>
                                    <input type="text" class="form-control" id="phone" name="phone" value="<?php echo htmlspecialchars($user['phone']); ?>">
                                </div>
                            </div>
                            
                            <div class="col-md-6 mb-3">
                                <label for="address" class="form-label">Address</label>
                                <div class="input-group">
                                    <span class="input-group-text"><i class="bi bi-geo-alt"></i></span>
                                    <input type="text" class="form-control" id="address" name="address" value="<?php echo htmlspecialchars($user['address']); ?>">
                                </div>
                            </div>
                        </div>
                    </div>
                    
                    <!-- Security Section -->
                    <div class="form-section">
                        <h3 class="section-title"><i class="bi bi-shield-lock me-2"></i> Security</h3>
                        
                        <div class="mb-3">
                            <label for="password" class="form-label">New Password <small class="text-muted">(leave blank to keep current)</small></label>
                            <div class="input-group">
                                <span class="input-group-text"><i class="bi bi-lock"></i></span>
                                <input type="password" class="form-control" id="password" name="password" placeholder="Enter new password">
                                <button class="btn btn-outline-secondary" type="button" id="togglePassword">
                                    <i class="bi bi-eye"></i>
                                </button>
                            </div>
                            <div class="password-strength">
                                <div class="password-strength-meter" id="passwordStrength"></div>
                            </div>
                            <small class="text-muted">Use 8 or more characters with a mix of letters, numbers & symbols</small>
                        </div>
                    </div>
                    
                    <div class="d-grid gap-2 d-md-flex justify-content-md-center mt-4">
                        <button type="submit" class="btn btn-update">
                            <i class="bi bi-check-circle me-2"></i> Update Profile
                        </button>
                    </div>
                </form>
            </div>
        </div>
    </div>

    <!-- Bootstrap JS Bundle -->
    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>
    
    <script>
        // Toggle password visibility
        document.getElementById('togglePassword').addEventListener('click', function() {
            const passwordInput = document.getElementById('password');
            const icon = this.querySelector('i');
            
            if (passwordInput.type === 'password') {
                passwordInput.type = 'text';
                icon.classList.remove('bi-eye');
                icon.classList.add('bi-eye-slash');
            } else {
                passwordInput.type = 'password';
                icon.classList.remove('bi-eye-slash');
                icon.classList.add('bi-eye');
            }
        });
        
        // Password strength indicator
        document.getElementById('password').addEventListener('input', function() {
            const password = this.value;
            const strengthMeter = document.getElementById('passwordStrength');
            
            // Remove existing strength classes
            strengthMeter.classList.remove('strength-weak', 'strength-medium', 'strength-strong');
            
            if (password.length === 0) {
                strengthMeter.style.width = '0';
                return;
            }
            
            // Simple password strength calculation
            let strength = 0;
            
            // Length check
            if (password.length >= 8) strength += 1;
            
            // Complexity checks
            if (password.match(/[a-z]+/)) strength += 1;
            if (password.match(/[A-Z]+/)) strength += 1;
            if (password.match(/[0-9]+/)) strength += 1;
            if (password.match(/[$@#&!]+/)) strength += 1;
            
            // Set strength meter
            if (strength <= 2) {
                strengthMeter.classList.add('strength-weak');
            } else if (strength <= 4) {
                strengthMeter.classList.add('strength-medium');
            } else {
                strengthMeter.classList.add('strength-strong');
            }
        });
        
        // Profile picture preview
        document.getElementById('profile_picture').addEventListener('change', function() {
            const file = this.files[0];
            if (file) {
                const reader = new FileReader();
                
                reader.onload = function(e) {
                    document.querySelector('.profile-pic').src = e.target.result;
                }
                
                reader.readAsDataURL(file);
            }
        });
    </script>
</body>
</html>