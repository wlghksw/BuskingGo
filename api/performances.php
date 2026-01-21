<?php
/**
 * 공연 목록 API 엔드포인트
 * 웹과 모바일 앱에서 공통으로 사용할 수 있는 RESTful API
 */

header('Content-Type: application/json; charset=utf-8');
header('Access-Control-Allow-Origin: *'); // 개발용, 실제 배포 시 특정 도메인으로 제한
header('Access-Control-Allow-Methods: GET, POST, PUT, DELETE');
header('Access-Control-Allow-Headers: Content-Type');

require_once __DIR__ . '/../config/constants.php';

// GET 요청: 공연 목록 조회
if ($_SERVER['REQUEST_METHOD'] === 'GET') {
    $location = $_GET['location'] ?? '';
    $status = $_GET['status'] ?? ''; // '진행중', '예정' 등
    
    $performances = $samplePerformances;
    
    // 지역 필터링
    if ($location) {
        $performances = array_filter($performances, function($p) use ($location) {
            return stripos($p['location'], $location) !== false;
        });
    }
    
    // 상태 필터링
    if ($status) {
        $performances = array_filter($performances, function($p) use ($status) {
            return $p['status'] === $status;
        });
    }
    
    echo json_encode([
        'success' => true,
        'data' => array_values($performances),
        'count' => count($performances)
    ], JSON_UNESCAPED_UNICODE);
    exit;
}

// POST 요청: 새 공연 등록 (향후 데이터베이스 연동 시 사용)
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $data = json_decode(file_get_contents('php://input'), true);
    
    // 유효성 검사
    if (!isset($data['buskerName']) || !isset($data['location'])) {
        http_response_code(400);
        echo json_encode([
            'success' => false,
            'message' => '필수 필드가 누락되었습니다.'
        ], JSON_UNESCAPED_UNICODE);
        exit;
    }
    
    // 여기에 데이터베이스 저장 로직 추가 예정
    // 예: $db->insert('performances', $data);
    
    echo json_encode([
        'success' => true,
        'message' => '공연이 등록되었습니다.',
        'data' => $data
    ], JSON_UNESCAPED_UNICODE);
    exit;
}

// 지원하지 않는 메서드
http_response_code(405);
echo json_encode([
    'success' => false,
    'message' => '지원하지 않는 HTTP 메서드입니다.'
], JSON_UNESCAPED_UNICODE);
