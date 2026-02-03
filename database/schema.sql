-- 버스킹고 데이터베이스 스키마 생성 스크립트
-- MySQL 8.0 이상 또는 MariaDB 10.5 이상 권장

-- 데이터베이스 생성
CREATE DATABASE IF NOT EXISTS buskinggo 
CHARACTER SET utf8mb4 
COLLATE utf8mb4_unicode_ci;

USE buskinggo;

-- 1. users 테이블 (사용자 정보)
CREATE TABLE users (
    id BIGINT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
    email VARCHAR(255) UNIQUE NOT NULL,
    password VARCHAR(255) NOT NULL COMMENT '해시된 비밀번호',
    name VARCHAR(100) NOT NULL COMMENT '이름/닉네임',
    user_type ENUM('viewer', 'artist') NOT NULL COMMENT '사용자 유형: viewer=관람자, artist=버스커',
    phone VARCHAR(20) NULL COMMENT '연락처',
    interested_location VARCHAR(50) NULL COMMENT '관심 지역',
    email_notification BOOLEAN DEFAULT FALSE COMMENT '이메일 마케팅 수신 동의',
    sms_notification BOOLEAN DEFAULT FALSE COMMENT 'SMS 마케팅 수신 동의',
    last_login_at DATETIME NULL COMMENT '마지막 로그인 시간',
    created_at DATETIME NOT NULL DEFAULT CURRENT_TIMESTAMP COMMENT '생성일시',
    updated_at DATETIME NULL ON UPDATE CURRENT_TIMESTAMP COMMENT '수정일시',
    INDEX idx_email (email),
    INDEX idx_user_type (user_type),
    INDEX idx_created_at (created_at)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci COMMENT='사용자 테이블';

-- 2. buskers 테이블 (버스커 상세 정보)
CREATE TABLE buskers (
    id BIGINT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
    user_id BIGINT UNSIGNED UNIQUE NOT NULL COMMENT '사용자 ID (users 테이블 참조)',
    team_name VARCHAR(100) NOT NULL COMMENT '팀명/예명',
    team_size INT UNSIGNED DEFAULT 1 COMMENT '팀 인원 수',
    equipment TEXT NULL COMMENT '보유 장비',
    contact_phone VARCHAR(20) NOT NULL COMMENT '대표 연락처',
    bio TEXT NULL COMMENT '자기소개',
    available_days JSON NULL COMMENT '공연 가능 요일 (배열: ["월", "화", ...])',
    preferred_time VARCHAR(50) NULL COMMENT '선호 시간대',
    activity_location VARCHAR(50) NOT NULL COMMENT '활동 지역',
    rating DECIMAL(3,2) DEFAULT 0.00 COMMENT '평점 (0.00 ~ 5.00)',
    performance_count INT UNSIGNED DEFAULT 0 COMMENT '공연 횟수',
    created_at DATETIME NOT NULL DEFAULT CURRENT_TIMESTAMP COMMENT '생성일시',
    updated_at DATETIME NULL ON UPDATE CURRENT_TIMESTAMP COMMENT '수정일시',
    FOREIGN KEY (user_id) REFERENCES users(id) ON DELETE CASCADE,
    INDEX idx_user_id (user_id),
    INDEX idx_activity_location (activity_location),
    INDEX idx_rating (rating)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci COMMENT='버스커 테이블';

-- 3. performances 테이블 (공연 정보)
CREATE TABLE performances (
    id BIGINT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
    busker_id BIGINT UNSIGNED NOT NULL COMMENT '버스커 ID',
    busker_name VARCHAR(100) NOT NULL COMMENT '버스커명 (캐시용)',
    location VARCHAR(200) NOT NULL COMMENT '공연 장소',
    lat DECIMAL(10, 8) NULL COMMENT '위도',
    lng DECIMAL(11, 8) NULL COMMENT '경도',
    start_time TIME NOT NULL COMMENT '시작 시간',
    end_time TIME NOT NULL COMMENT '종료 시간',
    performance_date DATE NOT NULL COMMENT '공연 날짜',
    status ENUM('예정', '진행중', '종료', '취소') DEFAULT '예정' COMMENT '공연 상태',
    image VARCHAR(255) NULL COMMENT '공연 이미지 URL/이모지',
    description TEXT NULL COMMENT '공연 설명',
    created_at DATETIME NOT NULL DEFAULT CURRENT_TIMESTAMP COMMENT '생성일시',
    updated_at DATETIME NULL ON UPDATE CURRENT_TIMESTAMP COMMENT '수정일시',
    FOREIGN KEY (busker_id) REFERENCES buskers(id) ON DELETE CASCADE,
    INDEX idx_busker_id (busker_id),
    INDEX idx_location (location),
    INDEX idx_status (status),
    INDEX idx_performance_date (performance_date),
    INDEX idx_lat_lng (lat, lng)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci COMMENT='공연 테이블';

-- 4. bookings 테이블 (예약 정보)
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
    created_by BIGINT UNSIGNED NOT NULL COMMENT '예약 생성자 ID',
    created_at DATETIME NOT NULL DEFAULT CURRENT_TIMESTAMP COMMENT '생성일시',
    updated_at DATETIME NULL ON UPDATE CURRENT_TIMESTAMP COMMENT '수정일시',
    FOREIGN KEY (busker_id) REFERENCES buskers(id) ON DELETE SET NULL,
    FOREIGN KEY (created_by) REFERENCES users(id) ON DELETE CASCADE,
    INDEX idx_busker_id (busker_id),
    INDEX idx_created_by (created_by),
    INDEX idx_status (status),
    INDEX idx_booking_date (booking_date)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci COMMENT='예약 테이블';

-- 5. community_posts 테이블 (커뮤니티 게시글)
CREATE TABLE community_posts (
    id BIGINT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
    user_id BIGINT UNSIGNED NOT NULL COMMENT '작성자 ID',
    author VARCHAR(100) NOT NULL COMMENT '작성자명 (캐시용)',
    tab ENUM('free', 'recruit', 'collab') NOT NULL COMMENT '게시판 탭: free=자유, recruit=팀원모집, collab=함께공연',
    title VARCHAR(255) NOT NULL COMMENT '제목',
    content TEXT NOT NULL COMMENT '내용',
    location VARCHAR(50) NULL COMMENT '지역 (recruit, collab 탭용)',
    genre VARCHAR(50) NULL COMMENT '장르 (recruit 탭용)',
    performance_date DATE NULL COMMENT '공연 날짜 (collab 탭용)',
    views INT UNSIGNED DEFAULT 0 COMMENT '조회수',
    comments_count INT UNSIGNED DEFAULT 0 COMMENT '댓글 수',
    created_at DATETIME NOT NULL DEFAULT CURRENT_TIMESTAMP COMMENT '생성일시',
    updated_at DATETIME NULL ON UPDATE CURRENT_TIMESTAMP COMMENT '수정일시',
    FOREIGN KEY (user_id) REFERENCES users(id) ON DELETE CASCADE,
    INDEX idx_user_id (user_id),
    INDEX idx_tab (tab),
    INDEX idx_created_at (created_at),
    INDEX idx_tab_created_at (tab, created_at)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci COMMENT='커뮤니티 게시글 테이블';

-- 6. community_comments 테이블 (커뮤니티 댓글)
CREATE TABLE community_comments (
    id BIGINT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
    post_id BIGINT UNSIGNED NOT NULL COMMENT '게시글 ID',
    user_id BIGINT UNSIGNED NOT NULL COMMENT '작성자 ID',
    author VARCHAR(100) NOT NULL COMMENT '작성자명 (캐시용)',
    tab ENUM('free', 'recruit', 'collab') NOT NULL COMMENT '게시판 탭',
    content TEXT NOT NULL COMMENT '댓글 내용',
    created_at DATETIME NOT NULL DEFAULT CURRENT_TIMESTAMP COMMENT '생성일시',
    updated_at DATETIME NULL ON UPDATE CURRENT_TIMESTAMP COMMENT '수정일시',
    FOREIGN KEY (post_id) REFERENCES community_posts(id) ON DELETE CASCADE,
    FOREIGN KEY (user_id) REFERENCES users(id) ON DELETE CASCADE,
    INDEX idx_post_id (post_id),
    INDEX idx_user_id (user_id),
    INDEX idx_created_at (created_at)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci COMMENT='커뮤니티 댓글 테이블';

-- 7. favorites 테이블 (찜하기)
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

-- 8. user_genres 테이블 (사용자 관심 장르 - 다대다 관계)
CREATE TABLE user_genres (
    id BIGINT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
    user_id BIGINT UNSIGNED NOT NULL COMMENT '사용자 ID',
    genre VARCHAR(50) NOT NULL COMMENT '장르 코드 (acoustic, rock, jazz 등)',
    created_at DATETIME NOT NULL DEFAULT CURRENT_TIMESTAMP COMMENT '생성일시',
    FOREIGN KEY (user_id) REFERENCES users(id) ON DELETE CASCADE,
    INDEX idx_user_id (user_id),
    UNIQUE KEY uk_user_genre (user_id, genre) COMMENT '중복 방지'
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci COMMENT='사용자 관심 장르 테이블';

-- 9. user_time_slots 테이블 (사용자 선호 시간대 - 다대다 관계)
CREATE TABLE user_time_slots (
    id BIGINT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
    user_id BIGINT UNSIGNED NOT NULL COMMENT '사용자 ID',
    time_slot VARCHAR(50) NOT NULL COMMENT '시간대 코드 (weekday_day, weekday_evening 등)',
    created_at DATETIME NOT NULL DEFAULT CURRENT_TIMESTAMP COMMENT '생성일시',
    FOREIGN KEY (user_id) REFERENCES users(id) ON DELETE CASCADE,
    INDEX idx_user_id (user_id),
    UNIQUE KEY uk_user_time_slot (user_id, time_slot) COMMENT '중복 방지'
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci COMMENT='사용자 선호 시간대 테이블';

-- 10. busker_genres 테이블 (버스커 공연 장르 - 다대다 관계)
CREATE TABLE busker_genres (
    id BIGINT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
    busker_id BIGINT UNSIGNED NOT NULL COMMENT '버스커 ID',
    genre VARCHAR(50) NOT NULL COMMENT '장르 코드 (acoustic, rock, jazz 등)',
    created_at DATETIME NOT NULL DEFAULT CURRENT_TIMESTAMP COMMENT '생성일시',
    FOREIGN KEY (busker_id) REFERENCES buskers(id) ON DELETE CASCADE,
    INDEX idx_busker_id (busker_id),
    UNIQUE KEY uk_busker_genre (busker_id, genre) COMMENT '중복 방지'
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci COMMENT='버스커 공연 장르 테이블';
