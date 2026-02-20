<?php
/**
 * 인증/로그인 API 엔드포인트
 * 웹과 모바일 앱에서 공통으로 사용할 수 있는 RESTful API
 */

session_start();
header('Content-Type: application/json; charset=utf-8');
header('Access-Control-Allow-Origin: *'); // 개발용, 실제 배포 시 특정 도메인으로 제한
header('Access-Control-Allow-Methods: GET, POST, DELETE');
header('Access-Control-Allow-Headers: Content-Type');

require_once __DIR__ . '/../config/database.php';

$pdo = getDBConnection();

// GET 요청: 현재 사용자 정보 조회
if ($_SERVER['REQUEST_METHOD'] === 'GET') {
    $userId = $_SESSION['userId'] ?? null;
    $userType = $_SESSION['userType'] ?? null;
    
    // 데이터베이스에서 사용자 정보 조회 (세션에 userId가 있는 경우)
    if ($userId && $pdo) {
        try {
            $stmt = $pdo->prepare("SELECT id, user_id, name, user_type FROM users WHERE id = ?");
            $stmt->execute([$userId]);
            $dbUser = $stmt->fetch();
            
            if ($dbUser) {
                $userType = $dbUser['user_type'];
                $_SESSION['user_id'] = $dbUser['user_id'];
                $_SESSION['userName'] = $dbUser['name'];
                $_SESSION['userType'] = $dbUser['user_type'];
            }
        } catch (PDOException $e) {
            error_log("Database error in auth.php GET: " . $e->getMessage());
        }
    }
    
    if (!$userId && !$userType) {
        echo json_encode([
            'success' => true,
            'authenticated' => false,
            'data' => null
        ], JSON_UNESCAPED_UNICODE);
        exit;
    }
    
    $userData = [
        'userType' => $userType,
        'favorites' => $_SESSION['favorites'] ?? [],
        'selectedLocation' => $_SESSION['selectedLocation'] ?? ''
    ];
    
    if ($userId) {
        $userData['userId'] = $userId;
        $userData['user_id'] = $_SESSION['user_id'] ?? '';
        $userData['name'] = $_SESSION['userName'] ?? '';
    }
    
    echo json_encode([
        'success' => true,
        'authenticated' => true,
        'data' => $userData
    ], JSON_UNESCAPED_UNICODE);
    exit;
}

// POST 요청: 로그인
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $data = json_decode(file_get_contents('php://input'), true);
    
    // POST 데이터가 없으면 form-data로 시도
    if (!$data) {
        $data = $_POST;
    }
    
    // 아이디/비밀번호 로그인인지 사용자 유형 설정인지 확인
    if (isset($data['user_id']) && isset($data['password'])) {
        // 실제 로그인 (아이디/비밀번호)
        $user_id = trim($data['user_id']);
        $password = $data['password'];
        
        // 데이터베이스에서 사용자 찾기
        $foundUser = null;
        
        if ($pdo) {
            try {
                $stmt = $pdo->prepare("SELECT * FROM users WHERE user_id = ?");
                $stmt->execute([$user_id]);
                $foundUser = $stmt->fetch();
                
                if ($foundUser && password_verify($password, $foundUser['password'])) {
                    // 로그인 성공 - 마지막 로그인 시간 업데이트 (컬럼이 있는 경우만)
                    try {
                        $updateStmt = $pdo->prepare("UPDATE users SET last_login_at = NOW() WHERE id = ?");
                        $updateStmt->execute([$foundUser['id']]);
                    } catch (PDOException $e) {
                        // last_login_at 컬럼이 없으면 무시
                        error_log("Could not update last_login_at: " . $e->getMessage());
                    }
                    
                    // 세션에 사용자 정보 저장
                    $_SESSION['userId'] = $foundUser['id'];
                    $_SESSION['user_id'] = $foundUser['user_id'];
                    $_SESSION['userName'] = $foundUser['name'];
                    $_SESSION['userType'] = $foundUser['user_type'];
                    
                    // 초기화 (없는 경우)
                    if (!isset($_SESSION['favorites'])) {
                        $_SESSION['favorites'] = [];
                    }
                    if (!isset($_SESSION['selectedLocation'])) {
                        $_SESSION['selectedLocation'] = '';
                    }
                    
                    echo json_encode([
                        'success' => true,
                        'message' => '로그인되었습니다.',
                        'data' => [
                            'userId' => $foundUser['id'],
                            'user_id' => $foundUser['user_id'],
                            'name' => $foundUser['name'],
                            'userType' => $foundUser['user_type'],
                            'favorites' => $_SESSION['favorites'],
                            'selectedLocation' => $_SESSION['selectedLocation']
                        ]
                    ], JSON_UNESCAPED_UNICODE);
                    exit;
                }
            } catch (PDOException $e) {
                error_log("Database error in auth.php: " . $e->getMessage());
            }
        }
        
        // 데이터베이스 연결 실패 또는 사용자를 찾지 못한 경우
        // 기존 세션 기반 로직으로 폴백 (하위 호환성)
        foreach ($_SESSION['users'] ?? [] as $user) {
            if ($user['user_id'] === $user_id) {
                $foundUser = $user;
                break;
            }
        }
        
        if (!$foundUser) {
            http_response_code(401);
            echo json_encode([
                'success' => false,
                'message' => '아이디 또는 비밀번호가 올바르지 않습니다.'
            ], JSON_UNESCAPED_UNICODE);
            exit;
        }
        
        // 비밀번호 확인
        if (!password_verify($password, $foundUser['password'])) {
            http_response_code(401);
            echo json_encode([
                'success' => false,
                'message' => '아이디 또는 비밀번호가 올바르지 않습니다.'
            ], JSON_UNESCAPED_UNICODE);
            exit;
        }
        
        // 로그인 성공 - 세션에 사용자 정보 저장
        $_SESSION['userId'] = $foundUser['id'];
        $_SESSION['user_id'] = $foundUser['user_id'];
        $_SESSION['userName'] = $foundUser['name'];
        $_SESSION['userType'] = $foundUser['userType'];
        
        // 마지막 로그인 시간 업데이트
        if (!isset($_SESSION['users'])) {
            $_SESSION['users'] = [];
        }
        foreach ($_SESSION['users'] as &$user) {
            if ($user['id'] == $foundUser['id']) {
                $user['lastLoginAt'] = date('Y-m-d H:i:s');
                break;
            }
        }
        
        // 초기화 (없는 경우)
        if (!isset($_SESSION['favorites'])) {
            $_SESSION['favorites'] = [];
        }
        if (!isset($_SESSION['selectedLocation'])) {
            $_SESSION['selectedLocation'] = '';
        }
        
        echo json_encode([
            'success' => true,
            'message' => '로그인되었습니다.',
            'data' => [
                'userId' => $foundUser['id'],
                'user_id' => $foundUser['user_id'],
                'name' => $foundUser['name'],
                'userType' => $foundUser['userType'],
                'favorites' => $_SESSION['favorites'],
                'selectedLocation' => $_SESSION['selectedLocation']
            ]
        ], JSON_UNESCAPED_UNICODE);
        exit;
        
    } elseif (isset($data['userType'])) {
        // 사용자 유형 설정 (기존 기능 유지)
        $userType = $data['userType'];
        
        // 사용자 유형 검증
        if (!in_array($userType, ['viewer', 'artist'])) {
            http_response_code(400);
            echo json_encode([
                'success' => false,
                'message' => '유효하지 않은 사용자 유형입니다. (viewer 또는 artist만 가능)'
            ], JSON_UNESCAPED_UNICODE);
            exit;
        }
        
        // 세션에 사용자 유형 저장
        $_SESSION['userType'] = $userType;
        
        // 초기화 (없는 경우)
        if (!isset($_SESSION['favorites'])) {
            $_SESSION['favorites'] = [];
        }
        if (!isset($_SESSION['selectedLocation'])) {
            $_SESSION['selectedLocation'] = '';
        }
        
        echo json_encode([
            'success' => true,
            'message' => '로그인되었습니다.',
            'data' => [
                'userType' => $userType,
                'favorites' => $_SESSION['favorites'],
                'selectedLocation' => $_SESSION['selectedLocation']
            ]
        ], JSON_UNESCAPED_UNICODE);
        exit;
    } else {
        http_response_code(400);
        echo json_encode([
            'success' => false,
            'message' => '아이디/비밀번호 또는 사용자 유형이 필요합니다.'
        ], JSON_UNESCAPED_UNICODE);
        exit;
    }
}

// 로그아웃 (DELETE 메서드로 처리)
if ($_SERVER['REQUEST_METHOD'] === 'DELETE') {
    // 세션 데이터 초기화
    unset($_SESSION['userId']);
    unset($_SESSION['user_id']);
    unset($_SESSION['userName']);
    $_SESSION['userType'] = null;
    $_SESSION['favorites'] = [];
    $_SESSION['selectedLocation'] = '';
    
    echo json_encode([
        'success' => true,
        'message' => '로그아웃되었습니다.'
    ], JSON_UNESCAPED_UNICODE);
    exit;
}

// 지원하지 않는 메서드
http_response_code(405);
echo json_encode([
    'success' => false,
    'message' => '지원하지 않는 HTTP 메서드입니다.'
], JSON_UNESCAPED_UNICODE);
