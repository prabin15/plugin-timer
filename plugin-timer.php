<?php
/**
 * Plugin Name:       Safe Activation & Timer
 * Plugin URI:        https://github.com/prabin15/plugin-timer
 * Description:       Safely activate plugins with Pre-Flight Crash Checks and Automatic Timers.
 * Version:           2.5.0
 * Author:            Prabin Regmi
 * License:           GPL v2 or later
 * Text Domain:       plugin-timer
 */

if ( ! defined( 'ABSPATH' ) ) exit;

define( 'PT_VERSION', '2.2.0' );
define( 'PT_URL', plugin_dir_url( __FILE__ ) );
define( 'PT_PATH', plugin_dir_path( __FILE__ ) );

require_once PT_PATH . 'includes/class-engine.php';
require_once PT_PATH . 'includes/class-dashboard.php';

new PT_Engine();
new PT_Dashboard();