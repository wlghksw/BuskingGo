<?php
/**
 * 버스커 등록 페이지
 * 아티스트가 자신의 프로필을 등록하는 페이지입니다.
 * 팀 정보, 연락처, 공연 가능 요일 및 시간대를 입력할 수 있습니다.
 */
?>
<div class="space-y-6">
    <div class="bg-gradient-to-r from-purple-600 to-pink-600 rounded-2xl p-6 text-white">
        <h2 class="text-3xl font-bold mb-2">버스커 등록</h2>
        <p>프로필을 등록하고 공연 기회를 받아보세요</p>
    </div>

    <div class="bg-gray-800 rounded-2xl p-6 border border-gray-700 shadow-sm">
        <form method="POST" action="index.php?page=register" class="space-y-6">
            <div>
                <label class="block text-sm font-bold mb-2 text-gray-300">팀/개인명 *</label>
                <input
                    type="text"
                    name="name"
                    placeholder="예: 어쿠스틱 소울"
                    class="w-full bg-gray-900 border border-gray-700 rounded-lg px-4 py-3 focus:outline-none focus:border-purple-500 text-white placeholder-gray-500"
                />
            </div>

            <div>
                <label class="block text-sm font-bold mb-2 text-gray-300">팀 인원</label>
                <input
                    type="number"
                    name="teamSize"
                    min="1"
                    value="1"
                    class="w-full bg-gray-900 border border-gray-700 rounded-lg px-4 py-3 focus:outline-none focus:border-purple-500 text-white"
                />
            </div>

            <div>
                <label class="block text-sm font-bold mb-2 text-gray-300">보유 장비</label>
                <input
                    type="text"
                    name="equipment"
                    placeholder="예: 기타, 앰프, 마이크"
                    class="w-full bg-gray-900 border border-gray-700 rounded-lg px-4 py-3 focus:outline-none focus:border-purple-500 text-white placeholder-gray-500"
                />
            </div>

            <div>
                <label class="block text-sm font-bold mb-2 text-gray-300">연락처 *</label>
                <input
                    type="tel"
                    name="phone"
                    placeholder="010-0000-0000"
                    class="w-full bg-gray-900 border border-gray-700 rounded-lg px-4 py-3 focus:outline-none focus:border-purple-500 text-white placeholder-gray-500"
                />
            </div>

            <div>
                <label class="block text-sm font-bold mb-2 text-gray-300">소개</label>
                <textarea
                    name="bio"
                    placeholder="팀 소개 및 공연 스타일을 자유롭게 작성해주세요"
                    rows="4"
                    class="w-full bg-gray-900 border border-gray-700 rounded-lg px-4 py-3 focus:outline-none focus:border-purple-500 text-white placeholder-gray-500"
                ></textarea>
            </div>

            <!-- 공연 가능 요일 선택: 다중 선택 가능한 버튼 그룹 -->
            <div>
                <label class="block text-sm font-bold mb-2">공연 가능 요일</label>
                <div class="flex gap-2 flex-wrap" id="availableDays">
                    <?php foreach ($days as $day): ?>
                    <button
                        type="button"
                        onclick="toggleDay(this, '<?= $day ?>')"
                        class="px-4 py-2 rounded-lg transition-all bg-gray-700 text-gray-300 hover:bg-gray-600"
                        data-day="<?= $day ?>"
                    >
                        <?= $day ?>
                    </button>
                    <?php endforeach; ?>
                </div>
                <input type="hidden" name="availableDays" id="availableDaysInput" value="">
            </div>

            <div>
                <label class="block text-sm font-bold mb-2">선호 시간대</label>
                <select
                    name="preferredTime"
                    class="w-full bg-gray-900 border border-gray-700 rounded-lg px-4 py-3 focus:outline-none focus:border-purple-500 text-white"
                >
                    <option value="">선택하세요</option>
                    <option value="오후">오후 (14:00-18:00)</option>
                    <option value="저녁">저녁 (18:00-22:00)</option>
                    <option value="야간">야간 (22:00-24:00)</option>
                </select>
            </div>

            <button
                type="submit"
                class="w-full bg-gradient-to-r from-purple-600 to-pink-600 text-white font-bold py-4 rounded-lg hover:scale-105 transition-transform"
            >
                등록 완료
            </button>
        </form>
    </div>
</div>

<script>
let selectedDays = [];
function toggleDay(button, day) {
    if (selectedDays.includes(day)) {
        selectedDays = selectedDays.filter(d => d !== day);
        button.classList.remove('bg-purple-600', 'text-white');
        button.classList.add('bg-gray-700', 'text-gray-300');
    } else {
        selectedDays.push(day);
        button.classList.add('bg-purple-600', 'text-white');
        button.classList.remove('bg-gray-700', 'text-gray-300');
    }
    document.getElementById('availableDaysInput').value = selectedDays.join(',');
}
</script>
