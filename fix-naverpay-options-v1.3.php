<?php
/**
 * Plugin Name: Naver Pay YITH WAPO Options Fix v1.3
 * Description: 네이버페이 옵션 전달 (URL 안전 버전)
 * Version: 1.3
 * Author: Claude
 */

if (!defined('ABSPATH')) {
    exit;
}

if (!class_exists('NaverPay_YITH_WAPO_Fix_V13')) {

    class NaverPay_YITH_WAPO_Fix_V13 {

        private static $instance = null;

        public static function get_instance() {
            if (null === self::$instance) {
                self::$instance = new self();
            }
            return self::$instance;
        }

        private function __construct() {
            // 매우 높은 우선순위로 hook (다른 데이터 보존)
            add_filter('mshop_npay_order_data', array($this, 'add_wapo_to_order'), 999, 2);

            // 프론트엔드 스크립트
            add_action('wp_footer', array($this, 'inject_frontend_scripts'), 100);
        }

        /**
         * 네이버페이 주문 데이터에 옵션만 추가 (다른 필드 건드리지 않음)
         */
        public function add_wapo_to_order($order_data, $product_id) {
            // 디버그 로그
            $this->debug_log('Original order_data', $order_data);

            // YITH WAPO 확인
            if (!function_exists('YITH_WAPO') || !isset($_POST['yith_wapo']) || !is_array($_POST['yith_wapo'])) {
                return $order_data;
            }

            try {
                $wapo_options = $_POST['yith_wapo'];
                $option_texts = array();

                // 옵션 레이블 수집
                foreach ($wapo_options as $addon_id => $addon_values) {
                    if (!is_array($addon_values)) {
                        continue;
                    }

                    foreach ($addon_values as $option_index => $option_value) {
                        $label = $this->get_option_label($addon_id, $option_value);
                        if ($label) {
                            $option_texts[] = $label;
                        }
                    }
                }

                // 옵션이 있으면 상품명에만 추가 (다른 필드는 절대 건드리지 않음)
                if (!empty($option_texts)) {
                    $option_string = implode(', ', $option_texts);

                    // 상품명에 옵션 추가
                    if (isset($order_data['productName']) && is_string($order_data['productName'])) {
                        // 기존 상품명 보존하고 옵션만 추가
                        $original_name = $order_data['productName'];
                        $order_data['productName'] = $original_name . ' [옵션: ' . $option_string . ']';

                        $this->debug_log('Product name updated', array(
                            'original' => $original_name,
                            'updated' => $order_data['productName']
                        ));
                    }

                    // 옵션 정보를 별도 필드로 저장 (있다면)
                    if (!isset($order_data['productOption'])) {
                        $order_data['productOption'] = $option_string;
                    }
                }

                $this->debug_log('Modified order_data', $order_data);

            } catch (Exception $e) {
                error_log('[NaverPay WAPO Fix v1.3] Error: ' . $e->getMessage());
                // 에러 발생해도 원본 데이터 반환
            }

            return $order_data;
        }

        /**
         * 옵션 레이블 가져오기
         */
        private function get_option_label($addon_id, $option_value) {
            global $wpdb;

            $addon_id = intval($addon_id);
            $option_value = intval($option_value);

            $option = $wpdb->get_row($wpdb->prepare(
                "SELECT label FROM {$wpdb->prefix}yith_wapo_options
                WHERE addon_id = %d AND id = %d
                LIMIT 1",
                $addon_id,
                $option_value
            ));

            if ($option && !empty($option->label)) {
                return sanitize_text_field($option->label);
            }

            return '';
        }

        /**
         * 디버그 로그
         */
        private function debug_log($message, $data = null) {
            if (defined('WP_DEBUG') && WP_DEBUG) {
                $log_message = '[NaverPay WAPO v1.3] ' . $message;
                if ($data !== null) {
                    $log_message .= ' | ' . print_r($data, true);
                }
                error_log($log_message);
            }
        }

        /**
         * 프론트엔드 스크립트 (옵션 검증만)
         */
        public function inject_frontend_scripts() {
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

                if (window.NaverPayWAPOFixV13) {
                    return;
                }
                window.NaverPayWAPOFixV13 = true;

                $(document).ready(function() {
                    console.log('[NaverPay WAPO v1.3] Loaded');

                    // 네이버페이 버튼 클릭 시 옵션 검증만
                    $(document).on('click', '[id^="NPAY_BUY_LINK_ID"], [id^="NPAY_WISH_LINK_ID"]', function(e) {
                        var $wapoSelect = $('[id^="yith-wapo-"]');

                        if ($wapoSelect.length === 0) {
                            return true;
                        }

                        var hasSelection = false;
                        var selectedOptions = [];

                        $wapoSelect.each(function() {
                            var value = $(this).val();
                            if (value && value !== 'default') {
                                hasSelection = true;
                                selectedOptions.push($(this).find('option:selected').text());
                            }
                        });

                        if (!hasSelection) {
                            e.preventDefault();
                            alert('상품 옵션을 선택해주세요.');
                            $wapoSelect.first().focus();
                            return false;
                        }

                        console.log('[NaverPay WAPO v1.3] Selected options:', selectedOptions);
                    });
                });
            })(jQuery);
            </script>
            <?php
        }
    }

    // 플러그인 초기화
    NaverPay_YITH_WAPO_Fix_V13::get_instance();
}
