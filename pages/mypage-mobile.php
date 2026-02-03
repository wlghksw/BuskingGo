<?php
/**
 * 마이페이지 모바일 버전
 * 관람자: 찜한 공연 목록
 * 아티스트: 버스커 팀 정보, 예약한 공연 목록
 */
$userType = $_SESSION['userType'] ?? null;
$userId = $_SESSION['userId'] ?? null;
$userName = $_SESSION['userName'] ?? null;
$user_id = $_SESSION['user_id'] ?? null;

// 사용자 정보 가져오기
$currentUser = null;
if (isset($_SESSION['users']) && is_array($_SESSION['users'])) {
    foreach ($_SESSION['users'] as $user) {
        if (isset($user['id']) && $user['id'] == $userId) {
            $currentUser = $user;
            break;
        }
    }
}

// 아티스트인 경우 버스커 정보 가져오기
$buskerInfo = null;
if ($userType === 'artist' && isset($_SESSION['buskers']) && is_array($_SESSION['buskers'])) {
    foreach ($_SESSION['buskers'] as $busker) {
        if (isset($busker['user_id']) && $busker['user_id'] === $user_id) {
            $buskerInfo = $busker;
            break;
        }
    }
    // 없으면 가장 최근 등록된 버스커 정보 사용
    if (!$buskerInfo && !empty($_SESSION['buskers'])) {
        $buskerInfo = end($_SESSION['buskers']);
    }
}

// 아티스트인 경우 예약한 공연 목록 가져오기
$myPerformances = [];
if ($userType === 'artist' && isset($_SESSION['performances']) && is_array($_SESSION['performances'])) {
    foreach ($_SESSION['performances'] as $perf) {
        if (isset($perf['createdByUserId']) && $perf['createdByUserId'] == $userId) {
            $myPerformances[] = $perf;
        } elseif (isset($perf['bookingId']) && isset($_SESSION['bookings'])) {
            foreach ($_SESSION['bookings'] as $booking) {
                if ($booking['id'] == $perf['bookingId'] && $booking['createdBy'] === 'artist') {
                    $myPerformances[] = $perf;
                    break;
                }
            }
        }
    }
}

// 관람자인 경우 찜한 공연 목록 가져오기
$favorites = $_SESSION['favorites'] ?? [];
$favoritePerformances = [];
if ($userType === 'viewer') {
    // samplePerformances와 세션의 performances를 합쳐서 검색
    $allPerformances = [];
    if (isset($samplePerformances) && is_array($samplePerformances)) {
        $allPerformances = $samplePerformances;
    }
    if (isset($_SESSION['performances']) && is_array($_SESSION['performances'])) {
        $allPerformances = array_merge($_SESSION['performances'], $allPerformances);
    }
    
    foreach ($allPerformances as $perf) {
        if (in_array($perf['id'], $favorites)) {
            $favoritePerformances[] = $perf;
        }
    }
}
?>

<div class="p-4 space-y-4 pb-20">
    <!-- 프로필 헤더 -->
    <div class="bg-gradient-to-r from-purple-600 to-pink-600 rounded-2xl p-6 text-white">
        <div class="flex items-center justify-between mb-4">
            <div>
                <h2 class="text-2xl font-bold mb-1">마이페이지</h2>
                <p class="text-sm opacity-90">
                    <?= $userType === 'viewer' ? '👀 관람자' : '🎤 아티스트' ?>
                </p>
            </div>
            <div class="text-4xl">
                <?= $userType === 'viewer' ? '👀' : '🎤' ?>
            </div>
        </div>
        <div class="space-y-2 text-sm">
            <div class="flex items-center gap-2">
                <i data-lucide="user" style="width: 16px; height: 16px;"></i>
                <span><?= htmlspecialchars($userName ?? '사용자') ?></span>
            </div>
            <?php if ($user_id): ?>
            <div class="flex items-center gap-2">
                <i data-lucide="at-sign" style="width: 16px; height: 16px;"></i>
                <span><?= htmlspecialchars($user_id) ?></span>
            </div>
            <?php endif; ?>
        </div>
    </div>

    <?php if ($userType === 'viewer'): ?>
    <!-- 관람자: 찜한 공연 목록 -->
    <div class="bg-gray-800 rounded-2xl p-4">
        <h3 class="text-xl font-bold text-white mb-4 flex items-center gap-2">
            <i data-lucide="heart" class="text-red-500" style="width: 20px; height: 20px;"></i>
            찜한 공연
        </h3>
        
        <?php if (empty($favoritePerformances)): ?>
        <div class="flex flex-col items-center justify-center py-8">
            <i data-lucide="heart" class="text-gray-400 mb-3" style="width: 48px; height: 48px;"></i>
            <p class="text-gray-400 text-sm mb-4">찜한 공연이 없습니다</p>
            <a href="index.php?page=split&appPage=home" class="px-4 py-2 bg-purple-600 text-white rounded-lg text-sm font-bold hover:bg-purple-700 transition-colors">
                공연 찾아보기
            </a>
        </div>
        <?php else: ?>
        <div class="space-y-3">
            <?php foreach ($favoritePerformances as $perf): ?>
            <div onclick="showPerformanceModal(<?= htmlspecialchars(json_encode($perf)) ?>)" class="bg-gray-700/50 rounded-xl p-3 cursor-pointer hover:bg-gray-700 transition-colors">
                <div class="flex items-center gap-3">
                    <div class="text-3xl"><?= htmlspecialchars($perf['image']) ?></div>
                    <div class="flex-1">
                        <div class="flex items-center gap-2 mb-1">
                            <h4 class="font-bold text-white"><?= htmlspecialchars($perf['buskerName']) ?></h4>
                            <?php if ($perf['status'] === '진행중'): ?>
                            <span class="px-2 py-0.5 bg-red-500 text-white text-xs rounded-full">LIVE</span>
                            <?php endif; ?>
                        </div>
                        <p class="text-xs text-gray-400"><?= htmlspecialchars($perf['location']) ?></p>
                    </div>
                    <a href="index.php?page=split&appPage=mypage&toggleFavorite=<?= $perf['id'] ?>" onclick="event.stopPropagation();" class="p-2 hover:bg-gray-600 rounded-full">
                        <i data-lucide="heart" class="fill-red-500 text-red-500" style="width: 18px; height: 18px;"></i>
                    </a>
                </div>
            </div>
            <?php endforeach; ?>
        </div>
        <?php endif; ?>
    </div>

    <?php else: ?>
    <!-- 아티스트: 버스커 팀 정보 -->
    <?php if ($buskerInfo): ?>
    <div class="bg-gray-800 rounded-2xl p-4">
        <h3 class="text-xl font-bold text-white mb-4 flex items-center gap-2">
            <i data-lucide="users" style="width: 20px; height: 20px;"></i>
            버스커 팀 정보
        </h3>
        <div class="space-y-3">
            <div class="bg-gray-700/50 rounded-xl p-3">
                <div class="flex items-center justify-between mb-2">
                    <span class="text-sm text-gray-400">팀명</span>
                    <span class="font-bold text-white"><?= htmlspecialchars($buskerInfo['name'] ?? $currentUser['teamName'] ?? '미등록') ?></span>
                </div>
                <?php if (isset($buskerInfo['teamSize'])): ?>
                <div class="flex items-center justify-between mb-2">
                    <span class="text-sm text-gray-400">팀 인원</span>
                    <span class="text-white"><?= htmlspecialchars($buskerInfo['teamSize']) ?>명</span>
                </div>
                <?php endif; ?>
                <?php if (isset($buskerInfo['genre'])): ?>
                <div class="flex items-center justify-between mb-2">
                    <span class="text-sm text-gray-400">장르</span>
                    <span class="text-white"><?= htmlspecialchars($buskerInfo['genre']) ?></span>
                </div>
                <?php endif; ?>
                <?php if (isset($buskerInfo['activityLocation'])): ?>
                <div class="flex items-center justify-between">
                    <span class="text-sm text-gray-400">활동 지역</span>
                    <span class="text-white"><?= htmlspecialchars($buskerInfo['activityLocation']) ?></span>
                </div>
                <?php endif; ?>
            </div>
            <a href="index.php?page=split&appPage=register" class="block w-full text-center py-2 bg-purple-600 text-white rounded-lg font-bold hover:bg-purple-700 transition-colors">
                팀 정보 수정하기
            </a>
        </div>
    </div>
    <?php else: ?>
    <div class="bg-gray-800 rounded-2xl p-4">
        <h3 class="text-xl font-bold text-white mb-4 flex items-center gap-2">
            <i data-lucide="users" style="width: 20px; height: 20px;"></i>
            버스커 팀 정보
        </h3>
        <div class="flex flex-col items-center justify-center py-6">
            <i data-lucide="user-plus" class="text-gray-400 mb-3" style="width: 48px; height: 48px;"></i>
            <p class="text-gray-400 text-sm mb-4">아직 버스커 팀을 등록하지 않았습니다</p>
            <a href="index.php?page=split&appPage=register" class="px-4 py-2 bg-purple-600 text-white rounded-lg text-sm font-bold hover:bg-purple-700 transition-colors">
                팀 등록하기
            </a>
        </div>
    </div>
    <?php endif; ?>

    <!-- 아티스트: 예약한 공연 목록 -->
    <div class="bg-gray-800 rounded-2xl p-4">
        <h3 class="text-xl font-bold text-white mb-4 flex items-center gap-2">
            <i data-lucide="calendar" style="width: 20px; height: 20px;"></i>
            내가 예약한 공연
        </h3>
        
        <?php if (empty($myPerformances)): ?>
        <div class="flex flex-col items-center justify-center py-8">
            <i data-lucide="calendar-x" class="text-gray-400 mb-3" style="width: 48px; height: 48px;"></i>
            <p class="text-gray-400 text-sm mb-4">예약한 공연이 없습니다</p>
            <a href="index.php?page=split&appPage=booking" class="px-4 py-2 bg-purple-600 text-white rounded-lg text-sm font-bold hover:bg-purple-700 transition-colors">
                공연 예약하기
            </a>
        </div>
        <?php else: ?>
        <div class="space-y-3">
            <?php foreach ($myPerformances as $perf): ?>
            <div onclick="showPerformanceModal(<?= htmlspecialchars(json_encode($perf)) ?>)" class="bg-gray-700/50 rounded-xl p-3 cursor-pointer hover:bg-gray-700 transition-colors">
                <div class="flex items-center gap-3">
                    <div class="text-3xl"><?= htmlspecialchars($perf['image']) ?></div>
                    <div class="flex-1">
                        <div class="flex items-center gap-2 mb-1">
                            <h4 class="font-bold text-white"><?= htmlspecialchars($perf['buskerName']) ?></h4>
                            <?php if ($perf['status'] === '진행중'): ?>
                            <span class="px-2 py-0.5 bg-red-500 text-white text-xs rounded-full">LIVE</span>
                            <?php elseif ($perf['status'] === '예정'): ?>
                            <span class="px-2 py-0.5 bg-purple-500 text-white text-xs rounded-full">예정</span>
                            <?php endif; ?>
                        </div>
                        <p class="text-xs text-gray-400"><?= htmlspecialchars($perf['location']) ?></p>
                        <?php if (isset($perf['performanceDate'])): ?>
                        <p class="text-xs text-gray-500 mt-1"><?= htmlspecialchars($perf['performanceDate']) ?></p>
                        <?php endif; ?>
                    </div>
                    <a href="index.php?page=split&appPage=mypage&deletePerformance=<?= htmlspecialchars($perf['id']) ?>" 
                       onclick="event.stopPropagation(); return confirm('정말 이 공연을 삭제하시겠습니까?');" 
                       class="p-2 hover:bg-red-900/50 rounded-full text-red-400">
                        <i data-lucide="trash-2" style="width: 18px; height: 18px;"></i>
                    </a>
                </div>
            </div>
            <?php endforeach; ?>
        </div>
        <?php endif; ?>
    </div>
    <?php endif; ?>

    <!-- 로그아웃 버튼 -->
    <div class="bg-gray-800 rounded-2xl p-4">
        <a href="index.php?page=split&appPage=mypage&logout=1" 
           onclick="return confirm('정말 로그아웃하시겠습니까?');" 
           class="block w-full text-center py-3 bg-red-600 text-white rounded-lg font-bold hover:bg-red-700 transition-colors">
            로그아웃
        </a>
    </div>
</div>

<script>
// Lucide 아이콘 초기화
if (typeof lucide !== 'undefined') {
    lucide.createIcons();
}
</script>
