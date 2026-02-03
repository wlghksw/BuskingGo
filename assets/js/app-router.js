/**
 * 앱 내 라우터 (SPA 방식)
 * 앱 내에서 페이지 전환을 처리합니다.
 */

class AppRouter {
    constructor() {
        this.currentPage = 'home';
        this.init();
    }

    init() {
        // 네비게이션 바 이벤트 리스너
        document.querySelectorAll('[data-page]').forEach(link => {
            link.addEventListener('click', (e) => {
                e.preventDefault();
                const page = link.getAttribute('data-page');
                this.navigate(page);
            });
        });

        // 초기 페이지 로드
        const urlParams = new URLSearchParams(window.location.search);
        const page = urlParams.get('page') || 'home';
        if (page === 'split' || page === '') {
            this.navigate('home');
        }
    }

    navigate(page) {
        this.currentPage = page;
        
        // 네비게이션 바 활성화 업데이트
        document.querySelectorAll('[data-page]').forEach(link => {
            if (link.getAttribute('data-page') === page) {
                link.classList.remove('text-gray-400');
                link.classList.add('text-purple-400');
                link.querySelector('i').classList.add('fill-current');
            } else {
                link.classList.remove('text-purple-400');
                link.classList.add('text-gray-400');
                link.querySelector('i').classList.remove('fill-current');
            }
        });

        // 페이지 콘텐츠 로드
        this.loadPage(page);
    }

    async loadPage(page) {
        const contentArea = document.getElementById('app-content');
        if (!contentArea) return;

        // 로딩 표시
        contentArea.innerHTML = '<div class="flex items-center justify-center h-full"><div class="text-white">로딩 중...</div></div>';

        try {
            // 페이지별 콘텐츠 로드
            let html = '';
            
            switch(page) {
                case 'home':
                    html = await this.loadHomePage();
                    break;
                case 'register':
                    html = await this.loadRegisterPage();
                    break;
                case 'booking':
                    html = await this.loadBookingPage();
                    break;
                case 'community':
                    html = await this.loadCommunityPage();
                    break;
                default:
                    html = await this.loadHomePage();
            }

            contentArea.innerHTML = html;
            
            // Lucide 아이콘 재초기화
            if (typeof lucide !== 'undefined') {
                lucide.createIcons();
            }

            // 페이지별 스크립트 실행
            this.initPageScripts(page);
        } catch (error) {
            console.error('페이지 로드 실패:', error);
            contentArea.innerHTML = '<div class="flex items-center justify-center h-full"><div class="text-red-500">페이지를 불러올 수 없습니다.</div></div>';
        }
    }

    async loadHomePage() {
        // 홈 페이지 HTML 반환
        const performances = <?= json_encode($filteredPerformances) ?>;
        const userLocation = <?= json_encode($userLocation) ?>;
        const selectedLocation = '<?= htmlspecialchars($selectedLocation ?: '') ?>';
        
        let html = `
            <!-- 내 주변 버스킹 찾기 섹션 -->
            <div class="bg-gradient-to-r from-purple-600 to-pink-600 rounded-b-3xl p-6 text-white mb-4">
                <div class="flex items-center gap-2 mb-3">
                    <i data-lucide="music" style="width: 24px; height: 24px;"></i>
                    <h1 class="text-2xl font-bold">내 주변 버스킹 찾기</h1>
                </div>
                <p class="text-sm opacity-90 mb-4">지금 진행 중인 공연을 확인하세요</p>
                
                <div class="flex gap-3">
                    <button class="flex-1 bg-white/20 backdrop-blur-lg rounded-xl p-3 flex items-center gap-2 hover:bg-white/30 transition-colors">
                        <i data-lucide="navigation" style="width: 20px; height: 20px;"></i>
                        <span class="text-sm font-medium">${selectedLocation || '전체 지역'}</span>
                    </button>
                    <button class="flex-1 bg-white/20 backdrop-blur-lg rounded-xl p-3 flex items-center gap-2 hover:bg-white/30 transition-colors">
                        <i data-lucide="clock" style="width: 20px; height: 20px;"></i>
                        <span class="text-sm font-bold">${performances.length}개</span>
                    </button>
                </div>
            </div>

            <!-- 실시간 공연 지도 섹션 -->
            <div class="bg-gray-800/80 backdrop-blur-xl border border-gray-700/50 rounded-2xl p-4 mx-4 mb-4 shadow-xl">
                <div class="flex items-center justify-between mb-3">
                    <h2 class="text-lg font-bold text-white">실시간 공연 지도</h2>
                    <form method="GET" class="flex items-center gap-2" onsubmit="event.preventDefault(); handleLocationChange(event);">
                        <input type="hidden" name="page" value="split">
                        <select name="location" onchange="handleLocationChange(event)" class="px-3 py-1.5 bg-gray-900/50 border border-gray-600 rounded-lg text-sm text-white focus:outline-none focus:border-purple-500">
                            <option value="">전체 지역</option>
                            ${this.getLocationOptions(selectedLocation)}
                        </select>
                    </form>
                </div>
                <div id="map" class="rounded-xl overflow-hidden border border-gray-600/50" style="height: 250px;"></div>
            </div>

            <!-- 공연 목록 -->
            <div class="px-4 space-y-3 pb-4">
        `;

        performances.forEach(perf => {
            const isFavorite = window.favorites && window.favorites.includes(perf.id);
            html += `
                <div onclick="showPerformanceModal(${JSON.stringify(perf).replace(/"/g, '&quot;')})" class="bg-gray-800/80 backdrop-blur-xl border border-gray-700/50 rounded-2xl p-4 shadow-lg hover:shadow-xl hover:border-purple-500/50 transition-all cursor-pointer">
                    <div class="flex items-start justify-between mb-3">
                        <div class="flex items-center gap-3 flex-1">
                            <div class="text-4xl">${perf.image}</div>
                            <div class="flex-1">
                                <div class="flex items-center gap-2 mb-1">
                                    <h3 class="text-lg font-bold text-white">${perf.buskerName}</h3>
                                    ${perf.status === '진행중' ? '<span class="px-2 py-0.5 bg-red-500 text-white text-xs rounded-full font-bold">LIVE</span>' : ''}
                                </div>
                                <div class="flex items-center gap-4 text-sm text-gray-300 mb-1">
                                    <span class="flex items-center gap-1">
                                        <i data-lucide="map-pin" style="width: 14px; height: 14px;"></i>
                                        ${perf.location}
                                    </span>
                                    <span class="flex items-center gap-1">
                                        <i data-lucide="navigation" style="width: 14px; height: 14px;"></i>
                                        ${perf.distance}km
                                    </span>
                                </div>
                                <div class="flex items-center gap-4 text-sm text-gray-300">
                                    <span class="flex items-center gap-1">
                                        <i data-lucide="clock" style="width: 14px; height: 14px;"></i>
                                        ${perf.startTime} - ${perf.endTime}
                                    </span>
                                    <span class="flex items-center gap-1 text-yellow-500">
                                        <i data-lucide="star" fill="currentColor" style="width: 14px; height: 14px;"></i>
                                        ${perf.rating}
                                    </span>
                                </div>
                            </div>
                        </div>
                        <a href="index.php?page=split&toggleFavorite=${perf.id}" onclick="event.stopPropagation();" class="p-2 hover:bg-gray-700/50 rounded-full transition-all ml-2">
                            <i data-lucide="heart" class="${isFavorite ? 'fill-red-500 text-red-500' : 'text-gray-400'}" style="width: 20px; height: 20px;"></i>
                        </a>
                    </div>
                    <p class="text-sm text-gray-400 mt-2">${perf.description}</p>
                </div>
            `;
        });

        html += '</div>';
        return html;
    }

    async loadRegisterPage() {
        return `
            <div class="p-4 space-y-4">
                <div class="bg-gradient-to-r from-purple-600 to-pink-600 rounded-2xl p-6 text-white">
                    <h2 class="text-2xl font-bold mb-2">버스커 등록</h2>
                    <p class="text-sm opacity-90">프로필을 등록하고 공연 기회를 받아보세요</p>
                </div>

                <div class="bg-gray-800 rounded-2xl p-4 mx-4 border border-gray-700">
                    <form method="POST" action="index.php?page=split" class="space-y-4">
                        <div>
                            <label class="block text-sm font-bold mb-2 text-gray-300">팀/개인명 *</label>
                            <input type="text" name="name" placeholder="예: 어쿠스틱 소울" class="w-full bg-gray-900 border border-gray-700 rounded-lg px-4 py-3 focus:outline-none focus:border-purple-500 text-white placeholder-gray-500" />
                        </div>

                        <div>
                            <label class="block text-sm font-bold mb-2 text-gray-300">팀 인원</label>
                            <input type="number" name="teamSize" min="1" value="1" class="w-full bg-gray-900 border border-gray-700 rounded-lg px-4 py-3 focus:outline-none focus:border-purple-500 text-white" />
                        </div>

                        <div>
                            <label class="block text-sm font-bold mb-2 text-gray-300">보유 장비</label>
                            <input type="text" name="equipment" placeholder="예: 기타, 앰프, 마이크" class="w-full bg-gray-900 border border-gray-700 rounded-lg px-4 py-3 focus:outline-none focus:border-purple-500 text-white placeholder-gray-500" />
                        </div>

                        <div>
                            <label class="block text-sm font-bold mb-2 text-gray-300">연락처 *</label>
                            <input type="tel" name="phone" placeholder="010-0000-0000" class="w-full bg-gray-900 border border-gray-700 rounded-lg px-4 py-3 focus:outline-none focus:border-purple-500 text-white placeholder-gray-500" />
                        </div>

                        <div>
                            <label class="block text-sm font-bold mb-2 text-gray-300">소개</label>
                            <textarea name="bio" placeholder="팀 소개 및 공연 스타일을 자유롭게 작성해주세요" rows="4" class="w-full bg-gray-900 border border-gray-700 rounded-lg px-4 py-3 focus:outline-none focus:border-purple-500 text-white placeholder-gray-500"></textarea>
                        </div>

                        <div>
                            <label class="block text-sm font-bold mb-2 text-gray-300">공연 가능 요일</label>
                            <div class="flex gap-2 flex-wrap" id="availableDays">
                                ${this.getDayButtons()}
                            </div>
                            <input type="hidden" name="availableDays" id="availableDaysInput" value="">
                        </div>

                        <div>
                            <label class="block text-sm font-bold mb-2 text-gray-300">선호 시간대</label>
                            <select name="preferredTime" class="w-full bg-gray-900 border border-gray-700 rounded-lg px-4 py-3 focus:outline-none focus:border-purple-500 text-white">
                                <option value="">선택하세요</option>
                                <option value="오후">오후 (14:00-18:00)</option>
                                <option value="저녁">저녁 (18:00-22:00)</option>
                                <option value="야간">야간 (22:00-24:00)</option>
                            </select>
                        </div>

                        <button type="submit" class="w-full bg-gradient-to-r from-purple-600 to-pink-600 text-white font-bold py-4 rounded-lg hover:scale-105 transition-transform">
                            등록 완료
                        </button>
                    </form>
                </div>
            </div>
        `;
    }

    async loadBookingPage() {
        return `
            <div class="p-4 space-y-4">
                <div class="bg-gradient-to-r from-blue-600 to-purple-600 rounded-2xl p-6 text-white">
                    <h2 class="text-2xl font-bold mb-2">공연 예약</h2>
                    <p class="text-sm opacity-90">행사에 필요한 공연을 예약하세요</p>
                </div>

                <div class="bg-gray-800 rounded-2xl p-4 mx-4 border border-gray-700">
                    <form method="POST" action="index.php?page=split" class="space-y-4">
                        <div>
                            <label class="block text-sm font-bold mb-2 text-gray-300">주최자명 *</label>
                            <input type="text" name="organizerName" placeholder="예: 천안시청" class="w-full bg-gray-900 border border-gray-700 rounded-lg px-4 py-3 focus:outline-none focus:border-purple-500 text-white placeholder-gray-500" />
                        </div>

                        <div>
                            <label class="block text-sm font-bold mb-2 text-gray-300">주최자 유형 *</label>
                            <select name="organizerType" class="w-full bg-gray-900 border border-gray-700 rounded-lg px-4 py-3 focus:outline-none focus:border-purple-500 text-white">
                                <option value="">선택하세요</option>
                                <option value="지자체">지자체</option>
                                <option value="대학">대학교</option>
                                <option value="축제">축제 운영사</option>
                                <option value="상권">상권조합</option>
                                <option value="기타">기타</option>
                            </select>
                        </div>

                        <div>
                            <label class="block text-sm font-bold mb-2 text-gray-300">공연 장소 *</label>
                            <input type="text" name="location" placeholder="예: 천안역 광장" class="w-full bg-gray-900 border border-gray-700 rounded-lg px-4 py-3 focus:outline-none focus:border-purple-500 text-white placeholder-gray-500" />
                        </div>

                        <div>
                            <label class="block text-sm font-bold mb-2 text-gray-300">공연 날짜 *</label>
                            <input type="date" name="date" class="w-full bg-gray-900 border border-gray-700 rounded-lg px-4 py-3 focus:outline-none focus:border-purple-500 text-white" />
                        </div>

                        <div class="grid grid-cols-2 gap-4">
                            <div>
                                <label class="block text-sm font-bold mb-2 text-gray-300">시작 시간 *</label>
                                <input type="time" name="startTime" class="w-full bg-gray-900 border border-gray-700 rounded-lg px-4 py-3 focus:outline-none focus:border-purple-500 text-white" />
                            </div>
                            <div>
                                <label class="block text-sm font-bold mb-2 text-gray-300">종료 시간 *</label>
                                <input type="time" name="endTime" class="w-full bg-gray-900 border border-gray-700 rounded-lg px-4 py-3 focus:outline-none focus:border-purple-500 text-white" />
                            </div>
                        </div>

                        <div>
                            <label class="block text-sm font-bold mb-2 text-gray-300">추가 요청사항</label>
                            <textarea name="additionalRequest" placeholder="특별한 요청사항이 있으시면 작성해주세요" rows="4" class="w-full bg-gray-900 border border-gray-700 rounded-lg px-4 py-3 focus:outline-none focus:border-purple-500 text-white placeholder-gray-500"></textarea>
                        </div>

                        <button type="submit" class="w-full bg-gradient-to-r from-blue-600 to-purple-600 text-white font-bold py-4 rounded-lg hover:scale-105 transition-transform">
                            예약 신청하기
                        </button>
                    </form>
                </div>
            </div>
        `;
    }

    async loadCommunityPage() {
        const communityPosts = <?= json_encode($communityPosts) ?>;
        
        let html = `
            <div class="p-4 space-y-4">
                <div class="bg-gradient-to-r from-green-600 to-teal-600 rounded-2xl p-6 text-white">
                    <h2 class="text-2xl font-bold mb-2">커뮤니티</h2>
                    <p class="text-sm opacity-90">아티스트들과 소통하고 정보를 공유하세요</p>
                </div>

                <!-- 탭 메뉴 -->
                <div class="flex gap-2 px-4 border-b border-gray-700">
                    <button onclick="showCommunityTab('free')" class="px-4 py-2 text-sm font-bold text-purple-400 border-b-2 border-purple-400">자유게시판</button>
                    <button onclick="showCommunityTab('recruit')" class="px-4 py-2 text-sm font-bold text-gray-400">팀원모집</button>
                    <button onclick="showCommunityTab('collab')" class="px-4 py-2 text-sm font-bold text-gray-400">함께공연</button>
                </div>

                <div id="community-content" class="px-4 space-y-3 pb-4">
        `;

        // 자유게시판 게시글
        communityPosts.free.forEach(post => {
            html += `
                <div class="bg-gray-800/80 backdrop-blur-xl border border-gray-700/50 rounded-2xl p-4 shadow-lg">
                    <h3 class="font-bold text-white mb-2">${post.title}</h3>
                    <div class="flex items-center gap-4 text-sm text-gray-300">
                        <span>${post.author}</span>
                        <span>${post.date}</span>
                        <span>조회 ${post.views}</span>
                        <span>댓글 ${post.comments}</span>
                    </div>
                </div>
            `;
        });

        html += '</div></div>';
        return html;
    }

    getLocationOptions(selected) {
        const locations = <?= json_encode($locationCoordinates) ?>;
        let options = '';
        for (const loc in locations) {
            const selectedAttr = selected === loc ? 'selected' : '';
            options += `<option value="${loc}" ${selectedAttr}>${loc}</option>`;
        }
        return options;
    }

    getDayButtons() {
        const days = ['월', '화', '수', '목', '금', '토', '일'];
        return days.map(day => `
            <button type="button" onclick="toggleDay(this, '${day}')" class="px-4 py-2 rounded-lg transition-all bg-gray-700 text-gray-300 hover:bg-gray-600" data-day="${day}">
                ${day}
            </button>
        `).join('');
    }

    initPageScripts(page) {
        if (page === 'home') {
            this.initMap();
        } else if (page === 'register') {
            this.initRegisterScripts();
        }
    }

    initMap() {
        const performances = <?= json_encode($filteredPerformances) ?>;
        const userLocation = <?= json_encode($userLocation) ?>;
        
        if (typeof L === 'undefined') return;
        
        const map = L.map('map').setView([userLocation.lat, userLocation.lng], 13);
        
        L.tileLayer('https://{s}.tile.openstreetmap.org/{z}/{x}/{y}.png', {
            attribution: '&copy; <a href="https://www.openstreetmap.org/copyright">OpenStreetMap</a> contributors'
        }).addTo(map);
        
        performances.forEach(perf => {
            const isLive = perf.status === '진행중';
            const statusText = isLive ? 'LIVE' : '진행 예정';
            const icon = L.divIcon({
                className: 'custom-marker',
                html: `
                    <div style="display: flex; flex-direction: column; align-items: center;">
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
                        <div style="
                            margin-top: 4px;
                            background: ${isLive ? '#ef4444' : '#9333ea'};
                            color: white;
                            font-size: 9px;
                            font-weight: bold;
                            padding: 2px 5px;
                            border-radius: 6px;
                            white-space: nowrap;
                            box-shadow: 0 2px 4px rgba(0, 0, 0, 0.3);
                        ">
                            ${statusText}
                        </div>
                    </div>
                `,
                iconSize: [40, 60],
                iconAnchor: [20, 60],
                popupAnchor: [0, -60],
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
                if (typeof showPerformanceModal === 'function') {
                    showPerformanceModal(perf);
                }
            });
        });
    }

    initRegisterScripts() {
        window.selectedDays = [];
        window.toggleDay = function(button, day) {
            if (window.selectedDays.includes(day)) {
                window.selectedDays = window.selectedDays.filter(d => d !== day);
                button.classList.remove('bg-purple-600', 'text-white');
                button.classList.add('bg-gray-700', 'text-gray-300');
            } else {
                window.selectedDays.push(day);
                button.classList.add('bg-purple-600', 'text-white');
                button.classList.remove('bg-gray-700', 'text-gray-300');
            }
            const input = document.getElementById('availableDaysInput');
            if (input) {
                input.value = window.selectedDays.join(',');
            }
        };
    }
}

// 전역 함수들
function handleLocationChange(event) {
    const location = event.target.value;
    const urlParams = new URLSearchParams(window.location.search);
    const appPage = urlParams.get('appPage') || 'home';
    // location 파라미터로 이동하면 index.php에서 세션에 저장하고 리디렉트됨
    window.location.href = `index.php?page=split&appPage=${appPage}&location=${encodeURIComponent(location)}`;
}

function showCommunityTab(tab) {
    // 탭 전환 로직
    document.querySelectorAll('[onclick^="showCommunityTab"]').forEach(btn => {
        btn.classList.remove('text-purple-400', 'border-purple-400');
        btn.classList.add('text-gray-400');
    });
    event.target.classList.remove('text-gray-400');
    event.target.classList.add('text-purple-400', 'border-purple-400');
}

// 라우터 초기화
let appRouter;
document.addEventListener('DOMContentLoaded', function() {
    appRouter = new AppRouter();
});
