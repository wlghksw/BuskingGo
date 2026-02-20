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

require_once __DIR__ . '/../config/database.php';
require_once __DIR__ . '/../config/constants.php';

$pdo = getDBConnection();

// 세션에 예약 데이터 초기화 (없는 경우)
if (!isset($_SESSION['bookings'])) {
    $_SESSION['bookings'] = [];
}

// GET 요청: 예약 목록 조회
if ($_SERVER['REQUEST_METHOD'] === 'GET') {
    $organizerName = $_GET['organizerName'] ?? '';
    $status = $_GET['status'] ?? '';
    $date = $_GET['date'] ?? '';
    
    $bookings = [];
    
    // 데이터베이스에서 조회 시도
    if ($pdo) {
        try {
            $whereConditions = [];
            $params = [];
            
            if ($organizerName) {
                $whereConditions[] = "organizer_name LIKE ?";
                $params[] = "%{$organizerName}%";
            }
            
            if ($status) {
                $whereConditions[] = "status = ?";
                $params[] = $status;
            }
            
            if ($date) {
                $whereConditions[] = "date = ?";
                $params[] = $date;
            }
            
            $whereClause = !empty($whereConditions) ? "WHERE " . implode(" AND ", $whereConditions) : "";
            $sql = "SELECT * FROM bookings {$whereClause} ORDER BY created_at DESC";
            
            $stmt = $pdo->prepare($sql);
            $stmt->execute($params);
            $dbBookings = $stmt->fetchAll();
            
            // 데이터베이스 결과를 API 형식으로 변환
            foreach ($dbBookings as $booking) {
                $bookings[] = [
                    'id' => $booking['id'],
                    'organizerName' => $booking['organizer_name'],
                    'organizerType' => $booking['organizer_type'],
                    'location' => $booking['location'],
                    'lat' => $booking['lat'],
                    'lng' => $booking['lng'],
                    'date' => $booking['date'],
                    'startTime' => $booking['start_time'],
                    'endTime' => $booking['end_time'],
                    'additionalRequest' => $booking['additional_request'],
                    'buskerId' => $booking['busker_id'],
                    'status' => $booking['status'],
                    'createdAt' => $booking['created_at'],
                    'createdBy' => $booking['created_by']
                ];
            }
        } catch (PDOException $e) {
            error_log("Database error in bookings.php GET: " . $e->getMessage());
            // 폴백: 세션 데이터 사용
            $bookings = $_SESSION['bookings'];
        }
    } else {
        // 데이터베이스 연결 실패 시 세션 데이터 사용
        $bookings = $_SESSION['bookings'];
    }
    
    // 세션 데이터와 병합 (중복 제거)
    if (!empty($_SESSION['bookings'])) {
        $sessionIds = array_column($bookings, 'id');
        foreach ($_SESSION['bookings'] as $sessionBooking) {
            if (!in_array($sessionBooking['id'], $sessionIds)) {
                $bookings[] = $sessionBooking;
            }
        }
    }
    
    // 필터링 (세션 데이터에 대해서도)
    if ($organizerName) {
        $bookings = array_filter($bookings, function($b) use ($organizerName) {
            return isset($b['organizerName']) && stripos($b['organizerName'], $organizerName) !== false;
        });
    }
    
    if ($status) {
        $bookings = array_filter($bookings, function($b) use ($status) {
            return isset($b['status']) && $b['status'] === $status;
        });
    }
    
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
    
    // 데이터베이스에 저장 시도
    if ($pdo) {
        try {
            $stmt = $pdo->prepare("INSERT INTO bookings (organizer_name, organizer_type, busker_id, location, lat, lng, date, start_time, end_time, additional_request, status, created_by) VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?)");
            $stmt->execute([
                $data['organizerName'],
                $data['organizerType'],
                $data['buskerId'] ?? null,
                $data['location'],
                $data['lat'] ?? null,
                $data['lng'] ?? null,
                $data['date'],
                $data['startTime'],
                $data['endTime'],
                $data['additionalRequest'] ?? null,
                '대기중',
                $_SESSION['userType'] ?? 'viewer'
            ]);
            
            $bookingId = $pdo->lastInsertId();
            
            // 조회하여 반환
            $stmt = $pdo->prepare("SELECT * FROM bookings WHERE id = ?");
            $stmt->execute([$bookingId]);
            $dbBooking = $stmt->fetch();
            
            $newBooking = [
                'id' => $dbBooking['id'],
                'organizerName' => $dbBooking['organizer_name'],
                'organizerType' => $dbBooking['organizer_type'],
                'location' => $dbBooking['location'],
                'lat' => $dbBooking['lat'],
                'lng' => $dbBooking['lng'],
                'date' => $dbBooking['date'],
                'startTime' => $dbBooking['start_time'],
                'endTime' => $dbBooking['end_time'],
                'additionalRequest' => $dbBooking['additional_request'],
                'buskerId' => $dbBooking['busker_id'],
                'status' => $dbBooking['status'],
                'createdAt' => $dbBooking['created_at'],
                'createdBy' => $dbBooking['created_by']
            ];
            
            echo json_encode([
                'success' => true,
                'message' => '예약이 신청되었습니다.',
                'data' => $newBooking
            ], JSON_UNESCAPED_UNICODE);
            exit;
        } catch (PDOException $e) {
            error_log("Database error in bookings.php POST: " . $e->getMessage());
            // 폴백: 세션에 저장
        }
    }
    
    // 세션 기반 저장 (폴백)
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
        'buskerId' => $data['buskerId'] ?? null,
        'status' => '대기중',
        'createdAt' => date('Y-m-d H:i:s'),
        'createdBy' => $_SESSION['userType'] ?? 'viewer'
    ];
    
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
    
    // 데이터베이스에서 업데이트 시도
    if ($pdo) {
        try {
            $updateFields = [];
            $params = [];
            
            if (isset($data['organizerName'])) {
                $updateFields[] = "organizer_name = ?";
                $params[] = $data['organizerName'];
            }
            if (isset($data['organizerType'])) {
                $updateFields[] = "organizer_type = ?";
                $params[] = $data['organizerType'];
            }
            if (isset($data['location'])) {
                $updateFields[] = "location = ?";
                $params[] = $data['location'];
            }
            if (isset($data['lat'])) {
                $updateFields[] = "lat = ?";
                $params[] = $data['lat'];
            }
            if (isset($data['lng'])) {
                $updateFields[] = "lng = ?";
                $params[] = $data['lng'];
            }
            if (isset($data['date'])) {
                $updateFields[] = "date = ?";
                $params[] = $data['date'];
            }
            if (isset($data['startTime'])) {
                $updateFields[] = "start_time = ?";
                $params[] = $data['startTime'];
            }
            if (isset($data['endTime'])) {
                $updateFields[] = "end_time = ?";
                $params[] = $data['endTime'];
            }
            if (isset($data['additionalRequest'])) {
                $updateFields[] = "additional_request = ?";
                $params[] = $data['additionalRequest'];
            }
            if (isset($data['buskerId'])) {
                $updateFields[] = "busker_id = ?";
                $params[] = $data['buskerId'];
            }
            if (isset($data['status'])) {
                $updateFields[] = "status = ?";
                $params[] = $data['status'];
            }
            
            if (!empty($updateFields)) {
                $params[] = $id;
                $sql = "UPDATE bookings SET " . implode(", ", $updateFields) . " WHERE id = ?";
                $stmt = $pdo->prepare($sql);
                $stmt->execute($params);
                
                // 업데이트된 예약 조회
                $stmt = $pdo->prepare("SELECT * FROM bookings WHERE id = ?");
                $stmt->execute([$id]);
                $dbBooking = $stmt->fetch();
                
                if ($dbBooking) {
                    $booking = [
                        'id' => $dbBooking['id'],
                        'organizerName' => $dbBooking['organizer_name'],
                        'organizerType' => $dbBooking['organizer_type'],
                        'location' => $dbBooking['location'],
                        'lat' => $dbBooking['lat'],
                        'lng' => $dbBooking['lng'],
                        'date' => $dbBooking['date'],
                        'startTime' => $dbBooking['start_time'],
                        'endTime' => $dbBooking['end_time'],
                        'additionalRequest' => $dbBooking['additional_request'],
                        'buskerId' => $dbBooking['busker_id'],
                        'status' => $dbBooking['status'],
                        'createdAt' => $dbBooking['created_at'],
                        'createdBy' => $dbBooking['created_by']
                    ];
                    
                    echo json_encode([
                        'success' => true,
                        'message' => '예약 정보가 수정되었습니다.',
                        'data' => $booking
                    ], JSON_UNESCAPED_UNICODE);
                    exit;
                }
            }
        } catch (PDOException $e) {
            error_log("Database error in bookings.php PUT: " . $e->getMessage());
        }
    }
    
    // 세션 기반 업데이트 (폴백)
    $found = false;
    foreach ($_SESSION['bookings'] as &$booking) {
        if ($booking['id'] == $id) {
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
    
    // 데이터베이스에서 삭제 시도
    if ($pdo) {
        try {
            $stmt = $pdo->prepare("DELETE FROM bookings WHERE id = ?");
            $stmt->execute([$id]);
            
            if ($stmt->rowCount() > 0) {
                echo json_encode([
                    'success' => true,
                    'message' => '예약이 취소되었습니다.'
                ], JSON_UNESCAPED_UNICODE);
                exit;
            }
        } catch (PDOException $e) {
            error_log("Database error in bookings.php DELETE: " . $e->getMessage());
        }
    }
    
    // 세션 기반 삭제 (폴백)
    $found = false;
    foreach ($_SESSION['bookings'] as $key => $booking) {
        if ($booking['id'] == $id) {
            unset($_SESSION['bookings'][$key]);
            $_SESSION['bookings'] = array_values($_SESSION['bookings']);
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
