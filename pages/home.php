<?php
/**
 * 공연 찾기 메인 페이지
 * 지도와 공연 목록을 통해 사용자가 주변 버스킹 공연을 찾을 수 있는 페이지입니다.
 * 지역 필터링, 지도 마커 표시, 공연 상세 정보 확인 기능을 제공합니다.
 */
?>
<div class="space-y-6">
    <!-- 히어로 섹션: 현재 위치 및 진행 중 공연 수 표시 -->
    <div class="bg-gradient-to-r from-purple-600 to-pink-600 rounded-2xl p-8 text-white">
        <h1 class="text-4xl font-bold mb-4">내 주변 버스킹 찾기 🎵</h1>
        <p class="text-xl mb-6">지금 진행 중인 공연을 확인하세요</p>
        
        <div class="flex gap-4 flex-wrap">
            <!-- 현재 선택된 지역 표시 -->
            <div class="bg-white/20 backdrop-blur-lg rounded-xl p-4 flex items-center gap-3">
                <i data-lucide="navigation" class="text-white" style="width: 24px; height: 24px;"></i>
                <div>
                    <p class="text-sm opacity-80">현재 위치</p>
                    <p class="font-bold"><?= htmlspecialchars($selectedLocation ?: '전체 지역') ?></p>
                </div>
            </div>
            <!-- 필터링된 공연 개수 표시 -->
            <div class="bg-white/20 backdrop-blur-lg rounded-xl p-4 flex items-center gap-3">
                <i data-lucide="clock" class="text-white" style="width: 24px; height: 24px;"></i>
                <div>
                    <p class="text-sm opacity-80">진행중 공연</p>
                    <p class="font-bold text-2xl"><?= count($filteredPerformances) ?>개</p>
                </div>
            </div>
        </div>
    </div>

    <!-- 지도 섹션: 공연 위치를 지도에 마커로 표시 -->
    <div class="bg-gray-800 rounded-2xl p-6 border border-gray-700 shadow-sm">
        <div class="flex items-center justify-between mb-4">
            <h2 class="text-2xl font-bold text-white">실시간 공연 지도</h2>
            <!-- 지역 선택 드롭다운 및 초기화 버튼 -->
            <div class="flex items-center gap-2 flex-wrap">
                <form method="GET" class="flex items-center gap-2">
                    <input type="hidden" name="page" value="home">
                    <select name="location" onchange="this.form.submit()" class="px-4 py-2 bg-gray-900 border border-gray-700 rounded-lg text-white focus:outline-none focus:border-purple-500">
                        <option value="">전체 지역</option>
                        <?php foreach ($locationCoordinates as $loc => $coords): ?>
                        <option value="<?= htmlspecialchars($loc) ?>" <?= $selectedLocation === $loc ? 'selected' : '' ?>>
                            <?= htmlspecialchars($loc) ?>
                        </option>
                        <?php endforeach; ?>
                    </select>
                </form>
                <!-- 지역 선택 시에만 초기화 버튼 표시 -->
                <?php if ($selectedLocation): ?>
                <a href="index.php?page=home&location=" class="px-3 py-2 bg-gray-700 hover:bg-gray-600 rounded-lg text-gray-300 text-sm transition-colors">
                    초기화
                </a>
                <?php endif; ?>
            </div>
        </div>
        <!-- Leaflet 지도 컨테이너 -->
        <div id="map" class="rounded-xl overflow-hidden border border-gray-700 shadow-sm" style="height: 400px;"></div>
    </div>

    <!-- 공연 목록: 필터링된 공연을 카드 형태로 표시 -->
    <div class="space-y-4">
        <?php foreach ($filteredPerformances as $perf): ?>
        <div onclick="showPerformanceModal(<?= htmlspecialchars(json_encode($perf)) ?>)" class="bg-gray-800 rounded-2xl p-6 hover:bg-gray-750 transition-all cursor-pointer border border-gray-700 hover:border-purple-500 shadow-sm">
            <div class="flex items-start justify-between mb-4">
                <div class="flex items-center gap-4">
                    <div class="text-5xl"><?= htmlspecialchars($perf['image']) ?></div>
                    <div>
                        <div class="flex items-center gap-2 mb-1">
                            <h3 class="text-2xl font-bold text-white"><?= htmlspecialchars($perf['buskerName']) ?></h3>
                            <!-- 진행 중 공연만 LIVE 배지 표시 (펄스 애니메이션) -->
                            <?php if ($perf['status'] === '진행중'): ?>
                            <span class="px-3 py-1 bg-red-500 text-white text-sm rounded-full animate-pulse">
                                LIVE
                            </span>
                            <?php endif; ?>
                        </div>
                    </div>
                </div>
                <!-- 찜하기 버튼 (이벤트 전파 방지) -->
                <a href="index.php?page=home&toggleFavorite=<?= $perf['id'] ?>" onclick="event.stopPropagation();" class="p-2 hover:bg-gray-700 rounded-full transition-all">
                    <i data-lucide="heart" class="<?= in_array($perf['id'], $_SESSION['favorites']) ? 'fill-red-500 text-red-500' : 'text-gray-400' ?>" style="width: 24px; height: 24px;"></i>
                </a>
            </div>

            <!-- 공연 정보 그리드: 위치, 시간, 거리, 평점 -->
            <div class="grid grid-cols-2 gap-4 mb-4">
                <div class="flex items-center gap-2 text-gray-300">
                    <i data-lucide="map-pin" style="width: 18px; height: 18px;"></i>
                    <span><?= htmlspecialchars($perf['location']) ?></span>
                </div>
                <div class="flex items-center gap-2 text-gray-300">
                    <i data-lucide="clock" style="width: 18px; height: 18px;"></i>
                    <span><?= htmlspecialchars($perf['startTime']) ?> - <?= htmlspecialchars($perf['endTime']) ?></span>
                </div>
                <div class="flex items-center gap-2 text-gray-300">
                    <i data-lucide="navigation" style="width: 18px; height: 18px;"></i>
                    <span><?= htmlspecialchars($perf['distance']) ?>km</span>
                </div>
                <div class="flex items-center gap-2 text-yellow-400">
                    <i data-lucide="star" fill="currentColor" style="width: 18px; height: 18px;"></i>
                    <span><?= htmlspecialchars($perf['rating']) ?></span>
                </div>
            </div>

            <!-- 공연 설명 -->
            <p class="text-gray-400 mb-4"><?= htmlspecialchars($perf['description']) ?></p>
        </div>
        <?php endforeach; ?>
    </div>
</div>

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
                    width: 50px;
                    height: 50px;
                    display: flex;
                    align-items: center;
                    justify-content: center;
                    font-size: 28px;
                    box-shadow: 0 4px 12px rgba(0, 0, 0, 0.3);
                    transition: transform 0.2s;
                ">
                    🎤
                </div>
            `,
            iconSize: [50, 50],
            iconAnchor: [25, 25],
            popupAnchor: [0, -25],
        });
        
        const marker = L.marker([perf.lat, perf.lng], { icon }).addTo(map);
        marker.bindPopup(`
            <div class="text-white">
                <div class="text-2xl mb-2">${perf.image}</div>
                <h3 class="font-bold text-lg mb-1 text-white">${perf.buskerName}</h3>
                <p class="text-xs text-gray-300 mb-1">📍 ${perf.location}</p>
                <p class="text-xs text-gray-300 mb-1">🕐 ${perf.startTime} - ${perf.endTime}</p>
                ${perf.status === '진행중' ? '<span class="inline-block px-2 py-1 bg-red-500 text-white text-xs rounded-full mt-2">LIVE</span>' : ''}
            </div>
        `);
        
        marker.on('click', () => {
            showPerformanceModal(perf);
        });
    });
    
    lucide.createIcons();
});
</script>
