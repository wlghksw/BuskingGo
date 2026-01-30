<?php
/**
 * 공연 예약 API 엔드포인트
 * 웹과 모바일 앱에서 공통으로 사용할 수 있는 RESTful API
 */

session_start();
header('Content-Type: application/json; charset=utf-8');
header('Access-Control-Allow-Origin: *'); // 개발용, 실제 배포 시 특정 도메인으로 제한
header('Access-Control-Allow-Methods: GET, POST, PUT, DELETE');
header('Access-Control-Allow-Headers: Content-Type');

require_once __DIR__ . '/../config/constants.php';

// 세션에 예약 데이터 초기화 (없는 경우)
if (!isset($_SESSION['bookings'])) {
    $_SESSION['bookings'] = [];
}

// GET 요청: 예약 목록 조회
if ($_SERVER['REQUEST_METHOD'] === 'GET') {
    $organizerName = $_GET['organizerName'] ?? '';
    $status = $_GET['status'] ?? '';
    $date = $_GET['date'] ?? '';
    
    $bookings = $_SESSION['bookings'];
    
    // 주최자명 필터링
    if ($organizerName) {
        $bookings = array_filter($bookings, function($b) use ($organizerName) {
            return isset($b['organizerName']) && stripos($b['organizerName'], $organizerName) !== false;
        });
    }
    
    // 상태 필터링
    if ($status) {
        $bookings = array_filter($bookings, function($b) use ($status) {
            return isset($b['status']) && $b['status'] === $status;
        });
    }
    
    // 날짜 필터링
    if ($date) {
        $bookings = array_filter($bookings, function($b) use ($date) {
            return isset($b['date']) && $b['date'] === $date;
        });
    }
    
    echo json_encode([
        'success' => true,
        'data' => array_values($bookings),
        'count' => count($bookings)
    ], JSON_UNESCAPED_UNICODE);
    exit;
}

// POST 요청: 새 예약 생성
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $data = json_decode(file_get_contents('php://input'), true);
    
    // POST 데이터가 없으면 form-data로 시도
    if (!$data) {
        $data = $_POST;
    }
    
    // 유효성 검사
    $requiredFields = ['organizerName', 'organizerType', 'location', 'date', 'startTime', 'endTime'];
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
    
    // 새 예약 데이터 생성
    $newBooking = [
        'id' => time() . rand(1000, 9999), // 임시 ID
        'organizerName' => $data['organizerName'],
        'organizerType' => $data['organizerType'],
        'location' => $data['location'],
        'lat' => $data['lat'] ?? null,
        'lng' => $data['lng'] ?? null,
        'date' => $data['date'],
        'startTime' => $data['startTime'],
        'endTime' => $data['endTime'],
        'additionalRequest' => $data['additionalRequest'] ?? '',
        'buskerId' => $data['buskerId'] ?? null, // 예약할 버스커 ID (선택)
        'status' => '대기중', // 대기중, 승인됨, 거절됨, 완료됨
        'createdAt' => date('Y-m-d H:i:s'),
        'createdBy' => $_SESSION['userType'] ?? 'viewer'
    ];
    
    // 세션에 저장
    $_SESSION['bookings'][] = $newBooking;
    
    echo json_encode([
        'success' => true,
        'message' => '예약이 신청되었습니다.',
        'data' => $newBooking
    ], JSON_UNESCAPED_UNICODE);
    exit;
}

// PUT 요청: 예약 정보 수정 (상태 변경 등)
if ($_SERVER['REQUEST_METHOD'] === 'PUT') {
    $data = json_decode(file_get_contents('php://input'), true);
    $id = $_GET['id'] ?? $data['id'] ?? null;
    
    if (!$id) {
        http_response_code(400);
        echo json_encode([
            'success' => false,
            'message' => '예약 ID가 필요합니다.'
        ], JSON_UNESCAPED_UNICODE);
        exit;
    }
    
    // 예약 찾기
    $found = false;
    foreach ($_SESSION['bookings'] as &$booking) {
        if ($booking['id'] == $id) {
            // 데이터 업데이트
            if (isset($data['organizerName'])) $booking['organizerName'] = $data['organizerName'];
            if (isset($data['organizerType'])) $booking['organizerType'] = $data['organizerType'];
            if (isset($data['location'])) $booking['location'] = $data['location'];
            if (isset($data['lat'])) $booking['lat'] = $data['lat'];
            if (isset($data['lng'])) $booking['lng'] = $data['lng'];
            if (isset($data['date'])) $booking['date'] = $data['date'];
            if (isset($data['startTime'])) $booking['startTime'] = $data['startTime'];
            if (isset($data['endTime'])) $booking['endTime'] = $data['endTime'];
            if (isset($data['additionalRequest'])) $booking['additionalRequest'] = $data['additionalRequest'];
            if (isset($data['buskerId'])) $booking['buskerId'] = $data['buskerId'];
            if (isset($data['status'])) $booking['status'] = $data['status'];
            
            $found = true;
            break;
        }
    }
    
    if (!$found) {
        http_response_code(404);
        echo json_encode([
            'success' => false,
            'message' => '예약을 찾을 수 없습니다.'
        ], JSON_UNESCAPED_UNICODE);
        exit;
    }
    
    echo json_encode([
        'success' => true,
        'message' => '예약 정보가 수정되었습니다.',
        'data' => $booking
    ], JSON_UNESCAPED_UNICODE);
    exit;
}

// DELETE 요청: 예약 취소
if ($_SERVER['REQUEST_METHOD'] === 'DELETE') {
    $id = $_GET['id'] ?? null;
    
    if (!$id) {
        http_response_code(400);
        echo json_encode([
            'success' => false,
            'message' => '예약 ID가 필요합니다.'
        ], JSON_UNESCAPED_UNICODE);
        exit;
    }
    
    // 예약 찾아서 삭제
    $found = false;
    foreach ($_SESSION['bookings'] as $key => $booking) {
        if ($booking['id'] == $id) {
            unset($_SESSION['bookings'][$key]);
            $_SESSION['bookings'] = array_values($_SESSION['bookings']); // 인덱스 재정렬
            $found = true;
            break;
        }
    }
    
    if (!$found) {
        http_response_code(404);
        echo json_encode([
            'success' => false,
            'message' => '예약을 찾을 수 없습니다.'
        ], JSON_UNESCAPED_UNICODE);
        exit;
    }
    
    echo json_encode([
        'success' => true,
        'message' => '예약이 취소되었습니다.'
    ], JSON_UNESCAPED_UNICODE);
    exit;
}

// 지원하지 않는 메서드
http_response_code(405);
echo json_encode([
    'success' => false,
    'message' => '지원하지 않는 HTTP 메서드입니다.'
], JSON_UNESCAPED_UNICODE);
