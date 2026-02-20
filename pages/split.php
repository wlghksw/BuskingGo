<?php
/**
 * 랜딩 페이지 + 홈 페이지 좌우 분할 레이아웃
 * 왼쪽: 랜딩 페이지 (프로모션)
 * 오른쪽: 모바일 앱 UI (모든 기능 포함)
 */
$appPage = $_GET['appPage'] ?? 'home';
$userType = $_SESSION['userType'] ?? null;

// 마이페이지는 로그인 필수
if ($appPage === 'mypage' && !$userType) {
    // 로그인하지 않은 경우 홈으로 리다이렉트
    header('Location: index.php?page=split&appPage=home');
    exit;
}

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
    <div class="w-full lg:w-2/5 min-h-screen flex items-center justify-center relative overflow-hidden">
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

            <!-- 서비스 설명 -->
            <div class="bg-white/10 backdrop-blur-lg rounded-2xl pl-6 pr-6 py-6 mb-8 ml-12 text-left">
                <h2 class="text-2xl font-bold text-white mb-4">버스킹고란?</h2>
                <div class="space-y-4 text-gray-200">
                    <div class="flex items-start gap-3">
                        <div class="text-2xl flex-shrink-0">🎵</div>
                        <div>
                            <h3 class="font-bold text-white mb-1">주변 버스킹 공연 찾기</h3>
                            <p class="text-sm">지도에서 실시간으로 진행 중인 버스킹 공연을 찾아보세요. 위치, 시간, 거리 정보를 한눈에 확인할 수 있습니다.</p>
                        </div>
                    </div>
                    <div class="flex items-start gap-3">
                        <div class="text-2xl flex-shrink-0">🎤</div>
                        <div>
                            <h3 class="font-bold text-white mb-1">아티스트 공연 예약</h3>
                            <p class="text-sm">아티스트로 등록하고 원하는 장소와 시간에 버스킹 공연을 예약하세요. 공연 정보가 메인 리스트에 자동으로 등록됩니다.</p>
                        </div>
                    </div>
                    <div class="flex items-start gap-3">
                        <div class="text-2xl flex-shrink-0">💬</div>
                        <div>
                            <h3 class="font-bold text-white mb-1">커뮤니티 소통</h3>
                            <p class="text-sm">아티스트와 관람자들이 함께 소통하는 커뮤니티에서 정보를 공유하고 교류하세요.</p>
                        </div>
                    </div>
                    <div class="flex items-start gap-3">
                        <div class="text-2xl flex-shrink-0">❤️</div>
                        <div>
                            <h3 class="font-bold text-white mb-1">찜하기 기능</h3>
                            <p class="text-sm">관심 있는 공연을 찜하여 나중에 쉽게 찾아볼 수 있습니다.</p>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <!-- 오른쪽: 모바일 앱 UI (모든 기능 포함) -->
    <div class="w-full lg:w-3/5 min-h-screen flex items-center justify-center relative overflow-hidden p-4 lg:p-8">
        
        <!-- 모바일 앱 UI 컨테이너 -->
        <div class="relative z-10 w-full max-w-md bg-gray-900 rounded-3xl shadow-2xl overflow-hidden" style="height: 95vh; max-height: 900px;">
            <!-- 모바일 알림 토스트 (앱 컨테이너 내부 최상단) -->
            <div id="mobileNotificationToast" class="hidden absolute top-0 left-0 right-0 bg-gradient-to-r from-purple-600 to-pink-600 text-white px-4 py-3 shadow-2xl z-[10000] animate-slide-in-mobile" style="border-bottom: 2px solid rgba(255,255,255,0.2); border-radius: 0.75rem 0.75rem 0 0;">
                <div class="flex items-center gap-3">
                    <div class="text-2xl flex-shrink-0">🎉</div>
                    <div class="flex-1 min-w-0">
                        <p class="font-bold text-sm" id="mobileNotificationMessage"></p>
                    </div>
                    <button onclick="closeMobileNotification()" class="text-white hover:text-gray-200 flex-shrink-0 p-1">
                        <i data-lucide="x" style="width: 18px; height: 18px;"></i>
                    </button>
                </div>
            </div>
            
            <!-- 모바일 헤더 -->
            <header class="bg-gray-900 border-b border-gray-800 relative z-10">
                <div class="px-4 py-3 flex items-center justify-between">
                    <div class="flex items-center gap-2">
                        <i data-lucide="music" class="text-purple-500" style="width: 24px; height: 24px;"></i>
                        <h1 class="text-xl font-bold text-white">버스킹고</h1>
                    </div>
                    
                    <?php if (isset($_SESSION['userType']) && $_SESSION['userType']): ?>
                    <a href="index.php?page=split&appPage=mypage" class="flex items-center gap-2 px-3 py-1.5 bg-purple-900/50 rounded-lg border border-purple-700 hover:bg-purple-800/50 transition-colors">
                        <span class="text-xs font-bold text-purple-300">
                            <?= $_SESSION['userType'] === 'viewer' ? '👀 관람자' : '🎤 아티스트' ?>
                        </span>
                        <i data-lucide="chevron-down" class="text-purple-400" style="width: 14px; height: 14px;"></i>
                    </a>
                    <?php else: ?>
                    <button onclick="showUserTypeModal()" class="px-4 py-2 bg-purple-600 text-white rounded-lg hover:bg-purple-700 transition-colors text-sm font-bold">
                        로그인
                    </button>
                    <?php endif; ?>
                </div>
            </header>
            
            <!-- 스크롤 가능한 콘텐츠 영역 -->
            <div id="app-content" class="overflow-y-auto relative" style="height: calc(95vh - 60px - 70px); max-height: calc(900px - 60px - 70px);">
                <?php
                // 앱 내 페이지 로드
                switch($appPage) {
                    case 'home':
                        include __DIR__ . '/home-mobile.php';
                        break;
                    case 'favorites':
                        include __DIR__ . '/favorites-mobile.php';
                        break;
                    case 'mypage':
                        include __DIR__ . '/mypage-mobile.php';
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

// 모바일 알림 함수
function showMobileNotification(message) {
    const toast = document.getElementById('mobileNotificationToast');
    const messageEl = document.getElementById('mobileNotificationMessage');
    if (toast && messageEl) {
        messageEl.textContent = message;
        toast.classList.remove('hidden');
        toast.classList.add('animate-slide-in-mobile');
        
        // 5초 후 자동 닫기
        setTimeout(() => {
            closeMobileNotification();
        }, 5000);
    }
}

function closeMobileNotification() {
    const toast = document.getElementById('mobileNotificationToast');
    if (toast) {
        toast.classList.add('hidden');
        toast.classList.remove('animate-slide-in-mobile');
    }
}

document.addEventListener('DOMContentLoaded', function() {
    lucide.createIcons();
    
    // 예약 완료 알림 확인
    <?php if (isset($_SESSION['bookingNotification']) && $_SESSION['bookingNotification']['show']): ?>
    showMobileNotification('<?= htmlspecialchars($_SESSION['bookingNotification']['message']) ?>');
    <?php unset($_SESSION['bookingNotification']); ?>
    <?php endif; ?>
    
    // URL 파라미터로 알림 표시
    const urlParams = new URLSearchParams(window.location.search);
    if (urlParams.get('bookingSuccess') === '1' || urlParams.get('notify') === '1') {
        showMobileNotification('버스킹 공연이 예약되었습니다! 메인 리스트에서 확인하실 수 있습니다.');
    }
    
    // 공연 삭제 성공 알림
    if (urlParams.get('deleted') === '1') {
        showMobileNotification('공연이 삭제되었습니다.');
    }
    
    // 공연 삭제 실패 알림
    if (urlParams.get('error') === 'no_permission') {
        showMobileNotification('삭제 권한이 없습니다.');
    }
    if (urlParams.get('error') === 'not_found') {
        showMobileNotification('공연을 찾을 수 없습니다.');
    }
    
    // 로그아웃 성공 알림 (세션 플래그 확인)
    <?php if (isset($_SESSION['just_logged_out']) && $_SESSION['just_logged_out']): ?>
    showMobileNotification('로그아웃되었습니다.');
    <?php 
    unset($_SESSION['just_logged_out']); // 플래그 제거
    ?>
    <?php endif; ?>
    
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
            // 좌표가 없으면 건너뛰기
            if (!perf.lat || !perf.lng) {
                return;
            }
            
            const isLive = perf.status === '진행중';
            const statusText = isLive ? 'LIVE' : '진행 예정';
            const icon = L.divIcon({
                className: 'custom-marker',
                html: `
                    <div style="display: flex; flex-direction: column; align-items: center;">
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
                        <div style="
                            margin-top: 4px;
                            background: ${isLive ? '#ef4444' : '#9333ea'};
                            color: white;
                            font-size: 9px;
                            font-weight: bold;
                            padding: 2px 5px;
                            border-radius: 6px;
                            white-space: nowrap;
                            box-shadow: 0 2px 4px rgba(0, 0, 0, 0.3);
                        ">
                            ${statusText}
                        </div>
                    </div>
                `,
                iconSize: [40, 60],
                iconAnchor: [20, 60],
                popupAnchor: [0, -60],
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
    });
    
    // Lucide 아이콘 초기화 (헤더의 아이콘을 위해)
    if (typeof lucide !== 'undefined') {
        lucide.createIcons();
    }
}
</script>

    <style>
        @keyframes slide-in-mobile {
            from {
                transform: translateY(-100%);
                opacity: 0;
            }
            to {
                transform: translateY(0);
                opacity: 1;
            }
        }
        .animate-slide-in-mobile {
            animation: slide-in-mobile 0.4s ease-out;
        }
        
        /* 모바일 알림이 앱 컨테이너 내부 최상단에 표시되도록 */
        #mobileNotificationToast {
            position: absolute;
            top: 0;
            left: 0;
            right: 0;
            z-index: 10000;
            width: 100%;
            box-shadow: 0 4px 12px rgba(0, 0, 0, 0.3);
            border-radius: 0.75rem 0.75rem 0 0;
        }
        
        /* 알림이 표시될 때 헤더 위에 오버레이 */
        header {
            position: relative;
            z-index: 10;
        }
    </style>
