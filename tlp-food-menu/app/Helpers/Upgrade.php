<?php
// phpcs:disable WordPress.NamingConventions.PrefixAllGlobals -- Established plugin: public namespace, hook names, functions and theme-overridable template variables must stay unchanged for backward compatibility.
/**
 * Upgrade actions.
 *
 * @package RT_FoodMenu
 */

namespace RT\FoodMenu\Helpers;

// Do not allow directly accessing this file.
if ( ! defined( 'ABSPATH' ) ) {
	exit( 'This script cannot be accessed directly.' );
}

/**
 * Upgrade actions.
 */
class Upgrade {

	/**
	 * Check Plugins Version
	 *
	 * @return bool
	 */
	public static function check_plugin_version() {
		if ( ! defined( 'FOOD_MENU_PRO_VERSION' ) || version_compare( FOOD_MENU_PRO_VERSION, TLP_FOOD_MENU_REQUIRED_PRO_VERSION, '<' ) ) {
			self::notice();

			return false;
		}

		return true;
	}

	/**
	 * Admin Notice
	 *
	 * @return void
	 */
	public static function notice() {
		add_action(
			'admin_notices',
			function () {
				$link_pro = 'https://www.radiustheme.com/downloads/food-menu-pro-wordpress/';
				$version  = esc_html( TLP_FOOD_MENU_REQUIRED_PRO_VERSION );
				?>
				<div class="notice notice-error fmp-version-notice" style="border-left: 4px solid #d63638; background: #fff; padding: 16px 20px; margin: 5px 0 15px; box-shadow: 0 1px 4px rgba(0,0,0,.08); font-family: -apple-system, BlinkMacSystemFont, 'Segoe UI', Roboto, sans-serif;">
					<div style="display: flex; align-items: center; gap: 10px; margin-bottom: 10px;">
						<span style="display: inline-flex; align-items: center; justify-content: center; width: 28px; height: 28px; background: #fce4e4; border-radius: 50%;">
							<svg width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="#d63638" stroke-width="2.5" stroke-linecap="round" stroke-linejoin="round"><circle cx="12" cy="12" r="10"/><line x1="12" y1="8" x2="12" y2="12"/><line x1="12" y1="16" x2="12.01" y2="16"/></svg>
						</span>
						<strong style="font-size: 14px; color: #1d2327;">Food Menu Pro — Features Disabled</strong>
					</div>
					<p style="margin: 0 0 12px; color: #50575e; font-size: 13px; line-height: 1.6;">
						<strong>Food Menu Pro</strong> is not compatible with the current version of <strong>Food Menu</strong>.
						Please update <strong>Food Menu Pro</strong> to version <code style="background: #f0f0f1; padding: 2px 6px; border-radius: 3px; font-size: 12px;"><?php echo esc_html( $version ); ?></code> or higher to restore pro features.
					</p>
					<a href="<?php echo esc_url( $link_pro ); ?>" target="_blank" style="display: inline-block; padding: 6px 16px; background: #d63638; color: #fff; text-decoration: none; border-radius: 4px; font-size: 13px; font-weight: 500;">Update Food Menu Pro</a>
				</div>
				<?php
			}
		);
	}

	/**
	 * Deactivate plugin.
	 *
	 * @return void
	 */
	public static function deactivate() {
		require_once ABSPATH . 'wp-admin/includes/plugin.php';
		\deactivate_plugins( \plugin_basename( FOOD_MENU_PRO_PLUGIN_ACTIVE_FILE_NAME ) );

		unset( $_GET['activate'] ); // phpcs:ignore WordPress.Security.NonceVerification.Recommended
	}
}
