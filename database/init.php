<?php
/**
 * 데이터베이스 초기화 스크립트
 * schema_minimal.sql을 실행하여 데이터베이스와 테이블을 생성합니다.
 * 
 * 사용법: php database/init.php
 */

require_once __DIR__ . '/../config/database.php';

echo "데이터베이스 초기화를 시작합니다...\n\n";

// MySQL 연결 (데이터베이스 없이)
try {
    $pdo = getDBConnection(true); // 데이터베이스 없이 연결
    if (!$pdo) {
        throw new Exception("데이터베이스 서버에 연결할 수 없습니다.");
    }
    
    echo "✓ MySQL 서버에 연결되었습니다.\n";
    
    // SQL 파일 읽기
    $sqlFile = __DIR__ . '/schema_minimal.sql';
    if (!file_exists($sqlFile)) {
        die("✗ SQL 파일을 찾을 수 없습니다: {$sqlFile}\n");
    }
    
    $sql = file_get_contents($sqlFile);
    
    // SQL 문을 세미콜론으로 분리 (주석 처리 고려)
    $statements = array_filter(
        array_map('trim', explode(';', $sql)),
        function($stmt) {
            return !empty($stmt) && !preg_match('/^--/', $stmt);
        }
    );
    
    echo "✓ SQL 파일을 읽었습니다.\n\n";
    
    // 각 SQL 문 실행
    foreach ($statements as $statement) {
        if (empty(trim($statement))) {
            continue;
        }
        
        try {
            $pdo->exec($statement);
            // CREATE TABLE 문 감지
            if (preg_match('/CREATE TABLE\s+(\w+)/i', $statement, $matches)) {
                echo "✓ 테이블 생성됨: {$matches[1]}\n";
            } elseif (preg_match('/CREATE DATABASE/i', $statement)) {
                echo "✓ 데이터베이스 생성됨: buskinggo\n";
            }
        } catch (PDOException $e) {
            // 테이블이 이미 존재하는 경우는 무시
            if (strpos($e->getMessage(), 'already exists') === false && 
                strpos($e->getMessage(), 'Duplicate') === false) {
                echo "⚠ 경고: " . $e->getMessage() . "\n";
            }
        }
    }
    
    echo "\n✓ 데이터베이스 초기화가 완료되었습니다!\n";
    echo "\n연결 테스트 중...\n";
    
    // 연결 테스트
    if (testDBConnection()) {
        echo "✓ 데이터베이스 연결 성공!\n";
    } else {
        echo "✗ 데이터베이스 연결 실패. config/database.php의 설정을 확인하세요.\n";
    }
    
} catch (PDOException $e) {
    echo "✗ 오류 발생: " . $e->getMessage() . "\n";
    echo "\n다음 사항을 확인하세요:\n";
    echo "1. MySQL/MariaDB 서버가 실행 중인지 확인\n";
    echo "2. config/database.php의 DB_USER와 DB_PASS가 올바른지 확인\n";
    echo "3. MySQL 사용자에게 데이터베이스 생성 권한이 있는지 확인\n";
    exit(1);
}
