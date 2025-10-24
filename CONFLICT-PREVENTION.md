# 🛡️ 충돌 방지 가이드

## 두 가지 버전 비교

### Version 1.0 (기본 버전)
- 파일: `fix-naverpay-options.php`
- 적합한 경우: 플러그인이 적고, 테스트 환경

### Version 1.1 (안전 버전) ⭐ **권장**
- 파일: `fix-naverpay-options-safe.php`
- 적합한 경우: 프로덕션 환경, 플러그인 많음

---

## 🔒 Version 1.1의 안전 장치

### 1. **클래스 캡슐화**
```php
// ✅ 안전 버전
class NaverPay_YITH_WAPO_Fix {
    // 함수명 충돌 방지
}

// ⚠️ 기본 버전
function add_yith_wapo_to_naverpay_order() {
    // 다른 플러그인과 충돌 가능
}
```

### 2. **싱글톤 패턴**
```php
// 중복 로드 방지
private static $instance = null;
public static function get_instance() {
    if (null === self::$instance) {
        self::$instance = new self();
    }
    return self::$instance;
}
```

### 3. **의존성 확인**
```php
// 필수 플러그인 자동 확인
public function check_dependencies() {
    if (!class_exists('WooCommerce')) {
        // 경고 표시
    }
    if (!function_exists('YITH_WAPO')) {
        // 경고 표시
    }
    if (!class_exists('Mshop_Naverpay')) {
        // 경고 표시
    }
}
```

### 4. **에러 처리**
```php
try {
    // 옵션 처리
} catch (Exception $e) {
    error_log('Error: ' . $e->getMessage());
    // 에러 발생해도 사이트 정상 작동
}
```

### 5. **JavaScript 중복 방지**
```javascript
// 이미 로드되었는지 확인
if (window.NaverPayWAPOFix) {
    return; // 중복 로드 방지
}
window.NaverPayWAPOFix = true;
```

### 6. **데이터 검증**
```php
// 모든 입력값 검증
$addon_id = intval($addon_id);
$label = sanitize_text_field($label);
```

---

## 🤔 어떤 버전을 사용해야 하나요?

### ✅ **안전 버전 (v1.1)** 사용 권장 상황:

- ✓ **운영 중인 쇼핑몰** (실제 판매 중)
- ✓ **플러그인이 많이 설치됨** (10개 이상)
- ✓ **안정성이 최우선**
- ✓ **처음 사용**
- ✓ **확신이 없음**

### ⚠️ 기본 버전 (v1.0) 사용 가능 상황:

- 테스트 환경
- 플러그인이 적음 (5개 이하)
- 빠른 테스트 필요

---

## 📦 설치 파일

### 안전 버전 (권장)
```
fix-naverpay-options-safe.zip
```

### 기본 버전
```
fix-naverpay-options.zip
```

---

## 🔍 충돌 발생 시 해결 방법

### 1단계: 플러그인 비활성화 테스트

다른 플러그인들을 하나씩 비활성화하며 테스트:

```
1. WooCommerce 외 모든 플러그인 비활성화
2. YITH WAPO만 활성화
3. 네이버페이 플러그인만 활성화
4. 본 플러그인 활성화
5. 하나씩 다시 활성화하며 테스트
```

### 2단계: 디버그 모드 활성화

`wp-config.php`에 추가:

```php
define('WP_DEBUG', true);
define('WP_DEBUG_LOG', true);
define('WP_DEBUG_DISPLAY', false);
```

에러 로그 확인: `wp-content/debug.log`

### 3단계: 브라우저 콘솔 확인

1. F12 키 누르기
2. Console 탭 열기
3. 에러 메시지 확인
4. `[NaverPay WAPO Fix]`로 시작하는 로그 찾기

---

## ⚡ 성능 최적화

### 캐싱
옵션 데이터를 캐싱하여 DB 쿼리 줄이기:

```php
// 추후 업데이트 예정
```

### 지연 로딩
필요할 때만 스크립트 로드:

```php
// 이미 적용됨
if (!is_product()) {
    return; // 상품 페이지가 아니면 스크립트 안 로드
}
```

---

## 🆘 문제 해결

### Q: 여전히 옵션이 안 보여요
**A:**

1. 플러그인 활성화 확인
2. 브라우저 캐시 삭제 (Ctrl + Shift + Delete)
3. WordPress 캐시 플러그인 비활성화 후 테스트
4. F12 콘솔에서 JavaScript 에러 확인

### Q: 사이트가 느려졌어요
**A:**

1. WP_DEBUG 비활성화
2. 다른 플러그인 캐싱 확인
3. 데이터베이스 최적화

### Q: 에러 메시지가 나와요
**A:**

1. `debug.log` 파일 확인
2. 필수 플러그인 활성화 확인:
   - WooCommerce
   - YITH WAPO
   - Mshop 네이버페이

---

## 📞 지원

문제가 계속되면:

1. `wp-content/debug.log` 파일 내용 확인
2. 브라우저 콘솔 스크린샷
3. 설치된 플러그인 목록
4. WordPress/PHP 버전

---

## 🔄 업데이트 내역

### v1.1 (안전 버전)
- ✅ 클래스 캡슐화
- ✅ 싱글톤 패턴
- ✅ 의존성 자동 확인
- ✅ 에러 처리 강화
- ✅ JavaScript 중복 방지
- ✅ 데이터 검증 강화

### v1.0 (기본 버전)
- ✅ 기본 기능
- ✅ 옵션 전달
- ✅ 옵션 검증
