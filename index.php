<?php
require_once 'includes/init.php';

// Fetch verification count
$db = db();
$userCount = $db->fetchOne("SELECT COUNT(*) as count FROM users")['count'];
$productCount = $db->fetchOne("SELECT COUNT(*) as count FROM user_products UP JOIN users U ON UP.user_id = U.id WHERE U.status = 'active'")['count'];

// Fetch Featured Products (Approved in Registry or Export Management)
$featuredProducts = $db->fetchAll("
    SELECT DISTINCT p.*, u.business_name 
    FROM user_products p 
    JOIN users u ON p.user_id = u.id 
    WHERE p.status = 'approved'
    ORDER BY RAND() 
    LIMIT 6
");


?>
<!DOCTYPE html>
<html lang="en">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>LGU 3 - Official Portal</title>
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
    <link href="https://fonts.googleapis.com/css2?family=Inter:wght@300;400;500;600;700;800&display=swap"
        rel="stylesheet">
    <link rel="stylesheet" href="css/landing.css?v=2.0">
    <script src="https://challenges.cloudflare.com/turnstile/v0/api.js" async defer></script>
</head>

<body>
    <!-- Navigation -->
    <nav class="navbar">
        <div class="container">
            <div class="logo">
                <img src="images/logo.png" alt="PH Logo">
                <div class="logo-text">
                    <span class="lgu-name">Local Government Unit 3</span>
                    <span class="lgu-sub">Local Product & Export Development</span>
                </div>
            </div>
            <div class="nav-right">
                <ul class="nav-links">
                    <li><a href="#home">Home</a></li>
                    <li><a href="#services">Services</a></li>

                    <li><a href="#how-it-works">How It Works</a></li>
                    <li><a href="#products">Featured Products</a></li>
                </ul>
                <div class="nav-btns">
                    <a href="login.php" class="btn-nav-login">Login</a>
                    <a href="signup.php" class="btn-nav-signup">Sign Up</a>
                </div>
            </div>
        </div>
    </nav>

    <!-- Hero Section -->
    <header id="home" class="hero">
        <div class="container">
            <div class="hero-content">
                <span class="badge">Welcome to Local Government Unit 3 Official Portal</span>
                <h1>Supporting Local Products <span class="highlight"> & Export Growth</span></h1>
                <p>Empowering MSMEs and local producers through digitalization. Access packaging support, market
                    matching, and export assistance to scale your business globally.</p>
                <div class="hero-btns">
                    <a href="#services" class="primary-btn">Explore Services</a>
                    <a href="#how-it-works" class="secondary-btn"><i class="fas fa-arrow-circle-right"></i> Get Started
                        Guide</a>
                </div>
            </div>
            <div class="hero-image">
                <div class="floating-card c1">
                    <i class="fas fa-check-circle"></i>
                    <span><?php echo number_format($productCount); ?> Approved Products</span>
                </div>
                <div class="floating-card c2">
                    <i class="fas fa-users"></i>
                    <span><?php echo number_format($userCount); ?> Registered Users</span>
                </div>
                <img src="https://images.unsplash.com/photo-1573164067507-40616da10c71?q=80&w=2070&auto=format&fit=crop"
                    alt="Digital PH" class="main-img">
            </div>
        </div>
    </header>

    <!-- Services Section -->
    <section id="services" class="services">
        <div class="container">
            <div class="section-header">
                <h2>Online Public Services</h2>
                <p>Skip the lines and process your documents from the comfort of your home.</p>
            </div>
            <div class="services-grid">
                <div class="service-card">
                    <div class="icon-box blue"><i class="fas fa-store"></i></div>
                    <h3>Business & Product Registration</h3>
                    <p>Get help with packaging, quality, and branding for your local products.</p>
                    <a href="#">Learn More <i class="fas fa-arrow-right"></i></a>
                </div>
                <div class="service-card">
                    <div class="icon-box red"><i class="fas fa-chalkboard-teacher"></i></div>
                    <h3>Training & Workshops</h3>
                    <p>Apply for skills development and comprehensive business trainings.</p>
                    <a href="#">View Schedule <i class="fas fa-arrow-right"></i></a>
                </div>
                <div class="service-card">
                    <div class="icon-box yellow"><i class="fas fa-bullhorn"></i></div>
                    <h3>Market Access & Promotion</h3>
                    <p>Join local trade fairs and connect with potential buyers through matching.</p>
                    <a href="#">Join Now <i class="fas fa-arrow-right"></i></a>
                </div>
                <div class="service-card">
                    <div class="icon-box green"><i class="fas fa-ship"></i></div>
                    <h3>Export Assistance</h3>
                    <p>Prepare your business and apply for global export opportunities.</p>
                    <a href="#">Start Exporting <i class="fas fa-arrow-right"></i></a>
                </div>
                <div class="service-card">
                    <div class="icon-box blue"><i class="fas fa-hand-holding-usd"></i></div>
                    <h3>Incentives & Support</h3>
                    <p>Requests grants, financial aid, and specialized business assistance.</p>
                    <a href="#">Check Eligibility <i class="fas fa-arrow-right"></i></a>
                </div>
                <div class="service-card">
                    <div class="icon-box red"><i class="fas fa-book-open"></i></div>
                    <h3>Information & Resources</h3>
                    <p>Access official guides, government policies, and program updates.</p>
                    <a href="#">Read Guides <i class="fas fa-arrow-right"></i></a>
                </div>
                <div class="service-card">
                    <div class="icon-box yellow"><i class="fas fa-tasks"></i></div>
                    <h3>Application Tracking</h3>
                    <p>Monitor the real-time status of your requests and pending applications.</p>
                    <a href="index.php">Track Now <i class="fas fa-arrow-right"></i></a>
                </div>
                <div class="service-card">
                    <div class="icon-box green"><i class="fas fa-bell"></i></div>
                    <h3>Notifications & Alerts</h3>
                    <p>Receive timely system notifications and program-specific updates.</p>
                    <a href="#">Configure Alerts <i class="fas fa-arrow-right"></i></a>
                </div>
                <div class="service-card">
                    <div class="icon-box blue"><i class="fas fa-headset"></i></div>
                    <h3>Feedback & Helpdesk</h3>
                    <p>Submit your inquiries and get support from our dedicated helpdesk.</p>
                    <a href="#">Get Support <i class="fas fa-arrow-right"></i></a>
                </div>
            </div>
        </div>
    </section>



    <!-- How It Works Section -->
    <section id="how-it-works" class="how-it-works">
        <div class="container">
            <div class="section-header">
                <h2>How It Works</h2>
                <p>Get started with the Local Product & Export Development program in four easy steps.</p>
            </div>
            <div class="steps-container">
                <div class="step">
                    <div class="step-num">01</div>
                    <div class="step-content">
                        <h3>Create Your Account</h3>
                        <p>Register as a producer or MSME to access our full suite of digital government services.</p>
                    </div>
                </div>
                <div class="step">
                    <div class="step-num">02</div>
                    <div class="step-content">
                        <h3>Submit Your Requirements</h3>
                        <p>Upload your product details and business documents for verification and diagnostic
                            assessment.</p>
                    </div>
                </div>
                <div class="step">
                    <div class="step-num">03</div>
                    <div class="step-content">
                        <h3>Avail Development Programs</h3>
                        <p>Participate in workshops, get packaging labels, or apply for export matching opportunities.
                        </p>
                    </div>
                </div>
                <div class="step">
                    <div class="step-num">04</div>
                    <div class="step-content">
                        <h3>Scale and Export</h3>
                        <p>Grow your local presence and start reaching international markets with our continuous
                            support.</p>
                    </div>
                </div>
            </div>
        </div>
    </section>

    <!-- Featured Products Section -->
    <section id="products" class="products">
        <div class="container">
            <div class="section-header">
                <h2>Featured Local Products</h2>
                <p>Discover the finest products from our LGU, verified and ready for the global market.</p>
            </div>

            <!-- Search and Filter Bar -->
            <div class="product-filters">
                <div class="search-box">
                    <i class="fas fa-search"></i>
                    <input type="text" id="product-search" placeholder="Search by name or business...">
                </div>
                <div class="filter-controls">
                    <select id="category-filter">
                        <option value="">All Categories</option>
                        <option value="Food & Beverages">Food & Beverages</option>
                        <option value="Arts & Crafts">Arts & Crafts</option>
                        <option value="Home & Living">Home & Living</option>
                        <option value="Fashion & Apparels">Fashion & Apparels</option>
                        <option value="Health & Wellness">Health & Wellness</option>
                        <option value="Cosmetics">Cosmetics</option>
                    </select>
                </div>
            </div>

            <div class="products-grid" id="featured-products-grid">
                <?php if (empty($featuredProducts)): ?>
                    <div class="no-products-msg"
                        style="grid-column: 1 / -1; text-align: center; padding: 60px; background: var(--bg-dark-alt); border-radius: 20px;">
                        <i class="fas fa-box-open"
                            style="font-size: 48px; color: var(--text-muted); opacity: 0.3; margin-bottom: 20px;"></i>
                        <p style="color: var(--text-muted);">No featured products available at the moment. Check back soon!
                        </p>
                    </div>
                <?php else: ?>
                    <?php foreach ($featuredProducts as $product):
                        $images = !empty($product['product_images']) ? json_decode($product['product_images'], true) : [];
                        $mainImage = !empty($images) ? $images[0] : 'https://images.unsplash.com/photo-1586201375761-83865001e31c?q=80&w=2070&auto=format&fit=crop';
                        ?>
                        <div class="product-card">
                            <div class="product-thumb">
                                <img src="<?php echo htmlspecialchars($mainImage); ?>"
                                    alt="<?php echo htmlspecialchars($product['product_name']); ?>">
                                <div class="product-badge">Verified</div>
                            </div>
                            <div class="product-info">
                                <span class="business-name"><?php echo htmlspecialchars($product['business_name']); ?></span>
                                <h3><?php echo htmlspecialchars($product['product_name']); ?></h3>
                                <p class="product-desc">
                                    <?php echo htmlspecialchars(mb_strimwidth($product['description'] ?? '', 0, 80, "...")); ?>
                                </p>
                                <div class="product-footer">
                                    <span class="price">₱<?php echo number_format($product['srp'], 2); ?></span>
                                    <a href="javascript:void(0)" class="view-btn open-product-modal"
                                        data-product='<?php echo htmlspecialchars(json_encode($product)); ?>'>View Details</a>
                                </div>
                            </div>
                        </div>
                    <?php endforeach; ?>
                <?php endif; ?>
            </div>

            <div class="show-more-container">
                <button id="show-more-products" class="btn-show-more">
                    <i class="fas fa-plus-circle"></i> Show More Products
                </button>
            </div>
        </div>
    </section>

    <!-- Footer -->
    <footer class="footer">
        <div class="container">
            <div class="footer-grid">
                <div class="footer-brand">
                    <h3>Local Government Unit 3</h3>
                    <p>Official Government Portal of Local Government Unit 3, Republic of the Philippines.</p>
                </div>
                <div class="footer-links">
                    <h4>Quick Links</h4>
                    <ul>
                        <li><a href="javascript:void(0)" id="privacy-link">Privacy Policy</a></li>
                        <li><a href="javascript:void(0)" id="terms-link">Terms of Service</a></li>
                    </ul>
                </div>
                <div class="footer-contact">
                    <h4>Contact Us</h4>
                    <p><i class="fas fa-map-marker-alt"></i> Quezon City, Philippines</p>
                    <p><i class="fas fa-phone"></i> (123) 456-7890</p>
                    <p><i class="fas fa-envelope"></i> localproductexportdevelopment@gmail.com</p>
                </div>
            </div>
            <div class="footer-bottom">
                <p>&copy; 2024 LGU 3 Republic of the Philippines. All Rights Reserved.</p>
                <div class="socials">
                    <a href="#"><i class="fab fa-facebook"></i></a>
                    <a href="#"><i class="fab fa-twitter"></i></a>
                    <a href="#"><i class="fab fa-instagram"></i></a>
                </div>
            </div>
        </div>
    </footer>

    <!-- Modals -->
    <div id="product-details-modal" class="modal">
        <div class="modal-content product-modal-content">
            <span class="close-modal" data-modal="product-details-modal">&times;</span>
            <div class="product-modal-body">
                <div class="product-gallery">
                    <a id="modal-image-link" href="" target="_blank"
                        style="display: block; width: 100%; height: 100%; cursor: zoom-in;">
                        <img id="modal-product-image" src="" alt="Product Image">
                    </a>
                </div>
                <div class="product-details-info" style="max-height: 70vh; overflow-y: auto; padding-right: 10px;">
                    <span id="modal-business-name" class="business-name"></span>
                    <h2 id="modal-product-name"></h2>
                    <div id="modal-product-price" class="price"
                        style="margin-bottom: 20px; font-size: 28px; color: #fff;"></div>

                    <div class="details-section">
                        <h3 style="color: var(--primary-color); margin-bottom: 10px;">Description</h3>
                        <p id="modal-product-description"
                            style="color: var(--text-muted); font-size: 14px; margin-bottom: 20px;"></p>
                    </div>

                    <div class="details-grid">
                        <div class="detail-item">
                            <label
                                style="display: block; font-size: 11px; text-transform: uppercase; color: var(--primary-color); font-weight: 700;">Category</label>
                            <span id="modal-product-category" style="color: #fff; font-size: 15px;"></span>
                        </div>
                        <div class="detail-item">
                            <label
                                style="display: block; font-size: 11px; text-transform: uppercase; color: var(--primary-color); font-weight: 700;">Ingredients</label>
                            <span id="modal-product-ingredients" style="color: #fff; font-size: 15px;"></span>
                        </div>
                        <div class="detail-item">
                            <label
                                style="display: block; font-size: 11px; text-transform: uppercase; color: var(--primary-color); font-weight: 700;">Shelf
                                Life</label>
                            <span id="modal-product-shelf-life" style="color: #fff; font-size: 15px;"></span>
                        </div>
                        <div class="detail-item">
                            <label
                                style="display: block; font-size: 11px; text-transform: uppercase; color: var(--primary-color); font-weight: 700;">Net
                                Weight</label>
                            <span id="modal-product-net-weight" style="color: #fff; font-size: 15px;"></span>
                        </div>
                        <div class="detail-item">
                            <label
                                style="display: block; font-size: 11px; text-transform: uppercase; color: var(--primary-color); font-weight: 700;">Available
                                Volume</label>
                            <span id="modal-product-volume" style="color: #fff; font-size: 15px;"></span>
                        </div>
                        <div class="detail-item">
                            <label
                                style="display: block; font-size: 11px; text-transform: uppercase; color: var(--primary-color); font-weight: 700;">Market</label>
                            <span id="modal-product-market" style="color: #fff; font-size: 15px;"></span>
                        </div>
                    </div>

                    <div class="product-inquiry-section"
                        style="margin-top: 30px; border-top: 1px solid var(--border-color); padding-top: 25px;">
                        <h3 style="color: var(--primary-color); margin-bottom: 15px; font-size: 18px;">Interested in
                            this product?</h3>
                        <p style="color: var(--text-muted); font-size: 13px; margin-bottom: 20px;">Send a direct inquiry
                            to the producer below.</p>

                        <form id="public-inquiry-form" class="inquiry-form">
                            <input type="hidden" id="inquiry-product-id" name="product_id">
                            <div class="form-grid"
                                style="display: grid; grid-template-columns: 1fr 1fr; gap: 15px; margin-bottom: 15px;">
                                <div class="form-group">
                                    <input type="text" name="name" placeholder="Your Name" required
                                        style="width: 100%; padding: 12px; border-radius: 8px; background: rgba(255,255,255,0.05); border: 1px solid var(--border-color); color: #fff;">
                                </div>
                                <div class="form-group">
                                    <input type="email" name="email" placeholder="Your Email" required
                                        style="width: 100%; padding: 12px; border-radius: 8px; background: rgba(255,255,255,0.05); border: 1px solid var(--border-color); color: #fff;">
                                </div>
                            </div>
                            <div class="form-group" style="margin-bottom: 15px;">
                                <textarea name="message"
                                    placeholder="I am interested in this product. Please provide more details on bulk ordering..."
                                    required
                                    style="width: 100%; padding: 12px; border-radius: 8px; background: rgba(255,255,255,0.05); border: 1px solid var(--border-color); color: #fff; height: 100px; resize: none;"></textarea>
                            </div>

                            <div class="captcha-box">
                                <div class="cf-turnstile"
                                    data-sitekey="<?php echo defined('TURNSTILE_SITE_KEY') ? TURNSTILE_SITE_KEY : ''; ?>"
                                    data-theme="dark" data-size="normal"></div>
                            </div>

                            <button type="submit" class="primary-btn"
                                style="width: 100%; padding: 14px; border: none; border-radius: 8px; cursor: pointer; font-weight: 700;">
                                <i class="fas fa-paper-plane"></i> Send Inquiry
                            </button>
                        </form>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <div id="privacy-modal" class="modal">
        <div class="modal-content">
            <span class="close-modal" data-modal="privacy-modal">&times;</span>
            <h2>Privacy Policy</h2>
            <p class="effective-date">Effective Date: February 07, 2024</p>
            <div class="modal-body">
                <p>The Local Product and Export Development System (“the System”) is committed to protecting the privacy
                    and personal data of its users. This Privacy Policy explains how we collect, use, store, and protect
                    your information in compliance with applicable data privacy laws.</p>

                <h3>1. Information We Collect</h3>
                <ul>
                    <li>Personal information (name, contact details, position)</li>
                    <li>Business information (business name, address, registration details)</li>
                    <li>Product information and related documents</li>
                    <li>System usage data (logins, applications, submissions)</li>
                </ul>

                <h3>2. Purpose of Data Collection</h3>
                <p>Your information is collected to:</p>
                <ul>
                    <li>Register and manage user accounts</li>
                    <li>Process applications for programs, trainings, and assistance</li>
                    <li>Support local product and export development initiatives</li>
                    <li>Generate reports for planning, monitoring, and policy-making</li>
                    <li>Communicate updates, announcements, and notifications</li>
                </ul>

                <h3>3. Data Sharing and Disclosure</h3>
                <p>User information may be shared only with:</p>
                <ul>
                    <li>Authorized LGU personnel</li>
                    <li>Partner government agencies involved in economic development</li>
                    <li>Other parties as required by law</li>
                </ul>
                <p>The System does not sell or misuse personal data.</p>

                <h3>4. Data Protection and Security</h3>
                <p>We implement appropriate technical and organizational measures to protect your data against
                    unauthorized access, loss, or misuse. Access to information is limited to authorized personnel only.
                </p>

                <h3>5. Data Retention</h3>
                <p>User data is retained only for as long as necessary to fulfill the purposes of the System or as
                    required by law.</p>

                <h3>6. User Rights</h3>
                <p>Users have the right to:</p>
                <ul>
                    <li>Access and update their information</li>
                    <li>Request correction of inaccurate data</li>
                    <li>Inquire about how their data is used</li>
                </ul>

                <h3>7. Changes to This Policy</h3>
                <p>This Privacy Policy may be updated from time to time. Users will be informed of significant changes
                    through the System.</p>
            </div>
        </div>
    </div>

    <div id="terms-modal" class="modal">
        <div class="modal-content">
            <span class="close-modal" data-modal="terms-modal">&times;</span>
            <h2>Terms of Service</h2>
            <p class="effective-date">Effective Date: February 07, 2024</p>
            <div class="modal-body">
                <p>By accessing and using the Local Product and Export Development System, you agree to comply with the
                    following Terms of Service.</p>

                <h3>1. Use of the System</h3>
                <p>The System is intended to support MSMEs, producers, exporters, and stakeholders in local product and
                    export development. Users must provide accurate and truthful information at all times.</p>

                <h3>2. User Responsibilities</h3>
                <p>Users agree to:</p>
                <ul>
                    <li>Maintain the confidentiality of their login credentials</li>
                    <li>Use the System only for lawful and legitimate purposes</li>
                    <li>Submit accurate business and product information</li>
                    <li>Avoid misuse, unauthorized access, or harmful activities</li>
                </ul>

                <h3>3. Account Management</h3>
                <p>The LGU reserves the right to:</p>
                <ul>
                    <li>Verify, suspend, or deactivate accounts with false or misleading information</li>
                    <li>Deny access to users who violate these Terms</li>
                </ul>

                <h3>4. System Availability</h3>
                <p>The LGU aims to ensure continuous access to the System but does not guarantee uninterrupted or
                    error-free operation. Maintenance or technical issues may cause temporary downtime.</p>

                <h3>5. Intellectual Property</h3>
                <p>All content, logos, data structures, and system features are the property of the LGU unless otherwise
                    stated. Unauthorized copying or use is prohibited.</p>

                <h3>6. Limitation of Liability</h3>
                <p>The LGU shall not be liable for losses or damages arising from the use or inability to use the
                    System, except as provided by law.</p>

                <h3>7. Modifications</h3>
                <p>The LGU may update or modify these Terms of Service at any time. Continued use of the System
                    signifies acceptance of the updated terms.</p>

                <h3>8. Governing Law</h3>
                <p>These Terms shall be governed by and interpreted in accordance with applicable Philippine laws and
                    LGU policies.</p>
            </div>
        </div>
    </div>

    <!-- Image Preview Modal -->
    <div id="image-preview-modal" class="modal image-viewer-modal">
        <span class="close-modal preview-close" data-modal="image-preview-modal">&times;</span>
        <div class="preview-container">
            <img id="preview-image" src="" alt="Enlarged Product Image">
        </div>
    </div>


    <script src="js/news-api.js"></script>
    <script src="js/landing.js"></script>
</body>

</html>