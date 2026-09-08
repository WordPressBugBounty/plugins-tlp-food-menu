<?php
// phpcs:disable WordPress.NamingConventions.PrefixAllGlobals -- Established plugin: public namespace, hook names, functions and theme-overridable template variables must stay unchanged for backward compatibility.
/**
 * Activation & Deactivation actions.
 *
 * @package RT_FoodMenu
 */

namespace RT\FoodMenu\Helpers;

// Do not allow directly accessing this file.
if ( ! defined( 'ABSPATH' ) ) {
	exit( 'This script cannot be accessed directly.' );
}

/**
 * Activation & Deactivation actions.
 */
class Install {

	/**
	 * Marks the one-time "which menu type should a new install start on" decision.
	 *
	 * @var string
	 */
	const SEEDED_OPTION = 'tlp_fm_menu_type_seeded';

	/**
	 * Activation actions.
	 *
	 * @return void
	 */
	public static function activate() {
		$get_activation_time = strtotime( 'now' );

		add_option( 'rtfm_plugin_activation_time', $get_activation_time );
		add_option( 'rtfm_activation_redirect', true );

		\flush_rewrite_rules();
	}

	/**
	 * Start a brand-new install on Online Ordering when WooCommerce is already there.
	 *
	 * The stored default is "Food Menu Post Type" — a display-only menu. Someone who
	 * already runs WooCommerce almost always wants the ordering side, and landing on
	 * the display-only type reads as "the cart and checkout features are missing"
	 * rather than as a setting they have yet to pick.
	 *
	 * Runs on `admin_init`, not on the activation hook. The activation hook fires
	 * exactly once and only if this code was already present when the button was
	 * clicked — activate first and update later, activate via WP-CLI or a host's
	 * bulk installer, or hit the sandboxed activation path, and the seeding is simply
	 * lost with no second chance. `admin_init` runs on the first admin page load
	 * whatever route the install took, and `SEEDED_OPTION` keeps it to one decision
	 * ever, which is why it is safe to run on every request.
	 *
	 * Existing sites — including the many running the display-only type on purpose —
	 * must never be switched, so nothing is written unless **all** of these hold:
	 *
	 *   1. `SEEDED_OPTION` is absent, i.e. this decision has not been made before.
	 *   2. The settings option does not exist at all. It is created the first time
	 *      settings are saved, so its presence means the site has been configured —
	 *      this is what protects every existing install.
	 *   3. `fm_food_menu_type` is not already stored, so a saved choice always wins.
	 *   4. The site has no `food-menu` posts — a site with menu items on it is not a
	 *      new install, whatever its options happen to say. The backstop for an old
	 *      install that somehow never saved a setting.
	 *
	 * The marker is written whichever way the decision goes, so a site that had no
	 * WooCommerce at install time is not silently converted the day it adds one.
	 *
	 * Nothing is written when WooCommerce is missing: the `?? 'food_menu_post'`
	 * fallbacks throughout the plugin already cover that, and an absent key stays
	 * meaningful as "never chosen".
	 *
	 * @return void
	 */
	public static function maybe_seed_menu_type() {
		if ( get_option( self::SEEDED_OPTION ) ) {
			return;
		}

		// Decide once, whatever the outcome. Autoloaded, since the guard above then
		// reads it on every admin request and must not cost a query to do so.
		update_option( self::SEEDED_OPTION, 1, true );

		if ( ! self::is_woocommerce_active() ) {
			return;
		}

		$option_key = TLPFoodMenu()->options['settings'];
		$settings   = get_option( $option_key, null );

		// Option exists at all => this site has saved settings before.
		if ( null !== $settings ) {
			return;
		}

		if ( self::has_existing_menu_items() ) {
			return;
		}

		update_option( $option_key, [ 'fm_food_menu_type' => 'online_ordering' ] );
	}

	/**
	 * Whether this site already holds Food Menu items.
	 *
	 * The last line of defence for deciding "is this a new install": a site with menu
	 * items on it is an existing user, whatever its options happen to say. Queried
	 * directly rather than through `WP_Query` because the `food-menu` post type is
	 * registered on `init` and has not been registered yet during activation.
	 *
	 * @return bool
	 */
	private static function has_existing_menu_items() {
		global $wpdb;

		$found = $wpdb->get_var(
			$wpdb->prepare(
				"SELECT ID FROM {$wpdb->posts} WHERE post_type = %s AND post_status != 'auto-draft' LIMIT 1",
				TLPFoodMenu()->post_type
			)
		);

		return ! empty( $found );
	}

	/**
	 * Whether WooCommerce is available to this install.
	 *
	 * Checks the active-plugins list as well as the class, because activation hooks
	 * can run before WooCommerce has loaded — during a bulk activation, or a WP-CLI
	 * `plugin activate` naming both plugins — and the class check alone would then
	 * report a WooCommerce store as not having WooCommerce.
	 *
	 * @return bool
	 */
	private static function is_woocommerce_active() {
		if ( class_exists( 'WooCommerce' ) ) {
			return true;
		}

		$active = (array) get_option( 'active_plugins', [] );

		if ( is_multisite() ) {
			$active = array_merge( $active, array_keys( (array) get_site_option( 'active_sitewide_plugins', [] ) ) );
		}

		foreach ( $active as $plugin ) {
			if ( 'woocommerce/woocommerce.php' === $plugin ) {
				return true;
			}
		}

		return false;
	}

	/**
	 * Deactivation actions.
	 *
	 * @return void
	 */
	public static function deactivate() {
		\flush_rewrite_rules();
	}
}
