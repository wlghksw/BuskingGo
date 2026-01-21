/**
 * 앱 메인 JavaScript 파일
 * 모달 표시 및 기타 인터랙티브 기능을 담당합니다.
 */

// 공연 상세 모달 표시 함수
function showPerformanceModal(performance) {
    // favorites는 전역 변수로 설정됨 (home.php에서)
    const favorites = window.favorites || [];
    const isFavorite = favorites.includes(performance.id);
    
    const modalHTML = `
        <div class="bg-gray-800/90 backdrop-blur-xl rounded-2xl max-w-2xl w-full max-h-[90vh] overflow-y-auto border border-gray-700/50 shadow-2xl" onclick="event.stopPropagation()">
            <div class="p-6">
                <div class="flex items-start justify-between mb-6">
                    <div class="flex items-center gap-4">
                        <div class="text-6xl">${performance.image}</div>
                        <div>
                            <h2 class="text-3xl font-bold mb-2 text-white">${performance.buskerName}</h2>
                        </div>
                    </div>
                    <button onclick="closePerformanceModal()" class="p-2 hover:bg-gray-700 rounded-full text-gray-400">
                        <i data-lucide="x" style="width: 24px; height: 24px;"></i>
                    </button>
                </div>

                <div class="space-y-6">
                    <!-- 공연 기본 정보 카드 -->
                    <div class="bg-gray-900 rounded-xl p-4 border border-gray-700">
                        <h3 class="font-bold mb-3 text-white">공연 정보</h3>
                        <div class="space-y-2">
                            <div class="flex items-center gap-2 text-gray-300">
                                <i data-lucide="map-pin" class="text-purple-400" style="width: 18px; height: 18px;"></i>
                                <span>${performance.location}</span>
                            </div>
                            <div class="flex items-center gap-2 text-gray-300">
                                <i data-lucide="clock" class="text-purple-400" style="width: 18px; height: 18px;"></i>
                                <span>${performance.startTime} - ${performance.endTime}</span>
                            </div>
                            <div class="flex items-center gap-2 text-gray-300">
                                <i data-lucide="navigation" class="text-purple-400" style="width: 18px; height: 18px;"></i>
                                <span>현재 위치에서 ${performance.distance}km</span>
                            </div>
                            <div class="flex items-center gap-2 text-yellow-400">
                                <i data-lucide="star" fill="currentColor" class="text-yellow-400" style="width: 18px; height: 18px;"></i>
                                <span>${performance.rating} / 5.0</span>
                            </div>
                        </div>
                    </div>

                    <!-- 공연 소개 섹션 -->
                    <div>
                        <h3 class="font-bold mb-3 text-white">공연 소개</h3>
                        <p class="text-gray-300">${performance.description}</p>
                    </div>

                    <!-- QR 모바일 팁박스 섹션 -->
                    <div class="bg-gradient-to-r from-purple-900/50 to-pink-900/50 rounded-xl p-4 border border-purple-700">
                        <div class="flex items-center gap-3 mb-3">
                            <i data-lucide="qr-code" class="text-purple-400" style="width: 24px; height: 24px;"></i>
                            <h3 class="font-bold text-white">QR 모바일 팁박스</h3>
                        </div>
                        <p class="text-sm text-gray-300 mb-4">QR 코드를 스캔하여 아티스트에게 팁을 후원하세요</p>
                        <div class="flex gap-2">
                            <button class="flex-1 bg-purple-600 text-white font-bold py-2 px-4 rounded-lg hover:bg-purple-700 transition-colors text-sm">
                                QR 스캔하기
                            </button>
                            <button class="px-4 py-2 bg-gray-700 border border-purple-500 text-purple-300 rounded-lg hover:bg-gray-600 transition-colors text-sm font-bold">
                                팁 후원하기
                            </button>
                        </div>
                    </div>

                    <!-- 액션 버튼: 길찾기 및 찜하기 -->
                    <div class="flex gap-3">
                        <button class="flex-1 bg-gradient-to-r from-purple-600 to-pink-600 text-white font-bold py-3 rounded-lg hover:scale-105 transition-transform">
                            길찾기
                        </button>
                        <a href="index.php?page=home&toggleFavorite=${performance.id}" class="px-6 py-3 bg-gray-700 rounded-lg hover:bg-gray-600 transition-all flex items-center justify-center">
                            <i data-lucide="heart" class="${isFavorite ? 'fill-red-500 text-red-500' : 'text-gray-400'}" style="width: 24px; height: 24px;"></i>
                        </a>
                    </div>
                </div>
            </div>
        </div>
    `;
    
    const modal = document.getElementById('performanceModal');
    modal.innerHTML = modalHTML;
    modal.classList.remove('hidden');
    modal.onclick = closePerformanceModal;
    
    lucide.createIcons();
}

// 공연 상세 모달 닫기 함수
function closePerformanceModal() {
    document.getElementById('performanceModal').classList.add('hidden');
}
