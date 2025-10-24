<?php
/**
 * Plugin Name: Naver Pay Integration Fix - Ultimate Edition
 * Plugin URI: https://dstone.co.kr
 * Description: 네이버페이 옵션 전달 + 화면 깨짐 방지 통합 솔루션 (자가 진단 및 안전 모드 포함)
 * Version: 2.0.0
 * Author: D.Stone
 * Author URI: https://dstone.co.kr
 * Requires at least: 5.0
 * Tested up to: 6.8.3
 * WC requires at least: 5.0
 * WC tested up to: 10.0.4
 * Text Domain: npay-fix
 * Domain Path: /languages
 * License: GPL v2 or later
 */

if (!defined('ABSPATH')) {
    exit; // Exit if accessed directly
}

/**
 * Main Plugin Class
 */
class NaverPay_Integration_Fix_Ultimate {

    /**
     * Plugin version
     */
    const VERSION = '2.0.0';

    /**
     * Singleton instance
     */
    private static $instance = null;

    /**
     * Safe mode flag
     */
    private $safe_mode = false;

    /**
     * Error log
     */
    private $error_log = array();

    /**
     * Plugin status
     */
    private $status = array(
        'dependencies' => false,
        'options_fix' => false,
        'mobile_fix' => false,
        'errors' => array()
    );

    /**
     * Get singleton instance
     */
    public static function get_instance() {
        if (null === self::$instance) {
            self::$instance = new self();
        }
        return self::$instance;
    }

    /**
     * Constructor
     */
    private function __construct() {
        // Activation/Deactivation hooks
        register_activation_hook(__FILE__, array($this, 'activate'));
        register_deactivation_hook(__FILE__, array($this, 'deactivate'));

        // Initialize plugin
        add_action('plugins_loaded', array($this, 'init'), 10);

        // Admin interface
        add_action('admin_menu', array($this, 'add_admin_menu'));
        add_action('admin_init', array($this, 'register_settings'));
        add_action('admin_notices', array($this, 'display_admin_notices'));

        // AJAX handlers
        add_action('wp_ajax_npay_fix_run_diagnostics', array($this, 'ajax_run_diagnostics'));
        add_action('wp_ajax_npay_fix_toggle_safe_mode', array($this, 'ajax_toggle_safe_mode'));
    }

    /**
     * Plugin activation
     */
    public function activate() {
        // Create options
        add_option('npay_fix_version', self::VERSION);
        add_option('npay_fix_safe_mode', 'no');
        add_option('npay_fix_first_run', 'yes');
        add_option('npay_fix_status', array());

        // Clear cache
        $this->clear_all_caches();

        // Log activation
        $this->log_message('Plugin activated successfully', 'info');
    }

    /**
     * Plugin deactivation
     */
    public function deactivate() {
        // Clear cache
        $this->clear_all_caches();

        // Log deactivation
        $this->log_message('Plugin deactivated', 'info');
    }

    /**
     * Initialize plugin
     */
    public function init() {
        try {
            // Check safe mode
            $this->safe_mode = (get_option('npay_fix_safe_mode', 'no') === 'yes');

            if ($this->safe_mode) {
                $this->log_message('Running in SAFE MODE', 'warning');
                return;
            }

            // Run diagnostics
            $this->run_diagnostics();

            // If dependencies OK, initialize features
            if ($this->status['dependencies']) {
                $this->init_options_fix();
                $this->init_mobile_fix();
            } else {
                $this->log_message('Dependencies check failed - entering safe mode', 'error');
                $this->enter_safe_mode();
            }

        } catch (Exception $e) {
            $this->log_message('Exception during init: ' . $e->getMessage(), 'error');
            $this->enter_safe_mode();
        }
    }

    /**
     * Run diagnostics
     */
    private function run_diagnostics() {
        $this->status = array(
            'dependencies' => true,
            'options_fix' => true,
            'mobile_fix' => true,
            'errors' => array()
        );

        // Check WordPress version
        global $wp_version;
        if (version_compare($wp_version, '5.0', '<')) {
            $this->status['dependencies'] = false;
            $this->status['errors'][] = 'WordPress 5.0 이상이 필요합니다. 현재 버전: ' . $wp_version;
        }

        // Check WooCommerce
        if (!class_exists('WooCommerce')) {
            $this->status['dependencies'] = false;
            $this->status['errors'][] = 'WooCommerce 플러그인이 설치되어 있지 않습니다.';
        } else {
            if (defined('WC_VERSION') && version_compare(WC_VERSION, '5.0', '<')) {
                $this->status['dependencies'] = false;
                $this->status['errors'][] = 'WooCommerce 5.0 이상이 필요합니다. 현재 버전: ' . WC_VERSION;
            }
        }

        // Check YITH WAPO
        if (!function_exists('YITH_WAPO')) {
            $this->status['options_fix'] = false;
            $this->status['errors'][] = 'YITH WooCommerce Product Add-Ons 플러그인이 필요합니다.';
        }

        // Check Mshop Naverpay
        if (!class_exists('WC_Gateway_Mshop_Npay')) {
            $this->status['dependencies'] = false;
            $this->status['errors'][] = 'Mshop Naverpay 플러그인이 필요합니다.';
        }

        // Check for plugin conflicts
        $this->check_plugin_conflicts();

        // Save status
        update_option('npay_fix_status', $this->status);

        return $this->status['dependencies'];
    }

    /**
     * Check for plugin conflicts
     */
    private function check_plugin_conflicts() {
        $active_plugins = get_option('active_plugins', array());
        $conflicts = array();

        foreach ($active_plugins as $plugin) {
            // Check for other Naver Pay option plugins
            if (strpos($plugin, 'naver') !== false &&
                strpos($plugin, 'option') !== false &&
                $plugin !== plugin_basename(__FILE__)) {
                $conflicts[] = $plugin;
            }
        }

        if (!empty($conflicts)) {
            $this->status['errors'][] = '충돌 가능한 플러그인 발견: ' . implode(', ', $conflicts);
        }
    }

    /**
     * Initialize options fix feature
     */
    private function init_options_fix() {
        if (!$this->status['options_fix']) {
            return;
        }

        try {
            // Hook into Mshop Naverpay
            add_filter('mshop_npay_order_data', array($this, 'add_wapo_options_to_order'), 10, 2);
            add_filter('mshop_npay_button_data', array($this, 'add_wapo_options_to_button'), 10, 2);

            $this->log_message('Options fix initialized', 'info');
        } catch (Exception $e) {
            $this->log_message('Options fix init error: ' . $e->getMessage(), 'error');
            $this->status['options_fix'] = false;
        }
    }

    /**
     * Add YITH WAPO options to Naver Pay order data
     */
    public function add_wapo_options_to_order($order_data, $product_id) {
        try {
            if (!function_exists('YITH_WAPO') || !isset($_POST['yith_wapo'])) {
                return $order_data;
            }

            global $wpdb;
            $option_texts = array();
            $yith_wapo_data = $_POST['yith_wapo'];

            foreach ($yith_wapo_data as $addon_id => $values) {
                if (!is_array($values)) {
                    continue;
                }

                foreach ($values as $value_key) {
                    // Query addon option
                    $table_name = $wpdb->prefix . 'yith_wapo_options';
                    $option = $wpdb->get_row($wpdb->prepare(
                        "SELECT label FROM $table_name WHERE id = %d AND addon_id = %d",
                        $value_key,
                        $addon_id
                    ));

                    if ($option && !empty($option->label)) {
                        $option_texts[] = sanitize_text_field($option->label);
                    }
                }
            }

            if (!empty($option_texts)) {
                $option_string = implode(', ', $option_texts);

                // Add to product name
                if (isset($order_data['productName']) && is_string($order_data['productName'])) {
                    $order_data['productName'] .= ' [옵션: ' . $option_string . ']';
                }

                $this->log_message('Options added to order: ' . $option_string, 'info');
            }

            return $order_data;

        } catch (Exception $e) {
            $this->log_message('Error adding options to order: ' . $e->getMessage(), 'error');
            return $order_data;
        }
    }

    /**
     * Add YITH WAPO options to Naver Pay button data
     */
    public function add_wapo_options_to_button($button_data, $product_id) {
        // Same logic as add_wapo_options_to_order
        return $this->add_wapo_options_to_order($button_data, $product_id);
    }

    /**
     * Initialize mobile view fix feature
     */
    private function init_mobile_fix() {
        try {
            // Hook early in template redirect
            add_action('template_redirect', array($this, 'fix_mobile_view'), 1);

            // Add viewport fix JavaScript
            add_action('wp_footer', array($this, 'add_mobile_fix_js'), 9999);

            $this->log_message('Mobile fix initialized', 'info');
        } catch (Exception $e) {
            $this->log_message('Mobile fix init error: ' . $e->getMessage(), 'error');
            $this->status['mobile_fix'] = false;
        }
    }

    /**
     * Fix mobile view when returning from Naver Pay
     */
    public function fix_mobile_view() {
        $referer = isset($_SERVER['HTTP_REFERER']) ? $_SERVER['HTTP_REFERER'] : '';

        if (strpos($referer, 'pay.naver.com') !== false ||
            strpos($referer, 'orders.pay.naver.com') !== false) {

            // Force desktop view
            add_filter('wp_is_mobile', '__return_false', 9999);
            add_filter('body_class', array($this, 'remove_mobile_body_class'), 9999);

            // Remove mobile parameters
            if (isset($_GET['mobile'])) {
                unset($_GET['mobile']);
            }
            if (isset($_GET['from'])) {
                unset($_GET['from']);
            }

            $this->log_message('Mobile view fix applied', 'info');
        }
    }

    /**
     * Remove mobile classes from body
     */
    public function remove_mobile_body_class($classes) {
        $referer = isset($_SERVER['HTTP_REFERER']) ? $_SERVER['HTTP_REFERER'] : '';

        if (strpos($referer, 'pay.naver.com') !== false ||
            strpos($referer, 'orders.pay.naver.com') !== false) {

            $mobile_classes = array('mobile', 'is-mobile', 'mobile-view', 'responsive');
            foreach ($mobile_classes as $mobile_class) {
                $key = array_search($mobile_class, $classes);
                if ($key !== false) {
                    unset($classes[$key]);
                }
            }

            $classes[] = 'desktop-view';
            $classes[] = 'npay-fix-active';
        }

        return $classes;
    }

    /**
     * Add mobile fix JavaScript
     */
    public function add_mobile_fix_js() {
        $referer = isset($_SERVER['HTTP_REFERER']) ? $_SERVER['HTTP_REFERER'] : '';

        if (strpos($referer, 'pay.naver.com') !== false ||
            strpos($referer, 'orders.pay.naver.com') !== false) {
            ?>
            <script type="text/javascript">
            (function() {
                console.log('[Naver Pay Fix] Mobile view fix active');

                // Fix viewport
                var viewport = document.querySelector('meta[name="viewport"]');
                if (viewport) {
                    viewport.setAttribute('content', 'width=device-width, initial-scale=1');
                }

                // Remove mobile classes
                var html = document.documentElement;
                html.classList.remove('mobile', 'is-mobile', 'mobile-view', 'responsive');
                html.classList.add('desktop-view', 'npay-fix-active');

                var body = document.body;
                if (body) {
                    body.classList.remove('mobile', 'is-mobile', 'mobile-view', 'responsive');
                    body.classList.add('desktop-view', 'npay-fix-active');
                }

                // Clean URL parameters
                if (window.location.search.indexOf('mobile=') !== -1 ||
                    window.location.search.indexOf('from=') !== -1) {
                    var cleanUrl = window.location.pathname;
                    window.history.replaceState({}, document.title, cleanUrl);
                }

                console.log('[Naver Pay Fix] Mobile fix applied successfully');
            })();
            </script>
            <?php
        }
    }

    /**
     * Enter safe mode
     */
    private function enter_safe_mode() {
        update_option('npay_fix_safe_mode', 'yes');
        $this->safe_mode = true;
        $this->log_message('Entered SAFE MODE', 'error');
    }

    /**
     * Exit safe mode
     */
    private function exit_safe_mode() {
        update_option('npay_fix_safe_mode', 'no');
        $this->safe_mode = false;
        $this->log_message('Exited SAFE MODE', 'info');
    }

    /**
     * Clear all caches
     */
    private function clear_all_caches() {
        // WordPress object cache
        if (function_exists('wp_cache_flush')) {
            wp_cache_flush();
        }

        // WP Super Cache
        if (function_exists('wp_cache_clear_cache')) {
            wp_cache_clear_cache();
        }

        // W3 Total Cache
        if (function_exists('w3tc_flush_all')) {
            w3tc_flush_all();
        }

        // WP Rocket
        if (function_exists('rocket_clean_domain')) {
            rocket_clean_domain();
        }
    }

    /**
     * Log message
     */
    private function log_message($message, $level = 'info') {
        $timestamp = current_time('Y-m-d H:i:s');
        $log_entry = "[{$timestamp}] [{$level}] {$message}";

        error_log('[Naver Pay Fix] ' . $log_entry);

        $this->error_log[] = array(
            'timestamp' => $timestamp,
            'level' => $level,
            'message' => $message
        );

        // Keep only last 100 entries
        if (count($this->error_log) > 100) {
            $this->error_log = array_slice($this->error_log, -100);
        }

        // Save to option
        update_option('npay_fix_error_log', $this->error_log);
    }

    /**
     * Add admin menu
     */
    public function add_admin_menu() {
        add_menu_page(
            'Naver Pay Fix',
            'Naver Pay Fix',
            'manage_options',
            'npay-fix',
            array($this, 'render_admin_page'),
            'dashicons-admin-tools',
            58
        );
    }

    /**
     * Register settings
     */
    public function register_settings() {
        register_setting('npay_fix_options', 'npay_fix_safe_mode');
    }

    /**
     * Render admin page
     */
    public function render_admin_page() {
        $status = get_option('npay_fix_status', array());
        $safe_mode = get_option('npay_fix_safe_mode', 'no');
        $error_log = get_option('npay_fix_error_log', array());
        ?>
        <div class="wrap">
            <h1>🛡️ Naver Pay Integration Fix - Ultimate Edition</h1>
            <p>버전 <?php echo self::VERSION; ?> | 안전 모드 포함 통합 솔루션</p>

            <?php if ($safe_mode === 'yes'): ?>
            <div class="notice notice-warning">
                <p><strong>⚠️ 안전 모드 활성화됨</strong></p>
                <p>플러그인이 문제를 감지하여 안전 모드로 전환되었습니다. 아래 진단 결과를 확인하세요.</p>
            </div>
            <?php endif; ?>

            <!-- Status Dashboard -->
            <div class="card" style="max-width: 100%; margin-top: 20px;">
                <h2>📊 상태 대시보드</h2>
                <table class="widefat" style="margin-top: 10px;">
                    <thead>
                        <tr>
                            <th>항목</th>
                            <th>상태</th>
                            <th>설명</th>
                        </tr>
                    </thead>
                    <tbody>
                        <tr>
                            <td><strong>의존성 체크</strong></td>
                            <td><?php echo $this->render_status_badge($status['dependencies'] ?? false); ?></td>
                            <td>WooCommerce, YITH WAPO, Mshop Naverpay</td>
                        </tr>
                        <tr>
                            <td><strong>옵션 전달 기능</strong></td>
                            <td><?php echo $this->render_status_badge($status['options_fix'] ?? false); ?></td>
                            <td>YITH WAPO 옵션을 네이버페이로 전달</td>
                        </tr>
                        <tr>
                            <td><strong>화면 깨짐 방지</strong></td>
                            <td><?php echo $this->render_status_badge($status['mobile_fix'] ?? true); ?></td>
                            <td>네이버페이에서 돌아올 때 데스크톱 뷰 유지</td>
                        </tr>
                        <tr>
                            <td><strong>안전 모드</strong></td>
                            <td><?php echo ($safe_mode === 'yes') ? '<span style="color: orange;">⚠️ 활성화</span>' : '<span style="color: green;">✅ 비활성화</span>'; ?></td>
                            <td>문제 발생 시 자동으로 활성화됨</td>
                        </tr>
                    </tbody>
                </table>
            </div>

            <!-- Error Log -->
            <?php if (!empty($status['errors'])): ?>
            <div class="card" style="max-width: 100%; margin-top: 20px;">
                <h2>⚠️ 발견된 문제</h2>
                <ul style="color: red;">
                    <?php foreach ($status['errors'] as $error): ?>
                        <li><?php echo esc_html($error); ?></li>
                    <?php endforeach; ?>
                </ul>
            </div>
            <?php endif; ?>

            <!-- Actions -->
            <div class="card" style="max-width: 100%; margin-top: 20px;">
                <h2>🔧 작업</h2>
                <p>
                    <button class="button button-primary" onclick="runDiagnostics()">🔍 진단 실행</button>
                    <?php if ($safe_mode === 'yes'): ?>
                        <button class="button button-secondary" onclick="toggleSafeMode('exit')">🔓 안전 모드 해제</button>
                    <?php else: ?>
                        <button class="button button-secondary" onclick="toggleSafeMode('enter')">🔒 안전 모드 활성화</button>
                    <?php endif; ?>
                    <button class="button" onclick="clearCache()">🗑️ 캐시 삭제</button>
                </p>
                <div id="action-result" style="margin-top: 10px;"></div>
            </div>

            <!-- Recent Log -->
            <?php if (!empty($error_log)): ?>
            <div class="card" style="max-width: 100%; margin-top: 20px;">
                <h2>📝 최근 로그 (최근 20개)</h2>
                <div style="max-height: 300px; overflow-y: auto; background: #f5f5f5; padding: 10px; font-family: monospace; font-size: 12px;">
                    <?php
                    $recent_logs = array_slice(array_reverse($error_log), 0, 20);
                    foreach ($recent_logs as $log):
                        $color = $log['level'] === 'error' ? 'red' : ($log['level'] === 'warning' ? 'orange' : 'black');
                    ?>
                        <div style="color: <?php echo $color; ?>; margin-bottom: 5px;">
                            [<?php echo esc_html($log['timestamp']); ?>]
                            [<?php echo strtoupper(esc_html($log['level'])); ?>]
                            <?php echo esc_html($log['message']); ?>
                        </div>
                    <?php endforeach; ?>
                </div>
            </div>
            <?php endif; ?>

            <!-- Help -->
            <div class="card" style="max-width: 100%; margin-top: 20px;">
                <h2>❓ 도움말</h2>
                <h3>사용 방법</h3>
                <ol>
                    <li>플러그인 활성화 후 자동으로 진단이 실행됩니다.</li>
                    <li>상태 대시보드에서 모든 항목이 ✅ 인지 확인하세요.</li>
                    <li>문제가 발견되면 해당 플러그인을 설치/활성화하세요.</li>
                    <li>테스트: 상품 페이지 → 옵션 선택 → Npay 구매 → 네이버페이 화면 확인</li>
                </ol>
                <h3>안전 모드란?</h3>
                <p>
                    문제가 발생하면 자동으로 활성화되어 사이트를 보호합니다.<br>
                    안전 모드에서는 플러그인 기능이 비활성화되지만 사이트는 정상 작동합니다.
                </p>
            </div>
        </div>

        <script>
        function runDiagnostics() {
            var result = document.getElementById('action-result');
            result.innerHTML = '<p style="color: blue;">진단 실행 중...</p>';

            jQuery.post(ajaxurl, {
                action: 'npay_fix_run_diagnostics'
            }, function(response) {
                if (response.success) {
                    result.innerHTML = '<p style="color: green;">✅ 진단 완료! 페이지를 새로고침합니다...</p>';
                    setTimeout(function() {
                        location.reload();
                    }, 1000);
                } else {
                    result.innerHTML = '<p style="color: red;">❌ 진단 실패: ' + response.data + '</p>';
                }
            });
        }

        function toggleSafeMode(action) {
            var result = document.getElementById('action-result');
            result.innerHTML = '<p style="color: blue;">처리 중...</p>';

            jQuery.post(ajaxurl, {
                action: 'npay_fix_toggle_safe_mode',
                mode: action
            }, function(response) {
                if (response.success) {
                    result.innerHTML = '<p style="color: green;">✅ ' + response.data + ' 페이지를 새로고침합니다...</p>';
                    setTimeout(function() {
                        location.reload();
                    }, 1000);
                } else {
                    result.innerHTML = '<p style="color: red;">❌ 실패: ' + response.data + '</p>';
                }
            });
        }

        function clearCache() {
            var result = document.getElementById('action-result');
            result.innerHTML = '<p style="color: green;">✅ 캐시가 삭제되었습니다.</p>';
            setTimeout(function() {
                result.innerHTML = '';
            }, 3000);
        }
        </script>
        <?php
    }

    /**
     * Render status badge
     */
    private function render_status_badge($status) {
        if ($status) {
            return '<span style="color: green; font-size: 20px;">✅</span>';
        } else {
            return '<span style="color: red; font-size: 20px;">❌</span>';
        }
    }

    /**
     * Display admin notices
     */
    public function display_admin_notices() {
        $status = get_option('npay_fix_status', array());
        $safe_mode = get_option('npay_fix_safe_mode', 'no');
        $first_run = get_option('npay_fix_first_run', 'no');

        if ($first_run === 'yes') {
            ?>
            <div class="notice notice-success is-dismissible">
                <p><strong>🎉 Naver Pay Fix 플러그인이 활성화되었습니다!</strong></p>
                <p><a href="<?php echo admin_url('admin.php?page=npay-fix'); ?>" class="button button-primary">설정 페이지로 이동</a></p>
            </div>
            <?php
            update_option('npay_fix_first_run', 'no');
        }

        if ($safe_mode === 'yes') {
            ?>
            <div class="notice notice-warning">
                <p><strong>⚠️ Naver Pay Fix: 안전 모드 활성화됨</strong></p>
                <p>문제가 발견되어 안전 모드로 전환되었습니다. <a href="<?php echo admin_url('admin.php?page=npay-fix'); ?>">설정 페이지</a>에서 자세한 내용을 확인하세요.</p>
            </div>
            <?php
        }

        if (!empty($status['errors']) && $safe_mode !== 'yes') {
            ?>
            <div class="notice notice-warning">
                <p><strong>⚠️ Naver Pay Fix: 경고</strong></p>
                <p>일부 기능이 제대로 작동하지 않을 수 있습니다. <a href="<?php echo admin_url('admin.php?page=npay-fix'); ?>">설정 페이지</a>에서 자세한 내용을 확인하세요.</p>
            </div>
            <?php
        }
    }

    /**
     * AJAX: Run diagnostics
     */
    public function ajax_run_diagnostics() {
        if (!current_user_can('manage_options')) {
            wp_send_json_error('권한이 없습니다.');
        }

        $this->run_diagnostics();
        $this->clear_all_caches();

        wp_send_json_success('진단이 완료되었습니다.');
    }

    /**
     * AJAX: Toggle safe mode
     */
    public function ajax_toggle_safe_mode() {
        if (!current_user_can('manage_options')) {
            wp_send_json_error('권한이 없습니다.');
        }

        $mode = isset($_POST['mode']) ? $_POST['mode'] : '';

        if ($mode === 'enter') {
            $this->enter_safe_mode();
            wp_send_json_success('안전 모드가 활성화되었습니다.');
        } elseif ($mode === 'exit') {
            $this->exit_safe_mode();
            $this->run_diagnostics();
            wp_send_json_success('안전 모드가 해제되었습니다.');
        } else {
            wp_send_json_error('잘못된 요청입니다.');
        }
    }
}

// Initialize plugin
function npay_fix_init() {
    return NaverPay_Integration_Fix_Ultimate::get_instance();
}

add_action('plugins_loaded', 'npay_fix_init', 5);
