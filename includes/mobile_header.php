<?php
/**
 * 모바일 앱 스타일 헤더
 * 간단한 로고와 로그인 버튼만 표시
 */
$userType = $_SESSION['userType'] ?? null;
?>
<header class="sticky top-0 z-40 bg-gray-900 border-b border-gray-800">
    <div class="max-w-md mx-auto px-4 py-3 flex items-center justify-between">
        <div class="flex items-center gap-2">
            <i data-lucide="music" class="text-purple-500" style="width: 24px; height: 24px;"></i>
            <h1 class="text-xl font-bold text-white">버스킹고</h1>
        </div>
        
        <?php if ($userType): ?>
        <div class="px-3 py-1.5 bg-purple-900/50 rounded-lg border border-purple-700">
            <span class="text-xs font-bold text-purple-300">
                <?= $userType === 'viewer' ? '👀 관람자' : '🎤 아티스트' ?>
            </span>
        </div>
        <?php else: ?>
        <button onclick="showUserTypeModal()" class="px-4 py-2 bg-purple-600 text-white rounded-lg hover:bg-purple-700 transition-colors text-sm font-bold">
            로그인
        </button>
        <?php endif; ?>
    </div>
</header>
