<?php
// phpcs:disable WordPress.NamingConventions.PrefixAllGlobals -- Established plugin: public namespace, hook names, functions and theme-overridable template variables must stay unchanged for backward compatibility.
/**
 * Tip main class.
 *
 * @package RT_FoodMenu
 */

namespace RT\FoodMenu\Controllers\Tip;

defined( 'ABSPATH' ) || exit();

use RT\FoodMenu\Traits\SingletonTrait;
use RT\FoodMenu\Blocks\CheckoutTip;
use RT\FoodMenu\Helpers\Fns;

/**
 * Tip main class
 */
class Tip {

	use SingletonTrait;

	/**
	 * Settings option
	 *
	 * @var array
	 */
	private $options;

	/**
	 * Init function call hooks
	 *
	 * @return void
	 */
	public function init() {
		$this->options = get_option( TLPFoodMenu()->options['settings'], [] );
		$enable_tip    = $this->options['enable_tip'] ?? 'on';
		$fm_type       = $this->options['fm_food_menu_type'] ?? 'food_menu_post';

		if ( TLPFoodMenu()->isWcActive() && 'on' === $enable_tip && 'online_ordering' === $fm_type ) {

			$show_tip_form = $this->options['show_tip_form'] ?? 'both';

			// Add form in order and cart page (classic shortcode checkout).
			if ( 'both' === $show_tip_form ) {
				add_action( 'woocommerce_after_order_notes', [ $this, 'tip_form' ] );
				add_action( 'woocommerce_before_cart_totals', [ $this, 'tip_form' ] );
			} elseif ( 'cart' === $show_tip_form ) {
				add_action( 'woocommerce_before_cart_totals', [ $this, 'tip_form' ] );
			} else {
				add_action( 'woocommerce_after_order_notes', [ $this, 'tip_form' ] );
			}

			// WooCommerce Blocks integration.
			// WC uses PluginArea scope "woocommerce-checkout" for BOTH cart and
			// checkout blocks, so we always register via the checkout hook.
			// The JS component handles cart-vs-checkout visibility based on
			// the show_tip_form setting.
			add_action( 'woocommerce_blocks_checkout_block_registration', [ $this, 'register_blocks_integration' ] );
			add_action( 'woocommerce_blocks_cart_block_registration', [ $this, 'register_blocks_integration' ] );

			// Add tip assets file enqueue.
			add_action( 'wp_enqueue_scripts', [ $this, 'frontend_enqueue_assets' ] );

			// Tip data add to session.
			add_action( 'wp_ajax_add_tip', [ $this, 'add_tip_to_session' ] );
			add_action( 'wp_ajax_nopriv_add_tip', [ $this, 'add_tip_to_session' ] );

			// Cart calculate update.
			add_action( 'woocommerce_cart_calculate_fees', [ $this, 'cart_calculate_fee_update' ] );

			add_filter( 'woocommerce_get_order_item_totals', function ( $totals, $order ) {
				$type = $order->get_meta( 'fmp_tip_type' );
				if ( ! $type ) {
					return $totals;
				}

				$suffix = 'fixed' === $type
					? '(Fixed)'
					: '(' . $order->get_meta( 'fmp_percentage_amount' ) . '%)';

				foreach ( $totals as $key => &$row ) {
					if ( 0 === strpos( $key, 'fee_' ) && false !== stripos( $row['label'], 'Tip' ) ) {
						$row['value'] .= ' <small style="color:#6b7280;font-weight:400;">' . esc_html( $suffix ) . '</small>';
					}
				}
				return $totals;
			}, 10, 2 );

			// when new order the remove session.
			add_action( 'woocommerce_new_order', [ $this, 'remove_tip_session_place_order' ] );

			// Tip data remove to session.
			add_action( 'wp_ajax_remove_tip', [ $this, 'remove_tip_to_session' ] );
			add_action( 'wp_ajax_nopriv_remove_tip', [ $this, 'remove_tip_to_session' ] );

		}
	}

	/**
	 * Remove tip from session.
	 *
	 * @return void
	 */
	public function remove_tip_to_session() {
		$status = 0;
		if ( Fns::verifyNonce() ) {
			WC()->session->__unset( 'fmp_tip' );
			$status  = 1;
			$message = esc_html__( 'The Tip has been removed successfully', 'tlp-food-menu' );
		} else {
			$message = esc_html__( 'Nonce is not valid! Please try again', 'tlp-food-menu' );
		}
		wp_send_json(
			[
				'status'  => $status,
				'message' => $message,
			]
		);
		wp_die();
	}

	/**
	 * Remove fmp_tip session when new order.
	 *
	 * @return void
	 */
	public function remove_tip_session_place_order() {
		if ( ! is_admin() && WC()->session ) {
			WC()->session->__unset( 'fmp_tip' );
		}
	}

	/**
	 * Cart calculate fee update
	 *
	 * @param object $cart receive cart object.
	 */
	public function cart_calculate_fee_update( $cart ) {

		$tip_session_data = WC()->session->get( 'fmp_tip' );

		if ( ! empty( $tip_session_data ) ) {
			$tip_title  = esc_html__( 'Tip', 'tlp-food-menu' );
			$tip_amount = 0;

			switch ( $tip_session_data['tip_selected_type'] ) {
				case 'fixed':
					$tip_amount = $tip_session_data['tip_fixed_amount'];
					break;

				case 'percentage':
					$sub_total  = $cart->get_subtotal();
					$tip_amount = ( $tip_session_data['tip_percentage_amount'] / 100 ) * $sub_total;
					$tip_title .= '(' . $tip_session_data['tip_percentage_amount'] . '%)';
					break;
			}

			if ( $tip_amount > 0 ) {
				$cart->add_fee( $tip_title, $tip_amount );
			}
		}
	}

	/**
	 * Tip add wc session.
	 *
	 * @return void
	 */
	public function add_tip_to_session() {
		$status = 0;
		if ( Fns::verifyNonce() ) {

            $selected_type = sanitize_text_field(wp_unslash($_POST['selectedType'])); //phpcs:ignore
            $fixed_amount = floatval(sanitize_text_field($_POST['fixedAmount'])); //phpcs:ignore
            $percentage_amount = intval(sanitize_text_field($_POST['percentageAmount']));//phpcs:ignore

			$tip_added = ( 'fixed' === $selected_type && $fixed_amount > 0 ) || ( 'percentage' === $selected_type && $percentage_amount > 0 );

			if ( $tip_added ) {
				$tip_data = [
					'tip_added'             => 1,
					'tip_selected_type'     => $selected_type,
					'tip_fixed_amount'      => $fixed_amount,
					'tip_percentage_amount' => $percentage_amount,
				];
				WC()->session->set( 'fmp_tip', $tip_data );
				$status  = 1;
				$message = esc_html__( 'The tip has been successfully added.', 'tlp-food-menu' );
			} else {
				$message = esc_html__( 'Error: Invalid tip type or value selected. Please try again.', 'tlp-food-menu' );
			}
		} else {
			$message = esc_html__( 'Nonce is not valid! Please try again', 'tlp-food-menu' );
		}

		wp_send_json(
			[
				'status'  => $status,
				'message' => $message,
			]
		);

		wp_die();
	}

	/**
	 * Tip frontend assets enqueue
	 *
	 * @return void
	 */
	public function frontend_enqueue_assets() {
		$nonce = wp_create_nonce( Fns::nonceText() );
		// tip frontend js.
		wp_enqueue_script( 'fmp-ajax-tip' );
		wp_localize_script(
			'fmp-ajax-tip',
			'fmpTipParams',
			[
				'nonceID' => esc_attr( Fns::nonceId() ),
				'nonce'   => esc_attr( $nonce ),
				'ajaxurl' => esc_url( admin_url( 'admin-ajax.php' ) ),
			]
		);
	}

	/**
	 * Register WooCommerce Blocks checkout integration for Tip.
	 *
	 * @param object $integration_registry The integration registry.
	 *
	 * @return void
	 */
	public function register_blocks_integration( $integration_registry ) {
		$integration_registry->register( new CheckoutTip() );
	}

	/**
	 * Render tip form
	 *
	 * @return void
	 */
	public function tip_form() {
		// Skip if Pro custom checkout already rendered the tip form at its own position.
		if ( did_action( 'fmp_custom_checkout_tip_rendered' ) ) {
			return;
		}

		Fns::render( 'tip/tip-form' );
	}
}
