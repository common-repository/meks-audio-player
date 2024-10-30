<?php


/**
 * Trim text characters with UTF-8
 * for adding to html attributes it's not breaking the code and
 * you are able to have all the kind of characters (Japanese, Cyrillic, German, French, etc.)
 *
 * @param string  $text
 * @since  1.0
 */
if ( ! function_exists( 'meks_ap_esc_text' ) ) :
	function meks_ap_esc_text( $text ) {
		return rawurlencode( html_entity_decode( wp_kses( $text, null ), ENT_COMPAT, 'UTF-8' ) );
	}
endif;


/**
 * Get all post types
 *
 * Function to get all post types
 *
 * @return array of post types
 * @since  1.1
 */

if ( ! function_exists( 'meks_ap_post_types' ) ) :
	function meks_ap_post_types() {

		$args = array(
			'public' => true,
		);

		$post_types = get_post_types( $args, 'objects' );

		if ( ! empty( $post_types ) ) {

			$exclude = array( 'attachment', 'topic', 'forum', 'guest-author', 'reply' );

			foreach ( $post_types as $key => $post_type ) {
				if ( in_array( $key, $exclude ) ) {
					unset( $post_types[ $key ] );
				}
			}
		}

		$post_types = apply_filters( 'meks_ap_modify_post_types_list', $post_types );

		return $post_types;
	}
endif;


/**
 * Parse args ( merge arrays )
 *
 * Similar to wp_parse_args() but extended to also merge multidimensional arrays
 *
 * @param array   $a - set of values to merge
 * @param array   $b - set of default values
 * @return array Merged set of elements
 * @since  1.0.0
 */

if ( ! function_exists( 'meks_ap_parse_args' ) ) :
	function meks_ap_parse_args( &$a, $b ) {

		$a = (array) $a;
		$b = (array) $b;
		$r = $b;
		foreach ( $a as $k => &$v ) {
			if ( is_array( $v ) && ! isset( $v[0] ) && isset( $r[ $k ] ) ) {
				$r[ $k ] = meks_ap_parse_args( $v, $r[ $k ] );
			} else {
				$r[ $k ] = $v;
			}
		}

		return $r;
	}
endif;


/**
 * Debug (log) function
 *
 * Outputs any content into log file in theme root directory
 *
 * @param mixed   $mixed Content to output
 * @since  1.0
 */

if (!function_exists('meks_ap_log')):
    function meks_ap_log($mixed) {

        if (is_array($mixed)) {
            $mixed = print_r($mixed, 1);
        } else if (is_object($mixed)) {
            ob_start();
            var_dump($mixed);
            $mixed = ob_get_clean();
        }

        $handle = fopen( plugin_dir_path( __FILE__ ) . 'log', 'a');
        fwrite($handle, $mixed . PHP_EOL);
        fclose($handle);
    }
endif;
