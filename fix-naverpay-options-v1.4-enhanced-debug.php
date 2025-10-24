<?php
/**
 * Plugin Name: Naver Pay YITH WAPO Options Fix v1.4 Enhanced Debug
 * Description: 네이버페이 URL 문제 진단용 상세 디버깅
 * Version: 1.4-debug
 * Author: Claude
 */

if (!defined('ABSPATH')) {
    exit;
}

if (!class_exists('NaverPay_YITH_WAPO_Fix_V14_Debug')) {

    class NaverPay_YITH_WAPO_Fix_V14_Debug {

        private static $instance = null;
        private $debug_data = array();

        public static function get_instance() {
            if (null === self::$instance) {
                self::$instance = new self();
            }
            return self::$instance;
        }

        private function __construct() {
            // 모든 mshop_npay 관련 필터 후킹
            add_filter('mshop_npay_button_data', array($this, 'log_button_data'), 1, 2);
            add_filter('mshop_npay_order_data', array($this, 'log_and_modify_order_data'), 1, 2);

            // 매우 낮은 우선순위로 최종 데이터 확인
            add_filter('mshop_npay_order_data', array($this, 'log_final_order_data'), 9999, 2);

            // 프론트엔드
            add_action('wp_footer', array($this, 'inject_debug_ui'), 999);
        }

        /**
         * 버튼 데이터 로그
         */
        public function log_button_data($button_data, $product_id) {
            $this->debug_data['button_data'] = array(
                'product_id' => $product_id,
                'data' => $button_data,
                'timestamp' => current_time('mysql')
            );

            error_log('[NaverPay Debug v1.4] Button Data: ' . print_r($button_data, true));

            return $button_data;
        }

        /**
         * 주문 데이터 로그 및 수정 (최초)
         */
        public function log_and_modify_order_data($order_data, $product_id) {
            // 원본 데이터 저장
            $this->debug_data['original_order_data'] = array(
                'product_id' => $product_id,
                'data' => $order_data,
                'timestamp' => current_time('mysql')
            );

            error_log('[NaverPay Debug v1.4] Original Order Data: ' . print_r($order_data, true));

            // YITH WAPO 옵션 처리
            if (isset($_POST['yith_wapo']) && is_array($_POST['yith_wapo'])) {
                $this->debug_data['yith_wapo_post'] = $_POST['yith_wapo'];

                $option_texts = array();

                foreach ($_POST['yith_wapo'] as $addon_id => $addon_values) {
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

                // 옵션을 상품명에만 추가
                if (!empty($option_texts) && isset($order_data['productName'])) {
                    $option_string = implode(', ', $option_texts);
                    $original_name = $order_data['productName'];

                    // ⚠️ 상품명만 수정, 다른 필드는 절대 건드리지 않음!
                    $order_data['productName'] = $original_name . ' [옵션: ' . $option_string . ']';

                    $this->debug_data['option_modification'] = array(
                        'original_name' => $original_name,
                        'modified_name' => $order_data['productName'],
                        'options' => $option_texts
                    );

                    error_log('[NaverPay Debug v1.4] Product name modified: ' . $original_name . ' -> ' . $order_data['productName']);
                }
            }

            // 수정된 데이터 저장
            $this->debug_data['modified_order_data'] = $order_data;

            return $order_data;
        }

        /**
         * 최종 주문 데이터 로그
         */
        public function log_final_order_data($order_data, $product_id) {
            $this->debug_data['final_order_data'] = array(
                'product_id' => $product_id,
                'data' => $order_data,
                'timestamp' => current_time('mysql')
            );

            error_log('[NaverPay Debug v1.4] Final Order Data: ' . print_r($order_data, true));

            // URL 필드 상세 로그
            $url_fields = array('productUrl', 'productLink', 'productDetailUrl', 'url', 'link');
            foreach ($url_fields as $field) {
                if (isset($order_data[$field])) {
                    error_log('[NaverPay Debug v1.4] URL Field "' . $field . '": ' . $order_data[$field]);
                }
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
         * 디버그 UI 표시
         */
        public function inject_debug_ui() {
            if (!is_product()) {
                return;
            }
            ?>
            <style>
            #naverpay-debug-panel {
                position: fixed;
                top: 50px;
                right: 10px;
                width: 450px;
                max-height: 80vh;
                overflow-y: auto;
                background: #1e1e1e;
                color: #00ff00;
                border: 3px solid #00ff00;
                border-radius: 8px;
                padding: 15px;
                font-family: 'Courier New', monospace;
                font-size: 12px;
                z-index: 999999;
                box-shadow: 0 0 20px rgba(0,255,0,0.5);
            }
            #naverpay-debug-panel h3 {
                margin: 0 0 10px 0;
                color: #00ff00;
                font-size: 16px;
                border-bottom: 2px solid #00ff00;
                padding-bottom: 5px;
            }
            #naverpay-debug-panel .section {
                margin-bottom: 15px;
                padding: 10px;
                background: rgba(0,255,0,0.05);
                border-left: 3px solid #00ff00;
            }
            #naverpay-debug-panel .label {
                color: #ffff00;
                font-weight: bold;
            }
            #naverpay-debug-panel .value {
                color: #00ff00;
                word-break: break-all;
            }
            #naverpay-debug-panel .close-btn {
                position: absolute;
                top: 10px;
                right: 10px;
                background: #ff0000;
                color: #fff;
                border: none;
                padding: 5px 10px;
                cursor: pointer;
                border-radius: 3px;
            }
            #naverpay-debug-panel pre {
                background: #000;
                padding: 10px;
                overflow-x: auto;
                margin: 5px 0;
                border: 1px solid #00ff00;
            }
            </style>
            <div id="naverpay-debug-panel">
                <button class="close-btn" onclick="this.parentElement.style.display='none'">X</button>
                <h3>🔍 NaverPay Debug v1.4</h3>

                <?php if (!empty($this->debug_data)): ?>

                    <?php if (isset($this->debug_data['original_order_data'])): ?>
                    <div class="section">
                        <div class="label">📦 Original Order Data:</div>
                        <pre><?php print_r($this->debug_data['original_order_data']['data']); ?></pre>
                    </div>
                    <?php endif; ?>

                    <?php if (isset($this->debug_data['yith_wapo_post'])): ?>
                    <div class="section">
                        <div class="label">🎯 YITH WAPO POST Data:</div>
                        <pre><?php print_r($this->debug_data['yith_wapo_post']); ?></pre>
                    </div>
                    <?php endif; ?>

                    <?php if (isset($this->debug_data['option_modification'])): ?>
                    <div class="section">
                        <div class="label">✏️ Product Name Modification:</div>
                        <div class="value">Before: <?php echo esc_html($this->debug_data['option_modification']['original_name']); ?></div>
                        <div class="value">After: <?php echo esc_html($this->debug_data['option_modification']['modified_name']); ?></div>
                        <div class="value">Options: <?php echo esc_html(implode(', ', $this->debug_data['option_modification']['options'])); ?></div>
                    </div>
                    <?php endif; ?>

                    <?php if (isset($this->debug_data['final_order_data'])): ?>
                    <div class="section">
                        <div class="label">📮 Final Order Data (sent to Naver):</div>
                        <pre><?php print_r($this->debug_data['final_order_data']['data']); ?></pre>
                    </div>

                    <?php
                    // URL 필드 강조 표시
                    $url_fields = array('productUrl', 'productLink', 'productDetailUrl', 'url', 'link');
                    $final_data = $this->debug_data['final_order_data']['data'];
                    ?>
                    <div class="section">
                        <div class="label">🔗 URL Fields Check:</div>
                        <?php foreach ($url_fields as $field): ?>
                            <?php if (isset($final_data[$field])): ?>
                                <div class="value">
                                    <strong><?php echo $field; ?>:</strong>
                                    <br><?php echo esc_html($final_data[$field]); ?>
                                </div>
                            <?php endif; ?>
                        <?php endforeach; ?>
                    </div>
                    <?php endif; ?>

                <?php else: ?>
                    <div class="section">
                        <div class="value">Waiting for Naver Pay button click...</div>
                    </div>
                <?php endif; ?>
            </div>

            <script>
            (function($) {
                console.log('[NaverPay Debug v1.4] Enhanced debug panel loaded');

                $(document).on('click', '[id^="NPAY_BUY_LINK_ID"], [id^="NPAY_WISH_LINK_ID"]', function(e) {
                    console.log('[NaverPay Debug v1.4] Naver Pay button clicked');
                    console.log('Form data:', $('form.cart').serialize());

                    // 옵션 데이터 수집
                    var options = {};
                    $('[id^="yith-wapo-"]').each(function() {
                        options[$(this).attr('id')] = {
                            value: $(this).val(),
                            text: $(this).find('option:selected').text()
                        };
                    });
                    console.log('YITH WAPO Options:', options);
                });
            })(jQuery);
            </script>
            <?php
        }
    }

    // 플러그인 초기화
    NaverPay_YITH_WAPO_Fix_V14_Debug::get_instance();
}
