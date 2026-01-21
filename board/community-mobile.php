<?php
/**
 * 커뮤니티 페이지 모바일 버전
 */
// 관람자는 접근 불가 (아티스트만 접근 가능)
if (!isset($_SESSION['userType']) || $_SESSION['userType'] !== 'artist') {
    header('Location: index.php?page=split&appPage=home');
    exit;
}
$communityTab = $_GET['tab'] ?? 'free';
$viewPostId = isset($_GET['postId']) ? (int)$_GET['postId'] : null;
?>
<div class="p-4 space-y-4">
    <div class="bg-gradient-to-r from-green-600 to-teal-600 rounded-2xl p-6 text-white">
        <h2 class="text-2xl font-bold mb-2">커뮤니티</h2>
        <p class="text-sm opacity-90">아티스트들과 소통하고 정보를 공유하세요</p>
    </div>

    <!-- 탭 메뉴 및 글쓰기 버튼 -->
    <div class="px-4">
        <div class="flex gap-2 border-b border-gray-700 mb-4">
            <a href="index.php?page=split&appPage=community&tab=free" class="px-4 py-2 text-sm font-bold <?= $communityTab === 'free' ? 'text-purple-400 border-b-2 border-purple-400' : 'text-gray-400' ?>">
                자유게시판
            </a>
            <a href="index.php?page=split&appPage=community&tab=recruit" class="px-4 py-2 text-sm font-bold <?= $communityTab === 'recruit' ? 'text-purple-400 border-b-2 border-purple-400' : 'text-gray-400' ?>">
                팀원모집
            </a>
            <a href="index.php?page=split&appPage=community&tab=collab" class="px-4 py-2 text-sm font-bold <?= $communityTab === 'collab' ? 'text-purple-400 border-b-2 border-purple-400' : 'text-gray-400' ?>">
                함께공연
            </a>
        </div>
        <!-- 글쓰기 버튼 -->
        <button onclick="showWritePostModal('<?= $communityTab ?>')" class="w-full py-3 bg-gradient-to-r from-green-600 to-teal-600 text-white font-bold rounded-xl hover:scale-105 transition-transform flex items-center justify-center gap-2 mb-4">
            <i data-lucide="plus" style="width: 20px; height: 20px;"></i>
            글쓰기
        </button>
    </div>

    <!-- 게시글 목록 -->
    <div class="px-4 space-y-3 pb-4">
        <?php
        // 세션에서 게시글 가져오기 (최신순 정렬)
        $sessionPosts = $_SESSION['communityPosts'][$communityTab] ?? [];
        $defaultPosts = $communityPosts[$communityTab] ?? [];
        $posts = array_merge($sessionPosts, $defaultPosts);
        // ID 기준 역순 정렬 (최신순)
        usort($posts, function($a, $b) {
            return ($b['id'] ?? 0) - ($a['id'] ?? 0);
        });
        
        foreach ($posts as $post):
        ?>
        <div onclick="showPostDetail(<?= $post['id'] ?>, '<?= $communityTab ?>')" class="bg-gray-800/80 backdrop-blur-xl border border-gray-700/50 rounded-2xl p-4 shadow-lg hover:shadow-xl hover:border-green-500/50 transition-all cursor-pointer">
            <h3 class="font-bold text-white mb-2"><?= htmlspecialchars($post['title']) ?></h3>
            <div class="flex items-center gap-4 text-sm text-gray-300 flex-wrap">
                <span><?= htmlspecialchars($post['author']) ?></span>
                <span><?= htmlspecialchars($post['date']) ?></span>
                <?php if ($communityTab === 'free'): ?>
                <span>조회 <?= $post['views'] ?></span>
                <span>댓글 <?= $post['comments'] ?></span>
                <?php elseif ($communityTab === 'recruit'): ?>
                <span class="px-2 py-1 bg-purple-100 text-purple-700 rounded text-xs"><?= htmlspecialchars($post['location']) ?></span>
                <span class="px-2 py-1 bg-pink-100 text-pink-700 rounded text-xs"><?= htmlspecialchars($post['genre']) ?></span>
                <?php elseif ($communityTab === 'collab'): ?>
                <span class="px-2 py-1 bg-blue-100 text-blue-700 rounded text-xs">공연일: <?= htmlspecialchars($post['performanceDate']) ?></span>
                <span class="px-2 py-1 bg-purple-100 text-purple-700 rounded text-xs"><?= htmlspecialchars($post['location']) ?></span>
                <?php endif; ?>
            </div>
            <?php if (isset($post['content']) && $post['content']): ?>
            <p class="text-sm text-gray-400 mt-2 line-clamp-2"><?= htmlspecialchars($post['content']) ?></p>
            <?php endif; ?>
        </div>
        <?php endforeach; ?>
    </div>
</div>

<!-- 글쓰기 모달 -->
<div id="writePostModal" class="hidden fixed inset-0 bg-black/80 flex items-center justify-center z-50 p-4">
    <div class="bg-gray-800 rounded-2xl max-w-lg w-full p-6 shadow-xl border border-gray-700 max-h-[90vh] overflow-y-auto" onclick="event.stopPropagation()">
        <div class="flex items-center justify-between mb-4">
            <h2 class="text-2xl font-bold text-white">글쓰기</h2>
            <button onclick="closeWritePostModal()" class="p-2 hover:bg-gray-700 rounded-full text-gray-400">
                <i data-lucide="x" style="width: 24px; height: 24px;"></i>
            </button>
        </div>
        <form method="POST" action="index.php?page=split&appPage=community" id="writePostForm" class="space-y-4">
            <input type="hidden" name="writePost" value="1">
            <input type="hidden" name="tab" id="writePostTab" value="">
            <div>
                <label class="block text-sm font-bold mb-2 text-gray-300">제목 *</label>
                <input type="text" name="title" required class="w-full bg-gray-900 border border-gray-700 rounded-lg px-4 py-3 focus:outline-none focus:border-purple-500 text-white placeholder-gray-500" placeholder="제목을 입력하세요" />
            </div>
            <div>
                <label class="block text-sm font-bold mb-2 text-gray-300">내용 *</label>
                <textarea name="content" required rows="6" class="w-full bg-gray-900 border border-gray-700 rounded-lg px-4 py-3 focus:outline-none focus:border-purple-500 text-white placeholder-gray-500" placeholder="내용을 입력하세요"></textarea>
            </div>
            <!-- 팀원모집 추가 필드 -->
            <div id="recruitFields" class="hidden space-y-4">
                <div>
                    <label class="block text-sm font-bold mb-2 text-gray-300">지역</label>
                    <select name="location" class="w-full bg-gray-900 border border-gray-700 rounded-lg px-4 py-3 focus:outline-none focus:border-purple-500 text-white">
                        <?php foreach ($locationCoordinates as $loc => $coords): ?>
                        <option value="<?= htmlspecialchars($loc) ?>"><?= htmlspecialchars($loc) ?></option>
                        <?php endforeach; ?>
                    </select>
                </div>
                <div>
                    <label class="block text-sm font-bold mb-2 text-gray-300">장르</label>
                    <input type="text" name="genre" class="w-full bg-gray-900 border border-gray-700 rounded-lg px-4 py-3 focus:outline-none focus:border-purple-500 text-white placeholder-gray-500" placeholder="예: 어쿠스틱, 록, 재즈" />
                </div>
            </div>
            <!-- 함께공연 추가 필드 -->
            <div id="collabFields" class="hidden space-y-4">
                <div>
                    <label class="block text-sm font-bold mb-2 text-gray-300">공연 날짜</label>
                    <input type="date" name="performanceDate" class="w-full bg-gray-900 border border-gray-700 rounded-lg px-4 py-3 focus:outline-none focus:border-purple-500 text-white" />
                </div>
                <div>
                    <label class="block text-sm font-bold mb-2 text-gray-300">공연 장소</label>
                    <input type="text" name="location" class="w-full bg-gray-900 border border-gray-700 rounded-lg px-4 py-3 focus:outline-none focus:border-purple-500 text-white placeholder-gray-500" placeholder="예: 천안역 광장" />
                </div>
            </div>
            <div class="flex gap-3">
                <button type="button" onclick="closeWritePostModal()" class="flex-1 px-4 py-3 bg-gray-700 text-white font-bold rounded-lg hover:bg-gray-600 transition-colors">
                    취소
                </button>
                <button type="submit" class="flex-1 px-4 py-3 bg-gradient-to-r from-green-600 to-teal-600 text-white font-bold rounded-lg hover:scale-105 transition-transform">
                    등록
                </button>
            </div>
        </form>
    </div>
</div>

<!-- 게시글 상세 보기 모달 -->
<div id="postDetailModal" class="hidden fixed inset-0 bg-black/80 flex items-center justify-center z-50 p-4">
    <div class="bg-gray-800 rounded-2xl max-w-lg w-full p-6 shadow-xl border border-gray-700 max-h-[90vh] overflow-y-auto" onclick="event.stopPropagation()">
        <div id="postDetailContent">
            <!-- JavaScript로 동적으로 로드 -->
        </div>
    </div>
</div>

<script>
// Lucide 아이콘 초기화
if (typeof lucide !== 'undefined') {
    lucide.createIcons();
}

// 글쓰기 모달 표시
function showWritePostModal(tab) {
    document.getElementById('writePostTab').value = tab;
    
    // 탭별 추가 필드 표시/숨김
    const recruitFields = document.getElementById('recruitFields');
    const collabFields = document.getElementById('collabFields');
    
    if (recruitFields) recruitFields.classList.add('hidden');
    if (collabFields) collabFields.classList.add('hidden');
    
    if (tab === 'recruit') {
        if (recruitFields) recruitFields.classList.remove('hidden');
    } else if (tab === 'collab') {
        if (collabFields) collabFields.classList.remove('hidden');
    }
    
    document.getElementById('writePostModal').classList.remove('hidden');
    if (typeof lucide !== 'undefined') {
        lucide.createIcons();
    }
}

// 글쓰기 모달 닫기
function closeWritePostModal() {
    document.getElementById('writePostModal').classList.add('hidden');
    document.getElementById('writePostForm').reset();
}

// 게시글 상세 보기
function showPostDetail(postId, tab) {
    const modal = document.getElementById('postDetailModal');
    const content = document.getElementById('postDetailContent');
    
    // 게시글 데이터 가져오기 (세션 데이터 사용)
    const sessionPosts = <?= json_encode($_SESSION['communityPosts'][$communityTab] ?? []) ?>;
    const defaultPosts = <?= json_encode($communityPosts[$communityTab] ?? []) ?>;
    const posts = [...sessionPosts, ...defaultPosts];
    const post = posts.find(p => p.id == postId);
    
    if (!post) {
        alert('게시글을 찾을 수 없습니다.');
        return;
    }
    
    // 댓글 가져오기
    const comments = <?= json_encode($_SESSION['communityComments'][$communityTab] ?? []) ?>;
    const postComments = comments.filter(c => c.postId == postId);
    
    let html = `
        <div class="flex items-center justify-between mb-4">
            <h2 class="text-2xl font-bold text-white">게시글</h2>
            <button onclick="closePostDetailModal()" class="p-2 hover:bg-gray-700 rounded-full text-gray-400">
                <i data-lucide="x" style="width: 24px; height: 24px;"></i>
            </button>
        </div>
        
        <div class="space-y-4">
            <div>
                <h3 class="text-xl font-bold text-white mb-2">${post.title}</h3>
                <div class="flex items-center gap-4 text-sm text-gray-400 mb-4">
                    <span>${post.author}</span>
                    <span>${post.date}</span>
                    ${tab === 'free' ? `<span>조회 ${post.views}</span>` : ''}
                </div>
                <div class="text-gray-300 whitespace-pre-wrap">${post.content || '내용이 없습니다.'}</div>
            </div>
            
            <!-- 댓글 섹션 -->
            <div class="border-t border-gray-700 pt-4">
                <h4 class="font-bold text-white mb-3">댓글 (${postComments.length})</h4>
                
                <!-- 댓글 작성 폼 -->
                <form method="POST" action="index.php?page=split&appPage=community&tab=${tab}&postId=${postId}" class="mb-4">
                    <input type="hidden" name="writeComment" value="1">
                    <input type="hidden" name="postId" value="${postId}">
                    <input type="hidden" name="tab" value="${tab}">
                    <div class="flex gap-2">
                        <input type="text" name="comment" required placeholder="댓글을 입력하세요" class="flex-1 bg-gray-900 border border-gray-700 rounded-lg px-4 py-2 focus:outline-none focus:border-purple-500 text-white placeholder-gray-500" />
                        <button type="submit" class="px-4 py-2 bg-purple-600 text-white font-bold rounded-lg hover:bg-purple-700 transition-colors">
                            등록
                        </button>
                    </div>
                </form>
                
                <!-- 댓글 목록 -->
                <div class="space-y-3">
    `;
    
    if (postComments.length === 0) {
        html += '<p class="text-gray-500 text-sm text-center py-4">댓글이 없습니다.</p>';
    } else {
        postComments.forEach(comment => {
            html += `
                <div class="bg-gray-900 rounded-lg p-3">
                    <div class="flex items-center gap-2 mb-2">
                        <span class="font-bold text-white text-sm">${comment.author}</span>
                        <span class="text-gray-500 text-xs">${comment.date}</span>
                    </div>
                    <p class="text-gray-300 text-sm">${comment.content}</p>
                </div>
            `;
        });
    }
    
    html += `
                </div>
            </div>
        </div>
    `;
    
    content.innerHTML = html;
    modal.classList.remove('hidden');
    if (typeof lucide !== 'undefined') {
        lucide.createIcons();
    }
}

// 게시글 상세 모달 닫기
function closePostDetailModal() {
    document.getElementById('postDetailModal').classList.add('hidden');
}

// 모달 외부 클릭 시 닫기
document.getElementById('writePostModal')?.addEventListener('click', function(e) {
    if (e.target === this) {
        closeWritePostModal();
    }
});

document.getElementById('postDetailModal')?.addEventListener('click', function(e) {
    if (e.target === this) {
        closePostDetailModal();
    }
});

// 페이지 로드 시 특정 게시글 열기
<?php if (isset($viewPostId) && $viewPostId): ?>
window.addEventListener('DOMContentLoaded', function() {
    showPostDetail(<?= $viewPostId ?>, '<?= $communityTab ?>');
});
<?php endif; ?>
</script>

