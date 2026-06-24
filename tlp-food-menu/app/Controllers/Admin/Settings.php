<?php
/**
 * Admin Settings Class.
 *
 * @package RT_FoodMenu
 */

namespace RT\FoodMenu\Controllers\Admin;

use RT\FoodMenu\Helpers\Fns;
use RT\FoodMenuPro\Helpers\FnsPro;

// Do not allow directly accessing this file.
if ( ! defined( 'ABSPATH' ) ) {
	exit( 'This script cannot be accessed directly.' );
}

/**
 * Admin Settings Class.
 */
class Settings {

	use \RT\FoodMenu\Traits\SingletonTrait;

	/**
	 * Class Init.
	 *
	 * @return void
	 */
	protected function init() {
		add_action( 'admin_menu', [ $this, 'register_admin_menu' ], 15 );
		add_action( 'admin_menu', [ $this, 'reorder_submenu' ], 999 );
		add_action( 'admin_init', [ $this, 'redirect' ] );
		add_action( 'in_admin_header', [ $this, 'remove_admin_notices' ], 99 );

		add_filter( 'plugin_row_meta', [ $this, 'plugin_row_meta' ], 10, 2 );
		add_filter( 'plugin_action_links_' . plugin_basename( TLP_FOOD_MENU_PLUGIN_ACTIVE_FILE_NAME ), [ $this, 'marketing' ] );
		add_filter( 'parent_file', [ $this, 'fix_food_location_parent_menu' ] );
	}

	/**
	 * Remove all admin notices on the React settings page.
	 *
	 * @return void
	 */
	public function remove_admin_notices() {
		$screen = get_current_screen();

		if ( ! $screen || 'food-menu_page_food_menu_settings_updated' !== $screen->id ) {
			return;
		}

		remove_all_actions( 'admin_notices' );
		remove_all_actions( 'all_admin_notices' );
	}

	/**
	 * Reorder submenu so Table Layout appears right after Reservations.
	 *
	 * @return void
	 */
	public function reorder_submenu() {
		global $submenu;

		$parent = 'edit.php?post_type=' . TLPFoodMenu()->post_type;

		if ( empty( $submenu[ $parent ] ) ) {
			return;
		}

		$menu_items   = &$submenu[ $parent ];
		$table_layout = null;
		$table_key    = null;
		$resi_key     = null;

		foreach ( $menu_items as $key => $item ) {
			if ( 'table_layout' === $item[2] ) {
				$table_layout = $item;
				$table_key    = $key;
			}

			if ( 'edit.php?post_type=fmp_reservation' === $item[2] ) {
				$resi_key = $key;
			}
		}

		if ( null === $table_key || null === $resi_key ) {
			return;
		}

		// Remove Table Layout from its current position.
		unset( $menu_items[ $table_key ] );

		// Re-index and insert Table Layout right after Reservations.
		$reordered = [];
		foreach ( $menu_items as $item ) {
			$reordered[] = $item;
			if ( 'edit.php?post_type=fmp_reservation' === $item[2] ) {
				$reordered[] = $table_layout;
			}
		}

		$submenu[ $parent ] = $reordered;
	}

	/**
	 * Admin menu.
	 *
	 * @return void
	 */
	public function register_admin_menu() {
		add_submenu_page(
			'edit.php?post_type=' . TLPFoodMenu()->post_type,
			esc_html__( '', 'tlp-food-menu' ),
			esc_html__( '', 'tlp-food-menu' ),
			'manage_options',
			'fmp_dashboard',
			[ $this, 'render_admin_panel' ],
			1
		);

		if ( TLPFoodMenu()->isWcActive() && Fns::get_setting( 'fm_food_menu_type' ) === 'online_ordering' && Fns::get_setting( 'fmp_food_location_popup' ) === 'on' ) {
			add_submenu_page(
				'edit.php?post_type=' . TLPFoodMenu()->post_type,
				esc_html__( 'Food Locations', 'tlp-food-menu' ),
				esc_html__( 'Food Locations', 'tlp-food-menu' ),
				'manage_options',
				'edit-tags.php?taxonomy=tpl-food-location&post_type=product',
				null,
				7
			);
		}

		if ( TLPFoodMenu()->has_pro() && Fns::get_setting( 'fm_food_menu_type' ) === 'online_ordering' && TLPFoodMenu()->isWcActive() && Fns::get_setting( 'fmp_enable_product_addons', 'on' ) === 'on' ) {
			add_submenu_page(
				'edit.php?post_type=' . TLPFoodMenu()->post_type,
				esc_html__( 'Product Addons', 'tlp-food-menu' ),
				esc_html__( 'Product Addons', 'tlp-food-menu' ),
				'manage_options',
				'product_addons',
				[ $this, 'render_product_addons' ],
				8
			);
		}

		if ( TLPFoodMenu()->has_pro() && method_exists( 'RT\FoodMenuPro\Helpers\FnsPro', 'enable_reservation' ) && FnsPro::enable_reservation() ) {
			add_submenu_page(
				'edit.php?post_type=' . TLPFoodMenu()->post_type,
				esc_html__( 'Table Layout', 'tlp-food-menu' ),
				esc_html__( 'Table Layout', 'tlp-food-menu' ),
				'manage_options',
				'table_layout',
				[ $this, 'render_table_layout' ],
				9
			);
		}

		/*if ( TLPFoodMenu()->has_pro() && Fns::is_inventory_activate() ) {
			add_submenu_page(
				'edit.php?post_type=' . TLPFoodMenu()->post_type,
				esc_html__( 'Inventory', 'tlp-food-menu' ),
				esc_html__( 'Inventory', 'tlp-food-menu' ),
				'manage_options',
				'inventory',
				[ $this, 'render_inventory' ],
				10
			);
		}*/

		if ( TLPFoodMenu()->has_pro() && TLPFoodMenu()->isWcActive() && Fns::get_setting( 'fm_food_menu_type' ) === 'online_ordering' && Fns::get_setting( 'fmp_enable_qr_table_ordering' ) === 'on' ) {
			add_submenu_page(
				'edit.php?post_type=' . TLPFoodMenu()->post_type,
				esc_html__( 'QR Table Ordering', 'tlp-food-menu' ),
				esc_html__( 'QR Table Ordering', 'tlp-food-menu' ),
				'manage_options',
				'qr_table_ordering',
				[ $this, 'render_qr_table_ordering' ],
				11
			);
		}

		if ( TLPFoodMenu()->has_pro() && TLPFoodMenu()->isWcActive() && Fns::get_setting( 'fm_food_menu_type' ) === 'online_ordering' && Fns::get_setting( 'fmp_enable_timed_products' ) === 'on' ) {
			add_submenu_page(
				'edit.php?post_type=' . TLPFoodMenu()->post_type,
				esc_html__( 'Timed Products', 'tlp-food-menu' ),
				esc_html__( 'Timed Products', 'tlp-food-menu' ),
				'manage_options',
				'timed_products',
				[ $this, 'render_timed_products' ],
				12
			);
		}

		// Register the old settings page so it remains accessible via direct URL
		// (edit.php?post_type=food-menu&page=food_menu_settings) but hide it from
		// the left admin menu by removing the submenu entry right after registration.
		$old_settings_hook = add_submenu_page(
			'edit.php?post_type=' . TLPFoodMenu()->post_type,
			esc_html__( 'Food Menu Settings', 'tlp-food-menu' ),
			esc_html__( 'Settings', 'tlp-food-menu' ),
			'manage_options',
			'food_menu_settings',
			[ $this, 'render_settings_page' ],
			19
		);
		remove_submenu_page(
			'edit.php?post_type=' . TLPFoodMenu()->post_type,
			'food_menu_settings'
		);

		// After remove_submenu_page() the page title can no longer be looked up
		// from the $submenu global, which causes get_admin_page_title() to return
		// null and triggers a strip_tags(null) deprecation in admin-header.php
		// on PHP 8.1+. Restore $title manually when this page loads.
		if ( $old_settings_hook ) {
			add_action(
				"load-{$old_settings_hook}",
				static function () {
					$GLOBALS['title'] = esc_html__( 'Food Menu Settings', 'tlp-food-menu' );
				}
			);
		}

		add_submenu_page(
			'edit.php?post_type=' . TLPFoodMenu()->post_type,
			esc_html__( 'Food Menu Settings', 'tlp-food-menu' ),
			esc_html__( 'Settings', 'tlp-food-menu' ),
			'manage_options',
			'food_menu_settings_updated',
			[ $this, 'render_settings_page_updated' ],
			20
		);

		add_submenu_page(
			'edit.php?post_type=' . TLPFoodMenu()->post_type,
			esc_html__( 'Get Help', 'tlp-food-menu' ),
			esc_html__( 'Get Help', 'tlp-food-menu' ),
			'manage_options',
			'rtfm_get_help',
			[ $this, 'render_help_page' ],
			21
		);
	}

	/**
	 * Highlight the Food Menu parent menu when editing Food Locations taxonomy.
	 *
	 * @param string $parent_file The parent file slug.
	 *
	 * @return string
	 */
	public function fix_food_location_parent_menu( $parent_file ) {
		$screen = get_current_screen();

		if ( $screen && 'edit-tpl-food-location' === $screen->id ) {
			return 'edit.php?post_type=' . TLPFoodMenu()->post_type;
		}

		return $parent_file;
	}

	/**
	 * Render Admin Panel (React Dashboard).
	 *
	 * @return void
	 */
	public function render_admin_panel() {
		echo '<div class="wrap"><div id="fmp-admin-panel"></div></div>';
	}

	public function render_settings_page_updated() {
		echo '<div id="fmp-admin-settings"></div>';
	}

	/**
	 * Render Settings.
	 *
	 * @return void|string
	 */
	public function render_settings_page() {
		Fns::renderView( 'settings' );
	}

	/**
	 * Render Help.
	 *
	 * @return void|string
	 */
	public function render_help_page() {
		Fns::renderView( 'help' );
	}

	/**
	 * Plugin links row.
	 *
	 * @param array $links Links.
	 * @param string $file File.
	 *
	 * @return array
	 */
	public function plugin_row_meta( $links, $file ) {
		if ( TLP_FOOD_MENU_PLUGIN_ACTIVE_FILE_NAME === $file ) {
			$report_url         = 'https://www.radiustheme.com/contact/';
			$row_meta['issues'] = sprintf(
				'%2$s <a target="_blank" href="%1$s"><span style="color: red">%3$s</span></a>',
				esc_url( $report_url ),
				esc_html__( 'Facing issue?', 'tlp-food-menu' ),
				esc_html__( 'Please open a support ticket.', 'tlp-food-menu' )
			);

			return array_merge( $links, $row_meta );
		}

		return (array) $links;
	}

	/**
	 * Action link.
	 *
	 * @param array $links Links.
	 *
	 * @return array
	 */
	public function marketing( $links ) {
		$links[] = '<a target="_blank" href="' . esc_url( 'https://www.radiustheme.com/demo/plugins/food-menu/' ) . '">Demo</a>';
		$links[] = '<a target="_blank" href="' . esc_url( 'https://www.radiustheme.com/docs/food-menu/getting-started/installations/' ) . '">Documentation</a>';

		if ( ! TLPFoodMenu()->has_pro() ) {
			$links[] = '<a target="_blank" style="color: #39b54a;font-weight: 700;"  href="'
			           . esc_url( 'https://www.radiustheme.com/downloads/food-menu-pro-wordpress/' ) . '">Get Pro</a>';
		}

		return $links;
	}

	/**
	 * Redirect.
	 *
	 * @return void
	 */
	public function redirect() {
		if ( get_option( 'rtfm_activation_redirect', false ) ) {
			delete_option( 'rtfm_activation_redirect' );
			wp_safe_redirect( admin_url( 'edit.php?post_type=' . TLPFoodMenu()->post_type . '&page=food_menu_settings_updated' ) );
		}
	}

	/**
	 * Product Addons
	 *
	 * @return template;
	 */
	public function render_product_addons() {
		return apply_filters( 'fmp/sub_menu/product_addons', null );
	}

	/**
	 * Render table layout
	 *
	 * @return mixed|null
	 */
	public function render_table_layout() {
		return apply_filters( 'fmp/sub_menu/table_layout', null );
	}

	/**
	 * Render table layout
	 *
	 * @return mixed|null
	 */
	public function render_inventory() {
		echo "<div id='fmp-inventory'></div>";
	}

	/**
	 * Render QR Table Ordering.
	 *
	 * @return mixed|null
	 */
	public function render_qr_table_ordering() {
		return apply_filters( 'fmp/sub_menu/qr_table_ordering', null );
	}

	/**
	 * Render Timed Products.
	 *
	 * @return mixed|null
	 */
	public function render_timed_products() {
		return apply_filters( 'fmp/sub_menu/timed_products', null );
	}
}
