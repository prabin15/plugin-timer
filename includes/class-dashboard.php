<?php
if ( ! defined( 'ABSPATH' ) ) exit;

class PT_Dashboard {

    public function __construct() {
        add_action( 'admin_menu', [ $this, 'register_menu' ] );
        add_action( 'admin_init', [ $this, 'handle_clear_logs' ] );
    }

    public function handle_clear_logs() {
        // FIX: Verify Nonce
        if ( isset( $_POST['pt_clear_logs'] ) && current_user_can( 'manage_options' ) ) {
            
            if ( ! isset( $_POST['pt_clear_logs_nonce'] ) || ! wp_verify_nonce( $_POST['pt_clear_logs_nonce'], 'pt_clear_logs_action' ) ) {
                wp_die( 'Security check failed' );
            }

            delete_option( 'pt_error_logs' );
            
            // FIX: Use wp_safe_redirect instead of wp_redirect
            wp_safe_redirect( admin_url( 'tools.php?page=plugin-timer&tab=logs&cleared=1' ) );
            exit;
        }
    }

    public function register_menu() {
        add_management_page(
            'Safe Activation', 
            'Safe Activation', 
            'manage_options', 
            'plugin-timer', 
            [ $this, 'render_page' ] 
        );
    }

    public function render_page() {
        // FIX: Sanitize $_GET input
        $active_tab = isset( $_GET['tab'] ) ? sanitize_text_field( wp_unslash( $_GET['tab'] ) ) : 'dashboard';
        
        ?>
        <div class="wrap">
            <h1>⏱️ Safe Activation & Timer</h1>
            
            <h2 class="nav-tab-wrapper">
                <a href="?page=plugin-timer&tab=dashboard" class="nav-tab <?php echo $active_tab == 'dashboard' ? 'nav-tab-active' : ''; ?>">Active Timers</a>
                <a href="?page=plugin-timer&tab=logs" class="nav-tab <?php echo $active_tab == 'logs' ? 'nav-tab-active' : ''; ?>">⚠️ Error Logs</a>
                <a href="?page=plugin-timer&tab=guide" class="nav-tab <?php echo $active_tab == 'guide' ? 'nav-tab-active' : ''; ?>">User Guide</a>
            </h2>

            <div class="pt-tab-content">
                <?php 
                switch( $active_tab ) {
                    case 'guide': include PT_PATH . 'views/tab-guide.php'; break;
                    case 'logs': include PT_PATH . 'views/tab-logs.php'; break;
                    default: include PT_PATH . 'views/tab-dashboard.php'; break;
                }
                ?>
            </div>
        </div>
        <?php
    }
}