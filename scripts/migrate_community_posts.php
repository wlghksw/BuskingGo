<?php
/**
 * constants.php의 커뮤니티 게시글 데이터를 DB로 마이그레이션하는 스크립트
 * 
 * 사용법:
 * php scripts/migrate_community_posts.php
 */

require_once __DIR__ . '/../config/database.php';
require_once __DIR__ . '/../config/constants.php';

$pdo = getDBConnection();

if (!$pdo) {
    die("데이터베이스 연결 실패!\n");
}

echo "커뮤니티 게시글 데이터 마이그레이션 시작...\n\n";

$migratedCount = 0;
$skippedCount = 0;

foreach ($communityPosts as $tab => $posts) {
    echo "탭: {$tab}\n";
    
    foreach ($posts as $post) {
        // 이미 존재하는 게시글인지 확인 (ID 기준)
        $stmt = $pdo->prepare("SELECT id FROM community_posts WHERE id = ?");
        $stmt->execute([$post['id']]);
        
        if ($stmt->fetch()) {
            echo "  - 게시글 ID {$post['id']} 이미 존재, 건너뜀\n";
            $skippedCount++;
            continue;
        }
        
        // DB에 삽입
        $stmt = $pdo->prepare("
            INSERT INTO community_posts 
            (id, author, tab, title, content, date, views, comments, location, genre, performance_date)
            VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?)
        ");
        
        try {
            $stmt->execute([
                $post['id'],
                $post['author'],
                $tab,
                $post['title'],
                $post['content'] ?? '', // content가 없으면 빈 문자열
                $post['date'],
                $post['views'] ?? 0,
                $post['comments'] ?? 0,
                $post['location'] ?? null,
                $post['genre'] ?? null,
                $post['performanceDate'] ?? null
            ]);
            
            echo "  ✓ 게시글 ID {$post['id']}: {$post['title']}\n";
            $migratedCount++;
        } catch (PDOException $e) {
            echo "  ✗ 게시글 ID {$post['id']} 삽입 실패: " . $e->getMessage() . "\n";
        }
    }
    
    echo "\n";
}

echo "마이그레이션 완료!\n";
echo "  - 성공: {$migratedCount}개\n";
echo "  - 건너뜀: {$skippedCount}개\n\n";

echo "주의: 마이그레이션 후 constants.php에서 \$communityPosts 변수를 제거하거나 주석 처리하세요.\n";
