<?php
/**
 * Black Friday Notice Class.
 *
 * @package RT_FoodMenu
 */

namespace RT\FoodMenu\Controllers\Admin\Notices;

// Do not allow directly accessing this file.
if ( ! defined( 'ABSPATH' ) ) {
	exit( 'This script cannot be accessed directly.' );
}

/**
 * Black Friday Notice Class.
 */
class BlackFriday {
	use \RT\FoodMenu\Traits\SingletonTrait;

	/**
	 * Class Init.
	 *
	 * @return void
	 */
	protected function init() {
//		$this->remove_admin_notice();
		$current      = time();
		$black_friday = mktime( 0, 0, 0, 11, 13, 2025 ) <= $current && $current <= mktime( 0, 0, 0, 1, 15, 2026 );

		if ( ! $black_friday ) {
			return;
		}

		add_action( 'admin_init', [ $this, 'bf_notice' ] );
	}

	/**
	 * Removes all admin notices on the Food Menu settings page.
	 *
	 * This method hooks into the `in_admin_header` action to detect when the current
	 * admin screen is the TLP Food Menu settings page (post type screen with base
	 * `food-menu_page_food_menu_settings`). On that screen, it removes all actions
	 * hooked to `admin_notices` and `all_admin_notices` to prevent any core or
	 * plugin notices from displaying.
	 *
	 * ⚠ Note: `remove_all_actions()` is aggressive and will also remove WordPress
	 * core notices (updates, errors, warnings). Use carefully if you only want a
	 * clean UI on this specific settings page.
	 *
	 * @return void
	 */

	public function remove_admin_notice() {
		add_action(
			'in_admin_header',
			function () {
				$screen = get_current_screen();
				if (
					! empty( $screen->post_type )
					&& in_array( $screen->post_type, [ TLPFoodMenu()->post_type ], true )
					&& in_array(
						$screen->base,
						[
							'food-menu_page_food_menu_settings',
							'food-menu_page_rtfm_get_help',
						],
						true
					)
				) {
					remove_all_actions( 'admin_notices' );
					remove_all_actions( 'all_admin_notices' );
				}
			},
			1000
		);
	}

	/**
	 * Black Friday Notice.
	 *
	 * @return void|string
	 */
	public function bf_notice() {
		if ( get_option( 'rtfm_ny_2025' ) != '1' ) {
			if ( ! isset( $GLOBALS['rt_ny_2025_notice'] ) ) {
				$GLOBALS['rt_ny_2025_notice'] = 'rtfm_ny_2025';
				self::notice();
			}
		}
	}

	/**
	 * Render Notice
	 *
	 * @return void
	 */
	private static function notice() {

		add_action(
			'admin_enqueue_scripts',
			function () {
				wp_enqueue_script( 'jquery' );
			}
		);

		add_action(
			'admin_notices',
			function () {
				$plugin_name   = 'Food Menu Pro';
				$discount      = '40%';
				$download_link = 'https://www.radiustheme.com/downloads/food-menu-pro-wordpress/?utm_source=Food_menu_dashboard&utm_medium=side_banner&utm_campaign=free';
				?>

                <div class="notice notice-info is-dismissible" data-rtfmdismissable="rtfm_ny_2023" style="display:grid;grid-template-columns: 100px auto;padding-top: 25px; padding-bottom: 22px;">
                    <img alt="<?php echo esc_attr( $plugin_name ); ?>" src="<?php echo esc_url( TLPFoodMenu()->assets_url() ) . 'images/foodmenu.png'; ?>" width="74px" height="74px" style="grid-row: 1 / 4; align-self: center;justify-self: center"/>
                    <h3 style="margin:0;"><?php echo sprintf( '%s Black Friday - Up to <span style="color:#e60000;font-weight: 700;" class="red-color">%s</span> Sale 2025!!', esc_html( $plugin_name ), $discount ); ?></h3>
                    <p style="margin:0 0 2px;"><?php echo sprintf( '🚀 Exciting News: %s Black Friday sale is now live!', esc_html( $plugin_name ) ); ?>
                        Get the plugin today and enjoy discounts up to <b> 40%.</b>
                    </p>
                    <p style="margin:0;">
                        <a class="button button-primary" href="<?php echo esc_url( $download_link ); ?>" target="_blank">Buy Now</a>
                        <a class="button button-dismiss" href="#">Dismiss</a>
                    </p>
                </div>

				<?php
			}
		);

		add_action(
			'admin_footer',
			function () {
				?>
				<script type="text/javascript">
					(function ($) {
						$(function () {
							setTimeout(function () {
								$('div[data-rtfmdismissable] .notice-dismiss, div[data-rtfmdismissable] .button-dismiss')
									.on('click', function (e) {
										e.preventDefault();
										$.post(ajaxurl, {
											'action': 'rtfm_dismiss_admin_notice',
											'nonce': <?php echo wp_json_encode( wp_create_nonce( 'rtfm-dismissible-notice' ) ); ?>
										});
										$(e.target).closest('.is-dismissible').remove();
									});
							}, 1000);
						});
					})(jQuery);
				</script>
				<?php
			}
		);

		add_action(
			'wp_ajax_rtfm_dismiss_admin_notice',
			function () {
				check_ajax_referer( 'rtfm-dismissible-notice', 'nonce' );

				update_option( 'rtfm_ny_2025', '1' );
				wp_die();
			}
		);
	}
}
