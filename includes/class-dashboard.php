<?php
if ( ! defined( 'ABSPATH' ) ) exit;

class PT_Dashboard {

    public function __construct() {
        add_action( 'admin_menu', [ $this, 'register_menu' ] );
        // NEW: Listen for the clear action
        add_action( 'admin_init', [ $this, 'handle_clear_logs' ] );
    }

    public function handle_clear_logs() {
        // Check if button was clicked and user has permission
        if ( isset( $_POST['pt_clear_logs'] ) && current_user_can( 'manage_options' ) ) {
            delete_option( 'pt_error_logs' );
            // Refresh the page to show empty state
            wp_redirect( admin_url( 'tools.php?page=plugin-timer&tab=logs&cleared=1' ) );
            exit;
        }
    }

    public function register_menu() {
        add_management_page(
            'Plugin Tester', 
            'Plugin Tester', 
            'manage_options', 
            'plugin-timer', 
            [ $this, 'render_page' ] 
        );
    }

    public function render_page() {
        $active_tab = isset( $_GET['tab'] ) ? sanitize_text_field( $_GET['tab'] ) : 'dashboard';
        ?>
        <div class="wrap">
            <h1>⏱️ Plugin Tester & Timer</h1>
            
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