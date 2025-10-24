<?php
/**
 * Plugin Name: Naver Pay YITH WAPO Options Fix (Safe Version)
 * Description: 네이버페이 결제 시 YITH WAPO 옵션을 전달하도록 수정 (충돌 방지 버전)
 * Version: 1.1
 * Author: Claude
 * Requires at least: 5.8
 * Requires PHP: 7.4
 */

// 직접 접근 방지
if (!defined('ABSPATH')) {
    exit;
}

// 플러그인 클래스로 캡슐화하여 충돌 방지
if (!class_exists('NaverPay_YITH_WAPO_Fix')) {

    class NaverPay_YITH_WAPO_Fix {

        private static $instance = null;

        /**
         * 싱글톤 인스턴스 반환
         */
        public static function get_instance() {
            if (null === self::$instance) {
                self::$instance = new self();
            }
            return self::$instance;
        }

        /**
         * 생성자
         */
        private function __construct() {
            // 필수 플러그인 확인
            add_action('plugins_loaded', array($this, 'check_dependencies'));

            // 훅 등록
            add_filter('mshop_npay_order_data', array($this, 'add_wapo_to_order'), 10, 2);
            add_action('wp_footer', array($this, 'inject_frontend_scripts'), 100);
            add_filter('woocommerce_add_cart_item_data', array($this, 'save_wapo_to_cart'), 10, 3);
            add_filter('woocommerce_get_item_data', array($this, 'display_wapo_in_cart'), 10, 2);
        }

        /**
         * 의존성 확인
         */
        public function check_dependencies() {
            // WooCommerce 확인
            if (!class_exists('WooCommerce')) {
                add_action('admin_notices', array($this, 'woocommerce_missing_notice'));
                return false;
            }

            // YITH WAPO 확인
            if (!function_exists('YITH_WAPO')) {
                add_action('admin_notices', array($this, 'yith_wapo_missing_notice'));
                return false;
            }

            // 네이버페이 플러그인 확인
            if (!class_exists('Mshop_Naverpay')) {
                add_action('admin_notices', array($this, 'naverpay_missing_notice'));
                return false;
            }

            return true;
        }

        /**
         * WooCommerce 미설치 알림
         */
        public function woocommerce_missing_notice() {
            echo '<div class="error"><p><strong>Naver Pay YITH WAPO Fix:</strong> WooCommerce 플러그인이 필요합니다.</p></div>';
        }

        /**
         * YITH WAPO 미설치 알림
         */
        public function yith_wapo_missing_notice() {
            echo '<div class="error"><p><strong>Naver Pay YITH WAPO Fix:</strong> YITH WooCommerce Product Add-Ons 플러그인이 필요합니다.</p></div>';
        }

        /**
         * 네이버페이 미설치 알림
         */
        public function naverpay_missing_notice() {
            echo '<div class="error"><p><strong>Naver Pay YITH WAPO Fix:</strong> Mshop 네이버페이 플러그인이 필요합니다.</p></div>';
        }

        /**
         * 네이버페이 주문 데이터에 WAPO 옵션 추가
         */
        public function add_wapo_to_order($order_data, $product_id) {
            // YITH WAPO 활성화 확인
            if (!function_exists('YITH_WAPO')) {
                return $order_data;
            }

            // POST 데이터 확인
            if (!isset($_POST['yith_wapo']) || !is_array($_POST['yith_wapo'])) {
                return $order_data;
            }

            try {
                $wapo_options = $_POST['yith_wapo'];
                $option_text = $this->get_wapo_option_labels($wapo_options);

                // 옵션이 있으면 상품명에 추가
                if (!empty($option_text)) {
                    if (isset($order_data['productName'])) {
                        $order_data['productName'] .= ' [옵션: ' . implode(', ', $option_text) . ']';
                    }

                    // 별도 필드로 저장
                    $order_data['productOption'] = implode(', ', $option_text);
                }
            } catch (Exception $e) {
                // 에러 로깅 (디버깅용)
                error_log('NaverPay WAPO Fix Error: ' . $e->getMessage());
            }

            return $order_data;
        }

        /**
         * WAPO 옵션 레이블 가져오기 (YITH WAPO API 사용)
         */
        private function get_wapo_option_labels($wapo_options) {
            $option_text = array();

            // YITH WAPO의 공식 API 사용 시도
            if (class_exists('YITH_WAPO_Addon')) {
                foreach ($wapo_options as $addon_id => $addon_values) {
                    $addon = new YITH_WAPO_Addon($addon_id);

                    if ($addon) {
                        foreach ($addon_values as $option_index => $option_value) {
                            $option = $addon->get_option($option_value);
                            if ($option && !empty($option['label'])) {
                                $option_text[] = sanitize_text_field($option['label']);
                            }
                        }
                    }
                }
            } else {
                // API가 없으면 데이터베이스 직접 조회
                $option_text = $this->get_wapo_option_labels_fallback($wapo_options);
            }

            return $option_text;
        }

        /**
         * WAPO 옵션 레이블 가져오기 (Fallback)
         */
        private function get_wapo_option_labels_fallback($wapo_options) {
            global $wpdb;
            $option_text = array();

            foreach ($wapo_options as $addon_id => $addon_values) {
                $addon_id = intval($addon_id);

                // Prepared statement로 안전하게 조회
                $addon = $wpdb->get_row($wpdb->prepare(
                    "SELECT * FROM {$wpdb->prefix}yith_wapo_addons WHERE id = %d",
                    $addon_id
                ));

                if ($addon) {
                    $options = $wpdb->get_results($wpdb->prepare(
                        "SELECT * FROM {$wpdb->prefix}yith_wapo_options WHERE addon_id = %d",
                        $addon_id
                    ));

                    foreach ($addon_values as $option_index => $option_value) {
                        $option_value = intval($option_value);

                        if (!empty($options) && isset($options[$option_value])) {
                            $selected_option = $options[$option_value];
                            $label = !empty($selected_option->label) ? $selected_option->label : '';

                            if ($label) {
                                $option_text[] = sanitize_text_field($label);
                            }
                        }
                    }
                }
            }

            return $option_text;
        }

        /**
         * 프론트엔드 JavaScript 추가
         */
        public function inject_frontend_scripts() {
            // 상품 페이지가 아니면 리턴
            if (!is_product()) {
                return;
            }

            global $product;
            if (!$product) {
                return;
            }
            ?>
            <script type="text/javascript">
            (function($) {
                'use strict';

                // 이미 로드되었는지 확인 (중복 방지)
                if (window.NaverPayWAPOFix) {
                    return;
                }
                window.NaverPayWAPOFix = true;

                $(document).ready(function() {
                    console.log('[NaverPay WAPO Fix] 스크립트 로드됨');

                    // 네이버페이 버튼 클릭 이벤트
                    $(document).on('click', '[id^="NPAY_BUY_LINK_ID"], [id^="NPAY_WISH_LINK_ID"]', function(e) {
                        console.log('[NaverPay WAPO Fix] 네이버페이 버튼 클릭');

                        // YITH WAPO 옵션 확인
                        var $wapoSelect = $('[id^="yith-wapo-"]');

                        if ($wapoSelect.length === 0) {
                            // 옵션이 없으면 통과
                            return true;
                        }

                        var hasSelection = false;

                        $wapoSelect.each(function() {
                            var value = $(this).val();
                            if (value && value !== 'default') {
                                hasSelection = true;
                                console.log('[NaverPay WAPO Fix] 선택된 옵션:', $(this).find('option:selected').text());
                            }
                        });

                        // 옵션이 있는데 선택하지 않았으면 경고
                        if (!hasSelection) {
                            e.preventDefault();
                            e.stopPropagation();
                            alert('상품 옵션을 선택해주세요.');
                            $wapoSelect.first().focus();
                            return false;
                        }
                    });
                });
            })(jQuery);
            </script>
            <?php
        }

        /**
         * 장바구니에 WAPO 옵션 저장
         */
        public function save_wapo_to_cart($cart_item_data, $product_id, $variation_id) {
            if (isset($_POST['yith_wapo']) && is_array($_POST['yith_wapo'])) {
                $cart_item_data['naverpay_wapo_options'] = $_POST['yith_wapo'];
            }
            return $cart_item_data;
        }

        /**
         * 장바구니에 WAPO 옵션 표시
         */
        public function display_wapo_in_cart($item_data, $cart_item) {
            if (isset($cart_item['naverpay_wapo_options']) && is_array($cart_item['naverpay_wapo_options'])) {
                $option_text = $this->get_wapo_option_labels($cart_item['naverpay_wapo_options']);

                foreach ($option_text as $label) {
                    $item_data[] = array(
                        'key'   => '옵션',
                        'value' => $label
                    );
                }
            }
            return $item_data;
        }
    }

    // 플러그인 초기화
    NaverPay_YITH_WAPO_Fix::get_instance();
}
