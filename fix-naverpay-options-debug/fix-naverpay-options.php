<?php
/**
 * Plugin Name: Naver Pay YITH WAPO Options Fix (Debug Version)
 * Description: 네이버페이 결제 시 YITH WAPO 옵션 전달 (디버깅 버전)
 * Version: 1.2-debug
 * Author: Claude
 */

if (!defined('ABSPATH')) {
    exit;
}

if (!class_exists('NaverPay_YITH_WAPO_Fix_Debug')) {

    class NaverPay_YITH_WAPO_Fix_Debug {

        private static $instance = null;
        private $debug_log = array();

        public static function get_instance() {
            if (null === self::$instance) {
                self::$instance = new self();
            }
            return self::$instance;
        }

        private function __construct() {
            // 디버그 로그 활성화
            add_action('wp_footer', array($this, 'show_debug_log'), 999);

            // 네이버페이 버튼 생성 시점에 hook
            add_filter('mshop_npay_button_data', array($this, 'modify_button_data'), 10, 2);
            add_filter('mshop_npay_order_data', array($this, 'add_wapo_to_order'), 10, 2);

            // AJAX 요청 가로채기
            add_action('wp_ajax_mshop-npay-create_order', array($this, 'intercept_create_order'), 1);
            add_action('wp_ajax_nopriv_mshop-npay-create_order', array($this, 'intercept_create_order'), 1);

            // 장바구니 hook
            add_action('woocommerce_add_to_cart', array($this, 'log_add_to_cart'), 10, 6);

            // 프론트엔드 스크립트
            add_action('wp_footer', array($this, 'inject_debug_scripts'), 100);
        }

        /**
         * 디버그 로그 추가
         */
        private function log($message, $data = null) {
            $this->debug_log[] = array(
                'time' => current_time('H:i:s'),
                'message' => $message,
                'data' => $data
            );

            // PHP error_log에도 기록
            $log_message = '[NaverPay WAPO Debug] ' . $message;
            if ($data !== null) {
                $log_message .= ' | Data: ' . print_r($data, true);
            }
            error_log($log_message);
        }

        /**
         * 네이버페이 버튼 데이터 수정
         */
        public function modify_button_data($button_data, $product_id) {
            $this->log('Button Data Hook Called', array(
                'product_id' => $product_id,
                'button_data' => $button_data,
                'POST' => $_POST
            ));

            return $button_data;
        }

        /**
         * 네이버페이 주문 데이터에 옵션 추가
         */
        public function add_wapo_to_order($order_data, $product_id) {
            $this->log('Order Data Hook Called', array(
                'product_id' => $product_id,
                'order_data' => $order_data,
                'POST_keys' => array_keys($_POST),
                'has_yith_wapo' => isset($_POST['yith_wapo'])
            ));

            // POST 데이터 전체 로그
            if (isset($_POST['yith_wapo'])) {
                $this->log('YITH WAPO Data Found', $_POST['yith_wapo']);

                $wapo_options = $_POST['yith_wapo'];
                $option_texts = array();

                // 옵션 처리
                foreach ($wapo_options as $addon_id => $values) {
                    $this->log('Processing Addon', array(
                        'addon_id' => $addon_id,
                        'values' => $values
                    ));

                    if (is_array($values)) {
                        foreach ($values as $key => $value) {
                            // Select 박스의 경우 선택된 텍스트 가져오기
                            $label = $this->get_option_label($addon_id, $value);
                            if ($label) {
                                $option_texts[] = $label;
                                $this->log('Option Label Found', $label);
                            }
                        }
                    }
                }

                // 옵션을 상품명에 추가
                if (!empty($option_texts)) {
                    $option_string = implode(', ', $option_texts);

                    if (isset($order_data['productName'])) {
                        $original_name = $order_data['productName'];
                        $order_data['productName'] .= ' [옵션: ' . $option_string . ']';

                        $this->log('Product Name Modified', array(
                            'original' => $original_name,
                            'modified' => $order_data['productName']
                        ));
                    }

                    $order_data['productOption'] = $option_string;
                }
            } else {
                $this->log('No YITH WAPO data in POST');
            }

            return $order_data;
        }

        /**
         * 옵션 레이블 가져오기
         */
        private function get_option_label($addon_id, $option_value) {
            global $wpdb;

            // 데이터베이스에서 옵션 정보 조회
            $option = $wpdb->get_row($wpdb->prepare(
                "SELECT label FROM {$wpdb->prefix}yith_wapo_options
                WHERE addon_id = %d AND id = %d",
                intval($addon_id),
                intval($option_value)
            ));

            if ($option && !empty($option->label)) {
                return sanitize_text_field($option->label);
            }

            return '';
        }

        /**
         * AJAX 요청 가로채기
         */
        public function intercept_create_order() {
            $this->log('AJAX Create Order Called', array(
                'POST' => $_POST,
                'REQUEST' => $_REQUEST,
                'php_input' => file_get_contents('php://input')
            ));
        }

        /**
         * 장바구니 추가 로그
         */
        public function log_add_to_cart($cart_item_key, $product_id, $quantity, $variation_id, $variation, $cart_item_data) {
            $this->log('Add to Cart Called', array(
                'product_id' => $product_id,
                'cart_item_data' => $cart_item_data,
                'POST' => $_POST
            ));
        }

        /**
         * 프론트엔드 디버그 스크립트
         */
        public function inject_debug_scripts() {
            if (!is_product()) {
                return;
            }
            ?>
            <script type="text/javascript">
            (function($) {
                'use strict';

                console.log('[NaverPay WAPO Debug] Script loaded');

                // 모든 form submit 감지
                $('form.cart').on('submit', function(e) {
                    console.log('[NaverPay WAPO Debug] Form submit detected');
                    console.log('Form data:', $(this).serialize());
                });

                // 네이버페이 버튼 클릭 감지
                $(document).on('click', '[id^="NPAY_BUY_LINK_ID"], [id^="NPAY_WISH_LINK_ID"]', function(e) {
                    console.log('[NaverPay WAPO Debug] Naver Pay button clicked');

                    // YITH WAPO 옵션 확인
                    var wapoData = {};
                    $('[id^="yith-wapo-"]').each(function() {
                        var id = $(this).attr('id');
                        var value = $(this).val();
                        var text = $(this).find('option:selected').text();
                        wapoData[id] = {
                            value: value,
                            text: text
                        };
                        console.log('[NaverPay WAPO Debug] Option found:', id, value, text);
                    });

                    console.log('[NaverPay WAPO Debug] All WAPO data:', wapoData);

                    // 옵션 미선택 시 경고
                    var hasSelection = false;
                    $.each(wapoData, function(key, data) {
                        if (data.value && data.value !== 'default') {
                            hasSelection = true;
                        }
                    });

                    if (!hasSelection && Object.keys(wapoData).length > 0) {
                        e.preventDefault();
                        alert('옵션을 선택해주세요.');
                        return false;
                    }

                    // 폼 데이터 확인
                    var formData = $('form.cart').serialize();
                    console.log('[NaverPay WAPO Debug] Form serialize:', formData);
                });

                // AJAX 요청 감시
                $(document).ajaxSend(function(event, jqxhr, settings) {
                    if (settings.url.indexOf('mshop-npay-create_order') !== -1) {
                        console.log('[NaverPay WAPO Debug] AJAX Request to Naver Pay');
                        console.log('URL:', settings.url);
                        console.log('Data:', settings.data);
                    }
                });

            })(jQuery);
            </script>
            <?php
        }

        /**
         * 디버그 로그 출력
         */
        public function show_debug_log() {
            if (!is_product() || empty($this->debug_log)) {
                return;
            }
            ?>
            <div id="naverpay-wapo-debug" style="position: fixed; bottom: 10px; right: 10px; background: #f0f0f0; border: 2px solid #333; padding: 10px; max-width: 400px; max-height: 400px; overflow: auto; z-index: 99999; font-size: 11px;">
                <h4 style="margin: 0 0 10px 0;">NaverPay WAPO Debug Log</h4>
                <button onclick="this.parentElement.style.display='none'" style="position: absolute; top: 5px; right: 5px;">X</button>
                <?php foreach ($this->debug_log as $log): ?>
                    <div style="border-bottom: 1px solid #ccc; padding: 5px 0;">
                        <strong>[<?php echo $log['time']; ?>]</strong> <?php echo esc_html($log['message']); ?>
                        <?php if ($log['data'] !== null): ?>
                            <pre style="background: #fff; padding: 5px; font-size: 10px; overflow: auto;"><?php print_r($log['data']); ?></pre>
                        <?php endif; ?>
                    </div>
                <?php endforeach; ?>
            </div>
            <?php
        }
    }

    // 플러그인 초기화
    NaverPay_YITH_WAPO_Fix_Debug::get_instance();
}
