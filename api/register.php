<?php
/**
 * 회원가입 API 엔드포인트
 * 웹과 모바일 앱에서 공통으로 사용할 수 있는 RESTful API
 */

session_start();
header('Content-Type: application/json; charset=utf-8');
header('Access-Control-Allow-Origin: *'); // 개발용, 실제 배포 시 특정 도메인으로 제한
header('Access-Control-Allow-Methods: POST');
header('Access-Control-Allow-Headers: Content-Type');

require_once __DIR__ . '/../config/database.php';

$pdo = getDBConnection();

// POST 요청: 회원가입
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $data = json_decode(file_get_contents('php://input'), true);
    
    // POST 데이터가 없으면 form-data로 시도
    if (!$data) {
        $data = $_POST;
    }
    
    // 유효성 검사
    $requiredFields = ['user_id', 'password', 'name', 'userType'];
    foreach ($requiredFields as $field) {
        if (!isset($data[$field]) || empty($data[$field])) {
            http_response_code(400);
            echo json_encode([
                'success' => false,
                'message' => "필수 필드가 누락되었습니다: {$field}"
            ], JSON_UNESCAPED_UNICODE);
            exit;
        }
    }
    
    $user_id = trim($data['user_id']);
    $password = $data['password'];
    $name = $data['name'];
    $userType = $data['userType'];
    
    // 아이디 형식 검증 (영문, 숫자, 언더스코어만 허용, 4-20자)
    if (!preg_match('/^[a-zA-Z0-9_]{4,20}$/', $user_id)) {
        http_response_code(400);
        echo json_encode([
            'success' => false,
            'message' => '아이디는 4-20자의 영문, 숫자, 언더스코어만 사용 가능합니다.'
        ], JSON_UNESCAPED_UNICODE);
        exit;
    }
    
    // 비밀번호 강도 검증 (8자 이상, 영문+숫자)
    if (strlen($password) < 8) {
        http_response_code(400);
        echo json_encode([
            'success' => false,
            'message' => '비밀번호는 최소 8자 이상이어야 합니다.'
        ], JSON_UNESCAPED_UNICODE);
        exit;
    }
    
    if (!preg_match('/[a-zA-Z]/', $password) || !preg_match('/[0-9]/', $password)) {
        http_response_code(400);
        echo json_encode([
            'success' => false,
            'message' => '비밀번호는 영문과 숫자를 포함해야 합니다.'
        ], JSON_UNESCAPED_UNICODE);
        exit;
    }
    
    // 사용자 유형 검증
    if (!in_array($userType, ['viewer', 'artist'])) {
        http_response_code(400);
        echo json_encode([
            'success' => false,
            'message' => '유효하지 않은 사용자 유형입니다. (viewer 또는 artist만 가능)'
        ], JSON_UNESCAPED_UNICODE);
        exit;
    }
    
    // 중복 아이디 확인 (데이터베이스 우선)
    if ($pdo) {
        try {
            $stmt = $pdo->prepare("SELECT id FROM users WHERE user_id = ?");
            $stmt->execute([$user_id]);
            if ($stmt->fetch()) {
                http_response_code(409);
                echo json_encode([
                    'success' => false,
                    'message' => '이미 사용 중인 아이디입니다.'
                ], JSON_UNESCAPED_UNICODE);
                exit;
            }
        } catch (PDOException $e) {
            error_log("Database error in register.php: " . $e->getMessage());
        }
    }
    
    // 세션 기반 중복 확인 (폴백)
    if (!isset($_SESSION['users'])) {
        $_SESSION['users'] = [];
    }
    foreach ($_SESSION['users'] as $user) {
        if ($user['user_id'] === $user_id) {
            http_response_code(409);
            echo json_encode([
                'success' => false,
                'message' => '이미 사용 중인 아이디입니다.'
            ], JSON_UNESCAPED_UNICODE);
            exit;
        }
    }
    
    // 사용자 유형별 필수 필드 검증
    if ($userType === 'artist') {
        if (empty($data['teamName']) || empty($data['contactPhone']) || empty($data['activityLocation'])) {
            http_response_code(400);
            echo json_encode([
                'success' => false,
                'message' => '버스커 필수 정보를 모두 입력해주세요. (팀명/예명, 대표 연락처, 활동 지역)'
            ], JSON_UNESCAPED_UNICODE);
            exit;
        }
        
        if (empty($data['performanceGenres']) || !is_array($data['performanceGenres']) || count($data['performanceGenres']) === 0) {
            http_response_code(400);
            echo json_encode([
                'success' => false,
                'message' => '최소 1개 이상의 공연 장르를 선택해주세요.'
            ], JSON_UNESCAPED_UNICODE);
            exit;
        }
    }
    
    // 비밀번호 해싱
    $hashedPassword = password_hash($password, PASSWORD_DEFAULT);
    
    // 데이터베이스에 저장 시도
    if ($pdo) {
        try {
            $stmt = $pdo->prepare("INSERT INTO users (user_id, password, name, user_type, phone, interested_location) VALUES (?, ?, ?, ?, ?, ?)");
            $stmt->execute([
                $user_id,
                $hashedPassword,
                $name,
                $userType,
                $data['phone'] ?? null,
                $data['interestedLocation'] ?? null
            ]);
            
            $userId = $pdo->lastInsertId();
            
            // 버스커인 경우 buskers 테이블에도 추가
            if ($userType === 'artist' && !empty($data['teamName'])) {
                try {
                    $buskerStmt = $pdo->prepare("INSERT INTO buskers (user_id, name, phone, preferred_location) VALUES (?, ?, ?, ?)");
                    $buskerStmt->execute([
                        $userId,
                        $data['teamName'],
                        $data['contactPhone'] ?? $data['phone'] ?? '',
                        $data['activityLocation'] ?? null
                    ]);
                } catch (PDOException $e) {
                    error_log("Error creating busker record: " . $e->getMessage());
                }
            }
            
            // 회원가입 성공 시 자동 로그인
            $_SESSION['userId'] = $userId;
            $_SESSION['user_id'] = $user_id;
            $_SESSION['userName'] = $name;
            $_SESSION['userType'] = $userType;
            $_SESSION['favorites'] = [];
            $_SESSION['selectedLocation'] = '';
            
            echo json_encode([
                'success' => true,
                'message' => '회원가입이 완료되었습니다.',
                'data' => [
                    'id' => $userId,
                    'user_id' => $user_id,
                    'name' => $name,
                    'userType' => $userType
                ]
            ], JSON_UNESCAPED_UNICODE);
            exit;
        } catch (PDOException $e) {
            error_log("Database error in register.php: " . $e->getMessage());
            // 데이터베이스 저장 실패 시 세션 기반으로 폴백
        }
    }
    
    // 세션 기반 저장 (폴백)
    $newUser = [
        'id' => time() . rand(1000, 9999),
        'user_id' => $user_id,
        'password' => $hashedPassword,
        'name' => $name,
        'userType' => $userType,
        'phone' => $data['phone'] ?? '',
        'createdAt' => date('Y-m-d H:i:s'),
        'lastLoginAt' => null,
        'interestedLocation' => $data['interestedLocation'] ?? ''
    ];
    
    $_SESSION['users'][] = $newUser;
    
    // 회원가입 성공 시 자동 로그인
    $_SESSION['userId'] = $newUser['id'];
    $_SESSION['user_id'] = $user_id;
    $_SESSION['userName'] = $name;
    $_SESSION['userType'] = $userType;
    $_SESSION['favorites'] = [];
    $_SESSION['selectedLocation'] = '';
    
    // 비밀번호는 응답에서 제외
    unset($newUser['password']);
    
    echo json_encode([
        'success' => true,
        'message' => '회원가입이 완료되었습니다.',
        'data' => $newUser
    ], JSON_UNESCAPED_UNICODE);
    exit;
}

// 지원하지 않는 메서드
http_response_code(405);
echo json_encode([
    'success' => false,
    'message' => '지원하지 않는 HTTP 메서드입니다.'
], JSON_UNESCAPED_UNICODE);
