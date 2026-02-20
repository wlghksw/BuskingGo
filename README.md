# 버스킹고 데이터베이스 스키마 

## 필수 테이블 (7개)

### 1. users (사용자 테이블)

| 컬럼명 | 타입 | 제약조건 | 설명 | 코드에서 사용 |
|--------|------|----------|------|--------------|
| id | BIGINT UNSIGNED | PRIMARY KEY, AUTO_INCREMENT | 사용자 고유 ID | register.php, auth.php |
| email | VARCHAR(255) | UNIQUE, NOT NULL | 이메일 (로그인 ID) | register.php, auth.php |
| password | VARCHAR(255) | NOT NULL | 비밀번호 (해시) | register.php, auth.php |
| name | VARCHAR(100) | NOT NULL | 이름/닉네임 | register.php, auth.php |
| user_type | ENUM('viewer', 'artist') | NOT NULL | 사용자 유형 | register.php, auth.php |
| phone | VARCHAR(20) | NULL | 연락처 | register.php |
| interested_location | VARCHAR(50) | NULL | 관심 지역 | register.php, index.php |
| created_at | DATETIME | NOT NULL, DEFAULT CURRENT_TIMESTAMP | 생성일시 | register.php |


### 2. buskers (버스커 테이블)

| 컬럼명 | 타입 | 제약조건 | 설명 | 코드에서 사용 |
|--------|------|----------|------|--------------|
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

---

### 3. performances (공연 테이블)

| 컬럼명 | 타입 | 제약조건 | 설명 | 코드에서 사용 |
|--------|------|----------|------|--------------|
| id | BIGINT UNSIGNED | PRIMARY KEY, AUTO_INCREMENT | 공연 고유 ID | performances.php, index.php |
| busker_id | BIGINT UNSIGNED | FOREIGN KEY (buskers.id), NULL | 버스커 ID | performances.php |
| busker_name | VARCHAR(100) | NOT NULL | 버스커명 | performances.php, index.php, constants.php |
| location | VARCHAR(200) | NOT NULL | 공연 장소 | performances.php, index.php |
| lat | DECIMAL(10, 8) | NULL | 위도 (지도 마커용, 선택) | performances.php, index.php |
| lng | DECIMAL(11, 8) | NULL | 경도 (지도 마커용, 선택) | performances.php, index.php |
| start_time | TIME | NOT NULL | 시작 시간 | performances.php, index.php |
| end_time | TIME | NOT NULL | 종료 시간 | performances.php, index.php |
| status | ENUM('예정', '진행중', '종료', '취소') | DEFAULT '예정' | 공연 상태 | performances.php, index.php |
| image | VARCHAR(255) | NULL | 공연 이미지/이모지 | performances.php, index.php |
| description | TEXT | NULL | 공연 설명 | performances.php, index.php |
| rating | DECIMAL(3,2) | DEFAULT 0.00 | 평점 | index.php, constants.php |
| distance | DECIMAL(5,2) | NULL | 거리 (km) | index.php, constants.php |
| created_at | DATETIME | NOT NULL, DEFAULT CURRENT_TIMESTAMP | 생성일시 | performances.php |

---

### 4. bookings (예약 테이블)

| 컬럼명 | 타입 | 제약조건 | 설명 | 코드에서 사용 |
|--------|------|----------|------|--------------|
| id | BIGINT UNSIGNED | PRIMARY KEY, AUTO_INCREMENT | 예약 고유 ID | bookings.php, index.php |
| organizer_name | VARCHAR(100) | NOT NULL | 주최자명 | bookings.php, index.php |
| organizer_type | VARCHAR(50) | NOT NULL | 주최자 유형 | bookings.php, index.php |
| busker_id | BIGINT UNSIGNED | FOREIGN KEY (buskers.id), NULL | 예약할 버스커 ID | bookings.php, index.php |
| location | VARCHAR(200) | NOT NULL | 공연 장소 | bookings.php, index.php |
| lat | DECIMAL(10, 8) | NULL | 위도 (지도 마커용, 선택) | bookings.php, index.php |
| lng | DECIMAL(11, 8) | NULL | 경도 (지도 마커용, 선택) | bookings.php, index.php |
| date | DATE | NOT NULL | 예약 날짜 | bookings.php, index.php |
| start_time | TIME | NOT NULL | 시작 시간 | bookings.php, index.php |
| end_time | TIME | NOT NULL | 종료 시간 | bookings.php, index.php |
| additional_request | TEXT | NULL | 추가 요청사항 | bookings.php, index.php |
| status | ENUM('대기중', '승인됨', '거절됨', '완료됨', '취소됨') | DEFAULT '대기중' | 예약 상태 | bookings.php, index.php |
| created_by | VARCHAR(20) | NULL | 예약 생성자 유형 ('viewer', 'artist') | bookings.php, index.php |
| created_at | DATETIME | NOT NULL, DEFAULT CURRENT_TIMESTAMP | 생성일시 | bookings.php, index.php |

---

### 5. community_posts (커뮤니티 게시글 테이블)

| 컬럼명 | 타입 | 제약조건 | 설명 | 코드에서 사용 |
|--------|------|----------|------|--------------|
| id | BIGINT UNSIGNED | PRIMARY KEY, AUTO_INCREMENT | 게시글 고유 ID | community.php, index.php |
| user_id | BIGINT UNSIGNED | FOREIGN KEY (users.id), NULL | 작성자 ID | community.php |
| author | VARCHAR(100) | NOT NULL | 작성자명 | community.php, index.php |
| tab | ENUM('free', 'recruit', 'collab') | NOT NULL | 게시판 탭 | community.php, index.php |
| title | VARCHAR(255) | NOT NULL | 제목 | community.php, index.php |
| content | TEXT | NOT NULL | 내용 | community.php, index.php |
| location | VARCHAR(50) | NULL | 지역 (recruit, collab용) | community.php, index.php |
| genre | VARCHAR(50) | NULL | 장르 (recruit용) | community.php, index.php |
| performance_date | DATE | NULL | 공연 날짜 (collab용) | community.php, index.php |
| views | INT UNSIGNED | DEFAULT 0 | 조회수 | community.php, index.php |
| comments | INT UNSIGNED | DEFAULT 0 | 댓글 수 | community.php, index.php |
| date | DATE | NOT NULL | 작성일 | community.php, index.php |

---

### 6. community_comments (커뮤니티 댓글 테이블)

| 컬럼명 | 타입 | 제약조건 | 설명 | 코드에서 사용 |
|--------|------|----------|------|--------------|
| id | BIGINT UNSIGNED | PRIMARY KEY, AUTO_INCREMENT | 댓글 고유 ID | comments.php, index.php |
| post_id | BIGINT UNSIGNED | FOREIGN KEY (community_posts.id), NOT NULL | 게시글 ID | comments.php, index.php |
| user_id | BIGINT UNSIGNED | FOREIGN KEY (users.id), NULL | 작성자 ID | comments.php |
| author | VARCHAR(100) | NOT NULL | 작성자명 | comments.php, index.php |
| tab | ENUM('free', 'recruit', 'collab') | NOT NULL | 게시판 탭 | comments.php, index.php |
| content | TEXT | NOT NULL | 댓글 내용 | comments.php, index.php |
| date | DATETIME | NOT NULL | 작성일시 | comments.php, index.php |

---

### 7. favorites (찜하기 테이블)

| 컬럼명 | 타입 | 제약조건 | 설명 | 코드에서 사용 |
|--------|------|----------|------|--------------|
| id | BIGINT UNSIGNED | PRIMARY KEY, AUTO_INCREMENT | 찜하기 고유 ID | index.php |
| user_id | BIGINT UNSIGNED | FOREIGN KEY (users.id), NULL | 사용자 ID | index.php |
| performance_id | BIGINT UNSIGNED | FOREIGN KEY (performances.id), NOT NULL | 공연 ID | index.php |


