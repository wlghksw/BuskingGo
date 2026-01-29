<?php
/**
 * 인증/로그인 API 엔드포인트
 * 웹과 모바일 앱에서 공통으로 사용할 수 있는 RESTful API
 */

session_start();
header('Content-Type: application/json; charset=utf-8');
header('Access-Control-Allow-Origin: *'); // 개발용, 실제 배포 시 특정 도메인으로 제한
header('Access-Control-Allow-Methods: GET, POST');
header('Access-Control-Allow-Headers: Content-Type');

// 세션에 사용자 데이터 초기화
if (!isset($_SESSION['users'])) {
    $_SESSION['users'] = [];
}

// GET 요청: 현재 사용자 정보 조회
if ($_SERVER['REQUEST_METHOD'] === 'GET') {
    $userId = $_SESSION['userId'] ?? null;
    $userType = $_SESSION['userType'] ?? null;
    
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
        $userData['email'] = $_SESSION['userEmail'] ?? '';
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
    
    // 이메일/비밀번호 로그인인지 사용자 유형 설정인지 확인
    if (isset($data['email']) && isset($data['password'])) {
        // 실제 로그인 (이메일/비밀번호)
        $email = trim(strtolower($data['email']));
        $password = $data['password'];
        
        // 사용자 찾기
        $foundUser = null;
        foreach ($_SESSION['users'] as $user) {
            if ($user['email'] === $email) {
                $foundUser = $user;
                break;
            }
        }
        
        if (!$foundUser) {
            http_response_code(401);
            echo json_encode([
                'success' => false,
                'message' => '이메일 또는 비밀번호가 올바르지 않습니다.'
            ], JSON_UNESCAPED_UNICODE);
            exit;
        }
        
        // 비밀번호 확인
        if (!password_verify($password, $foundUser['password'])) {
            http_response_code(401);
            echo json_encode([
                'success' => false,
                'message' => '이메일 또는 비밀번호가 올바르지 않습니다.'
            ], JSON_UNESCAPED_UNICODE);
            exit;
        }
        
        // 로그인 성공 - 세션에 사용자 정보 저장
        $_SESSION['userId'] = $foundUser['id'];
        $_SESSION['userEmail'] = $foundUser['email'];
        $_SESSION['userName'] = $foundUser['name'];
        $_SESSION['userType'] = $foundUser['userType'];
        
        // 마지막 로그인 시간 업데이트
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
                'email' => $foundUser['email'],
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
            'message' => '이메일/비밀번호 또는 사용자 유형이 필요합니다.'
        ], JSON_UNESCAPED_UNICODE);
        exit;
    }
}

// 로그아웃 (DELETE 메서드로 처리)
if ($_SERVER['REQUEST_METHOD'] === 'DELETE') {
    // 세션 데이터 초기화
    unset($_SESSION['userId']);
    unset($_SESSION['userEmail']);
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
