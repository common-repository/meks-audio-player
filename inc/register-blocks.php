<?php

/**
 * Enqueue block editor only JavaScript and CSS.
 */

add_action( 'enqueue_block_editor_assets', 'meks_app_register_block_editor_assets' );

function meks_app_register_block_editor_assets() {

	global $pagenow;
	
	if ( $pagenow == 'widgets.php' ) {
		return;
	}

	$editor_js_path = 'assets/js/blocks.editor.js';
	$style_path = 'assets/css/blocks.style.css';

    // modify
	$js_dependencies = [ 'wp-element', 'wp-edit-post', 'wp-i18n', 'wp-components', 'wp-blocks', 'wp-editor' ];

	// Register the bundled block JS file
	wp_enqueue_script(
		'meks-app-editor-js',
		MEKS_AP_URL . $editor_js_path,
		$js_dependencies,
		MEKS_AP_VER,
		true
	);
	
	// Register shared editor and frontend styles
	wp_enqueue_style(
		'meks-app-block-css',
		MEKS_AP_URL . $style_path,
		[],
		MEKS_AP_VER
	);
	
}


/**
 * Enqueue block frontend JavaScript
 */

add_action( "wp_enqueue_scripts", 'meks_app_frontend_assets' );

function meks_app_frontend_assets(){

	$frontend_js_path = "assets/css/blocks.style.css";

	wp_enqueue_style( 
		"meks-app-block-frontend-css",
		MEKS_AP_URL . $frontend_js_path,
		[],
		MEKS_AP_VER
	);
}
