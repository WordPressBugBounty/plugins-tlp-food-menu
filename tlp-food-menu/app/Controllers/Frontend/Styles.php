<?php
// phpcs:disable WordPress.NamingConventions.PrefixAllGlobals -- Established plugin: public namespace, hook names, functions and theme-overridable template variables must stay unchanged for backward compatibility.
/**
 * Dynamic Styles Class.
 *
 * @package RT_FoodMenu
 */

namespace RT\FoodMenu\Controllers\Frontend;

use RT\FoodMenu\Helpers\Fns;

// Do not allow directly accessing this file.
if ( ! defined( 'ABSPATH' ) ) {
	exit( 'This script cannot be accessed directly.' );
}

/**
 * Dynamic Styles Class.
 */
class Styles {
	use \RT\FoodMenu\Traits\SingletonTrait;

	/**
	 * Class Init.
	 *
	 * @return void
	 */
	protected function init() {
		add_action( 'wp_enqueue_scripts', [ $this, 'dynamicStyles' ], 99 );
	}

	/**
	 * Dynamic Styles
	 *
	 * @return void
	 */
	public static function dynamicStyles() {
		$styles = '';

		$settings = get_option( TLPFoodMenu()->options['settings'] );

		// Global primary color from General settings.
		if ( ! empty( $settings['fm_primary_color'] ) ) {
			$hex = Fns::sanitize_hex_color( $settings['fm_primary_color'] );

			// Compute dark variant (10% darker).
			$r        = hexdec( substr( $hex, 1, 2 ) );
			$g        = hexdec( substr( $hex, 3, 2 ) );
			$b        = hexdec( substr( $hex, 5, 2 ) );
			$dark_hex = sprintf( '#%02x%02x%02x', max( 0, (int) ( $r * 0.9 ) ), max( 0, (int) ( $g * 0.9 ) ), max( 0, (int) ( $b * 0.9 ) ) );
			$soft_hex = sprintf( '#%02x%02x%02x', 255 - (int) ( ( 255 - $r ) * 0.03 ), 255 - (int) ( ( 255 - $g ) * 0.03 ), 255 - (int) ( ( 255 - $b ) * 0.03 ) );

			$styles .= 'body { --rtfm-primary-color: ' . $hex . '; --rtfm-primary-rgb: ' . $r . ',' . $g . ',' . $b . '; --rtfm-primary-dark: ' . $dark_hex . '; --rtfm-primary-soft: ' . $soft_hex . '; }';

			// Detail-page tabs use the derived primary-dark as their background sitewide.
			$styles .= '.fmp-wrapper .fmp-tabs > li { background: var(--rtfm-primary-dark); }';
		}

		wp_add_inline_style( 'fm-frontend', str_replace( [ "\r", "\n", "\t" ], '', $styles ) );
	}
}
