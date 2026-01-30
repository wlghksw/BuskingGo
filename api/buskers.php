<?php
/**
 * 버스커 등록/조회 API 엔드포인트
 * 웹과 모바일 앱에서 공통으로 사용할 수 있는 RESTful API
 */

session_start();
header('Content-Type: application/json; charset=utf-8');
header('Access-Control-Allow-Origin: *'); // 개발용, 실제 배포 시 특정 도메인으로 제한
header('Access-Control-Allow-Methods: GET, POST, PUT, DELETE');
header('Access-Control-Allow-Headers: Content-Type');

require_once __DIR__ . '/../config/constants.php';

// 세션에 버스커 데이터 초기화 (없는 경우)
if (!isset($_SESSION['buskers'])) {
    $_SESSION['buskers'] = [];
}

// GET 요청: 버스커 목록 조회
if ($_SERVER['REQUEST_METHOD'] === 'GET') {
    $location = $_GET['location'] ?? '';
    $name = $_GET['name'] ?? '';
    
    $buskers = $_SESSION['buskers'];
    
    // 지역 필터링
    if ($location) {
        $buskers = array_filter($buskers, function($b) use ($location) {
            return isset($b['preferredLocation']) && stripos($b['preferredLocation'], $location) !== false;
        });
    }
    
    // 이름 필터링
    if ($name) {
        $buskers = array_filter($buskers, function($b) use ($name) {
            return isset($b['name']) && stripos($b['name'], $name) !== false;
        });
    }
    
    echo json_encode([
        'success' => true,
        'data' => array_values($buskers),
        'count' => count($buskers)
    ], JSON_UNESCAPED_UNICODE);
    exit;
}

// POST 요청: 새 버스커 등록
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $data = json_decode(file_get_contents('php://input'), true);
    
    // POST 데이터가 없으면 form-data로 시도
    if (!$data) {
        $data = $_POST;
    }
    
    // 유효성 검사
    if (!isset($data['name']) || empty($data['name'])) {
        http_response_code(400);
        echo json_encode([
            'success' => false,
            'message' => '팀/개인명은 필수입니다.'
        ], JSON_UNESCAPED_UNICODE);
        exit;
    }
    
    if (!isset($data['phone']) || empty($data['phone'])) {
        http_response_code(400);
        echo json_encode([
            'success' => false,
            'message' => '연락처는 필수입니다.'
        ], JSON_UNESCAPED_UNICODE);
        exit;
    }
    
    // 새 버스커 데이터 생성
    $newBusker = [
        'id' => time() . rand(1000, 9999), // 임시 ID
        'name' => $data['name'],
        'teamSize' => isset($data['teamSize']) ? (int)$data['teamSize'] : 1,
        'equipment' => $data['equipment'] ?? '',
        'phone' => $data['phone'],
        'bio' => $data['bio'] ?? '',
        'availableDays' => isset($data['availableDays']) ? (is_array($data['availableDays']) ? $data['availableDays'] : explode(',', $data['availableDays'])) : [],
        'preferredTime' => $data['preferredTime'] ?? '',
        'preferredLocation' => $data['preferredLocation'] ?? '',
        'createdAt' => date('Y-m-d H:i:s'),
        'rating' => 0,
        'performanceCount' => 0
    ];
    
    // 세션에 저장
    $_SESSION['buskers'][] = $newBusker;
    
    echo json_encode([
        'success' => true,
        'message' => '버스커가 등록되었습니다.',
        'data' => $newBusker
    ], JSON_UNESCAPED_UNICODE);
    exit;
}

// PUT 요청: 버스커 정보 수정
if ($_SERVER['REQUEST_METHOD'] === 'PUT') {
    $data = json_decode(file_get_contents('php://input'), true);
    $id = $_GET['id'] ?? $data['id'] ?? null;
    
    if (!$id) {
        http_response_code(400);
        echo json_encode([
            'success' => false,
            'message' => '버스커 ID가 필요합니다.'
        ], JSON_UNESCAPED_UNICODE);
        exit;
    }
    
    // 버스커 찾기
    $found = false;
    foreach ($_SESSION['buskers'] as &$busker) {
        if ($busker['id'] == $id) {
            // 데이터 업데이트
            if (isset($data['name'])) $busker['name'] = $data['name'];
            if (isset($data['teamSize'])) $busker['teamSize'] = (int)$data['teamSize'];
            if (isset($data['equipment'])) $busker['equipment'] = $data['equipment'];
            if (isset($data['phone'])) $busker['phone'] = $data['phone'];
            if (isset($data['bio'])) $busker['bio'] = $data['bio'];
            if (isset($data['availableDays'])) {
                $busker['availableDays'] = is_array($data['availableDays']) ? $data['availableDays'] : explode(',', $data['availableDays']);
            }
            if (isset($data['preferredTime'])) $busker['preferredTime'] = $data['preferredTime'];
            if (isset($data['preferredLocation'])) $busker['preferredLocation'] = $data['preferredLocation'];
            
            $found = true;
            break;
        }
    }
    
    if (!$found) {
        http_response_code(404);
        echo json_encode([
            'success' => false,
            'message' => '버스커를 찾을 수 없습니다.'
        ], JSON_UNESCAPED_UNICODE);
        exit;
    }
    
    echo json_encode([
        'success' => true,
        'message' => '버스커 정보가 수정되었습니다.',
        'data' => $busker
    ], JSON_UNESCAPED_UNICODE);
    exit;
}

// DELETE 요청: 버스커 삭제
if ($_SERVER['REQUEST_METHOD'] === 'DELETE') {
    $id = $_GET['id'] ?? null;
    
    if (!$id) {
        http_response_code(400);
        echo json_encode([
            'success' => false,
            'message' => '버스커 ID가 필요합니다.'
        ], JSON_UNESCAPED_UNICODE);
        exit;
    }
    
    // 버스커 찾아서 삭제
    $found = false;
    foreach ($_SESSION['buskers'] as $key => $busker) {
        if ($busker['id'] == $id) {
            unset($_SESSION['buskers'][$key]);
            $_SESSION['buskers'] = array_values($_SESSION['buskers']); // 인덱스 재정렬
            $found = true;
            break;
        }
    }
    
    if (!$found) {
        http_response_code(404);
        echo json_encode([
            'success' => false,
            'message' => '버스커를 찾을 수 없습니다.'
        ], JSON_UNESCAPED_UNICODE);
        exit;
    }
    
    echo json_encode([
        'success' => true,
        'message' => '버스커가 삭제되었습니다.'
    ], JSON_UNESCAPED_UNICODE);
    exit;
}

// 지원하지 않는 메서드
http_response_code(405);
echo json_encode([
    'success' => false,
    'message' => '지원하지 않는 HTTP 메서드입니다.'
], JSON_UNESCAPED_UNICODE);
