<?php
require_once __DIR__ . '/../includes/init.php';
require_once __DIR__ . '/../includes/Mailer.php';

header('Content-Type: application/json');

// Ensure no stray output corrupts the JSON response
if (ob_get_length()) ob_clean();

$action = $_GET['action'] ?? $_POST['action'] ?? '';

if ($action === 'signup') {
    $firstname = sanitize($_POST['firstname'] ?? '');
    $lastname = sanitize($_POST['lastname'] ?? '');
    $business_name = sanitize($_POST['business-name'] ?? '');
    $email = sanitize($_POST['email'] ?? '');
    $password = $_POST['password'] ?? '';

    // Simple validation
    if (empty($firstname) || empty($lastname) || empty($business_name) || empty($email) || empty($password)) {
        echo json_encode(['success' => false, 'message' => 'All fields are required.']);
        exit;
    }

    // Verify CAPTCHA
    $captchaVerify = verifyTurnstile($_POST['cf-turnstile-response'] ?? null, $_SERVER['REMOTE_ADDR']);
    if (!$captchaVerify['success']) {
        echo json_encode(['success' => false, 'message' => $captchaVerify['message']]);
        exit;
    }

    // Check if email exists
    $db = db();
    $existing = $db->fetchOne("SELECT id FROM users WHERE email = ?", [$email]);
    if ($existing) {
        echo json_encode(['success' => false, 'message' => 'Email already registered.']);
        exit;
    }

    // Hash password
    $hashed_password = password_hash($password, PASSWORD_DEFAULT);

    // Insert user
    $userId = $db->insert('users', [
        'firstname' => $firstname,
        'lastname' => $lastname,
        'business_name' => $business_name,
        'email' => $email,
        'password' => $hashed_password,
        'role' => 'user',
        'status' => 'active',
        'status_updated_at' => date('Y-m-d H:i:s')
    ]);

    if ($userId) {
        // Notify admin of new registration
        addNotification("New Registration", "A new user account ({$firstname} {$lastname}) has been created.", 'info', 'admin');
        echo json_encode(['success' => true, 'message' => 'Registration successful!']);
        exit;
    } else {
        echo json_encode(['success' => false, 'message' => 'Registration failed. Please try again.']);
        exit;
    }
}

if ($action === 'login') {
    try {
        $email = sanitize($_POST['email'] ?? '');
        $password = $_POST['password'] ?? '';

        if (empty($email) || empty($password)) {
            if (ob_get_length()) ob_clean();
            echo json_encode(['success' => false, 'message' => 'Email and password are required.']);
            exit;
        }

        // Verify CAPTCHA
        $captchaVerify = verifyTurnstile($_POST['cf-turnstile-response'] ?? null, $_SERVER['REMOTE_ADDR']);
        if (!$captchaVerify['success']) {
            if (ob_get_length()) ob_clean();
            echo json_encode(['success' => false, 'message' => $captchaVerify['message']]);
            exit;
        }

        $db = db();
        // Check admins table first for administrative priority - Using case-insensitive search
        $user = $db->fetchOne("SELECT * FROM admins WHERE LOWER(email) = LOWER(?)", [$email]);
        $table = 'admins';

        if (!$user) {
            // Check users table - Using case-insensitive search
            $user = $db->fetchOne("SELECT * FROM users WHERE LOWER(email) = LOWER(?)", [$email]);
            $table = 'users';
        }

        if ($user && password_verify($password, $user['password'])) {
            // Essential: Normalize email from DB to avoid casing issues later
            $email = $user['email']; 
            // Check if 2FA is enabled (Default to 1/Enabled)
            $is2FAEnabled = (int)($user['two_factor_enabled'] ?? 1);

            if ($is2FAEnabled === 0) {
                // Bypass OTP if disabled
                $_SESSION['user_id'] = $user['id'];
                $_SESSION['user_email'] = $user['email'];
                $_SESSION['user_role'] = strtolower($user['role'] ?? 'user');
                $_SESSION['user_name'] = ($user['firstname'] ?? '') . ' ' . ($user['lastname'] ?? '');
                $_SESSION['business_name'] = $user['business_name'] ?? null;
                
                try {
                    $profile = $db->fetchOne("SELECT logo_path FROM business_profiles WHERE user_id = ?", [$user['id']]);
                    $_SESSION['business_logo'] = ($profile && isset($profile['logo_path'])) ? $profile['logo_path'] : null;
                } catch (Exception $e) {
                    $_SESSION['business_logo'] = null;
                }

                // Comprehensive list of administrative roles
                $adminRoles = ['admin', 'staff', 'superadmin', 'manager', 'administrator', 'coordinator', 'analyst'];
                $isAdminRole = in_array($_SESSION['user_role'], $adminRoles);
                $_SESSION['profile_completed'] = $isAdminRole ? true : (bool)($user['profile_completed'] ?? false);

                $targetPage = $isAdminRole ? 'dashboard/administrator/index.php?login=success' : 
                             ($_SESSION['profile_completed'] ? 'dashboard/users/index.php?login=success' : 'complete-profile.php');

                if (ob_get_length()) ob_clean();
                echo json_encode([
                    'success' => true, 
                    'message' => 'Login successful!',
                    'no_otp' => true,
                    'role' => $_SESSION['user_role'],
                    'is_admin' => $isAdminRole,
                    'profile_completed' => $_SESSION['profile_completed'],
                    'redirect_url' => $targetPage
                ]);
                exit;
            }

            // Generate OTP
            $otp = sprintf("%06d", mt_rand(0, 999999));
            $expires = date('Y-m-d H:i:s', strtotime('+10 minutes'));
            
            // Delete old OTPs for this email
            $db->delete('otp_verifications', 'email = ?', [$email]);
            
            // Store new OTP using MySQL NOW() for timezone consistency
            $db->query(
                "INSERT INTO otp_verifications (email, otp_code, expires_at) VALUES (?, ?, DATE_ADD(NOW(), INTERVAL 10 MINUTE))",
                [$email, $otp]
            );

            if (Mailer::sendOTP($email, $otp)) {
                if (ob_get_length()) ob_clean();
                echo json_encode([
                    'success' => true, 
                    'message' => 'OTP sent successfully!',
                    'email' => $email
                ]);
            } else {
                // For development, if mailing fails, you might still want to see the OTP 
                // but for production, this should be handled properly.
                if (ob_get_length()) ob_clean();
                echo json_encode([
                    'success' => true, 
                    'message' => 'Login success, but failed to send email. (Check server logs)',
                    'temp_otp' => $otp // Keeping for your testing until SMT is configured
                ]);
            }
            exit;
        } else {
            if (ob_get_length()) ob_clean();
            echo json_encode(['success' => false, 'message' => 'Invalid email or password.']);
            exit;
        }
    } catch (Exception $e) {
        if (ob_get_length()) ob_clean();
        echo json_encode(['success' => false, 'message' => 'Login error: ' . $e->getMessage()]);
        exit;
    }
}

if ($action === 'verify-otp') {
    try {
        $email = sanitize($_POST['email'] ?? '');
        $otp = sanitize($_POST['otp'] ?? '');

        if (empty($email) || empty($otp)) {
            echo json_encode(['success' => false, 'message' => 'Invalid request.']);
            exit;
        }

        $db = db();
        
        $verification = $db->fetchOne(
            "SELECT * FROM otp_verifications WHERE LOWER(email) = LOWER(?) AND otp_code = ? AND expires_at > NOW()",
            [$email, $otp]
        );

        if (!$verification) {
            echo json_encode(['success' => false, 'message' => 'Invalid or expired OTP.']);
            exit;
        }

        // Get user/admin data - Prioritize admins
        $user = $db->fetchOne("SELECT * FROM admins WHERE LOWER(email) = LOWER(?)", [$email]);
        $role = $user ? ($user['role'] ?? 'admin') : null;
        
        if (!$user) {
            $user = $db->fetchOne("SELECT * FROM users WHERE LOWER(email) = LOWER(?)", [$email]);
            $role = $user ? ($user['role'] ?? 'user') : null;
        }

        if (!$user) {
            echo json_encode(['success' => false, 'message' => 'User account could not be found.']);
            exit;
        }

        // Set session variables
        $_SESSION['user_id'] = $user['id'];
        $_SESSION['user_email'] = $user['email'];
        $_SESSION['user_role'] = strtolower($role);
        $_SESSION['user_name'] = ($user['firstname'] ?? '') . ' ' . ($user['lastname'] ?? '');
        $_SESSION['business_name'] = $user['business_name'] ?? null;
        
        try {
            $profile = $db->fetchOne("SELECT logo_path FROM business_profiles WHERE user_id = ?", [$user['id']]);
            $_SESSION['business_logo'] = ($profile && isset($profile['logo_path'])) ? $profile['logo_path'] : null;
        } catch (Exception $e) {
            $_SESSION['business_logo'] = null;
        }
        
        $adminRoles = ['admin', 'staff', 'superadmin', 'manager', 'administrator', 'coordinator', 'analyst'];
        $isAdminRole = in_array($_SESSION['user_role'], $adminRoles);
        $_SESSION['profile_completed'] = $isAdminRole ? true : (bool)($user['profile_completed'] ?? false);

        $targetPage = $isAdminRole ? 'dashboard/administrator/index.php?login=success' : 
                     ($_SESSION['profile_completed'] ? 'dashboard/users/index.php?login=success' : 'complete-profile.php');

        // Clean up OTP
        $db->delete('otp_verifications', 'email = ?', [$email]);

        // Clear buffer and send success
        if (ob_get_length()) ob_clean();
        echo json_encode([
            'success' => true, 
            'message' => 'Verification successful!',
            'role' => $_SESSION['user_role'],
            'is_admin' => $isAdminRole,
            'profile_completed' => $_SESSION['profile_completed'],
            'redirect_url' => $targetPage
        ]);
        exit;
        
    } catch (Exception $e) {
        if (ob_get_length()) ob_clean();
        echo json_encode(['success' => false, 'message' => 'System error: ' . $e->getMessage()]);
        exit;
    }
}


// Logout Action
if ($action === 'logout') {
    $_SESSION = array();
    if (ini_get("session.use_cookies")) {
        $params = session_get_cookie_params();
        setcookie(session_name(), '', time() - 42000,
            $params["path"], $params["domain"],
            $params["secure"], $params["httponly"]
        );
    }
    session_destroy();
    
    // Robust redirection to root login.php
    $php_self = $_SERVER['PHP_SELF'] ?? '/ajax/auth.php';
    $project_root = str_replace('/ajax/auth.php', '', $php_self);
    
    // Ensure we redirect to the correct path
    header("Location: " . $project_root . "/login.php");
    exit();
}

if ($action === 'complete-profile') {
    // Debug logging
    error_log("Complete Profile Action Triggered");
    error_log("POST Data: " . print_r($_POST, true));
    error_log("User ID: " . ($_SESSION['user_id'] ?? 'Not Set'));

    // Add aggressive error reporting for this block
    ini_set('display_errors', 0); // Don't output errors to HTML, handle them in JSON
    error_reporting(E_ALL);

    try {
        if (!isLoggedIn()) {
            echo json_encode(['success' => false, 'message' => 'Unauthorized.']);
            exit;
        }

        $userId = $_SESSION['user_id'];
        $db = db();

        // Start transaction for atomicity
        $db->beginTransaction();

        // 1. Update User Record (Business Name, Status)
        $business_name = sanitize($_POST['business_name'] ?? '');
        $db->update('users', [
            'business_name' => $business_name, 
            'profile_completed' => 1,
            'status' => 'active'
        ], 'id = :id', ['id' => $userId]);
        $_SESSION['business_name'] = $business_name;

        // 2. Insert Business Profile
        $sector = sanitize($_POST['sector'] ?? '');
        if ($sector === 'Others' && !empty($_POST['sector_other'])) {
            $sector = sanitize($_POST['sector_other']);
        }

        $db->insert('business_profiles', [
            'user_id' => $userId,
            'business_type' => sanitize($_POST['business_type'] ?? ''),
            'sector' => $sector,
            'address' => sanitize($_POST['business_address'] ?? ''),
            'registration_number' => sanitize($_POST['registration_number'] ?? ''),
            'year_started' => (int)($_POST['year_started'] ?? 0),
            'compliance_type' => sanitize($_POST['compliance_type'] ?? ''),
            'data_consent' => isset($_POST['privacy_consent']) ? 1 : 0
        ]);

        // 3. Insert Product
        $prodCategory = sanitize($_POST['product_category'] ?? '');
        if ($prodCategory === 'Others' && !empty($_POST['product_category_other'])) {
            $prodCategory = sanitize($_POST['product_category_other']);
        }

        $db->insert('user_products', [
            'user_id' => $userId,
            'product_name' => $business_name, // Use business name as default product name
            'category' => $prodCategory,
            'description' => sanitize($_POST['product_description'] ?? ''),
            'production_capacity' => sanitize($_POST['production_capacity'] ?? '') . ' kg'
        ]);
        
        $db->commit();
        $_SESSION['profile_completed'] = true;

        // Notify admin of profile completion
        addNotification("New MSME Profile", "{$_SESSION['user_name']} has completed their business profile for '{$business_name}'.", 'success', 'admin');

        // Prepare response with target redirect
        $adminRoles = ['admin', 'staff', 'superadmin', 'manager', 'administrator', 'coordinator', 'analyst'];
        $isAdminRole = in_array(strtolower($_SESSION['user_role'] ?? ''), $adminRoles);
        $targetPage = $isAdminRole ? 'dashboard/administrator/index.php?login=success' : 'dashboard/users/index.php?login=success';

        echo json_encode([
            'success' => true, 
            'message' => 'Profile completed successfully!',
            'is_admin' => $isAdminRole,
            'role' => $_SESSION['user_role'],
            'redirect_url' => $targetPage
        ]);
    } catch (Throwable $e) {
        if (isset($db)) {
            $db->rollback();
        }
        error_log("Profile Completion Error: " . $e->getMessage());
        echo json_encode(['success' => false, 'message' => 'Server Error: ' . $e->getMessage()]);
    }
}

/**
 * Get full user details for review (Admin only)
 */
if ($action === 'get-user-details-review') {
    if (!isLoggedIn() || !in_array($_SESSION['user_role'], ['admin', 'staff', 'superadmin', 'manager'])) {
        echo json_encode(['success' => false, 'message' => 'Unauthorized access.']);
        exit;
    }

    $id = (int)($_GET['userId'] ?? 0);
    if (!$id) {
        echo json_encode(['success' => false, 'message' => 'Invalid user ID.']);
        exit;
    }

    $db = db();
    $user = $db->fetchOne("SELECT id, firstname, lastname, email, role, status, business_name, created_at FROM users WHERE id = ?", [$id]);
    
    if (!$user) {
        echo json_encode(['success' => false, 'message' => 'User not found.']);
        exit;
    }

    $profile = $db->fetchOne("SELECT * FROM business_profiles WHERE user_id = ?", [$id]);
    $products = $db->fetchAll("SELECT * FROM user_products WHERE user_id = ?", [$id]);

    echo json_encode([
        'success' => true,
        'user' => $user,
        'profile' => $profile,
        'products' => $products
    ]);
    exit;
}

/**
 * Handle MSME Registry Updates (Admin only)
 */
if ($action === 'update-msme') {
    if (!isLoggedIn() || !in_array($_SESSION['user_role'], ['admin', 'staff', 'superadmin', 'manager'])) {
        echo json_encode(['success' => false, 'message' => 'Unauthorized access.']);
        exit;
    }

    $id = (int)($_POST['userId'] ?? 0);
    if (!$id) {
        echo json_encode(['success' => false, 'message' => 'Invalid user ID.']);
        exit;
    }

    $fullName = sanitize($_POST['fullName'] ?? '');
    $email = sanitize($_POST['email'] ?? '');
    $businessName = sanitize($_POST['businessName'] ?? '');
    $role = sanitize($_POST['role'] ?? 'user');
    $status = sanitize($_POST['status'] ?? 'active');

    if (empty($fullName) || empty($email)) {
        echo json_encode(['success' => false, 'message' => 'Name and Email are required.']);
        exit;
    }

    // Split name into first and last
    $parts = explode(' ', $fullName, 2);
    $firstname = $parts[0];
    $lastname = $parts[1] ?? '';

    try {
        $db = db();
        $db->beginTransaction();
        
        // 1. Update User
        $db->update('users', [
            'firstname' => $firstname,
            'lastname' => $lastname,
            'email' => $email,
            'business_name' => $businessName,
            'role' => $role,
            'status' => $status,
            'status_updated_at' => date('Y-m-d H:i:s')
        ], 'id = :target_id', ['target_id' => $id]);

        // 2. Update Business Profile
        // Check if profile exists
        $profile = $db->fetchOne("SELECT id FROM business_profiles WHERE user_id = ?", [$id]);
        
        $profileData = [
            'business_type' => sanitize($_POST['business_type'] ?? ''),
            'sector' => sanitize($_POST['sector'] ?? ''),
            'address' => sanitize($_POST['business_address'] ?? ''),
            'registration_number' => sanitize($_POST['registration_number'] ?? ''),
            'year_started' => (int)($_POST['year_started'] ?? 0),
            'compliance_type' => sanitize($_POST['compliance_type'] ?? '')
        ];

        if ($profile) {
            $db->update('business_profiles', $profileData, 'user_id = :uid', ['uid' => $id]);
        } else {
            // Create if missing (rare but possible)
            $profileData['user_id'] = $id;
            $db->insert('business_profiles', $profileData);
        }

        $db->commit();
        echo json_encode(['success' => true, 'message' => 'User and Business profile updated successfully!']);

    } catch (Exception $e) {
        $db->rollBack();
        echo json_encode(['success' => false, 'message' => 'Server Error: ' . $e->getMessage()]);
    }
}

/**
 * Simple status update for administrators (Dashboard review)
 */
if ($action === 'update-status-simple') {
    if (!isLoggedIn() || !in_array($_SESSION['user_role'], ['admin', 'staff', 'superadmin', 'manager'])) {
        echo json_encode(['success' => false, 'message' => 'Unauthorized access.']);
        exit;
    }

    $id = (int)($_POST['userId'] ?? 0);
    $status = sanitize($_POST['status'] ?? '');

    if (!$id || !$status) {
        echo json_encode(['success' => false, 'message' => 'Missing required data.']);
        exit;
    }

    try {
        $db = db();
        $success = $db->update('users', ['status' => $status], 'id = :id', ['id' => $id]);
        
        if ($success || $success === 0) {
            // Log for notification
            $user = $db->fetchOne("SELECT firstname, lastname FROM users WHERE id = ?", [$id]);
            $msg = "User application for " . $user['firstname'] . " " . $user['lastname'] . " has been " . $status . ".";
            addNotification("Application Updated", $msg, ($status == 'active' ? 'success' : 'warning'), 'admin');

            echo json_encode(['success' => true, 'message' => 'Status updated successfully.']);
        } else {
            echo json_encode(['success' => false, 'message' => 'Failed to update user status.']);
        }
    } catch (Exception $e) {
        echo json_encode(['success' => false, 'message' => 'Database Error: ' . $e->getMessage()]);
    }
    exit;
}

/**
 * Handle Forgot Password Request (Send Code)
 */
if ($action === 'forgot-password') {
    $email = sanitize($_POST['email'] ?? '');

    if (empty($email)) {
        echo json_encode(['success' => false, 'message' => 'Email address is required.']);
        exit;
    }

    $db = db();
    // Check if email exists
    $user = $db->fetchOne("SELECT id FROM admins WHERE email = ?", [$email]);
    if (!$user) {
        $user = $db->fetchOne("SELECT id FROM users WHERE email = ?", [$email]);
    }

    if ($user) {
        $otp = sprintf("%06d", mt_rand(0, 999999));
        $expires = date('Y-m-d H:i:s', strtotime('+10 minutes'));

        // Delete old OTPs
        $db->delete('otp_verifications', 'email = ?', [$email]);
        
        // Store new OTP using MySQL NOW() for timezone consistency
        $db->query(
            "INSERT INTO otp_verifications (email, otp_code, expires_at) VALUES (?, ?, DATE_ADD(NOW(), INTERVAL 10 MINUTE))",
            [$email, $otp]
        );

        if (Mailer::sendPasswordReset($email, $otp)) {
            echo json_encode(['success' => true, 'message' => 'Reset code sent to your email.']);
            exit;
        } else {
            echo json_encode(['success' => false, 'message' => 'Failed to send email. Please try again later.']);
            exit;
        }
    } else {
        echo json_encode(['success' => false, 'message' => 'Email address not found.']);
        exit;
    }
}

/**
 * Handle Reset Password (Verify Code & Change Password)
 */
if ($action === 'reset-password') {
    $email = sanitize($_POST['email'] ?? '');
    $otp = sanitize($_POST['otp'] ?? '');
    $password = $_POST['password'] ?? '';

    if (empty($email) || empty($otp) || empty($password)) {
        echo json_encode(['success' => false, 'message' => 'All fields are required.']);
        exit;
    }

    $db = db();
    
    // Verify OTP first
    $verification = $db->fetchOne(
        "SELECT * FROM otp_verifications WHERE email = ? AND otp_code = ? AND expires_at > NOW()",
        [$email, $otp]
    );

    if ($verification) {
        $hashed_password = password_hash($password, PASSWORD_DEFAULT);
        $updated = false;

        // Try updating admin first if email exists there
        $admin = $db->fetchOne("SELECT * FROM admins WHERE email = ?", [$email]);
        if ($admin) {
            $updated = $db->update('admins', ['password' => $hashed_password], 'email = ?', [$email]);
        } else {
            // Try updating user
            $user = $db->fetchOne("SELECT * FROM users WHERE email = ?", [$email]);
            if ($user) {
                $updated = $db->update('users', ['password' => $hashed_password], 'email = ?', [$email]);
            }
        }

        if ($updated) {
            // Clean up OTP
            $db->delete('otp_verifications', 'email = ?', [$email]);
            echo json_encode(['success' => true, 'message' => 'Password has been reset successfully.']);
            exit;
        } else {
            echo json_encode(['success' => false, 'message' => 'Failed to update password. Account not found.']);
            exit;
        }
    } else {
        echo json_encode(['success' => false, 'message' => 'Invalid or expired code.']);
        exit;
    }
}

/**
 * Handle Password Change from Profile
 */
if ($action === 'change-password') {
    if (!isLoggedIn()) {
        echo json_encode(['success' => false, 'message' => 'Unauthorized.']);
        exit;
    }

    $userId = $_SESSION['user_id'];
    $currentPassword = $_POST['current_password'] ?? '';
    $newPassword = $_POST['new_password'] ?? '';

    if (empty($currentPassword) || empty($newPassword)) {
        echo json_encode(['success' => false, 'message' => 'All fields are required.']);
        exit;
    }

    $db = db();
    $user = $db->fetchOne("SELECT password FROM users WHERE id = ?", [$userId]);

    if ($user && password_verify($currentPassword, $user['password'])) {
        $hashed = password_hash($newPassword, PASSWORD_DEFAULT);
        $db->update('users', ['password' => $hashed], 'id = :id', ['id' => $userId]);
        echo json_encode(['success' => true, 'message' => 'Password updated successfully!']);
        exit;
    } else {
        echo json_encode(['success' => false, 'message' => 'Incorrect current password.']);
        exit;
    }
}

/**
 * Handle 2FA Settings Update
 */
if ($action === 'update-2fa') {
    if (!isLoggedIn()) {
        echo json_encode(['success' => false, 'message' => 'Unauthorized.']);
        exit;
    }

    $userId = $_SESSION['user_id'];
    $enabled = (int)($_POST['enabled'] ?? 1);
    $db = db();

    try {
        $db->update('users', ['two_factor_enabled' => $enabled], 'id = :id', ['id' => $userId]);
        echo json_encode(['success' => true, 'message' => '2FA settings updated.']);
    } catch (Exception $e) {
        if (strpos($e->getMessage(), 'Unknown column') !== false || strpos($e->getMessage(), 'two_factor_enabled') !== false) {
            try {
                $db->query("ALTER TABLE users ADD COLUMN two_factor_enabled TINYINT(1) DEFAULT 1 AFTER status");
                $db->update('users', ['two_factor_enabled' => $enabled], 'id = :id', ['id' => $userId]);
                echo json_encode(['success' => true, 'message' => '2FA settings updated (Table repaired).']);
                exit;
            } catch (Exception $e2) {
                 echo json_encode(['success' => false, 'message' => 'Database error during repair: ' . $e2->getMessage()]);
                 exit;
            }
        }
        echo json_encode(['success' => false, 'message' => 'Database error: ' . $e->getMessage()]);
    }
}

/**
 * Handle Account Deactivation
 */
if ($action === 'deactivate-account') {
    if (!isLoggedIn()) {
        echo json_encode(['success' => false, 'message' => 'Unauthorized.']);
        exit;
    }

    $userId = $_SESSION['user_id'];
    $db = db();
    
    // Set status to deactivated instead of deleting
    $success = $db->update('users', ['status' => 'deactivated'], 'id = :id', ['id' => $userId]);
    
    if ($success) {
        session_destroy();
        echo json_encode(['success' => true, 'message' => 'Account deactivated.']);
    } else {
        echo json_encode(['success' => false, 'message' => 'Failed to deactivate account.']);
    }
}

/**
 * Update Profile Settings (Admin & User)
 */
if ($action === 'update-settings-profile') {
    if (!isLoggedIn()) {
        echo json_encode(['success' => false, 'message' => 'Unauthorized.']);
        exit;
    }

    $userId = $_SESSION['user_id'];
    $firstname = sanitize($_POST['firstname'] ?? '');
    $lastname = sanitize($_POST['lastname'] ?? '');
    $email = sanitize($_POST['email'] ?? '');

    if (empty($firstname) || empty($lastname) || empty($email)) {
        echo json_encode(['success' => false, 'message' => 'All fields are required.']);
        exit;
    }

    $db = db();
    try {
        $db->beginTransaction();

        // Check which table the user belongs to
        $isAdmin = $db->fetchOne("SELECT id FROM admins WHERE id = ?", [$userId]);
        $table = $isAdmin ? 'admins' : 'users';

        // Update Record
        $db->update($table, [
            'firstname' => $firstname,
            'lastname' => $lastname,
            'email' => $email
        ], 'id = :id', ['id' => $userId]);

        // Update Session
        $_SESSION['user_name'] = $firstname . ' ' . $lastname;
        $_SESSION['user_email'] = $email;

        $db->commit();
        echo json_encode(['success' => true, 'message' => 'Profile updated successfully!']);
    } catch (Exception $e) {
        if (isset($db)) $db->rollBack();
        echo json_encode(['success' => false, 'message' => 'Update Error: ' . $e->getMessage()]);
    }
    exit;
}
