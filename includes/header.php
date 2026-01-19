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
        <nav class="flex gap-6">
            <!-- 공통 메뉴: 모든 사용자 접근 가능 -->
            <a href="index.php?page=home" class="font-bold transition-colors <?= $currentPage === 'home' ? 'text-purple-400' : 'text-gray-400 hover:text-white' ?>">
                공연 찾기
            </a>
            
            <!-- 버스커 등록: 아티스트 또는 미로그인 사용자만 접근 가능 -->
            <?php if ($userType === 'artist' || !$userType): ?>
            <a href="index.php?page=register" class="font-bold transition-colors <?= $currentPage === 'register' ? 'text-purple-400' : 'text-gray-400 hover:text-white' ?>">
                버스커 등록
            </a>
            <?php endif; ?>
            
            <!-- 공연 예약: 미로그인 사용자만 접근 가능 -->
            <?php if (!$userType): ?>
            <a href="index.php?page=booking" class="font-bold transition-colors <?= $currentPage === 'booking' ? 'text-purple-400' : 'text-gray-400 hover:text-white' ?>">
                공연 예약
            </a>
            <?php endif; ?>
            
            <!-- 아티스트 전용 메뉴 -->
            <?php if ($userType === 'artist'): ?>
            <a href="index.php?page=alarm" class="font-bold transition-colors flex items-center gap-1 <?= $currentPage === 'alarm' ? 'text-purple-400' : 'text-gray-400 hover:text-white' ?>">
                <i data-lucide="bell" style="width: 18px; height: 18px;"></i>
                맞춤 알람
            </a>
            <a href="index.php?page=community" class="font-bold transition-colors flex items-center gap-1 <?= $currentPage === 'community' ? 'text-purple-400' : 'text-gray-400 hover:text-white' ?>">
                <i data-lucide="message-square" style="width: 18px; height: 18px;"></i>
                커뮤니티
            </a>
            <?php endif; ?>
        </nav>

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
