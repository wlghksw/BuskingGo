<?php
/**
 * 찜 목록 페이지 모바일 버전
 * 관람자가 찜한 공연 목록을 표시합니다.
 */
$favorites = $_SESSION['favorites'] ?? [];
$favoritePerformances = [];

// 찜한 공연 ID로 실제 공연 데이터 찾기
foreach ($samplePerformances as $perf) {
    if (in_array($perf['id'], $favorites)) {
        $favoritePerformances[] = $perf;
    }
}
?>
<div class="p-4 space-y-4">
    <div class="bg-gradient-to-r from-pink-600 to-red-600 rounded-2xl p-6 text-white">
        <h2 class="text-2xl font-bold mb-2">찜 목록</h2>
        <p class="text-sm opacity-90">관심 있는 공연을 모아보세요</p>
    </div>

    <?php if (empty($favoritePerformances)): ?>
    <!-- 찜한 공연이 없을 때 -->
    <div class="flex flex-col items-center justify-center py-12 px-4">
        <i data-lucide="heart" class="text-gray-400 mb-4" style="width: 64px; height: 64px;"></i>
        <h3 class="text-xl font-bold text-gray-300 mb-2">찜한 공연이 없습니다</h3>
        <p class="text-sm text-gray-500 text-center mb-6">홈에서 관심 있는 공연을 찜해보세요</p>
        <a href="index.php?page=split&appPage=home" class="px-6 py-3 bg-gradient-to-r from-purple-600 to-pink-600 text-white font-bold rounded-lg hover:scale-105 transition-transform">
            공연 찾아보기
        </a>
    </div>
    <?php else: ?>
    <!-- 찜한 공연 목록 -->
    <div class="px-4 space-y-3 pb-4">
        <?php foreach ($favoritePerformances as $perf): ?>
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
                </div>
                <!-- 찜하기 버튼 (이미 찜한 상태) -->
                <a href="index.php?page=split&appPage=favorites&toggleFavorite=<?= $perf['id'] ?>" onclick="event.stopPropagation();" class="p-2 hover:bg-gray-700/50 rounded-full transition-all ml-2">
                    <i data-lucide="heart" class="fill-red-500 text-red-500" style="width: 20px; height: 20px;"></i>
                </a>
            </div>
            <!-- 공연 설명 -->
            <p class="text-sm text-gray-400 mt-2"><?= htmlspecialchars($perf['description']) ?></p>
        </div>
        <?php endforeach; ?>
    </div>
    <?php endif; ?>
</div>

<script>
// Lucide 아이콘 초기화
if (typeof lucide !== 'undefined') {
    lucide.createIcons();
}
</script>
