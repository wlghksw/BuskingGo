<?php
/**
 * 버스킹고 메인 애플리케이션 파일
 * 전체 앱의 라우팅 및 전역 상태를 관리합니다.
 */
session_start();

// 상수 파일 로드
require_once __DIR__ . '/config/constants.php';

// 페이지 라우팅 (기본값: home)
$page = $_GET['page'] ?? 'home';

// 세션 초기화 (없는 경우)
if (!isset($_SESSION['userType'])) {
    $_SESSION['userType'] = null;
}
if (!isset($_SESSION['favorites'])) {
    $_SESSION['favorites'] = [];
}
if (!isset($_SESSION['selectedLocation'])) {
    $_SESSION['selectedLocation'] = '';
}

// 사용자 유형 설정 처리
if (isset($_POST['userType'])) {
    $_SESSION['userType'] = $_POST['userType'];
    header('Location: index.php?page=' . $page);
    exit;
}

// 찜하기 토글 처리
if (isset($_GET['toggleFavorite'])) {
    $id = (int)$_GET['toggleFavorite'];
    if (in_array($id, $_SESSION['favorites'])) {
        $_SESSION['favorites'] = array_values(array_filter($_SESSION['favorites'], fn($fid) => $fid !== $id));
    } else {
        $_SESSION['favorites'][] = $id;
    }
    header('Location: index.php?page=' . $page);
    exit;
}

// 지역 선택 처리
if (isset($_GET['location'])) {
    $_SESSION['selectedLocation'] = $_GET['location'];
    header('Location: index.php?page=' . $page);
    exit;
}

// 공연 필터링
$selectedLocation = $_SESSION['selectedLocation'];
$filteredPerformances = $samplePerformances;
if ($selectedLocation) {
    $filteredPerformances = array_filter($samplePerformances, function($perf) use ($selectedLocation) {
        return stripos($perf['location'], $selectedLocation) !== false;
    });
}

// 지도 중심 좌표 설정
$userLocation = $defaultLocation;
if ($selectedLocation && isset($locationCoordinates[$selectedLocation])) {
    $userLocation = $locationCoordinates[$selectedLocation];
}
?>
<!DOCTYPE html>
<html lang="ko">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>버스킹고 - 내 주변 버스킹 찾기</title>
    <!-- Tailwind CSS -->
    <script src="https://cdn.tailwindcss.com"></script>
    <!-- Leaflet CSS -->
    <link rel="stylesheet" href="https://unpkg.com/leaflet@1.9.4/dist/leaflet.css" />
    <!-- Lucide Icons -->
    <script src="https://unpkg.com/lucide@latest"></script>
    <!-- 커스텀 CSS -->
    <link rel="stylesheet" href="assets/css/style.css" />
</head>
<body>
    <div class="min-h-screen bg-gradient-to-b from-gray-900 via-slate-900 to-gray-900 text-white">
        <?php include __DIR__ . '/includes/header.php'; ?>

        <main class="max-w-6xl mx-auto px-4 py-8">
            <?php
            switch ($page) {
                case 'home':
                    include __DIR__ . '/pages/home.php';
                    break;
                case 'register':
                    include __DIR__ . '/pages/register.php';
                    break;
                case 'booking':
                    include __DIR__ . '/pages/booking.php';
                    break;
                case 'community':
                    include __DIR__ . '/board/community.php';
                    break;
                default:
                    include __DIR__ . '/pages/home.php';
            }
            ?>
        </main>
    </div>

    <!-- 모달들 -->
    <div id="userTypeModal" class="hidden fixed inset-0 bg-black/80 flex items-center justify-center z-50 p-4">
        <div class="bg-gray-800 rounded-2xl max-w-2xl w-full p-8 shadow-xl border border-gray-700">
            <h2 class="text-3xl font-bold mb-6 text-white text-center">사용자 유형 선택</h2>
            <form method="POST" class="grid grid-cols-2 gap-4">
                <input type="hidden" name="page" value="<?= htmlspecialchars($page) ?>">
                <button type="submit" name="userType" value="viewer" class="p-6 border-2 border-gray-700 rounded-xl hover:border-purple-500 hover:bg-purple-900/20 transition-all text-left">
                    <i data-lucide="user" class="text-purple-400 mb-3" style="width: 32px; height: 32px;"></i>
                    <h3 class="font-bold text-lg mb-2 text-white">관람자</h3>
                    <p class="text-sm text-gray-400">일반 시민</p>
                </button>
                <button type="submit" name="userType" value="artist" class="p-6 border-2 border-gray-700 rounded-xl hover:border-purple-500 hover:bg-purple-900/20 transition-all text-left">
                    <i data-lucide="music" class="text-purple-400 mb-3" style="width: 32px; height: 32px;"></i>
                    <h3 class="font-bold text-lg mb-2 text-white">아티스트</h3>
                    <p class="text-sm text-gray-400">버스커</p>
                </button>
            </form>
        </div>
    </div>

    <div id="performanceModal" class="hidden fixed inset-0 bg-black/80 flex items-center justify-center z-50 p-4"></div>

    <!-- Leaflet JS -->
    <script src="https://unpkg.com/leaflet@1.9.4/dist/leaflet.js"></script>
    <!-- 앱 JavaScript -->
    <script src="assets/js/app.js"></script>
    <script>
        // Lucide 아이콘 초기화
        lucide.createIcons();
        
        // 사용자 유형 모달 표시
        function showUserTypeModal() {
            document.getElementById('userTypeModal').classList.remove('hidden');
        }
        
        // 모달 닫기
        document.getElementById('userTypeModal').addEventListener('click', function(e) {
            if (e.target === this) {
                this.classList.add('hidden');
            }
        });
    </script>
</body>
</html>
