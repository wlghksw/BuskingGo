<?php
/**
 * 데이터베이스 연결 설정 파일
 * 실제 사용 시 환경변수나 별도 설정 파일로 관리하는 것을 권장합니다.
 */

// 데이터베이스 연결 정보
define('DB_HOST', 'localhost');
define('DB_NAME', 'buskinggo');
define('DB_USER', 'root');
define('DB_PASS', '');
define('DB_CHARSET', 'utf8mb4');

/**
 * PDO 데이터베이스 연결 생성
 * @return PDO|null
 */
function getDBConnection() {
    static $pdo = null;
    
    if ($pdo === null) {
        try {
            $dsn = "mysql:host=" . DB_HOST . ";dbname=" . DB_NAME . ";charset=" . DB_CHARSET;
            $options = [
                PDO::ATTR_ERRMODE            => PDO::ERRMODE_EXCEPTION,
                PDO::ATTR_DEFAULT_FETCH_MODE   => PDO::FETCH_ASSOC,
                PDO::ATTR_EMULATE_PREPARES    => false,
                PDO::MYSQL_ATTR_INIT_COMMAND => "SET NAMES " . DB_CHARSET
            ];
            
            $pdo = new PDO($dsn, DB_USER, DB_PASS, $options);
        } catch (PDOException $e) {
            error_log("Database connection failed: " . $e->getMessage());
            return null;
        }
    }
    
    return $pdo;
}

/**
 * MySQLi 데이터베이스 연결 생성 (대안)
 * @return mysqli|null
 */
function getMySQLiConnection() {
    static $mysqli = null;
    
    if ($mysqli === null) {
        $mysqli = new mysqli(DB_HOST, DB_USER, DB_PASS, DB_NAME);
        
        if ($mysqli->connect_error) {
            error_log("Database connection failed: " . $mysqli->connect_error);
            return null;
        }
        
        $mysqli->set_charset(DB_CHARSET);
    }
    
    return $mysqli;
}

/**
 * 데이터베이스 연결 테스트
 * @return bool
 */
function testDBConnection() {
    $pdo = getDBConnection();
    if ($pdo === null) {
        return false;
    }
    
    try {
        $stmt = $pdo->query("SELECT 1");
        return $stmt !== false;
    } catch (PDOException $e) {
        error_log("Database test failed: " . $e->getMessage());
        return false;
    }
}
