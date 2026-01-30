# 버스킹고 데이터베이스 스키마 (현재 사용 중인 기능만)

## 개요
현재 웹 애플리케이션에서 **실제로 사용 중인 기능**만을 반영한 최소한의 데이터베이스 스키마입니다.

---

## 필수 테이블 (7개)

### 1. users (사용자 테이블)
회원가입/로그인에 사용되는 기본 사용자 정보

| 컬럼명 | 타입 | 제약조건 | 설명 | 사용처 |
|--------|------|----------|------|--------|
| id | BIGINT UNSIGNED | PRIMARY KEY, AUTO_INCREMENT | 사용자 고유 ID | 모든 API |
| email | VARCHAR(255) | UNIQUE, NOT NULL | 이메일 (로그인 ID) | register.php, auth.php |
| password | VARCHAR(255) | NOT NULL | 비밀번호 (해시) | register.php, auth.php |
| name | VARCHAR(100) | NOT NULL | 이름/닉네임 | register.php |
| user_type | ENUM('viewer', 'artist') | NOT NULL | 사용자 유형 | register.php, auth.php |
| phone | VARCHAR(20) | NULL | 연락처 | register.php |
| interested_location | VARCHAR(50) | NULL | 관심 지역 | register.php, index.php |
| last_login_at | DATETIME | NULL | 마지막 로그인 시간 | auth.php |
| created_at | DATETIME | NOT NULL, DEFAULT CURRENT_TIMESTAMP | 생성일시 | register.php |

**인덱스:** `idx_email`, `idx_user_type`

---

### 2. buskers (버스커 테이블)
버스커 등록 페이지에서 사용

| 컬럼명 | 타입 | 제약조건 | 설명 | 사용처 |
|--------|------|----------|------|--------|
| id | BIGINT UNSIGNED | PRIMARY KEY, AUTO_INCREMENT | 버스커 고유 ID | buskers.php, index.php |
| user_id | BIGINT UNSIGNED | FOREIGN KEY (users.id), UNIQUE, NULL | 사용자 ID (회원가입 시 연결) | register.php (artist) |
| name | VARCHAR(100) | NOT NULL | 팀/개인명 | buskers.php, index.php |
| team_size | INT UNSIGNED | DEFAULT 1 | 팀 인원 수 | buskers.php, index.php |
| equipment | TEXT | NULL | 보유 장비 | buskers.php, index.php |
| phone | VARCHAR(20) | NOT NULL | 연락처 | buskers.php, index.php |
| bio | TEXT | NULL | 자기소개 | buskers.php, index.php |
| available_days | JSON | NULL | 공연 가능 요일 (배열) | buskers.php, index.php |
| preferred_time | VARCHAR(50) | NULL | 선호 시간대 | buskers.php, index.php |
| preferred_location | VARCHAR(50) | NULL | 선호 지역 | buskers.php, index.php |
| rating | DECIMAL(3,2) | DEFAULT 0.00 | 평점 | buskers.php, index.php |
| performance_count | INT UNSIGNED | DEFAULT 0 | 공연 횟수 | buskers.php, index.php |
| created_at | DATETIME | NOT NULL, DEFAULT CURRENT_TIMESTAMP | 생성일시 | buskers.php, index.php |

**인덱스:** `idx_user_id`, `idx_preferred_location`

**참고:** 
- 회원가입 시 `user_id` 연결 (artist 유형)
- 또는 독립적으로 버스커 등록 가능 (user_id NULL 허용)

---

### 3. performances (공연 테이블)
홈 페이지 공연 목록/지도에 표시

| 컬럼명 | 타입 | 제약조건 | 설명 | 사용처 |
|--------|------|----------|------|--------|
| id | BIGINT UNSIGNED | PRIMARY KEY, AUTO_INCREMENT | 공연 고유 ID | performances.php, index.php |
| busker_id | BIGINT UNSIGNED | FOREIGN KEY (buskers.id), NULL | 버스커 ID | performances.php |
| busker_name | VARCHAR(100) | NOT NULL | 버스커명 | performances.php, index.php |
| location | VARCHAR(200) | NOT NULL | 공연 장소 | performances.php, index.php |
| lat | DECIMAL(10, 8) | NULL | 위도 | performances.php, index.php |
| lng | DECIMAL(11, 8) | NULL | 경도 | performances.php, index.php |
| start_time | TIME | NOT NULL | 시작 시간 | performances.php, index.php |
| end_time | TIME | NOT NULL | 종료 시간 | performances.php, index.php |
| performance_date | DATE | NOT NULL | 공연 날짜 | performances.php |
| status | ENUM('예정', '진행중', '종료', '취소') | DEFAULT '예정' | 공연 상태 | performances.php, index.php |
| image | VARCHAR(255) | NULL | 공연 이미지/이모지 | performances.php, index.php |
| description | TEXT | NULL | 공연 설명 | performances.php, index.php |
| rating | DECIMAL(3,2) | DEFAULT 0.00 | 평점 | index.php (샘플 데이터) |
| distance | DECIMAL(5,2) | NULL | 거리 (km) | index.php (샘플 데이터) |
| created_at | DATETIME | NOT NULL, DEFAULT CURRENT_TIMESTAMP | 생성일시 | performances.php |

**인덱스:** `idx_busker_id`, `idx_location`, `idx_status`, `idx_performance_date`, `idx_lat_lng`

---

### 4. bookings (예약 테이블)
공연 예약 페이지에서 사용

| 컬럼명 | 타입 | 제약조건 | 설명 | 사용처 |
|--------|------|----------|------|--------|
| id | BIGINT UNSIGNED | PRIMARY KEY, AUTO_INCREMENT | 예약 고유 ID | bookings.php, index.php |
| organizer_name | VARCHAR(100) | NOT NULL | 주최자명 | bookings.php, index.php |
| organizer_type | VARCHAR(50) | NOT NULL | 주최자 유형 | bookings.php, index.php |
| busker_id | BIGINT UNSIGNED | FOREIGN KEY (buskers.id), NULL | 예약할 버스커 ID | bookings.php, index.php |
| location | VARCHAR(200) | NOT NULL | 공연 장소 | bookings.php, index.php |
| lat | DECIMAL(10, 8) | NULL | 위도 | bookings.php, index.php |
| lng | DECIMAL(11, 8) | NULL | 경도 | bookings.php, index.php |
| booking_date | DATE | NOT NULL | 예약 날짜 | bookings.php, index.php |
| start_time | TIME | NOT NULL | 시작 시간 | bookings.php, index.php |
| end_time | TIME | NOT NULL | 종료 시간 | bookings.php, index.php |
| additional_request | TEXT | NULL | 추가 요청사항 | bookings.php, index.php |
| status | ENUM('대기중', '승인됨', '거절됨', '완료됨', '취소됨') | DEFAULT '대기중' | 예약 상태 | bookings.php, index.php |
| created_by | BIGINT UNSIGNED | FOREIGN KEY (users.id), NULL | 예약 생성자 ID | bookings.php, index.php |
| created_at | DATETIME | NOT NULL, DEFAULT CURRENT_TIMESTAMP | 생성일시 | bookings.php, index.php |

**인덱스:** `idx_busker_id`, `idx_created_by`, `idx_status`, `idx_booking_date`

---

### 5. community_posts (커뮤니티 게시글 테이블)
커뮤니티 게시판에서 사용

| 컬럼명 | 타입 | 제약조건 | 설명 | 사용처 |
|--------|------|----------|------|--------|
| id | BIGINT UNSIGNED | PRIMARY KEY, AUTO_INCREMENT | 게시글 고유 ID | community.php, index.php |
| user_id | BIGINT UNSIGNED | FOREIGN KEY (users.id), NULL | 작성자 ID | community.php, index.php |
| author | VARCHAR(100) | NOT NULL | 작성자명 | community.php, index.php |
| tab | ENUM('free', 'recruit', 'collab') | NOT NULL | 게시판 탭 | community.php, index.php |
| title | VARCHAR(255) | NOT NULL | 제목 | community.php, index.php |
| content | TEXT | NOT NULL | 내용 | community.php, index.php |
| location | VARCHAR(50) | NULL | 지역 (recruit, collab용) | community.php, index.php |
| genre | VARCHAR(50) | NULL | 장르 (recruit용) | community.php, index.php |
| performance_date | DATE | NULL | 공연 날짜 (collab용) | community.php, index.php |
| views | INT UNSIGNED | DEFAULT 0 | 조회수 | community.php, index.php |
| comments_count | INT UNSIGNED | DEFAULT 0 | 댓글 수 | community.php, index.php |
| created_at | DATETIME | NOT NULL, DEFAULT CURRENT_TIMESTAMP | 생성일시 | community.php, index.php |

**인덱스:** `idx_user_id`, `idx_tab`, `idx_created_at`, `idx_tab_created_at`

---

### 6. community_comments (커뮤니티 댓글 테이블)
커뮤니티 게시글 댓글에 사용

| 컬럼명 | 타입 | 제약조건 | 설명 | 사용처 |
|--------|------|----------|------|--------|
| id | BIGINT UNSIGNED | PRIMARY KEY, AUTO_INCREMENT | 댓글 고유 ID | comments.php, index.php |
| post_id | BIGINT UNSIGNED | FOREIGN KEY (community_posts.id), NOT NULL | 게시글 ID | comments.php, index.php |
| user_id | BIGINT UNSIGNED | FOREIGN KEY (users.id), NULL | 작성자 ID | comments.php, index.php |
| author | VARCHAR(100) | NOT NULL | 작성자명 | comments.php, index.php |
| tab | ENUM('free', 'recruit', 'collab') | NOT NULL | 게시판 탭 | comments.php, index.php |
| content | TEXT | NOT NULL | 댓글 내용 | comments.php, index.php |
| created_at | DATETIME | NOT NULL, DEFAULT CURRENT_TIMESTAMP | 생성일시 | comments.php, index.php |

**인덱스:** `idx_post_id`, `idx_user_id`, `idx_created_at`

---

### 7. favorites (찜하기 테이블)
찜하기 기능에 사용

| 컬럼명 | 타입 | 제약조건 | 설명 | 사용처 |
|--------|------|----------|------|--------|
| id | BIGINT UNSIGNED | PRIMARY KEY, AUTO_INCREMENT | 찜하기 고유 ID | index.php |
| user_id | BIGINT UNSIGNED | FOREIGN KEY (users.id), NOT NULL | 사용자 ID | index.php |
| performance_id | BIGINT UNSIGNED | FOREIGN KEY (performances.id), NOT NULL | 공연 ID | index.php |
| created_at | DATETIME | NOT NULL, DEFAULT CURRENT_TIMESTAMP | 생성일시 | index.php |

**인덱스:** `idx_user_id`, `idx_performance_id`
**UNIQUE:** `(user_id, performance_id)` - 중복 방지

---

## 선택 테이블 (정규화용, 현재는 배열로 저장 가능)

현재 코드에서는 배열로 저장하지만, 정규화를 원할 경우 사용:

### 8. user_genres (사용자 관심 장르) - 선택
관람자의 관심 장르 (register.php에서 `interestedGenres` 배열로 저장)

### 9. busker_genres (버스커 공연 장르) - 선택  
버스커의 공연 장르 (register.php에서 `performanceGenres` 배열로 저장)

### 10. user_time_slots (사용자 선호 시간대) - 선택
관람자의 선호 시간대 (register.php에서 `preferredTimeSlots` 배열로 저장)

**참고:** 현재는 `users` 테이블에 JSON 컬럼으로 저장하거나, 별도 테이블로 분리 가능합니다.

---

## 최소 SQL 스크립트 (필수 테이블만)

```sql
CREATE DATABASE IF NOT EXISTS buskinggo 
CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci;

USE buskinggo;

-- 1. users
CREATE TABLE users (
    id BIGINT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
    email VARCHAR(255) UNIQUE NOT NULL,
    password VARCHAR(255) NOT NULL,
    name VARCHAR(100) NOT NULL,
    user_type ENUM('viewer', 'artist') NOT NULL,
    phone VARCHAR(20) NULL,
    interested_location VARCHAR(50) NULL,
    last_login_at DATETIME NULL,
    created_at DATETIME NOT NULL DEFAULT CURRENT_TIMESTAMP,
    INDEX idx_email (email),
    INDEX idx_user_type (user_type)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- 2. buskers
CREATE TABLE buskers (
    id BIGINT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
    user_id BIGINT UNSIGNED UNIQUE NULL,
    name VARCHAR(100) NOT NULL,
    team_size INT UNSIGNED DEFAULT 1,
    equipment TEXT NULL,
    phone VARCHAR(20) NOT NULL,
    bio TEXT NULL,
    available_days JSON NULL,
    preferred_time VARCHAR(50) NULL,
    preferred_location VARCHAR(50) NULL,
    rating DECIMAL(3,2) DEFAULT 0.00,
    performance_count INT UNSIGNED DEFAULT 0,
    created_at DATETIME NOT NULL DEFAULT CURRENT_TIMESTAMP,
    FOREIGN KEY (user_id) REFERENCES users(id) ON DELETE SET NULL,
    INDEX idx_user_id (user_id),
    INDEX idx_preferred_location (preferred_location)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- 3. performances
CREATE TABLE performances (
    id BIGINT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
    busker_id BIGINT UNSIGNED NULL,
    busker_name VARCHAR(100) NOT NULL,
    location VARCHAR(200) NOT NULL,
    lat DECIMAL(10, 8) NULL,
    lng DECIMAL(11, 8) NULL,
    start_time TIME NOT NULL,
    end_time TIME NOT NULL,
    performance_date DATE NOT NULL,
    status ENUM('예정', '진행중', '종료', '취소') DEFAULT '예정',
    image VARCHAR(255) NULL,
    description TEXT NULL,
    rating DECIMAL(3,2) DEFAULT 0.00,
    distance DECIMAL(5,2) NULL,
    created_at DATETIME NOT NULL DEFAULT CURRENT_TIMESTAMP,
    FOREIGN KEY (busker_id) REFERENCES buskers(id) ON DELETE SET NULL,
    INDEX idx_busker_id (busker_id),
    INDEX idx_location (location),
    INDEX idx_status (status),
    INDEX idx_performance_date (performance_date),
    INDEX idx_lat_lng (lat, lng)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- 4. bookings
CREATE TABLE bookings (
    id BIGINT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
    organizer_name VARCHAR(100) NOT NULL,
    organizer_type VARCHAR(50) NOT NULL,
    busker_id BIGINT UNSIGNED NULL,
    location VARCHAR(200) NOT NULL,
    lat DECIMAL(10, 8) NULL,
    lng DECIMAL(11, 8) NULL,
    booking_date DATE NOT NULL,
    start_time TIME NOT NULL,
    end_time TIME NOT NULL,
    additional_request TEXT NULL,
    status ENUM('대기중', '승인됨', '거절됨', '완료됨', '취소됨') DEFAULT '대기중',
    created_by BIGINT UNSIGNED NULL,
    created_at DATETIME NOT NULL DEFAULT CURRENT_TIMESTAMP,
    FOREIGN KEY (busker_id) REFERENCES buskers(id) ON DELETE SET NULL,
    FOREIGN KEY (created_by) REFERENCES users(id) ON DELETE SET NULL,
    INDEX idx_busker_id (busker_id),
    INDEX idx_created_by (created_by),
    INDEX idx_status (status),
    INDEX idx_booking_date (booking_date)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- 5. community_posts
CREATE TABLE community_posts (
    id BIGINT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
    user_id BIGINT UNSIGNED NULL,
    author VARCHAR(100) NOT NULL,
    tab ENUM('free', 'recruit', 'collab') NOT NULL,
    title VARCHAR(255) NOT NULL,
    content TEXT NOT NULL,
    location VARCHAR(50) NULL,
    genre VARCHAR(50) NULL,
    performance_date DATE NULL,
    views INT UNSIGNED DEFAULT 0,
    comments_count INT UNSIGNED DEFAULT 0,
    created_at DATETIME NOT NULL DEFAULT CURRENT_TIMESTAMP,
    FOREIGN KEY (user_id) REFERENCES users(id) ON DELETE SET NULL,
    INDEX idx_user_id (user_id),
    INDEX idx_tab (tab),
    INDEX idx_created_at (created_at),
    INDEX idx_tab_created_at (tab, created_at)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- 6. community_comments
CREATE TABLE community_comments (
    id BIGINT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
    post_id BIGINT UNSIGNED NOT NULL,
    user_id BIGINT UNSIGNED NULL,
    author VARCHAR(100) NOT NULL,
    tab ENUM('free', 'recruit', 'collab') NOT NULL,
    content TEXT NOT NULL,
    created_at DATETIME NOT NULL DEFAULT CURRENT_TIMESTAMP,
    FOREIGN KEY (post_id) REFERENCES community_posts(id) ON DELETE CASCADE,
    FOREIGN KEY (user_id) REFERENCES users(id) ON DELETE SET NULL,
    INDEX idx_post_id (post_id),
    INDEX idx_user_id (user_id),
    INDEX idx_created_at (created_at)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- 7. favorites
CREATE TABLE favorites (
    id BIGINT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
    user_id BIGINT UNSIGNED NOT NULL,
    performance_id BIGINT UNSIGNED NOT NULL,
    created_at DATETIME NOT NULL DEFAULT CURRENT_TIMESTAMP,
    FOREIGN KEY (user_id) REFERENCES users(id) ON DELETE CASCADE,
    FOREIGN KEY (performance_id) REFERENCES performances(id) ON DELETE CASCADE,
    INDEX idx_user_id (user_id),
    INDEX idx_performance_id (performance_id),
    UNIQUE KEY uk_user_performance (user_id, performance_id)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
```

---

## 주요 변경사항 (전체 스키마 대비)

1. **제거된 테이블:**
   - `user_genres` (현재 배열로 저장)
   - `user_time_slots` (현재 배열로 저장)
   - `busker_genres` (현재 배열로 저장)

2. **간소화된 필드:**
   - `users` 테이블에 `interestedGenres`, `preferredTimeSlots`는 JSON 컬럼으로 저장 가능
   - `buskers` 테이블에 `performanceGenres`는 JSON 컬럼으로 저장 가능

3. **NULL 허용:**
   - `buskers.user_id` - 독립적으로 버스커 등록 가능
   - `bookings.created_by` - 비회원 예약 가능
   - `community_posts.user_id` - 비회원 게시글 가능

---

## 사용 예시 쿼리

### 사용자 로그인
```sql
SELECT id, email, name, user_type, phone, interested_location, 
       last_login_at, created_at
FROM users 
WHERE email = ?;
```

### 버스커 목록 (지역 필터링)
```sql
SELECT * FROM buskers 
WHERE preferred_location LIKE ? 
ORDER BY rating DESC, performance_count DESC;
```

### 공연 목록 (지역/상태 필터링)
```sql
SELECT * FROM performances 
WHERE location LIKE ? AND status = ? 
ORDER BY performance_date ASC, start_time ASC;
```

### 찜한 공연 목록
```sql
SELECT p.* FROM favorites f
INNER JOIN performances p ON f.performance_id = p.id
WHERE f.user_id = ?
ORDER BY f.created_at DESC;
```

### 커뮤니티 게시글 목록 (탭별)
```sql
SELECT * FROM community_posts 
WHERE tab = ? 
ORDER BY created_at DESC 
LIMIT ? OFFSET ?;
```
