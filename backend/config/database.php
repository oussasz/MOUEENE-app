<?php
/**
 * Database Configuration File
 * Moueene - Home Services Platform
 * 
 * This file manages database connection settings and provides
 * a singleton PDO connection instance for the application.
 * 
 * @author Moueene Development Team
 * @version 1.0.0
 * @date 2026-01-26
 */

class Database {
    // Database credentials
    private static $host = 'localhost';
    private static $db_name = 'moueene_db';
    private static $username = 'root';
    private static $password = '';
    private static $charset = 'utf8mb4';
    
    /**
     * Load environment variables from .env file
     */
    private static function loadEnv() {
        $envFile = __DIR__ . '/.env';
        if (file_exists($envFile)) {
            $lines = file($envFile, FILE_IGNORE_NEW_LINES | FILE_SKIP_EMPTY_LINES);
            foreach ($lines as $line) {
                if (strpos(trim($line), '#') === 0) continue;
                list($key, $value) = explode('=', $line, 2);
                $key = trim($key);
                $value = trim($value);
                
                switch ($key) {
                    case 'DB_HOST':
                        self::$host = $value;
                        break;
                    case 'DB_NAME':
                        self::$db_name = $value;
                        break;
                    case 'DB_USER':
                        self::$username = $value;
                        break;
                    case 'DB_PASSWORD':
                        self::$password = $value;
                        break;
                    case 'DB_CHARSET':
                        self::$charset = $value;
                        break;
                }
            }
        }
    }
    
    // PDO connection instance
    private static $connection = null;
    
    /**
     * Private constructor to prevent direct instantiation
     */
    private function __construct() {}
    
    /**
     * Get database connection using singleton pattern
     * 
     * @return PDO|null Database connection object or null if connection fails
     */
    public static function getConnection() {
        if (self::$connection === null) {
            // Load environment variables
            self::loadEnv();
            
            try {
                $dsn = "mysql:host=" . self::$host . 
                       ";dbname=" . self::$db_name . 
                       ";charset=" . self::$charset;
                
                $options = [
                    PDO::ATTR_ERRMODE => PDO::ERRMODE_EXCEPTION,
                    PDO::ATTR_DEFAULT_FETCH_MODE => PDO::FETCH_ASSOC,
                    PDO::ATTR_EMULATE_PREPARES => false,
                    PDO::ATTR_PERSISTENT => false,
                    PDO::MYSQL_ATTR_INIT_COMMAND => "SET NAMES utf8mb4 COLLATE utf8mb4_unicode_ci"
                ];
                
                self::$connection = new PDO($dsn, self::$username, self::$password, $options);
                
                // Set timezone
                self::$connection->exec("SET time_zone = '+00:00'");
                
            } catch (PDOException $e) {
                // Log error (in production, log to file instead of displaying)
                error_log("Database Connection Error: " . $e->getMessage());
                // Return null instead of throwing to allow graceful degradation
                return null;
            }
        }
        
        return self::$connection;
    }
    
    /**
     * Close database connection
     */
    public static function closeConnection() {
        self::$connection = null;
    }
    
    /**
     * Test database connection
     * 
     * @return array Status and message
     */
    public static function testConnection() {
        try {
            $conn = self::getConnection();
            return [
                'status' => 'success',
                'message' => 'Database connection successful',
                'server_info' => $conn->getAttribute(PDO::ATTR_SERVER_INFO)
            ];
        } catch (PDOException $e) {
            return [
                'status' => 'error',
                'message' => $e->getMessage()
            ];
        }
    }
    
    /**
     * Get database configuration for environment setup
     * 
     * @return array Database configuration
     */
    public static function getConfig() {
        return [
            'host' => self::$host,
            'database' => self::$db_name,
            'username' => self::$username,
            'charset' => self::$charset
        ];
    }
    
    /**
     * Set database credentials (useful for testing or dynamic config)
     * 
     * @param string $host Database host
     * @param string $db_name Database name
     * @param string $username Database username
     * @param string $password Database password
     */
    public static function setCredentials($host, $db_name, $username, $password) {
        self::$host = $host;
        self::$db_name = $db_name;
        self::$username = $username;
        self::$password = $password;
        
        // Reset connection to use new credentials
        self::closeConnection();
    }
}

/**
 * Helper function to get database connection
 * 
 * @return PDO Database connection
 */
function getDB() {
    return Database::getConnection();
}

/**
 * Helper function to execute a prepared statement
 * 
 * @param string $sql SQL query
 * @param array $params Parameters for prepared statement
 * @return PDOStatement Executed statement
 */
function executeQuery($sql, $params = []) {
    try {
        $conn = Database::getConnection();
        $stmt = $conn->prepare($sql);
        $stmt->execute($params);
        return $stmt;
    } catch (PDOException $e) {
        error_log("Query Execution Error: " . $e->getMessage());
        throw $e;
    }
}

/**
 * Helper function to fetch all results
 * 
 * @param string $sql SQL query
 * @param array $params Parameters for prepared statement
 * @return array Results
 */
function fetchAll($sql, $params = []) {
    $stmt = executeQuery($sql, $params);
    return $stmt->fetchAll();
}

/**
 * Helper function to fetch single row
 * 
 * @param string $sql SQL query
 * @param array $params Parameters for prepared statement
 * @return mixed Single row or false
 */
function fetchOne($sql, $params = []) {
    $stmt = executeQuery($sql, $params);
    return $stmt->fetch();
}

/**
 * Helper function to get last insert ID
 * 
 * @return string Last insert ID
 */
function getLastInsertId() {
    $conn = Database::getConnection();
    return $conn->lastInsertId();
}
?>
