<?php require_once 'includes/init.php'; ?>
<!DOCTYPE html>
<html lang="en">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Sign Up - LGU 3 Local Product & Export Development</title>
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
    <link href="https://fonts.googleapis.com/css2?family=Inter:wght@300;400;500;600;700;800&display=swap"
        rel="stylesheet">
    <link rel="stylesheet" href="css/auth.css?v=2.3">
</head>

<body>
    <div class="auth-container">
        <div class="auth-visual signup-visual">
            <div class="visual-content">
                <h2>Empowering Local Producers</h2>
                <p>Register your MSME today and access exclusive government support, training, and export opportunities.
                </p>
            </div>
        </div>

        <div class="auth-box">
            <div class="auth-header">
                <a href="index.php" class="back-link"><i class="fas fa-arrow-left"></i> Back to Home</a>
                <h2>Create Account</h2>
                <p>Register to start your business development journey</p>
            </div>

            <form id="signup-form" class="auth-form">
                <div class="form-row">
                    <div class="form-group">
                        <label for="firstname">First Name</label>
                        <input type="text" id="firstname" name="firstname" placeholder="John" autocapitalize="words" required>
                    </div>
                    <div class="form-group">
                        <label for="lastname">Last Name</label>
                        <input type="text" id="lastname" name="lastname" placeholder="Doe" autocapitalize="words" required>
                    </div>
                </div>

                <div class="form-group">
                    <label for="business-name">Business/Company Name</label>
                    <div class="input-icon">
                        <i class="fas fa-building"></i>
                        <input type="text" id="business-name" name="business-name" placeholder="Your Business Name" required>
                    </div>
                </div>

                <div class="form-group">
                    <label for="email">Email Address</label>
                    <div class="input-icon">
                        <i class="fas fa-envelope"></i>
                        <input type="email" id="email" name="email" placeholder="example@email.com" required>
                    </div>
                </div>

                <div class="form-group password-group">
                    <label for="password">Password</label>
                    <div class="input-icon">
                        <i class="fas fa-lock"></i>
                        <input type="password" id="password" name="password" placeholder="••••••••" required>
                        <i class="fas fa-eye toggle-password" id="togglePassword"></i>
                    </div>
                    <!-- Password Requirements Tooltip -->
                    <div class="password-requirements" id="passwordRequirements">
                        <p class="req-title">Password must contain:</p>
                        <ul>
                            <li id="length" class="requirement"><i class="fas fa-circle"></i> At least 8 characters</li>
                            <li id="uppercase" class="requirement"><i class="fas fa-circle"></i> An uppercase letter
                            </li>
                            <li id="number" class="requirement"><i class="fas fa-circle"></i> A number</li>
                            <li id="special" class="requirement"><i class="fas fa-circle"></i> A special character</li>
                        </ul>
                    </div>
                </div>

                <div class="form-options">
                    <label class="terms-check">
                        <input type="checkbox" required> <span>I agree to the <a href="terms.php" target="_blank">Terms
                                & Conditions</a></span>
                    </label>
                </div>

                <div class="captcha-container" id="captcha-box">
                    <div class="cf-turnstile" data-sitekey="<?php echo defined('TURNSTILE_SITE_KEY') ? TURNSTILE_SITE_KEY : ''; ?>" data-theme="dark"></div>
                </div>

                <button type="submit" class="btn-auth">Create Account</button>
            </form>

            <div class="auth-footer">
                <p>Already have an account? <a href="login.php">Sign In</a></p>
            </div>
        </div>
    </div>
    <script src="https://challenges.cloudflare.com/turnstile/v0/api.js" async defer></script>
    <script src="js/auth.js"></script>
</body>

</html>
