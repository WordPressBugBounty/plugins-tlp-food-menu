<?php
// phpcs:disable WordPress.NamingConventions.PrefixAllGlobals -- Established plugin: public namespace, hook names, functions and theme-overridable template variables must stay unchanged for backward compatibility.
/**
 * Special Menu controller.
 *
 * Renders the time-limited "special menu" promo popup in the footer. Gated by
 * the `fmp_enable_menu` setting + an end-date check; respects per-page exclusion
 * via `fmp_special_menu_exclude_pages`. The actual show/hide JS (delay,
 * close-behavior cookie/localStorage) lives in `src/js/foodmenu.js`.
 *
 * @package RT_FoodMenu
 */

namespace RT\FoodMenu\Controllers\SpecialMenu;

use RT\FoodMenu\Helpers\Fns;
use RT\FoodMenu\Traits\SingletonTrait;

defined( 'ABSPATH' ) || exit();

//phpcs:disable PluginCheck.CodeAnalysis.ImageFunctions.NonEnqueuedImage

/**
 * Special Menu Main class.
 */
class SpecialMenu {

	use SingletonTrait;

	/**
	 * Admin settings.
	 *
	 * @var array
	 */
	private $options;

	/**
	 * Init function call hooks.
	 *
	 * @return void
	 */
	public function init() {
		$this->options = Fns::get_settings_option();
		$enable_menu   = $this->options['fmp_enable_menu'] ?? '';
		$fm_type       = $this->options['fm_food_menu_type'] ?? 'food_menu_post';

		if ( TLPFoodMenu()->isWcActive() && 'on' === $enable_menu && 'online_ordering' === $fm_type ) {
			add_action( 'wp_enqueue_scripts', [ $this, 'frontend_enqueue_assets' ], 99 );
			add_action( 'admin_enqueue_scripts', [ $this, 'admin_enqueue_assets' ] );
			add_action( 'wp_footer', [ $this, 'render_special_menu_popup' ] );
		}
	}

	/**
	 * Frontend enqueue — output dynamic colour/dimension overrides.
	 *
	 * @return void
	 */
	public function frontend_enqueue_assets() {
		$opt         = $this->options;
		$dynamic_css = '';

		$pre = '#fmp-special-menu-modal';

		if ( ! empty( $opt['fmp_menu_modal_width'] ) ) {
			$dynamic_css .= "$pre .fmp-menu-popup-modal .modal-content {width:{$opt['fmp_menu_modal_width']}px}";
		}

		if ( ! empty( $opt['fmp_menu_modal_height'] ) ) {
			$dynamic_css .= "$pre .fmp-menu-popup-modal .modal-content .right-content {height:{$opt['fmp_menu_modal_height']}px}";
		}

		if ( ! empty( $opt['fmp_menu_modal_bg'] ) ) {
			$dynamic_css .= "$pre .fmp-menu-popup-modal .modal-content {background:{$opt['fmp_menu_modal_bg']}}";
		}

		if ( ! empty( $opt['fmp_menu_modal_title_color'] ) ) {
			$dynamic_css .= "$pre .fmp-menu-popup-modal .left-content .title {color:{$opt['fmp_menu_modal_title_color']}}";
		}

		if ( ! empty( $opt['fmp_menu_modal_btn_bg'] ) ) {
			$dynamic_css .= "$pre .fmp-menu-popup-modal a.fmp-btn.special-menu-btn::before {background:{$opt['fmp_menu_modal_btn_bg']}}";
		}

		if ( ! empty( $opt['fmp_menu_modal_btn_bg_hover'] ) ) {
			$dynamic_css .= "$pre .fmp-menu-popup-modal a.fmp-btn.special-menu-btn::after {background:{$opt['fmp_menu_modal_btn_bg_hover']}}";
		}

		if ( ! empty( $dynamic_css ) ) {
			wp_add_inline_style( 'fm-frontend', $dynamic_css );
		}
	}

	/**
	 * Admin enqueue — flatpickr for the popup end-date picker.
	 *
	 * @return void
	 */
	public function admin_enqueue_assets() {
		wp_enqueue_script( 'fmp-flatpickr' );
		wp_enqueue_style( 'fmp-flatpickr' );
	}

	/**
	 * Special menu popup.
	 *
	 * @return void
	 */
	public function render_special_menu_popup() {
		$settings            = $this->options;
		$menu_popup_duration = $settings['fmp_menu_popup_duration'] ?? '';
		$now_date            = gmdate( TLP_FOOD_MENU_DEFAULT_DATE_FORMAT );
		if ( empty( $menu_popup_duration ) || strtotime( $now_date ) > strtotime( $menu_popup_duration ) ) {
			return;
		}

		// Hide on WooCommerce My Account (and its sub-endpoints).
		if ( function_exists( 'is_account_page' ) && is_account_page() ) {
			return;
		}

		$exclude_pages = ! empty( $settings['fmp_special_menu_exclude_pages'] ) ? (array) $settings['fmp_special_menu_exclude_pages'] : [];

		// Per-page exclusion is a Pro feature. When Pro is not active we
		// discard whatever the user previously saved so a downgrade can't
		// silently keep the promo hidden on configured pages.
		if ( ! TLPFoodMenu()->has_pro() ) {
			$exclude_pages = [];
		}

		// Filter so Pro (or any other plugin) can append additional pages —
		// e.g. Pro's frontend-order-dashboard page that shouldn't show the popup.
		$exclude_pages = (array) apply_filters( 'fmp/special_menu/exclude_pages', $exclude_pages, $settings );

		if ( ! empty( $exclude_pages ) && is_singular() ) {
			$current_id    = get_queried_object_id();
			$exclude_pages = array_map( 'intval', $exclude_pages );
			if ( in_array( $current_id, $exclude_pages, true ) ) {
				return;
			}
		}

		$fmp_menu_title       = $settings['fmp_menu_title'] ?? '';
		$fmp_menu_offer       = $settings['fmp_menu_offer'] ?? '';
		$fmp_menu_duration    = $settings['fmp_menu_duration'] ?? '';
		$fmp_special_menus    = (array) ( $settings['fmp_special_menus'] ?? [] );
		$fmp_menu_button_text = $settings['fmp_menu_button_text'] ?? '';
		$fmp_menu_button_link = $settings['fmp_menu_button_link'] ?? '';
		$close_behavior       = $settings['fmp_menu_close_behavior'] ?? '24';

		// Preset / visual style — 1 is the classic layout (free), 2-4 are
		// Pro-only variants. Whitelist guard rejects junk values; Pro guard
		// forces preset 1 whenever Pro is inactive, so a saved Pro preset
		// gracefully degrades when the user downgrades.
		$preset = (string) ( $settings['fmp_menu_preset'] ?? '1' );
		if ( ! in_array( $preset, [ '1', '2', '3', '4' ], true ) ) {
			$preset = '1';
		}
		if ( '1' !== $preset && ! TLPFoodMenu()->has_pro() ) {
			$preset = '1';
		}
		?>

		<div id="fmp-special-menu-modal" class="fmp-menu-popup fmp-menu-popup--style-<?php echo esc_attr( $preset ); ?>"
             data-close-behavior="<?php echo esc_attr( $close_behavior ); ?>">
			<div class="fmp-menu-popup-modal">
				<div class="modal-content">
					<div class="modal-row">
						<div class="modal-col-5 col-12">
							<div class="left-content">
								<h3 class="title"><?php echo esc_html( $fmp_menu_title ); ?></h3>

								<?php if ( ! empty( $fmp_menu_offer ) ) : ?>
									<h4 class="subtitle"><?php echo esc_html( $fmp_menu_offer ); ?></h4>
								<?php endif; ?>

								<?php if ( ! empty( $fmp_menu_duration ) ) : ?>
									<p class="description"><?php echo esc_html( $fmp_menu_duration ); ?></p>
								<?php endif; ?>

								<?php if ( ! empty( $fmp_menu_button_link ) && ! empty( $fmp_menu_button_text ) ) : ?>
									<a class="fmp-btn special-menu-btn" href="<?php echo esc_url( $fmp_menu_button_link ); ?>">
										<?php echo esc_html( $fmp_menu_button_text ); ?>
									</a>
								<?php endif; ?>
							</div>
						</div>
						<div class="modal-col-6 col-12">
							<div class="right-content">
								<?php foreach ( $fmp_special_menus as $special_menu ) : ?>
									<a href="<?php echo esc_url( get_permalink( $special_menu ) ); ?>" class="modal-product">
										<div class="product-img">
											<?php
											$thumb_url = get_the_post_thumbnail_url( $special_menu, 'medium' );
											if ( ! $thumb_url ) {
												$thumb_url = function_exists( 'wc_placeholder_img_src' ) ? wc_placeholder_img_src( 'medium' ) : '';
											}
											?>
											<img src="<?php echo esc_url( $thumb_url ); ?>" alt="<?php echo esc_attr( get_the_title( $special_menu ) ); ?>">
										</div>
										<div class="product-title">
											<h3 class="title"><?php echo esc_html( get_the_title( $special_menu ) ); ?></h3>
										</div>
									</a>
								<?php endforeach; ?>
							</div>
						</div>
					</div>
                    <button class="fmp-btn menu-popup-close" aria-label="Close">
                        <svg xmlns="http://www.w3.org/2000/svg" width="18" height="18" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><line x1="18" y1="6" x2="6" y2="18"></line><line x1="6" y1="6" x2="18" y2="18"></line></svg>
                    </button>
				</div>
			</div>
		</div>

		<?php
	}
}
