<?php
/**
 * 관리자 페이지
 */
session_start();
require_once __DIR__ . '/../config/database.php';

$pdo = getDBConnection();

// 관리자 권한 확인
function isAdmin() {
    global $pdo;
    
    if (!isset($_SESSION['userId'])) {
        return false;
    }
    
    if ($pdo) {
        try {
            $stmt = $pdo->prepare("SELECT user_type FROM users WHERE id = ?");
            $stmt->execute([$_SESSION['userId']]);
            $user = $stmt->fetch();
            
            return $user && $user['user_type'] === 'admin';
        } catch (PDOException $e) {
            return false;
        }
    }
    
    return isset($_SESSION['userType']) && $_SESSION['userType'] === 'admin';
}

if (!isAdmin()) {
    header('Location: ../index.php?page=home');
    exit;
}
?>
<!DOCTYPE html>
<html lang="ko">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>관리자 페이지 - 버스킹고</title>
    <style>
        * {
            margin: 0;
            padding: 0;
            box-sizing: border-box;
        }
        
        body {
            font-family: -apple-system, BlinkMacSystemFont, 'Segoe UI', Roboto, 'Helvetica Neue', Arial, sans-serif;
            background: #f5f5f5;
            color: #333;
        }
        
        .admin-container {
            max-width: 1400px;
            margin: 0 auto;
            padding: 20px;
        }
        
        .admin-header {
            background: white;
            padding: 20px;
            border-radius: 8px;
            margin-bottom: 20px;
            box-shadow: 0 2px 4px rgba(0,0,0,0.1);
        }
        
        .admin-header h1 {
            font-size: 24px;
            margin-bottom: 10px;
        }
        
        .admin-nav {
            display: flex;
            gap: 10px;
            margin-bottom: 20px;
            background: white;
            padding: 15px;
            border-radius: 8px;
            box-shadow: 0 2px 4px rgba(0,0,0,0.1);
        }
        
        .nav-btn {
            padding: 10px 20px;
            border: none;
            background: #007bff;
            color: white;
            border-radius: 5px;
            cursor: pointer;
            font-size: 14px;
            transition: background 0.3s;
        }
        
        .nav-btn:hover {
            background: #0056b3;
        }
        
        .nav-btn.active {
            background: #0056b3;
        }
        
        .content-area {
            background: white;
            padding: 20px;
            border-radius: 8px;
            box-shadow: 0 2px 4px rgba(0,0,0,0.1);
        }
        
        .search-bar {
            margin-bottom: 20px;
            display: flex;
            gap: 10px;
        }
        
        .search-bar input {
            flex: 1;
            padding: 10px;
            border: 1px solid #ddd;
            border-radius: 5px;
            font-size: 14px;
        }
        
        .search-bar button {
            padding: 10px 20px;
            background: #28a745;
            color: white;
            border: none;
            border-radius: 5px;
            cursor: pointer;
        }
        
        table {
            width: 100%;
            border-collapse: collapse;
            margin-top: 20px;
        }
        
        th, td {
            padding: 12px;
            text-align: left;
            border-bottom: 1px solid #ddd;
        }
        
        th {
            background: #f8f9fa;
            font-weight: 600;
        }
        
        tr:hover {
            background: #f8f9fa;
        }
        
        .btn {
            padding: 6px 12px;
            border: none;
            border-radius: 4px;
            cursor: pointer;
            font-size: 12px;
            margin-right: 5px;
        }
        
        .btn-edit {
            background: #ffc107;
            color: #000;
        }
        
        .btn-delete {
            background: #dc3545;
            color: white;
        }
        
        .btn-view {
            background: #17a2b8;
            color: white;
        }
        
        .pagination {
            margin-top: 20px;
            display: flex;
            justify-content: center;
            gap: 10px;
        }
        
        .pagination button {
            padding: 8px 12px;
            border: 1px solid #ddd;
            background: white;
            cursor: pointer;
            border-radius: 4px;
        }
        
        .pagination button.active {
            background: #007bff;
            color: white;
            border-color: #007bff;
        }
        
        .stats {
            display: grid;
            grid-template-columns: repeat(auto-fit, minmax(200px, 1fr));
            gap: 15px;
            margin-bottom: 20px;
        }
        
        .stat-card {
            background: linear-gradient(135deg, #667eea 0%, #764ba2 100%);
            color: white;
            padding: 20px;
            border-radius: 8px;
            text-align: center;
        }
        
        .stat-card h3 {
            font-size: 32px;
            margin-bottom: 5px;
        }
        
        .stat-card p {
            font-size: 14px;
            opacity: 0.9;
        }
        
        .hidden {
            display: none;
        }
    </style>
</head>
<body>
    <div class="admin-container">
        <div class="admin-header">
            <h1>관리자 페이지</h1>
            <p>사용자 및 게시글 관리</p>
        </div>
        
        <div class="admin-nav">
            <button class="nav-btn active" onclick="showSection('users')">사용자 관리</button>
            <button class="nav-btn" onclick="showSection('posts')">게시글 관리</button>
            <button class="nav-btn" onclick="showSection('bookings')">예약 관리</button>
        </div>
        
        <div id="users-section" class="content-area">
            <h2>사용자 관리</h2>
            <div class="search-bar">
                <input type="text" id="user-search" placeholder="아이디 또는 이름으로 검색...">
                <select id="user-type-filter">
                    <option value="">전체</option>
                    <option value="viewer">관람자</option>
                    <option value="artist">버스커</option>
                    <option value="admin">관리자</option>
                </select>
                <button onclick="loadUsers()">검색</button>
            </div>
            <div id="users-stats" class="stats"></div>
            <div id="users-table"></div>
        </div>
        
        <div id="posts-section" class="content-area hidden">
            <h2>게시글 관리</h2>
            <div class="search-bar">
                <input type="text" id="post-search" placeholder="제목, 내용, 작성자로 검색...">
                <select id="post-tab-filter">
                    <option value="">전체</option>
                    <option value="free">자유게시판</option>
                    <option value="recruit">모집</option>
                    <option value="collab">협업</option>
                </select>
                <button onclick="loadPosts()">검색</button>
            </div>
            <div id="posts-stats" class="stats"></div>
            <div id="posts-table"></div>
        </div>
        
        <div id="bookings-section" class="content-area hidden">
            <h2>예약 관리</h2>
            <div class="search-bar">
                <select id="booking-status-filter">
                    <option value="">전체</option>
                    <option value="대기중">대기중</option>
                    <option value="승인됨">승인됨</option>
                    <option value="거절됨">거절됨</option>
                    <option value="완료됨">완료됨</option>
                    <option value="취소됨">취소됨</option>
                </select>
                <button onclick="loadBookings()">검색</button>
            </div>
            <div id="bookings-stats" class="stats"></div>
            <div id="bookings-table"></div>
        </div>
    </div>
    
    <script>
        let currentPage = {
            users: 1,
            posts: 1,
            bookings: 1
        };
        
        function showSection(section) {
            document.querySelectorAll('.content-area').forEach(el => el.classList.add('hidden'));
            document.querySelectorAll('.nav-btn').forEach(btn => btn.classList.remove('active'));
            
            document.getElementById(section + '-section').classList.remove('hidden');
            event.target.classList.add('active');
            
            if (section === 'users') loadUsers();
            else if (section === 'posts') loadPosts();
            else if (section === 'bookings') loadBookings();
        }
        
        async function loadUsers(page = 1) {
            const search = document.getElementById('user-search').value;
            const userType = document.getElementById('user-type-filter').value;
            const params = new URLSearchParams({ resource: 'users', page, limit: 20 });
            if (search) params.append('search', search);
            if (userType) params.append('user_type', userType);
            
            try {
                const response = await fetch(`../api/admin.php?${params}`);
                const data = await response.json();
                
                if (data.success) {
                    displayUsers(data.data, data.pagination);
                    loadUserStats();
                }
            } catch (error) {
                console.error('Error loading users:', error);
            }
        }
        
        async function loadUserStats() {
            try {
                const response = await fetch('../api/admin.php?resource=users&limit=1000');
                const data = await response.json();
                
                if (data.success) {
                    const stats = {
                        total: data.pagination.total,
                        viewer: data.data.filter(u => u.user_type === 'viewer').length,
                        artist: data.data.filter(u => u.user_type === 'artist').length,
                        admin: data.data.filter(u => u.user_type === 'admin').length
                    };
                    
                    document.getElementById('users-stats').innerHTML = `
                        <div class="stat-card">
                            <h3>${stats.total}</h3>
                            <p>전체 사용자</p>
                        </div>
                        <div class="stat-card">
                            <h3>${stats.viewer}</h3>
                            <p>관람자</p>
                        </div>
                        <div class="stat-card">
                            <h3>${stats.artist}</h3>
                            <p>버스커</p>
                        </div>
                        <div class="stat-card">
                            <h3>${stats.admin}</h3>
                            <p>관리자</p>
                        </div>
                    `;
                }
            } catch (error) {
                console.error('Error loading stats:', error);
            }
        }
        
        function displayUsers(users, pagination) {
            const html = `
                <table>
                    <thead>
                        <tr>
                            <th>ID</th>
                            <th>아이디</th>
                            <th>이름</th>
                            <th>유형</th>
                            <th>연락처</th>
                            <th>가입일</th>
                            <th>작업</th>
                        </tr>
                    </thead>
                    <tbody>
                        ${users.map(user => `
                            <tr>
                                <td>${user.id}</td>
                                <td>${user.user_id}</td>
                                <td>${user.name}</td>
                                <td>${user.user_type}</td>
                                <td>${user.phone || '-'}</td>
                                <td>${user.created_at}</td>
                                <td>
                                    <button class="btn btn-delete" onclick="deleteUser(${user.id})">삭제</button>
                                </td>
                            </tr>
                        `).join('')}
                    </tbody>
                </table>
                ${pagination.totalPages > 1 ? `
                    <div class="pagination">
                        ${Array.from({length: pagination.totalPages}, (_, i) => i + 1).map(page => `
                            <button ${page === pagination.page ? 'class="active"' : ''} onclick="loadUsers(${page})">${page}</button>
                        `).join('')}
                    </div>
                ` : ''}
            `;
            document.getElementById('users-table').innerHTML = html;
        }
        
        async function deleteUser(id) {
            if (!confirm('정말 삭제하시겠습니까?')) return;
            
            try {
                const response = await fetch(`../api/admin.php?resource=users&id=${id}`, {
                    method: 'DELETE'
                });
                const data = await response.json();
                
                if (data.success) {
                    alert('삭제되었습니다.');
                    loadUsers(currentPage.users);
                } else {
                    alert('삭제 실패: ' + data.message);
                }
            } catch (error) {
                console.error('Error deleting user:', error);
                alert('삭제 중 오류가 발생했습니다.');
            }
        }
        
        async function loadPosts(page = 1) {
            const search = document.getElementById('post-search').value;
            const tab = document.getElementById('post-tab-filter').value;
            const params = new URLSearchParams({ resource: 'posts', page, limit: 20 });
            if (search) params.append('search', search);
            if (tab) params.append('tab', tab);
            
            try {
                const response = await fetch(`../api/admin.php?${params}`);
                const data = await response.json();
                
                if (data.success) {
                    displayPosts(data.data, data.pagination);
                    loadPostStats();
                }
            } catch (error) {
                console.error('Error loading posts:', error);
            }
        }
        
        async function loadPostStats() {
            try {
                const response = await fetch('../api/admin.php?resource=posts&limit=1000');
                const data = await response.json();
                
                if (data.success) {
                    const stats = {
                        total: data.pagination.total,
                        free: data.data.filter(p => p.tab === 'free').length,
                        recruit: data.data.filter(p => p.tab === 'recruit').length,
                        collab: data.data.filter(p => p.tab === 'collab').length
                    };
                    
                    document.getElementById('posts-stats').innerHTML = `
                        <div class="stat-card">
                            <h3>${stats.total}</h3>
                            <p>전체 게시글</p>
                        </div>
                        <div class="stat-card">
                            <h3>${stats.free}</h3>
                            <p>자유게시판</p>
                        </div>
                        <div class="stat-card">
                            <h3>${stats.recruit}</h3>
                            <p>모집</p>
                        </div>
                        <div class="stat-card">
                            <h3>${stats.collab}</h3>
                            <p>협업</p>
                        </div>
                    `;
                }
            } catch (error) {
                console.error('Error loading stats:', error);
            }
        }
        
        function displayPosts(posts, pagination) {
            const html = `
                <table>
                    <thead>
                        <tr>
                            <th>ID</th>
                            <th>제목</th>
                            <th>작성자</th>
                            <th>게시판</th>
                            <th>조회수</th>
                            <th>작성일</th>
                            <th>작업</th>
                        </tr>
                    </thead>
                    <tbody>
                        ${posts.map(post => `
                            <tr>
                                <td>${post.id}</td>
                                <td>${post.title.substring(0, 30)}${post.title.length > 30 ? '...' : ''}</td>
                                <td>${post.author}</td>
                                <td>${post.tab}</td>
                                <td>${post.views}</td>
                                <td>${post.date}</td>
                                <td>
                                    <button class="btn btn-delete" onclick="deletePost(${post.id})">삭제</button>
                                </td>
                            </tr>
                        `).join('')}
                    </tbody>
                </table>
                ${pagination.totalPages > 1 ? `
                    <div class="pagination">
                        ${Array.from({length: pagination.totalPages}, (_, i) => i + 1).map(page => `
                            <button ${page === pagination.page ? 'class="active"' : ''} onclick="loadPosts(${page})">${page}</button>
                        `).join('')}
                    </div>
                ` : ''}
            `;
            document.getElementById('posts-table').innerHTML = html;
        }
        
        async function deletePost(id) {
            if (!confirm('정말 삭제하시겠습니까?')) return;
            
            try {
                const response = await fetch(`../api/admin.php?resource=posts&id=${id}`, {
                    method: 'DELETE'
                });
                const data = await response.json();
                
                if (data.success) {
                    alert('삭제되었습니다.');
                    loadPosts(currentPage.posts);
                } else {
                    alert('삭제 실패: ' + data.message);
                }
            } catch (error) {
                console.error('Error deleting post:', error);
                alert('삭제 중 오류가 발생했습니다.');
            }
        }
        
        async function loadBookings(page = 1) {
            const status = document.getElementById('booking-status-filter').value;
            const params = new URLSearchParams({ resource: 'bookings', page, limit: 20 });
            if (status) params.append('status', status);
            
            try {
                const response = await fetch(`../api/admin.php?${params}`);
                const data = await response.json();
                
                if (data.success) {
                    displayBookings(data.data, data.pagination);
                }
            } catch (error) {
                console.error('Error loading bookings:', error);
            }
        }
        
        function displayBookings(bookings, pagination) {
            const html = `
                <table>
                    <thead>
                        <tr>
                            <th>ID</th>
                            <th>주최자</th>
                            <th>장소</th>
                            <th>날짜</th>
                            <th>시간</th>
                            <th>상태</th>
                            <th>작성일</th>
                            <th>작업</th>
                        </tr>
                    </thead>
                    <tbody>
                        ${bookings.map(booking => `
                            <tr>
                                <td>${booking.id}</td>
                                <td>${booking.organizer_name}</td>
                                <td>${booking.location}</td>
                                <td>${booking.date}</td>
                                <td>${booking.start_time} - ${booking.end_time}</td>
                                <td>${booking.status}</td>
                                <td>${booking.created_at}</td>
                                <td>
                                    <button class="btn btn-delete" onclick="deleteBooking(${booking.id})">삭제</button>
                                </td>
                            </tr>
                        `).join('')}
                    </tbody>
                </table>
                ${pagination.totalPages > 1 ? `
                    <div class="pagination">
                        ${Array.from({length: pagination.totalPages}, (_, i) => i + 1).map(page => `
                            <button ${page === pagination.page ? 'class="active"' : ''} onclick="loadBookings(${page})">${page}</button>
                        `).join('')}
                    </div>
                ` : ''}
            `;
            document.getElementById('bookings-table').innerHTML = html;
        }
        
        async function deleteBooking(id) {
            if (!confirm('정말 삭제하시겠습니까?')) return;
            
            try {
                const response = await fetch(`../api/admin.php?resource=bookings&id=${id}`, {
                    method: 'DELETE'
                });
                const data = await response.json();
                
                if (data.success) {
                    alert('삭제되었습니다.');
                    loadBookings(currentPage.bookings);
                } else {
                    alert('삭제 실패: ' + data.message);
                }
            } catch (error) {
                console.error('Error deleting booking:', error);
                alert('삭제 중 오류가 발생했습니다.');
            }
        }
        
        // 초기 로드
        loadUsers();
    </script>
</body>
</html>
