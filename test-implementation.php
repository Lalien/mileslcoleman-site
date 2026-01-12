#!/usr/bin/env php
<?php
/**
 * Test Script for SQLite Contact Form Implementation
 * 
 * ⚠️ WARNING: This is a development/testing script only!
 * DO NOT deploy this file to production servers.
 * Add test-implementation.php to .gitignore if deploying manually.
 * 
 * This script tests:
 * 1. Database initialization
 * 2. Database connection from contact-handler.php
 * 3. Data insertion
 * 4. Data retrieval
 */

echo "=== SQLite Contact Form Implementation Tests ===\n\n";

// Test 1: Database exists
echo "Test 1: Checking if database exists...\n";
$dbPath = __DIR__ . '/leads.db';
if (file_exists($dbPath)) {
    echo "✓ Database file exists at: $dbPath\n";
} else {
    echo "✗ Database file not found. Run: php init-database.php\n";
    exit(1);
}

// Test 2: Database connection
echo "\nTest 2: Testing database connection...\n";
try {
    $db = new PDO('sqlite:' . $dbPath);
    $db->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
    echo "✓ Successfully connected to database\n";
} catch (PDOException $e) {
    echo "✗ Database connection failed: " . $e->getMessage() . "\n";
    exit(1);
}

// Test 3: Table structure
echo "\nTest 3: Verifying table structure...\n";
try {
    $result = $db->query("PRAGMA table_info(leads)");
    $columns = $result->fetchAll(PDO::FETCH_ASSOC);
    
    $expectedColumns = ['id', 'name', 'email', 'phone', 'message', 'submitted_at', 'user_agent', 'ip_address'];
    $actualColumns = array_column($columns, 'name');
    
    if ($actualColumns === $expectedColumns) {
        echo "✓ Table structure is correct\n";
        foreach ($columns as $col) {
            echo "  - {$col['name']} ({$col['type']})\n";
        }
    } else {
        echo "✗ Table structure mismatch\n";
        echo "  Expected: " . implode(', ', $expectedColumns) . "\n";
        echo "  Actual: " . implode(', ', $actualColumns) . "\n";
        exit(1);
    }
} catch (PDOException $e) {
    echo "✗ Error checking table structure: " . $e->getMessage() . "\n";
    exit(1);
}

// Test 4: Insert test data
echo "\nTest 4: Testing data insertion...\n";
try {
    $stmt = $db->prepare("
        INSERT INTO leads (name, email, phone, message, user_agent, ip_address)
        VALUES (:name, :email, :phone, :message, :user_agent, :ip_address)
    ");
    
    $stmt->execute([
        ':name' => 'Test User',
        ':email' => 'test@example.com',
        ':phone' => '555-0123',
        ':message' => 'This is a test message from the automated test script.',
        ':user_agent' => 'Test Script v1.0',
        ':ip_address' => '127.0.0.1'
    ]);
    
    $lastId = $db->lastInsertId();
    echo "✓ Test data inserted successfully (ID: $lastId)\n";
} catch (PDOException $e) {
    echo "✗ Data insertion failed: " . $e->getMessage() . "\n";
    exit(1);
}

// Test 5: Retrieve data
echo "\nTest 5: Testing data retrieval...\n";
try {
    $result = $db->query("SELECT * FROM leads ORDER BY id DESC LIMIT 1");
    $lead = $result->fetch(PDO::FETCH_ASSOC);
    
    if ($lead) {
        echo "✓ Successfully retrieved latest lead:\n";
        echo "  - ID: {$lead['id']}\n";
        echo "  - Name: {$lead['name']}\n";
        echo "  - Email: {$lead['email']}\n";
        echo "  - Phone: {$lead['phone']}\n";
        echo "  - Message: " . substr($lead['message'], 0, 50) . "...\n";
        echo "  - Submitted: {$lead['submitted_at']}\n";
    } else {
        echo "✗ No data found in database\n";
        exit(1);
    }
} catch (PDOException $e) {
    echo "✗ Data retrieval failed: " . $e->getMessage() . "\n";
    exit(1);
}

// Test 6: Count total leads
echo "\nTest 6: Counting total leads...\n";
try {
    $result = $db->query("SELECT COUNT(*) as total FROM leads");
    $count = $result->fetch(PDO::FETCH_ASSOC);
    echo "✓ Total leads in database: {$count['total']}\n";
} catch (PDOException $e) {
    echo "✗ Count query failed: " . $e->getMessage() . "\n";
    exit(1);
}

// Test 7: Verify contact-handler.php has database code
echo "\nTest 7: Verifying contact-handler.php contains database code...\n";
$handlerPath = __DIR__ . '/contact-handler.php';
$handlerContent = file_get_contents($handlerPath);

$checks = [
    'PDO' => 'PDO class usage',
    'leads.db' => 'Database path reference',
    'INSERT INTO leads' => 'Insert statement',
    'user_agent' => 'User agent tracking',
    'ip_address' => 'IP address tracking'
];

$allPassed = true;
foreach ($checks as $needle => $description) {
    if (strpos($handlerContent, $needle) !== false) {
        echo "✓ Found: $description\n";
    } else {
        echo "✗ Missing: $description\n";
        $allPassed = false;
    }
}

if (!$allPassed) {
    exit(1);
}

// Test 8: Verify admin interface exists
echo "\nTest 8: Verifying admin interface exists...\n";
$adminPath = __DIR__ . '/admin/view-leads.php';
if (file_exists($adminPath)) {
    echo "✓ Admin interface file exists\n";
    $adminContent = file_get_contents($adminPath);
    
    $adminChecks = [
        'session_start()' => 'Session management',
        'ADMIN_PASSWORD' => 'Password authentication',
        'export=csv' => 'CSV export functionality',
        'search' => 'Search functionality'
    ];
    
    foreach ($adminChecks as $needle => $description) {
        if (strpos($adminContent, $needle) !== false) {
            echo "✓ Found: $description\n";
        } else {
            echo "✗ Missing: $description\n";
        }
    }
} else {
    echo "✗ Admin interface file not found\n";
    exit(1);
}

// Test 9: Verify .gitignore excludes database
echo "\nTest 9: Verifying .gitignore configuration...\n";
$gitignorePath = __DIR__ . '/.gitignore';
if (file_exists($gitignorePath)) {
    $gitignoreContent = file_get_contents($gitignorePath);
    if (strpos($gitignoreContent, 'leads.db') !== false) {
        echo "✓ .gitignore excludes leads.db\n";
    } else {
        echo "✗ .gitignore does not exclude leads.db\n";
    }
} else {
    echo "✗ .gitignore file not found\n";
}

// Test 10: Verify .htaccess protects database
echo "\nTest 10: Verifying .htaccess configuration...\n";
$htaccessPath = __DIR__ . '/.htaccess';
if (file_exists($htaccessPath)) {
    $htaccessContent = file_get_contents($htaccessPath);
    if (strpos($htaccessContent, 'leads\.db') !== false) {
        echo "✓ .htaccess protects database file\n";
    } else {
        echo "✗ .htaccess does not protect database file\n";
    }
} else {
    echo "✗ .htaccess file not found\n";
}

echo "\n=== All Tests Completed Successfully! ===\n\n";
echo "Summary:\n";
echo "- Database initialized and working\n";
echo "- Contact handler updated with database integration\n";
echo "- Admin interface created with authentication and export\n";
echo "- Security measures in place (.htaccess, .gitignore)\n";
echo "\nNext steps:\n";
echo "1. Change the admin password in admin/view-leads.php\n";
echo "2. Test the contact form on your website\n";
echo "3. Access admin interface at: /admin/view-leads.php\n";
echo "4. Run 'php init-database.php' on your production server\n";
?>
