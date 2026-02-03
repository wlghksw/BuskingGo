<?php
/**
 * 홈 페이지 모바일 버전
 */
?>
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
<div class="bg-gray-800/80 backdrop-blur-xl border border-gray-700/50 rounded-2xl p-4 mx-4 mb-4 shadow-xl">
    <div class="flex items-center justify-between mb-3">
        <h2 class="text-lg font-bold text-white">실시간 공연 지도</h2>
        <!-- 지역 선택 드롭다운 -->
        <form method="GET" class="flex items-center gap-2">
            <input type="hidden" name="page" value="split">
            <input type="hidden" name="appPage" value="home">
            <select name="location" onchange="this.form.submit()" class="px-3 py-1.5 bg-gray-900/50 border border-gray-600 rounded-lg text-sm text-white focus:outline-none focus:border-purple-500">
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
    <div id="map" class="rounded-xl overflow-hidden border border-gray-600/50" style="height: 250px;"></div>
</div>

<!-- 공연 목록 -->
<div class="px-4 space-y-3 pb-4">
    <?php foreach ($filteredPerformances as $perf): ?>
    <div onclick="showPerformanceModal(<?= htmlspecialchars(json_encode($perf)) ?>)" class="bg-gray-800/80 backdrop-blur-xl border border-gray-700/50 rounded-2xl p-4 shadow-lg hover:shadow-xl hover:border-purple-500/50 transition-all cursor-pointer">
        <div class="flex items-start justify-between mb-3">
            <div class="flex items-center gap-3 flex-1">
                <!-- 공연 아이콘 -->
                <div class="text-4xl"><?= htmlspecialchars($perf['image']) ?></div>
                <div class="flex-1">
                    <div class="flex items-center gap-2 mb-1">
                        <h3 class="text-lg font-bold text-white"><?= htmlspecialchars($perf['buskerName']) ?></h3>
                        <?php if ($perf['status'] === '진행중'): ?>
                        <span class="px-2 py-0.5 bg-red-500 text-white text-xs rounded-full font-bold">
                            LIVE
                        </span>
                        <?php endif; ?>
                    </div>
                    <?php
                    // 자신이 올린 공연인지 확인
                    $isMyPerformance = false;
                    if ($_SESSION['userType'] === 'artist') {
                        if (isset($perf['createdByUserId']) && $perf['createdByUserId'] == ($_SESSION['userId'] ?? null)) {
                            $isMyPerformance = true;
                        } elseif (isset($perf['bookingId']) && isset($_SESSION['bookings'])) {
                            foreach ($_SESSION['bookings'] as $booking) {
                                if ($booking['id'] == $perf['bookingId'] && $booking['createdBy'] === 'artist') {
                                    $isMyPerformance = true;
                                    break;
                                }
                            }
                        }
                    }
                    ?>
                    <!-- 위치와 거리 -->
                    <div class="flex items-center gap-4 text-sm text-gray-300 mb-1">
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
                    <div class="flex items-center gap-4 text-sm text-gray-300">
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
                <div class="flex items-center gap-2 flex-shrink-0">
                    <!-- 자신이 올린 공연인 경우 삭제 버튼 표시 -->
                    <?php if ($isMyPerformance): ?>
                    <a href="index.php?page=split&appPage=home&deletePerformance=<?= htmlspecialchars($perf['id']) ?>" 
                       onclick="event.stopPropagation(); return confirm('정말 이 공연을 삭제하시겠습니까?');" 
                       class="p-2 hover:bg-red-900/50 rounded-full transition-all text-red-400 hover:text-red-300"
                       title="공연 삭제">
                        <i data-lucide="trash-2" style="width: 18px; height: 18px;"></i>
                    </a>
                    <?php endif; ?>
                    <!-- 찜하기 버튼 -->
                    <a href="index.php?page=split&appPage=home&toggleFavorite=<?= $perf['id'] ?>" onclick="event.stopPropagation();" class="p-2 hover:bg-gray-700/50 rounded-full transition-all">
                        <i data-lucide="heart" class="<?= in_array($perf['id'], $_SESSION['favorites']) ? 'fill-red-500 text-red-500' : 'text-gray-400' ?>" style="width: 20px; height: 20px;"></i>
                    </a>
                </div>
            </div>
        </div>
        <!-- 공연 설명 -->
        <p class="text-sm text-gray-400 mt-2"><?= htmlspecialchars($perf['description']) ?></p>
    </div>
    <?php endforeach; ?>
</div>
