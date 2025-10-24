<?php
/**
 * Plugin Name: Fix Naver Pay Mobile View Issue
 * Description: 네이버페이에서 돌아올 때 모바일 뷰로 표시되는 문제 해결
 * Version: 1.0.0
 * Author: Claude
 */

if (!defined('ABSPATH')) {
    exit;
}

class Fix_NaverPay_Mobile_View {

    private static $instance = null;

    public static function get_instance() {
        if (null === self::$instance) {
            self::$instance = new self();
        }
        return self::$instance;
    }

    private function __construct() {
        // 네이버페이 referrer 감지 시 모바일 뷰 강제 비활성화
        add_action('template_redirect', array($this, 'fix_naverpay_mobile_view'), 1);

        // 추가 JavaScript로 viewport 강제 고정
        add_action('wp_footer', array($this, 'add_viewport_fix_js'), 9999);
    }

    /**
     * 네이버페이에서 돌아올 때 데스크톱 뷰 강제
     */
    public function fix_naverpay_mobile_view() {
        // HTTP Referer 확인
        $referer = isset($_SERVER['HTTP_REFERER']) ? $_SERVER['HTTP_REFERER'] : '';

        // 네이버페이에서 온 경우
        if (strpos($referer, 'pay.naver.com') !== false ||
            strpos($referer, 'orders.pay.naver.com') !== false) {

            // 로그 남기기
            error_log('[NaverPay Mobile Fix] Detected Naver Pay referrer: ' . $referer);

            // 모바일 감지 강제 비활성화
            add_filter('wp_is_mobile', '__return_false', 9999);

            // 테마의 모바일 감지 함수 오버라이드
            add_filter('body_class', array($this, 'remove_mobile_body_class'), 9999);

            // URL 파라미터로 온 경우 제거
            if (isset($_GET['mobile'])) {
                unset($_GET['mobile']);
            }
            if (isset($_GET['from'])) {
                unset($_GET['from']);
            }
        }
    }

    /**
     * body 태그에서 mobile 관련 클래스 제거
     */
    public function remove_mobile_body_class($classes) {
        $referer = isset($_SERVER['HTTP_REFERER']) ? $_SERVER['HTTP_REFERER'] : '';

        if (strpos($referer, 'pay.naver.com') !== false ||
            strpos($referer, 'orders.pay.naver.com') !== false) {

            // 모바일 관련 클래스 제거
            $mobile_classes = array('mobile', 'is-mobile', 'mobile-view', 'responsive');
            foreach ($mobile_classes as $mobile_class) {
                $key = array_search($mobile_class, $classes);
                if ($key !== false) {
                    unset($classes[$key]);
                    error_log('[NaverPay Mobile Fix] Removed body class: ' . $mobile_class);
                }
            }

            // 데스크톱 클래스 추가
            $classes[] = 'desktop-view';
            $classes[] = 'naverpay-return';
        }

        return $classes;
    }

    /**
     * JavaScript로 viewport 강제 고정
     */
    public function add_viewport_fix_js() {
        $referer = isset($_SERVER['HTTP_REFERER']) ? $_SERVER['HTTP_REFERER'] : '';

        if (strpos($referer, 'pay.naver.com') !== false ||
            strpos($referer, 'orders.pay.naver.com') !== false) {
            ?>
            <script type="text/javascript">
            (function() {
                console.log('[NaverPay Mobile Fix] Detected Naver Pay return');

                // viewport 메타 태그 강제 설정
                var viewport = document.querySelector('meta[name="viewport"]');
                if (viewport) {
                    viewport.setAttribute('content', 'width=device-width, initial-scale=1');
                    console.log('[NaverPay Mobile Fix] Viewport reset');
                }

                // html 태그에서 mobile 관련 클래스 제거
                var html = document.documentElement;
                html.classList.remove('mobile', 'is-mobile', 'mobile-view', 'responsive');
                html.classList.add('desktop-view', 'naverpay-return');
                console.log('[NaverPay Mobile Fix] HTML classes updated');

                // body 태그에서 mobile 관련 클래스 제거
                var body = document.body;
                if (body) {
                    body.classList.remove('mobile', 'is-mobile', 'mobile-view', 'responsive');
                    body.classList.add('desktop-view', 'naverpay-return');
                    console.log('[NaverPay Mobile Fix] Body classes updated');
                }

                // 강제로 데스크톱 스타일 적용
                document.body.style.maxWidth = 'none';
                document.body.style.width = 'auto';

                // 네이버페이 관련 파라미터가 URL에 있다면 제거
                if (window.location.search.indexOf('mobile=') !== -1 ||
                    window.location.search.indexOf('from=') !== -1) {
                    console.log('[NaverPay Mobile Fix] Removing URL parameters');
                    var cleanUrl = window.location.pathname;
                    window.history.replaceState({}, document.title, cleanUrl);
                }
            })();
            </script>
            <?php
        }
    }
}

// 플러그인 초기화
Fix_NaverPay_Mobile_View::get_instance();
