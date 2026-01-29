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

// 세션에 사용자 데이터 초기화
if (!isset($_SESSION['users'])) {
    $_SESSION['users'] = [];
}

// POST 요청: 회원가입
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $data = json_decode(file_get_contents('php://input'), true);
    
    // POST 데이터가 없으면 form-data로 시도
    if (!$data) {
        $data = $_POST;
    }
    
    // 유효성 검사
    $requiredFields = ['email', 'password', 'name', 'userType'];
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
    
    $email = trim(strtolower($data['email']));
    $password = $data['password'];
    $name = $data['name'];
    $userType = $data['userType'];
    
    // 이메일 형식 검증
    if (!filter_var($email, FILTER_VALIDATE_EMAIL)) {
        http_response_code(400);
        echo json_encode([
            'success' => false,
            'message' => '유효하지 않은 이메일 형식입니다.'
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
    
    // 중복 이메일 확인
    foreach ($_SESSION['users'] as $user) {
        if ($user['email'] === $email) {
            http_response_code(409);
            echo json_encode([
                'success' => false,
                'message' => '이미 등록된 이메일입니다.'
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
    
    // 새 사용자 데이터 생성
    $newUser = [
        'id' => time() . rand(1000, 9999),
        'email' => $email,
        'password' => $hashedPassword,
        'name' => $name,
        'userType' => $userType,
        'phone' => $data['phone'] ?? '',
        'createdAt' => date('Y-m-d H:i:s'),
        'lastLoginAt' => null,
        // 선택 정보
        'interestedLocation' => $data['interestedLocation'] ?? '',
        'emailNotification' => isset($data['emailNotification']) && $data['emailNotification'],
        'smsNotification' => isset($data['smsNotification']) && $data['smsNotification']
    ];
    
    // 사용자 유형별 추가 정보
    if ($userType === 'viewer') {
        $newUser['interestedGenres'] = $data['interestedGenres'] ?? [];
        $newUser['preferredTimeSlots'] = $data['preferredTimeSlots'] ?? [];
    } else if ($userType === 'artist') {
        $newUser['teamName'] = $data['teamName'];
        $newUser['performanceGenres'] = $data['performanceGenres'] ?? [];
        $newUser['contactPhone'] = $data['contactPhone'];
        $newUser['activityLocation'] = $data['activityLocation'];
    }
    
    // 세션에 저장
    $_SESSION['users'][] = $newUser;
    
    // 회원가입 성공 시 자동 로그인
    $_SESSION['userId'] = $newUser['id'];
    $_SESSION['userEmail'] = $email;
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
