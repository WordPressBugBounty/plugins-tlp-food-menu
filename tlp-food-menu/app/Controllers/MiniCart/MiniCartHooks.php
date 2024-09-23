<?php
/**
 * Main MiniCart class.
 *
 * @package RT_FoodMenuPro
 */

namespace RT\FoodMenu\Controllers\MiniCart;

use RT\FoodMenu\Helpers\Fns;
use RT\FoodMenu\Traits\SingletonTrait;

defined( 'ABSPATH' ) || exit();

/**
 * Main FilterHooks class.
 */
class MiniCartHooks {
	/**
	 * Singleton Trait.
	 */
	use SingletonTrait;

	/**
	 * @var array|mixed
	 */
	private static $options;

	/**
	 * Class constructor
	 */
	private function __construct() {

		self::$options = get_option( TLPFoodMenu()->options['settings'] );

		add_filter( 'woocommerce_loop_add_to_cart_args', [ __CLASS__, 'woocommerce_loop_add_to_cart_args' ] );

		// Load Template Ajax.
		add_action( 'wp_ajax_nopriv_fmp_mini_cart_template', [ __CLASS__, 'fmp_mini_cart_template' ] );
		add_action( 'wp_ajax_fmp_mini_cart_template', [ __CLASS__, 'fmp_mini_cart_template' ] );

		// Applly coupon Ajax.
		add_action( 'wp_ajax_fmp_apply_coupon', [ __CLASS__, 'fmp_apply_coupon_ajax_handler' ] );
		add_action( 'wp_ajax_nopriv_fmp_apply_coupon', [ __CLASS__, 'fmp_apply_coupon_ajax_handler' ] );

		// Cart count updated.
		add_filter( 'woocommerce_add_to_cart_fragments', [ __CLASS__, 'cart_count_update' ] );

		add_filter( 'body_class', [ __CLASS__, 'body_classes' ] );

		add_action( 'fmp_minicart_quantity', [ __CLASS__, 'fmp_minicart_quantity' ], 10, 3 );
		add_action( 'fmp_minicart_extra_fields', [ __CLASS__, 'fmp_minicart_extra_fields' ] );

		// Remove All Item From Cart.
		add_action( 'wp_ajax_nopriv_fmp_clear_cart_items', [ $this, 'fmp_clear_cart_items' ] );
		add_action( 'wp_ajax_fmp_clear_cart_items', [ $this, 'fmp_clear_cart_items' ] );
	}

	/**
	 * Apply coupon code
	 *
	 * @return void
	 */
	public static function fmp_apply_coupon_ajax_handler() {

		if ( wp_verify_nonce( Fns::getNonce(), Fns::nonceText() ) && isset( $_POST['coupon_code'] ) ) {

			$couponCode = sanitize_text_field( $_POST['coupon_code'] );

			// Apply the coupon.
			$result = WC()->cart->apply_coupon( $couponCode );

			if ( $result ) {
				wp_send_json_success();
			} else {
				wp_send_json_error();
			}
		} else {
			wp_send_json_error();
		}
	}


	/**
	 * Added new class in Add to cart button for mini-cart
	 *
	 * @param array $args argument.
	 *
	 * @return array
	 */
	public static function woocommerce_loop_add_to_cart_args( $args ) {
		$args['class'] .= ' fmp-mini-cart';
		return $args;
	}

	/**
	 * Added body classes
	 *
	 * @param array $classes body classes.
	 *
	 * @return array
	 */
	public static function body_classes( $classes ) {

		$opt = self::$options;

		if ( ! empty( $opt['mini_cart_open_style'] ) && 'open-always' == $opt['mini_cart_open_style'] ) {
			$classes[] = 'fmp-ajax-sidebar';
		}

		if ( ! empty( $opt['mini_cart_open_style'] ) ) {
			$classes[] = "fmp-{$opt['mini_cart_open_style']}";
		}

		return $classes;
	}


	/**
	 * Cart fragments update
	 *
	 * @param array $fragments .
	 *
	 * @return mixed
	 */
	public static function cart_count_update( $fragments ) {
		// Determine the label based on the number of items in the cart.
		$count_label = WC()->cart->get_cart_contents_count() < 2 ? __( 'Item', 'food-menu-pro' ) : __( 'Items', 'food-menu-pro' );

		// Generate the number of items element.
		$number = '<span class="fmp-cart-icon-num">' . WC()->cart->get_cart_contents_count() . ' <span class="items">' . $count_label . '</span></span>';

		// Get currency position from WooCommerce settings.
		$currency_position = get_option( 'woocommerce_currency_pos', 'left' );
		$cart_total        = WC()->cart->get_cart_contents_total();
		$currency_symbol   = get_woocommerce_currency_symbol();
		$total             = '';
		// Generate the total amount element based on the currency position using switch statement.
		switch ( $currency_position ) {
			case 'left':
				$total = '<span class="fmp-cart-icon-total">' . $currency_symbol . $cart_total . '</span>';
				break;
			case 'right':
				$total = '<span class="fmp-cart-icon-total">' . $cart_total . $currency_symbol . '</span>';
				break;
			case 'left_space':
				$total = '<span class="fmp-cart-icon-total">' . $currency_symbol . ' ' . $cart_total . '</span>';
				break;
			case 'right_space':
				$total = '<span class="fmp-cart-icon-total">' . $cart_total . ' ' . $currency_symbol . '</span>';
				break;
		}

		// Add the elements to the fragments array.
		$fragments['span.fmp-cart-icon-num']   = $number;
		$fragments['span.fmp-cart-icon-total'] = $total;

		return $fragments;
	}

	/**
	 * Load minicart template by ajax
	 *
	 * @return void
	 */
	public static function fmp_mini_cart_template() {

		if ( ! wp_verify_nonce( Fns::getNonce(), Fns::nonceText() ) ) {
			wp_send_json_error();
		}
		$param = $_POST['param'] ?? [];

		if ( isset( $param['cartItemKey'] ) && isset( $param['quantity'] ) ) {
			WC()->cart->set_quantity( $param['cartItemKey'], $param['quantity'] );
		}

		$htmlData = Fns::render( 'mini-cart/mini-cart', [], true );

		wp_send_json(
			[
				'status' => 'OK',
				'data'   => $htmlData,
			]
		);
		// wp_die();
	}


	/**
	 * Mini-cart quantity markup
	 *
	 * @param $args
	 * @param $product
	 * @param $echo
	 *
	 * @return false|string|void
	 */
	public static function fmp_woocommerce_quantity_input( $args = [], $product = null, $echo = true ) {
		if ( is_null( $product ) ) {
			$product = $GLOBALS['product'];
		}
		$defaults = [
			'input_id'     => uniqid( 'quantity_' ),
			'input_name'   => 'quantity',
			'input_value'  => '1',
			'classes'      => apply_filters( 'woocommerce_quantity_input_classes', [ 'input-text', 'qty', 'text' ], $product ),
			'max_value'    => apply_filters( 'woocommerce_quantity_input_max', - 1, $product ),
			'min_value'    => apply_filters( 'woocommerce_quantity_input_min', 0, $product ),
			'step'         => apply_filters( 'woocommerce_quantity_input_step', 1, $product ),
			'pattern'      => apply_filters( 'woocommerce_quantity_input_pattern', has_filter( 'woocommerce_stock_amount', 'intval' ) ? '[0-9]*' : '' ),
			'inputmode'    => apply_filters( 'woocommerce_quantity_input_inputmode', has_filter( 'woocommerce_stock_amount', 'intval' ) ? 'numeric' : '' ),
			'product_name' => $product ? $product->get_title() : '',
			'placeholder'  => apply_filters( 'woocommerce_quantity_input_placeholder', '', $product ),
			// When autocomplete is enabled in firefox, it will overwrite actual value with what user entered last. So we default to off.
			// See @link https://github.com/woocommerce/woocommerce/issues/30733.
			'autocomplete' => apply_filters( 'woocommerce_quantity_input_autocomplete', 'off', $product ),
			'readonly'     => false,
		];

		$args = apply_filters( 'woocommerce_quantity_input_args', wp_parse_args( $args, $defaults ), $product );

		// Apply sanity to min/max args - min cannot be lower than 0.
		$args['min_value'] = max( $args['min_value'], 0 );
		$args['max_value'] = 0 < $args['max_value'] ? $args['max_value'] : '';

		// Max cannot be lower than min if defined.
		if ( '' !== $args['max_value'] && $args['max_value'] < $args['min_value'] ) {
			$args['max_value'] = $args['min_value'];
		}
		$type = $args['min_value'] > 0 && $args['min_value'] === $args['max_value'] ? 'hidden' : 'number';
		$type = $args['readonly'] && 'hidden' !== $type ? 'text' : $type;

		$args['type'] = apply_filters( 'woocommerce_quantity_input_type', $type );

		ob_start();
		extract( $args )
		?>

		<div class="quantity">
			<input
					type="<?php echo esc_attr( $type ); ?>"
				<?php echo $readonly ? 'readonly="readonly"' : ''; ?>
					id="<?php echo esc_attr( $input_id ); ?>"
					class="<?php echo esc_attr( join( ' ', (array) $classes ) ); ?>"
					name="<?php echo esc_attr( $input_name ); ?>"
					value="<?php echo esc_attr( $input_value ); ?>"
					aria-label="<?php esc_attr_e( 'Product quantity', 'woocommerce' ); ?>"
					size="4"
					min="<?php echo esc_attr( $min_value ); ?>"
					max="<?php echo esc_attr( 0 < $max_value ? $max_value : '' ); ?>"
				<?php if ( ! $readonly ) : ?>
					step="<?php echo esc_attr( $step ); ?>"
					placeholder="<?php echo esc_attr( $placeholder ); ?>"
					inputmode="<?php echo esc_attr( $inputmode ); ?>"
					autocomplete="<?php echo esc_attr( isset( $autocomplete ) ? $autocomplete : 'on' ); ?>"
				<?php endif; ?>
			/>
		</div>

		<?php
		if ( $echo ) {
			//phpcs:ignore WordPress.Security.EscapeOutput.OutputNotEscaped
			echo ob_get_clean();
		} else {
			return ob_get_clean();
		}
	}

	public static function fmp_minicart_quantity( $_product, $cart_item_key, $cart_item ) {
		if ( $_product->is_sold_individually() ) {
			$min_quantity = 1;
			$max_quantity = 1;
		} else {
			$min_quantity = 0;
			$max_quantity = $_product->get_max_purchase_quantity();
		}

		$product_quantity = self::fmp_woocommerce_quantity_input(
			[
				'input_name'   => "cart[{$cart_item_key}][qty]",
				'input_value'  => sprintf( '%02d', $cart_item['quantity'] ),
				'max_value'    => $max_quantity,
				'min_value'    => $min_quantity,
				'product_name' => $_product->get_name(),
			],
			$_product,
			false
		);

		?>
		<button type="button" class="button decrement">
			<svg width="8" height="2" viewBox="0 0 8 2" fill="none"
				 xmlns="http://www.w3.org/2000/svg">
				<rect width="8" height="2" rx="1" fill="#323846"/>
			</svg>
		</button>
		<?php echo apply_filters( 'fmp_woocommerce_cart_item_quantity', $product_quantity, $cart_item_key, $cart_item ); //phpcs:ignore ?>
		<button type="button" class="button increment">
			<svg width="8" height="8" viewBox="0 0 8 8" fill="none"
				 xmlns="http://www.w3.org/2000/svg">
				<path fill-rule="evenodd" clip-rule="evenodd"
					  d="M4 0C4.21217 0 4.41566 0.0842856 4.56569 0.234315C4.71571 0.384344 4.8 0.587827 4.8 0.8V3.2H7.2C7.41217 3.2 7.61566 3.28429 7.76569 3.43431C7.91571 3.58434 8 3.78783 8 4C8 4.21217 7.91571 4.41566 7.76569 4.56569C7.61566 4.71571 7.41217 4.8 7.2 4.8H4.8V7.2C4.8 7.41217 4.71571 7.61566 4.56569 7.76569C4.41566 7.91571 4.21217 8 4 8C3.78783 8 3.58434 7.91571 3.43431 7.76569C3.28429 7.61566 3.2 7.41217 3.2 7.2V4.8H0.8C0.587827 4.8 0.384344 4.71571 0.234315 4.56569C0.0842856 4.41566 0 4.21217 0 4C0 3.78783 0.0842856 3.58434 0.234315 3.43431C0.384344 3.28429 0.587827 3.2 0.8 3.2H3.2V0.8C3.2 0.587827 3.28429 0.384344 3.43431 0.234315C3.58434 0.0842856 3.78783 0 4 0Z"
					  fill="#323846"/>
			</svg>
		</button>
		<?php
	}

	/**
	 * Minicart extra fields markup.
	 *
	 * @param $_product
	 *
	 * @return void
	 */
	public static function fmp_minicart_extra_fields() {
		foreach ( WC()->cart->get_coupons() as $code => $coupon ) :
			?>
			<tr class="cart-discount coupon-<?php echo esc_attr( sanitize_title( $code ) ); ?>">
				<th><?php wc_cart_totals_coupon_label( $coupon ); ?></th>
				<td data-title="<?php echo esc_attr( wc_cart_totals_coupon_label( $coupon, false ) ); ?>"><?php wc_cart_totals_coupon_html( $coupon ); ?></td>
			</tr>
		<?php endforeach; ?>

		<?php if ( WC()->cart->get_shipping_total() && 0 != WC()->cart->get_shipping_total() ) : ?>
			<tr class="order-total">
				<th><?php esc_html_e( 'Shipping', 'woocommerce' ); ?></th>
				<td data-title="<?php esc_attr_e( 'Shipping', 'woocommerce' ); ?>"><?php echo wc_price( WC()->cart->get_shipping_total() ); //phpcs:ignore ?></td>
			</tr>
		<?php endif; ?>

		<?php foreach ( WC()->cart->get_fees() as $fee ) : ?>
			<tr class="fee">
				<th><?php echo esc_html( $fee->name ); ?></th>
				<td data-title="<?php echo esc_attr( $fee->name ); ?>"><?php wc_cart_totals_fee_html( $fee ); ?></td>
			</tr>
		<?php endforeach; ?>

		<?php
		if ( wc_tax_enabled() && ! WC()->cart->display_prices_including_tax() ) {
			$taxable_address = WC()->customer->get_taxable_address();
			$estimated_text  = '';

			if ( WC()->customer->is_customer_outside_base() && ! WC()->customer->has_calculated_shipping() ) {
				/* translators: %s location. */
				$estimated_text = sprintf( ' <small>' . esc_html__( '(estimated for %s)', 'woocommerce' ) . '</small>', WC()->countries->estimated_for_prefix( $taxable_address[0] ) . WC()->countries->countries[ $taxable_address[0] ] );
			}

			if ( 'itemized' === get_option( 'woocommerce_tax_total_display' ) ) {
				foreach ( WC()->cart->get_tax_totals() as $code => $tax ) { // phpcs:ignore WordPress.WP.GlobalVariablesOverride.Prohibited
					?>
					<tr class="tax-rate tax-rate-<?php echo esc_attr( sanitize_title( $code ) ); ?>">
						<th><?php echo esc_html( $tax->label ) . $estimated_text; // phpcs:ignore WordPress.Security.EscapeOutput.OutputNotEscaped ?></th>
						<td data-title="<?php echo esc_attr( $tax->label ); ?>"><?php echo wp_kses_post( $tax->formatted_amount ); ?></td>
					</tr>
					<?php
				}
			} else {
				?>
				<tr class="tax-total">
					<th><?php echo esc_html( WC()->countries->tax_or_vat() ) . $estimated_text; // phpcs:ignore WordPress.Security.EscapeOutput.OutputNotEscaped ?></th>
					<td data-title="<?php echo esc_attr( WC()->countries->tax_or_vat() ); ?>"><?php wc_cart_totals_taxes_total_html(); ?></td>
				</tr>
				<?php
			}
		}
		?>

		<tr class="order-total">
			<th><?php esc_html_e( 'Total', 'woocommerce' ); ?></th>
			<td data-title="<?php esc_attr_e( 'Total', 'woocommerce' ); ?>"><?php wc_cart_totals_order_total_html(); ?></td>
		</tr>
		<?php
	}

	/**
	 * Load minicart template by ajax
	 *
	 * @return void
	 */
	public function fmp_clear_cart_items() {

		if ( ! Fns::verifyNonce() ) {
			wp_send_json_error();
		}

		WC()->cart->empty_cart();
		wp_die();
	}
}




