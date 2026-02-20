<?php
/**
 * 커뮤니티 게시글 API 엔드포인트
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

// 세션에 커뮤니티 게시글 데이터 초기화 (더미 데이터 제거됨)
if (!isset($_SESSION['communityPosts'])) {
    $_SESSION['communityPosts'] = [
        'free' => [],
        'recruit' => [],
        'collab' => []
    ];
}
if (!isset($_SESSION['communityComments'])) {
    $_SESSION['communityComments'] = [];
}

// GET 요청: 게시글 목록 조회
if ($_SERVER['REQUEST_METHOD'] === 'GET') {
    $tab = $_GET['tab'] ?? 'free'; // free, recruit, collab
    $postId = $_GET['postId'] ?? null;
    
    // 특정 게시글 조회 (댓글 포함)
    if ($postId) {
        $post = null;
        $tabPosts = $_SESSION['communityPosts'][$tab] ?? [];
        
        foreach ($tabPosts as $p) {
            if ($p['id'] == $postId) {
                $post = $p;
                break;
            }
        }
        
        if (!$post) {
            http_response_code(404);
            echo json_encode([
                'success' => false,
                'message' => '게시글을 찾을 수 없습니다.'
            ], JSON_UNESCAPED_UNICODE);
            exit;
        }
        
        // 댓글 가져오기
        $comments = [];
        if (isset($_SESSION['communityComments'][$tab])) {
            $comments = array_filter($_SESSION['communityComments'][$tab], function($c) use ($postId) {
                return $c['postId'] == $postId;
            });
        }
        
        echo json_encode([
            'success' => true,
            'data' => $post,
            'comments' => array_values($comments)
        ], JSON_UNESCAPED_UNICODE);
        exit;
    }
    
    // 게시글 목록 조회 (데이터베이스 우선)
    $posts = [];
    
    // 데이터베이스에서 게시글 조회
    if ($pdo) {
        try {
            $stmt = $pdo->prepare("SELECT * FROM community_posts WHERE tab = ? ORDER BY date DESC, id DESC");
            $stmt->execute([$tab]);
            $dbPosts = $stmt->fetchAll();
            
            foreach ($dbPosts as $post) {
                $posts[] = [
                    'id' => $post['id'],
                    'title' => $post['title'],
                    'content' => $post['content'],
                    'author' => $post['author'],
                    'date' => $post['date'],
                    'views' => $post['views'],
                    'comments' => $post['comments'],
                    'location' => $post['location'] ?? null,
                    'genre' => $post['genre'] ?? null,
                    'performanceDate' => $post['performance_date'] ?? null
                ];
            }
        } catch (PDOException $e) {
            error_log("Database error in community.php GET: " . $e->getMessage());
        }
    }
    
    // 세션에 저장된 게시글도 추가 (하위 호환성)
    $sessionPosts = $_SESSION['communityPosts'][$tab] ?? [];
    $posts = array_merge($posts, $sessionPosts);
    
    echo json_encode([
        'success' => true,
        'data' => array_values($posts),
        'count' => count($posts),
        'tab' => $tab
    ], JSON_UNESCAPED_UNICODE);
    exit;
}

// POST 요청: 새 게시글 작성
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $data = json_decode(file_get_contents('php://input'), true);
    
    // POST 데이터가 없으면 form-data로 시도
    if (!$data) {
        $data = $_POST;
    }
    
    // 유효성 검사
    if (!isset($data['title']) || empty($data['title'])) {
        http_response_code(400);
        echo json_encode([
            'success' => false,
            'message' => '제목은 필수입니다.'
        ], JSON_UNESCAPED_UNICODE);
        exit;
    }
    
    if (!isset($data['content']) || empty($data['content'])) {
        http_response_code(400);
        echo json_encode([
            'success' => false,
            'message' => '내용은 필수입니다.'
        ], JSON_UNESCAPED_UNICODE);
        exit;
    }
    
    $tab = $data['tab'] ?? 'free';
    $author = $_SESSION['userType'] === 'artist' ? '아티스트' : '사용자';
    
    // 새 게시글 데이터 생성
    $newPost = [
        'id' => time() . rand(1000, 9999),
        'title' => $data['title'],
        'content' => $data['content'],
        'author' => $author,
        'date' => date('Y-m-d'),
        'views' => 0,
        'comments' => 0
    ];
    
    // 탭별 추가 필드
    if ($tab === 'recruit') {
        $newPost['location'] = $data['location'] ?? '';
        $newPost['genre'] = $data['genre'] ?? '';
    } elseif ($tab === 'collab') {
        $newPost['performanceDate'] = $data['performanceDate'] ?? '';
        $newPost['location'] = $data['location'] ?? '';
    }
    
    // 세션에 저장
    if (!isset($_SESSION['communityPosts'][$tab])) {
        $_SESSION['communityPosts'][$tab] = [];
    }
    $_SESSION['communityPosts'][$tab][] = $newPost;
    
    echo json_encode([
        'success' => true,
        'message' => '게시글이 작성되었습니다.',
        'data' => $newPost
    ], JSON_UNESCAPED_UNICODE);
    exit;
}

// PUT 요청: 게시글 수정
if ($_SERVER['REQUEST_METHOD'] === 'PUT') {
    $data = json_decode(file_get_contents('php://input'), true);
    $id = $_GET['id'] ?? $data['id'] ?? null;
    $tab = $_GET['tab'] ?? $data['tab'] ?? 'free';
    
    if (!$id) {
        http_response_code(400);
        echo json_encode([
            'success' => false,
            'message' => '게시글 ID가 필요합니다.'
        ], JSON_UNESCAPED_UNICODE);
        exit;
    }
    
    // 게시글 찾기
    $found = false;
    $posts = &$_SESSION['communityPosts'][$tab];
    
    foreach ($posts as &$post) {
        if ($post['id'] == $id) {
            if (isset($data['title'])) $post['title'] = $data['title'];
            if (isset($data['content'])) $post['content'] = $data['content'];
            
            // 탭별 필드 업데이트
            if ($tab === 'recruit') {
                if (isset($data['location'])) $post['location'] = $data['location'];
                if (isset($data['genre'])) $post['genre'] = $data['genre'];
            } elseif ($tab === 'collab') {
                if (isset($data['performanceDate'])) $post['performanceDate'] = $data['performanceDate'];
                if (isset($data['location'])) $post['location'] = $data['location'];
            }
            
            $found = true;
            break;
        }
    }
    
    if (!$found) {
        http_response_code(404);
        echo json_encode([
            'success' => false,
            'message' => '게시글을 찾을 수 없습니다.'
        ], JSON_UNESCAPED_UNICODE);
        exit;
    }
    
    echo json_encode([
        'success' => true,
        'message' => '게시글이 수정되었습니다.',
        'data' => $post
    ], JSON_UNESCAPED_UNICODE);
    exit;
}

// DELETE 요청: 게시글 삭제
if ($_SERVER['REQUEST_METHOD'] === 'DELETE') {
    $id = $_GET['id'] ?? null;
    $tab = $_GET['tab'] ?? 'free';
    
    if (!$id) {
        http_response_code(400);
        echo json_encode([
            'success' => false,
            'message' => '게시글 ID가 필요합니다.'
        ], JSON_UNESCAPED_UNICODE);
        exit;
    }
    
    // 게시글 찾아서 삭제
    $found = false;
    $posts = &$_SESSION['communityPosts'][$tab];
    
    foreach ($posts as $key => $post) {
        if ($post['id'] == $id) {
            unset($posts[$key]);
            $posts = array_values($posts); // 인덱스 재정렬
            $found = true;
            break;
        }
    }
    
    if (!$found) {
        http_response_code(404);
        echo json_encode([
            'success' => false,
            'message' => '게시글을 찾을 수 없습니다.'
        ], JSON_UNESCAPED_UNICODE);
        exit;
    }
    
    echo json_encode([
        'success' => true,
        'message' => '게시글이 삭제되었습니다.'
    ], JSON_UNESCAPED_UNICODE);
    exit;
}

// 지원하지 않는 메서드
http_response_code(405);
echo json_encode([
    'success' => false,
    'message' => '지원하지 않는 HTTP 메서드입니다.'
], JSON_UNESCAPED_UNICODE);
