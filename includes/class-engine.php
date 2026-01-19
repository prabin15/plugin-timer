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
        
        // Scout hook
        add_action( 'init', [ $this, 'handle_sandbox_request' ] );
    }

    // --- 1. THE SANDBOX (Background Scout) ---
    public function handle_sandbox_request() {
        if ( ! isset( $_GET['pt_sandbox_check'] ) || ! isset( $_GET['pt_plugin'] ) ) return;
        if ( ! isset( $_GET['nonce'] ) || ! wp_verify_nonce( sanitize_text_field( wp_unslash( $_GET['nonce'] ) ), 'pt_scout_check' ) ) return;

        // Prevent infinite loops
        remove_action( 'init', [ $this, 'handle_sandbox_request' ] );

        // Helper: Load Plugin API if missing
        if ( ! function_exists( 'activate_plugin' ) ) {
            require_once ABSPATH . 'wp-admin/includes/plugin.php';
        }

        // phpcs:ignore WordPress.Security.ValidatedSanitizedInput.InputNotSanitized
        $plugin = sanitize_text_field( urldecode( wp_unslash( $_GET['pt_plugin'] ) ) );

        // phpcs:ignore
        @ini_set( 'display_errors', 1 );
        // phpcs:ignore
        @error_reporting( E_ALL );

        // 1. SNAPSHOT: Capture the "Safe State" of the database before we do anything.
        $original_active_plugins = get_option( 'active_plugins', [] );

        // 2. THE GUARANTEE: Register shutdown to restore the snapshot NO MATTER WHAT.
        register_shutdown_function( function() use ($plugin, $original_active_plugins) {
            
            // A. LOGGING (Did we crash?)
            $error = error_get_last();
            if ( $error && in_array( $error['type'], [ E_ERROR, E_PARSE, E_COMPILE_ERROR, E_CORE_ERROR, E_USER_ERROR ] ) ) {
                $msg = "Fatal Error: " . $error['message'] . "\nFile: " . $error['file'] . "\nLine: " . $error['line'];
                $file = $this->get_crash_file_path( $plugin );
                file_put_contents( $file, $msg );
            }

            // B. RESTORE DATABASE (The Critical Fix)
            // We blindly force the database back to the snapshot.
            // This ensures the bad plugin cannot stay active.
            update_option( 'active_plugins', $original_active_plugins );
        });

        add_action( 'shutdown', function() { echo ''; } );

        // 3. EXECUTE DANGEROUS CODE
        ob_start(); 
        
        // Activate (This updates DB, but our shutdown function will undo it)
        $result = activate_plugin( $plugin, '', false, false );
        
        if ( ! is_wp_error( $result ) ) {
            // Load Admin API to prevent false positives
            if ( ! function_exists( 'add_settings_section' ) ) {
                require_once ABSPATH . 'wp-admin/includes/template.php';
            }
            if ( ! function_exists( 'get_current_screen' ) ) {
                require_once ABSPATH . 'wp-admin/includes/screen.php';
            }
            
            // Trigger the "Delayed Bombs"
            do_action( 'init' );
            do_action( 'admin_init' ); 
        }
        ob_end_clean();

        if ( is_wp_error( $result ) ) {
            $file = $this->get_crash_file_path( $plugin );
            file_put_contents( $file, 'WP Error: ' . $result->get_error_message() );
        }

        // 4. CLEAN EXIT
        // If we reached here, PHP didn't crash. 
        // The shutdown function will still run and reset the DB, which is exactly what we want.
        exit;
    }

    // --- 2. PRE-FLIGHT CHECK ---
    public function perform_pre_flight_check( $plugin ) {
        $crash_file = $this->get_crash_file_path( $plugin );
        if ( file_exists( $crash_file ) ) wp_delete_file( $crash_file );

        $url = add_query_arg( [
            'pt_sandbox_check' => '1',
            'pt_plugin'        => urlencode( $plugin ),
            'nonce'            => wp_create_nonce( 'pt_scout_check' ),
            't'                => time()
        ], home_url() );

        $response = wp_remote_get( $url, [ 'timeout' => 5, 'sslverify' => false, 'cookies' => $_COOKIE ] );

        // CASE 1: Dead Drop Found
        if ( file_exists( $crash_file ) ) {
            $log = file_get_contents( $crash_file );
            wp_delete_file( $crash_file );
            $this->log_error( $plugin, $log );
            return $log;
        }

        // CASE 2: Connection Failed
        if ( is_wp_error( $response ) ) {
            $log = "Error: Timeout/Connection Failed.\nDetails: " . $response->get_error_message();
            $this->log_error( $plugin, $log );
            return $log;
        }

        // CASE 3: Silent Death
        $body = wp_remote_retrieve_body( $response );
        $is_alive = ( strpos( $body, '' ) !== false );
        if ( $is_alive ) return true;

        $preview = substr( wp_strip_all_tags( $body ), 0, 500 );
        $log = "Error: Sandbox crashed silently (Delayed Bomb).\nOutput: $preview";
        $this->log_error( $plugin, $log );
        return $log;
    }

    // --- 3. AJAX HANDLERS ---
    public function ajax_test_and_activate() {
        check_ajax_referer( 'pt_nonce', 'nonce' );
        if ( ! current_user_can( 'activate_plugins' ) ) wp_send_json_error( 'Forbidden' );

        // phpcs:ignore WordPress.Security.ValidatedSanitizedInput.InputNotSanitized
        $plugin = isset($_POST['plugin']) ? sanitize_text_field( urldecode( wp_unslash( $_POST['plugin'] ) ) ) : '';
        $minutes = isset($_POST['minutes']) ? intval( $_POST['minutes'] ) : 0;
        $force   = isset($_POST['force']) && $_POST['force'] === 'true';

        if ( ! $plugin ) wp_send_json_error( 'No plugin specified' );

        // If not forced, run the scout
        if ( ! $force ) {
            $safety = $this->perform_pre_flight_check( $plugin );
            if ( $safety !== true ) {
                wp_send_json_error( $safety ); // Return crash log. PLUGIN IS OFF IN DB.
                return;
            }
        }
        
        // Activate for real
        $result = activate_plugin( $plugin );
        if ( is_wp_error( $result ) ) {
             wp_send_json_error( $result->get_error_message() );
             return;
        }

        if ( $minutes > 0 ) {
            $timers = get_option( 'pt_active_timers', [] );
            $timers[ $plugin ] = time() + ( $minutes * 60 );
            update_option( 'pt_active_timers', $timers );
        }
        
        wp_send_json_success( 'Activated' );
    }

    public function ajax_stop_timer() {
        check_ajax_referer( 'pt_nonce', 'nonce' );
        // phpcs:ignore WordPress.Security.ValidatedSanitizedInput.InputNotSanitized
        $p = isset($_POST['plugin']) ? sanitize_text_field( urldecode( wp_unslash( $_POST['plugin'] ) ) ) : '';
        if ( $p ) {
            $t = get_option('pt_active_timers', []);
            if ( isset( $t[$p] ) ) { unset( $t[$p] ); update_option('pt_active_timers', $t); wp_send_json_success(); }
        }
        wp_send_json_error('Plugin not found');
    }

    public function ajax_deactivate_now() {
        check_ajax_referer( 'pt_nonce', 'nonce' );
        // phpcs:ignore WordPress.Security.ValidatedSanitizedInput.InputNotSanitized
        $p = isset($_POST['plugin']) ? sanitize_text_field( urldecode( wp_unslash( $_POST['plugin'] ) ) ) : '';
        if ( $p ) {
            deactivate_plugins($p);
            $t = get_option('pt_active_timers', []);
            if ( isset( $t[$p] ) ) { unset( $t[$p] ); update_option('pt_active_timers', $t); }
            wp_send_json_success();
        }
        wp_send_json_error('Invalid Plugin');
    }

    // --- UTILS ---
    public function get_crash_file_path( $plugin ) {
        $upload_dir = wp_upload_dir();
        $dir = $upload_dir['basedir'] . '/pt-logs';
        if ( ! file_exists( $dir ) ) wp_mkdir_p( $dir );
        return $dir . '/crash-' . md5( $plugin ) . '.txt';
    }

    public function log_error( $plugin, $message ) {
        $logs = get_option( 'pt_error_logs', [] );
        array_unshift( $logs, [ 'plugin' => $plugin, 'time' => time(), 'error' => $message ] );
        $logs = array_slice( $logs, 0, 20 );
        update_option( 'pt_error_logs', $logs );
    }

    public function check_expired_plugins() {
        $t = get_option('pt_active_timers', []);
        if ( empty( $t ) ) return;
        $d = false;
        foreach ( $t as $p => $e ) {
            if ( time() > $e ) { deactivate_plugins( $p ); unset( $t[$p] ); $d = true; }
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
                $actions['pt_settings'] = '<a href="' . admin_url( 'tools.php?page=plugin-timer' ) . '" style="color:#00a32a;font-weight:bold;">Edit Timer</a>';
            }
            return $actions;
        }
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
    
    public function ajax_report_crash() { wp_send_json_success(); }
    public function cleanup_on_deactivation( $plugin ) {
        $t = get_option( 'pt_active_timers', [] );
        if ( isset( $t[$plugin] ) ) { unset( $t[$plugin] ); update_option( 'pt_active_timers', $t ); }
    }
}