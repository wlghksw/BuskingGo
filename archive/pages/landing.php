<?php
/**
 * 랜딩 페이지 (프로모션 페이지)
 * 앱 다운로드 및 소개 페이지
 */
?>
<div class="min-h-screen flex items-center justify-center relative overflow-hidden">
    <!-- 배경: 별이 있는 밤하늘 + 도시 실루엣 -->
    <div class="absolute inset-0 bg-gradient-to-b from-gray-900 via-purple-900 to-gray-900">
        <!-- 별 효과 -->
        <div class="absolute inset-0" style="background-image: 
            radial-gradient(2px 2px at 20% 30%, white, transparent),
            radial-gradient(2px 2px at 60% 70%, white, transparent),
            radial-gradient(1px 1px at 50% 50%, white, transparent),
            radial-gradient(1px 1px at 80% 10%, white, transparent),
            radial-gradient(2px 2px at 90% 40%, white, transparent),
            radial-gradient(1px 1px at 33% 60%, white, transparent),
            radial-gradient(2px 2px at 10% 80%, white, transparent);
            background-size: 200% 200%;
            animation: twinkle 20s ease-in-out infinite;
        "></div>
        
        <!-- 도시 실루엣 -->
        <div class="absolute bottom-0 left-0 right-0 h-32 bg-gradient-to-t from-gray-800 to-transparent opacity-50">
            <svg class="w-full h-full" viewBox="0 0 1200 120" preserveAspectRatio="none">
                <path d="M0,120 L50,100 L100,110 L150,80 L200,90 L250,70 L300,85 L350,60 L400,75 L450,50 L500,65 L550,45 L600,55 L650,40 L700,50 L750,35 L800,45 L850,30 L900,40 L950,25 L1000,35 L1050,20 L1100,30 L1150,15 L1200,25 L1200,120 Z" fill="currentColor" class="text-gray-900"/>
            </svg>
        </div>
    </div>

    <!-- 메인 콘텐츠 -->
    <div class="relative z-10 max-w-6xl mx-auto px-4 py-12 text-center">
        <!-- 로고 -->
        <div class="mb-8 flex items-center justify-center gap-3">
            <div class="text-6xl">🎵</div>
            <h1 class="text-6xl md:text-7xl font-bold text-white">버스킹고</h1>
        </div>

        <!-- 태그라인 -->
        <p class="text-2xl md:text-3xl text-white mb-12 font-light">
            당신의 일상 가까이에서 울리는 음악
        </p>

        <!-- 다운로드 버튼 및 QR 코드 -->
        <div class="flex flex-col md:flex-row items-center justify-center gap-8 mb-12">
            <!-- 다운로드 버튼들 -->
            <div class="flex flex-col gap-4">
                <!-- Google Play -->
                <a href="#" class="inline-block">
                    <img src="https://upload.wikimedia.org/wikipedia/commons/7/78/Google_Play_Store_badge_EN.svg" 
                         alt="GET IT ON Google Play" 
                         class="h-14 hover:opacity-80 transition-opacity"
                         onerror="this.style.display='none'; this.nextElementSibling.style.display='block';">
                    <div style="display:none;" class="bg-black px-6 py-3 rounded-lg border-2 border-white text-white font-bold hover:bg-gray-800 transition-colors">
                        GET IT ON Google Play
                    </div>
                </a>
                
                <!-- App Store -->
                <a href="#" class="inline-block">
                    <img src="https://tools.applemediaservices.com/api/badges/download-on-the-app-store/black/en-us?size=250x83&releaseDate=2010-06-21" 
                         alt="Download on the App Store" 
                         class="h-14 hover:opacity-80 transition-opacity"
                         onerror="this.style.display='none'; this.nextElementSibling.style.display='block';">
                    <div style="display:none;" class="bg-black px-6 py-3 rounded-lg border-2 border-white text-white font-bold hover:bg-gray-800 transition-colors">
                        Download on the App Store
                    </div>
                </a>
            </div>

            <!-- QR 코드 -->
            <div class="bg-white p-4 rounded-xl shadow-2xl">
                <div class="w-32 h-32 bg-gray-100 flex items-center justify-center rounded-lg">
                    <!-- QR 코드 플레이스홀더 (실제 QR 코드 생성 라이브러리 사용 권장) -->
                    <div class="text-center text-gray-500 text-xs p-2">
                        <div class="grid grid-cols-8 gap-0.5 mb-2">
                            <div class="w-3 h-3 bg-black"></div><div class="w-3 h-3 bg-black"></div><div class="w-3 h-3 bg-black"></div><div class="w-3 h-3 bg-black"></div><div class="w-3 h-3 bg-black"></div><div class="w-3 h-3 bg-black"></div><div class="w-3 h-3 bg-black"></div><div class="w-3 h-3 bg-black"></div>
                            <div class="w-3 h-3 bg-black"></div><div class="w-3 h-3 bg-white"></div><div class="w-3 h-3 bg-white"></div><div class="w-3 h-3 bg-black"></div><div class="w-3 h-3 bg-white"></div><div class="w-3 h-3 bg-white"></div><div class="w-3 h-3 bg-black"></div><div class="w-3 h-3 bg-black"></div>
                            <div class="w-3 h-3 bg-black"></div><div class="w-3 h-3 bg-white"></div><div class="w-3 h-3 bg-black"></div><div class="w-3 h-3 bg-black"></div><div class="w-3 h-3 bg-white"></div><div class="w-3 h-3 bg-black"></div><div class="w-3 h-3 bg-white"></div><div class="w-3 h-3 bg-black"></div>
                            <div class="w-3 h-3 bg-black"></div><div class="w-3 h-3 bg-white"></div><div class="w-3 h-3 bg-white"></div><div class="w-3 h-3 bg-black"></div><div class="w-3 h-3 bg-white"></div><div class="w-3 h-3 bg-white"></div><div class="w-3 h-3 bg-black"></div><div class="w-3 h-3 bg-black"></div>
                            <div class="w-3 h-3 bg-black"></div><div class="w-3 h-3 bg-white"></div><div class="w-3 h-3 bg-black"></div><div class="w-3 h-3 bg-white"></div><div class="w-3 h-3 bg-black"></div><div class="w-3 h-3 bg-white"></div><div class="w-3 h-3 bg-black"></div><div class="w-3 h-3 bg-black"></div>
                            <div class="w-3 h-3 bg-black"></div><div class="w-3 h-3 bg-white"></div><div class="w-3 h-3 bg-white"></div><div class="w-3 h-3 bg-black"></div><div class="w-3 h-3 bg-white"></div><div class="w-3 h-3 bg-white"></div><div class="w-3 h-3 bg-black"></div><div class="w-3 h-3 bg-black"></div>
                            <div class="w-3 h-3 bg-black"></div><div class="w-3 h-3 bg-white"></div><div class="w-3 h-3 bg-black"></div><div class="w-3 h-3 bg-black"></div><div class="w-3 h-3 bg-white"></div><div class="w-3 h-3 bg-black"></div><div class="w-3 h-3 bg-white"></div><div class="w-3 h-3 bg-black"></div>
                            <div class="w-3 h-3 bg-black"></div><div class="w-3 h-3 bg-black"></div><div class="w-3 h-3 bg-black"></div><div class="w-3 h-3 bg-black"></div><div class="w-3 h-3 bg-black"></div><div class="w-3 h-3 bg-black"></div><div class="w-3 h-3 bg-black"></div><div class="w-3 h-3 bg-black"></div>
                        </div>
                        QR 코드
                    </div>
                </div>
            </div>
        </div>

        <!-- 웹에서 바로 사용하기 버튼 -->
        <div class="mt-8">
            <a href="index.php?page=home" class="inline-block px-8 py-4 bg-gradient-to-r from-purple-600 to-pink-600 text-white font-bold rounded-xl hover:from-purple-700 hover:to-pink-700 transition-all shadow-lg hover:shadow-xl transform hover:scale-105">
                웹에서 바로 사용하기 →
            </a>
        </div>
    </div>

    <!-- 애니메이션 스타일 -->
    <style>
        @keyframes twinkle {
            0%, 100% { opacity: 1; }
            50% { opacity: 0.5; }
        }
    </style>
</div>
