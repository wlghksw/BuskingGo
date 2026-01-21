<?php
/**
 * 커뮤니티 페이지 모바일 버전
 */
// 관람자는 접근 불가 (아티스트만 접근 가능)
if (!isset($_SESSION['userType']) || $_SESSION['userType'] !== 'artist') {
    header('Location: index.php?page=split&appPage=home');
    exit;
}
$communityTab = $_GET['tab'] ?? 'free';
?>
<div class="p-4 space-y-4">
    <div class="bg-gradient-to-r from-green-600 to-teal-600 rounded-2xl p-6 text-white">
        <h2 class="text-2xl font-bold mb-2">커뮤니티</h2>
        <p class="text-sm opacity-90">아티스트들과 소통하고 정보를 공유하세요</p>
    </div>

    <!-- 탭 메뉴 -->
    <div class="flex gap-2 px-4 border-b border-gray-700">
        <a href="index.php?page=split&appPage=community&tab=free" class="px-4 py-2 text-sm font-bold <?= $communityTab === 'free' ? 'text-purple-400 border-b-2 border-purple-400' : 'text-gray-400' ?>">
            자유게시판
        </a>
        <a href="index.php?page=split&appPage=community&tab=recruit" class="px-4 py-2 text-sm font-bold <?= $communityTab === 'recruit' ? 'text-purple-400 border-b-2 border-purple-400' : 'text-gray-400' ?>">
            팀원모집
        </a>
        <a href="index.php?page=split&appPage=community&tab=collab" class="px-4 py-2 text-sm font-bold <?= $communityTab === 'collab' ? 'text-purple-400 border-b-2 border-purple-400' : 'text-gray-400' ?>">
            함께공연
        </a>
    </div>

    <!-- 게시글 목록 -->
    <div class="px-4 space-y-3 pb-4">
        <?php
        $posts = $communityPosts[$communityTab] ?? [];
        foreach ($posts as $post):
        ?>
        <div class="bg-white rounded-2xl p-4 shadow-md">
            <h3 class="font-bold text-gray-900 mb-2"><?= htmlspecialchars($post['title']) ?></h3>
            <div class="flex items-center gap-4 text-sm text-gray-600 flex-wrap">
                <span><?= htmlspecialchars($post['author']) ?></span>
                <span><?= htmlspecialchars($post['date']) ?></span>
                <?php if ($communityTab === 'free'): ?>
                <span>조회 <?= $post['views'] ?></span>
                <span>댓글 <?= $post['comments'] ?></span>
                <?php elseif ($communityTab === 'recruit'): ?>
                <span class="px-2 py-1 bg-purple-100 text-purple-700 rounded text-xs"><?= htmlspecialchars($post['location']) ?></span>
                <span class="px-2 py-1 bg-pink-100 text-pink-700 rounded text-xs"><?= htmlspecialchars($post['genre']) ?></span>
                <?php elseif ($communityTab === 'collab'): ?>
                <span class="px-2 py-1 bg-blue-100 text-blue-700 rounded text-xs">공연일: <?= htmlspecialchars($post['performanceDate']) ?></span>
                <span class="px-2 py-1 bg-purple-100 text-purple-700 rounded text-xs"><?= htmlspecialchars($post['location']) ?></span>
                <?php endif; ?>
            </div>
        </div>
        <?php endforeach; ?>
    </div>
</div>
