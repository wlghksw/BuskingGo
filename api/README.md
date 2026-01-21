# API 엔드포인트 문서

버스킹고 웹과 모바일 앱에서 공통으로 사용하는 RESTful API입니다.

## 기본 URL

```
http://localhost:8000/api/
```

## 엔드포인트 목록

### 1. 공연 목록 조회

**GET** `/api/performances.php`

**쿼리 파라미터:**
- `location` (선택): 지역 필터 (예: "천안", "서울")
- `status` (선택): 상태 필터 (예: "진행중", "예정")

**응답 예시:**
```json
{
  "success": true,
  "data": [
    {
      "id": 1,
      "buskerName": "어쿠스틱 소울",
      "location": "천안역 광장",
      "lat": 36.8151,
      "lng": 127.1139,
      "startTime": "18:00",
      "endTime": "20:00",
      "status": "진행중",
      "image": "🎸",
      "rating": 4.8,
      "distance": 0.5,
      "description": "감성 넘치는 어쿠스틱 공연"
    }
  ],
  "count": 1
}
```

**사용 예시:**
```javascript
// JavaScript (웹)
fetch('/api/performances.php?location=천안')
    .then(res => res.json())
    .then(data => console.log(data.data));

// React Native (모바일 앱)
const response = await fetch('https://your-domain.com/api/performances.php?location=천안');
const data = await response.json();
```

### 2. 공연 등록 (향후 구현)

**POST** `/api/performances.php`

**요청 본문:**
```json
{
  "buskerName": "새로운 버스커",
  "location": "천안역 광장",
  "lat": 36.8151,
  "lng": 127.1139,
  "startTime": "19:00",
  "endTime": "21:00",
  "description": "공연 설명"
}
```

## 향후 추가 예정

- `/api/buskers.php` - 버스커 등록/조회
- `/api/bookings.php` - 공연 예약
- `/api/community.php` - 커뮤니티 게시글
- `/api/auth.php` - 인증/로그인

## CORS 설정

현재는 개발용으로 모든 도메인에서 접근 가능합니다 (`Access-Control-Allow-Origin: *`).
실제 배포 시에는 특정 도메인만 허용하도록 변경해야 합니다.
