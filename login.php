<?php require_once 'includes/init.php'; ?>
<!DOCTYPE html>
<html lang="en">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Login - LGU 3 Local Product & Export Development</title>
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
    <link href="https://fonts.googleapis.com/css2?family=Inter:wght@300;400;500;600;700;800&display=swap"
        rel="stylesheet">
    <link rel="stylesheet" href="css/auth.css?v=2.4">
</head>

<body>
    <div class="auth-container">
        <div class="auth-box">
            <div class="auth-header">
                <a href="index.php" class="back-link"><i class="fas fa-arrow-left"></i> Back to Home</a>
                <h2>Welcome Back</h2>
                <p>Sign in to your account to manage your applications</p>
            </div>

            <!-- Login Step -->
            <form id="login-form" class="auth-form">
                <div class="form-group">
                    <label for="email">Email Address</label>
                    <div class="input-icon">
                        <i class="fas fa-envelope"></i>
                        <input type="email" id="email" name="email" placeholder="Enter your email" required>
                    </div>
                </div>

                <div class="form-group password-group">
                    <label for="password">Password</label>
                    <div class="input-icon">
                        <i class="fas fa-lock"></i>
                        <input type="password" id="password" name="password" placeholder="••••••••" required>
                        <i class="fas fa-eye toggle-password" id="togglePassword"></i>
                    </div>
                </div>

                <div class="form-options login-options">
                    <a href="forgot-password.php" class="forgot-password">Forgot Password?</a>
                </div>

                <!-- <div class="captcha-container" id="captcha-box">
                    <div class="cf-turnstile" data-sitekey="<?php echo defined('TURNSTILE_SITE_KEY') ? TURNSTILE_SITE_KEY : ''; ?>" data-theme="dark"></div>
                </div> -->

                <button type="submit" class="btn-auth">Sign In</button>
            </form>

            <!-- OTP Step (Hidden by default) -->
            <div id="otp-section" class="auth-form" style="display: none;">
                <div class="otp-header">
                    <h3>Two-Step Verification</h3>
                    <p>Enter the 6-digit code sent to your email</p>
                </div>

                <div class="otp-inputs" id="otp-inputs">
                    <input type="text" maxlength="1" pattern="\d*" inputmode="numeric">
                    <input type="text" maxlength="1" pattern="\d*" inputmode="numeric">
                    <input type="text" maxlength="1" pattern="\d*" inputmode="numeric">
                    <input type="text" maxlength="1" pattern="\d*" inputmode="numeric">
                    <input type="text" maxlength="1" pattern="\d*" inputmode="numeric">
                    <input type="text" maxlength="1" pattern="\d*" inputmode="numeric">
                </div>

                <button id="verify-otp" class="btn-auth">Verify Code</button>

                <div class="resend-container">
                    <p>Didn't receive the code? <a href="#" id="resend-link">Resend in <span id="timer">60</span>s</a>
                    </p>
                </div>
            </div>

            <div class="auth-footer">
                <p>Don't have an account? <a href="signup.php">Create an Account</a></p>
            </div>
        </div>

        <div class="auth-visual">
            <div class="visual-content">
                <h2>Grow Your Business Locally</h2>
                <p>Join our community of local producers and start your export journey today with LGU 3 support.</p>
            </div>
        </div>
    </div>
    <script src="https://challenges.cloudflare.com/turnstile/v0/api.js" async defer></script>
    <script src="js/auth.js"></script>
</body>

</html>
