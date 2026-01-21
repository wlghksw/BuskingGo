<?php
/**
 * 공연 찾기 메인 페이지 (모바일 앱 스타일)
 * 지도와 공연 목록을 통해 사용자가 주변 버스킹 공연을 찾을 수 있는 페이지입니다.
 */
?>
<!-- 모바일 앱 스타일 컨테이너 -->
<div class="max-w-md mx-auto bg-gray-900 min-h-screen pb-20 pt-0">
    <!-- 모바일 헤더 -->
    <?php include __DIR__ . '/../includes/mobile_header.php'; ?>
    <!-- 내 주변 버스킹 찾기 섹션 -->
    <div class="bg-gradient-to-r from-purple-600 to-pink-600 rounded-b-3xl p-6 text-white mb-4">
        <div class="flex items-center gap-2 mb-3">
            <i data-lucide="music" style="width: 24px; height: 24px;"></i>
            <h1 class="text-2xl font-bold">내 주변 버스킹 찾기</h1>
        </div>
        <p class="text-sm opacity-90 mb-4">지금 진행 중인 공연을 확인하세요</p>
        
        <div class="flex gap-3">
            <!-- 현재 위치 버튼 -->
            <button class="flex-1 bg-white/20 backdrop-blur-lg rounded-xl p-3 flex items-center gap-2 hover:bg-white/30 transition-colors">
                <i data-lucide="navigation" style="width: 20px; height: 20px;"></i>
                <span class="text-sm font-medium"><?= htmlspecialchars($selectedLocation ?: '전체 지역') ?></span>
            </button>
            
            <!-- 진행중 공연 수 버튼 -->
            <button class="flex-1 bg-white/20 backdrop-blur-lg rounded-xl p-3 flex items-center gap-2 hover:bg-white/30 transition-colors">
                <i data-lucide="clock" style="width: 20px; height: 20px;"></i>
                <span class="text-sm font-bold"><?= count($filteredPerformances) ?>개</span>
            </button>
        </div>
    </div>

    <!-- 실시간 공연 지도 섹션 -->
    <div class="bg-white rounded-2xl p-4 mx-4 mb-4 shadow-lg">
        <div class="flex items-center justify-between mb-3">
            <h2 class="text-lg font-bold text-gray-900">실시간 공연 지도</h2>
            <!-- 지역 선택 드롭다운 -->
            <form method="GET" class="flex items-center gap-2">
                <input type="hidden" name="page" value="home">
                <select name="location" onchange="this.form.submit()" class="px-3 py-1.5 bg-gray-100 border border-gray-300 rounded-lg text-sm text-gray-700 focus:outline-none focus:border-purple-500">
                    <option value="">전체 지역</option>
                    <?php foreach ($locationCoordinates as $loc => $coords): ?>
                    <option value="<?= htmlspecialchars($loc) ?>" <?= $selectedLocation === $loc ? 'selected' : '' ?>>
                        <?= htmlspecialchars($loc) ?>
                    </option>
                    <?php endforeach; ?>
                </select>
            </form>
        </div>
        <!-- Leaflet 지도 컨테이너 -->
        <div id="map" class="rounded-xl overflow-hidden border border-gray-200" style="height: 300px;"></div>
    </div>

    <!-- 공연 목록 -->
    <div class="px-4 space-y-3">
        <?php foreach ($filteredPerformances as $perf): ?>
        <div onclick="showPerformanceModal(<?= htmlspecialchars(json_encode($perf)) ?>)" class="bg-white rounded-2xl p-4 shadow-md hover:shadow-lg transition-all cursor-pointer">
            <div class="flex items-start justify-between mb-3">
                <div class="flex items-center gap-3 flex-1">
                    <!-- 공연 아이콘 -->
                    <div class="text-4xl"><?= htmlspecialchars($perf['image']) ?></div>
                    <div class="flex-1">
                        <div class="flex items-center gap-2 mb-1">
                            <h3 class="text-lg font-bold text-gray-900"><?= htmlspecialchars($perf['buskerName']) ?></h3>
                            <?php if ($perf['status'] === '진행중'): ?>
                            <span class="px-2 py-0.5 bg-red-500 text-white text-xs rounded-full font-bold">
                                LIVE
                            </span>
                            <?php endif; ?>
                        </div>
                        <!-- 위치와 거리 -->
                        <div class="flex items-center gap-4 text-sm text-gray-600 mb-1">
                            <span class="flex items-center gap-1">
                                <i data-lucide="map-pin" style="width: 14px; height: 14px;"></i>
                                <?= htmlspecialchars($perf['location']) ?>
                            </span>
                            <span class="flex items-center gap-1">
                                <i data-lucide="navigation" style="width: 14px; height: 14px;"></i>
                                <?= htmlspecialchars($perf['distance']) ?>km
                            </span>
                        </div>
                        <!-- 시간과 평점 -->
                        <div class="flex items-center gap-4 text-sm text-gray-600">
                            <span class="flex items-center gap-1">
                                <i data-lucide="clock" style="width: 14px; height: 14px;"></i>
                                <?= htmlspecialchars($perf['startTime']) ?> - <?= htmlspecialchars($perf['endTime']) ?>
                            </span>
                            <span class="flex items-center gap-1 text-yellow-500">
                                <i data-lucide="star" fill="currentColor" style="width: 14px; height: 14px;"></i>
                                <?= htmlspecialchars($perf['rating']) ?>
                            </span>
                        </div>
                    </div>
                </div>
                <!-- 찜하기 버튼 -->
                <a href="index.php?page=home&toggleFavorite=<?= $perf['id'] ?>" onclick="event.stopPropagation();" class="p-2 hover:bg-gray-100 rounded-full transition-all ml-2">
                    <i data-lucide="heart" class="<?= in_array($perf['id'], $_SESSION['favorites']) ? 'fill-red-500 text-red-500' : 'text-gray-400' ?>" style="width: 20px; height: 20px;"></i>
                </a>
            </div>
            <!-- 공연 설명 -->
            <p class="text-sm text-gray-500 mt-2"><?= htmlspecialchars($perf['description']) ?></p>
        </div>
        <?php endforeach; ?>
    </div>
</div>

<!-- 하단 네비게이션 바 (모바일 앱 스타일) -->
<nav class="fixed bottom-0 left-0 right-0 bg-gray-900 border-t border-gray-800 z-50">
    <div class="max-w-md mx-auto flex items-center justify-around py-2">
        <a href="index.php?page=home" class="flex flex-col items-center gap-1 py-2 px-4 <?= ($page ?? '') === 'home' ? 'text-purple-400' : 'text-gray-400' ?>">
            <i data-lucide="home" style="width: 24px; height: 24px;" class="<?= ($page ?? '') === 'home' ? 'fill-current' : '' ?>"></i>
            <span class="text-xs font-medium">홈</span>
        </a>
        <a href="index.php?page=home" class="flex flex-col items-center gap-1 py-2 px-4 text-gray-400">
            <i data-lucide="search" style="width: 24px; height: 24px;"></i>
            <span class="text-xs font-medium">검색</span>
        </a>
        <a href="index.php?page=booking" class="flex flex-col items-center gap-1 py-2 px-4 text-gray-400">
            <i data-lucide="calendar" style="width: 24px; height: 24px;"></i>
            <span class="text-xs font-medium">예약</span>
        </a>
        <a href="index.php?page=community" class="flex flex-col items-center gap-1 py-2 px-4 text-gray-400">
            <i data-lucide="message-square" style="width: 24px; height: 24px;"></i>
            <span class="text-xs font-medium">커뮤니티</span>
        </a>
        <a href="#" class="flex flex-col items-center gap-1 py-2 px-4 text-gray-400">
            <i data-lucide="user" style="width: 24px; height: 24px;"></i>
            <span class="text-xs font-medium">나페이지</span>
        </a>
    </div>
</nav>

<script>
// 지도 초기화
const performances = <?= json_encode($filteredPerformances) ?>;
const userLocation = <?= json_encode($userLocation) ?>;
window.favorites = <?= json_encode($_SESSION['favorites']) ?>;

document.addEventListener('DOMContentLoaded', function() {
    const map = L.map('map').setView([userLocation.lat, userLocation.lng], 13);
    
    L.tileLayer('https://{s}.tile.openstreetmap.org/{z}/{x}/{y}.png', {
        attribution: '&copy; <a href="https://www.openstreetmap.org/copyright">OpenStreetMap</a> contributors'
    }).addTo(map);
    
    // 마커 추가
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
            showPerformanceModal(perf);
        });
    });
    
    lucide.createIcons();
});
</script>
