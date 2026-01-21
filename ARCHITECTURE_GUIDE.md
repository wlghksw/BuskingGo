# 웹과 앱 아키텍처 가이드

## 🤔 웹과 앱을 따로 만들어도 되나요?

**답: 네, 가능합니다!** 하지만 더 나은 방법이 있습니다.

## 📊 아키텍처 옵션 비교

### 옵션 1: 웹과 앱 완전 분리 (비추천 ❌)

```
[웹 앱] ──┐
          ├── [각각의 백엔드]
[모바일 앱] ──┘
```

**장점:**
- 완전히 독립적
- 각각 최적화 가능

**단점:**
- 코드 중복 (비즈니스 로직 2번 작성)
- 유지보수 어려움 (버그 수정 2번)
- 개발 시간 2배
- 데이터 일관성 문제 가능

---

### 옵션 2: 공통 API 백엔드 (강력 추천 ✅)

```
[웹 앱] ────┐
            ├── [공통 API 백엔드] ─── [데이터베이스]
[모바일 앱] ────┘
```

**장점:**
- 코드 재사용 (비즈니스 로직 1번만 작성)
- 유지보수 쉬움 (한 곳만 수정)
- 데이터 일관성 보장
- 웹과 앱 동시 개발 가능
- 확장성 좋음 (나중에 다른 클라이언트 추가 쉬움)

**단점:**
- 초기 설계 필요
- API 설계 필요

---

### 옵션 3: 현재 웹을 API로 변환 (현실적 추천 ⭐)

```
[현재 PHP 웹] ──┐
                ├── [PHP API 백엔드] ─── [데이터베이스]
[새 모바일 앱] ──┘
```

**장점:**
- 기존 코드 활용 가능
- 점진적 전환 가능
- PHP 지식 그대로 활용

---

## 🎯 추천 전략: 하이브리드 접근법

### 단계 1: 현재 웹 유지 + API 레이어 추가

현재 PHP 웹은 그대로 두고, API 엔드포인트를 추가:

```
/api/
  ├── performances.php    # 공연 목록 API
  ├── buskers.php        # 버스커 등록 API
  ├── bookings.php       # 예약 API
  └── community.php      # 커뮤니티 API
```

### 단계 2: 웹도 API 사용하도록 점진적 전환

웹의 PHP 코드도 내부적으로 API를 호출하도록 변경 (선택사항)

### 단계 3: 모바일 앱 개발

React Native, Flutter 등으로 모바일 앱 개발 시 같은 API 사용

---

## 💻 구현 예시

### API 엔드포인트 예시

**`api/performances.php`**
```php
<?php
header('Content-Type: application/json');
require_once __DIR__ . '/../config/constants.php';

// GET 요청: 공연 목록 조회
if ($_SERVER['REQUEST_METHOD'] === 'GET') {
    $location = $_GET['location'] ?? '';
    
    $performances = $samplePerformances;
    if ($location) {
        $performances = array_filter($performances, function($p) use ($location) {
            return stripos($p['location'], $location) !== false;
        });
    }
    
    echo json_encode([
        'success' => true,
        'data' => array_values($performances)
    ]);
}

// POST 요청: 새 공연 등록
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $data = json_decode(file_get_contents('php://input'), true);
    // 데이터베이스에 저장 로직
    echo json_encode(['success' => true, 'message' => '공연이 등록되었습니다']);
}
?>
```

### 웹에서 API 사용

**`pages/home.php` (JavaScript로 API 호출)**
```javascript
// 기존 PHP 렌더링 대신 또는 추가로
fetch('/api/performances.php?location=천안')
    .then(res => res.json())
    .then(data => {
        // 공연 목록 업데이트
    });
```

### 모바일 앱에서 API 사용

**React Native 예시**
```javascript
const fetchPerformances = async (location) => {
    const response = await fetch(`https://your-api.com/api/performances.php?location=${location}`);
    const data = await response.json();
    return data.data;
};
```

---

## 🚀 실제 구현 단계

### Phase 1: API 레이어 구축 (1-2주)
1. `api/` 폴더 생성
2. RESTful API 엔드포인트 작성
3. JSON 응답 형식 통일
4. 인증/인가 추가 (JWT 등)

### Phase 2: 데이터베이스 연동 (2-3주)
1. MySQL/PostgreSQL 설정
2. 데이터베이스 스키마 설계
3. PDO 또는 ORM 사용
4. 기존 샘플 데이터를 DB로 마이그레이션

### Phase 3: 모바일 앱 개발 (4-8주)
1. React Native 또는 Flutter 선택
2. API 연동
3. UI/UX 구현
4. 테스트 및 배포

---

## 📝 결론

**질문: 웹 따로 앱 따로 만들어도 되나요?**
- **기술적으로는 가능**하지만
- **비추천**: 코드 중복, 유지보수 어려움
- **추천**: 공통 API 백엔드 사용

**현실적인 접근:**
1. 현재 PHP 웹은 그대로 유지
2. API 레이어 추가 (`api/` 폴더)
3. 모바일 앱은 API 사용
4. 나중에 웹도 API 사용하도록 점진적 전환 (선택사항)

이렇게 하면 **웹과 앱이 같은 데이터를 공유**하면서도 **각각 독립적으로 개발**할 수 있습니다!
