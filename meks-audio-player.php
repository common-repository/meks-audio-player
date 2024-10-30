<?php
/*
Plugin Name: Meks Audio Player
Description: Easily enhance your podcasts, music or any audio files with a full-featured and customizable sticky audio player.
Version: 1.3
Author: Meks
Author URI: https://mekshq.com/
Text Domain: meks-audio-player
Domain Path: /languages
*/

/* Prevent direct accAP */
if ( !defined( 'DB_NAME' ) ) {
	header( 'HTTP/1.0 403 Forbidden' );
	die;
}

define( 'MEKS_AP_URL', trailingslashit( plugin_dir_url( __FILE__ ) ) );
define( 'MEKS_AP_DIR', trailingslashit( plugin_dir_path( __FILE__ ) ) );
define( 'MEKS_AP_VER', '1.3' );
define( 'MEKS_AP_BASENAME', plugin_basename( __FILE__ ) );

/* Includes */
require_once MEKS_AP_DIR . 'inc/functions.php';
require_once MEKS_AP_DIR . 'inc/class-audio.php';
require_once MEKS_AP_DIR . 'inc/register-blocks.php';


/* Start plugin */
add_action( 'init', 'meks_ap_init' );

function meks_ap_init() {
	$meks_ap = Meks_AP::get_instance();
}