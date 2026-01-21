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
    // split 페이지인 경우 appPage 파라미터 유지
    if ($page === 'split' && isset($_POST['appPage'])) {
        header('Location: index.php?page=split&appPage=' . urlencode($_POST['appPage']));
    } else {
        header('Location: index.php?page=' . ($page ?: 'split'));
    }
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
    // split 페이지인 경우 appPage 파라미터 유지
    if ($page === 'split') {
        $appPage = $_GET['appPage'] ?? 'home';
        // favorites 페이지에서 찜 해제 시에도 favorites 페이지에 머물기
        if ($appPage === 'favorites' && !in_array($id, $_SESSION['favorites'])) {
            header('Location: index.php?page=split&appPage=favorites');
        } else {
            header('Location: index.php?page=split&appPage=' . $appPage);
        }
    } else {
        $redirectPage = $page ?: 'split';
        header('Location: index.php?page=' . $redirectPage);
    }
    exit;
}

// 지역 선택 처리
if (isset($_GET['location'])) {
    $_SESSION['selectedLocation'] = $_GET['location'];
    // split 페이지인 경우 appPage 파라미터 유지
    if ($page === 'split') {
        $appPage = $_GET['appPage'] ?? 'home';
        header('Location: index.php?page=split&appPage=' . $appPage . '&location=' . urlencode($_GET['location']));
    } else {
        header('Location: index.php?page=' . $page);
    }
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
    <meta name="theme-color" content="#9333ea">
    <meta name="description" content="당신의 일상 가까이에서 울리는 음악 - 내 주변 버스킹 공연 찾기">
    <title>버스킹고 - 내 주변 버스킹 찾기</title>
    
    <!-- PWA Manifest -->
    <link rel="manifest" href="manifest.json">
    <link rel="apple-touch-icon" href="assets/images/icon-192.png">
    
    <!-- Tailwind CSS -->
    <script src="https://cdn.tailwindcss.com"></script>
    <!-- Leaflet CSS -->
    <link rel="stylesheet" href="https://unpkg.com/leaflet@1.9.4/dist/leaflet.css" />
    <!-- Lucide Icons -->
    <script src="https://unpkg.com/lucide@latest"></script>
    <!-- 커스텀 CSS -->
    <link rel="stylesheet" href="assets/css/style.css" />
</head>
<body class="night-sky-bg text-white">
    <!-- 전체 화면 배경 별 효과 (하나로 통합) -->
    <div class="fixed inset-0 stars-background -z-10"></div>
    
    <!-- 도시 실루엣 (전체 화면 하단) -->
    <div class="fixed bottom-0 left-0 right-0 h-40 city-silhouette -z-10">
        <svg class="w-full h-full" viewBox="0 0 1200 200" preserveAspectRatio="none">
            <path d="M0,200 L0,180 L30,170 L60,175 L90,160 L120,165 L150,150 L180,155 L210,140 L240,145 L270,130 L300,135 L330,120 L360,125 L390,110 L420,115 L450,100 L480,105 L510,90 L540,95 L570,80 L600,85 L630,70 L660,75 L690,60 L720,65 L750,50 L780,55 L810,40 L840,45 L870,30 L900,35 L930,20 L960,25 L990,15 L1020,20 L1050,10 L1080,15 L1110,5 L1140,10 L1170,0 L1200,5 L1200,200 Z" 
                  fill="url(#cityGradientFull)" opacity="0.8"/>
            <defs>
                <linearGradient id="cityGradientFull" x1="0%" y1="0%" x2="0%" y2="100%">
                    <stop offset="0%" style="stop-color:#1f2937;stop-opacity:0" />
                    <stop offset="50%" style="stop-color:#111827;stop-opacity:0.5" />
                    <stop offset="100%" style="stop-color:#030712;stop-opacity:1" />
                </linearGradient>
            </defs>
        </svg>
    </div>
    
    <!-- 빛나는 효과 -->
    <div class="fixed inset-0 glow-effect -z-10"></div>
    
    <div class="min-h-screen relative z-0">
        <?php 
        // split 페이지가 메인이므로 일반 헤더는 사용하지 않음
        // 필요시 아래 주석을 해제하여 사용 가능
        /*
        if ($page !== 'split'): 
            include __DIR__ . '/includes/header.php'; 
        endif;
        */
        ?>

        <main class="<?= ($page === 'landing' || ($page ?? '') === 'landing') ? '' : 'max-w-6xl mx-auto px-4 py-8' ?>">
            <?php
            switch ($page) {
                case 'split':
                    include __DIR__ . '/pages/split.php';
                    break;
                // 기존 웹 페이지들은 더 이상 사용하지 않음 (split 페이지가 메인)
                // 필요시 아래 주석을 해제하여 사용 가능
                /*
                case 'landing':
                    include __DIR__ . '/pages/landing.php';
                    break;
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
                */
                default:
                    // 기본값: 좌우 분할 페이지 (메인 페이지)
                    include __DIR__ . '/pages/split.php';
            }
            ?>
        </main>
    </div>

    <!-- 모달들 -->
    <div id="userTypeModal" class="hidden fixed inset-0 bg-black/80 flex items-center justify-center z-50 p-4">
        <div class="bg-gray-800 rounded-2xl max-w-2xl w-full p-8 shadow-xl border border-gray-700">
            <h2 class="text-3xl font-bold mb-6 text-white text-center">사용자 유형 선택</h2>
            <form method="POST" class="grid grid-cols-2 gap-4">
                <input type="hidden" name="page" value="<?= htmlspecialchars($page === 'split' ? 'split' : ($page ?: 'split')) ?>">
                <?php if ($page === 'split'): ?>
                <input type="hidden" name="appPage" value="<?= htmlspecialchars($_GET['appPage'] ?? 'home') ?>">
                <?php endif; ?>
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
        
        // PWA Service Worker 등록
        if ('serviceWorker' in navigator) {
            window.addEventListener('load', () => {
                navigator.serviceWorker.register('/sw.js')
                    .then((registration) => {
                        console.log('ServiceWorker 등록 성공:', registration.scope);
                    })
                    .catch((error) => {
                        console.log('ServiceWorker 등록 실패:', error);
                    });
            });
        }
        
        // PWA 설치 프롬프트 처리
        let deferredPrompt;
        window.addEventListener('beforeinstallprompt', (e) => {
            e.preventDefault();
            deferredPrompt = e;
            // 설치 버튼 표시 로직을 여기에 추가할 수 있습니다
        });
    </script>
    </div>
</body>
</html>
