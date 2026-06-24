<?php
/**
 * Food Location Controller.
 *
 * Filters food menu / shop queries by the customer's selected location and
 * renders the location popup + optional floating button.
 *
 * @package RT_FoodMenu
 */

namespace RT\FoodMenu\Controllers\Frontend;

use RT\FoodMenu\Helpers\Fns;
use RT\FoodMenu\Blocks\CheckoutLocation;
use RT\FoodMenu\Traits\SingletonTrait;

// Do not allow directly accessing this file.
if ( ! defined( 'ABSPATH' ) ) {
	exit( 'This script cannot be accessed directly.' );
}

/**
 * Food Location Controller.
 */
class FoodLocation {

	use SingletonTrait;

	/**
	 * Settings.
	 *
	 * @var array
	 */
	public $settings = [];

	/**
	 * Class Init.
	 *
	 * @return void
	 */
	protected function init() {
		$this->settings       = Fns::get_settings_option();
		$enable_food_location = $this->settings['fmp_food_location_popup'] ?? '';

		if ( empty( $enable_food_location ) ) {
			return;
		}

		if ( ! TLPFoodMenu()->isWcActive() ) {
			return;
		}

		add_filter( 'rt_fm_sc_query_args', [ $this, 'filterByLocation' ], 10, 2 );
		add_action( 'woocommerce_product_query', [ $this, 'filterShopByLocation' ] );

		// Validate cart when location changes (only registers if cookie exists).
		if ( $this->getLocationId() ) {
			add_action( 'woocommerce_cart_loaded_from_session', [ $this, 'validateCartLocation' ] );
		}

		// Footer: location popup.
		add_action( 'wp_footer', [ $this, 'render_food_location_popup' ] );

		// Footer: floating "change location" button. Defaults to enabled —
		// the settings UI shows this toggle ON by default, but the value is
		// only persisted once the user explicitly toggles it. So an absent
		// value means "default on"; only an explicit '' (toggled off) disables.
		if ( 'on' === ( $this->settings['fmp_floating_location_btn'] ?? 'on' ) ) {
			add_action( 'wp_footer', [ $this, 'render_floating_location_button' ] );
		}

		// Render the hidden location field on the classic checkout — JS fills it
		// from localStorage so the term name is posted with the order. Pro's
		// custom checkout removes this hook and renders its own field instead.
		add_action( 'woocommerce_checkout_before_customer_details', [ $this, 'render_location_form' ] );

		// WooCommerce Blocks (Gutenberg) checkout display: register the
		// `CheckoutLocation` integration to render the same selected-location
		// card at the top of the Blocks checkout/cart order summary. WC uses
		// the same plugin-area scope for both blocks so we hook both events.
		add_action( 'woocommerce_blocks_checkout_block_registration', [ $this, 'register_blocks_integration' ] );
		add_action( 'woocommerce_blocks_cart_block_registration', [ $this, 'register_blocks_integration' ] );

		// Save selected location onto the order. Classic checkout fires
		// `woocommerce_checkout_create_order`; the Blocks (Gutenberg) checkout
		// goes through the Store API and fires `…_store_api_checkout_update_order_from_request`
		// instead. Hooking both keeps the order meta in sync regardless of
		// which checkout the site is using. The Store API hook passes
		// `($order, $request)` — the second arg is ignored.
		add_action( 'woocommerce_checkout_create_order', [ $this, 'location_update_meta' ] );
		add_action( 'woocommerce_store_api_checkout_update_order_from_request', [ $this, 'location_update_meta' ] );
	}

	/**
	 * Register the Blocks checkout integration for the selected-location card.
	 *
	 * @param object $integration_registry The integration registry.
	 *
	 * @return void
	 */
	public function register_blocks_integration( $integration_registry ) {
		$integration_registry->register( new CheckoutLocation() );
	}

	/**
	 * Render the hidden location field at the top of the classic WC checkout.
	 *
	 * The `.fmp-location-name` input is populated client-side by
	 * `src/js/foodmenu.js` from `localStorage.fmp_location` on document.ready,
	 * so the selected term name is submitted with the checkout form.
	 *
	 * @return void
	 */
	public function render_location_form() {
		$order_location = apply_filters( 'fm_order_location_checkout_title', __( 'Food Order Location', 'tlp-food-menu' ) );
		?>
		<div id="fmp-location-field">
			<div class="fmp-location-title"><?php echo esc_html( $order_location ); ?></div>
			<div class="fmp-location-name"></div>
			<input type="hidden" name="fmp_location_name" class="fmp-location-name"/>
		</div>
		<?php
	}

	/**
	 * Filter shortcode/widget queries by selected location.
	 *
	 * @param array $args Query args.
	 * @param int $scID Shortcode ID.
	 *
	 * @return array
	 */
	public function filterByLocation( $args, $scID ) {
		$locationId = $this->getLocationId();

		if ( ! $locationId ) {
			return $args;
		}

		$location_tax_query = [
			'relation' => 'OR',
			[
				'taxonomy' => 'tpl-food-location',
				'field'    => 'term_id',
				'terms'    => [ $locationId ],
			],
			[
				'taxonomy' => 'tpl-food-location',
				'operator' => 'NOT EXISTS',
			],
		];

		if ( ! empty( $args['tax_query'] ) ) {
			$args['tax_query'] = [ //phpcs:ignore WordPress.DB.SlowDBQuery.slow_db_query_tax_query
				'relation' => 'AND',
				$args['tax_query'],
				$location_tax_query,
			];
		} else {
			$args['tax_query'] = [ $location_tax_query ]; //phpcs:ignore WordPress.DB.SlowDBQuery.slow_db_query_tax_query
		}

		return $args;
	}

	/**
	 * Filter WooCommerce shop page by selected location.
	 *
	 * @param \WP_Query $query WooCommerce product query.
	 *
	 * @return void
	 */
	public function filterShopByLocation( $query ) {
		$locationId = $this->getLocationId();

		if ( ! $locationId ) {
			return;
		}

		$location_tax_query = [
			'relation' => 'OR',
			[
				'taxonomy' => 'tpl-food-location',
				'field'    => 'term_id',
				'terms'    => [ $locationId ],
			],
			[
				'taxonomy' => 'tpl-food-location',
				'operator' => 'NOT EXISTS',
			],
		];

		$existing = $query->get( 'tax_query' );

		if ( ! empty( $existing ) ) {
			$query->set(
				'tax_query', //phpcs:ignore WordPress.DB.SlowDBQuery.slow_db_query_tax_query
				[
					'relation' => 'AND',
					$existing,
					$location_tax_query,
				]
			);
		} else {
			$query->set( 'tax_query', [ $location_tax_query ] ); //phpcs:ignore WordPress.DB.SlowDBQuery.slow_db_query_tax_query
		}
	}

	/**
	 * Validate cart items against the selected location.
	 *
	 * Removes items that are assigned to a different location.
	 * Products with no location terms are kept (available everywhere).
	 *
	 * @return void
	 */
	public function validateCartLocation() {
		$locationId     = $this->getLocationId();
		$last_validated = WC()->session ? WC()->session->get( 'fmp_cart_validated_location' ) : null;

		// Skip if already validated for this location.
		if ( $last_validated && (int) $last_validated === $locationId ) {
			return;
		}

		$cart    = WC()->cart;
		$removed = [];

		foreach ( $cart->get_cart() as $cart_item_key => $cart_item ) {
			$product_id = $cart_item['product_id'];
			$terms      = get_the_terms( $product_id, 'tpl-food-location' );

			// No location terms = available everywhere, skip.
			if ( empty( $terms ) || is_wp_error( $terms ) ) {
				continue;
			}

			// Check if product has the current location.
			$term_ids = wp_list_pluck( $terms, 'term_id' );

			if ( ! in_array( $locationId, $term_ids, true ) ) {
				$removed[] = $cart_item['data']->get_name();
				$cart->remove_cart_item( $cart_item_key );
			}
		}

		// Mark this location as validated so we don't re-check on next page load.
		if ( WC()->session ) {
			WC()->session->set( 'fmp_cart_validated_location', $locationId );
		}

		if ( ! empty( $removed ) ) {
			wc_add_notice(
				sprintf(
				/* translators: %s: comma-separated list of removed product names */
					__( 'The following items were removed from your cart because they are not available at your selected location: %s', 'tlp-food-menu' ),
					'<strong>' . implode( ', ', $removed ) . '</strong>'
				),
				'notice'
			);
		}
	}

	/**
	 * Get the selected location ID from cookie.
	 *
	 * @return int
	 */
	private function getLocationId() {
		return ! empty( $_COOKIE['fmp_location_id'] ) ? absint( $_COOKIE['fmp_location_id'] ) : 0;
	}

	/**
	 * Popup food location in footer.
	 *
	 * @return void
	 */
	public function render_food_location_popup() {
		$fmp_locations = get_terms( [
			'taxonomy'   => 'tpl-food-location',
			'hide_empty' => false,
			'parent'     => 0,
		] );

		if ( is_wp_error( $fmp_locations ) || empty( $fmp_locations ) ) {
			return;
		}
		?>
        <div id="fmp-location-modal" class="fmp-popup-modal" style="display:none">
            <div class="modal-content">
                <div class="fmp-modal-icon">
                    <svg xmlns="http://www.w3.org/2000/svg" width="24" height="24" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round">
                        <path d="M21 10c0 7-9 13-9 13s-9-6-9-13a9 9 0 0 1 18 0z"/>
                        <circle cx="12" cy="10" r="3"/>
                    </svg>
                </div>
                <select name="fmp-location" class="fmp-location">
                    <option value=""><?php echo esc_html__( 'Select Location', 'tlp-food-menu' ); ?></option>
					<?php
					foreach ( $fmp_locations as $parent_term ) {
						$children = get_term_children( $parent_term->term_id, 'tpl-food-location' );
						if ( ! empty( $children ) && ! is_wp_error( $children ) ) {
							echo "<optgroup label='" . esc_attr( $parent_term->name ) . "'>";
							foreach ( $children as $child_id ) {
								$child = get_term( $child_id, 'tpl-food-location' );
								if ( $child && ! is_wp_error( $child ) ) {
									echo "<option value='" . esc_attr( $child->term_id ) . "'>" . esc_html( $child->name ) . '</option>';
								}
							}
							echo '</optgroup>';
						} else {
							echo "<option value='" . esc_attr( $parent_term->term_id ) . "'>" . esc_html( $parent_term->name ) . '</option>';
						}
					}
					?>
                </select>

                <button class="fmp-save-option fmp-btn"><?php echo esc_html__( 'Save', 'tlp-food-menu' ); ?></button>
                <div class="confirm-msg fmp-hidden"><?php echo esc_html__( 'Save Your Preferred Location', 'tlp-food-menu' ); ?></div>

                <button class="fmp-close fmp-btn"></button>
            </div>
        </div>
		<?php
	}

	/**
	 * Render floating location change button.
	 *
	 * @return void
	 */
	public function render_floating_location_button() {
		$position = $this->settings['fmp_location_float_position'] ?? 'right_bottom';
		Fns::render( 'location/floating-button', [ 'position' => $position ] );
	}

	/**
	 * Update location form data.
	 *
	 * Fires on `woocommerce_checkout_create_order` which is dispatched by both
	 * the classic shortcode checkout AND the WC Blocks Store API checkout — by
	 * the time we land here WC has already authenticated the request (classic
	 * `woocommerce-process-checkout-nonce` or Blocks `X-WP-Nonce`), so we
	 * deliberately skip a second nonce check here. The previous classic-only
	 * nonce check silently rejected every Blocks checkout submission, leaving
	 * the location meta empty on orders placed via the Gutenberg checkout.
	 *
	 * @param object $order .
	 *
	 * @return void
	 */
	public function location_update_meta( $order ) {
		$posted_name = isset( $_POST['fmp_location_name'] ) ? sanitize_text_field( wp_unslash( $_POST['fmp_location_name'] ) ) : '';
		$location_id = $this->getLocationId();

		// Resolve the location name. Prefer the posted value, but fall back
		// to the term name when nothing was posted (e.g. minimal/QR checkout
		// skips the hidden field but the cookie still has the location id).
		if ( '' === $posted_name && $location_id && taxonomy_exists( 'tpl-food-location' ) ) {
			$term = get_term( $location_id, 'tpl-food-location' );
			if ( $term && ! is_wp_error( $term ) ) {
				$posted_name = $term->name;
			}
		}

		if ( '' !== $posted_name ) {
			$order->update_meta_data( 'fmp_location_name', $posted_name );
		}

		if ( $location_id ) {
			$order->update_meta_data( 'fmp_location_id', $location_id );
		}
	}
}
