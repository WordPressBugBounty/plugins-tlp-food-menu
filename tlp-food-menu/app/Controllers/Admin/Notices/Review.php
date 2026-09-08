<?php
// phpcs:disable WordPress.NamingConventions.PrefixAllGlobals -- Established plugin: public namespace, hook names, functions and theme-overridable template variables must stay unchanged for backward compatibility.
/**
 * Review Notice Class.
 *
 * @package RT_FoodMenu
 */

namespace RT\FoodMenu\Controllers\Admin\Notices;

// Do not allow directly accessing this file.
if ( ! defined( 'ABSPATH' ) ) {
	exit( 'This script cannot be accessed directly.' );
}

/**
 * Review Notice Class.
 */
class Review {
	use \RT\FoodMenu\Traits\SingletonTrait;

	/**
	 * Class Init.
	 *
	 * @return void
	 */
	protected function init() {
		add_action( 'admin_init', [ __CLASS__, 'check_installation_time' ] );
		add_action( 'admin_init', [ __CLASS__, 'spare_me' ], 5 );
	}


	/**
	 * Check if review notice should be shown or not
	 *
	 * @return void
	 */
	public static function check_installation_time() {

		$nobug = get_option( 'rtfm_spare_me', '0' );

		// Dismissed permanently ("No thanks") or already rated — never show again.
		if ( '1' === $nobug || '3' === $nobug ) {
			return;
		}

		$now          = strtotime( 'now' );
		$install_date = (int) get_option( 'rtfm_plugin_activation_time' );

		// Safety net: if the activation time was never stored, start the clock now
		// so a fresh site never gets the notice immediately.
		if ( ! $install_date ) {
			$install_date = $now;
			update_option( 'rtfm_plugin_activation_time', $install_date );
		}

		// User clicked "Maybe later": show again 15 days after that click.
		if ( '2' === $nobug ) {
			$remind_time = (int) get_option( 'rtfm_remind_me' );

			if ( $remind_time && $now >= strtotime( '+15 days', $remind_time ) ) {
				add_action( 'admin_notices', [ __CLASS__, 'display_admin_notice' ] );
			}

			return;
		}

		// First-time prompt: only after the plugin has been active for 15 days.
		if ( $now >= strtotime( '+15 days', $install_date ) ) {
			add_action( 'admin_notices', [ __CLASS__, 'display_admin_notice' ] );
		}
	}

	/**
	 * Remove the notice for the user if review already done or if the user does not want to
	 *
	 * @return void
	 */
	public static function spare_me() {
		$nonce = ! empty( $_REQUEST['_wpnonce'] ) ? sanitize_text_field( wp_unslash( $_REQUEST['_wpnonce'] ) ) : null;

		if ( ! wp_verify_nonce( $nonce, 'rtfm_notice_nonce' ) ) {
			return;
		}

		if ( isset( $_GET['rtfm_spare_me'] ) && ! empty( $_GET['rtfm_spare_me'] ) ) {
			$spare_me = absint( $_GET['rtfm_spare_me'] );

			if ( 1 == $spare_me ) {
				update_option( 'rtfm_spare_me', '1' );
			}
		}

		if ( isset( $_GET['rtfm_remind_me'] ) && ! empty( $_GET['rtfm_remind_me'] ) ) {
			$remind_me = absint( $_GET['rtfm_remind_me'] );

			if ( 1 == $remind_me ) {
				$get_activation_time = strtotime( 'now' );

				update_option( 'rtfm_remind_me', $get_activation_time );
				update_option( 'rtfm_spare_me', '2' );
			}
		}

		if ( isset( $_GET['rtfm_rated'] ) && ! empty( $_GET['rtfm_rated'] ) ) {
			$rtfm_rated = absint( $_GET['rtfm_rated'] );

			if ( 1 == $rtfm_rated ) {
				update_option( 'rtfm_rated', 'yes' );
				update_option( 'rtfm_spare_me', '3' );
			}
		}
	}

	/**
	 * Current admin URL
	 *
	 * @return string
	 */
	protected static function current_admin_url() {
		$uri = isset( $_SERVER['REQUEST_URI'] ) ? esc_url_raw( wp_unslash( $_SERVER['REQUEST_URI'] ) ) : '';
		$uri = preg_replace( '|^.*/wp-admin/|i', '', $uri );

		if ( ! $uri ) {
			return '';
		}

		return remove_query_arg( [ '_wpnonce', '_wc_notice_nonce', 'wc_db_update', 'wc_db_update_nonce', 'wc-hide-notice' ], admin_url( $uri ) );
	}

	/**
	 * Display Admin Notice, asking for a review
	 **/
	public static function display_admin_notice() {
		// WordPress global variable.
		global $pagenow;

		$exclude = [ 'themes.php', 'users.php', 'tools.php', 'options-general.php', 'options-writing.php', 'options-reading.php', 'options-discussion.php', 'options-media.php', 'options-permalink.php', 'options-privacy.php', 'edit-comments.php', 'upload.php', 'media-new.php', 'admin.php', 'import.php', 'export.php', 'site-health.php', 'export-personal-data.php', 'erase-personal-data.php' ];

		if ( ! in_array( $pagenow, $exclude ) ) {
			$args         = [ '_wpnonce' => wp_create_nonce( 'rtfm_notice_nonce' ) ];
			$dont_disturb = add_query_arg( $args + [ 'rtfm_spare_me' => '1' ], self::current_admin_url() );
			$remind_me    = add_query_arg( $args + [ 'rtfm_remind_me' => '1' ], self::current_admin_url() );
			$rated        = add_query_arg( $args + [ 'rtfm_rated' => '1' ], self::current_admin_url() );
			$reviewurl    = 'https://wordpress.org/support/plugin/tlp-food-menu/reviews/#new-post';
			$logo         = TLPFoodMenu()->assets_url() . 'images/icon-128x128.svg';

			printf(
				'<div class="notice rtfm-review-notice">
					<div class="rtfm-review-notice__icon">
						<img class="rtfm-review-notice__icon-mark" src="%s" width="52" height="52" alt="Food Menu for WooCommerce" />
					</div>
					<div class="rtfm-review-notice__body">
						<div class="rtfm-review-notice__stars">★★★★★</div>
						<h3 class="rtfm-review-notice__title">Enjoying Food Menu for WooCommerce?</h3>
						<p class="rtfm-review-notice__text">We have poured a lot of care into building Food Menu &mdash; Restaurant Menu &amp; Online Ordering for WooCommerce. If it has made running your menu easier, a quick 5-star review on WordPress.org would mean the world to our team and help others discover it.</p>
						<div class="rtfm-review-notice__actions">
							<a href="%s" class="rtfm-review-button rtfm-review-button--primary" target="_blank" rel="noopener">⭐ Sure, you deserve it!</a>
							<a href="%s" class="rtfm-review-button rtfm-review-button--ghost">Already rated</a>
							<a href="%s" class="rtfm-review-button rtfm-review-button--ghost">Maybe later</a>
							<a href="%s" class="rtfm-review-button rtfm-review-button--link">No thanks</a>
						</div>
					</div>
				</div>',
				esc_url( $logo ),
				esc_url( $reviewurl ),
				esc_url( $rated ),
				esc_url( $remind_me ),
				esc_url( $dont_disturb )
			);

			echo '<style>
				.rtfm-review-notice {
					position: relative;
					display: flex;
					gap: 18px;
					margin: 16px 20px 16px 2px;
					padding: 22px 24px;
					border: 1px solid #e4e7ec;
					border-left: 4px solid #5d3dfd;
					border-radius: 10px;
					background: #fff;
					box-shadow: 0 4px 16px rgba(16, 24, 40, 0.06);
					font-family: -apple-system, BlinkMacSystemFont, "Segoe UI", Roboto, Helvetica, Arial, sans-serif;
				}
				.rtfm-review-notice.notice {
					padding: 22px 24px;
				}
				.rtfm-review-notice__icon {
					flex: 0 0 auto;
				}
				.rtfm-review-notice__icon-mark {
					display: block;
					width: 52px;
					height: 52px;
					border-radius: 12px;
					box-shadow: 0 6px 14px rgba(16, 24, 40, 0.18);
				}
				.rtfm-review-notice__body {
					flex: 1 1 auto;
					min-width: 0;
				}
				.rtfm-review-notice__stars {
					color: #f5a623;
					font-size: 16px;
					letter-spacing: 3px;
					line-height: 1;
					margin-bottom: 8px;
				}
				.rtfm-review-notice__title {
					margin: 0 0 6px;
					padding: 0;
					font-size: 18px;
					font-weight: 600;
					line-height: 1.3;
					color: #101828;
				}
				.rtfm-review-notice__text {
					margin: 0;
					padding: 0;
					max-width: 760px;
					font-size: 13px;
					line-height: 1.6;
					color: #475467;
				}
				.rtfm-review-notice__actions {
					display: flex;
					flex-wrap: wrap;
					align-items: center;
					gap: 10px;
					margin-top: 16px;
				}
				.rtfm-review-button {
					display: inline-flex;
					align-items: center;
					padding: 8px 16px;
					border-radius: 8px;
					font-size: 13px;
					font-weight: 600;
					line-height: 1;
					text-decoration: none;
					white-space: nowrap;
					transition: all 0.15s ease;
				}
				.rtfm-review-button--primary {
					background: linear-gradient(135deg, #6d4bff 0%, #8b5cf6 100%);
					color: #fff !important;
					box-shadow: 0 4px 12px rgba(93, 61, 253, 0.28);
					text-decoration: none !important;
				}
				.rtfm-review-button--primary:hover,
				.rtfm-review-button--primary:focus {
					transform: translateY(-1px);
					box-shadow: 0 6px 16px rgba(93, 61, 253, 0.36);
					color: #fff;
				}
				.rtfm-review-button--ghost {
					background: #f4f3ff;
					color: #5d3dfd;
					border: 1px solid #e0dbff;
					text-decoration: none !important;
				}
				.rtfm-review-button--ghost:hover,
				.rtfm-review-button--ghost:focus {
					background: #ece9ff;
					color: #4c2ff0;
				}
				.rtfm-review-button--link {
					background: transparent;
					color: #98a2b3;
				}
				.rtfm-review-button--link:hover,
				.rtfm-review-button--link:focus {
					color: #667085;
					text-decoration: underline;
				}
				.rtfm-review-button:focus {
					outline: 2px solid rgba(93, 61, 253, 0.4);
					outline-offset: 2px;
				}
				@media screen and (max-width: 782px) {
					.rtfm-review-notice {
						padding: 18px;
					}
					.rtfm-review-notice.notice {
						padding: 18px;
					}
					.rtfm-review-notice__icon {
						display: none;
					}
				}
			</style>';
		}
	}
}
