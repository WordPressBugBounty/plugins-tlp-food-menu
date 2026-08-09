<?php
// phpcs:disable WordPress.NamingConventions.PrefixAllGlobals -- Established plugin: public namespace, hook names, functions and theme-overridable template variables must stay unchanged for backward compatibility.

namespace RT\FoodMenu\Controllers\Discount;

defined( 'ABSPATH' ) || exit();

use RT\FoodMenu\Helpers\Fns;
use RT\FoodMenu\Traits\SingletonTrait;

/**
 * Percentage Discount controller (Free).
 *
 * Handles the percentage-based discount badges, cart-item meta, and price HTML.
 * Fixed-amount order discount stays in the Pro plugin's Discount controller.
 */
class Discount {

	use SingletonTrait;

	/**
	 * Settings.
	 *
	 * @var array
	 */
	private $settings = [];

	/**
	 * Init function call hooks.
	 *
	 * @return void
	 */
	public function init() {
		$this->settings = Fns::get_settings_option();
		$fm_type        = $this->settings['fm_food_menu_type'] ?? 'food_menu_post';
		if ( TLPFoodMenu()->isWcActive() && 'online_ordering' === $fm_type ) {
			add_filter( 'woocommerce_get_price_html', [ $this, 'append_discount_badge_to_price' ], 11, 2 );
			add_action( 'wp_footer', [ $this, 'render_discount_badge_script' ] );
			add_filter( 'woocommerce_get_item_data', [ $this, 'show_cart_item_custom_meta_data' ], 10, 2 );
			add_action( 'woocommerce_new_order_item', [ $this, 'order_details_thank_you_add_discount' ], 1, 2 );
			add_filter( 'woocommerce_get_price_html', [ $this, 'simple_product_price_html' ], 10, 2 );

			// Apply percentage discount to the cart item price so line totals reflect the discount.
			add_filter( 'woocommerce_add_cart_item', [ $this, 'price_update_cart_item' ], 20 );
			add_filter( 'woocommerce_get_cart_item_from_session', [ $this, 'get_cart_item_from_session' ], 20, 2 );
		}
	}

	/**
	 * Simple Product Price html change.
	 *
	 * @param string $price_html .
	 * @param object $product .
	 *
	 * @return mixed|string
	 */
	public function simple_product_price_html( $price_html, $product ) {
		$discount_data = Fns::discount_price(
			[
				'product_id'    => $product->get_id(),
				'data'          => 'fmp_single_page',
				'product_price' => null,
			]
		);

		if (
			! empty( $discount_data['main_price'] ) &&
			! empty( $discount_data['price_after_discount'] ) &&
			$product->get_type() != 'variable'
		) {
			return sprintf(
				'<div class="fmp-price-wrap"><del class="fmp-main-price">%s</del><span class="fmp-discount-price">%s</span></div>',
				wc_price( $discount_data['main_price'] ),
				wp_kses_post( $discount_data['price_after_discount'] )
			);
		}

		return $price_html;
	}

	/**
	 * Check discount for a product.
	 *
	 * @param int    $product_id .
	 * @param string $flag .
	 *
	 * @return array
	 */
	public function check_discount_of_product( $product_id, $flag = null ) {
		$settings             = $this->settings;
		$fmp_discount_message = '';
		$tag_message          = '';
		$data                 = [];
		$fmp_discount_product = (array) ( $settings['fmp_include_menu'] ?? [] );
		$fmp_discount_cat     = (array) ( $settings['fmp_include_cat'] ?? [] );
		$fmp_percentage       = $settings['fmp_discount_percentage'] ?? null;

		if ( in_array( $product_id, $fmp_discount_product ) ) {
			if ( '0' !== $fmp_percentage && ! empty( $fmp_percentage ) ) {
				$fmp_discount_message .= esc_html__( 'Discount ', 'tlp-food-menu' ) . $fmp_percentage . '%';
				$tag_message          .= esc_html( $fmp_percentage ) . '%';
			}
		} else {
			$fmp_terms = get_the_terms( $product_id, 'product_cat' );
			if ( is_array( $fmp_terms ) ) {
				foreach ( $fmp_terms as $term ) {
					if ( in_array( $term->term_id, $fmp_discount_cat ) ) {
						if ( '0' !== $fmp_percentage && ! empty( $fmp_percentage ) ) {
							$fmp_discount_message .= esc_html__( 'Discount ', 'tlp-food-menu' ) . $fmp_percentage . '%';
							$tag_message          .= $fmp_percentage . esc_html__( '% off', 'tlp-food-menu' );
						}
					}
				}
			}
		}

		if ( empty( $flag ) ) {
			$data['message']    = $fmp_discount_message;
			$data['percentage'] = ! empty( $data['message'] ) ? $fmp_percentage : '';
		} else {
			$data['percentage_offer'] = $tag_message;
		}

		return $data;
	}

	/**
	 * Append discount badge to price HTML on single product page only.
	 *
	 * @param string      $price_html Price HTML.
	 * @param \WC_Product $product    Product object.
	 *
	 * @return string
	 */
	public function append_discount_badge_to_price( $price_html, $product ) {
		$settings = $this->settings;
		$display  = $settings['fmp_discount_badge_display'] ?? 'product_page';

		if ( 'product_page' !== $display ) {
			return $price_html;
		}

		global $post;
		if ( ! is_product() || ! $post || $post->ID !== $product->get_id() ) {
			return $price_html;
		}

		$fmp_check_discount = $this->check_discount_of_product( $product->get_id(), null );

		if ( ! empty( $fmp_check_discount['message'] ) ) {
			$price_html .= '<div class="fmp-discount-badge-wrap">'
				. '<span class="fmp-discount-badge">'
				. '<span class="fmp-discount-badge__icon"></span>'
				. '<span>' . esc_html( $fmp_check_discount['message'] ) . '</span>'
				. '</span>'
				. '</div>';
		}

		return $price_html;
	}

	/**
	 * Get product IDs eligible for the discount badge.
	 *
	 * @return int[]
	 */
	private function get_discounted_product_ids() {
		$settings     = $this->settings;
		$include_menu = $settings['fmp_include_menu'] ?? [];
		$include_cat  = (array) ( $settings['fmp_include_cat'] ?? [] );
		$percentage   = $settings['fmp_discount_percentage'] ?? null;

		if ( empty( $percentage ) ) {
			return [];
		}

		$ids = is_array( $include_menu ) ? array_map( 'intval', $include_menu ) : [];

		if ( ! empty( $include_cat ) ) {
			$cat_products = get_posts(
				[
					'post_type'      => 'product',
					'posts_per_page' => - 1,
					'fields'         => 'ids',
					'tax_query'      => [
						[
							'taxonomy' => 'product_cat',
							'field'    => 'term_id',
							'terms'    => array_map( 'intval', $include_cat ),
						],
					],
				]
			);
			if ( ! empty( $cat_products ) ) {
				$ids = array_merge( $ids, array_map( 'intval', $cat_products ) );
			}
		}

		return array_values( array_unique( array_filter( $ids ) ) );
	}

	/**
	 * Inject discount badge above the product title via JS (everywhere mode).
	 *
	 * @return void
	 */
	public function render_discount_badge_script() {
		$settings = $this->settings;
		$display  = $settings['fmp_discount_badge_display'] ?? 'product_page';

		if ( 'everywhere' !== $display ) {
			return;
		}

		$discount_ids = $this->get_discounted_product_ids();
		if ( empty( $discount_ids ) ) {
			return;
		}

		$message_map = [];
		foreach ( $discount_ids as $pid ) {
			$data = $this->check_discount_of_product( $pid, null );
			if ( ! empty( $data['message'] ) ) {
				$message_map[ $pid ] = $data['message'];
			}
		}

		if ( empty( $message_map ) ) {
			return;
		}

		// Encode with hex flags so no HTML characters ( <, >, &, quotes ) can break out of the <script> tag.
		$json_flags    = JSON_HEX_TAG | JSON_HEX_AMP | JSON_HEX_APOS | JSON_HEX_QUOT;
		$ids_json      = wp_json_encode( array_map( 'intval', array_keys( $message_map ) ), $json_flags );
		$messages_json = wp_json_encode( $message_map, $json_flags );

		?>
		<script>
			(function () {
				var ids = <?php echo $ids_json; // phpcs:ignore WordPress.Security.EscapeOutput.OutputNotEscaped -- Safe JSON, HTML chars hex-encoded above. ?>;
				var messages = <?php echo $messages_json; // phpcs:ignore WordPress.Security.EscapeOutput.OutputNotEscaped -- Safe JSON, HTML chars hex-encoded above. ?>;

				function escapeHtml(str) {
					return String(str)
						.replace(/&/g, '&amp;')
						.replace(/</g, '&lt;')
						.replace(/>/g, '&gt;')
						.replace(/"/g, '&quot;')
						.replace(/'/g, '&#039;');
				}

				function applyDiscountBadges() {
					ids.forEach(function (id) {
						var wrappers = document.querySelectorAll(
							'[data-pid="' + id + '"], .fmp-item-' + id + ', .post-' + id
						);
						wrappers.forEach(function (el) {
							processElement(el, id);
						});

						var links = document.querySelectorAll('a[data-id="' + id + '"]');
						links.forEach(function (link) {
							var container = link.closest('.fmp-food-item')
								|| link.closest('.fmp-box-wrapper')
								|| link.closest('.food-menu5-box')
								|| link.closest('li')
								|| link.parentElement;
							if (container) {
								processElement(container, id);
							}
						});
					});
				}

				function processElement(el, id) {
					if (el.classList.contains('fmp-discount-badge-processed')) return;
					el.classList.add('fmp-discount-badge-processed');

					var titleEl = el.querySelector('.product_title')
						|| el.querySelector('.fmp-title')
						|| el.querySelector('.woocommerce-loop-product__title')
						|| el.querySelector('h3')
						|| el.querySelector('h2');

					if (titleEl && !titleEl.querySelector('.fmp-discount-badge')) {
						var wrap = document.createElement('div');
						wrap.className = 'fmp-discount-badge-wrap';
						var badge = document.createElement('span');
						badge.className = 'fmp-discount-badge';
						badge.innerHTML = '<span class="fmp-discount-badge__icon"></span>'
							+ '<span>' + escapeHtml(messages[id] || '') + '</span>';
						wrap.appendChild(badge);
						titleEl.insertBefore(wrap, titleEl.firstChild);
					}
				}

				var debounceTimer = null;
				function scheduleApply() {
					if (debounceTimer) {
						return;
					}
					debounceTimer = setTimeout(function () {
						debounceTimer = null;
						applyDiscountBadges();
					}, 50);
				}

				if (document.readyState === 'loading') {
					document.addEventListener('DOMContentLoaded', applyDiscountBadges);
				} else {
					applyDiscountBadges();
				}

				document.addEventListener('fmp:content-loaded', applyDiscountBadges);

				function startObserver() {
					if (typeof MutationObserver === 'undefined' || !document.body) {
						return;
					}

					var observer = new MutationObserver(function (mutations) {
						for (var i = 0; i < mutations.length; i++) {
							var added = mutations[i].addedNodes;
							if (!added || !added.length) {
								continue;
							}
							for (var j = 0; j < added.length; j++) {
								if (added[j].nodeType === 1) {
									scheduleApply();
									return;
								}
							}
						}
					});

					observer.observe(document.body, { childList: true, subtree: true });
				}

				if (document.body) {
					startObserver();
				} else {
					document.addEventListener('DOMContentLoaded', startObserver);
				}

				if (window.jQuery) {
					window.jQuery(document.body).on(
						'updated_wc_div wc_fragments_refreshed wc_fragments_loaded post-load',
						scheduleApply
					);
				}
			})();
		</script>
		<?php
	}

	/**
	 * Show discount data in cart item checkout and cart page.
	 *
	 * @param array $item_data .
	 * @param array $cart_item .
	 *
	 * @return array
	 */
	public function show_cart_item_custom_meta_data( $item_data, $cart_item ) {
		$fmp_check_discount = $this->check_discount_of_product( $cart_item['product_id'], null );
		if ( ! empty( $fmp_check_discount['message'] ) ) {
			$item_data['fmp_discount_price'] = [
				'key'     => esc_html__( 'Discount', 'tlp-food-menu' ),
				'value'   => esc_html( $fmp_check_discount['message'] ),
				'display' => '',
			];

			$product = wc_get_product( $cart_item['product_id'] );
			if ( $product ) {
				$main_price = wc_get_price_excluding_tax( $product ) ?: wc_get_price_including_tax( $product );
				if ( ! empty( $main_price ) ) {
					$item_data[] = [
						'name'    => esc_html__( 'Product price', 'tlp-food-menu' ),
						'display' => wc_price( $main_price ),
					];
				}
			}
		}

		return $item_data;
	}

	/**
	 * Set discounted price when a product is added to the cart.
	 *
	 * @param array $cart_item_data Cart item data.
	 *
	 * @return array
	 */
	public function price_update_cart_item( $cart_item_data ) {
		return $this->apply_discount_to_cart_item( $cart_item_data );
	}

	/**
	 * Set discounted price when cart items are loaded from session.
	 *
	 * @param array $cart_item Cart item.
	 * @param array $values    Session values.
	 *
	 * @return array
	 */
	public function get_cart_item_from_session( $cart_item, $values ) {
		return $this->apply_discount_to_cart_item( $cart_item );
	}

	/**
	 * Recalculate the cart item price using the configured percentage discount.
	 *
	 * @param array $cart_item Cart item.
	 *
	 * @return array
	 */
	private function apply_discount_to_cart_item( $cart_item ) {
		// Pro plugin's FrontendCartAction handles the cart-item price (including addons).
		// Skip here to avoid double-discounting when both run on the same hook.
		if ( TLPFoodMenu()->has_pro() ) {
			return $cart_item;
		}

		if ( empty( $cart_item['data'] ) || empty( $cart_item['product_id'] ) ) {
			return $cart_item;
		}

		$base_price    = (float) $cart_item['data']->get_price( 'edit' );
		$discount_data = Fns::discount_price(
			[
				'product_id'    => $cart_item['product_id'],
				'data'          => 'fmp_cart',
				'product_price' => $base_price,
			]
		);

		if ( empty( $discount_data['new_price'] ) ) {
			return $cart_item;
		}

		$new_price = (float) $discount_data['new_price'];
		$cart_item['data']->set_price( $new_price );
		$cart_item['data']->set_regular_price( $new_price );
		$cart_item['data']->set_sale_price( $new_price );

		return $cart_item;
	}

	/**
	 * Admin order details & Checkout thank you page add discount.
	 *
	 * @param int   $item_id .
	 * @param array $values .
	 *
	 * @return void
	 */
	public function order_details_thank_you_add_discount( $item_id, $values ) {
		$fmp_data = Fns::discount_price(
			[
				'product_id'    => $values['product_id'],
				'data'          => 'thank_you_order_details',
				'product_price' => null,
			]
		);

		if ( empty( $fmp_data['main_price'] ) || empty( $fmp_data['price_after_discount'] ) ) {
			return;
		}
		wc_add_order_item_meta( $item_id, esc_html__( 'Discount', 'tlp-food-menu' ), $fmp_data['discount_percentage'] . '%' );
		wc_add_order_item_meta( $item_id, esc_html__( 'Main price', 'tlp-food-menu' ), wc_price( $fmp_data['main_price'] ) );
		wc_add_order_item_meta( $item_id, esc_html__( 'Price after discount', 'tlp-food-menu' ), $fmp_data['price_after_discount'] );
	}
}
