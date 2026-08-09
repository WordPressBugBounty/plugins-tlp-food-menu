<?php
// phpcs:disable WordPress.NamingConventions.PrefixAllGlobals -- Established plugin: public namespace, hook names, functions and theme-overridable template variables must stay unchanged for backward compatibility.
/**
 * WooCommerce Blocks Checkout integration for Food Location.
 *
 * Renders a read-only "Selected Location" card at the top of the Blocks
 * checkout/cart, mirroring the classic-checkout card rendered by
 * `FoodLocation::render_location_form()`. The Store API hook saves the
 * order meta server-side from the `fmp_location_id` cookie, so this
 * integration is purely for display parity with the classic flow.
 *
 * @package RT_FoodMenu
 */

namespace RT\FoodMenu\Blocks;

defined( 'ABSPATH' ) || exit();

use Automattic\WooCommerce\Blocks\Integrations\IntegrationInterface;

/**
 * Checkout Location block integration.
 */
class CheckoutLocation implements IntegrationInterface {

	/**
	 * The name of the integration.
	 *
	 * @return string
	 */
	public function get_name() {
		return 'fmp-checkout-location';
	}

	/**
	 * Runs on initialize.
	 *
	 * @return void
	 */
	public function initialize() {
		$this->register_block_frontend_scripts();
	}

	/**
	 * Register frontend scripts for the checkout block.
	 *
	 * @return void
	 */
	private function register_block_frontend_scripts() {
		$asset_url = TLPFoodMenu()->assets_url();
		// Vanilla DOM-injection script, no React/SlotFill — only wc-settings
		// is needed for `getSetting('fmp-checkout-location_data')`.
		wp_register_script(
			'fmp-checkout-location',
			$asset_url . 'js/checkout-location.min.js',
			[ 'wc-settings' ],
			TLPFoodMenu()->options['version'],
			true
		);
		wp_set_script_translations( 'fmp-checkout-location', 'tlp-food-menu', FOOD_MENU_PLUGIN_DIR_PATH . 'languages' );
	}

	/**
	 * Returns an array of script handles to enqueue in the frontend context.
	 *
	 * @return string[]
	 */
	public function get_script_handles() {
		return [ 'fmp-checkout-location' ];
	}

	/**
	 * Returns an array of script handles to enqueue in the editor context.
	 *
	 * @return string[]
	 */
	public function get_editor_script_handles() {
		return [];
	}

	/**
	 * Data exposed to the React component via `getSetting('fmp-checkout-location_data')`.
	 *
	 * @return array
	 */
	public function get_script_data() {
		$location_id   = ! empty( $_COOKIE['fmp_location_id'] ) ? absint( $_COOKIE['fmp_location_id'] ) : 0;
		$location_name = '';

		if ( $location_id && taxonomy_exists( 'tpl-food-location' ) ) {
			$term = get_term( $location_id, 'tpl-food-location' );
			if ( $term && ! is_wp_error( $term ) ) {
				$location_name = $term->name;
			}
		}

		return [
			'heading'       => apply_filters( 'fm_order_location_checkout_title', __( 'Food Order Location', 'tlp-food-menu' ) ),
			'location_id'   => $location_id,
			'location_name' => $location_name,
		];
	}
}
