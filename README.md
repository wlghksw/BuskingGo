# 버스킹고 데이터베이스 스키마 설계

## 개요
버스킹고 애플리케이션의 데이터베이스 테이블 구조 및 컬럼 정보입니다.

## 데이터베이스 정보
- 권장 DBMS: MySQL 8.0 이상 또는 MariaDB 10.5 이상
- 문자셋: utf8mb4
- 콜레이션: utf8mb4_unicode_ci

---

## 테이블 목록

### 1. users (사용자 테이블)
기본 사용자 정보를 저장하는 테이블입니다.

| 컬럼명 | 타입 | 제약조건 | 설명 |
|--------|------|----------|------|
| id | BIGINT UNSIGNED | PRIMARY KEY, AUTO_INCREMENT | 사용자 고유 ID |
| email | VARCHAR(255) | UNIQUE, NOT NULL | 이메일 (로그인 ID) |
| password | VARCHAR(255) | NOT NULL | 비밀번호 (해시) |
| name | VARCHAR(100) | NOT NULL | 이름/닉네임 |
| user_type | ENUM('viewer', 'artist') | NOT NULL | 사용자 유형 (관람자/버스커) |
| phone | VARCHAR(20) | NULL | 연락처 |
| interested_location | VARCHAR(50) | NULL | 관심 지역 |
| email_notification | BOOLEAN | DEFAULT FALSE | 이메일 마케팅 수신 동의 |
| sms_notification | BOOLEAN | DEFAULT FALSE | SMS 마케팅 수신 동의 |
| last_login_at | DATETIME | NULL | 마지막 로그인 시간 |
| created_at | DATETIME | NOT NULL, DEFAULT CURRENT_TIMESTAMP | 생성일시 |
| updated_at | DATETIME | NULL, ON UPDATE CURRENT_TIMESTAMP | 수정일시 |

**인덱스:**
- `idx_email` ON `email`
- `idx_user_type` ON `user_type`
- `idx_created_at` ON `created_at`

---

### 2. buskers (버스커 테이블)
버스커 상세 정보를 저장하는 테이블입니다.

| 컬럼명 | 타입 | 제약조건 | 설명 |
|--------|------|----------|------|
| id | BIGINT UNSIGNED | PRIMARY KEY, AUTO_INCREMENT | 버스커 고유 ID |
| user_id | BIGINT UNSIGNED | FOREIGN KEY (users.id), UNIQUE, NOT NULL | 사용자 ID (users 테이블 참조) |
| team_name | VARCHAR(100) | NOT NULL | 팀명/예명 |
| team_size | INT UNSIGNED | DEFAULT 1 | 팀 인원 수 |
| equipment | TEXT | NULL | 보유 장비 |
| contact_phone | VARCHAR(20) | NOT NULL | 대표 연락처 |
| bio | TEXT | NULL | 자기소개 |
| available_days | JSON | NULL | 공연 가능 요일 (배열: ['월', '화', ...]) |
| preferred_time | VARCHAR(50) | NULL | 선호 시간대 |
| activity_location | VARCHAR(50) | NOT NULL | 활동 지역 |
| rating | DECIMAL(3,2) | DEFAULT 0.00 | 평점 (0.00 ~ 5.00) |
| performance_count | INT UNSIGNED | DEFAULT 0 | 공연 횟수 |
| created_at | DATETIME | NOT NULL, DEFAULT CURRENT_TIMESTAMP | 생성일시 |
| updated_at | DATETIME | NULL, ON UPDATE CURRENT_TIMESTAMP | 수정일시 |

**인덱스:**
- `idx_user_id` ON `user_id`
- `idx_activity_location` ON `activity_location`
- `idx_rating` ON `rating`

---

### 3. performances (공연 테이블)
실시간 공연 정보를 저장하는 테이블입니다.

| 컬럼명 | 타입 | 제약조건 | 설명 |
|--------|------|----------|------|
| id | BIGINT UNSIGNED | PRIMARY KEY, AUTO_INCREMENT | 공연 고유 ID |
| busker_id | BIGINT UNSIGNED | FOREIGN KEY (buskers.id), NOT NULL | 버스커 ID |
| busker_name | VARCHAR(100) | NOT NULL | 버스커명 (캐시용) |
| location | VARCHAR(200) | NOT NULL | 공연 장소 |
| lat | DECIMAL(10, 8) | NULL | 위도 |
| lng | DECIMAL(11, 8) | NULL | 경도 |
| start_time | TIME | NOT NULL | 시작 시간 |
| end_time | TIME | NOT NULL | 종료 시간 |
| performance_date | DATE | NOT NULL | 공연 날짜 |
| status | ENUM('예정', '진행중', '종료', '취소') | DEFAULT '예정' | 공연 상태 |
| image | VARCHAR(255) | NULL | 공연 이미지 URL/이모지 |
| description | TEXT | NULL | 공연 설명 |
| created_at | DATETIME | NOT NULL, DEFAULT CURRENT_TIMESTAMP | 생성일시 |
| updated_at | DATETIME | NULL, ON UPDATE CURRENT_TIMESTAMP | 수정일시 |

**인덱스:**
- `idx_busker_id` ON `busker_id`
- `idx_location` ON `location`
- `idx_status` ON `status`
- `idx_performance_date` ON `performance_date`
- `idx_lat_lng` ON `lat`, `lng` (복합 인덱스)

---

### 4. bookings (예약 테이블)
공연 예약 정보를 저장하는 테이블입니다.

| 컬럼명 | 타입 | 제약조건 | 설명 |
|--------|------|----------|------|
| id | BIGINT UNSIGNED | PRIMARY KEY, AUTO_INCREMENT | 예약 고유 ID |
| organizer_name | VARCHAR(100) | NOT NULL | 주최자명 |
| organizer_type | VARCHAR(50) | NOT NULL | 주최자 유형 |
| busker_id | BIGINT UNSIGNED | FOREIGN KEY (buskers.id), NULL | 예약할 버스커 ID |
| location | VARCHAR(200) | NOT NULL | 공연 장소 |
| lat | DECIMAL(10, 8) | NULL | 위도 |
| lng | DECIMAL(11, 8) | NULL | 경도 |
| booking_date | DATE | NOT NULL | 예약 날짜 |
| start_time | TIME | NOT NULL | 시작 시간 |
| end_time | TIME | NOT NULL | 종료 시간 |
| additional_request | TEXT | NULL | 추가 요청사항 |
| status | ENUM('대기중', '승인됨', '거절됨', '완료됨', '취소됨') | DEFAULT '대기중' | 예약 상태 |
| created_by | BIGINT UNSIGNED | FOREIGN KEY (users.id), NOT NULL | 예약 생성자 ID |
| created_at | DATETIME | NOT NULL, DEFAULT CURRENT_TIMESTAMP | 생성일시 |
| updated_at | DATETIME | NULL, ON UPDATE CURRENT_TIMESTAMP | 수정일시 |

**인덱스:**
- `idx_busker_id` ON `busker_id`
- `idx_created_by` ON `created_by`
- `idx_status` ON `status`
- `idx_booking_date` ON `booking_date`

---

### 5. community_posts (커뮤니티 게시글 테이블)
커뮤니티 게시글 정보를 저장하는 테이블입니다.

| 컬럼명 | 타입 | 제약조건 | 설명 |
|--------|------|----------|------|
| id | BIGINT UNSIGNED | PRIMARY KEY, AUTO_INCREMENT | 게시글 고유 ID |
| user_id | BIGINT UNSIGNED | FOREIGN KEY (users.id), NOT NULL | 작성자 ID |
| author | VARCHAR(100) | NOT NULL | 작성자명 (캐시용) |
| tab | ENUM('free', 'recruit', 'collab') | NOT NULL | 게시판 탭 (자유/팀원모집/함께공연) |
| title | VARCHAR(255) | NOT NULL | 제목 |
| content | TEXT | NOT NULL | 내용 |
| location | VARCHAR(50) | NULL | 지역 (recruit, collab 탭용) |
| genre | VARCHAR(50) | NULL | 장르 (recruit 탭용) |
| performance_date | DATE | NULL | 공연 날짜 (collab 탭용) |
| views | INT UNSIGNED | DEFAULT 0 | 조회수 |
| comments_count | INT UNSIGNED | DEFAULT 0 | 댓글 수 |
| created_at | DATETIME | NOT NULL, DEFAULT CURRENT_TIMESTAMP | 생성일시 |
| updated_at | DATETIME | NULL, ON UPDATE CURRENT_TIMESTAMP | 수정일시 |

**인덱스:**
- `idx_user_id` ON `user_id`
- `idx_tab` ON `tab`
- `idx_created_at` ON `created_at`
- `idx_tab_created_at` ON `tab`, `created_at` (복합 인덱스)

---

### 6. community_comments (커뮤니티 댓글 테이블)
커뮤니티 게시글의 댓글을 저장하는 테이블입니다.

| 컬럼명 | 타입 | 제약조건 | 설명 |
|--------|------|----------|------|
| id | BIGINT UNSIGNED | PRIMARY KEY, AUTO_INCREMENT | 댓글 고유 ID |
| post_id | BIGINT UNSIGNED | FOREIGN KEY (community_posts.id), NOT NULL | 게시글 ID |
| user_id | BIGINT UNSIGNED | FOREIGN KEY (users.id), NOT NULL | 작성자 ID |
| author | VARCHAR(100) | NOT NULL | 작성자명 (캐시용) |
| tab | ENUM('free', 'recruit', 'collab') | NOT NULL | 게시판 탭 |
| content | TEXT | NOT NULL | 댓글 내용 |
| created_at | DATETIME | NOT NULL, DEFAULT CURRENT_TIMESTAMP | 생성일시 |
| updated_at | DATETIME | NULL, ON UPDATE CURRENT_TIMESTAMP | 수정일시 |

**인덱스:**
- `idx_post_id` ON `post_id`
- `idx_user_id` ON `user_id`
- `idx_created_at` ON `created_at`

---

### 7. favorites (찜하기 테이블)
사용자가 찜한 공연을 저장하는 테이블입니다.

| 컬럼명 | 타입 | 제약조건 | 설명 |
|--------|------|----------|------|
| id | BIGINT UNSIGNED | PRIMARY KEY, AUTO_INCREMENT | 찜하기 고유 ID |
| user_id | BIGINT UNSIGNED | FOREIGN KEY (users.id), NOT NULL | 사용자 ID |
| performance_id | BIGINT UNSIGNED | FOREIGN KEY (performances.id), NOT NULL | 공연 ID |
| created_at | DATETIME | NOT NULL, DEFAULT CURRENT_TIMESTAMP | 생성일시 |

**인덱스:**
- `idx_user_id` ON `user_id`
- `idx_performance_id` ON `performance_id`
- `UNIQUE KEY` ON `user_id`, `performance_id` (중복 방지)

---

### 8. user_genres (사용자 관심 장르 테이블)
관람자의 관심 장르를 저장하는 테이블입니다. (다대다 관계)

| 컬럼명 | 타입 | 제약조건 | 설명 |
|--------|------|----------|------|
| id | BIGINT UNSIGNED | PRIMARY KEY, AUTO_INCREMENT | 고유 ID |
| user_id | BIGINT UNSIGNED | FOREIGN KEY (users.id), NOT NULL | 사용자 ID |
| genre | VARCHAR(50) | NOT NULL | 장르 코드 (acoustic, rock, jazz 등) |
| created_at | DATETIME | NOT NULL, DEFAULT CURRENT_TIMESTAMP | 생성일시 |

**인덱스:**
- `idx_user_id` ON `user_id`
- `UNIQUE KEY` ON `user_id`, `genre` (중복 방지)

---

### 9. user_time_slots (사용자 선호 시간대 테이블)
관람자의 선호 시간대를 저장하는 테이블입니다. (다대다 관계)

| 컬럼명 | 타입 | 제약조건 | 설명 |
|--------|------|----------|------|
| id | BIGINT UNSIGNED | PRIMARY KEY, AUTO_INCREMENT | 고유 ID |
| user_id | BIGINT UNSIGNED | FOREIGN KEY (users.id), NOT NULL | 사용자 ID |
| time_slot | VARCHAR(50) | NOT NULL | 시간대 코드 (weekday_day, weekday_evening 등) |
| created_at | DATETIME | NOT NULL, DEFAULT CURRENT_TIMESTAMP | 생성일시 |

**인덱스:**
- `idx_user_id` ON `user_id`
- `UNIQUE KEY` ON `user_id`, `time_slot` (중복 방지)

---

### 10. busker_genres (버스커 공연 장르 테이블)
버스커의 공연 장르를 저장하는 테이블입니다. (다대다 관계)

| 컬럼명 | 타입 | 제약조건 | 설명 |
|--------|------|----------|------|
| id | BIGINT UNSIGNED | PRIMARY KEY, AUTO_INCREMENT | 고유 ID |
| busker_id | BIGINT UNSIGNED | FOREIGN KEY (buskers.id), NOT NULL | 버스커 ID |
| genre | VARCHAR(50) | NOT NULL | 장르 코드 (acoustic, rock, jazz 등) |
| created_at | DATETIME | NOT NULL, DEFAULT CURRENT_TIMESTAMP | 생성일시 |

**인덱스:**
- `idx_busker_id` ON `busker_id`
- `UNIQUE KEY` ON `busker_id`, `genre` (중복 방지)

---

## 외래키 관계

```
users (id)
  ├── buskers (user_id) - 1:1
  ├── bookings (created_by) - 1:N
  ├── community_posts (user_id) - 1:N
  ├── community_comments (user_id) - 1:N
  ├── favorites (user_id) - 1:N
  ├── user_genres (user_id) - 1:N
  └── user_time_slots (user_id) - 1:N

buskers (id)
  ├── performances (busker_id) - 1:N
  ├── bookings (busker_id) - 1:N
  └── busker_genres (busker_id) - 1:N

performances (id)
  └── favorites (performance_id) - 1:N

community_posts (id)
  └── community_comments (post_id) - 1:N
```

---

## 데이터 타입 상세 설명

### ENUM 타입 값

**users.user_type:**
- `viewer`: 관람자
- `artist`: 버스커

**performances.status:**
- `예정`: 예정된 공연
- `진행중`: 현재 진행 중인 공연
- `종료`: 종료된 공연
- `취소`: 취소된 공연

**bookings.status:**
- `대기중`: 예약 대기 중
- `승인됨`: 예약 승인됨
- `거절됨`: 예약 거절됨
- `완료됨`: 공연 완료됨
- `취소됨`: 예약 취소됨

**community_posts.tab:**
- `free`: 자유게시판
- `recruit`: 팀원모집
- `collab`: 함께공연

**장르 코드 (genre):**
- `acoustic`: 어쿠스틱
- `rock`: 록
- `jazz`: 재즈
- `hiphop`: 힙합
- `dance`: 댄스
- `magic`: 마술
- `performance`: 퍼포먼스
- `classic`: 클래식
- `pop`: 팝
- `rnb`: R&B
- `electronic`: 일렉트로닉
- `other`: 기타

**시간대 코드 (time_slot):**
- `weekday_day`: 주중 낮
- `weekday_evening`: 주중 저녁
- `weekend_day`: 주말 낮
- `weekend_evening`: 주말 저녁

---

## SQL 생성 스크립트 예시

```sql
-- 데이터베이스 생성
CREATE DATABASE IF NOT EXISTS buskinggo 
CHARACTER SET utf8mb4 
COLLATE utf8mb4_unicode_ci;

USE buskinggo;

-- 1. users 테이블
CREATE TABLE users (
    id BIGINT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
    email VARCHAR(255) UNIQUE NOT NULL,
    password VARCHAR(255) NOT NULL,
    name VARCHAR(100) NOT NULL,
    user_type ENUM('viewer', 'artist') NOT NULL,
    phone VARCHAR(20) NULL,
    interested_location VARCHAR(50) NULL,
    email_notification BOOLEAN DEFAULT FALSE,
    sms_notification BOOLEAN DEFAULT FALSE,
    last_login_at DATETIME NULL,
    created_at DATETIME NOT NULL DEFAULT CURRENT_TIMESTAMP,
    updated_at DATETIME NULL ON UPDATE CURRENT_TIMESTAMP,
    INDEX idx_email (email),
    INDEX idx_user_type (user_type),
    INDEX idx_created_at (created_at)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- 2. buskers 테이블
CREATE TABLE buskers (
    id BIGINT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
    user_id BIGINT UNSIGNED UNIQUE NOT NULL,
    team_name VARCHAR(100) NOT NULL,
    team_size INT UNSIGNED DEFAULT 1,
    equipment TEXT NULL,
    contact_phone VARCHAR(20) NOT NULL,
    bio TEXT NULL,
    available_days JSON NULL,
    preferred_time VARCHAR(50) NULL,
    activity_location VARCHAR(50) NOT NULL,
    rating DECIMAL(3,2) DEFAULT 0.00,
    performance_count INT UNSIGNED DEFAULT 0,
    created_at DATETIME NOT NULL DEFAULT CURRENT_TIMESTAMP,
    updated_at DATETIME NULL ON UPDATE CURRENT_TIMESTAMP,
    FOREIGN KEY (user_id) REFERENCES users(id) ON DELETE CASCADE,
    INDEX idx_user_id (user_id),
    INDEX idx_activity_location (activity_location),
    INDEX idx_rating (rating)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- 3. performances 테이블
CREATE TABLE performances (
    id BIGINT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
    busker_id BIGINT UNSIGNED NOT NULL,
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
    created_at DATETIME NOT NULL DEFAULT CURRENT_TIMESTAMP,
    updated_at DATETIME NULL ON UPDATE CURRENT_TIMESTAMP,
    FOREIGN KEY (busker_id) REFERENCES buskers(id) ON DELETE CASCADE,
    INDEX idx_busker_id (busker_id),
    INDEX idx_location (location),
    INDEX idx_status (status),
    INDEX idx_performance_date (performance_date),
    INDEX idx_lat_lng (lat, lng)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- 4. bookings 테이블
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
    created_by BIGINT UNSIGNED NOT NULL,
    created_at DATETIME NOT NULL DEFAULT CURRENT_TIMESTAMP,
    updated_at DATETIME NULL ON UPDATE CURRENT_TIMESTAMP,
    FOREIGN KEY (busker_id) REFERENCES buskers(id) ON DELETE SET NULL,
    FOREIGN KEY (created_by) REFERENCES users(id) ON DELETE CASCADE,
    INDEX idx_busker_id (busker_id),
    INDEX idx_created_by (created_by),
    INDEX idx_status (status),
    INDEX idx_booking_date (booking_date)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- 5. community_posts 테이블
CREATE TABLE community_posts (
    id BIGINT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
    user_id BIGINT UNSIGNED NOT NULL,
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
    updated_at DATETIME NULL ON UPDATE CURRENT_TIMESTAMP,
    FOREIGN KEY (user_id) REFERENCES users(id) ON DELETE CASCADE,
    INDEX idx_user_id (user_id),
    INDEX idx_tab (tab),
    INDEX idx_created_at (created_at),
    INDEX idx_tab_created_at (tab, created_at)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- 6. community_comments 테이블
CREATE TABLE community_comments (
    id BIGINT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
    post_id BIGINT UNSIGNED NOT NULL,
    user_id BIGINT UNSIGNED NOT NULL,
    author VARCHAR(100) NOT NULL,
    tab ENUM('free', 'recruit', 'collab') NOT NULL,
    content TEXT NOT NULL,
    created_at DATETIME NOT NULL DEFAULT CURRENT_TIMESTAMP,
    updated_at DATETIME NULL ON UPDATE CURRENT_TIMESTAMP,
    FOREIGN KEY (post_id) REFERENCES community_posts(id) ON DELETE CASCADE,
    FOREIGN KEY (user_id) REFERENCES users(id) ON DELETE CASCADE,
    INDEX idx_post_id (post_id),
    INDEX idx_user_id (user_id),
    INDEX idx_created_at (created_at)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- 7. favorites 테이블
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

-- 8. user_genres 테이블
CREATE TABLE user_genres (
    id BIGINT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
    user_id BIGINT UNSIGNED NOT NULL,
    genre VARCHAR(50) NOT NULL,
    created_at DATETIME NOT NULL DEFAULT CURRENT_TIMESTAMP,
    FOREIGN KEY (user_id) REFERENCES users(id) ON DELETE CASCADE,
    INDEX idx_user_id (user_id),
    UNIQUE KEY uk_user_genre (user_id, genre)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- 9. user_time_slots 테이블
CREATE TABLE user_time_slots (
    id BIGINT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
    user_id BIGINT UNSIGNED NOT NULL,
    time_slot VARCHAR(50) NOT NULL,
    created_at DATETIME NOT NULL DEFAULT CURRENT_TIMESTAMP,
    FOREIGN KEY (user_id) REFERENCES users(id) ON DELETE CASCADE,
    INDEX idx_user_id (user_id),
    UNIQUE KEY uk_user_time_slot (user_id, time_slot)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- 10. busker_genres 테이블
CREATE TABLE busker_genres (
    id BIGINT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
    busker_id BIGINT UNSIGNED NOT NULL,
    genre VARCHAR(50) NOT NULL,
    created_at DATETIME NOT NULL DEFAULT CURRENT_TIMESTAMP,
    FOREIGN KEY (busker_id) REFERENCES buskers(id) ON DELETE CASCADE,
    INDEX idx_busker_id (busker_id),
    UNIQUE KEY uk_busker_genre (busker_id, genre)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
```

---

## 주요 쿼리 예시

### 사용자 로그인
```sql
SELECT id, email, name, user_type, phone, interested_location, 
       email_notification, sms_notification, last_login_at, created_at
FROM users 
WHERE email = ? AND password = ?;
```

### 버스커 목록 조회 (지역 필터링)
```sql
SELECT b.*, u.email, u.name as user_name
FROM buskers b
INNER JOIN users u ON b.user_id = u.id
WHERE b.activity_location LIKE ?
ORDER BY b.rating DESC, b.performance_count DESC;
```

### 공연 목록 조회 (지역 및 상태 필터링)
```sql
SELECT p.*, b.team_name, b.rating
FROM performances p
INNER JOIN buskers b ON p.busker_id = b.id
WHERE p.location LIKE ? AND p.status = ?
ORDER BY p.performance_date ASC, p.start_time ASC;
```

### 찜한 공연 목록 조회
```sql
SELECT p.*, b.team_name
FROM favorites f
INNER JOIN performances p ON f.performance_id = p.id
INNER JOIN buskers b ON p.busker_id = b.id
WHERE f.user_id = ?
ORDER BY f.created_at DESC;
```

### 커뮤니티 게시글 목록 조회 (탭별)
```sql
SELECT cp.*, u.name as author_name
FROM community_posts cp
INNER JOIN users u ON cp.user_id = u.id
WHERE cp.tab = ?
ORDER BY cp.created_at DESC
LIMIT ? OFFSET ?;
```

### 예약 목록 조회 (버스커별)
```sql
SELECT bk.*, u.name as organizer_user_name, b.team_name
FROM bookings bk
LEFT JOIN users u ON bk.created_by = u.id
LEFT JOIN buskers b ON bk.busker_id = b.id
WHERE bk.busker_id = ? AND bk.status = ?
ORDER BY bk.booking_date ASC, bk.start_time ASC;
```

---

## 주의사항

1. **비밀번호 저장**: `password` 컬럼에는 해시된 비밀번호만 저장해야 합니다. PHP의 `password_hash()` 함수 사용 권장.

2. **JSON 데이터**: `buskers.available_days`는 JSON 타입으로 저장되며, PHP에서는 `json_encode()`/`json_decode()`로 처리합니다.

3. **소프트 삭제**: 필요시 `deleted_at` 컬럼을 추가하여 소프트 삭제를 구현할 수 있습니다.

4. **성능 최적화**: 
   - 자주 조회되는 컬럼에 인덱스 추가
   - `performances` 테이블의 지리적 검색을 위해 공간 인덱스(Spatial Index) 고려
   - 대용량 데이터의 경우 파티셔닝 고려

5. **데이터 무결성**: 
   - 외래키 제약조건으로 데이터 무결성 보장
   - 트랜잭션 사용 권장 (예: 예약 생성 시 여러 테이블 동시 업데이트)

6. **보안**: 
   - SQL 인젝션 방지를 위해 Prepared Statement 사용 필수
   - 민감한 정보(비밀번호 등)는 절대 평문으로 저장하지 않음
