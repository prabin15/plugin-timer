<?php
/**
 * Plugin Name:       Plugin Tester and Activation Timer
 * Plugin URI:        https://github.com/YOUR_GITHUB_USERNAME/plugin-timer
 * Description:       Safely activate plugins with Pre-Flight Crash Checks and Automatic Timers.
 * Version:           2.2.0
 * Author:            Your Name
 * License:           GPL v2 or later
 * Text Domain:       plugin-timer
 */

if ( ! defined( 'ABSPATH' ) ) exit;

define( 'PT_VERSION', '2.2.0' );
define( 'PT_URL', plugin_dir_url( __FILE__ ) );
define( 'PT_PATH', plugin_dir_path( __FILE__ ) );

require_once PT_PATH . 'includes/class-engine.php';
require_once PT_PATH . 'includes/class-dashboard.php';

// LOAD UPDATER
if ( is_admin() ) {
    require_once PT_PATH . 'includes/class-github-updater.php';
    // REPLACE 'yourusername' and 'plugin-timer' with your actual info!
    new PT_GitHub_Updater( __FILE__, 'yourusername', 'plugin-timer' );
}

new PT_Engine();
new PT_Dashboard();