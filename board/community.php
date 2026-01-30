<?php
/**
 * 커뮤니티 페이지 (아티스트 전용)
 * 아티스트들이 정보를 공유하고 소통할 수 있는 커뮤니티 페이지입니다.
 * 자유게시판, 팀원모집, 함께공연 세 가지 탭으로 구성되어 있습니다.
 */
$communityTab = $_GET['tab'] ?? 'free';
?>
<div class="space-y-6">
    <div class="bg-gradient-to-r from-purple-600 to-pink-600 rounded-2xl p-6 text-white">
        <h2 class="text-3xl font-bold mb-2">아티스트 커뮤니티</h2>
        <p>정보를 공유하고 함께 성장해요</p>
    </div>

    <!-- 게시판 탭 네비게이션 -->
    <div class="flex gap-2 border-b border-gray-700">
        <a href="index.php?page=community&tab=free" class="px-6 py-3 font-bold transition-colors border-b-2 <?= $communityTab === 'free' ? 'border-purple-500 text-purple-400' : 'border-transparent text-gray-400 hover:text-gray-300' ?>">
            자유게시판
        </a>
        <a href="index.php?page=community&tab=recruit" class="px-6 py-3 font-bold transition-colors border-b-2 <?= $communityTab === 'recruit' ? 'border-purple-500 text-purple-400' : 'border-transparent text-gray-400 hover:text-gray-300' ?>">
            팀원모집
        </a>
        <a href="index.php?page=community&tab=collab" class="px-6 py-3 font-bold transition-colors border-b-2 <?= $communityTab === 'collab' ? 'border-purple-500 text-purple-400' : 'border-transparent text-gray-400 hover:text-gray-300' ?>">
            함께공연
        </a>
    </div>

    <div class="bg-gray-800 rounded-2xl border border-gray-700 shadow-sm">
        <div class="p-4 border-b border-gray-700 flex items-center justify-between">
            <h3 class="font-bold text-white">
                <?php
                if ($communityTab === 'free') echo '자유게시판';
                elseif ($communityTab === 'recruit') echo '팀원모집 게시판';
                elseif ($communityTab === 'collab') echo '함께공연 게시판';
                ?>
            </h3>
            <button class="px-4 py-2 bg-purple-600 text-white rounded-lg hover:bg-purple-700 transition-colors text-sm font-bold flex items-center gap-2">
                <i data-lucide="plus" style="width: 16px; height: 16px;"></i>
                글쓰기
            </button>
        </div>

        <!-- 게시글 목록: 선택된 탭에 따라 다른 게시글 표시 -->
        <div class="divide-y divide-gray-700">
            <?php
            $posts = $communityPosts[$communityTab] ?? [];
            foreach ($posts as $post):
            ?>
            <div class="p-4 hover:bg-gray-750 cursor-pointer transition-colors">
                <h4 class="font-bold text-white mb-2"><?= htmlspecialchars($post['title']) ?></h4>
                <div class="flex items-center gap-4 text-sm text-gray-400">
                    <span><?= htmlspecialchars($post['author']) ?></span>
                    <span><?= htmlspecialchars($post['date']) ?></span>
                    <?php if ($communityTab === 'free'): ?>
                    <span>조회 <?= $post['views'] ?></span>
                    <span>댓글 <?= $post['comments'] ?></span>
                    <?php elseif ($communityTab === 'recruit'): ?>
                    <span class="px-2 py-1 bg-purple-900/50 text-purple-300 rounded text-xs border border-purple-700"><?= htmlspecialchars($post['location']) ?></span>
                    <span class="px-2 py-1 bg-pink-900/50 text-pink-300 rounded text-xs border border-pink-700"><?= htmlspecialchars($post['genre']) ?></span>
                    <?php elseif ($communityTab === 'collab'): ?>
                    <span class="px-2 py-1 bg-blue-900/50 text-blue-300 rounded text-xs border border-blue-700">공연일: <?= htmlspecialchars($post['performanceDate']) ?></span>
                    <span class="px-2 py-1 bg-purple-900/50 text-purple-300 rounded text-xs border border-purple-700"><?= htmlspecialchars($post['location']) ?></span>
                    <?php endif; ?>
                </div>
            </div>
            <?php endforeach; ?>
        </div>
    </div>
</div>

<script>
lucide.createIcons();
</script>
