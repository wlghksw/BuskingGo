# 버스킹고 PHP 버전

React에서 PHP로 변환된 버스킹고 애플리케이션입니다.

## 요구사항

- PHP 7.4 이상
- 웹 서버 (Apache, Nginx 등)

## 설치 및 실행

1. 프로젝트를 웹 서버의 문서 루트에 배치합니다.

2. Apache를 사용하는 경우:
   ```bash
   # 프로젝트 디렉토리로 이동
   cd /path/to/Busking
   
   # PHP 내장 서버 실행 (개발용)
   php -S localhost:8000
   ```

3. 브라우저에서 `http://localhost:8000` 접속

## 프로젝트 구조

```
Busking/
├── index.php              # 메인 라우팅 파일
├── config/
│   └── constants.php      # 상수 데이터
├── includes/
│   └── header.php         # 헤더 컴포넌트
├── pages/
│   ├── home.php           # 홈 페이지
│   ├── register.php       # 버스커 등록 페이지
│   ├── booking.php        # 공연 예약 페이지
│   └── community.php      # 커뮤니티 페이지
└── assets/
    └── js/
        └── app.js         # JavaScript 파일
```

## 주요 기능

- 공연 찾기 (지도 및 목록)
- 버스커 등록
- 공연 예약
- 커뮤니티 게시판
- 사용자 유형 선택 (관람자/아티스트)
- 찜하기 기능

## 기술 스택

- PHP (서버사이드)
- Tailwind CSS (CDN)
- Leaflet (지도)
- Lucide Icons (아이콘)

## 세션 관리

사용자 상태는 PHP 세션으로 관리됩니다:
- `$_SESSION['userType']`: 사용자 유형 ('viewer', 'artist', null)
- `$_SESSION['favorites']`: 찜한 공연 ID 배열
- `$_SESSION['selectedLocation']`: 선택된 지역
