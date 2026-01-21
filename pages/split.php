<?php
/**
 * 랜딩 페이지 + 홈 페이지 좌우 분할 레이아웃
 * 왼쪽: 랜딩 페이지 (프로모션)
 * 오른쪽: 모바일 앱 UI (모든 기능 포함)
 */
$appPage = $_GET['appPage'] ?? 'home';
$userType = $_SESSION['userType'] ?? null;

// 관람자는 특정 페이지에 접근 불가
if ($userType === 'viewer') {
    if ($appPage === 'register' || $appPage === 'community' || $appPage === 'booking') {
        // 관람자는 홈으로 리다이렉트
        header('Location: index.php?page=split&appPage=home');
        exit;
    }
}
?>
<div class="min-h-screen flex flex-col lg:flex-row">
    <!-- 왼쪽: 랜딩 페이지 -->
    <div class="w-full lg:w-1/2 min-h-screen flex items-center justify-center relative overflow-hidden">
        <!-- 랜딩 페이지 콘텐츠 -->
        <div class="relative z-10 px-8 py-12 text-center">
            <!-- 로고 -->
            <div class="mb-8 flex items-center justify-center gap-3">
                <div class="text-5xl">🎵</div>
                <h1 class="text-5xl font-bold text-white">버스킹고</h1>
            </div>

            <!-- 태그라인 -->
            <p class="text-xl text-white mb-8 font-light">
                당신의 일상 가까이에서 울리는 음악
            </p>

            <!-- 다운로드 버튼 및 QR 코드 -->
            <div class="flex flex-col items-center gap-6 mb-8">
                <!-- 다운로드 버튼들 -->
                <div class="flex flex-col gap-3">
                    <!-- Google Play -->
                    <a href="#" class="inline-block">
                        <div class="bg-black px-6 py-3 rounded-lg border-2 border-white text-white font-bold hover:bg-gray-800 transition-colors text-sm">
                            GET IT ON Google Play
                        </div>
                    </a>
                    
                    <!-- App Store -->
                    <a href="#" class="inline-block">
                        <div class="bg-black px-6 py-3 rounded-lg border-2 border-white text-white font-bold hover:bg-gray-800 transition-colors text-sm">
                            Download on the App Store
                        </div>
                    </a>
                </div>

                <!-- QR 코드 -->
                <div class="bg-white p-3 rounded-xl shadow-2xl">
                    <div class="w-28 h-28 bg-gray-100 flex items-center justify-center rounded-lg">
                        <div class="text-center text-gray-500 text-xs p-2">
                            <div class="grid grid-cols-8 gap-0.5 mb-1">
                                <div class="w-2.5 h-2.5 bg-black"></div><div class="w-2.5 h-2.5 bg-black"></div><div class="w-2.5 h-2.5 bg-black"></div><div class="w-2.5 h-2.5 bg-black"></div><div class="w-2.5 h-2.5 bg-black"></div><div class="w-2.5 h-2.5 bg-black"></div><div class="w-2.5 h-2.5 bg-black"></div><div class="w-2.5 h-2.5 bg-black"></div>
                                <div class="w-2.5 h-2.5 bg-black"></div><div class="w-2.5 h-2.5 bg-white"></div><div class="w-2.5 h-2.5 bg-white"></div><div class="w-2.5 h-2.5 bg-black"></div><div class="w-2.5 h-2.5 bg-white"></div><div class="w-2.5 h-2.5 bg-white"></div><div class="w-2.5 h-2.5 bg-black"></div><div class="w-2.5 h-2.5 bg-black"></div>
                                <div class="w-2.5 h-2.5 bg-black"></div><div class="w-2.5 h-2.5 bg-white"></div><div class="w-2.5 h-2.5 bg-black"></div><div class="w-2.5 h-2.5 bg-black"></div><div class="w-2.5 h-2.5 bg-white"></div><div class="w-2.5 h-2.5 bg-black"></div><div class="w-2.5 h-2.5 bg-white"></div><div class="w-2.5 h-2.5 bg-black"></div>
                                <div class="w-2.5 h-2.5 bg-black"></div><div class="w-2.5 h-2.5 bg-white"></div><div class="w-2.5 h-2.5 bg-white"></div><div class="w-2.5 h-2.5 bg-black"></div><div class="w-2.5 h-2.5 bg-white"></div><div class="w-2.5 h-2.5 bg-white"></div><div class="w-2.5 h-2.5 bg-black"></div><div class="w-2.5 h-2.5 bg-black"></div>
                                <div class="w-2.5 h-2.5 bg-black"></div><div class="w-2.5 h-2.5 bg-white"></div><div class="w-2.5 h-2.5 bg-black"></div><div class="w-2.5 h-2.5 bg-white"></div><div class="w-2.5 h-2.5 bg-black"></div><div class="w-2.5 h-2.5 bg-white"></div><div class="w-2.5 h-2.5 bg-black"></div><div class="w-2.5 h-2.5 bg-black"></div>
                                <div class="w-2.5 h-2.5 bg-black"></div><div class="w-2.5 h-2.5 bg-white"></div><div class="w-2.5 h-2.5 bg-white"></div><div class="w-2.5 h-2.5 bg-black"></div><div class="w-2.5 h-2.5 bg-white"></div><div class="w-2.5 h-2.5 bg-white"></div><div class="w-2.5 h-2.5 bg-black"></div><div class="w-2.5 h-2.5 bg-black"></div>
                                <div class="w-2.5 h-2.5 bg-black"></div><div class="w-2.5 h-2.5 bg-white"></div><div class="w-2.5 h-2.5 bg-black"></div><div class="w-2.5 h-2.5 bg-black"></div><div class="w-2.5 h-2.5 bg-white"></div><div class="w-2.5 h-2.5 bg-black"></div><div class="w-2.5 h-2.5 bg-white"></div><div class="w-2.5 h-2.5 bg-black"></div>
                                <div class="w-2.5 h-2.5 bg-black"></div><div class="w-2.5 h-2.5 bg-black"></div><div class="w-2.5 h-2.5 bg-black"></div><div class="w-2.5 h-2.5 bg-black"></div><div class="w-2.5 h-2.5 bg-black"></div><div class="w-2.5 h-2.5 bg-black"></div><div class="w-2.5 h-2.5 bg-black"></div><div class="w-2.5 h-2.5 bg-black"></div>
                            </div>
                            QR 코드
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <!-- 오른쪽: 모바일 앱 UI (모든 기능 포함) -->
    <div class="w-full lg:w-1/2 min-h-screen flex items-center justify-center relative overflow-hidden p-4 lg:p-8">
        
        <!-- 모바일 앱 UI 컨테이너 -->
        <div class="relative z-10 w-full max-w-sm bg-gray-900 rounded-3xl shadow-2xl overflow-hidden" style="height: 90vh; max-height: 800px;">
            <!-- 모바일 헤더 -->
            <header class="bg-gray-900 border-b border-gray-800">
                <div class="px-4 py-3 flex items-center justify-between">
                    <div class="flex items-center gap-2">
                        <i data-lucide="music" class="text-purple-500" style="width: 24px; height: 24px;"></i>
                        <h1 class="text-xl font-bold text-white">버스킹고</h1>
                    </div>
                    
                    <?php if (isset($_SESSION['userType']) && $_SESSION['userType']): ?>
                    <button onclick="showUserTypeModal()" class="flex items-center gap-2 px-3 py-1.5 bg-purple-900/50 rounded-lg border border-purple-700 hover:bg-purple-800/50 transition-colors">
                        <span class="text-xs font-bold text-purple-300">
                            <?= $_SESSION['userType'] === 'viewer' ? '👀 관람자' : '🎤 아티스트' ?>
                        </span>
                        <i data-lucide="chevron-down" class="text-purple-400" style="width: 14px; height: 14px;"></i>
                    </button>
                    <?php else: ?>
                    <button onclick="showUserTypeModal()" class="px-4 py-2 bg-purple-600 text-white rounded-lg hover:bg-purple-700 transition-colors text-sm font-bold">
                        로그인
                    </button>
                    <?php endif; ?>
                </div>
            </header>
            
            <!-- 스크롤 가능한 콘텐츠 영역 -->
            <div id="app-content" class="overflow-y-auto" style="height: calc(90vh - 60px - 70px); max-height: calc(800px - 60px - 70px);">
                <?php
                // 앱 내 페이지 로드
                switch($appPage) {
                    case 'home':
                        include __DIR__ . '/home-mobile.php';
                        break;
                    case 'favorites':
                        include __DIR__ . '/favorites-mobile.php';
                        break;
                    case 'register':
                        include __DIR__ . '/register-mobile.php';
                        break;
                    case 'booking':
                        include __DIR__ . '/booking-mobile.php';
                        break;
                    case 'community':
                        include __DIR__ . '/../board/community-mobile.php';
                        break;
                    default:
                        include __DIR__ . '/home-mobile.php';
                }
                ?>
            </div>

            <!-- 하단 네비게이션 바 -->
            <nav class="bg-gray-900 border-t border-gray-800">
                <div class="flex items-center justify-around py-2">
                    <a href="index.php?page=split&appPage=home" data-page="home" class="flex flex-col items-center gap-1 py-2 px-4 <?= $appPage === 'home' ? 'text-purple-400' : 'text-gray-400' ?>">
                        <i data-lucide="home" style="width: 24px; height: 24px;" class="<?= $appPage === 'home' ? 'fill-current' : '' ?>"></i>
                        <span class="text-xs font-medium">홈</span>
                    </a>
                    <?php if ($userType === 'artist' || !$userType): ?>
                    <!-- 예약: 아티스트 또는 미로그인만 표시 -->
                    <a href="index.php?page=split&appPage=booking" data-page="booking" class="flex flex-col items-center gap-1 py-2 px-4 <?= $appPage === 'booking' ? 'text-purple-400' : 'text-gray-400' ?>">
                        <i data-lucide="calendar" style="width: 24px; height: 24px;" class="<?= $appPage === 'booking' ? 'fill-current' : '' ?>"></i>
                        <span class="text-xs font-medium">예약</span>
                    </a>
                    <?php endif; ?>
                    <?php if ($userType === 'artist'): ?>
                    <!-- 커뮤니티: 아티스트만 표시 -->
                    <a href="index.php?page=split&appPage=community" data-page="community" class="flex flex-col items-center gap-1 py-2 px-4 <?= $appPage === 'community' ? 'text-purple-400' : 'text-gray-400' ?>">
                        <i data-lucide="message-square" style="width: 24px; height: 24px;" class="<?= $appPage === 'community' ? 'fill-current' : '' ?>"></i>
                        <span class="text-xs font-medium">커뮤니티</span>
                    </a>
                    <?php endif; ?>
                    <?php if ($userType === 'artist' || !$userType): ?>
                    <!-- 버스커 등록: 아티스트 또는 미로그인만 표시 -->
                    <a href="index.php?page=split&appPage=register" data-page="register" class="flex flex-col items-center gap-1 py-2 px-4 <?= $appPage === 'register' ? 'text-purple-400' : 'text-gray-400' ?>">
                        <i data-lucide="user" style="width: 24px; height: 24px;" class="<?= $appPage === 'register' ? 'fill-current' : '' ?>"></i>
                        <span class="text-xs font-medium">등록</span>
                    </a>
                    <?php endif; ?>
                    <?php if ($userType === 'viewer'): ?>
                    <!-- 관람자는 찜 목록 버튼 표시 -->
                    <a href="index.php?page=split&appPage=favorites" data-page="favorites" class="flex flex-col items-center gap-1 py-2 px-4 <?= $appPage === 'favorites' ? 'text-purple-400' : 'text-gray-400' ?>">
                        <i data-lucide="heart" style="width: 24px; height: 24px;" class="<?= $appPage === 'favorites' ? 'fill-current' : '' ?>"></i>
                        <span class="text-xs font-medium">찜목록</span>
                    </a>
                    <?php endif; ?>
                </div>
            </nav>
        </div>
    </div>
</div>

<script>
// 지도 초기화 (홈 페이지일 때만)
const performances = <?= json_encode($filteredPerformances) ?>;
const userLocation = <?= json_encode($userLocation) ?>;
window.favorites = <?= json_encode($_SESSION['favorites']) ?>;

document.addEventListener('DOMContentLoaded', function() {
    lucide.createIcons();
    
    <?php if ($appPage === 'home'): ?>
    // 홈 페이지일 때만 지도 초기화
    initMap();
    <?php endif; ?>
});

function initMap() {
    if (typeof L === 'undefined') return;
    
    const map = L.map('map').setView([userLocation.lat, userLocation.lng], 13);
    
    L.tileLayer('https://{s}.tile.openstreetmap.org/{z}/{x}/{y}.png', {
        attribution: '&copy; <a href="https://www.openstreetmap.org/copyright">OpenStreetMap</a> contributors'
    }).addTo(map);
    
    performances.forEach(perf => {
        const isLive = perf.status === '진행중';
        const icon = L.divIcon({
            className: 'custom-marker',
            html: `
                <div style="
                    background: ${isLive ? 'linear-gradient(135deg, #ef4444, #dc2626)' : 'linear-gradient(135deg, #9333ea, #7c3aed)'};
                    border: 3px solid #ffffff;
                    border-radius: 50%;
                    width: 40px;
                    height: 40px;
                    display: flex;
                    align-items: center;
                    justify-content: center;
                    font-size: 24px;
                    box-shadow: 0 4px 12px rgba(0, 0, 0, 0.3);
                ">
                    🎤
                </div>
            `,
            iconSize: [40, 40],
            iconAnchor: [20, 20],
            popupAnchor: [0, -20],
        });
        
        const marker = L.marker([perf.lat, perf.lng], { icon }).addTo(map);
        marker.bindPopup(`
            <div style="color: #111827;">
                <div class="text-2xl mb-2">${perf.image}</div>
                <h3 class="font-bold text-base mb-1">${perf.buskerName}</h3>
                <p class="text-xs text-gray-600 mb-1">📍 ${perf.location}</p>
                <p class="text-xs text-gray-600 mb-1">🕐 ${perf.startTime} - ${perf.endTime}</p>
                ${perf.status === '진행중' ? '<span class="inline-block px-2 py-0.5 bg-red-500 text-white text-xs rounded-full mt-2">LIVE</span>' : ''}
            </div>
        `);
        
        marker.on('click', () => {
            if (typeof showPerformanceModal === 'function') {
                showPerformanceModal(perf);
            }
        });
    });
    
    // Lucide 아이콘 초기화 (헤더의 아이콘을 위해)
    if (typeof lucide !== 'undefined') {
        lucide.createIcons();
    }
}
</script>
