<?php
/**
 * 공연 목록 API 엔드포인트
 * 웹과 모바일 앱에서 공통으로 사용할 수 있는 RESTful API
 */

session_start();
header('Content-Type: application/json; charset=utf-8');
header('Access-Control-Allow-Origin: *'); // 개발용, 실제 배포 시 특정 도메인으로 제한
header('Access-Control-Allow-Methods: GET, POST, PUT, DELETE');
header('Access-Control-Allow-Headers: Content-Type');

require_once __DIR__ . '/../config/database.php';
require_once __DIR__ . '/../config/constants.php';

$pdo = getDBConnection();

// GET 요청: 공연 목록 조회
if ($_SERVER['REQUEST_METHOD'] === 'GET') {
    $location = $_GET['location'] ?? '';
    $status = $_GET['status'] ?? ''; // '진행중', '예정' 등
    
    $performances = [];
    
    // 데이터베이스에서 공연 목록 조회
    if ($pdo) {
        try {
            $whereConditions = [];
            $params = [];
            
            if ($location) {
                $whereConditions[] = "location LIKE ?";
                $params[] = "%{$location}%";
            }
            
            if ($status) {
                $whereConditions[] = "status = ?";
                $params[] = $status;
            }
            
            $whereClause = !empty($whereConditions) ? "WHERE " . implode(" AND ", $whereConditions) : "";
            $sql = "SELECT * FROM performances {$whereClause} ORDER BY created_at DESC";
            
            $stmt = $pdo->prepare($sql);
            $stmt->execute($params);
            $dbPerformances = $stmt->fetchAll();
            
            // 데이터베이스 결과를 API 형식으로 변환
            foreach ($dbPerformances as $perf) {
                $performances[] = [
                    'id' => $perf['id'],
                    'buskerName' => $perf['busker_name'],
                    'location' => $perf['location'],
                    'lat' => $perf['lat'] ? (float)$perf['lat'] : null,
                    'lng' => $perf['lng'] ? (float)$perf['lng'] : null,
                    'startTime' => $perf['start_time'],
                    'endTime' => $perf['end_time'],
                    'status' => $perf['status'],
                    'image' => $perf['image'] ?? '🎤',
                    'rating' => $perf['rating'] ? (float)$perf['rating'] : 0,
                    'distance' => $perf['distance'] ? (float)$perf['distance'] : 0,
                    'description' => $perf['description'] ?? '',
                    'performanceDate' => $perf['performance_date'] ?? null,
                    'buskerId' => $perf['busker_id']
                ];
            }
        } catch (PDOException $e) {
            error_log("Database error in performances.php GET: " . $e->getMessage());
            // 데이터베이스 오류 시 빈 배열 반환
            $performances = [];
        }
    } else {
        // 데이터베이스 연결 실패 시 빈 배열 반환
        $performances = [];
    }
    
    // 세션에 저장된 공연도 추가 (하위 호환성)
    if (isset($_SESSION['performances']) && is_array($_SESSION['performances'])) {
        $performances = array_merge($performances, $_SESSION['performances']);
    }
    
    echo json_encode([
        'success' => true,
        'data' => array_values($performances),
        'count' => count($performances)
    ], JSON_UNESCAPED_UNICODE);
    exit;
}

// POST 요청: 새 공연 등록
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $data = json_decode(file_get_contents('php://input'), true);
    
    // POST 데이터가 없으면 form-data로 시도
    if (!$data) {
        $data = $_POST;
    }
    
    // 유효성 검사
    $requiredFields = ['buskerName', 'location', 'startTime', 'endTime'];
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
    
    // 데이터베이스에 저장 시도
    if ($pdo) {
        try {
            // 버스커 ID 찾기 (buskerName으로)
            $buskerId = null;
            if (isset($_SESSION['userId'])) {
                $stmt = $pdo->prepare("SELECT id FROM buskers WHERE user_id = ? LIMIT 1");
                $stmt->execute([$_SESSION['userId']]);
                $busker = $stmt->fetch();
                if ($busker) {
                    $buskerId = $busker['id'];
                }
            }
            
            $stmt = $pdo->prepare("INSERT INTO performances (busker_id, busker_name, location, lat, lng, start_time, end_time, performance_date, status, image, description) VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?)");
            $stmt->execute([
                $buskerId,
                $data['buskerName'],
                $data['location'],
                $data['lat'] ?? null,
                $data['lng'] ?? null,
                $data['startTime'],
                $data['endTime'],
                $data['performanceDate'] ?? date('Y-m-d'),
                $data['status'] ?? '예정',
                $data['image'] ?? '🎤',
                $data['description'] ?? ''
            ]);
            
            $performanceId = $pdo->lastInsertId();
            
            // 조회하여 반환
            $stmt = $pdo->prepare("SELECT * FROM performances WHERE id = ?");
            $stmt->execute([$performanceId]);
            $dbPerformance = $stmt->fetch();
            
            $newPerformance = [
                'id' => $dbPerformance['id'],
                'buskerName' => $dbPerformance['busker_name'],
                'location' => $dbPerformance['location'],
                'lat' => $dbPerformance['lat'] ? (float)$dbPerformance['lat'] : null,
                'lng' => $dbPerformance['lng'] ? (float)$dbPerformance['lng'] : null,
                'startTime' => $dbPerformance['start_time'],
                'endTime' => $dbPerformance['end_time'],
                'status' => $dbPerformance['status'],
                'image' => $dbPerformance['image'] ?? '🎤',
                'rating' => $dbPerformance['rating'] ? (float)$dbPerformance['rating'] : 0,
                'distance' => $dbPerformance['distance'] ? (float)$dbPerformance['distance'] : 0,
                'description' => $dbPerformance['description'] ?? '',
                'performanceDate' => $dbPerformance['performance_date'] ?? null,
                'buskerId' => $dbPerformance['busker_id']
            ];
            
            echo json_encode([
                'success' => true,
                'message' => '공연이 등록되었습니다.',
                'data' => $newPerformance
            ], JSON_UNESCAPED_UNICODE);
            exit;
        } catch (PDOException $e) {
            error_log("Database error in performances.php POST: " . $e->getMessage());
            http_response_code(500);
            echo json_encode([
                'success' => false,
                'message' => '공연 등록 중 오류가 발생했습니다: ' . $e->getMessage()
            ], JSON_UNESCAPED_UNICODE);
            exit;
        }
    } else {
        // 데이터베이스 연결 실패 시 세션에 저장 (폴백)
        $newPerformance = [
            'id' => time() . rand(1000, 9999),
            'buskerName' => $data['buskerName'],
            'location' => $data['location'],
            'lat' => $data['lat'] ?? null,
            'lng' => $data['lng'] ?? null,
            'startTime' => $data['startTime'],
            'endTime' => $data['endTime'],
            'status' => $data['status'] ?? '예정',
            'image' => $data['image'] ?? '🎤',
            'rating' => 0,
            'distance' => 0,
            'description' => $data['description'] ?? '',
            'performanceDate' => $data['performanceDate'] ?? date('Y-m-d'),
            'createdByUserId' => $_SESSION['userId'] ?? null
        ];
        
        if (!isset($_SESSION['performances'])) {
            $_SESSION['performances'] = [];
        }
        $_SESSION['performances'][] = $newPerformance;
        
        echo json_encode([
            'success' => true,
            'message' => '공연이 등록되었습니다.',
            'data' => $newPerformance
        ], JSON_UNESCAPED_UNICODE);
        exit;
    }
}

// 지원하지 않는 메서드
http_response_code(405);
echo json_encode([
    'success' => false,
    'message' => '지원하지 않는 HTTP 메서드입니다.'
], JSON_UNESCAPED_UNICODE);
