<?php
require_once __DIR__ . '/../includes/init.php';

header('Content-Type: application/json');

if (ob_get_length()) ob_clean();

$action = $_GET['action'] ?? $_POST['action'] ?? '';

if ($action === 'submit_inquiry') {
    try {
        $name = sanitize($_POST['name'] ?? '');
        $email = sanitize($_POST['email'] ?? '');
        $message = sanitize($_POST['message'] ?? '');
        $productId = (int)($_POST['product_id'] ?? 0);

        if (empty($name) || empty($email) || empty($message) || !$productId) {
            echo json_encode(['success' => false, 'message' => 'All fields are required.']);
            exit;
        }

        if (!filter_var($email, FILTER_VALIDATE_EMAIL)) {
            echo json_encode(['success' => false, 'message' => 'Invalid email address.']);
            exit;
        }

        // Verify CAPTCHA (Turnstile) - DISABLED
        /*
        $captchaVerify = verifyTurnstile($_POST['cf-turnstile-response'] ?? null, $_SERVER['REMOTE_ADDR']);
        if (!$captchaVerify['success']) {
            echo json_encode(['success' => false, 'message' => $captchaVerify['message']]);
            exit;
        }
        */
        $captchaVerify = ['success' => true];

        $db = db();
        
        // Get producer ID from product
        $product = $db->fetchOne("SELECT user_id, product_name FROM user_products WHERE id = ?", [$productId]);
        if (!$product) {
            echo json_encode(['success' => false, 'message' => 'Product not found.']);
            exit;
        }
        $producerId = $product['user_id'];

        // Save to Database
        $inquiryId = $db->insert('inquiries', [
            'product_id' => $productId,
            'producer_id' => $producerId,
            'inquirer_name' => $name,
            'inquirer_email' => $email,
            'message' => $message
        ]);

        if ($inquiryId) {
            // Notify Producer
            addNotification(
                "New Product Inquiry", 
                "You have a new inquiry from {$name} ({$email}) regarding '{$product['product_name']}'. Check your email/inquiries for details.", 
                'info', 
                'user', 
                $producerId
            );

            echo json_encode([
                'success' => true, 
                'message' => 'Your inquiry has been sent successfully! The producer will contact you soon.'
            ]);
        } else {
            echo json_encode(['success' => false, 'message' => 'Failed to send inquiry. Please try again.']);
        }

    } catch (Exception $e) {
        echo json_encode(['success' => false, 'message' => 'Server Error: ' . $e->getMessage()]);
    }
    exit;
}

echo json_encode(['success' => false, 'message' => 'Invalid action.']);
exit;
