<?php
/**
 * Awalan Design.id - Website PHP Backend
 * Jasa Desain Grafis Profesional untuk UMKM
 */

// ===== KONFIGURASI DATABASE =====
define('DB_HOST', 'localhost');
define('DB_USER', 'root');
define('DB_PASS', '');
define('DB_NAME', 'awalan_design');
define('SITE_URL', 'http://localhost/awalan-design');

// ===== FUNGSI KONEKSI DATABASE =====
function getDBConnection() {
    $conn = new mysqli(DB_HOST, DB_USER, DB_PASS, DB_NAME);
    
    if ($conn->connect_error) {
        die("Koneksi database gagal: " . $conn->connect_error);
    }
    
    $conn->set_charset("utf8mb4");
    return $conn;
}

// ===== FUNGSI KEAMANAN =====
function sanitizeInput($input) {
    $input = trim($input);
    $input = stripslashes($input);
    $input = htmlspecialchars($input, ENT_QUOTES, 'UTF-8');
    return $input;
}

function validateEmail($email) {
    return filter_var($email, FILTER_VALIDATE_EMAIL);
}

function validatePhone($phone) {
    // Validasi nomor telepon Indonesia
    $phone = preg_replace('/[^0-9]/', '', $phone);
    return preg_match('/^(\+62|62|0)8[1-9][0-9]{6,9}$/', $phone);
}

// ===== FUNGSI UNTUK FORM KONTAK =====
function handleContactForm() {
    if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
        return ['success' => false, 'message' => 'Metode request tidak valid'];
    }
    
    // Ambil dan sanitasi data
    $name = sanitizeInput($_POST['name'] ?? '');
    $email = sanitizeInput($_POST['email'] ?? '');
    $phone = sanitizeInput($_POST['phone'] ?? '');
    $service = sanitizeInput($_POST['service'] ?? '');
    $message = sanitizeInput($_POST['message'] ?? '');
    
    // Validasi input
    $errors = [];
    
    if (empty($name)) {
        $errors[] = 'Nama harus diisi';
    }
    
    if (empty($phone) || !validatePhone($phone)) {
        $errors[] = 'Nomor telepon/WhatsApp tidak valid';
    }
    
    if (empty($service)) {
        $errors[] = 'Jenis layanan harus dipilih';
    }
    
    if (empty($message)) {
        $errors[] = 'Pesan harus diisi';
    }
    
    if (!empty($email) && !validateEmail($email)) {
        $errors[] = 'Format email tidak valid';
    }
    
    if (!empty($errors)) {
        return ['success' => false, 'message' => implode(', ', $errors)];
    }
    
    // Simpan ke database
    $conn = getDBConnection();
    
    $stmt = $conn->prepare("INSERT INTO contact_submissions (name, email, phone, service, message, created_at) VALUES (?, ?, ?, ?, ?, NOW())");
    $stmt->bind_param("sssss", $name, $email, $phone, $service, $message);
    
    if ($stmt->execute()) {
        // Kirim notifikasi ke admin
        sendAdminNotification($name, $email, $phone, $service, $message);
        
        // Kirim email konfirmasi ke user (jika email diisi)
        if (!empty($email)) {
            sendConfirmationEmail($email, $name);
        }
        
        return [
            'success' => true, 
            'message' => 'Pesan berhasil dikirim! Kami akan menghubungi Anda segera.',
            'data' => [
                'name' => $name,
                'phone' => $phone,
                'service' => $service
            ]
        ];
    } else {
        return ['success' => false, 'message' => 'Terjadi kesalahan saat menyimpan data: ' . $stmt->error];
    }
}

// ===== FUNGSI UNTUK NEWSLETTER =====
function handleNewsletterSubscription() {
    if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
        return ['success' => false, 'message' => 'Metode request tidak valid'];
    }
    
    $email = sanitizeInput($_POST['email'] ?? '');
    
    if (empty($email) || !validateEmail($email)) {
        return ['success' => false, 'message' => 'Email tidak valid'];
    }
    
    $conn = getDBConnection();
    
    // Cek apakah email sudah terdaftar
    $stmt = $conn->prepare("SELECT id FROM newsletter_subscribers WHERE email = ?");
    $stmt->bind_param("s", $email);
    $stmt->execute();
    $stmt->store_result();
    
    if ($stmt->num_rows > 0) {
        return ['success' => false, 'message' => 'Email sudah terdaftar'];
    }
    
    // Simpan subscriber baru
    $stmt = $conn->prepare("INSERT INTO newsletter_subscribers (email, subscribed_at) VALUES (?, NOW())");
    $stmt->bind_param("s", $email);
    
    if ($stmt->execute()) {
        // Kirim email welcome
        sendNewsletterWelcomeEmail($email);
        
        return ['success' => true, 'message' => 'Berhasil berlangganan newsletter!'];
    } else {
        return ['success' => false, 'message' => 'Terjadi kesalahan: ' . $stmt->error];
    }
}

// ===== FUNGSI UNTUK ADMIN PANEL =====
function getContactSubmissions($limit = 50) {
    $conn = getDBConnection();
    
    $query = "SELECT * FROM contact_submissions ORDER BY created_at DESC LIMIT ?";
    $stmt = $conn->prepare($query);
    $stmt->bind_param("i", $limit);
    $stmt->execute();
    
    $result = $stmt->get_result();
    $submissions = [];
    
    while ($row = $result->fetch_assoc()) {
        $submissions[] = $row;
    }
    
    return $submissions;
}

function getNewsletterSubscribers($limit = 100) {
    $conn = getDBConnection();
    
    $query = "SELECT * FROM newsletter_subscribers ORDER BY subscribed_at DESC LIMIT ?";
    $stmt = $conn->prepare($query);
    $stmt->bind_param("i", $limit);
    $stmt->execute();
    
    $result = $stmt->get_result();
    $subscribers = [];
    
    while ($row = $result->fetch_assoc()) {
        $subscribers[] = $row;
    }
    
    return $subscribers;
}

// ===== FUNGSI EMAIL =====
function sendAdminNotification($name, $email, $phone, $service, $message) {
    $to = "awalandesign.id@gmail.com";
    $subject = "Pesan Baru dari Website Awalan Design.id";
    
    $body = "
    <!DOCTYPE html>
    <html>
    <head>
        <style>
            body { font-family: Arial, sans-serif; line-height: 1.6; color: #333; }
            .container { max-width: 600px; margin: 0 auto; padding: 20px; }
            .header { background: linear-gradient(135deg, #2563eb 0%, #f59e0b 100%); color: white; padding: 20px; text-align: center; border-radius: 10px 10px 0 0; }
            .content { background: #f9fafb; padding: 30px; border-radius: 0 0 10px 10px; }
            .field { margin-bottom: 15px; }
            .label { font-weight: bold; color: #2563eb; }
            .value { color: #666; }
        </style>
    </head>
    <body>
        <div class='container'>
            <div class='header'>
                <h2>Pesan Baru dari Website</h2>
            </div>
            <div class='content'>
                <div class='field'>
                    <div class='label'>Nama:</div>
                    <div class='value'>$name</div>
                </div>
                <div class='field'>
                    <div class='label'>Email:</div>
                    <div class='value'>" . ($email ?: 'Tidak diisi') . "</div>
                </div>
                <div class='field'>
                    <div class='label'>WhatsApp:</div>
                    <div class='value'><a href='https://wa.me/$phone'>$phone</a></div>
                </div>
                <div class='field'>
                    <div class='label'>Layanan:</div>
                    <div class='value'>$service</div>
                </div>
                <div class='field'>
                    <div class='label'>Pesan:</div>
                    <div class='value'>$message</div>
                </div>
                <hr>
                <p><strong>Tanggal:</strong> " . date('d/m/Y H:i:s') . "</p>
                <p><a href='https://wa.me/$phone' style='background: #25D366; color: white; padding: 10px 20px; text-decoration: none; border-radius: 5px; display: inline-block; margin-top: 20px;'>Balas via WhatsApp</a></p>
            </div>
        </div>
    </body>
    </html>
    ";
    
    $headers = "MIME-Version: 1.0\r\n";
    $headers .= "Content-Type: text/html; charset=UTF-8\r\n";
    $headers .= "From: website@awalandesign.id\r\n";
    $headers .= "Reply-To: $email\r\n";
    
    return mail($to, $subject, $body, $headers);
}

function sendConfirmationEmail($to, $name) {
    $subject = "Terima Kasih Telah Menghubungi Awalan Design.id";
    
    $body = "
    <!DOCTYPE html>
    <html>
    <head>
        <style>
            body { font-family: Arial, sans-serif; line-height: 1.6; color: #333; }
            .container { max-width: 600px; margin: 0 auto; padding: 20px; }
            .header { background: linear-gradient(135deg, #2563eb 0%, #f59e0b 100%); color: white; padding: 30px; text-align: center; border-radius: 10px 10px 0 0; }
            .content { background: #f9fafb; padding: 30px; border-radius: 0 0 10px 10px; }
            .cta-button { background: #2563eb; color: white; padding: 12px 30px; text-decoration: none; border-radius: 5px; display: inline-block; margin: 20px 0; }
            .footer { margin-top: 30px; padding-top: 20px; border-top: 1px solid #ddd; color: #666; font-size: 14px; }
        </style>
    </head>
    <body>
        <div class='container'>
            <div class='header'>
                <h1>Terima Kasih, $name!</h1>
                <p>Pesan Anda telah kami terima</p>
            </div>
            <div class='content'>
                <h3>Halo $name,</h3>
                <p>Terima kasih telah menghubungi <strong>Awalan Design.id</strong>. Kami telah menerima pesan Anda dan akan segera menanggapinya dalam waktu 1x24 jam.</p>
                
                <h4>Yang bisa Anda lakukan selanjutnya:</h4>
                <ul>
                    <li>Tambahkan nomor kami di WhatsApp: <strong>0858-1505-6990</strong></li>
                    <li>Ikuti kami di media sosial untuk update terbaru</li>
                    <li>Lihat portofolio kami untuk inspirasi</li>
                </ul>
                
                <a href='https://wa.me/6285815056990' class='cta-button'>Chat via WhatsApp Sekarang</a>
                
                <p>Jika Anda memerlukan bantuan segera, silakan hubungi kami langsung melalui WhatsApp di atas.</p>
                
                <div class='footer'>
                    <p><strong>Awalan Design.id</strong><br>
                    Jasa Desain Grafis Profesional untuk UMKM<br>
                    WhatsApp: 0858-1505-6990<br>
                    Email: awalandesign.id@gmail.com</p>
                    <p style='font-size: 12px; color: #999;'>Email ini dikirim secara otomatis, mohon tidak membalas email ini.</p>
                </div>
            </div>
        </div>
    </body>
    </html>
    ";
    
    $headers = "MIME-Version: 1.0\r\n";
    $headers .= "Content-Type: text/html; charset=UTF-8\r\n";
    $headers .= "From: no-reply@awalandesign.id\r\n";
    $headers .= "Reply-To: awalandesign.id@gmail.com\r\n";
    
    return mail($to, $subject, $body, $headers);
}

function sendNewsletterWelcomeEmail($to) {
    $subject = "Selamat Bergabung dengan Newsletter Awalan Design.id";
    
    $body = "
    <!DOCTYPE html>
    <html>
    <head>
        <style>
            body { font-family: Arial, sans-serif; line-height: 1.6; color: #333; }
            .container { max-width: 600px; margin: 0 auto; padding: 20px; }
            .header { background: linear-gradient(135deg, #2563eb 0%, #f59e0b 100%); color: white; padding: 30px; text-align: center; border-radius: 10px 10px 0 0; }
            .content { background: #f9fafb; padding: 30px; border-radius: 0 0 10px 10px; }
            .benefits { background: white; padding: 20px; border-radius: 5px; margin: 20px 0; border-left: 4px solid #2563eb; }
            .footer { margin-top: 30px; padding-top: 20px; border-top: 1px solid #ddd; color: #666; font-size: 14px; }
        </style>
    </head>
    <body>
        <div class='container'>
            <div class='header'>
                <h1>Selamat Bergabung!</h1>
                <p>Anda telah berlangganan newsletter Awalan Design.id</p>
            </div>
            <div class='content'>
                <h3>Halo Subscriber,</h3>
                <p>Terima kasih telah bergabung dengan newsletter <strong>Awalan Design.id</strong>. Anda akan menerima:</p>
                
                <div class='benefits'>
                    <h4>💡 Apa yang akan Anda dapatkan:</h4>
                    <ul>
                        <li>Tips & trik desain grafis untuk UMKM</li>
                        <li>Promo dan diskon khusus subscriber</li>
                        <li>Update portofolio terbaru kami</li>
                        <li>Informasi workshop dan webinar gratis</li>
                        <li>Inspirasi desain terbaru</li>
                    </ul>
                </div>
                
                <p><strong>Jangan lewatkan promo spesial untuk subscriber newsletter!</strong> Kami memberikan diskon 10% untuk paket UMKM Premium bagi subscriber aktif.</p>
                
                <a href='" . SITE_URL . "/portfolio' style='background: #2563eb; color: white; padding: 12px 30px; text-decoration: none; border-radius: 5px; display: inline-block; margin: 20px 0;'>Lihat Portofolio Kami</a>
                
                <p>Ingin konsultasi desain? <a href='https://wa.me/6285815056990'>Hubungi kami via WhatsApp</a></p>
                
                <div class='footer'>
                    <p><strong>Awalan Design.id</strong><br>
                    Jasa Desain Grafis Profesional untuk UMKM<br>
                    WhatsApp: 0858-1505-6990<br>
                    Email: awalandesign.id@gmail.com</p>
                    <p style='font-size: 12px; color: #999;'>
                        <a href='" . SITE_URL . "/unsubscribe?email=$to' style='color: #666;'>Berhenti berlangganan</a> | 
                        Email ini dikirim secara otomatis
                    </p>
                </div>
            </div>
        </div>
    </body>
    </html>
    ";
    
    $headers = "MIME-Version: 1.0\r\n";
    $headers .= "Content-Type: text/html; charset=UTF-8\r\n";
    $headers .= "From: newsletter@awalandesign.id\r\n";
    $headers .= "Reply-To: awalandesign.id@gmail.com\r\n";
    
    return mail($to, $subject, $body, $headers);
}

// ===== API ENDPOINTS =====
function handleAPIRequest() {
    header('Content-Type: application/json');
    
    $action = $_GET['action'] ?? $_POST['action'] ?? '';
    
    switch ($action) {
        case 'contact':
            $response = handleContactForm();
            break;
            
        case 'newsletter':
            $response = handleNewsletterSubscription();
            break;
            
        case 'get_submissions':
            // Protected endpoint - require authentication
            if (!isAdminAuthenticated()) {
                $response = ['success' => false, 'message' => 'Unauthorized'];
                break;
            }
            $response = ['success' => true, 'data' => getContactSubmissions()];
            break;
            
        case 'get_subscribers':
            // Protected endpoint - require authentication
            if (!isAdminAuthenticated()) {
                $response = ['success' => false, 'message' => 'Unauthorized'];
                break;
            }
            $response = ['success' => true, 'data' => getNewsletterSubscribers()];
            break;
            
        default:
            $response = ['success' => false, 'message' => 'Action tidak valid'];
            break;
    }
    
    echo json_encode($response);
    exit;
}

// ===== ADMIN AUTHENTICATION =====
function isAdminAuthenticated() {
    session_start();
    return isset($_SESSION['admin_logged_in']) && $_SESSION['admin_logged_in'] === true;
}

function adminLogin($username, $password) {
    // In production, use secure password hashing
    $admin_username = 'admin';
    $admin_password_hash = password_hash('admin123', PASSWORD_DEFAULT);
    
    if ($username === $admin_username && password_verify($password, $admin_password_hash)) {
        session_start();
        $_SESSION['admin_logged_in'] = true;
        $_SESSION['admin_username'] = $username;
        return true;
    }
    
    return false;
}

function adminLogout() {
    session_start();
    session_destroy();
    return true;
}

// ===== FILE UPLOAD HANDLER =====
function handleFileUpload($file, $type = 'portfolio') {
    $allowedTypes = ['image/jpeg', 'image/png', 'image/gif', 'image/webp'];
    $maxSize = 5 * 1024 * 1024; // 5MB
    
    if ($file['error'] !== UPLOAD_ERR_OK) {
        return ['success' => false, 'message' => 'Error uploading file'];
    }
    
    if (!in_array($file['type'], $allowedTypes)) {
        return ['success' => false, 'message' => 'Tipe file tidak diizinkan'];
    }
    
    if ($file['size'] > $maxSize) {
        return ['success' => false, 'message' => 'File terlalu besar (max 5MB)'];
    }
    
    // Generate unique filename
    $extension = pathinfo($file['name'], PATHINFO_EXTENSION);
    $filename = uniqid() . '_' . date('Ymd') . '.' . $extension;
    $uploadPath = 'uploads/' . $type . '/' . $filename;
    
    if (!move_uploaded_file($file['tmp_name'], $uploadPath)) {
        return ['success' => false, 'message' => 'Gagal menyimpan file'];
    }
    
    // Save to database if needed
    if ($type === 'portfolio') {
        $conn = getDBConnection();
        $title = sanitizeInput($_POST['title'] ?? '');
        $category = sanitizeInput($_POST['category'] ?? '');
        $description = sanitizeInput($_POST['description'] ?? '');
        
        $stmt = $conn->prepare("INSERT INTO portfolio_items (title, category, description, image_path, created_at) VALUES (?, ?, ?, ?, NOW())");
        $stmt->bind_param("ssss", $title, $category, $description, $uploadPath);
        $stmt->execute();
    }
    
    return ['success' => true, 'filename' => $filename, 'path' => $uploadPath];
}

// ===== SITEMAP GENERATOR =====
function generateSitemap() {
    $pages = [
        '' => date('Y-m-d'),
        'services' => date('Y-m-d'),
        'portfolio' => date('Y-m-d'),
        'pricing' => date('Y-m-d'),
        'testimonials' => date('Y-m-d'),
        'contact' => date('Y-m-d')
    ];
    
    $sitemap = '<?xml version="1.0" encoding="UTF-8"?>' . "\n";
    $sitemap .= '<urlset xmlns="http://www.sitemaps.org/schemas/sitemap/0.9">' . "\n";
    
    foreach ($pages as $page => $lastmod) {
        $sitemap .= '  <url>' . "\n";
        $sitemap .= '    <loc>' . SITE_URL . '/' . $page . '</loc>' . "\n";
        $sitemap .= '    <lastmod>' . $lastmod . '</lastmod>' . "\n";
        $sitemap .= '    <changefreq>weekly</changefreq>' . "\n";
        $sitemap .= '    <priority>' . ($page === '' ? '1.0' : '0.8') . '</priority>' . "\n";
        $sitemap .= '  </url>' . "\n";
    }
    
    $sitemap .= '</urlset>';
    
    file_put_contents('sitemap.xml', $sitemap);
    return true;
}

// ===== DATABASE SETUP SCRIPT =====
function setupDatabase() {
    $conn = new mysqli(DB_HOST, DB_USER, DB_PASS);
    
    if ($conn->connect_error) {
        die("Koneksi database gagal: " . $conn->connect_error);
    }
    
    // Create database
    $sql = "CREATE DATABASE IF NOT EXISTS " . DB_NAME . " CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci";
    if ($conn->query($sql) === TRUE) {
        echo "Database created successfully<br>";
    } else {
        echo "Error creating database: " . $conn->error . "<br>";
    }
    
    $conn->select_db(DB_NAME);
    
    // Create tables
    $tables = [
        "CREATE TABLE IF NOT EXISTS contact_submissions (
            id INT AUTO_INCREMENT PRIMARY KEY,
            name VARCHAR(100) NOT NULL,
            email VARCHAR(100),
            phone VARCHAR(20) NOT NULL,
            service VARCHAR(50) NOT NULL,
            message TEXT NOT NULL,
            status ENUM('new', 'read', 'replied', 'archived') DEFAULT 'new',
            created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
            updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
            INDEX idx_status (status),
            INDEX idx_created (created_at)
        )",
        
        "CREATE TABLE IF NOT EXISTS newsletter_subscribers (
            id INT AUTO_INCREMENT PRIMARY KEY,
            email VARCHAR(100) UNIQUE NOT NULL,
            subscribed_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
            active BOOLEAN DEFAULT TRUE,
            INDEX idx_email (email)
        )",
        
        "CREATE TABLE IF NOT EXISTS portfolio_items (
            id INT AUTO_INCREMENT PRIMARY KEY,
            title VARCHAR(200) NOT NULL,
            category VARCHAR(50) NOT NULL,
            description TEXT,
            image_path VARCHAR(255) NOT NULL,
            featured BOOLEAN DEFAULT FALSE,
            created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
            INDEX idx_category (category),
            INDEX idx_featured (featured)
        )",
        
        "CREATE TABLE IF NOT EXISTS testimonials (
            id INT AUTO_INCREMENT PRIMARY KEY,
            client_name VARCHAR(100) NOT NULL,
            client_role VARCHAR(100),
            content TEXT NOT NULL,
            rating TINYINT DEFAULT 5,
            avatar_color VARCHAR(7),
            featured BOOLEAN DEFAULT FALSE,
            created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
            INDEX idx_featured (featured)
        )",
        
        "CREATE TABLE IF NOT EXISTS admin_users (
            id INT AUTO_INCREMENT PRIMARY KEY,
            username VARCHAR(50) UNIQUE NOT NULL,
            password_hash VARCHAR(255) NOT NULL,
            email VARCHAR(100),
            last_login TIMESTAMP NULL,
            created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP
        )"
    ];
    
    foreach ($tables as $tableSql) {
        if ($conn->query($tableSql) === TRUE) {
            echo "Table created successfully<br>";
        } else {
            echo "Error creating table: " . $conn->error . "<br>";
        }
    }
    
    // Insert default admin user (password: admin123)
    $password_hash = password_hash('admin123', PASSWORD_DEFAULT);
    $adminSql = "INSERT IGNORE INTO admin_users (username, password_hash, email) VALUES ('admin', '$password_hash', 'awalandesign.id@gmail.com')";
    $conn->query($adminSql);
    
    // Insert sample testimonials
    $testimonials = [
        "('Budi Santoso', 'Pemilik UMKM Makanan \"Rasa Nusantara\"', 'Logo yang dibuat oleh Awalan Design.id sangat merepresentasikan bisnis saya. Hasilnya profesional dan proses pengerjaannya cepat. Pelanggan sekarang lebih mudah mengingat brand saya!', 5, '#FF6B6B', 1)",
        "('Sari Dewi', 'Mahasiswa & Pengusaha Startup', 'Sebagai mahasiswa yang baru memulai bisnis, budget terbatas. Tapi tim Awalan Design.id memberikan hasil yang luar biasa dengan harga yang terjangkau. Desain brosur dan sosial media saya sekarang sangat menarik!', 5, '#4ECDC4', 1)",
        "('Agus Wijaya', 'Pemilik Toko Online \"Gadget Murah\"', '10 tahun pengalaman benar-benar terasa. Desain yang diberikan tidak hanya bagus tapi juga strategis untuk pemasaran. Penjualan meningkat 40% setelah menggunakan jasa mereka.', 5, '#118AB2', 1)"
    ];
    
    foreach ($testimonials as $testimonial) {
        $sql = "INSERT IGNORE INTO testimonials (client_name, client_role, content, rating, avatar_color, featured) VALUES $testimonial";
        $conn->query($sql);
    }
    
    echo "Database setup completed successfully!<br>";
    echo "Admin Login: admin / admin123<br>";
    $conn->close();
}

// ===== AJAX REQUEST HANDLER =====
if (isset($_SERVER['HTTP_X_REQUESTED_WITH']) && strtolower($_SERVER['HTTP_X_REQUESTED_WITH']) === 'xmlhttprequest') {
    handleAPIRequest();
}

// ===== ADMIN PANEL HTML =====
function renderAdminPanel() {
    if (!isAdminAuthenticated()) {
        header('Location: admin-login.php');
        exit;
    }
    
    $contactSubmissions = getContactSubmissions();
    $newsletterSubscribers = getNewsletterSubscribers();
    
    ?>
    <!DOCTYPE html>
    <html lang="id">
    <head>
        <meta charset="UTF-8">
        <meta name="viewport" content="width=device-width, initial-scale=1.0">
        <title>Admin Panel - Awalan Design.id</title>
        <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
        <style>
            :root {
                --primary: #2563eb;
                --primary-dark: #1d4ed8;
                --secondary: #f59e0b;
                --dark: #1f2937;
                --light: #f9fafb;
                --white: #ffffff;
            }
            
            * {
                margin: 0;
                padding: 0;
                box-sizing: border-box;
            }
            
            body {
                font-family: -apple-system, BlinkMacSystemFont, 'Segoe UI', Roboto, sans-serif;
                background: var(--light);
                color: var(--dark);
            }
            
            .admin-container {
                display: flex;
                min-height: 100vh;
            }
            
            .sidebar {
                width: 250px;
                background: var(--dark);
                color: var(--white);
                padding: 20px 0;
            }
            
            .sidebar-header {
                padding: 0 20px 20px;
                border-bottom: 1px solid rgba(255,255,255,0.1);
            }
            
            .sidebar-header h2 {
                color: var(--white);
                font-size: 1.5rem;
            }
            
            .sidebar-nav {
                margin-top: 20px;
            }
            
            .sidebar-nav a {
                display: flex;
                align-items: center;
                gap: 10px;
                padding: 12px 20px;
                color: rgba(255,255,255,0.8);
                text-decoration: none;
                transition: all 0.3s;
            }
            
            .sidebar-nav a:hover,
            .sidebar-nav a.active {
                background: rgba(255,255,255,0.1);
                color: var(--white);
                border-left: 4px solid var(--secondary);
            }
            
            .sidebar-nav i {
                width: 20px;
                text-align: center;
            }
            
            .main-content {
                flex: 1;
                padding: 20px;
                overflow-y: auto;
            }
            
            .header {
                display: flex;
                justify-content: space-between;
                align-items: center;
                margin-bottom: 30px;
                padding-bottom: 20px;
                border-bottom: 1px solid #e5e7eb;
            }
            
            .stats-grid {
                display: grid;
                grid-template-columns: repeat(auto-fit, minmax(240px, 1fr));
                gap: 20px;
                margin-bottom: 30px;
            }
            
            .stat-card {
                background: var(--white);
                padding: 20px;
                border-radius: 10px;
                box-shadow: 0 2px 10px rgba(0,0,0,0.05);
            }
            
            .stat-card h3 {
                color: var(--dark);
                font-size: 0.9rem;
                text-transform: uppercase;
                letter-spacing: 0.05em;
                margin-bottom: 10px;
            }
            
            .stat-number {
                font-size: 2rem;
                font-weight: bold;
                color: var(--primary);
            }
            
            .table-container {
                background: var(--white);
                border-radius: 10px;
                box-shadow: 0 2px 10px rgba(0,0,0,0.05);
                overflow: hidden;
                margin-bottom: 30px;
            }
            
            table {
                width: 100%;
                border-collapse: collapse;
            }
            
            th {
                background: var(--primary);
                color: var(--white);
                padding: 15px;
                text-align: left;
                font-weight: 600;
            }
            
            td {
                padding: 15px;
                border-bottom: 1px solid #e5e7eb;
            }
            
            tr:hover {
                background: #f8fafc;
            }
            
            .badge {
                display: inline-block;
                padding: 4px 8px;
                border-radius: 20px;
                font-size: 0.75rem;
                font-weight: 600;
                text-transform: uppercase;
            }
            
            .badge-new {
                background: #dbeafe;
                color: var(--primary);
            }
            
            .badge-read {
                background: #dcfce7;
                color: #16a34a;
            }
            
            .btn {
                padding: 8px 16px;
                border: none;
                border-radius: 5px;
                cursor: pointer;
                font-weight: 600;
                transition: all 0.3s;
            }
            
            .btn-primary {
                background: var(--primary);
                color: var(--white);
            }
            
            .btn-primary:hover {
                background: var(--primary-dark);
            }
            
            .btn-danger {
                background: #ef4444;
                color: var(--white);
            }
            
            .btn-danger:hover {
                background: #dc2626;
            }
            
            .btn-sm {
                padding: 4px 8px;
                font-size: 0.875rem;
            }
            
            .logout-btn {
                background: transparent;
                border: 1px solid rgba(255,255,255,0.2);
                color: var(--white);
                padding: 8px 16px;
                border-radius: 5px;
                cursor: pointer;
                transition: all 0.3s;
            }
            
            .logout-btn:hover {
                background: rgba(255,255,255,0.1);
            }
            
            @media (max-width: 768px) {
                .admin-container {
                    flex-direction: column;
                }
                
                .sidebar {
                    width: 100%;
                }
                
                .stats-grid {
                    grid-template-columns: 1fr;
                }
            }
        </style>
    </head>
    <body>
        <div class="admin-container">
            <div class="sidebar">
                <div class="sidebar-header">
                    <h2>Awalan Design.id</h2>
                    <p style="color: rgba(255,255,255,0.6); font-size: 0.9rem;">Admin Panel</p>
                </div>
                
                <div class="sidebar-nav">
                    <a href="#" class="active">
                        <i class="fas fa-tachometer-alt"></i>
                        Dashboard
                    </a>
                    <a href="#">
                        <i class="fas fa-envelope"></i>
                        Pesan Masuk
                        <span style="margin-left: auto; background: var(--primary); color: white; padding: 2px 8px; border-radius: 10px; font-size: 0.75rem;">
                            <?php echo count($contactSubmissions); ?>
                        </span>
                    </a>
                    <a href="#">
                        <i class="fas fa-users"></i>
                        Newsletter
                        <span style="margin-left: auto; background: var(--secondary); color: white; padding: 2px 8px; border-radius: 10px; font-size: 0.75rem;">
                            <?php echo count($newsletterSubscribers); ?>
                        </span>
                    </a>
                    <a href="#">
                        <i class="fas fa-images"></i>
                        Portofolio
                    </a>
                    <a href="#">
                        <i class="fas fa-star"></i>
                        Testimoni
                    </a>
                    <a href="#">
                        <i class="fas fa-cog"></i>
                        Pengaturan
                    </a>
                </div>
                
                <div style="padding: 20px; margin-top: auto;">
                    <form action="" method="POST">
                        <input type="hidden" name="action" value="logout">
                        <button type="submit" class="logout-btn">
                            <i class="fas fa-sign-out-alt"></i> Logout
                        </button>
                    </form>
                </div>
            </div>
            
            <div class="main-content">
                <div class="header">
                    <h1>Dashboard Admin</h1>
                    <div style="color: #6b7280;">
                        <i class="fas fa-calendar"></i> <?php echo date('d F Y'); ?>
                    </div>
                </div>
                
                <div class="stats-grid">
                    <div class="stat-card">
                        <h3>Total Pesan Masuk</h3>
                        <div class="stat-number"><?php echo count($contactSubmissions); ?></div>
                        <p style="color: #6b7280; font-size: 0.875rem; margin-top: 5px;">
                            <?php 
                            $today = date('Y-m-d');
                            $todayCount = 0;
                            foreach ($contactSubmissions as $submission) {
                                if (date('Y-m-d', strtotime($submission['created_at'])) === $today) {
                                    $todayCount++;
                                }
                            }
                            echo $todayCount . ' hari ini';
                            ?>
                        </p>
                    </div>
                    
                    <div class="stat-card">
                        <h3>Subscriber Newsletter</h3>
                        <div class="stat-number"><?php echo count($newsletterSubscribers); ?></div>
                        <p style="color: #6b7280; font-size: 0.875rem; margin-top: 5px;">
                            <?php
                            $activeSubscribers = 0;
                            foreach ($newsletterSubscribers as $subscriber) {
                                if ($subscriber['active']) {
                                    $activeSubscribers++;
                                }
                            }
                            echo $activeSubscribers . ' aktif';
                            ?>
                        </p>
                    </div>
                    
                    <div class="stat-card">
                        <h3>Pesan Belum Dibaca</h3>
                        <div class="stat-number">
                            <?php
                            $unreadCount = 0;
                            foreach ($contactSubmissions as $submission) {
                                if ($submission['status'] === 'new') {
                                    $unreadCount++;
                                }
                            }
                            echo $unreadCount;
                            ?>
                        </div>
                        <p style="color: #6b7280; font-size: 0.875rem; margin-top: 5px;">
                            <?php echo round(($unreadCount / max(1, count($contactSubmissions))) * 100) . '% dari total'; ?>
                        </p>
                    </div>
                </div>
                
                <div class="table-container">
                    <h3 style="padding: 20px 20px 0;">Pesan Terbaru</h3>
                    <table>
                        <thead>
                            <tr>
                                <th>Nama</th>
                                <th>Kontak</th>
                                <th>Layanan</th>
                                <th>Status</th>
                                <th>Tanggal</th>
                                <th>Aksi</th>
                            </tr>
                        </thead>
                        <tbody>
                            <?php foreach (array_slice($contactSubmissions, 0, 10) as $submission): ?>
                            <tr>
                                <td><?php echo htmlspecialchars($submission['name']); ?></td>
                                <td>
                                    <div><?php echo htmlspecialchars($submission['phone']); ?></div>
                                    <?php if ($submission['email']): ?>
                                    <div style="font-size: 0.875rem; color: #6b7280;">
                                        <?php echo htmlspecialchars($submission['email']); ?>
                                    </div>
                                    <?php endif; ?>
                                </td>
                                <td><?php echo htmlspecialchars($submission['service']); ?></td>
                                <td>
                                    <span class="badge badge-<?php echo $submission['status']; ?>">
                                        <?php echo $submission['status']; ?>
                                    </span>
                                </td>
                                <td>
                                    <?php echo date('d/m/Y', strtotime($submission['created_at'])); ?><br>
                                    <small style="color: #6b7280;">
                                        <?php echo date('H:i', strtotime($submission['created_at'])); ?>
                                    </small>
                                </td>
                                <td>
                                    <a href="https://wa.me/<?php echo $submission['phone']; ?>" 
                                       target="_blank" 
                                       class="btn btn-primary btn-sm"
                                       style="text-decoration: none; margin-right: 5px;">
                                        <i class="fab fa-whatsapp"></i>
                                    </a>
                                    <button class="btn btn-danger btn-sm">
                                        <i class="fas fa-trash"></i>
                                    </button>
                                </td>
                            </tr>
                            <?php endforeach; ?>
                        </tbody>
                    </table>
                    <?php if (count($contactSubmissions) > 10): ?>
                    <div style="padding: 15px 20px; text-align: center; border-top: 1px solid #e5e7eb;">
                        <a href="#" style="color: var(--primary); text-decoration: none;">
                            Lihat semua pesan (<?php echo count($contactSubmissions); ?>)
                        </a>
                    </div>
                    <?php endif; ?>
                </div>
            </div>
        </div>
        
        <script>
            // Auto-refresh every 60 seconds
            setTimeout(() => {
                window.location.reload();
            }, 60000);
        </script>
    </body>
    </html>
    <?php
}

// ===== ADMIN LOGIN PAGE =====
function renderAdminLogin() {
    if ($_SERVER['REQUEST_METHOD'] === 'POST') {
        $username = $_POST['username'] ?? '';
        $password = $_POST['password'] ?? '';
        
        if (adminLogin($username, $password)) {
            header('Location: admin.php');
            exit;
        } else {
            $error = 'Username atau password salah';
        }
    }
    
    ?>
    <!DOCTYPE html>
    <html lang="id">
    <head>
        <meta charset="UTF-8">
        <meta name="viewport" content="width=device-width, initial-scale=1.0">
        <title>Admin Login - Awalan Design.id</title>
        <style>
            * {
                margin: 0;
                padding: 0;
                box-sizing: border-box;
            }
            
            body {
                font-family: -apple-system, BlinkMacSystemFont, 'Segoe UI', Roboto, sans-serif;
                background: linear-gradient(135deg, #2563eb 0%, #f59e0b 100%);
                min-height: 100vh;
                display: flex;
                align-items: center;
                justify-content: center;
                padding: 20px;
            }
            
            .login-container {
                background: white;
                border-radius: 15px;
                box-shadow: 0 20px 40px rgba(0,0,0,0.1);
                width: 100%;
                max-width: 400px;
                overflow: hidden;
            }
            
            .login-header {
                background: linear-gradient(135deg, #2563eb 0%, #1d4ed8 100%);
                color: white;
                padding: 40px 20px;
                text-align: center;
            }
            
            .login-header h1 {
                font-size: 1.5rem;
                margin-bottom: 10px;
            }
            
            .login-body {
                padding: 40px;
            }
            
            .form-group {
                margin-bottom: 20px;
            }
            
            .form-group label {
                display: block;
                margin-bottom: 8px;
                font-weight: 600;
                color: #374151;
            }
            
            .form-control {
                width: 100%;
                padding: 12px 15px;
                border: 2px solid #e5e7eb;
                border-radius: 8px;
                font-size: 1rem;
                transition: all 0.3s;
            }
            
            .form-control:focus {
                outline: none;
                border-color: #2563eb;
                box-shadow: 0 0 0 3px rgba(37, 99, 235, 0.1);
            }
            
            .btn-login {
                width: 100%;
                padding: 14px;
                background: linear-gradient(135deg, #2563eb 0%, #f59e0b 100%);
                color: white;
                border: none;
                border-radius: 8px;
                font-size: 1rem;
                font-weight: 600;
                cursor: pointer;
                transition: all 0.3s;
            }
            
            .btn-login:hover {
                transform: translateY(-2px);
                box-shadow: 0 10px 20px rgba(0,0,0,0.1);
            }
            
            .error-message {
                background: #fee2e2;
                color: #dc2626;
                padding: 12px;
                border-radius: 8px;
                margin-bottom: 20px;
                text-align: center;
                font-size: 0.9rem;
            }
            
            .login-footer {
                text-align: center;
                margin-top: 20px;
                color: #6b7280;
                font-size: 0.875rem;
            }
        </style>
    </head>
    <body>
        <div class="login-container">
            <div class="login-header">
                <h1>Awalan Design.id</h1>
                <p>Admin Panel Login</p>
            </div>
            
            <div class="login-body">
                <?php if (isset($error)): ?>
                <div class="error-message">
                    <?php echo $error; ?>
                </div>
                <?php endif; ?>
                
                <form method="POST" action="">
                    <div class="form-group">
                        <label for="username">Username</label>
                        <input type="text" id="username" name="username" class="form-control" required>
                    </div>
                    
                    <div class="form-group">
                        <label for="password">Password</label>
                        <input type="password" id="password" name="password" class="form-control" required>
                    </div>
                    
                    <button type="submit" class="btn-login">
                        Masuk ke Admin Panel
                    </button>
                </form>
                
                <div class="login-footer">
                    <p>© <?php echo date('Y'); ?> Awalan Design.id</p>
                </div>
            </div>
        </div>
    </body>
    </html>
    <?php
}

// ===== ROUTING =====
if (isset($_GET['setup'])) {
    setupDatabase();
} elseif (isset($_GET['admin'])) {
    renderAdminPanel();
} elseif (isset($_GET['login'])) {
    renderAdminLogin();
} elseif (isset($_GET['sitemap'])) {
    generateSitemap();
    echo "Sitemap generated successfully!";
}

// ===== EXAMPLE USAGE IN HTML PAGES =====
/*
// Untuk form contact:
<form action="api.php" method="POST" id="contactForm">
    <input type="hidden" name="action" value="contact">
    <!-- form fields -->
</form>

// Untuk newsletter:
<form action="api.php" method="POST" id="newsletterForm">
    <input type="hidden" name="action" value="newsletter">
    <!-- email field -->
</form>

// JavaScript untuk handle form:
document.getElementById('contactForm').addEventListener('submit', function(e) {
    e.preventDefault();
    
    fetch('api.php', {
        method: 'POST',
        body: new FormData(this)
    })
    .then(response => response.json())
    .then(data => {
        if (data.success) {
            alert(data.message);
            // Redirect to WhatsApp
            window.open(`https://wa.me/6285815056990?text=${encodeURIComponent('Halo, saya ' + data.data.name + ', tertarik dengan ' + data.data.service)}`, '_blank');
        } else {
            alert('Error: ' + data.message);
        }
    });
});
*/
?>