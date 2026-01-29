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
                    placeholder="예: 천안역 광장"
                    class="w-full bg-gray-900 border border-gray-700 rounded-lg px-4 py-3 focus:outline-none focus:border-purple-500 text-white placeholder-gray-500"
                />
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
