<?php
/**
 * 버스킹고 메인 애플리케이션 파일
 * 전체 앱의 라우팅 및 전역 상태를 관리합니다.
 */
session_start();

// 상수 파일 로드
require_once __DIR__ . '/config/constants.php';

// 페이지 라우팅 (기본값: split)
$page = $_GET['page'] ?? 'split';

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
    // split 페이지로 리다이렉트
    $appPage = $_POST['appPage'] ?? 'home';
    header('Location: index.php?page=split&appPage=' . urlencode($appPage));
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
    // split 페이지로 리다이렉트
    $appPage = $_GET['appPage'] ?? 'home';
    // favorites 페이지에서 찜 해제 시에도 favorites 페이지에 머물기
    if ($appPage === 'favorites' && !in_array($id, $_SESSION['favorites'])) {
        header('Location: index.php?page=split&appPage=favorites');
    } else {
        header('Location: index.php?page=split&appPage=' . $appPage);
    }
    exit;
}

// 지역 선택 처리
if (isset($_GET['location'])) {
    $_SESSION['selectedLocation'] = $_GET['location'];
    // split 페이지로 리다이렉트
    $appPage = $_GET['appPage'] ?? 'home';
    header('Location: index.php?page=split&appPage=' . $appPage . '&location=' . urlencode($_GET['location']));
    exit;
}

// 버스커 등록 처리
if (isset($_POST['name']) && isset($_POST['phone']) && !isset($_POST['writePost']) && !isset($_POST['writeComment'])) {
    // 버스커 등록 폼에서 온 요청인지 확인
    $name = $_POST['name'] ?? '';
    $phone = $_POST['phone'] ?? '';
    
    if ($name && $phone) {
        // 세션에 버스커 데이터 초기화
        if (!isset($_SESSION['buskers'])) {
            $_SESSION['buskers'] = [];
        }
        
        // 새 버스커 데이터 생성
        $newBusker = [
            'id' => time() . rand(1000, 9999),
            'name' => $name,
            'teamSize' => isset($_POST['teamSize']) ? (int)$_POST['teamSize'] : 1,
            'equipment' => $_POST['equipment'] ?? '',
            'phone' => $phone,
            'bio' => $_POST['bio'] ?? '',
            'availableDays' => isset($_POST['availableDays']) && $_POST['availableDays'] ? explode(',', $_POST['availableDays']) : [],
            'preferredTime' => $_POST['preferredTime'] ?? '',
            'preferredLocation' => $_POST['preferredLocation'] ?? '',
            'createdAt' => date('Y-m-d H:i:s'),
            'rating' => 0,
            'performanceCount' => 0
        ];
        
        $_SESSION['buskers'][] = $newBusker;
        header('Location: index.php?page=split&appPage=register&success=1');
        exit;
    }
}

// 공연 예약 처리
if (isset($_POST['organizerName']) && isset($_POST['organizerType']) && isset($_POST['location']) && isset($_POST['date']) && !isset($_POST['writePost']) && !isset($_POST['writeComment'])) {
    // 공연 예약 폼에서 온 요청인지 확인
    $organizerName = $_POST['organizerName'] ?? '';
    $organizerType = $_POST['organizerType'] ?? '';
    $location = $_POST['location'] ?? '';
    $date = $_POST['date'] ?? '';
    $startTime = $_POST['startTime'] ?? '';
    $endTime = $_POST['endTime'] ?? '';
    
    if ($organizerName && $organizerType && $location && $date && $startTime && $endTime) {
        // 세션에 예약 데이터 초기화
        if (!isset($_SESSION['bookings'])) {
            $_SESSION['bookings'] = [];
        }
        
        // 새 예약 데이터 생성
        $newBooking = [
            'id' => time() . rand(1000, 9999),
            'organizerName' => $organizerName,
            'organizerType' => $organizerType,
            'location' => $location,
            'lat' => $_POST['lat'] ?? null,
            'lng' => $_POST['lng'] ?? null,
            'date' => $date,
            'startTime' => $startTime,
            'endTime' => $endTime,
            'additionalRequest' => $_POST['additionalRequest'] ?? '',
            'buskerId' => $_POST['buskerId'] ?? null,
            'status' => '대기중',
            'createdAt' => date('Y-m-d H:i:s'),
            'createdBy' => $_SESSION['userType'] ?? 'viewer'
        ];
        
        $_SESSION['bookings'][] = $newBooking;
        header('Location: index.php?page=split&appPage=booking&success=1');
        exit;
    }
}

// 커뮤니티 게시글 및 댓글 초기화 (세션에 저장)
if (!isset($_SESSION['communityPosts'])) {
    $_SESSION['communityPosts'] = $communityPosts;
}
if (!isset($_SESSION['communityComments'])) {
    $_SESSION['communityComments'] = [];
}

// 커뮤니티 게시글 작성 처리
if (isset($_POST['writePost'])) {
    $tab = $_POST['tab'] ?? 'free';
    $title = $_POST['title'] ?? '';
    $content = $_POST['content'] ?? '';
    $author = $_SESSION['userType'] === 'artist' ? '아티스트' : '사용자';
    
    if ($title && $content) {
        $newPost = [
            'id' => time(), // 임시 ID
            'title' => $title,
            'content' => $content,
            'author' => $author,
            'date' => date('Y-m-d'),
            'views' => 0,
            'comments' => 0
        ];
        
        // 탭별 추가 필드
        if ($tab === 'recruit') {
            $newPost['location'] = $_POST['location'] ?? '천안';
            $newPost['genre'] = $_POST['genre'] ?? '';
        } elseif ($tab === 'collab') {
            $newPost['performanceDate'] = $_POST['performanceDate'] ?? '';
            $newPost['location'] = $_POST['location'] ?? '';
        }
        
        $_SESSION['communityPosts'][$tab][] = $newPost;
        header('Location: index.php?page=split&appPage=community&tab=' . $tab);
        exit;
    }
}

// 댓글 작성 처리
if (isset($_POST['writeComment'])) {
    $postId = (int)$_POST['postId'];
    $tab = $_POST['tab'] ?? 'free';
    $comment = $_POST['comment'] ?? '';
    $author = $_SESSION['userType'] === 'artist' ? '아티스트' : '사용자';
    
    if ($comment) {
        $newComment = [
            'id' => time(),
            'postId' => $postId,
            'tab' => $tab,
            'author' => $author,
            'content' => $comment,
            'date' => date('Y-m-d H:i')
        ];
        
        if (!isset($_SESSION['communityComments'][$tab])) {
            $_SESSION['communityComments'][$tab] = [];
        }
        $_SESSION['communityComments'][$tab][] = $newComment;
        
        // 댓글 수 증가
        foreach ($_SESSION['communityPosts'][$tab] as &$post) {
            if ($post['id'] == $postId) {
                $post['comments'] = ($post['comments'] ?? 0) + 1;
                break;
            }
        }
        
        header('Location: index.php?page=split&appPage=community&tab=' . $tab . '&postId=' . $postId);
        exit;
    }
}

// 공연 필터링
$selectedLocation = $_SESSION['selectedLocation'];
$filteredPerformances = $samplePerformances;
if ($selectedLocation) {
    $filteredPerformances = array_filter($samplePerformances, function($perf) use ($selectedLocation) {
        return stripos($perf['location'], $selectedLocation) !== false;
    });
}

// 커뮤니티 게시글 데이터 (세션에서 가져오기, 없으면 기본값 사용)
if (isset($_SESSION['communityPosts'])) {
    // 세션 데이터와 기본 데이터 병합
    foreach ($communityPosts as $tab => $defaultPosts) {
        if (isset($_SESSION['communityPosts'][$tab])) {
            // 세션 데이터를 앞에 추가 (최신순)
            $communityPosts[$tab] = array_merge($_SESSION['communityPosts'][$tab], $defaultPosts);
        }
    }
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
        <main>
            <?php
            switch ($page) {
                case 'split':
                    include __DIR__ . '/pages/split.php';
                    break;
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
            <div class="flex items-center justify-between mb-6">
                <h2 class="text-3xl font-bold text-white">로그인 / 회원가입</h2>
                <button onclick="closeUserTypeModal()" class="p-2 hover:bg-gray-700 rounded-full text-gray-400">
                    <i data-lucide="x" style="width: 24px; height: 24px;"></i>
                </button>
            </div>
            
            <!-- 탭 전환 -->
            <div class="flex gap-2 mb-6 border-b border-gray-700">
                <button onclick="showLoginTab()" id="loginTabBtn" class="px-4 py-2 font-bold text-purple-400 border-b-2 border-purple-400">로그인</button>
                <button onclick="showRegisterTab()" id="registerTabBtn" class="px-4 py-2 font-bold text-gray-400 hover:text-gray-300">회원가입</button>
            </div>
            
            <!-- 로그인 탭 -->
            <div id="loginTab" class="space-y-4">
                <form id="loginForm" onsubmit="handleLogin(event)" class="space-y-4">
                    <div>
                        <label class="block text-sm font-bold mb-2 text-gray-300">아이디 *</label>
                        <input type="text" name="username" required class="w-full bg-gray-900 border border-gray-700 rounded-lg px-4 py-3 focus:outline-none focus:border-purple-500 text-white placeholder-gray-500" placeholder="아이디를 입력하세요" />
                    </div>
                    <div>
                        <label class="block text-sm font-bold mb-2 text-gray-300">비밀번호 *</label>
                        <input type="password" name="password" required class="w-full bg-gray-900 border border-gray-700 rounded-lg px-4 py-3 focus:outline-none focus:border-purple-500 text-white placeholder-gray-500" placeholder="비밀번호를 입력하세요" />
                    </div>
                    <button type="submit" class="w-full bg-gradient-to-r from-purple-600 to-pink-600 text-white font-bold py-4 rounded-lg hover:scale-105 transition-transform">
                        로그인
                    </button>
                </form>
            </div>
            
            <!-- 회원가입 탭 -->
            <div id="registerTab" class="hidden space-y-4 max-h-[80vh] overflow-y-auto">
                <form id="registerForm" onsubmit="handleRegister(event)" class="space-y-4">
                    <!-- 기본 정보 (공통) -->
                    <div class="border-b border-gray-700 pb-4">
                        <h3 class="text-lg font-bold mb-4 text-white">기본 정보</h3>
                        
                        <div class="space-y-4">
                            <div>
                                <label class="block text-sm font-bold mb-2 text-gray-300">아이디 *</label>
                                <input type="text" name="username" required pattern="[a-zA-Z0-9_]{3,20}" class="w-full bg-gray-900 border border-gray-700 rounded-lg px-4 py-3 focus:outline-none focus:border-purple-500 text-white placeholder-gray-500" placeholder="3-20자의 영문, 숫자, 언더스코어" />
                                <p class="text-xs text-gray-500 mt-1">3-20자의 영문, 숫자, 언더스코어(_)만 사용 가능</p>
                            </div>
                            
                            <div>
                                <label class="block text-sm font-bold mb-2 text-gray-300">비밀번호 *</label>
                                <input type="password" name="password" id="registerPassword" required minlength="8" class="w-full bg-gray-900 border border-gray-700 rounded-lg px-4 py-3 focus:outline-none focus:border-purple-500 text-white placeholder-gray-500" placeholder="8자 이상, 영문+숫자 조합" />
                                <p class="text-xs text-gray-500 mt-1">8자 이상, 영문과 숫자를 포함해야 합니다</p>
                                <div id="passwordStrength" class="text-xs mt-1"></div>
                            </div>
                            
                            <div>
                                <label class="block text-sm font-bold mb-2 text-gray-300">이름/닉네임 *</label>
                                <input type="text" name="name" required class="w-full bg-gray-900 border border-gray-700 rounded-lg px-4 py-3 focus:outline-none focus:border-purple-500 text-white placeholder-gray-500" placeholder="서비스 내 표시명" />
                            </div>
                            
                            <div>
                                <label class="block text-sm font-bold mb-2 text-gray-300">연락처 *</label>
                                <input type="tel" name="phone" required class="w-full bg-gray-900 border border-gray-700 rounded-lg px-4 py-3 focus:outline-none focus:border-purple-500 text-white placeholder-gray-500" placeholder="010-0000-0000" />
                                <p class="text-xs text-gray-500 mt-1">예약 및 매칭 시 연락용</p>
                            </div>
                            
                            <div>
                                <label class="block text-sm font-bold mb-2 text-gray-300">사용자 유형 *</label>
                                <div class="grid grid-cols-2 gap-4">
                                    <label class="p-4 border-2 border-gray-700 rounded-xl hover:border-purple-500 hover:bg-purple-900/20 transition-all cursor-pointer">
                                        <input type="radio" name="userType" value="viewer" required class="mr-2" onchange="updateUserTypeFields()" />
                                        <span class="text-white">관람자</span>
                                    </label>
                                    <label class="p-4 border-2 border-gray-700 rounded-xl hover:border-purple-500 hover:bg-purple-900/20 transition-all cursor-pointer">
                                        <input type="radio" name="userType" value="artist" required class="mr-2" onchange="updateUserTypeFields()" />
                                        <span class="text-white">버스커</span>
                                    </label>
                                </div>
                            </div>
                        </div>
                    </div>
                    
                    <!-- 관람자 추가 정보 -->
                    <div id="viewerFields" class="hidden border-b border-gray-700 pb-4">
                        <h3 class="text-lg font-bold mb-4 text-white">관람자 정보</h3>
                        
                        <div class="space-y-4">
                            <div>
                                <label class="block text-sm font-bold mb-2 text-gray-300">관심 장르 (선택)</label>
                                <div class="grid grid-cols-3 gap-2">
                                    <?php foreach ($genres as $genreName => $genreValue): ?>
                                    <label class="flex items-center p-2 bg-gray-900 border border-gray-700 rounded-lg hover:border-purple-500 cursor-pointer">
                                        <input type="checkbox" name="interestedGenres[]" value="<?= $genreValue ?>" class="mr-2" />
                                        <span class="text-sm text-white"><?= $genreName ?></span>
                                    </label>
                                    <?php endforeach; ?>
                                </div>
                            </div>
                            
                            <div>
                                <label class="block text-sm font-bold mb-2 text-gray-300">선호하는 공연 시간대 (선택)</label>
                                <div class="grid grid-cols-2 gap-2">
                                    <?php foreach ($timeSlots as $timeName => $timeValue): ?>
                                    <label class="flex items-center p-2 bg-gray-900 border border-gray-700 rounded-lg hover:border-purple-500 cursor-pointer">
                                        <input type="checkbox" name="preferredTimeSlots[]" value="<?= $timeValue ?>" class="mr-2" />
                                        <span class="text-sm text-white"><?= $timeName ?></span>
                                    </label>
                                    <?php endforeach; ?>
                                </div>
                            </div>
                        </div>
                    </div>
                    
                    <!-- 버스커 추가 정보 -->
                    <div id="artistFields" class="hidden border-b border-gray-700 pb-4">
                        <h3 class="text-lg font-bold mb-4 text-white">버스커 정보</h3>
                        
                        <div class="space-y-4">
                            <div>
                                <label class="block text-sm font-bold mb-2 text-gray-300">팀명/예명 *</label>
                                <input type="text" name="teamName" class="w-full bg-gray-900 border border-gray-700 rounded-lg px-4 py-3 focus:outline-none focus:border-purple-500 text-white placeholder-gray-500" placeholder="공개 프로필명" />
                                <p class="text-xs text-gray-500 mt-1">공개 프로필에 표시될 이름</p>
                            </div>
                            
                            <div>
                                <label class="block text-sm font-bold mb-2 text-gray-300">공연 장르 * (복수 선택 가능)</label>
                                <div class="grid grid-cols-3 gap-2">
                                    <?php foreach ($genres as $genreName => $genreValue): ?>
                                    <label class="flex items-center p-2 bg-gray-900 border border-gray-700 rounded-lg hover:border-purple-500 cursor-pointer">
                                        <input type="checkbox" name="performanceGenres[]" value="<?= $genreValue ?>" class="mr-2" />
                                        <span class="text-sm text-white"><?= $genreName ?></span>
                                    </label>
                                    <?php endforeach; ?>
                                </div>
                            </div>
                            
                            <div>
                                <label class="block text-sm font-bold mb-2 text-gray-300">대표 연락처 *</label>
                                <input type="tel" name="contactPhone" class="w-full bg-gray-900 border border-gray-700 rounded-lg px-4 py-3 focus:outline-none focus:border-purple-500 text-white placeholder-gray-500" placeholder="010-0000-0000" />
                                <p class="text-xs text-gray-500 mt-1">예약 문의용 연락처</p>
                            </div>
                            
                            <div>
                                <label class="block text-sm font-bold mb-2 text-gray-300">활동 지역 *</label>
                                <select name="activityLocation" class="w-full bg-gray-900 border border-gray-700 rounded-lg px-4 py-3 focus:outline-none focus:border-purple-500 text-white">
                                    <option value="">선택하세요</option>
                                    <?php foreach ($locationCoordinates as $loc => $coords): ?>
                                    <option value="<?= htmlspecialchars($loc) ?>"><?= htmlspecialchars($loc) ?></option>
                                    <?php endforeach; ?>
                                </select>
                                <p class="text-xs text-gray-500 mt-1">주요 공연 지역</p>
                            </div>
                        </div>
                    </div>
                    
                    <!-- 선택 정보 (공통) -->
                    <div class="border-b border-gray-700 pb-4">
                        <h3 class="text-lg font-bold mb-4 text-white">선택 정보</h3>
                        
                        <div class="space-y-4">
                            <div>
                                <label class="block text-sm font-bold mb-2 text-gray-300">관심 지역</label>
                                <select name="interestedLocation" class="w-full bg-gray-900 border border-gray-700 rounded-lg px-4 py-3 focus:outline-none focus:border-purple-500 text-white">
                                    <option value="">선택하세요</option>
                                    <?php foreach ($locationCoordinates as $loc => $coords): ?>
                                    <option value="<?= htmlspecialchars($loc) ?>"><?= htmlspecialchars($loc) ?></option>
                                    <?php endforeach; ?>
                                </select>
                                <p class="text-xs text-gray-500 mt-1">주로 활동하거나 공연을 찾는 지역</p>
                            </div>
                            
                            <div>
                                <label class="flex items-center gap-2 cursor-pointer">
                                    <input type="checkbox" name="emailNotification" value="1" class="w-4 h-4" />
                                    <span class="text-sm text-gray-300">이메일 마케팅 수신 동의</span>
                                </label>
                            </div>
                            
                            <div>
                                <label class="flex items-center gap-2 cursor-pointer">
                                    <input type="checkbox" name="smsNotification" value="1" class="w-4 h-4" />
                                    <span class="text-sm text-gray-300">SMS 마케팅 수신 동의</span>
                                </label>
                            </div>
                        </div>
                    </div>
                    
                    <button type="submit" class="w-full bg-gradient-to-r from-purple-600 to-pink-600 text-white font-bold py-4 rounded-lg hover:scale-105 transition-transform">
                        회원가입
                    </button>
                </form>
            </div>
            
            <div id="authMessage" class="mt-4 text-center text-sm"></div>
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
            showLoginTab(); // 기본으로 로그인 탭 표시
        }
        
        function closeUserTypeModal() {
            document.getElementById('userTypeModal').classList.add('hidden');
            document.getElementById('authMessage').textContent = '';
        }
        
        // 모달 닫기 (배경 클릭)
        document.getElementById('userTypeModal').addEventListener('click', function(e) {
            if (e.target === this) {
                closeUserTypeModal();
            }
        });
        
        // 탭 전환 함수들
        function showLoginTab() {
            document.getElementById('loginTab').classList.remove('hidden');
            document.getElementById('registerTab').classList.add('hidden');
            document.getElementById('loginTabBtn').classList.add('text-purple-400', 'border-b-2', 'border-purple-400');
            document.getElementById('loginTabBtn').classList.remove('text-gray-400');
            document.getElementById('registerTabBtn').classList.remove('text-purple-400', 'border-b-2', 'border-purple-400');
            document.getElementById('registerTabBtn').classList.add('text-gray-400');
        }
        
        function showRegisterTab() {
            document.getElementById('loginTab').classList.add('hidden');
            document.getElementById('registerTab').classList.remove('hidden');
            document.getElementById('registerTabBtn').classList.add('text-purple-400', 'border-b-2', 'border-purple-400');
            document.getElementById('registerTabBtn').classList.remove('text-gray-400');
            document.getElementById('loginTabBtn').classList.remove('text-purple-400', 'border-b-2', 'border-purple-400');
            document.getElementById('loginTabBtn').classList.add('text-gray-400');
        }
        
        // 로그인 처리
        async function handleLogin(event) {
            event.preventDefault();
            const formData = new FormData(event.target);
            const data = {
                username: formData.get('username'),
                password: formData.get('password')
            };
            
            try {
                const response = await fetch('/api/auth.php', {
                    method: 'POST',
                    headers: {
                        'Content-Type': 'application/json'
                    },
                    body: JSON.stringify(data)
                });
                
                const result = await response.json();
                
                if (result.success) {
                    document.getElementById('authMessage').textContent = '로그인 성공!';
                    document.getElementById('authMessage').className = 'mt-4 text-center text-sm text-green-400';
                    setTimeout(() => {
                        closeUserTypeModal();
                        location.reload();
                    }, 1000);
                } else {
                    document.getElementById('authMessage').textContent = result.message || '로그인에 실패했습니다.';
                    document.getElementById('authMessage').className = 'mt-4 text-center text-sm text-red-400';
                }
            } catch (error) {
                document.getElementById('authMessage').textContent = '오류가 발생했습니다.';
                document.getElementById('authMessage').className = 'mt-4 text-center text-sm text-red-400';
            }
        }
        
        // 사용자 유형에 따라 필드 표시/숨김
        function updateUserTypeFields() {
            const userType = document.querySelector('input[name="userType"]:checked')?.value;
            const viewerFields = document.getElementById('viewerFields');
            const artistFields = document.getElementById('artistFields');
            
            if (userType === 'viewer') {
                viewerFields.classList.remove('hidden');
                artistFields.classList.add('hidden');
                // 관람자 필드는 선택사항이므로 required 제거
                document.querySelectorAll('#artistFields [required]').forEach(el => el.removeAttribute('required'));
            } else if (userType === 'artist') {
                viewerFields.classList.add('hidden');
                artistFields.classList.remove('hidden');
                // 버스커 필수 필드 설정
                document.querySelector('input[name="teamName"]').setAttribute('required', 'required');
                document.querySelector('input[name="contactPhone"]').setAttribute('required', 'required');
                document.querySelector('select[name="activityLocation"]').setAttribute('required', 'required');
            }
        }
        
        // 비밀번호 강도 검사
        document.getElementById('registerPassword')?.addEventListener('input', function(e) {
            const password = e.target.value;
            const strengthDiv = document.getElementById('passwordStrength');
            
            if (password.length === 0) {
                strengthDiv.textContent = '';
                return;
            }
            
            const hasLetter = /[a-zA-Z]/.test(password);
            const hasNumber = /[0-9]/.test(password);
            const isLongEnough = password.length >= 8;
            
            let strength = [];
            if (!isLongEnough) strength.push('8자 이상');
            if (!hasLetter) strength.push('영문 포함');
            if (!hasNumber) strength.push('숫자 포함');
            
            if (strength.length === 0) {
                strengthDiv.textContent = '✓ 비밀번호 조건을 만족합니다';
                strengthDiv.className = 'text-xs mt-1 text-green-400';
            } else {
                strengthDiv.textContent = '필요: ' + strength.join(', ');
                strengthDiv.className = 'text-xs mt-1 text-red-400';
            }
        });
        
        // 회원가입 처리
        async function handleRegister(event) {
            event.preventDefault();
            const formData = new FormData(event.target);
            const userType = formData.get('userType');
            
            // 기본 데이터
            const data = {
                username: formData.get('username'),
                password: formData.get('password'),
                name: formData.get('name'),
                phone: formData.get('phone'),
                userType: userType
            };
            
            // 비밀번호 유효성 검사
            const password = data.password;
            const hasLetter = /[a-zA-Z]/.test(password);
            const hasNumber = /[0-9]/.test(password);
            const isLongEnough = password.length >= 8;
            
            if (!isLongEnough || !hasLetter || !hasNumber) {
                document.getElementById('authMessage').textContent = '비밀번호는 8자 이상, 영문과 숫자를 포함해야 합니다.';
                document.getElementById('authMessage').className = 'mt-4 text-center text-sm text-red-400';
                return;
            }
            
            // 선택 정보
            data.interestedLocation = formData.get('interestedLocation') || '';
            data.emailNotification = formData.get('emailNotification') === '1';
            data.smsNotification = formData.get('smsNotification') === '1';
            
            // 사용자 유형별 추가 정보
            if (userType === 'viewer') {
                data.interestedGenres = formData.getAll('interestedGenres[]');
                data.preferredTimeSlots = formData.getAll('preferredTimeSlots[]');
            } else if (userType === 'artist') {
                data.teamName = formData.get('teamName');
                data.performanceGenres = formData.getAll('performanceGenres[]');
                data.contactPhone = formData.get('contactPhone');
                data.activityLocation = formData.get('activityLocation');
                
                // 버스커 필수 필드 검증
                if (!data.teamName || !data.contactPhone || !data.activityLocation) {
                    document.getElementById('authMessage').textContent = '버스커 필수 정보를 모두 입력해주세요.';
                    document.getElementById('authMessage').className = 'mt-4 text-center text-sm text-red-400';
                    return;
                }
                
                if (!data.performanceGenres || data.performanceGenres.length === 0) {
                    document.getElementById('authMessage').textContent = '최소 1개 이상의 공연 장르를 선택해주세요.';
                    document.getElementById('authMessage').className = 'mt-4 text-center text-sm text-red-400';
                    return;
                }
            }
            
            try {
                const response = await fetch('/api/register.php', {
                    method: 'POST',
                    headers: {
                        'Content-Type': 'application/json'
                    },
                    body: JSON.stringify(data)
                });
                
                const result = await response.json();
                
                if (result.success) {
                    document.getElementById('authMessage').textContent = '회원가입 성공! 자동으로 로그인됩니다.';
                    document.getElementById('authMessage').className = 'mt-4 text-center text-sm text-green-400';
                    setTimeout(() => {
                        closeUserTypeModal();
                        location.reload();
                    }, 1000);
                } else {
                    document.getElementById('authMessage').textContent = result.message || '회원가입에 실패했습니다.';
                    document.getElementById('authMessage').className = 'mt-4 text-center text-sm text-red-400';
                }
            } catch (error) {
                document.getElementById('authMessage').textContent = '오류가 발생했습니다.';
                document.getElementById('authMessage').className = 'mt-4 text-center text-sm text-red-400';
            }
        }
        
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
