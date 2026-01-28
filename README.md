# 버스킹고 (BuskingGo)
https://busking-go-dev.azurewebsites.net/


내 주변 버스킹 공연을 찾고, 버스커를 등록하고, 공연을 예약할 수 있는 웹 애플리케이션
![2026-01-196 34 14-ezgif com-video-to-gif-converter](https://github.com/user-attachments/assets/9890cb91-5af3-43a6-a84e-5a600c77781a)


## 주요 기능

- 공연 찾기: 내 주변에서 진행 중이거나 예정된 버스킹 공연 검색
- 지도 보기: 실시간 공연 위치를 지도에서 확인
- 버스커 등록: 버스커 프로필 등록 및 관리
- 공연 예약: 행사 주최자가 버스커를 예약하고 매칭
- 커뮤니티: 아티스트들이 정보를 공유하고 소통할 수 있는 게시판
- 사용자 유형 선택: 관람자 또는 아티스트로 로그인
- 찜하기: 관심 있는 공연을 찜 목록에 추가

## 기술 스택

- PHP 8.5 이상 (서버사이드 렌더링)
- Tailwind CSS (CDN)
- Leaflet (지도 라이브러리)
- Lucide Icons (아이콘)

## 요구사항

- PHP 7.4 이상
- 웹 서버 (Apache, Nginx 등) 또는 PHP 내장 서버

## 프로젝트 구조

```
Busking/
├── index.php                    # 메인 라우팅 파일
├── config/
│   └── constants.php            # 상수 데이터 (공연, 커뮤니티 게시글 등)
├── includes/
│   └── header.php               # 공통 헤더 컴포넌트
├── pages/
│   ├── home.php                 # 홈 페이지 (공연 찾기)
│   ├── register.php             # 버스커 등록 페이지
│   └── booking.php              # 공연 예약 페이지
├── board/
│   └── community.php           # 커뮤니티 게시판 페이지
├── assets/
│   ├── js/
│   │   └── app.js              # JavaScript 파일 (모달 등)
│   ├── css/
│   │   └── style.css           # 커스텀 CSS 파일
│   └── images/                 # 이미지 파일 폴더
├── .htaccess                    # Apache 설정 파일
└── README.md                    # 프로젝트 문서
```

## 페이지 설명

### 홈 페이지 (home.php)
- 지도와 공연 목록을 통해 주변 버스킹 공연 검색
- 지역 필터링 기능
- 공연 상세 정보 모달 표시
- 찜하기 기능

### 버스커 등록 페이지 (register.php)
- 버스커 프로필 등록 폼
- 팀 정보, 연락처, 공연 가능 요일 및 시간대 입력

### 공연 예약 페이지 (booking.php)
- 행사 주최자가 버스커 공연을 예약하는 폼
- 주최자 정보, 공연 장소, 날짜 및 시간 입력

### 커뮤니티 페이지 (board/community.php)
- 자유게시판: 일반 정보 공유
- 팀원모집: 팀원 모집 게시글
- 함께공연: 공연 협업 제안 게시글


## 파일 구조 규칙

프론트 작업 시 참고할 작업 규칙:

- 파일 확장자는 .php로 사용
- index.php: 메인 페이지
- assets/js: JavaScript 파일
- assets/css: CSS 파일
- assets/images: 이미지 파일
- board/: 게시판 관련 페이지들
- pages/: 일반 페이지들
