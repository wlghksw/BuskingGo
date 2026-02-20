<?php
/**
 * 관리자 API 엔드포인트
 * 게시글 및 사용자 관리 기능
 */

session_start();
header('Content-Type: application/json; charset=utf-8');
header('Access-Control-Allow-Origin: *');
header('Access-Control-Allow-Methods: GET, POST, PUT, DELETE');
header('Access-Control-Allow-Headers: Content-Type');

require_once __DIR__ . '/../config/database.php';

$pdo = getDBConnection();

// 관리자 권한 확인
function isAdmin() {
    global $pdo;
    
    if (!isset($_SESSION['userId'])) {
        return false;
    }
    
    if ($pdo) {
        try {
            $stmt = $pdo->prepare("SELECT user_type FROM users WHERE id = ?");
            $stmt->execute([$_SESSION['userId']]);
            $user = $stmt->fetch();
            
            return $user && $user['user_type'] === 'admin';
        } catch (PDOException $e) {
            error_log("Admin check error: " . $e->getMessage());
            return false;
        }
    }
    
    // 세션 기반 폴백
    return isset($_SESSION['userType']) && $_SESSION['userType'] === 'admin';
}

// 관리자 권한이 없으면 에러 반환
if (!isAdmin()) {
    http_response_code(403);
    echo json_encode([
        'success' => false,
        'message' => '관리자 권한이 필요합니다.'
    ], JSON_UNESCAPED_UNICODE);
    exit;
}

$action = $_GET['action'] ?? '';
$resource = $_GET['resource'] ?? '';

// GET 요청: 목록 조회
if ($_SERVER['REQUEST_METHOD'] === 'GET') {
    if ($resource === 'users') {
        // 사용자 목록 조회
        $page = max(1, (int)($_GET['page'] ?? 1));
        $limit = max(1, min(100, (int)($_GET['limit'] ?? 20)));
        $offset = ($page - 1) * $limit;
        $search = $_GET['search'] ?? '';
        $userType = $_GET['user_type'] ?? '';
        
        $whereConditions = [];
        $params = [];
        
        if ($search) {
            $whereConditions[] = "(user_id LIKE ? OR name LIKE ?)";
            $params[] = "%{$search}%";
            $params[] = "%{$search}%";
        }
        
        if ($userType) {
            $whereConditions[] = "user_type = ?";
            $params[] = $userType;
        }
        
        $whereClause = !empty($whereConditions) ? "WHERE " . implode(" AND ", $whereConditions) : "";
        
        // 전체 개수 조회
        $countSql = "SELECT COUNT(*) FROM users {$whereClause}";
        $stmt = $pdo->prepare($countSql);
        $stmt->execute($params);
        $totalCount = (int)$stmt->fetchColumn();
        
        // 사용자 목록 조회
        $sql = "SELECT id, user_id, name, user_type, phone, interested_location, created_at FROM users {$whereClause} ORDER BY id DESC LIMIT ? OFFSET ?";
        $stmt = $pdo->prepare($sql);
        $params[] = $limit;
        $params[] = $offset;
        $stmt->execute($params);
        $users = $stmt->fetchAll();
        
        echo json_encode([
            'success' => true,
            'data' => $users,
            'pagination' => [
                'page' => $page,
                'limit' => $limit,
                'total' => $totalCount,
                'totalPages' => ceil($totalCount / $limit)
            ]
        ], JSON_UNESCAPED_UNICODE);
        exit;
        
    } elseif ($resource === 'posts') {
        // 게시글 목록 조회
        $page = max(1, (int)($_GET['page'] ?? 1));
        $limit = max(1, min(100, (int)($_GET['limit'] ?? 20)));
        $offset = ($page - 1) * $limit;
        $tab = $_GET['tab'] ?? '';
        $search = $_GET['search'] ?? '';
        
        $whereConditions = [];
        $params = [];
        
        if ($tab) {
            $whereConditions[] = "tab = ?";
            $params[] = $tab;
        }
        
        if ($search) {
            $whereConditions[] = "(title LIKE ? OR content LIKE ? OR author LIKE ?)";
            $params[] = "%{$search}%";
            $params[] = "%{$search}%";
            $params[] = "%{$search}%";
        }
        
        $whereClause = !empty($whereConditions) ? "WHERE " . implode(" AND ", $whereConditions) : "";
        
        // 전체 개수 조회
        $countSql = "SELECT COUNT(*) FROM community_posts {$whereClause}";
        $stmt = $pdo->prepare($countSql);
        $stmt->execute($params);
        $totalCount = (int)$stmt->fetchColumn();
        
        // 게시글 목록 조회
        $sql = "SELECT cp.*, u.user_id as author_user_id FROM community_posts cp LEFT JOIN users u ON cp.user_id = u.id {$whereClause} ORDER BY cp.id DESC LIMIT ? OFFSET ?";
        $stmt = $pdo->prepare($sql);
        $params[] = $limit;
        $params[] = $offset;
        $stmt->execute($params);
        $posts = $stmt->fetchAll();
        
        echo json_encode([
            'success' => true,
            'data' => $posts,
            'pagination' => [
                'page' => $page,
                'limit' => $limit,
                'total' => $totalCount,
                'totalPages' => ceil($totalCount / $limit)
            ]
        ], JSON_UNESCAPED_UNICODE);
        exit;
        
    } elseif ($resource === 'bookings') {
        // 예약 목록 조회
        $page = max(1, (int)($_GET['page'] ?? 1));
        $limit = max(1, min(100, (int)($_GET['limit'] ?? 20)));
        $offset = ($page - 1) * $limit;
        $status = $_GET['status'] ?? '';
        
        $whereConditions = [];
        $params = [];
        
        if ($status) {
            $whereConditions[] = "status = ?";
            $params[] = $status;
        }
        
        $whereClause = !empty($whereConditions) ? "WHERE " . implode(" AND ", $whereConditions) : "";
        
        // 전체 개수 조회
        $countSql = "SELECT COUNT(*) FROM bookings {$whereClause}";
        $stmt = $pdo->prepare($countSql);
        $stmt->execute($params);
        $totalCount = (int)$stmt->fetchColumn();
        
        // 예약 목록 조회
        $sql = "SELECT * FROM bookings {$whereClause} ORDER BY id DESC LIMIT ? OFFSET ?";
        $stmt = $pdo->prepare($sql);
        $params[] = $limit;
        $params[] = $offset;
        $stmt->execute($params);
        $bookings = $stmt->fetchAll();
        
        echo json_encode([
            'success' => true,
            'data' => $bookings,
            'pagination' => [
                'page' => $page,
                'limit' => $limit,
                'total' => $totalCount,
                'totalPages' => ceil($totalCount / $limit)
            ]
        ], JSON_UNESCAPED_UNICODE);
        exit;
    }
}

// DELETE 요청: 삭제
if ($_SERVER['REQUEST_METHOD'] === 'DELETE') {
    $id = $_GET['id'] ?? null;
    
    if (!$id) {
        http_response_code(400);
        echo json_encode([
            'success' => false,
            'message' => 'ID가 필요합니다.'
        ], JSON_UNESCAPED_UNICODE);
        exit;
    }
    
    if ($resource === 'users') {
        // 사용자 삭제
        try {
            $stmt = $pdo->prepare("DELETE FROM users WHERE id = ?");
            $stmt->execute([$id]);
            
            if ($stmt->rowCount() > 0) {
                echo json_encode([
                    'success' => true,
                    'message' => '사용자가 삭제되었습니다.'
                ], JSON_UNESCAPED_UNICODE);
            } else {
                http_response_code(404);
                echo json_encode([
                    'success' => false,
                    'message' => '사용자를 찾을 수 없습니다.'
                ], JSON_UNESCAPED_UNICODE);
            }
        } catch (PDOException $e) {
            http_response_code(500);
            echo json_encode([
                'success' => false,
                'message' => '삭제 중 오류가 발생했습니다: ' . $e->getMessage()
            ], JSON_UNESCAPED_UNICODE);
        }
        exit;
        
    } elseif ($resource === 'posts') {
        // 게시글 삭제
        try {
            $stmt = $pdo->prepare("DELETE FROM community_posts WHERE id = ?");
            $stmt->execute([$id]);
            
            if ($stmt->rowCount() > 0) {
                echo json_encode([
                    'success' => true,
                    'message' => '게시글이 삭제되었습니다.'
                ], JSON_UNESCAPED_UNICODE);
            } else {
                http_response_code(404);
                echo json_encode([
                    'success' => false,
                    'message' => '게시글을 찾을 수 없습니다.'
                ], JSON_UNESCAPED_UNICODE);
            }
        } catch (PDOException $e) {
            http_response_code(500);
            echo json_encode([
                'success' => false,
                'message' => '삭제 중 오류가 발생했습니다: ' . $e->getMessage()
            ], JSON_UNESCAPED_UNICODE);
        }
        exit;
        
    } elseif ($resource === 'bookings') {
        // 예약 삭제
        try {
            $stmt = $pdo->prepare("DELETE FROM bookings WHERE id = ?");
            $stmt->execute([$id]);
            
            if ($stmt->rowCount() > 0) {
                echo json_encode([
                    'success' => true,
                    'message' => '예약이 삭제되었습니다.'
                ], JSON_UNESCAPED_UNICODE);
            } else {
                http_response_code(404);
                echo json_encode([
                    'success' => false,
                    'message' => '예약을 찾을 수 없습니다.'
                ], JSON_UNESCAPED_UNICODE);
            }
        } catch (PDOException $e) {
            http_response_code(500);
            echo json_encode([
                'success' => false,
                'message' => '삭제 중 오류가 발생했습니다: ' . $e->getMessage()
            ], JSON_UNESCAPED_UNICODE);
        }
        exit;
    }
}

// PUT 요청: 수정
if ($_SERVER['REQUEST_METHOD'] === 'PUT') {
    $data = json_decode(file_get_contents('php://input'), true);
    $id = $_GET['id'] ?? $data['id'] ?? null;
    
    if (!$id) {
        http_response_code(400);
        echo json_encode([
            'success' => false,
            'message' => 'ID가 필요합니다.'
        ], JSON_UNESCAPED_UNICODE);
        exit;
    }
    
    if ($resource === 'users') {
        // 사용자 정보 수정
        try {
            $updateFields = [];
            $params = [];
            
            if (isset($data['name'])) {
                $updateFields[] = "name = ?";
                $params[] = $data['name'];
            }
            if (isset($data['user_type'])) {
                $updateFields[] = "user_type = ?";
                $params[] = $data['user_type'];
            }
            if (isset($data['phone'])) {
                $updateFields[] = "phone = ?";
                $params[] = $data['phone'];
            }
            if (isset($data['interested_location'])) {
                $updateFields[] = "interested_location = ?";
                $params[] = $data['interested_location'];
            }
            
            if (!empty($updateFields)) {
                $params[] = $id;
                $sql = "UPDATE users SET " . implode(", ", $updateFields) . " WHERE id = ?";
                $stmt = $pdo->prepare($sql);
                $stmt->execute($params);
                
                // 업데이트된 사용자 조회
                $stmt = $pdo->prepare("SELECT id, user_id, name, user_type, phone, interested_location, created_at FROM users WHERE id = ?");
                $stmt->execute([$id]);
                $user = $stmt->fetch();
                
                if ($user) {
                    echo json_encode([
                        'success' => true,
                        'message' => '사용자 정보가 수정되었습니다.',
                        'data' => $user
                    ], JSON_UNESCAPED_UNICODE);
                } else {
                    http_response_code(404);
                    echo json_encode([
                        'success' => false,
                        'message' => '사용자를 찾을 수 없습니다.'
                    ], JSON_UNESCAPED_UNICODE);
                }
            } else {
                http_response_code(400);
                echo json_encode([
                    'success' => false,
                    'message' => '수정할 필드가 없습니다.'
                ], JSON_UNESCAPED_UNICODE);
            }
        } catch (PDOException $e) {
            http_response_code(500);
            echo json_encode([
                'success' => false,
                'message' => '수정 중 오류가 발생했습니다: ' . $e->getMessage()
            ], JSON_UNESCAPED_UNICODE);
        }
        exit;
        
    } elseif ($resource === 'posts') {
        // 게시글 수정
        try {
            $updateFields = [];
            $params = [];
            
            if (isset($data['title'])) {
                $updateFields[] = "title = ?";
                $params[] = $data['title'];
            }
            if (isset($data['content'])) {
                $updateFields[] = "content = ?";
                $params[] = $data['content'];
            }
            if (isset($data['tab'])) {
                $updateFields[] = "tab = ?";
                $params[] = $data['tab'];
            }
            
            if (!empty($updateFields)) {
                $params[] = $id;
                $sql = "UPDATE community_posts SET " . implode(", ", $updateFields) . " WHERE id = ?";
                $stmt = $pdo->prepare($sql);
                $stmt->execute($params);
                
                echo json_encode([
                    'success' => true,
                    'message' => '게시글이 수정되었습니다.'
                ], JSON_UNESCAPED_UNICODE);
            } else {
                http_response_code(400);
                echo json_encode([
                    'success' => false,
                    'message' => '수정할 필드가 없습니다.'
                ], JSON_UNESCAPED_UNICODE);
            }
        } catch (PDOException $e) {
            http_response_code(500);
            echo json_encode([
                'success' => false,
                'message' => '수정 중 오류가 발생했습니다: ' . $e->getMessage()
            ], JSON_UNESCAPED_UNICODE);
        }
        exit;
    }
}

// 지원하지 않는 메서드
http_response_code(405);
echo json_encode([
    'success' => false,
    'message' => '지원하지 않는 HTTP 메서드입니다.'
], JSON_UNESCAPED_UNICODE);
