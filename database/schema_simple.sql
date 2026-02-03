-- 버스킹고 데이터베이스 스키마 (현재 사용 중인 기능만)
-- 필수 테이블 7개만 포함

CREATE DATABASE IF NOT EXISTS buskinggo 
CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci;

USE buskinggo;

-- 1. users (사용자 테이블)
CREATE TABLE users (
    id BIGINT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
    email VARCHAR(255) UNIQUE NOT NULL COMMENT '이메일 (로그인 ID)',
    password VARCHAR(255) NOT NULL COMMENT '비밀번호 (해시)',
    name VARCHAR(100) NOT NULL COMMENT '이름/닉네임',
    user_type ENUM('viewer', 'artist') NOT NULL COMMENT '사용자 유형',
    phone VARCHAR(20) NULL COMMENT '연락처',
    interested_location VARCHAR(50) NULL COMMENT '관심 지역',
    last_login_at DATETIME NULL COMMENT '마지막 로그인 시간',
    created_at DATETIME NOT NULL DEFAULT CURRENT_TIMESTAMP COMMENT '생성일시',
    INDEX idx_email (email),
    INDEX idx_user_type (user_type)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci COMMENT='사용자 테이블';

-- 2. buskers (버스커 테이블)
CREATE TABLE buskers (
    id BIGINT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
    user_id BIGINT UNSIGNED UNIQUE NULL COMMENT '사용자 ID (회원가입 시 연결)',
    name VARCHAR(100) NOT NULL COMMENT '팀/개인명',
    team_size INT UNSIGNED DEFAULT 1 COMMENT '팀 인원 수',
    equipment TEXT NULL COMMENT '보유 장비',
    phone VARCHAR(20) NOT NULL COMMENT '연락처',
    bio TEXT NULL COMMENT '자기소개',
    available_days JSON NULL COMMENT '공연 가능 요일 (배열)',
    preferred_time VARCHAR(50) NULL COMMENT '선호 시간대',
    preferred_location VARCHAR(50) NULL COMMENT '선호 지역',
    rating DECIMAL(3,2) DEFAULT 0.00 COMMENT '평점',
    performance_count INT UNSIGNED DEFAULT 0 COMMENT '공연 횟수',
    created_at DATETIME NOT NULL DEFAULT CURRENT_TIMESTAMP COMMENT '생성일시',
    FOREIGN KEY (user_id) REFERENCES users(id) ON DELETE SET NULL,
    INDEX idx_user_id (user_id),
    INDEX idx_preferred_location (preferred_location)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci COMMENT='버스커 테이블';

-- 3. performances (공연 테이블)
CREATE TABLE performances (
    id BIGINT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
    busker_id BIGINT UNSIGNED NULL COMMENT '버스커 ID',
    busker_name VARCHAR(100) NOT NULL COMMENT '버스커명',
    location VARCHAR(200) NOT NULL COMMENT '공연 장소',
    lat DECIMAL(10, 8) NULL COMMENT '위도',
    lng DECIMAL(11, 8) NULL COMMENT '경도',
    start_time TIME NOT NULL COMMENT '시작 시간',
    end_time TIME NOT NULL COMMENT '종료 시간',
    performance_date DATE NOT NULL COMMENT '공연 날짜',
    status ENUM('예정', '진행중', '종료', '취소') DEFAULT '예정' COMMENT '공연 상태',
    image VARCHAR(255) NULL COMMENT '공연 이미지/이모지',
    description TEXT NULL COMMENT '공연 설명',
    rating DECIMAL(3,2) DEFAULT 0.00 COMMENT '평점',
    distance DECIMAL(5,2) NULL COMMENT '거리 (km)',
    created_at DATETIME NOT NULL DEFAULT CURRENT_TIMESTAMP COMMENT '생성일시',
    FOREIGN KEY (busker_id) REFERENCES buskers(id) ON DELETE SET NULL,
    INDEX idx_busker_id (busker_id),
    INDEX idx_location (location),
    INDEX idx_status (status),
    INDEX idx_performance_date (performance_date),
    INDEX idx_lat_lng (lat, lng)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci COMMENT='공연 테이블';

-- 4. bookings (예약 테이블)
CREATE TABLE bookings (
    id BIGINT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
    organizer_name VARCHAR(100) NOT NULL COMMENT '주최자명',
    organizer_type VARCHAR(50) NOT NULL COMMENT '주최자 유형',
    busker_id BIGINT UNSIGNED NULL COMMENT '예약할 버스커 ID',
    location VARCHAR(200) NOT NULL COMMENT '공연 장소',
    lat DECIMAL(10, 8) NULL COMMENT '위도',
    lng DECIMAL(11, 8) NULL COMMENT '경도',
    booking_date DATE NOT NULL COMMENT '예약 날짜',
    start_time TIME NOT NULL COMMENT '시작 시간',
    end_time TIME NOT NULL COMMENT '종료 시간',
    additional_request TEXT NULL COMMENT '추가 요청사항',
    status ENUM('대기중', '승인됨', '거절됨', '완료됨', '취소됨') DEFAULT '대기중' COMMENT '예약 상태',
    created_by BIGINT UNSIGNED NULL COMMENT '예약 생성자 ID',
    created_at DATETIME NOT NULL DEFAULT CURRENT_TIMESTAMP COMMENT '생성일시',
    FOREIGN KEY (busker_id) REFERENCES buskers(id) ON DELETE SET NULL,
    FOREIGN KEY (created_by) REFERENCES users(id) ON DELETE SET NULL,
    INDEX idx_busker_id (busker_id),
    INDEX idx_created_by (created_by),
    INDEX idx_status (status),
    INDEX idx_booking_date (booking_date)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci COMMENT='예약 테이블';

-- 5. community_posts (커뮤니티 게시글 테이블)
CREATE TABLE community_posts (
    id BIGINT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
    user_id BIGINT UNSIGNED NULL COMMENT '작성자 ID',
    author VARCHAR(100) NOT NULL COMMENT '작성자명',
    tab ENUM('free', 'recruit', 'collab') NOT NULL COMMENT '게시판 탭',
    title VARCHAR(255) NOT NULL COMMENT '제목',
    content TEXT NOT NULL COMMENT '내용',
    location VARCHAR(50) NULL COMMENT '지역 (recruit, collab용)',
    genre VARCHAR(50) NULL COMMENT '장르 (recruit용)',
    performance_date DATE NULL COMMENT '공연 날짜 (collab용)',
    views INT UNSIGNED DEFAULT 0 COMMENT '조회수',
    comments_count INT UNSIGNED DEFAULT 0 COMMENT '댓글 수',
    created_at DATETIME NOT NULL DEFAULT CURRENT_TIMESTAMP COMMENT '생성일시',
    FOREIGN KEY (user_id) REFERENCES users(id) ON DELETE SET NULL,
    INDEX idx_user_id (user_id),
    INDEX idx_tab (tab),
    INDEX idx_created_at (created_at),
    INDEX idx_tab_created_at (tab, created_at)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci COMMENT='커뮤니티 게시글 테이블';

-- 6. community_comments (커뮤니티 댓글 테이블)
CREATE TABLE community_comments (
    id BIGINT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
    post_id BIGINT UNSIGNED NOT NULL COMMENT '게시글 ID',
    user_id BIGINT UNSIGNED NULL COMMENT '작성자 ID',
    author VARCHAR(100) NOT NULL COMMENT '작성자명',
    tab ENUM('free', 'recruit', 'collab') NOT NULL COMMENT '게시판 탭',
    content TEXT NOT NULL COMMENT '댓글 내용',
    created_at DATETIME NOT NULL DEFAULT CURRENT_TIMESTAMP COMMENT '생성일시',
    FOREIGN KEY (post_id) REFERENCES community_posts(id) ON DELETE CASCADE,
    FOREIGN KEY (user_id) REFERENCES users(id) ON DELETE SET NULL,
    INDEX idx_post_id (post_id),
    INDEX idx_user_id (user_id),
    INDEX idx_created_at (created_at)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci COMMENT='커뮤니티 댓글 테이블';

-- 7. favorites (찜하기 테이블)
CREATE TABLE favorites (
    id BIGINT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
    user_id BIGINT UNSIGNED NOT NULL COMMENT '사용자 ID',
    performance_id BIGINT UNSIGNED NOT NULL COMMENT '공연 ID',
    created_at DATETIME NOT NULL DEFAULT CURRENT_TIMESTAMP COMMENT '생성일시',
    FOREIGN KEY (user_id) REFERENCES users(id) ON DELETE CASCADE,
    FOREIGN KEY (performance_id) REFERENCES performances(id) ON DELETE CASCADE,
    INDEX idx_user_id (user_id),
    INDEX idx_performance_id (performance_id),
    UNIQUE KEY uk_user_performance (user_id, performance_id) COMMENT '중복 방지'
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci COMMENT='찜하기 테이블';
