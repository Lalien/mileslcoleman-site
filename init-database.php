<?php
/**
 * Database Initialization Script
 * 
 * This script creates the SQLite database and leads table.
 * It can be run multiple times safely - it checks if the table already exists.
 * 
 * Usage: php init-database.php
 */

// Database file path - store in the same directory as this script
$dbPath = __DIR__ . '/leads.db';

try {
    // Create/open the SQLite database
    $db = new PDO('sqlite:' . $dbPath);
    
    // Set error mode to exceptions
    $db->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
    
    // Create the leads table if it doesn't exist
    $createTableSQL = "
        CREATE TABLE IF NOT EXISTS leads (
            id INTEGER PRIMARY KEY AUTOINCREMENT,
            name TEXT NOT NULL,
            email TEXT NOT NULL,
            phone TEXT,
            message TEXT NOT NULL,
            submitted_at DATETIME DEFAULT CURRENT_TIMESTAMP,
            user_agent TEXT,
            ip_address TEXT
        )
    ";
    
    $db->exec($createTableSQL);
    
    echo "✓ Database initialized successfully!\n";
    echo "✓ Database location: " . $dbPath . "\n";
    echo "✓ Table 'leads' is ready.\n";
    
    // Check if table exists and show structure
    $result = $db->query("SELECT sql FROM sqlite_master WHERE type='table' AND name='leads'");
    $tableInfo = $result->fetch(PDO::FETCH_ASSOC);
    
    if ($tableInfo) {
        echo "\n✓ Table structure:\n";
        echo $tableInfo['sql'] . "\n";
    }
    
    // Get current count of leads
    $countResult = $db->query("SELECT COUNT(*) as count FROM leads");
    $count = $countResult->fetch(PDO::FETCH_ASSOC);
    echo "\n✓ Current number of leads: " . $count['count'] . "\n";
    
} catch (PDOException $e) {
    echo "✗ Error initializing database: " . $e->getMessage() . "\n";
    exit(1);
}
?>
