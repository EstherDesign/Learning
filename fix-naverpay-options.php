<?php
/**
 * Plugin Name: Naver Pay YITH WAPO Options Fix
 * Description: 네이버페이 결제 시 YITH WAPO 옵션을 전달하도록 수정
 * Version: 1.0
 * Author: Claude
 */

// 직접 접근 방지
if (!defined('ABSPATH')) {
    exit;
}

/**
 * 네이버페이 주문 생성 시 YITH WAPO 옵션 포함
 */
add_filter('mshop_npay_order_data', 'add_yith_wapo_to_naverpay_order', 10, 2);

function add_yith_wapo_to_naverpay_order($order_data, $product_id) {
    // YITH WAPO가 활성화되어 있는지 확인
    if (!function_exists('YITH_WAPO')) {
        return $order_data;
    }

    // POST 데이터에서 YITH WAPO 옵션 가져오기
    if (isset($_POST['yith_wapo']) && is_array($_POST['yith_wapo'])) {
        $wapo_options = $_POST['yith_wapo'];

        // 옵션 정보를 상품명에 추가
        $option_text = array();

        foreach ($wapo_options as $addon_id => $addon_values) {
            // YITH WAPO 데이터베이스에서 addon 정보 가져오기
            global $wpdb;

            foreach ($addon_values as $option_index => $option_value) {
                // Addon 정보 조회
                $addon = $wpdb->get_row($wpdb->prepare(
                    "SELECT * FROM {$wpdb->prefix}yith_wapo_addons WHERE id = %d",
                    $addon_id
                ));

                if ($addon) {
                    // 옵션 정보 조회
                    $options = $wpdb->get_results($wpdb->prepare(
                        "SELECT * FROM {$wpdb->prefix}yith_wapo_options WHERE addon_id = %d",
                        $addon_id
                    ));

                    if (!empty($options) && isset($options[$option_value])) {
                        $selected_option = $options[$option_value];
                        $option_label = !empty($selected_option->label) ? $selected_option->label : '';

                        if ($option_label) {
                            $option_text[] = $option_label;
                        }
                    }
                }
            }
        }

        // 옵션 텍스트를 상품명에 추가
        if (!empty($option_text)) {
            if (isset($order_data['productName'])) {
                $order_data['productName'] .= ' [옵션: ' . implode(', ', $option_text) . ']';
            }

            // 옵션 정보를 별도 필드로도 저장
            $order_data['productOption'] = implode(', ', $option_text);
        }
    }

    return $order_data;
}

/**
 * 네이버페이 JavaScript에 옵션 데이터 추가
 */
add_action('wp_footer', 'inject_wapo_options_to_naverpay_js', 100);

function inject_wapo_options_to_naverpay_js() {
    if (!is_product()) {
        return;
    }

    global $product;
    if (!$product) {
        return;
    }

    ?>
    <script type="text/javascript">
    jQuery(document).ready(function($) {
        console.log('네이버페이 YITH WAPO 옵션 연동 스크립트 로드됨');

        // 네이버페이 버튼 클릭 이벤트 가로채기
        $(document).on('click', '[id^="NPAY_BUY_LINK_ID"], [id^="NPAY_WISH_LINK_ID"]', function(e) {
            console.log('네이버페이 버튼 클릭 감지');

            // YITH WAPO 옵션 수집
            var wapoOptions = {};
            var hasOptions = false;

            $('[id^="yith-wapo-"]').each(function() {
                var $select = $(this);
                var addonId = $select.data('addon-id');
                var selectedValue = $select.val();

                if (selectedValue && selectedValue !== 'default') {
                    var selectedText = $select.find('option:selected').text();
                    wapoOptions[addonId] = {
                        value: selectedValue,
                        text: selectedText
                    };
                    hasOptions = true;
                    console.log('옵션 발견:', addonId, selectedValue, selectedText);
                }
            });

            // 옵션이 선택되지 않았으면 경고
            if (!hasOptions) {
                var $wapoSelect = $('[id^="yith-wapo-"]');
                if ($wapoSelect.length > 0) {
                    e.preventDefault();
                    alert('옵션을 선택해주세요.');
                    $wapoSelect.first().focus();
                    return false;
                }
            }

            // 옵션 데이터를 폼에 추가 (이미 있는 경우)
            // 네이버페이 AJAX 요청에 포함될 수 있도록 처리
            if (hasOptions) {
                console.log('수집된 옵션:', wapoOptions);
                // 세션 스토리지에 임시 저장
                sessionStorage.setItem('naverpay_wapo_options', JSON.stringify(wapoOptions));
            }
        });
    });
    </script>
    <?php
}

/**
 * 네이버페이 주문 생성 AJAX 요청 시 옵션 데이터 포함
 */
add_action('wp_ajax_mshop-npay-create_order', 'intercept_naverpay_create_order', 5);
add_action('wp_ajax_nopriv_mshop-npay-create_order', 'intercept_naverpay_create_order', 5);

function intercept_naverpay_create_order() {
    // YITH WAPO 옵션이 POST 데이터에 있는지 확인
    if (isset($_POST['yith_wapo']) && is_array($_POST['yith_wapo'])) {
        // 옵션 데이터를 세션에 저장하여 나중에 사용
        if (!session_id()) {
            session_start();
        }
        $_SESSION['naverpay_yith_wapo_options'] = $_POST['yith_wapo'];
    }
}

/**
 * WooCommerce 장바구니에 상품 추가 시 YITH WAPO 옵션 포함
 */
add_filter('woocommerce_add_cart_item_data', 'save_wapo_options_to_cart', 10, 3);

function save_wapo_options_to_cart($cart_item_data, $product_id, $variation_id) {
    if (isset($_POST['yith_wapo']) && is_array($_POST['yith_wapo'])) {
        $cart_item_data['yith_wapo_options'] = $_POST['yith_wapo'];
    }

    return $cart_item_data;
}

/**
 * 장바구니 아이템에 옵션 표시
 */
add_filter('woocommerce_get_item_data', 'display_wapo_options_in_cart', 10, 2);

function display_wapo_options_in_cart($item_data, $cart_item) {
    if (isset($cart_item['yith_wapo_options']) && is_array($cart_item['yith_wapo_options'])) {
        global $wpdb;

        foreach ($cart_item['yith_wapo_options'] as $addon_id => $addon_values) {
            foreach ($addon_values as $option_index => $option_value) {
                // Addon 정보 조회
                $addon = $wpdb->get_row($wpdb->prepare(
                    "SELECT * FROM {$wpdb->prefix}yith_wapo_addons WHERE id = %d",
                    $addon_id
                ));

                if ($addon) {
                    $options = $wpdb->get_results($wpdb->prepare(
                        "SELECT * FROM {$wpdb->prefix}yith_wapo_options WHERE addon_id = %d",
                        $addon_id
                    ));

                    if (!empty($options) && isset($options[$option_value])) {
                        $selected_option = $options[$option_value];
                        $option_label = !empty($selected_option->label) ? $selected_option->label : '';

                        if ($option_label) {
                            $item_data[] = array(
                                'key'   => !empty($addon->name) ? $addon->name : '옵션',
                                'value' => $option_label
                            );
                        }
                    }
                }
            }
        }
    }

    return $item_data;
}
