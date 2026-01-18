<?php
if ( ! defined( 'ABSPATH' ) ) exit;

class PT_Engine {

    public function __construct() {
        add_filter( 'plugin_action_links', [ $this, 'add_action_links' ], 10, 4 );
        add_action( 'admin_enqueue_scripts', [ $this, 'enqueue_assets' ] );
        add_action( 'deactivated_plugin', [ $this, 'cleanup_on_deactivation' ], 10, 1 );

        add_action( 'wp_ajax_pt_test_and_activate', [ $this, 'ajax_test_and_activate' ] );
        add_action( 'wp_ajax_pt_stop_timer', [ $this, 'ajax_stop_timer' ] );
        add_action( 'wp_ajax_pt_deactivate_now', [ $this, 'ajax_deactivate_now' ] );
        add_action( 'wp_ajax_pt_report_crash', [ $this, 'ajax_report_crash' ] );

        add_action( 'init', [ $this, 'check_expired_plugins' ] );
        add_action( 'init', [ $this, 'handle_sandbox_request' ] );
    }

    public function handle_sandbox_request() {
        if ( ! isset( $_GET['pt_sandbox_check'] ) || ! isset( $_GET['pt_plugin'] ) ) {
            return;
        }

        if ( ! isset( $_GET['nonce'] ) || ! wp_verify_nonce( sanitize_text_field( wp_unslash( $_GET['nonce'] ) ), 'pt_scout_check' ) ) {
            return;
        }

        $plugin = sanitize_text_field( wp_unslash( $_GET['pt_plugin'] ) );

        // Force Errors (Required for Dead Drop)
        // phpcs:ignore
        @ini_set( 'display_errors', 1 );
        
        // phpcs:ignore
        @error_reporting( E_ALL );

        register_shutdown_function( function() use ($plugin) {
            $error = error_get_last();
            if ( $error && in_array( $error['type'], [ E_ERROR, E_PARSE, E_COMPILE_ERROR, E_CORE_ERROR ] ) ) {
                $msg = "Fatal Error: " . $error['message'] . "\nFile: " . $error['file'] . "\nLine: " . $error['line'];
                $file = $this->get_crash_file_path( $plugin );
                file_put_contents( $file, $msg );
            }
        });

        add_action( 'shutdown', function() { echo ''; } );

        ob_start(); 
        $result = activate_plugin( $plugin, '', false, false );
        ob_end_clean();

        if ( is_wp_error( $result ) ) {
            $file = $this->get_crash_file_path( $plugin );
            file_put_contents( $file, 'WP Error: ' . $result->get_error_message() );
        }

        deactivate_plugins( $plugin );
        exit;
    }

    public function perform_pre_flight_check( $plugin ) {
        $crash_file = $this->get_crash_file_path( $plugin );
        
        if ( file_exists( $crash_file ) ) {
            wp_delete_file( $crash_file );
        }

        $url = add_query_arg( [
            'pt_sandbox_check' => '1',
            'pt_plugin'        => $plugin, 
            'nonce'            => wp_create_nonce( 'pt_scout_check' ),
            't'                => time()
        ], home_url() );

        $response = wp_remote_get( $url, [ 'timeout' => 5, 'sslverify' => false, 'cookies' => $_COOKIE ] );

        if ( file_exists( $crash_file ) ) {
            $log = file_get_contents( $crash_file );
            wp_delete_file( $crash_file );
            $this->log_error( $plugin, $log );
            return $log;
        }

        if ( is_wp_error( $response ) ) {
            $log = "Error: Timeout/Connection Failed.\nDetails: " . $response->get_error_message();
            $this->log_error( $plugin, $log );
            return $log;
        }

        $body = wp_remote_retrieve_body( $response );
        $is_alive = ( strpos( $body, '' ) !== false );
        if ( $is_alive ) return true;

        $preview = substr( wp_strip_all_tags( $body ), 0, 500 );
        $log = "Error: Sandbox crashed silently.\nOutput: $preview";
        $this->log_error( $plugin, $log );
        return $log;
    }

    private function get_crash_file_path( $plugin ) {
        $upload_dir = wp_upload_dir();
        $dir = $upload_dir['basedir'] . '/pt-logs';
        if ( ! file_exists( $dir ) ) wp_mkdir_p( $dir );
        return $dir . '/crash-' . md5( $plugin ) . '.txt';
    }

    private function log_error( $plugin, $message ) {
        $logs = get_option( 'pt_error_logs', [] );
        array_unshift( $logs, [ 'plugin' => $plugin, 'time' => time(), 'error' => $message ] );
        $logs = array_slice( $logs, 0, 20 );
        update_option( 'pt_error_logs', $logs );
    }

    public function ajax_test_and_activate() {
        check_ajax_referer( 'pt_nonce', 'nonce' );
        if ( ! current_user_can( 'activate_plugins' ) ) wp_send_json_error( 'Forbidden' );
        
        $plugin  = isset( $_POST['plugin'] ) ? sanitize_text_field( wp_unslash( $_POST['plugin'] ) ) : '';
        $minutes = isset( $_POST['minutes'] ) ? intval( $_POST['minutes'] ) : 0;

        if ( empty( $plugin ) ) {
            wp_send_json_error( 'No plugin specified' );
            return;
        }

        $safety = $this->perform_pre_flight_check( $plugin );

        if ( $safety !== true ) {
            wp_send_json_error( $safety );
            return;
        }
        
        activate_plugin( $plugin );
        if ( $minutes > 0 ) {
            $timers = get_option( 'pt_active_timers', [] );
            $timers[ $plugin ] = time() + ( $minutes * 60 );
            update_option( 'pt_active_timers', $timers );
        }
        wp_send_json_success( 'Activated' );
    }

    public function ajax_report_crash() {
        check_ajax_referer( 'pt_nonce', 'nonce' );
        $plugin = isset( $_POST['plugin'] ) ? sanitize_text_field( wp_unslash( $_POST['plugin'] ) ) : 'unknown';
        $text   = isset( $_POST['error_text'] ) ? sanitize_textarea_field( wp_unslash( $_POST['error_text'] ) ) : 'Unknown';
        $this->log_error( $plugin, "Client Report: $text" );
        wp_send_json_success();
    }

    public function ajax_stop_timer() {
        check_ajax_referer( 'pt_nonce', 'nonce' );
        $p = isset( $_POST['plugin'] ) ? sanitize_text_field( wp_unslash( $_POST['plugin'] ) ) : '';
        if ( $p ) {
            $t = get_option('pt_active_timers', []);
            unset($t[$p]);
            update_option('pt_active_timers', $t);
        }
        wp_send_json_success();
    }

    public function ajax_deactivate_now() {
        check_ajax_referer( 'pt_nonce', 'nonce' );
        $p = isset( $_POST['plugin'] ) ? sanitize_text_field( wp_unslash( $_POST['plugin'] ) ) : '';
        if ( $p ) {
            deactivate_plugins($p);
            $t = get_option('pt_active_timers', []);
            unset($t[$p]);
            update_option('pt_active_timers', $t);
        }
        wp_send_json_success();
    }

    public function check_expired_plugins() {
        $t = get_option('pt_active_timers', []);
        if ( empty( $t ) ) return;
        $d = false;
        foreach ( $t as $p => $e ) {
            if ( time() > $e ) {
                deactivate_plugins( $p );
                unset( $t[$p] );
                $d = true;
            }
        }
        if ( $d ) update_option( 'pt_active_timers', $t );
    }

    public function add_action_links( $actions, $plugin_file, $plugin_data, $context ) {
        if ( strpos( $plugin_file, 'plugin-timer.php' ) !== false ) {
            array_unshift( $actions, '<a href="' . admin_url( 'tools.php?page=plugin-timer' ) . '">Settings</a>' );
        }
        if ( is_plugin_active( $plugin_file ) ) {
            $timers = get_option( 'pt_active_timers', [] );
            if ( isset( $timers[ $plugin_file ] ) ) {
                $actions['pt_settings'] = '<a href="' . admin_url( 'tools.php?page=plugin-timer' ) . '" style="color:#00a32a;font-weight:bold;">Timer Active (Settings)</a>';
            }
            return $actions;
        }
        
        $slug = $plugin_file; 
        
        $actions['timed_activate'] = '<a href="#" class="pt-trigger" data-plugin="' . esc_attr( $slug ) . '" style="color:#d63638;font-weight:bold;">Safely Timed Activate</a>';
        return $actions;
    }

    public function enqueue_assets( $hook ) {
        if ( 'plugins.php' === $hook || strpos( $hook, 'plugin-timer' ) !== false ) {
            wp_enqueue_style( 'pt-css', PT_URL . 'assets/style.css', [], PT_VERSION );
            wp_enqueue_script( 'pt-js', PT_URL . 'assets/script.js', ['jquery'], PT_VERSION, true );
            wp_localize_script( 'pt-js', 'pt_vars', [
                'ajax_url' => admin_url( 'admin-ajax.php' ),
                'nonce'    => wp_create_nonce( 'pt_nonce' ),
                'logs_url' => admin_url( 'tools.php?page=plugin-timer&tab=logs' )
            ]);
        }
    }

    public function cleanup_on_deactivation( $plugin ) {
        $t = get_option( 'pt_active_timers', [] );
        if ( isset( $t[$plugin] ) ) {
            unset( $t[$plugin] );
            update_option( 'pt_active_timers', $t );
        }
    }
}