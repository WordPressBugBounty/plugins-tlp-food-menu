<?php
// phpcs:disable WordPress.NamingConventions.PrefixAllGlobals -- Established plugin: public namespace, hook names, functions and theme-overridable template variables must stay unchanged for backward compatibility.
/**
 * Black Friday Notice Class.
 *
 * @package RT_FoodMenu
 */

namespace RT\FoodMenu\Controllers\Admin\Notices;

use RT\FoodMenu\Helpers\Fns;

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
		if ( ! Fns::is_black_friday_active() ) {
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
		if ( ! current_user_can( 'manage_options' ) ) {
			return;
		}

		self::notice();
	}

	/**
	 * Render Notice
	 *
	 * @return void
	 */
	private static function notice() {

		add_action(
			'admin_notices',
			function () {
				$plugin_name   = 'Food Menu Pro';
				$download_link = 'https://www.radiustheme.com/downloads/food-menu-pro-wordpress/?utm_source=Food_menu_dashboard&utm_medium=side_banner&utm_campaign=free';
				?>
				<style>
					.rtfm-black-friday-notice.notice { position: relative; margin: 5px 0 15px; padding: 0 !important; border: 0; border-radius: 10px; overflow: hidden; box-shadow: 0 1px 2px rgba(16, 24, 40, .06), 0 4px 12px rgba(16, 24, 40, .06); }
					.rtfm-black-friday-notice .notice-dismiss { top: 50%; right: 8px; transform: translateY(-50%); width: 28px; height: 28px; padding: 0; border-radius: 50%; background: rgba(255, 255, 255, .95); justify-content: center; align-items: center; }
					.rtfm-black-friday-notice .notice-dismiss:before { color: #1d2327; }
					.rtfm-black-friday-notice .notice-dismiss:hover:before,
					.rtfm-black-friday-notice .notice-dismiss:focus:before { color: #d63638; }
				</style>
				<div class="notice notice-info is-dismissible rtfm-black-friday-notice">
					<a href="<?php echo esc_url( $download_link ); ?>" style="display: block" target="_blank">
						<img alt="<?php echo esc_attr( $plugin_name . ' Black Friday Sale' ); ?>"
							style="width: 100%;display: block;min-height: 30px;object-fit: cover"
							src="<?php echo esc_url( TLPFoodMenu()->assets_url() . 'images/admin/black-friday-banner.webp' ); ?>"/>
					</a>
				</div>
				<?php
			},
			1
		);

		add_action(
			'admin_footer',
			function () {
				?>
				<script>
					document.addEventListener('click', function (e) {
						const button = e.target.closest('.rtfm-black-friday-notice .notice-dismiss');
						if (!button || typeof ajaxurl === 'undefined') {
							return;
						}
						e.preventDefault();

						fetch(ajaxurl, {
							method: 'POST',
							headers: {
								'Content-Type': 'application/x-www-form-urlencoded; charset=UTF-8',
							},
							body: new URLSearchParams({
								'action': 'rtfm_dismiss_admin_notice',
								'nonce': <?php echo wp_json_encode( wp_create_nonce( 'rtfm-dismissible-notice' ) ); ?>
							}),
						});

						const notice = button.closest('.rtfm-black-friday-notice');
						if (notice) {
							notice.remove();
						}
					});
				</script>
				<?php
			}
		);

		add_action(
			'wp_ajax_rtfm_dismiss_admin_notice',
			function () {
				check_ajax_referer( 'rtfm-dismissible-notice', 'nonce' );

				if ( ! current_user_can( 'manage_options' ) ) {
					wp_die( '', '', 403 );
				}

				update_option( Fns::black_friday_dismiss_key(), '1', false );
				wp_die();
			}
		);
	}
}
