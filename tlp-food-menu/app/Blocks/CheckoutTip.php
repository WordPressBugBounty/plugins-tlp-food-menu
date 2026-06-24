<?php
/**
 * WooCommerce Blocks Checkout integration for Tips.
 *
 * @package RT_FoodMenu
 */

namespace RT\FoodMenu\Blocks;

defined( 'ABSPATH' ) || exit();

use Automattic\WooCommerce\Blocks\Integrations\IntegrationInterface;
use RT\FoodMenu\Helpers\Fns;

/**
 * Checkout Tip block integration.
 */
class CheckoutTip implements IntegrationInterface {

	/**
	 * The name of the integration.
	 *
	 * @return string
	 */
	public function get_name() {
		return 'fmp-checkout-tip';
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
		wp_register_script(
			'fmp-checkout-tip',
			$asset_url . 'js/checkout-tip.min.js',
			[ 'wp-element', 'wp-plugins', 'wc-blocks-checkout', 'wc-settings' ],
			TLPFoodMenu()->options['version'],
			true
		);
		wp_set_script_translations( 'fmp-checkout-tip', 'tlp-food-menu', FOOD_MENU_PLUGIN_DIR_PATH . 'languages' );
	}

	/**
	 * Returns an array of script handles to enqueue in the frontend context.
	 *
	 * @return string[]
	 */
	public function get_script_handles() {
		return [ 'fmp-checkout-tip' ];
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
	 * An array of key, value pairs of data made available to the block on the client side.
	 *
	 * @return array
	 */
	public function get_script_data() {
		$settings      = get_option( TLPFoodMenu()->options['settings'], [] );
		$allow_tip_for = $settings['allow_tip_for'] ?? 'both';

		// Pass current WC session tip state so the React component can
		// initialise with the correct values (e.g. show Remove button).
		$tip_session = WC()->session ? WC()->session->get( 'fmp_tip' ) : null;

		return [
			'show_tip_form'    => $settings['show_tip_form'] ?? 'both',
			'allow_tip_for'    => $allow_tip_for,
			'tip_heading_text' => $settings['tip_heading_text'] ?? esc_html__( 'Do you want to provide a tip?', 'tlp-food-menu' ),
			'ajaxurl'          => esc_url( admin_url( 'admin-ajax.php' ) ),
			'nonce_id'         => esc_attr( Fns::nonceId() ),
			'nonce'            => esc_attr( wp_create_nonce( Fns::nonceText() ) ),
			'tip_session'      => ! empty( $tip_session ) ? [
				'tip_added'             => ! empty( $tip_session['tip_added'] ),
				'tip_selected_type'     => $tip_session['tip_selected_type'] ?? 'fixed',
				'tip_fixed_amount'      => (float) ( $tip_session['tip_fixed_amount'] ?? 0 ),
				'tip_percentage_amount' => (int) ( $tip_session['tip_percentage_amount'] ?? 0 ),
			] : null,
			'i18n'             => [
				'fixed'      => esc_html__( 'Fixed', 'tlp-food-menu' ),
				'percentage' => esc_html__( 'Percentage(%)', 'tlp-food-menu' ),
				'add_tip'    => esc_html__( 'Add Tip', 'tlp-food-menu' ),
				'remove_tip' => esc_html__( 'Remove Tip', 'tlp-food-menu' ),
			],
		];
	}
}
