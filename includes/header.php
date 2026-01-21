<?php
/**
 * 헤더 네비게이션 컴포넌트
 * 상단 고정 헤더로 페이지 네비게이션과 사용자 유형 표시를 담당합니다.
 * 사용자 유형에 따라 접근 가능한 메뉴가 동적으로 변경됩니다.
 */
$currentPage = $page ?? 'home';
$userType = $_SESSION['userType'] ?? null;
?>
<header class="sticky top-0 z-40 bg-gray-900/95 backdrop-blur-lg border-b border-gray-800 shadow-sm">
    <div class="max-w-6xl mx-auto px-4 py-4 flex items-center justify-between">
        <div class="flex items-center gap-3">
            <i data-lucide="music" class="text-purple-500" style="width: 32px; height: 32px;"></i>
            <h1 class="text-2xl font-bold text-white">버스킹고</h1>
        </div>

        <!-- 네비게이션 메뉴 -->
        <!-- 주의: 이 헤더는 현재 사용되지 않음 (split 페이지가 메인) -->
        <!-- 필요시 아래 주석을 해제하여 사용 가능 -->
        <!--
        <nav class="flex gap-6">
            <a href="index.php?page=split&appPage=home" class="font-bold transition-colors">
                공연 찾기
            </a>
            <a href="index.php?page=split&appPage=register" class="font-bold transition-colors">
                버스커 등록
            </a>
            <a href="index.php?page=split&appPage=booking" class="font-bold transition-colors">
                공연 예약
            </a>
            <a href="index.php?page=split&appPage=community" class="font-bold transition-colors">
                커뮤니티
            </a>
        </nav>
        -->

        <!-- 사용자 유형 표시 -->
        <div class="flex items-center gap-4">
            <!-- 로그인된 경우: 사용자 유형 표시 및 변경 버튼 -->
            <?php if ($userType): ?>
            <div class="flex items-center gap-2 px-4 py-2 bg-purple-900/50 rounded-lg border border-purple-700">
                <span class="text-sm font-bold text-purple-300">
                    <?= $userType === 'viewer' ? '👀 관람자' : '🎤 아티스트' ?>
                </span>
                <button onclick="showUserTypeModal()" class="text-xs text-purple-400 hover:text-purple-300">
                    변경
                </button>
            </div>
            <?php else: ?>
            <!-- 미로그인 상태: 로그인 버튼 표시 -->
            <button onclick="showUserTypeModal()" class="px-4 py-2 bg-purple-600 text-white rounded-lg hover:bg-purple-700 transition-colors text-sm font-bold">
                로그인
            </button>
            <?php endif; ?>
        </div>
    </div>
</header>
