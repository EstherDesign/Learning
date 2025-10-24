# 🔍 네이버페이 옵션 & URL 문제 진단 가이드 (v1.4)

## 목적

이 가이드는 두 가지 문제를 진단하기 위한 것입니다:

1. **옵션 미전달 문제**: 상품 페이지에서 선택한 YITH WAPO 옵션이 네이버페이 결제 화면에 표시되지 않는 문제
2. **URL 손상 문제**: 네이버페이 결제 화면에서 상품 섬네일 클릭 시 페이지가 깨지는 문제

---

## 📦 설치 방법

### 1단계: 기존 플러그인 비활성화

WordPress 관리자 > 플러그인 메뉴에서:
- `Naver Pay YITH WAPO Options Fix` (v1.1 또는 다른 버전) 비활성화
- ⚠️ 삭제하지 마세요! 비활성화만 하세요

### 2단계: v1.4 디버그 버전 설치

1. `fix-naverpay-options-v1.4-enhanced-debug.zip` 업로드
2. 플러그인 활성화
3. 플러그인 이름: `Naver Pay YITH WAPO Options Fix v1.4 Enhanced Debug`

---

## 🧪 테스트 절차

### 준비 사항

1. **브라우저 개발자 도구 열기**
   - Chrome/Edge: F12 키 누르기
   - Console 탭 열어두기

2. **WordPress 디버그 모드 활성화** (선택사항)
   - `wp-config.php` 파일에 추가:
   ```php
   define('WP_DEBUG', true);
   define('WP_DEBUG_LOG', true);
   define('WP_DEBUG_DISPLAY', false);
   ```

---

### 테스트 A: 옵션 전달 확인

#### A-1. 상품 페이지 접속
```
https://dstone.co.kr/shop/halftoy_forest_dioramaset/
```

#### A-2. 옵션 선택
- "라이온" 또는 다른 옵션 선택
- 브라우저 콘솔에서 다음 메시지 확인:
  ```
  [NaverPay Debug v1.4] Enhanced debug panel loaded
  ```

#### A-3. 네이버페이 구매 버튼 클릭
- "Npay 구매" 버튼 클릭
- 브라우저 콘솔 메시지 확인:
  ```
  [NaverPay Debug v1.4] Naver Pay button clicked
  ```

#### A-4. 디버그 패널 확인

화면 우측 상단에 **녹색 테두리 디버그 패널**이 표시됩니다:

##### 체크포인트 1: Original Order Data
```
📦 Original Order Data:
```
- `productName` 필드 확인
- `productUrl` 또는 유사 URL 필드 확인
- 스크린샷 찍기

##### 체크포인트 2: YITH WAPO POST Data
```
🎯 YITH WAPO POST Data:
```
- 선택한 옵션 데이터가 있는지 확인
- 예: `Array ( [27] => Array ( [0] => 123 ) )`
- 스크린샷 찍기

##### 체크포인트 3: Product Name Modification
```
✏️ Product Name Modification:
```
- Before: 원래 상품명
- After: 상품명 [옵션: 라이온]
- 옵션이 추가되었는지 확인
- 스크린샷 찍기

##### 체크포인트 4: Final Order Data
```
📮 Final Order Data (sent to Naver):
```
- 네이버로 전송되는 최종 데이터
- `productName`에 옵션이 포함되어 있는지 확인
- 스크린샷 찍기

##### 체크포인트 5: URL Fields Check ⭐ 중요!
```
🔗 URL Fields Check:
```
이 섹션에서 **모든 URL 필드**를 확인하세요:
- `productUrl`
- `productLink`
- `productDetailUrl`
- `url`
- `link`

**정상 URL 예시:**
```
https://dstone.co.kr/shop/halftoy_forest_dioramaset/
```

**비정상 URL 예시 (문제 있음):**
```
https://dstone.co.kr/shop/halftoy_forest_dioramaset/?yith_wapo=...
https://dstone.co.kr/shop/halftoy_forest_dioramaset/[옵션:라이온]
(빈 값)
undefined
```

📸 **이 섹션 스크린샷 필수!**

---

### 테스트 B: 네이버페이 화면에서 URL 확인

#### B-1. 네이버페이 결제 화면 진입
- 네이버 로그인: `id: linktable`, `pw: wndPtnsla12^^`
- 결제 화면 도달 확인
- URL 예시: `https://orders.pay.naver.com/order/checkout/mall/c09dcfb0-4aa9-469b-e3d5-4d42a6b8c94b`

#### B-2. 옵션 표시 확인
네이버페이 결제 화면에서:
- 상품명에 `[옵션: 라이온]` 표시되는지 확인
- ✅ 표시됨: 문제 해결됨
- ❌ 미표시: 디버그 패널 데이터 제공 필요

#### B-3. 섬네일 클릭 테스트
상품 섬네일 또는 상품명 클릭:
```html
<article class="ProductItem_article__e2ngf">
```

**예상 동작:**
- 새 탭 또는 현재 탭에서 상품 페이지 열림
- URL: `https://dstone.co.kr/shop/halftoy_forest_dioramaset/`

**실제 동작 기록:**
- [ ] 정상적으로 상품 페이지 열림
- [ ] 페이지가 깨짐 (하얀 화면, 에러 등)
- [ ] 엉뚱한 페이지로 이동
- [ ] 클릭 반응 없음

**페이지가 깨지는 경우:**
1. 브라우저 개발자 도구 > Console 탭에서 에러 메시지 확인
2. 스크린샷 찍기
3. URL 주소창의 URL 복사

---

## 📊 수집할 데이터

다음 정보를 모두 수집해주세요:

### 1. 디버그 패널 스크린샷 (5장)
- [ ] 📦 Original Order Data
- [ ] 🎯 YITH WAPO POST Data
- [ ] ✏️ Product Name Modification
- [ ] 📮 Final Order Data
- [ ] 🔗 URL Fields Check ⭐ 가장 중요!

### 2. 브라우저 콘솔 로그
- [ ] Console 탭 전체 스크린샷
- [ ] `[NaverPay Debug v1.4]`로 시작하는 모든 메시지 복사

### 3. 네이버페이 화면
- [ ] 네이버페이 결제 화면 스크린샷 (옵션 표시 여부 확인)
- [ ] 섬네일 클릭 후 화면 (정상/에러)

### 4. PHP 에러 로그 (선택사항)
WordPress 디버그 모드를 활성화한 경우:
```
wp-content/debug.log
```
파일에서 `[NaverPay Debug v1.4]`로 시작하는 라인 복사

---

## 🔍 문제 진단 체크리스트

### 옵션 미전달 문제

**증상:** 네이버페이 화면에 옵션이 표시되지 않음

**확인 사항:**

1. **YITH WAPO POST Data 섹션이 비어있음**
   - ➡️ 문제: 옵션 데이터가 POST로 전송되지 않음
   - ➡️ 원인: 프론트엔드 JavaScript 문제

2. **YITH WAPO POST Data는 있으나 Product Name Modification이 없음**
   - ➡️ 문제: 옵션 레이블을 데이터베이스에서 가져오지 못함
   - ➡️ 원인: YITH WAPO 데이터베이스 구조 문제

3. **Product Name Modification은 있으나 Final Order Data에는 없음**
   - ➡️ 문제: 다른 플러그인이 데이터를 덮어씀
   - ➡️ 원인: Hook 우선순위 문제

4. **Final Order Data에는 있으나 네이버페이 화면에는 없음**
   - ➡️ 문제: 네이버페이 플러그인이 데이터를 무시함
   - ➡️ 원인: Mshop 네이버페이 플러그인 설정 문제

---

### URL 손상 문제

**증상:** 네이버페이 화면에서 섬네일 클릭 시 페이지 깨짐

**확인 사항:**

1. **URL Fields Check에 URL이 비어있음**
   - ➡️ 문제: URL이 아예 전송되지 않음
   - ➡️ 원인: Mshop 네이버페이 플러그인 버그

2. **URL에 이상한 파라미터가 포함됨**
   ```
   https://dstone.co.kr/shop/halftoy_forest_dioramaset/?yith_wapo[27][0]=123
   ```
   - ➡️ 문제: GET 파라미터가 URL에 포함됨
   - ➡️ 원인: 네이버페이 플러그인이 현재 URL을 그대로 사용

3. **URL이 인코딩되지 않음**
   ```
   https://dstone.co.kr/shop/halftoy_forest_dioramaset/ [옵션: 라이온]
   ```
   - ➡️ 문제: 한글이나 특수문자가 URL에 포함됨
   - ➡️ 원인: URL 인코딩 누락

4. **Original Order Data와 Final Order Data의 URL이 다름**
   - ➡️ 문제: 중간에 다른 플러그인이 URL을 수정함
   - ➡️ 원인: Hook 충돌

---

## 📤 결과 제공 방법

모든 스크린샷과 로그를 준비한 후:

### 옵션 A: 간단 보고
```
1. 옵션 표시: [예/아니오]
2. 섬네일 클릭: [정상/페이지 깨짐/기타]
3. URL Fields Check 스크린샷 첨부
```

### 옵션 B: 상세 보고
```
1. 디버그 패널 스크린샷 5장 첨부
2. 브라우저 콘솔 로그 복사
3. 네이버페이 화면 스크린샷
4. PHP debug.log 내용 (선택)
```

---

## 🆘 자주 묻는 질문

### Q1: 디버그 패널이 안 보여요
**A:**
1. 플러그인이 활성화되어 있는지 확인
2. 브라우저 캐시 삭제 (Ctrl + Shift + Delete)
3. 페이지 새로고침 (Ctrl + F5)

### Q2: 디버그 패널이 방해돼요
**A:**
- 우측 상단 `X` 버튼 클릭하면 닫힙니다
- 또는 CSS로 숨기기:
  ```javascript
  document.getElementById('naverpay-debug-panel').style.display = 'none';
  ```

### Q3: 콘솔에 에러가 많아요
**A:**
- `[NaverPay Debug v1.4]`로 시작하는 메시지만 찾으세요
- 다른 에러는 무시해도 됩니다

### Q4: 여러 옵션을 동시에 테스트하려면?
**A:**
- 한 번에 한 가지 옵션만 선택하세요
- 각 옵션별로 별도로 테스트하세요

---

## ⚙️ 테스트 완료 후

### 디버그 버전 제거
문제가 해결되면:
1. v1.4 디버그 버전 비활성화
2. 정식 버전 (v1.1 또는 수정된 버전) 활성화

### 성능 최적화
디버그 로그가 성능에 영향을 줄 수 있으므로:
- 운영 환경에서는 디버그 버전 사용하지 마세요
- 테스트 완료 후 즉시 제거하세요

---

## 📞 문제 지속 시

다음 정보와 함께 보고해주세요:

1. ✅ WordPress 버전
2. ✅ WooCommerce 버전
3. ✅ YITH WAPO 버전
4. ✅ Mshop 네이버페이 버전
5. ✅ PHP 버전
6. ✅ 활성화된 모든 플러그인 목록
7. ✅ 위에서 수집한 모든 스크린샷과 로그

---

## 🎯 핵심 요약

이 v1.4 디버그 버전은 **진단 도구**입니다:
- ✅ 옵션이 어디서 손실되는지 추적
- ✅ URL이 어떻게 손상되는지 확인
- ✅ 다른 플러그인과의 충돌 여부 파악

**가장 중요한 것:**
- 🔗 **URL Fields Check** 섹션의 스크린샷
- 📦 **Original Order Data** vs 📮 **Final Order Data** 비교

이 정보로 정확한 원인을 파악하고 최종 수정본을 만들 수 있습니다!
