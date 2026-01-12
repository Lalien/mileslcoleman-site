<?php
/**
 * Admin Lead Viewer
 * 
 * Displays all contact form submissions from the database.
 * Includes basic authentication, search/filter, and CSV export.
 */

// Simple authentication - change this password!
session_start();

// ⚠️ SECURITY WARNING: Change this password immediately!
// For production, consider using environment variables or a more secure authentication method
// Example: $ADMIN_PASSWORD = getenv('ADMIN_PASSWORD') ?: 'changeme123';
$ADMIN_PASSWORD = 'changeme123'; // TODO: Change this password!

// Handle login
if (isset($_POST['password'])) {
    if ($_POST['password'] === $ADMIN_PASSWORD) {
        $_SESSION['admin_logged_in'] = true;
    } else {
        $login_error = 'Invalid password';
    }
}

// Handle logout
if (isset($_GET['logout'])) {
    session_destroy();
    header('Location: view-leads.php');
    exit();
}

// Check if logged in
if (!isset($_SESSION['admin_logged_in']) || $_SESSION['admin_logged_in'] !== true) {
    // Show login form
    ?>
    <!DOCTYPE html>
    <html lang="en">
    <head>
        <meta charset="UTF-8">
        <meta name="viewport" content="width=device-width, initial-scale=1.0">
        <title>Admin Login</title>
        <style>
            body {
                font-family: Arial, sans-serif;
                display: flex;
                justify-content: center;
                align-items: center;
                min-height: 100vh;
                margin: 0;
                background: linear-gradient(135deg, #667eea 0%, #764ba2 100%);
            }
            .login-container {
                background: white;
                padding: 2rem;
                border-radius: 10px;
                box-shadow: 0 4px 6px rgba(0, 0, 0, 0.1);
                width: 100%;
                max-width: 400px;
            }
            h1 {
                margin-top: 0;
                color: #333;
                text-align: center;
            }
            input[type="password"] {
                width: 100%;
                padding: 0.75rem;
                margin: 1rem 0;
                border: 1px solid #ddd;
                border-radius: 5px;
                font-size: 1rem;
                box-sizing: border-box;
            }
            button {
                width: 100%;
                padding: 0.75rem;
                background: #667eea;
                color: white;
                border: none;
                border-radius: 5px;
                font-size: 1rem;
                cursor: pointer;
                transition: background 0.3s;
            }
            button:hover {
                background: #5568d3;
            }
            .error {
                color: #e53e3e;
                margin-bottom: 1rem;
                text-align: center;
            }
        </style>
    </head>
    <body>
        <div class="login-container">
            <h1>🔒 Admin Login</h1>
            <?php if (isset($login_error)): ?>
                <div class="error"><?php echo htmlspecialchars($login_error); ?></div>
            <?php endif; ?>
            <form method="POST">
                <input type="password" name="password" placeholder="Enter admin password" required autofocus>
                <button type="submit">Login</button>
            </form>
        </div>
    </body>
    </html>
    <?php
    exit();
}

// User is logged in - show leads
$dbPath = dirname(__DIR__) . '/leads.db';

// Check if database exists
if (!file_exists($dbPath)) {
    die('Database not found. Please run init-database.php first.');
}

try {
    $db = new PDO('sqlite:' . $dbPath);
    $db->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
} catch (PDOException $e) {
    die('Database connection error: ' . $e->getMessage());
}

// Handle CSV export
if (isset($_GET['export']) && $_GET['export'] === 'csv') {
    header('Content-Type: text/csv');
    header('Content-Disposition: attachment; filename="leads_' . date('Y-m-d') . '.csv"');
    
    $output = fopen('php://output', 'w');
    
    // Add CSV headers
    fputcsv($output, ['ID', 'Name', 'Email', 'Phone', 'Message', 'Submitted At', 'User Agent', 'IP Address']);
    
    // Get all leads
    $result = $db->query("SELECT * FROM leads ORDER BY submitted_at DESC");
    while ($row = $result->fetch(PDO::FETCH_ASSOC)) {
        fputcsv($output, $row);
    }
    
    fclose($output);
    exit();
}

// Handle search/filter
$search = $_GET['search'] ?? '';
$searchQuery = '';
$params = [];

if (!empty($search)) {
    $searchQuery = " WHERE name LIKE :search OR email LIKE :search OR message LIKE :search";
    $params[':search'] = '%' . $search . '%';
}

// Get leads with pagination
$page = isset($_GET['page']) ? (int)$_GET['page'] : 1;
$perPage = 20;
$offset = ($page - 1) * $perPage;

$countStmt = $db->prepare("SELECT COUNT(*) as total FROM leads" . $searchQuery);
$countStmt->execute($params);
$totalLeads = $countStmt->fetch(PDO::FETCH_ASSOC)['total'];
$totalPages = ceil($totalLeads / $perPage);

$stmt = $db->prepare("SELECT * FROM leads" . $searchQuery . " ORDER BY submitted_at DESC LIMIT :limit OFFSET :offset");
foreach ($params as $key => $value) {
    $stmt->bindValue($key, $value);
}
$stmt->bindValue(':limit', $perPage, PDO::PARAM_INT);
$stmt->bindValue(':offset', $offset, PDO::PARAM_INT);
$stmt->execute();
$leads = $stmt->fetchAll(PDO::FETCH_ASSOC);

?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Lead Management</title>
    <style>
        * {
            margin: 0;
            padding: 0;
            box-sizing: border-box;
        }
        body {
            font-family: -apple-system, BlinkMacSystemFont, 'Segoe UI', Roboto, 'Helvetica Neue', Arial, sans-serif;
            background: #f7fafc;
            padding: 2rem;
        }
        .container {
            max-width: 1400px;
            margin: 0 auto;
        }
        .header {
            background: white;
            padding: 1.5rem;
            border-radius: 10px;
            box-shadow: 0 1px 3px rgba(0, 0, 0, 0.1);
            margin-bottom: 2rem;
            display: flex;
            justify-content: space-between;
            align-items: center;
            flex-wrap: wrap;
            gap: 1rem;
        }
        h1 {
            color: #2d3748;
            font-size: 1.75rem;
        }
        .header-actions {
            display: flex;
            gap: 1rem;
            align-items: center;
        }
        .search-box {
            display: flex;
            gap: 0.5rem;
        }
        input[type="text"] {
            padding: 0.5rem 1rem;
            border: 1px solid #e2e8f0;
            border-radius: 5px;
            font-size: 0.95rem;
            min-width: 250px;
        }
        .btn {
            padding: 0.5rem 1rem;
            border: none;
            border-radius: 5px;
            font-size: 0.95rem;
            cursor: pointer;
            text-decoration: none;
            display: inline-block;
            transition: all 0.3s;
        }
        .btn-primary {
            background: #667eea;
            color: white;
        }
        .btn-primary:hover {
            background: #5568d3;
        }
        .btn-secondary {
            background: #48bb78;
            color: white;
        }
        .btn-secondary:hover {
            background: #38a169;
        }
        .btn-logout {
            background: #f56565;
            color: white;
        }
        .btn-logout:hover {
            background: #e53e3e;
        }
        .stats {
            background: white;
            padding: 1rem;
            border-radius: 10px;
            box-shadow: 0 1px 3px rgba(0, 0, 0, 0.1);
            margin-bottom: 2rem;
            display: flex;
            gap: 2rem;
        }
        .stat {
            flex: 1;
            text-align: center;
        }
        .stat-value {
            font-size: 2rem;
            font-weight: bold;
            color: #667eea;
        }
        .stat-label {
            color: #718096;
            font-size: 0.9rem;
            margin-top: 0.25rem;
        }
        .table-container {
            background: white;
            border-radius: 10px;
            box-shadow: 0 1px 3px rgba(0, 0, 0, 0.1);
            overflow: hidden;
        }
        table {
            width: 100%;
            border-collapse: collapse;
        }
        th {
            background: #f7fafc;
            padding: 1rem;
            text-align: left;
            font-weight: 600;
            color: #4a5568;
            border-bottom: 2px solid #e2e8f0;
        }
        td {
            padding: 1rem;
            border-bottom: 1px solid #e2e8f0;
            color: #2d3748;
        }
        tr:hover {
            background: #f7fafc;
        }
        .message-cell {
            max-width: 300px;
            overflow: hidden;
            text-overflow: ellipsis;
            white-space: nowrap;
        }
        .pagination {
            display: flex;
            justify-content: center;
            gap: 0.5rem;
            margin-top: 2rem;
        }
        .pagination a, .pagination span {
            padding: 0.5rem 1rem;
            background: white;
            border: 1px solid #e2e8f0;
            border-radius: 5px;
            text-decoration: none;
            color: #4a5568;
        }
        .pagination a:hover {
            background: #667eea;
            color: white;
            border-color: #667eea;
        }
        .pagination .active {
            background: #667eea;
            color: white;
            border-color: #667eea;
        }
        .empty-state {
            text-align: center;
            padding: 3rem;
            color: #718096;
        }
        .empty-state-icon {
            font-size: 3rem;
            margin-bottom: 1rem;
        }
    </style>
</head>
<body>
    <div class="container">
        <div class="header">
            <h1>📊 Lead Management</h1>
            <div class="header-actions">
                <form method="GET" class="search-box">
                    <input type="text" name="search" placeholder="Search by name, email, or message..." value="<?php echo htmlspecialchars($search); ?>">
                    <button type="submit" class="btn btn-primary">Search</button>
                    <?php if (!empty($search)): ?>
                        <a href="view-leads.php" class="btn btn-primary">Clear</a>
                    <?php endif; ?>
                </form>
                <a href="?export=csv" class="btn btn-secondary">📥 Export CSV</a>
                <a href="?logout" class="btn btn-logout">Logout</a>
            </div>
        </div>

        <div class="stats">
            <div class="stat">
                <div class="stat-value"><?php echo number_format($totalLeads); ?></div>
                <div class="stat-label">Total Leads</div>
            </div>
        </div>

        <div class="table-container">
            <?php if (count($leads) > 0): ?>
                <table>
                    <thead>
                        <tr>
                            <th>ID</th>
                            <th>Name</th>
                            <th>Email</th>
                            <th>Phone</th>
                            <th>Message</th>
                            <th>Submitted</th>
                        </tr>
                    </thead>
                    <tbody>
                        <?php foreach ($leads as $lead): ?>
                            <tr>
                                <td><?php echo htmlspecialchars($lead['id']); ?></td>
                                <td><?php echo htmlspecialchars($lead['name']); ?></td>
                                <td><?php echo htmlspecialchars($lead['email']); ?></td>
                                <td><?php echo htmlspecialchars($lead['phone'] ?: '-'); ?></td>
                                <td class="message-cell" title="<?php echo htmlspecialchars($lead['message']); ?>">
                                    <?php echo htmlspecialchars($lead['message']); ?>
                                </td>
                                <td><?php echo htmlspecialchars($lead['submitted_at']); ?></td>
                            </tr>
                        <?php endforeach; ?>
                    </tbody>
                </table>
            <?php else: ?>
                <div class="empty-state">
                    <div class="empty-state-icon">📭</div>
                    <h3>No leads found</h3>
                    <p><?php echo !empty($search) ? 'Try adjusting your search' : 'No submissions yet'; ?></p>
                </div>
            <?php endif; ?>
        </div>

        <?php if ($totalPages > 1): ?>
            <div class="pagination">
                <?php if ($page > 1): ?>
                    <a href="?page=<?php echo $page - 1; ?><?php echo !empty($search) ? '&search=' . urlencode($search) : ''; ?>">← Previous</a>
                <?php endif; ?>
                
                <?php for ($i = 1; $i <= $totalPages; $i++): ?>
                    <?php if ($i == $page): ?>
                        <span class="active"><?php echo $i; ?></span>
                    <?php else: ?>
                        <a href="?page=<?php echo $i; ?><?php echo !empty($search) ? '&search=' . urlencode($search) : ''; ?>"><?php echo $i; ?></a>
                    <?php endif; ?>
                <?php endfor; ?>
                
                <?php if ($page < $totalPages): ?>
                    <a href="?page=<?php echo $page + 1; ?><?php echo !empty($search) ? '&search=' . urlencode($search) : ''; ?>">Next →</a>
                <?php endif; ?>
            </div>
        <?php endif; ?>
    </div>
</body>
</html>
