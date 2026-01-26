<?php
/**
 * Database Test Connection Script
 * Moueene - Home Services Platform
 * 
 * This script tests the database connection and displays configuration info
 * 
 * @author Moueene Development Team
 * @version 1.0.0
 */

// Include the database configuration file
require_once __DIR__ . '/../config/database.php';

// Set content type to HTML
header('Content-Type: text/html; charset=utf-8');
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Database Connection Test - Moueene</title>
    <style>
        * {
            margin: 0;
            padding: 0;
            box-sizing: border-box;
        }
        
        body {
            font-family: 'Segoe UI', Tahoma, Geneva, Verdana, sans-serif;
            background: linear-gradient(135deg, #667eea 0%, #764ba2 100%);
            min-height: 100vh;
            display: flex;
            align-items: center;
            justify-content: center;
            padding: 20px;
        }
        
        .container {
            background: white;
            border-radius: 15px;
            box-shadow: 0 20px 60px rgba(0,0,0,0.3);
            padding: 40px;
            max-width: 600px;
            width: 100%;
        }
        
        h1 {
            color: #333;
            margin-bottom: 10px;
            font-size: 28px;
        }
        
        .subtitle {
            color: #666;
            margin-bottom: 30px;
            font-size: 14px;
        }
        
        .status-card {
            padding: 20px;
            border-radius: 10px;
            margin-bottom: 20px;
        }
        
        .success {
            background: #d4edda;
            border-left: 4px solid #28a745;
        }
        
        .error {
            background: #f8d7da;
            border-left: 4px solid #dc3545;
        }
        
        .info {
            background: #d1ecf1;
            border-left: 4px solid #17a2b8;
        }
        
        .status-icon {
            font-size: 48px;
            margin-bottom: 15px;
        }
        
        .success .status-icon { color: #28a745; }
        .error .status-icon { color: #dc3545; }
        
        h2 {
            color: #333;
            font-size: 20px;
            margin-bottom: 10px;
        }
        
        .message {
            color: #666;
            line-height: 1.6;
        }
        
        .config-table {
            width: 100%;
            border-collapse: collapse;
            margin-top: 15px;
        }
        
        .config-table th,
        .config-table td {
            padding: 12px;
            text-align: left;
            border-bottom: 1px solid #ddd;
        }
        
        .config-table th {
            background: #f8f9fa;
            font-weight: 600;
            color: #333;
            width: 40%;
        }
        
        .config-table td {
            color: #666;
        }
        
        .btn {
            display: inline-block;
            padding: 12px 30px;
            background: #667eea;
            color: white;
            text-decoration: none;
            border-radius: 5px;
            margin-top: 20px;
            transition: background 0.3s;
        }
        
        .btn:hover {
            background: #5568d3;
        }
        
        .footer {
            text-align: center;
            margin-top: 30px;
            padding-top: 20px;
            border-top: 1px solid #eee;
            color: #999;
            font-size: 12px;
        }
    </style>
</head>
<body>
    <div class="container">
        <h1>🗄️ Database Connection Test</h1>
        <p class="subtitle">Moueene Home Services Platform</p>
        
        <?php
        try {
            // Test the database connection
            $result = Database::testConnection();
            
            if ($result['status'] === 'success') {
                // Connection successful
                echo '<div class="status-card success">';
                echo '<div class="status-icon">✓</div>';
                echo '<h2>Connection Successful!</h2>';
                echo '<p class="message">' . htmlspecialchars($result['message']) . '</p>';
                echo '</div>';
                
                // Display configuration
                $config = Database::getConfig();
                echo '<div class="status-card info">';
                echo '<h2>Database Configuration</h2>';
                echo '<table class="config-table">';
                echo '<tr><th>Host</th><td>' . htmlspecialchars($config['host']) . '</td></tr>';
                echo '<tr><th>Database Name</th><td>' . htmlspecialchars($config['database']) . '</td></tr>';
                echo '<tr><th>Username</th><td>' . htmlspecialchars($config['username']) . '</td></tr>';
                echo '<tr><th>Charset</th><td>' . htmlspecialchars($config['charset']) . '</td></tr>';
                echo '<tr><th>Server Info</th><td>' . htmlspecialchars($result['server_info']) . '</td></tr>';
                echo '</table>';
                echo '</div>';
                
                // Test query to show table count
                $conn = Database::getConnection();
                $stmt = $conn->query("SELECT COUNT(*) as table_count FROM information_schema.tables WHERE table_schema = '" . $config['database'] . "'");
                $tableCount = $stmt->fetch();
                
                echo '<div class="status-card info">';
                echo '<h2>Database Statistics</h2>';
                echo '<table class="config-table">';
                echo '<tr><th>Total Tables</th><td>' . $tableCount['table_count'] . '</td></tr>';
                echo '<tr><th>Connection Time</th><td>' . date('Y-m-d H:i:s') . '</td></tr>';
                echo '<tr><th>PHP Version</th><td>' . phpversion() . '</td></tr>';
                echo '<tr><th>PDO Driver</th><td>MySQL</td></tr>';
                echo '</table>';
                echo '</div>';
                
            } else {
                // Connection failed
                echo '<div class="status-card error">';
                echo '<div class="status-icon">✗</div>';
                echo '<h2>Connection Failed</h2>';
                echo '<p class="message">' . htmlspecialchars($result['message']) . '</p>';
                echo '</div>';
                
                // Display configuration for debugging
                $config = Database::getConfig();
                echo '<div class="status-card info">';
                echo '<h2>Current Configuration</h2>';
                echo '<table class="config-table">';
                echo '<tr><th>Host</th><td>' . htmlspecialchars($config['host']) . '</td></tr>';
                echo '<tr><th>Database Name</th><td>' . htmlspecialchars($config['database']) . '</td></tr>';
                echo '<tr><th>Username</th><td>' . htmlspecialchars($config['username']) . '</td></tr>';
                echo '</table>';
                echo '<p class="message" style="margin-top: 15px;"><strong>Troubleshooting Tips:</strong></p>';
                echo '<ul style="margin-left: 20px; color: #666; line-height: 1.8;">';
                echo '<li>Verify MySQL server is running</li>';
                echo '<li>Check database credentials in config/database.php</li>';
                echo '<li>Ensure the database exists (run schema.sql first)</li>';
                echo '<li>Verify user has proper permissions</li>';
                echo '</ul>';
                echo '</div>';
            }
            
        } catch (Exception $e) {
            echo '<div class="status-card error">';
            echo '<div class="status-icon">✗</div>';
            echo '<h2>Error Occurred</h2>';
            echo '<p class="message">' . htmlspecialchars($e->getMessage()) . '</p>';
            echo '</div>';
        }
        ?>
        
        <div class="footer">
            <p>Moueene Database Test Utility v1.0.0</p>
            <p>Generated on <?php echo date('F j, Y, g:i a'); ?></p>
        </div>
    </div>
</body>
</html>
