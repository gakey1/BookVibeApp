<?php
/**
 * Universal Database Configuration for XAMPP and MAMP
 * Auto-detects environment and sets appropriate connection parameters
 */

// Environment Detection Logic
function detectEnvironment() {
    // Check for InfinityFree/production hosting
    $isProduction = (
        strpos($_SERVER['HTTP_HOST'] ?? '', 'infinityfree') !== false ||
        strpos($_SERVER['HTTP_HOST'] ?? '', 'epizy') !== false ||
        strpos(__DIR__, 'htdocs') !== false && !is_dir('/Applications/MAMP/') && !is_dir('C:/xampp/')
    );

    // Check for MAMP indicators
    $isMAMP = (
        strpos(__DIR__, '/MAMP/') !== false ||
        is_dir('/Applications/MAMP/') ||
        file_exists('/Applications/MAMP/conf/apache/httpd.conf')
    );

    // Check for XAMPP indicators
    $isXAMPP = (
        strpos(__DIR__, '/xampp/') !== false ||
        is_dir('/opt/lampp/') ||
        is_dir('C:/xampp/') ||
        file_exists('/opt/lampp/etc/httpd.conf')
    );

    if ($isProduction) return 'PRODUCTION';
    if ($isMAMP) return 'MAMP';
    if ($isXAMPP) return 'XAMPP';

    // Default fallback
    return 'UNKNOWN';
}

// Get environment and set configuration
$environment = detectEnvironment();

switch ($environment) {
    case 'PRODUCTION':
        $host = 'sql100.infinityfree.com';
        $port = '3306';
        $user = 'if0_40591059';
        $pass = 'OTDpamspKaEU2qA';
        $db = 'if0_40591059_bookvibe';
        break;

    case 'MAMP':
        $host = 'localhost';
        $port = '8889';
        $user = 'root';
        $pass = 'root';
        $db = 'bookvibe';
        break;

    case 'XAMPP':
        $host = 'localhost';
        $port = '3307';
        $user = 'root';
        $pass = '';
        $db = 'bookvibe';
        break;

    default:
        $host = 'localhost';
        $port = '3307';
        $user = 'root';
        $pass = '';
        $db = 'bookvibe';
        break;
}
$charset = 'utf8mb4';
$dsn = "mysql:host=$host;port=$port;dbname=$db;charset=$charset";

$options = [
    PDO::ATTR_ERRMODE => PDO::ERRMODE_EXCEPTION,
    PDO::ATTR_DEFAULT_FETCH_MODE => PDO::FETCH_ASSOC,
];

try {
    $pdo = new PDO($dsn, $user, $pass, $options);
    // Optional: Log successful connection for debugging
    // error_log("Database connected successfully using $environment configuration");
} catch (PDOException $e) {
    $error_msg = "Database connection failed ($environment): " . $e->getMessage();
    error_log($error_msg);
    die($error_msg);
}

/**
 * Database Singleton Class
 * Provides consistent interface for database operations
 */
class Database {
    private static $instance = null;
    private $pdo;

    private function __construct() {
        global $pdo;
        $this->pdo = $pdo;
    }

    public static function getInstance() {
        if (self::$instance === null) {
            self::$instance = new self();
        }
        return self::$instance;
    }

    public function fetchAll($query, $params = []) {
        try {
            $stmt = $this->pdo->prepare($query);
            $stmt->execute($params);
            return $stmt->fetchAll();
        } catch (PDOException $e) {
            error_log("Database query error: " . $e->getMessage());
            return [];
        }
    }

    public function fetch($query, $params = []) {
        try {
            $stmt = $this->pdo->prepare($query);
            $stmt->execute($params);
            return $stmt->fetch();
        } catch (PDOException $e) {
            error_log("Database query error: " . $e->getMessage());
            return false;
        }
    }

    public function execute($query, $params = []) {
        try {
            $stmt = $this->pdo->prepare($query);
            return $stmt->execute($params);
        } catch (PDOException $e) {
            error_log("Database execute error: " . $e->getMessage());
            return false;
        }
    }

    public function lastInsertId() {
        return $this->pdo->lastInsertId();
    }

    public function getPdo() {
        return $this->pdo;
    }
}

// Session configuration
if (!defined('SESSION_TIMEOUT')) {
    define('SESSION_TIMEOUT', 3600); // 1 hour
}

// Initialize session if not started and we're in a web context
if (session_status() === PHP_SESSION_NONE && !headers_sent()) {
    ini_set('session.cookie_httponly', 1);
    ini_set('session.use_only_cookies', 1);
    ini_set('session.cookie_secure', isset($_SERVER['HTTPS']));
    session_start();
}
?> 
