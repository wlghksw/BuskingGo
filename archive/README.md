# Archive 폴더

이 폴더에는 더 이상 사용하지 않는 기존 웹 페이지 파일들이 보관되어 있습니다.

## 파일 목록

- `pages/home.php` - 기존 홈 페이지 (현재는 `pages/home-mobile.php` 사용)
- `pages/register.php` - 기존 버스커 등록 페이지 (현재는 `pages/register-mobile.php` 사용)
- `pages/booking.php` - 기존 공연 예약 페이지 (현재는 `pages/booking-mobile.php` 사용)
- `pages/landing.php` - 기존 랜딩 페이지 (현재는 `pages/split.php`에 통합)
- `board/community.php` - 기존 커뮤니티 페이지 (현재는 `board/community-mobile.php` 사용)

## 현재 구조

현재는 `pages/split.php`가 메인 페이지이며, 좌우 분할 레이아웃으로 구성되어 있습니다:
- 왼쪽: 랜딩 페이지 (프로모션)
- 오른쪽: 모바일 앱 UI (모든 기능 포함)

모든 기능은 앱 내에서 동작하며, 별도의 웹 페이지로 이동하지 않습니다.

## 복원 방법

필요시 이 파일들을 원래 위치로 복원할 수 있습니다:
```bash
mv archive/pages/*.php pages/
mv archive/board/*.php board/
```

그리고 `index.php`의 라우팅 코드에서 주석을 해제하면 됩니다.
