<?php
// Enable error reporting for debugging (IMPORTANT: Disable in production!)
// For production: set error_reporting(0) and ini_set('display_errors', 0)
error_reporting(E_ALL);
ini_set('display_errors', 0);

// Set response headers
header('Content-Type: application/json');
// TODO: In production, replace '*' with your actual domain (e.g., 'https://milehighmiles.com')
// For local development, you may need to adjust this based on your setup
$allowedOrigins = ['https://milehighmiles.com', 'http://localhost:8080', 'http://127.0.0.1:8080'];
$origin = $_SERVER['HTTP_ORIGIN'] ?? '';
if (in_array($origin, $allowedOrigins)) {
    header('Access-Control-Allow-Origin: ' . $origin);
}
header('Access-Control-Allow-Methods: POST');
header('Access-Control-Allow-Headers: Content-Type');

// Initialize SQLite database connection
$dbPath = __DIR__ . '/leads.db';
$db = null;
$dbError = null;

try {
    $db = new PDO('sqlite:' . $dbPath);
    $db->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
} catch (PDOException $e) {
    // Log the error
    $dbError = $e->getMessage();
    error_log('Database connection error: ' . $dbError);
}

// Handle preflight requests
if ($_SERVER['REQUEST_METHOD'] === 'OPTIONS') {
    http_response_code(200);
    exit();
}

// Only accept POST requests
if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    http_response_code(405);
    echo json_encode(['success' => false, 'message' => 'Method not allowed']);
    exit();
}

// Get POST data
$input = file_get_contents('php://input');
$data = json_decode($input, true);

// Validate required fields
$required_fields = ['name', 'email', 'message'];
$errors = [];

foreach ($required_fields as $field) {
    if (empty($data[$field])) {
        $errors[] = ucfirst($field) . ' is required';
    }
}

// Validate email format
if (!empty($data['email']) && !filter_var($data['email'], FILTER_VALIDATE_EMAIL)) {
    $errors[] = 'Invalid email format';
}

// Return validation errors if any
if (!empty($errors)) {
    http_response_code(400);
    echo json_encode(['success' => false, 'message' => implode(', ', $errors)]);
    exit();
}

// Sanitize input data
$name = htmlspecialchars(strip_tags(trim($data['name'])));
$email = filter_var(trim($data['email']), FILTER_SANITIZE_EMAIL);
$phone = !empty($data['phone']) ? htmlspecialchars(strip_tags(trim($data['phone']))) : '';
$message = htmlspecialchars(strip_tags(trim($data['message'])));

// Get additional tracking information
$user_agent = $_SERVER['HTTP_USER_AGENT'] ?? '';
// Get real IP address (works behind proxies/load balancers)
$ip_address = $_SERVER['HTTP_X_FORWARDED_FOR'] ?? $_SERVER['HTTP_X_REAL_IP'] ?? $_SERVER['REMOTE_ADDR'] ?? '';
// If X-Forwarded-For contains multiple IPs, take the first one (client IP)
if (strpos($ip_address, ',') !== false) {
    $ip_address = trim(explode(',', $ip_address)[0]);
}

// Save to database
if ($db !== null) {
    try {
        $stmt = $db->prepare("
            INSERT INTO leads (name, email, phone, message, user_agent, ip_address)
            VALUES (:name, :email, :phone, :message, :user_agent, :ip_address)
        ");
        
        $stmt->execute([
            ':name' => $name,
            ':email' => $email,
            ':phone' => $phone,
            ':message' => $message,
            ':user_agent' => $user_agent,
            ':ip_address' => $ip_address
        ]);
        
        http_response_code(200);
        echo json_encode([
            'success' => true,
            'message' => 'Thank you for your message! We will get back to you soon.'
        ]);
        exit();
    } catch (PDOException $e) {
        // Log the error
        error_log('Database insert error: ' . $e->getMessage());
        
        http_response_code(500);
        echo json_encode([
            'success' => false,
            'message' => 'Sorry, there was an error saving your message. Please try again later or contact us directly.'
        ]);
        exit();
    }
} else {
    error_log('Database not available');
    http_response_code(500);
    echo json_encode([
        'success' => false,
        'message' => 'Sorry, there was an error processing your message. Please try again later or contact us directly.'
    ]);
    exit();
}
?>
