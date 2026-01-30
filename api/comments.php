<?php
/**
 * 커뮤니티 댓글 API 엔드포인트
 * 웹과 모바일 앱에서 공통으로 사용할 수 있는 RESTful API
 */

session_start();
header('Content-Type: application/json; charset=utf-8');
header('Access-Control-Allow-Origin: *'); // 개발용, 실제 배포 시 특정 도메인으로 제한
header('Access-Control-Allow-Methods: GET, POST, PUT, DELETE');
header('Access-Control-Allow-Headers: Content-Type');

require_once __DIR__ . '/../config/constants.php';

// 세션에 댓글 데이터 초기화
if (!isset($_SESSION['communityComments'])) {
    $_SESSION['communityComments'] = [];
}

// GET 요청: 댓글 목록 조회
if ($_SERVER['REQUEST_METHOD'] === 'GET') {
    $postId = $_GET['postId'] ?? null;
    $tab = $_GET['tab'] ?? 'free';
    
    if (!$postId) {
        http_response_code(400);
        echo json_encode([
            'success' => false,
            'message' => '게시글 ID가 필요합니다.'
        ], JSON_UNESCAPED_UNICODE);
        exit;
    }
    
    $comments = [];
    if (isset($_SESSION['communityComments'][$tab])) {
        $comments = array_filter($_SESSION['communityComments'][$tab], function($c) use ($postId) {
            return $c['postId'] == $postId;
        });
    }
    
    echo json_encode([
        'success' => true,
        'data' => array_values($comments),
        'count' => count($comments)
    ], JSON_UNESCAPED_UNICODE);
    exit;
}

// POST 요청: 새 댓글 작성
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $data = json_decode(file_get_contents('php://input'), true);
    
    // POST 데이터가 없으면 form-data로 시도
    if (!$data) {
        $data = $_POST;
    }
    
    // 유효성 검사
    if (!isset($data['postId']) || empty($data['postId'])) {
        http_response_code(400);
        echo json_encode([
            'success' => false,
            'message' => '게시글 ID가 필요합니다.'
        ], JSON_UNESCAPED_UNICODE);
        exit;
    }
    
    if (!isset($data['comment']) || empty($data['comment'])) {
        http_response_code(400);
        echo json_encode([
            'success' => false,
            'message' => '댓글 내용은 필수입니다.'
        ], JSON_UNESCAPED_UNICODE);
        exit;
    }
    
    $postId = (int)$data['postId'];
    $tab = $data['tab'] ?? 'free';
    $author = $_SESSION['userType'] === 'artist' ? '아티스트' : '사용자';
    
    // 새 댓글 데이터 생성
    $newComment = [
        'id' => time() . rand(1000, 9999),
        'postId' => $postId,
        'tab' => $tab,
        'author' => $author,
        'content' => $data['comment'],
        'date' => date('Y-m-d H:i:s')
    ];
    
    // 세션에 저장
    if (!isset($_SESSION['communityComments'][$tab])) {
        $_SESSION['communityComments'][$tab] = [];
    }
    $_SESSION['communityComments'][$tab][] = $newComment;
    
    // 게시글의 댓글 수 증가
    if (isset($_SESSION['communityPosts'][$tab])) {
        foreach ($_SESSION['communityPosts'][$tab] as &$post) {
            if ($post['id'] == $postId) {
                $post['comments'] = ($post['comments'] ?? 0) + 1;
                break;
            }
        }
    }
    
    echo json_encode([
        'success' => true,
        'message' => '댓글이 작성되었습니다.',
        'data' => $newComment
    ], JSON_UNESCAPED_UNICODE);
    exit;
}

// DELETE 요청: 댓글 삭제
if ($_SERVER['REQUEST_METHOD'] === 'DELETE') {
    $id = $_GET['id'] ?? null;
    $tab = $_GET['tab'] ?? 'free';
    
    if (!$id) {
        http_response_code(400);
        echo json_encode([
            'success' => false,
            'message' => '댓글 ID가 필요합니다.'
        ], JSON_UNESCAPED_UNICODE);
        exit;
    }
    
    // 댓글 찾아서 삭제
    $found = false;
    $comments = &$_SESSION['communityComments'][$tab];
    
    if (isset($comments)) {
        foreach ($comments as $key => $comment) {
            if ($comment['id'] == $id) {
                $postId = $comment['postId'];
                unset($comments[$key]);
                $comments = array_values($comments); // 인덱스 재정렬
                
                // 게시글의 댓글 수 감소
                if (isset($_SESSION['communityPosts'][$tab])) {
                    foreach ($_SESSION['communityPosts'][$tab] as &$post) {
                        if ($post['id'] == $postId) {
                            $post['comments'] = max(0, ($post['comments'] ?? 0) - 1);
                            break;
                        }
                    }
                }
                
                $found = true;
                break;
            }
        }
    }
    
    if (!$found) {
        http_response_code(404);
        echo json_encode([
            'success' => false,
            'message' => '댓글을 찾을 수 없습니다.'
        ], JSON_UNESCAPED_UNICODE);
        exit;
    }
    
    echo json_encode([
        'success' => true,
        'message' => '댓글이 삭제되었습니다.'
    ], JSON_UNESCAPED_UNICODE);
    exit;
}

// 지원하지 않는 메서드
http_response_code(405);
echo json_encode([
    'success' => false,
    'message' => '지원하지 않는 HTTP 메서드입니다.'
], JSON_UNESCAPED_UNICODE);
