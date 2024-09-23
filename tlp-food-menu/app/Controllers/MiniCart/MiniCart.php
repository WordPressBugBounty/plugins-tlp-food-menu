<?php
/**
 * Main MiniCart class.
 *
 * @package RT_FoodMenu
 */

namespace RT\FoodMenu\Controllers\MiniCart;

use RT\FoodMenu\Helpers\Fns;
use RT\FoodMenu\Traits\SingletonTrait;

defined( 'ABSPATH' ) || exit();

/**
 * Main FilterHooks class.
 */
class MiniCart {
	/**
	 * Singleton Trait.
	 */
	use SingletonTrait;

	/**
	 * @var array|mixed
	 */
	private $options;

	/**
	 * Class constructor
	 */
	public function init() {
		$this->options    = Fns::get_settings_option();
		$enable_mini_cart = $this->options['enable_mini_cart'] ?? 'on';
		if ( TLPFoodMenu()->isWcActive() && ! empty( $enable_mini_cart ) && 'on' == $enable_mini_cart ) {
			MiniCartHooks::get_instance();
			add_action( 'wp_enqueue_scripts', [ $this, 'enqueue_public_scripts' ], 99 );
			add_action( 'wp_footer', [ $this, 'render' ] );
		}
	}


	/**
	 * Public CSS
	 *
	 * @return void
	 */
	public function enqueue_public_scripts() {
		$opt                       = $this->options;
		$dynamic_css               = '';
		$mini_cart_custom_selector = $opt['mini_cart_custom_selector'] ?? '';
		$nonce                     = wp_create_nonce( Fns::nonceText() );

		// woocommerce js file.
		wp_enqueue_script( 'wc-cart-fragments' );
		// mini cart js.
		wp_enqueue_script( 'fm-ajax-minicart' );
		\wp_localize_script(
			'fm-ajax-minicart',
			'fmpMiniCartParamsPro',
			[
				'nonceID'                => esc_attr( Fns::nonceId() ),
				'nonce'                  => esc_attr( $nonce ),
				'ajaxurl'                => esc_url( admin_url( 'admin-ajax.php' ) ),
				'miniCartCustomSelector' => $mini_cart_custom_selector,
			]
		);

		// Float button style.
		$pre = '#fmp-cart-float-menu';
		if ( ! empty( $opt['mini_cart_float_btn_radius'] ) ) {
			$dynamic_css .= "$pre {border-radius:{$opt['mini_cart_float_btn_radius']};overflow:hidden}";
		}
		if ( ! empty( $opt['mini_cart_float_bg'] ) ) {
			$dynamic_css .= "{$pre} {--fmp-float-bg:{$opt['mini_cart_float_bg']};}";
		}
		if ( ! empty( $opt['mini_cart_float_bg_hover'] ) ) {
			$dynamic_css .= "{$pre} {--fmp-float-bg-hover:{$opt['mini_cart_float_bg_hover']};}";
		}
		if ( ! empty( $opt['mini_cart_btn_width'] ) ) {
			$dynamic_css .= "$pre {min-width:{$opt['mini_cart_btn_width']}px}";
		}

		// MiniCart Wrapper.
		if ( ! empty( $opt['mini_cart_primary'] ) ) {
			$dynamic_css .= "body {--fmp-drawer-primary:{$opt['mini_cart_primary']}}";
		}

		if ( ! empty( $opt['mini_cart_secondary'] ) ) {
			$dynamic_css .= "body {--fmp-drawer-secondary:{$opt['mini_cart_secondary']}}";
		}

		if ( ! empty( $dynamic_css ) ) {
			wp_add_inline_style( 'fmp-frontend', $dynamic_css );
		}
	}


	/**
	 * Mini cart markup
	 *
	 * @return void
	 */
	public function render() {
		add_filter( 'woocommerce_widget_cart_is_hidden', '__return_true' );

		$opt = $this->options;

		$count_label          = WC()->cart->get_cart_contents_count() < 2 ? __( 'Item', 'food-menu-pro' ) : __( 'Items', 'food-menu-pro' );
		$cart_drawer_classes  = ' ' . ( $opt['mini_cart_drawer_style'] ?? 'style1' );
		$cart_drawer_classes .= ' ' . ( $opt['mini_cart_open_style'] ?? 'open-always' );

		$fmp_float_classes  = ' ' . ( $opt['mini_cart_position'] ?? 'left_center' );
		$fmp_float_classes .= ' ' . ( $opt['mini_cart_float_btn_style'] ?? 'style1' );
		$show_on_mobile     = $opt['mini_cart_show_on_mobile'] ?? 'on';
		if ( empty( $show_on_mobile ) && 'on' !== $show_on_mobile ) {
			$fmp_float_classes   .= ' fmp-hide-mobile';
			$cart_drawer_classes .= ' fmp-hide-mobile';
		}

		$default_image_path = TLPFoodMenu()->assets_url() . 'images/mini-cart-loading.gif';
		if ( ! empty( $opt['mini_cart_loading_image'] ) ) {
			$loading_json    = json_decode( stripslashes( $opt['mini_cart_loading_image'] ), true );
			$loading_img_src = $loading_json['source'] ?? $default_image_path;
		} else {
			$loading_img_src = $default_image_path;
		}

		$has_ovelay = Fns::get_options_by_default_val( $opt, 'mini_cart_overlay_visibility', 'on' );
		?>
		<div id="fmp-cart-float-menu" class="fmp-cart-float-menu <?php echo esc_attr( $fmp_float_classes ); ?>">
			<div class="fmp-cart-float-inner">
				<span class="cart-icon">
					<span class="cart-icon-svg"></span>
					<span class="cart-number-wrapper">
						<span class="fmp-cart-icon-num">
							<?php Fns::print_html( WC()->cart->get_cart_contents_count() ); ?>
						</span>
						<span class="item-label"><?php echo esc_html( $count_label ); ?></span>
					</span>
				</span>
				<span class="fmp-cart-icon-total">
					<?php echo wc_price( WC()->cart->get_cart_contents_total() ); //phpcs:ignore ?>
				</span>
			</div>
		</div>

		<!-- Minicart Drawer -->
		<div class="fmp-drawer-container fmp-minicart-drawer <?php echo esc_attr( $cart_drawer_classes ); ?>">
			<span class="close"></span>
			<div id="fmp-side-content-area-id">
				<img class="loading-cart" src="<?php echo esc_url( $loading_img_src ); ?>" alt="<?php echo esc_attr__( 'Loadding...', 'food-menu-pro' ); ?>">
			</div>
		</div>

		<?php if ( 'on' === $has_ovelay ) : ?>
			<div class="drawer-overlay"></div>
			<?php
		endif;
	}
}
