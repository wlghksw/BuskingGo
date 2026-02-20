<?php
/**
 * 공연 예약 페이지
 * 버스커 공연을 예약하는 페이지입니다.
 * 주최자 정보, 공연 장소, 날짜 및 시간을 입력할 수 있습니다.
 */
?>
<div class="space-y-6">
    <div class="bg-gradient-to-r from-blue-600 to-purple-600 rounded-2xl p-6 text-white">
        <h2 class="text-3xl font-bold mb-2">공연 예약</h2>
        <p>행사에 필요한 공연을 예약하세요</p>
    </div>

    <div class="bg-gray-800 rounded-2xl p-6 border border-gray-700 shadow-sm">
        <form method="POST" action="index.php?page=booking" class="space-y-6">
            <div>
                <label class="block text-sm font-bold mb-2 text-gray-300">주최자명 *</label>
                <input
                    type="text"
                    name="organizerName"
                    placeholder="예: 천안시청, 백석대학교"
                    class="w-full bg-gray-900 border border-gray-700 rounded-lg px-4 py-3 focus:outline-none focus:border-purple-500 text-white placeholder-gray-500"
                />
            </div>

            <div>
                <label class="block text-sm font-bold mb-2 text-gray-300">주최자 유형 *</label>
                <select
                    name="organizerType"
                    class="w-full bg-gray-900 border border-gray-700 rounded-lg px-4 py-3 focus:outline-none focus:border-purple-500 text-white"
                >
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
                <input
                    type="text"
                    name="location"
                    id="location-input"
                    placeholder="예: 천안역 광장"
                    class="w-full bg-gray-900 border border-gray-700 rounded-lg px-4 py-3 focus:outline-none focus:border-purple-500 text-white placeholder-gray-500"
                />
                <p class="text-xs text-gray-400 mt-1">지도에서 위치를 클릭하여 좌표를 자동으로 설정할 수 있습니다</p>
            </div>
            
            <!-- 지도로 위치 선택 -->
            <div>
                <label class="block text-sm font-bold mb-2 text-gray-300">지도에서 위치 선택</label>
                <div id="location-map" style="height: 300px; border-radius: 8px; overflow: hidden; border: 1px solid #374151;" class="mb-2"></div>
                <div class="flex gap-2 text-sm text-gray-400">
                    <span>위도: <span id="lat-display">-</span></span>
                    <span>경도: <span id="lng-display">-</span></span>
                </div>
                <input type="hidden" name="lat" id="lat-input" value="">
                <input type="hidden" name="lng" id="lng-input" value="">
            </div>

            <div>
                <label class="block text-sm font-bold mb-2 text-gray-300">공연 날짜 *</label>
                <input
                    type="date"
                    name="date"
                    class="w-full bg-gray-900 border border-gray-700 rounded-lg px-4 py-3 focus:outline-none focus:border-purple-500 text-white"
                />
            </div>

            <!-- 시작/종료 시간 입력: 2열 그리드 -->
            <div class="grid grid-cols-2 gap-4">
                <div>
                    <label class="block text-sm font-bold mb-2 text-gray-300">시작 시간 *</label>
                    <input
                        type="time"
                        name="startTime"
                        class="w-full bg-gray-900 border border-gray-700 rounded-lg px-4 py-3 focus:outline-none focus:border-purple-500 text-white"
                    />
                </div>

                <div>
                    <label class="block text-sm font-bold mb-2 text-gray-300">종료 시간 *</label>
                    <input
                        type="time"
                        name="endTime"
                        class="w-full bg-gray-900 border border-gray-700 rounded-lg px-4 py-3 focus:outline-none focus:border-purple-500 text-white"
                    />
                </div>
            </div>

            <div>
                <label class="block text-sm font-bold mb-2 text-gray-300">추가 요청사항</label>
                <textarea
                    name="additionalRequest"
                    placeholder="특별한 요청사항이 있으시면 작성해주세요"
                    rows="4"
                    class="w-full bg-gray-900 border border-gray-700 rounded-lg px-4 py-3 focus:outline-none focus:border-purple-500 text-white placeholder-gray-500"
                ></textarea>
            </div>

            <button
                type="submit"
                class="w-full bg-gradient-to-r from-blue-600 to-purple-600 text-white font-bold py-4 rounded-lg hover:scale-105 transition-transform"
            >
                예약 신청하기
            </button>
        </form>
    </div>
</div>

<script>
document.addEventListener('DOMContentLoaded', function() {
    // 지도 초기화
    const map = L.map('location-map').setView([36.8151, 127.1139], 13);
    
    L.tileLayer('https://{s}.tile.openstreetmap.org/{z}/{x}/{y}.png', {
        attribution: '&copy; <a href="https://www.openstreetmap.org/copyright">OpenStreetMap</a> contributors'
    }).addTo(map);
    
    let marker = null;
    
    // 지도 클릭 시 마커 추가 및 좌표 저장
    map.on('click', function(e) {
        const lat = e.latlng.lat;
        const lng = e.latlng.lng;
        
        // 기존 마커 제거
        if (marker) {
            map.removeLayer(marker);
        }
        
        // 새 마커 추가
        marker = L.marker([lat, lng]).addTo(map);
        
        // 좌표 표시
        document.getElementById('lat-display').textContent = lat.toFixed(6);
        document.getElementById('lng-display').textContent = lng.toFixed(6);
        document.getElementById('lat-input').value = lat;
        document.getElementById('lng-input').value = lng;
        
        // 역지오코딩으로 주소 가져오기 (선택사항)
        fetch(`https://nominatim.openstreetmap.org/reverse?format=json&lat=${lat}&lon=${lng}`)
            .then(response => response.json())
            .then(data => {
                if (data.display_name) {
                    document.getElementById('location-input').value = data.display_name;
                }
            })
            .catch(err => console.log('주소 가져오기 실패:', err));
    });
    
    // 현재 위치 가져오기 (선택사항)
    if (navigator.geolocation) {
        navigator.geolocation.getCurrentPosition(function(position) {
            const lat = position.coords.latitude;
            const lng = position.coords.longitude;
            map.setView([lat, lng], 15);
        });
    }
});
</script>
