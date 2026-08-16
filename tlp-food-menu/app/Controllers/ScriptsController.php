<?php
// phpcs:disable WordPress.NamingConventions.PrefixAllGlobals -- Established plugin: public namespace, hook names, functions and theme-overridable template variables must stay unchanged for backward compatibility.
/**
 * Scripts Class.
 *
 * @package RT_FoodMenu
 */

namespace RT\FoodMenu\Controllers;

use RT\FoodMenu\Helpers\Fns;
use RT\FoodMenu\Helpers\Options;

// Do not allow directly accessing this file.
if ( ! defined( 'ABSPATH' ) ) {
	exit( 'This script cannot be accessed directly.' );
}

/**
 * Scripts Class.
 */
class ScriptsController {
	use \RT\FoodMenu\Traits\SingletonTrait;

	/**
	 * Styles.
	 *
	 * @var array
	 */
	private $styles = [];

	/**
	 * Scripts.
	 *
	 * @var array
	 */
	private $scripts = [];

	/**
	 * Class Init.
	 *
	 * @return void
	 */
	protected function init() {
		$this->get_assets();

		if ( empty( $this->styles ) && empty( $this->scripts ) ) {
			return;
		}

		$version = ( defined( 'WP_DEBUG' ) && WP_DEBUG ) ? time() : TLPFoodMenu()->options['version'];

		foreach ( $this->styles as $style ) {
			wp_register_style( $style['handle'], $style['src'], '', $version );
		}

		// Polyfill react-jsx-runtime for WordPress < 6.6 (handle not registered by core).
		if ( ! wp_script_is( 'react-jsx-runtime', 'registered' ) ) {
			wp_register_script(
				'react-jsx-runtime',
				esc_url( TLPFoodMenu()->assets_url() ) . 'js/react-jsx-runtime-polyfill.min.js',
				[ 'react' ],
				$version,
				false
			);
		}

		foreach ( $this->scripts as $script ) {
			wp_register_script( $script['handle'], $script['src'], $script['deps'], $version, $script['footer'] );
		}

		add_action( 'admin_enqueue_scripts', [ $this, 'admin_scripts' ] );
		add_action( 'admin_enqueue_scripts', [ $this, 'admin_primary_color' ], 999 );
		add_action( 'wp_enqueue_scripts', [ $this, 'frontend_script' ] );

		// Make client JS translations from any translation plugin load regardless
		// of the JSON filename hash (see load_merged_script_translations()).
		add_filter( 'pre_load_script_translations', [ $this, 'load_merged_script_translations' ], 10, 4 );
	}


	/**
	 * Frontend scripts.
	 *
	 * @return void
	 */
	public function frontend_script() {
		wp_enqueue_style( 'fm-frontend' );
		wp_enqueue_script( 'fm-frontend' );
		wp_localize_script(
			'fm-frontend',
			'fmParams',
			$this->fm_data_obj()
		);

	}

	/**
	 * Admin scripts.
	 *
	 * @return void
	 */
	public function admin_scripts() {
		global $pagenow, $typenow;

		// localize script .
		$nonce        = wp_create_nonce( Fns::nonceText() );
		$localize_obj = [
			'nonceID'      => esc_attr( Fns::nonceId() ),
			'nonce'        => esc_attr( $nonce ),
			'ajaxurl'      => esc_url( admin_url( 'admin-ajax.php' ) ),
			'foodMenuType' => TLPFoodMenu()->isFoodMenuType(),
			'hasPro'       => TLPFoodMenu()->has_pro(),
		];
		wp_localize_script( 'fm-admin', 'fm_var', $localize_obj );
		wp_localize_script( 'fm-admin-global', 'fm_var', $localize_obj );

		wp_enqueue_script( 'fm-admin-global' );

		// Enqueue Admin Panel (React Dashboard) scripts.
		$screen = get_current_screen();
		if ( $screen && 'food-menu_page_fmp_dashboard' === $screen->id ) {
			wp_enqueue_style( 'fm-admin-panel' );
			wp_enqueue_script( 'fm-admin-panel' );
			wp_set_script_translations( 'fm-admin-panel', 'tlp-food-menu', FOOD_MENU_PLUGIN_DIR_PATH . 'languages' );

			// Build order statuses for the dashboard (WC statuses + custom).
			$order_statuses = [];

			if ( TLPFoodMenu()->isWcActive() ) {
				$wc_statuses     = function_exists( 'wc_get_order_statuses' ) ? wc_get_order_statuses() : [];
				$custom_statuses = get_option( 'fmp_custom_order_statuses', [] );
				$custom_map      = [];

				if ( is_array( $custom_statuses ) ) {
					foreach ( $custom_statuses as $cs ) {
						if ( ! empty( $cs['enabled'] ) && ! empty( $cs['slug'] ) ) {
							$custom_map[ $cs['slug'] ] = $cs;
						}
					}
				}

				foreach ( $wc_statuses as $wc_key => $wc_label ) {
					if ( 'wc-checkout-draft' === $wc_key ) {
						continue;
					}

					$slug = str_replace( 'wc-', '', $wc_key );
					$item = [ 'label' => $wc_label ];

					if ( isset( $custom_map[ $wc_key ] ) ) {
						$item['color']    = sanitize_hex_color( $custom_map[ $wc_key ]['color'] ?? '' );
						$item['icon']     = sanitize_text_field( $custom_map[ $wc_key ]['icon'] ?? '' );
						$item['isCustom'] = true;
					}

					$order_statuses[ $slug ] = $item;
				}
			}

			$panel_data = [
				'ajaxUrl'        => esc_url( admin_url( 'admin-ajax.php' ) ),
				'nonce'          => esc_attr( $nonce ),
				'adminUrl'       => esc_url( admin_url() ),
				'restUrl'        => esc_url( rest_url( 'fmp/v1/' ) ),
				'restNonce'      => wp_create_nonce( 'wp_rest' ),
				'assetsUrl'      => esc_url( TLPFoodMenu()->assets_url() ),
				'hasPro'         => TLPFoodMenu()->has_pro(),
				'adminPages'     => Fns::admin_pages(),
				'orderStatuses'  => $order_statuses,
				'currencySymbol' => function_exists( 'get_woocommerce_currency_symbol' ) ? get_woocommerce_currency_symbol() : '$',
				'primaryColor'   => Fns::get_setting( 'fm_primary_color', '#fc202e' ),
			];

			wp_localize_script(
				'fm-admin-panel',
				'fmpAdminPanel',
				apply_filters( 'fmp/admin_panel_localize', $panel_data )
			);
		}

		// Enqueue Admin Settings (React) scripts.
		if ( $screen && 'food-menu_page_food_menu_settings_updated' === $screen->id ) {
			wp_enqueue_media();
			wp_enqueue_style( 'fm-admin-settings' );
			wp_enqueue_script( 'fm-admin-settings' );
			wp_set_script_translations( 'fm-admin-settings', 'tlp-food-menu', FOOD_MENU_PLUGIN_DIR_PATH . 'languages' );

			$settings = get_option( TLPFoodMenu()->options['settings'], [] );

			// Dynamic primary color for settings panel.
			$primary_hex = ! empty( $settings['fm_primary_color'] ) ? sanitize_hex_color( $settings['fm_primary_color'] ) : '';
			if ( $primary_hex ) {
				$hsl = Fns::hex_to_hsl( $primary_hex );
				if ( $hsl ) {
					$h = $hsl[0];
					$s = $hsl[1];
					$l = $hsl[2];

					$css = ':root{'
					       . '--primary:' . $h . ',' . $s . '%,' . $l . '%;'
					       . '--primary-dark:' . $h . ',' . $s . '%,' . max( 0, $l - 5 ) . '%;'
					       . '--primary-light:' . $h . ',' . $s . '%,' . min( 100, $l + 10 ) . '%;'
					       . '--primary-soft:' . $h . ',' . $s . '%,97%;'
					       . '--ring:' . $h . ',' . $s . '%,' . $l . '%;'
					       . '}';
					wp_add_inline_style( 'fm-admin-settings', $css );
				}
			}
			$pages = get_pages();

			$page_options = [];
			foreach ( $pages as $page ) {
				$page_options[] = [
					'value' => $page->ID,
					'label' => $page->post_title,
					'url'   => get_permalink( $page->ID ),
				];
			}

			$post_type_options = [];
			$public_post_types = get_post_types( [ 'public' => true ], 'objects' );
			$exclude           = [ 'page', 'attachment', 'revision', 'nav_menu_item', 'elementor_library', 'tpg_builder', 'e-landing-page' ];

			foreach ( $public_post_types as $pt_slug => $pt_obj ) {
				if ( in_array( $pt_slug, $exclude ) ) {
					continue;
				}
				$post_type_options[] = [
					'value' => $pt_slug,
					'label' => $pt_obj->labels->singular_name ? $pt_obj->labels->singular_name : $pt_obj->label,
				];
			}

			// Build slot counting status options — all WC statuses (includes custom ones if registered).
			$slot_status_options = [];

			if ( function_exists( 'wc_get_order_statuses' ) ) {
				foreach ( wc_get_order_statuses() as $wc_key => $wc_label ) {
					if ( 'wc-checkout-draft' === $wc_key ) {
						continue;
					}
					$slot_status_options[ $wc_key ] = esc_html( $wc_label );
				}
			}

			// Build all statuses list for Kitchen Monitor status selector.
			$all_statuses = [];

			if ( function_exists( 'wc_get_order_statuses' ) ) {
				foreach ( wc_get_order_statuses() as $slug => $label ) {
					if ( 'wc-checkout-draft' === $slug ) {
						continue;
					}
					$all_statuses[] = [
						'value' => $slug,
						'label' => $label,
					];
				}
			}

			// Build available WP roles list (excludes administrator + FMP custom roles to avoid circular inheritance).
			$available_roles = [];
			if ( function_exists( 'wp_roles' ) ) {
				foreach ( wp_roles()->roles as $role_slug => $role_info ) {
					if ( 'administrator' === $role_slug || 0 === strpos( $role_slug, 'fmp_' ) ) {
						continue;
					}
					$available_roles[] = [
						'value' => $role_slug,
						'label' => translate_user_role( $role_info['name'] ),
					];
				}
			}

			$current_user = wp_get_current_user();

			// Food locations for settings that can be scoped per branch (e.g. the
			// Dine In table list). The taxonomy is only registered when the
			// multi-location option is on, so guard before querying.
			$food_locations = [];
			if ( taxonomy_exists( 'tpl-food-location' ) ) {
				$location_terms = get_terms(
					[
						'taxonomy'   => 'tpl-food-location',
						'hide_empty' => false,
					]
				);

				if ( ! is_wp_error( $location_terms ) ) {
					foreach ( $location_terms as $location_term ) {
						$food_locations[] = [
							'value' => $location_term->term_id,
							'label' => $location_term->name,
						];
					}
				}
			}

			wp_localize_script(
				'fm-admin-settings',
				'fmpSettings',
				[
					'ajaxUrl'              => esc_url( admin_url( 'admin-ajax.php' ) ),
					'nonceId'              => esc_attr( Fns::nonceId() ),
					'nonce'                => esc_attr( $nonce ),
					'adminUrl'             => esc_url( admin_url() ),
					'assetsUrl'            => esc_url( TLPFoodMenu()->assets_url() ),
					'hasPro'               => TLPFoodMenu()->has_pro(),
					'hasWoo'               => class_exists( 'WooCommerce' ),
					'settings'             => $settings,
					'pages'                => $page_options,
					'postTypes'            => $post_type_options,
					'currencies'           => Options::currency_list(),
					'timezone'             => get_option( 'timezone_string', '' ),
					'utcTime'              => date_i18n( 'Y-m-d H:i:s', false, true ),
					'timeFormat'           => get_option( 'time_format', 'g:i a' ),
					'slotCountingStatuses' => $slot_status_options,
					'allStatuses'          => $all_statuses,
					'foodLocations'        => $food_locations,
					'availableRoles'       => $available_roles,
					'siteUrl'              => esc_url( home_url() ),
					'adminEmail'           => sanitize_email( get_option( 'admin_email', '' ) ),
					'userName'             => $current_user->exists() ? $current_user->display_name : '',
					'profileUrl'           => esc_url( admin_url( 'profile.php' ) ),
					'avatarUrl'            => $current_user->exists() ? esc_url( get_avatar_url( $current_user->ID, [ 'size' => 64 ] ) ) : '',
					'productTypes'         => function_exists( 'wc_get_product_types' ) ? array_map(
						function ( $label, $value ) {
							return [
								'value' => $value,
								'label' => $label,
							];
						},
						array_values( wc_get_product_types() ),
						array_keys( wc_get_product_types() )
					) : [],
				]
			);
		}

		// Validate page.
		if ( ! in_array( $pagenow, [ 'edit.php', 'post.php', 'post-new.php' ] ) ) {
			return;
		}

		if ( TLPFoodMenu()->post_type != $typenow && 'product' != $typenow ) {
			return;
		}

		// Scripts.
		wp_enqueue_script(
			[
				'jquery',
				'wp-color-picker',
				'fm-select2',
				'fm-admin',
			]
		);

		// Styles.
		wp_enqueue_style(
			[
				'wp-color-picker',
				'fm-select2',
				'fm-admin',
			]
		);
	}

	/**
	 * Inject admin primary color CSS variables.
	 *
	 * Runs at priority 99 so it fires after all enqueue calls.
	 * Attaches to whichever admin handle is enqueued (fm-admin or fm-admin-preview).
	 *
	 * @return void
	 */
	public function admin_primary_color() {
		$handle = wp_style_is( 'fm-admin', 'enqueued' ) ? 'fm-admin' : ( wp_style_is( 'fm-admin-preview', 'enqueued' ) ? 'fm-admin-preview' : '' );

		if ( ! $handle ) {
			return;
		}

		$settings    = get_option( TLPFoodMenu()->options['settings'], [] );
		$primary_hex = ! empty( $settings['fm_primary_color'] ) ? sanitize_hex_color( $settings['fm_primary_color'] ) : '';

		if ( ! $primary_hex ) {
			return;
		}

		$hex       = ltrim( $primary_hex, '#' );
		$r         = hexdec( substr( $hex, 0, 2 ) );
		$g         = hexdec( substr( $hex, 2, 2 ) );
		$b         = hexdec( substr( $hex, 4, 2 ) );
		$hover_hex = sprintf( '#%02x%02x%02x', max( 0, (int) round( $r * 0.85 ) ), max( 0, (int) round( $g * 0.85 ) ), max( 0, (int) round( $b * 0.85 ) ) );


		$css = ':root{'
		       . '--rtfm-primary-color:' . $primary_hex . ';'
		       . '--rtfm-primary-rgb:' . $r . ',' . $g . ',' . $b . ';'
		       . '--fmp-admin-primary:' . $primary_hex . ';'
		       . '--fmp-admin-primary-rgb:' . $r . ',' . $g . ',' . $b . ';'
		       . '--fmp-admin-primary-hover:' . $hover_hex . ';'
		       . '}';
		wp_add_inline_style( $handle, $css );
	}

	/**
	 * Get all scripts.
	 *
	 * @return void
	 */
	private function get_assets() {
		$this
			->get_styles()
			->get_scripts();
	}

	/**
	 * Get styles.
	 *
	 * @return object
	 */
	private function get_styles() {
		$this->styles[] = [
			'handle' => 'fm-frontend',
			'src'    => esc_url( TLPFoodMenu()->assets_url() ) . 'css/foodmenu.min.css',
		];

		// Vendor styles used by the reservation form.
		$this->styles[] = [
			'handle' => 'fmp-timepicker',
			'src'    => esc_url( TLPFoodMenu()->assets_url() ) . 'vendor/timepicker/jquery.timepicker.min.css',
		];

		$this->styles[] = [
			'handle' => 'fmp-flatpickr',
			'src'    => esc_url( TLPFoodMenu()->assets_url() ) . 'vendor/flatpickr/flatpickr.min.css',
		];

		$this->styles[] = [
			'handle' => 'fmp-intl-tel-input',
			'src'    => esc_url( TLPFoodMenu()->assets_url() ) . 'vendor/intl-tel-input/intlTelInput.min.css',
		];

		/**
		 * Admin Styles.
		 */
		if ( is_admin() ) {
			$this->styles[] = [
				'handle' => 'fm-select2',
				'src'    => esc_url( TLPFoodMenu()->assets_url() ) . 'vendor/select2/select2.min.css',
			];

			$this->styles[] = [
				'handle' => 'fm-admin',
				'src'    => esc_url( TLPFoodMenu()->assets_url() ) . 'css/admin.min.css',
			];

			$this->styles[] = [
				'handle' => 'fm-admin-preview',
				'src'    => esc_url( TLPFoodMenu()->assets_url() ) . 'css/admin-preview.min.css',
			];

			$this->styles[] = [
				'handle' => 'fm-admin-fmp-only',
				'src'    => esc_url( TLPFoodMenu()->assets_url() ) . 'css/admin-fmp-only.min.css',
			];

			$this->styles[] = [
				'handle' => 'fm-admin-panel',
				'src'    => esc_url( TLPFoodMenu()->assets_url() ) . 'css/admin-panel.min.css',
			];

			$this->styles[] = [
				'handle' => 'fm-admin-settings',
				'src'    => esc_url( TLPFoodMenu()->assets_url() ) . 'css/admin-settings.min.css',
			];
		}

		return $this;
	}

	/**
	 * Get scripts.
	 *
	 * @return object
	 */
	private function get_scripts() {
		$this->scripts[] = [
			'handle' => 'fm-frontend',
			'src'    => esc_url( TLPFoodMenu()->assets_url() ) . 'js/foodmenu.min.js',
			'deps'   => [ 'jquery' ],
			'footer' => true,
		];
		$this->scripts[] = [
			'handle' => 'fm-ajax-minicart',
			'src'    => esc_url( TLPFoodMenu()->assets_url() ) . 'js/ajax-mini-cart.min.js',
			'deps'   => [ 'jquery' ],
			'footer' => true,
		];

		// Vendor scripts used by the reservation form.
		$this->scripts[] = [
			'handle' => 'fmp-timepicker',
			'src'    => esc_url( TLPFoodMenu()->assets_url() ) . 'vendor/timepicker/jquery.timepicker.min.js',
			'deps'   => [ 'jquery' ],
			'footer' => true,
		];

		$this->scripts[] = [
			'handle' => 'fmp-flatpickr',
			'src'    => esc_url( TLPFoodMenu()->assets_url() ) . 'vendor/flatpickr/flatpickr.min.js',
			'deps'   => [ 'jquery' ],
			'footer' => true,
		];

		$this->scripts[] = [
			'handle' => 'fmp-range-flatpickr',
			'src'    => esc_url( TLPFoodMenu()->assets_url() ) . 'vendor/flatpickr/rangePlugin.min.js',
			'deps'   => [ 'jquery' ],
			'footer' => true,
		];

		$this->scripts[] = [
			'handle' => 'fmp-intl-tel-input',
			'src'    => esc_url( TLPFoodMenu()->assets_url() ) . 'vendor/intl-tel-input/intlTelInput.min.js',
			'deps'   => [],
			'footer' => true,
		];

		$this->scripts[] = [
			'handle' => 'fmp-form-validation',
			'src'    => esc_url( TLPFoodMenu()->assets_url() ) . 'vendor/jquery-validation/jquery.validate.min.js',
			'deps'   => [ 'jquery' ],
			'footer' => true,
		];

		$this->scripts[] = [
			'handle' => 'fmp-reservation',
			'src'    => esc_url( TLPFoodMenu()->assets_url() ) . 'js/reservation-new.min.js',
			'deps'   => [ 'jquery', 'fmp-timepicker', 'fmp-flatpickr', 'fmp-form-validation', 'fmp-intl-tel-input', 'moment' ],
			'footer' => true,
		];

		$this->scripts[] = [
			'handle' => 'fmp-ajax-tip',
			'src'    => esc_url( TLPFoodMenu()->assets_url() ) . 'js/ajax-tip.min.js',
			'deps'   => [ 'jquery' ],
			'footer' => true,
		];

		/**
		 * Admin Scripts.
		 */
		if ( is_admin() ) {
			$this->scripts[] = [
				'handle' => 'fm-select2',
				'src'    => esc_url( TLPFoodMenu()->assets_url() ) . 'vendor/select2/select2.min.js',
				'deps'   => [ 'jquery' ],
				'footer' => true,
			];

			$this->scripts[] = [
				'handle' => 'fm-admin',
				'src'    => esc_url( TLPFoodMenu()->assets_url() ) . 'js/admin.min.js',
				'deps'   => [ 'jquery' ],
				'footer' => true,
			];
			$this->scripts[] = [
				'handle' => 'fm-admin-preview',
				'src'    => esc_url( TLPFoodMenu()->assets_url() ) . 'js/admin-preview.min.js',
				'deps'   => [ 'jquery' ],
				'footer' => true,
			];
			$this->scripts[] = [
				'handle' => 'fm-admin-global',
				'src'    => esc_url( TLPFoodMenu()->assets_url() ) . 'js/admin-global.min.js',
				'deps'   => [ 'jquery' ],
				'footer' => true,
			];

			$this->scripts[] = [
				'handle' => 'fm-admin-panel',
				'src'    => esc_url( TLPFoodMenu()->assets_url() ) . 'js/admin-panel.min.js',
				'deps'   => [ 'wp-element', 'wp-i18n', 'react-jsx-runtime' ],
				'footer' => true,
			];

			$this->scripts[] = [
				'handle' => 'fm-admin-settings',
				'src'    => esc_url( TLPFoodMenu()->assets_url() ) . 'js/admin-settings.min.js',
				'deps'   => [ 'wp-element', 'wp-i18n', 'react-jsx-runtime' ],
				'footer' => true,
			];

			$this->scripts[] = [
				'handle' => 'fmp-admin-reservation',
				'src'    => esc_url( TLPFoodMenu()->assets_url() ) . 'js/admin-reservation.min.js',
				'deps'   => [ 'jquery' ],
				'footer' => true,
			];
		}

		return $this;
	}

	/**
	 * Merge every JSON translation file for the current locale into one payload,
	 * regardless of its filename hash.
	 *
	 * WordPress' default JS-translation loading expects a single JSON file named
	 * by the md5 of the (unminified) enqueued bundle path. Translation plugins
	 * (Loco Translate, GlotPress, …) name their JSON after the original *source*
	 * file instead, so WordPress never finds it and React strings stay English.
	 *
	 * Returning a non-null value from `pre_load_script_translations` short-
	 * circuits that md5 lookup. We glob every `tlp-food-menu-{locale}-*.json`
	 * (and the bare `tlp-food-menu-{locale}.json`) from the plugin, Loco and
	 * WordPress.org language folders and merge them — so whatever any translation
	 * plugin produced for this locale is loaded. No filename matching, no shipped
	 * JSON, existing files untouched. (Approach proven in the Radius Booking
	 * plugin.)
	 *
	 * @param string|false|null $translations Existing value (null by default).
	 * @param string            $file         Path WordPress would otherwise load.
	 * @param string            $handle       Script handle.
	 * @param string            $domain       Text domain.
	 * @return string|false|null JSON string to use, or the original value to fall through.
	 */
	public function load_merged_script_translations( $translations, $file, $handle, $domain ) {
		if ( 'tlp-food-menu' !== $domain ) {
			return $translations;
		}

		static $cache = [];

		$locales   = array_unique( array_filter( [ determine_locale(), get_locale() ] ) );
		$cache_key = implode( '|', $locales );

		if ( isset( $cache[ $cache_key ] ) ) {
			return $cache[ $cache_key ];
		}

		$files = $this->find_translation_json_files( $locales );

		if ( empty( $files ) ) {
			$cache[ $cache_key ] = $translations;
			return $translations;
		}

		$messages = [
			'' => [
				'domain' => 'messages',
				'lang'   => reset( $locales ),
			],
		];

		foreach ( $files as $f ) {
			if ( 'json' === strtolower( pathinfo( $f, PATHINFO_EXTENSION ) ) ) {
				// JSON files produced by translation plugins / `wp i18n make-json`.
				$raw = file_get_contents( $f ); // phpcs:ignore WordPress.WP.AlternativeFunctions.file_get_contents_file_get_contents
				if ( false === $raw ) {
					continue;
				}
				$data = json_decode( $raw, true );
				if ( empty( $data['locale_data'] ) || ! is_array( $data['locale_data'] ) ) {
					continue;
				}
				foreach ( $data['locale_data'] as $inner ) {
					if ( ! is_array( $inner ) ) {
						continue;
					}
					if ( isset( $inner['']['plural-forms'] ) ) {
						$messages['']['plural-forms'] = $inner['']['plural-forms'];
					}
					foreach ( $inner as $msgid => $msgstr ) {
						if ( '' === $msgid ) {
							continue;
						}
						$messages[ $msgid ] = $msgstr;
					}
					break;
				}
			} else {
				// Gettext files (.mo / .l10n.php). Loco and most tools generate
				// these on EVERY site (a JSON is only produced when the plugin's
				// JS source files are present, e.g. a dev copy — not a release
				// build), so reading them is what makes JS translations work in
				// production. The whole domain (PHP + JS strings) is merged; the
				// React bundle only consumes the keys it needs.
				foreach ( $this->read_gettext_messages( $f ) as $msgid => $msgstr ) {
					if ( '' === $msgid ) {
						continue;
					}
					$messages[ $msgid ] = $msgstr;
				}
			}
		}

		$merged = wp_json_encode(
			[
				'domain'      => 'messages',
				'locale_data' => [ 'messages' => $messages ],
			]
		);

		$cache[ $cache_key ] = $merged;

		return $merged;
	}

	/**
	 * Collect all JSON translation files for the given locales across the plugin
	 * and the common language folders (newest last so later edits win).
	 *
	 * The folder list is filterable via `tlp_fm_script_translation_dirs` so any
	 * translation plugin (WPML, etc.) or site setup that stores JSON elsewhere
	 * can register its location.
	 *
	 * @param array $locales Locale slugs to look for.
	 * @return array Absolute file paths.
	 */
	private function find_translation_json_files( array $locales ) {
		$dirs = [
			FOOD_MENU_PLUGIN_DIR_PATH . 'languages', // Plugin-shipped.
			WP_LANG_DIR . '/loco/plugins',           // Loco Translate.
			WP_LANG_DIR . '/plugins',                // WordPress.org language packs.
			WP_LANG_DIR,                             // wp i18n make-json / manual.
		];

		/**
		 * Filter the directories scanned for React/JS translation JSON files.
		 *
		 * @param string[] $dirs    Absolute directory paths.
		 * @param array    $locales Locale slugs being looked up.
		 */
		$dirs = (array) apply_filters( 'tlp_fm_script_translation_dirs', $dirs, $locales );

		$found = [];

		foreach ( $dirs as $dir ) {
			foreach ( $locales as $locale ) {
				$patterns = [
					$dir . '/tlp-food-menu-' . $locale . '-*.json', // Per-script JSON (tools with JS sources).
					$dir . '/tlp-food-menu-' . $locale . '.json',    // Single JSON.
					$dir . '/tlp-food-menu-' . $locale . '.l10n.php', // PHP gettext (WP 6.5+).
					$dir . '/tlp-food-menu-' . $locale . '.mo',       // Binary gettext.
				];
				foreach ( $patterns as $pattern ) {
					$matches = glob( $pattern );
					if ( ! empty( $matches ) ) {
						$found = array_merge( $found, $matches );
					}
				}
			}
		}

		$found = array_values( array_unique( $found ) );

		usort(
			$found,
			static function ( $a, $b ) {
				return filemtime( $a ) <=> filemtime( $b );
			}
		);

		return $found;
	}

	/**
	 * Read a gettext translation file (.mo / .l10n.php) into a flat
	 * `original => [ translations ]` map suitable for Jed locale data.
	 *
	 * @param string $file Absolute path to a .mo or .l10n.php file.
	 * @return array
	 */
	private function read_gettext_messages( $file ) {
		$out = [];

		// WP 6.5+ unified reader handles both .mo and .l10n.php. A fresh handle
		// returns the full entry set (originals => translation, "\0" = plural).
		if ( class_exists( '\WP_Translation_File' ) ) {
			$moe = \WP_Translation_File::create( $file );
			if ( $moe instanceof \WP_Translation_File && null === $moe->error() ) {
				foreach ( $moe->entries() as $original => $translation ) {
					$out[ $original ] = explode( "\0", (string) $translation );
				}
			}
			return $out;
		}

		// Fallback for WP < 6.5: read .mo via the bundled pomo MO class.
		if ( '.mo' === substr( $file, -3 ) && class_exists( '\MO' ) ) {
			$mo = new \MO();
			if ( $mo->import_from_file( $file ) ) {
				foreach ( $mo->entries as $entry ) {
					$key         = $entry->context ? $entry->context . "\4" . $entry->singular : $entry->singular;
					$out[ $key ] = $entry->translations;
				}
			}
		}

		return $out;
	}

	public function fm_data_obj() {

		$fm_date_format = get_option( 'date_format' );
		$fm_time_format = get_option( 'time_format' );

		return [
			'fm_date_format' => $fm_date_format,
			'fm_time_format' => $fm_time_format,
		];
	}
}
