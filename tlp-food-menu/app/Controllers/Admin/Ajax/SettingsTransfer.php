<?php
// phpcs:disable WordPress.NamingConventions.PrefixAllGlobals -- Established plugin: public namespace, hook names, functions and theme-overridable template variables must stay unchanged for backward compatibility.
/**
 * Settings Export / Import (Settings → System → Import / Export).
 *
 * @package RT_FoodMenu
 */

namespace RT\FoodMenu\Controllers\Admin\Ajax;

use RT\FoodMenu\Helpers\Fns;

// Do not allow directly accessing this file.
if ( ! defined( 'ABSPATH' ) ) {
	exit( 'This script cannot be accessed directly.' );
}

/**
 * Exports the plugin settings as JSON and imports them back.
 *
 * The license key/status (`fmp_settings_protected_keys`) never leave the site
 * and are never overwritten. Extra standalone options can be included via the
 * `tlp_fm_settings_export_options` filter (Pro adds custom order statuses).
 */
class SettingsTransfer {

	use \RT\FoodMenu\Traits\SingletonTrait;

	/**
	 * File type marker.
	 */
	const TYPE = 'food-menu-settings';

	/**
	 * Class Init.
	 *
	 * @return void
	 */
	protected function init() {
		add_action( 'wp_ajax_fmp_settings_export', [ $this, 'export' ] );
		add_action( 'wp_ajax_fmp_settings_import', [ $this, 'import' ] );
	}

	/**
	 * Permission + nonce check.
	 *
	 * @return void
	 */
	private function verify() {
		if ( ! current_user_can( 'manage_options' ) || ! wp_verify_nonce( Fns::getNonce(), Fns::nonceText() ) ) {
			wp_send_json_error( [ 'msg' => esc_html__( 'You are not allowed to do this.', 'tlp-food-menu' ) ], 403 );
		}
	}

	/**
	 * Keys that must never be exported or imported.
	 *
	 * @return array
	 */
	private function protected_keys() {
		return (array) apply_filters( 'fmp_settings_protected_keys', [ 'license_key', 'license_status' ] );
	}

	/**
	 * Standalone options exported alongside the main settings.
	 *
	 * @return array
	 */
	private function extra_options() {
		return array_values( array_filter( (array) apply_filters( 'tlp_fm_settings_export_options', [] ), 'is_string' ) );
	}

	/**
	 * AJAX: return the settings as an export payload.
	 *
	 * @return void
	 */
	public function export() {
		$this->verify();

		$settings = get_option( TLPFoodMenu()->options['settings'], [] );
		$settings = is_array( $settings ) ? array_diff_key( $settings, array_flip( $this->protected_keys() ) ) : [];
		$options  = [];

		foreach ( $this->extra_options() as $name ) {
			$value = get_option( $name, null );

			if ( null !== $value ) {
				$options[ $name ] = $value;
			}
		}

		wp_send_json_success(
			[
				'type'        => self::TYPE,
				'version'     => TLPFoodMenu()->options['version'],
				'pro_version' => defined( 'FOOD_MENU_PRO_VERSION' ) ? FOOD_MENU_PRO_VERSION : '',
				'site'        => home_url(),
				'exported_at' => gmdate( 'c' ),
				'settings'    => $settings,
				'options'     => $options,
			]
		);
	}

	/**
	 * Sanitize an imported value, keeping line breaks in message templates.
	 *
	 * @param mixed $value Value.
	 * @return mixed
	 */
	private function sanitize( $value ) {
		if ( is_array( $value ) ) {
			$clean = [];

			foreach ( $value as $key => $item ) {
				$clean[ is_int( $key ) ? $key : sanitize_text_field( $key ) ] = $this->sanitize( $item );
			}

			return $clean;
		}

		if ( is_bool( $value ) || is_int( $value ) || is_float( $value ) || null === $value ) {
			return $value;
		}

		return sanitize_textarea_field( (string) $value );
	}

	/**
	 * AJAX: import a settings file.
	 *
	 * Keys in the file overwrite the current values; settings the file does
	 * not contain are left as they are.
	 *
	 * @return void
	 */
	public function import() {
		$this->verify();

		// phpcs:ignore WordPress.Security.ValidatedSanitizedInput.InputNotSanitized -- JSON, sanitized per value below.
		$raw  = isset( $_POST['data'] ) ? wp_unslash( $_POST['data'] ) : '';
		$data = json_decode( (string) $raw, true );

		if ( ! is_array( $data ) || ( $data['type'] ?? '' ) !== self::TYPE || ! isset( $data['settings'] ) || ! is_array( $data['settings'] ) ) {
			wp_send_json_error( [ 'msg' => esc_html__( 'This is not a Food Menu settings file.', 'tlp-food-menu' ) ] );
		}

		$existing = get_option( TLPFoodMenu()->options['settings'], [] );
		$existing = is_array( $existing ) ? $existing : [];
		$incoming = array_diff_key( $this->sanitize( $data['settings'] ), array_flip( $this->protected_keys() ) );

		// Protected keys (license) always keep this site's values.
		$settings = array_merge( $existing, $incoming );

		update_option( TLPFoodMenu()->options['settings'], $settings );

		/** This action is documented in app/Controllers/Admin/Ajax/Settings.php */
		do_action( 'fmp_settings_saved', $existing, $settings );

		$imported_options = 0;
		$allowed          = $this->extra_options();

		if ( ! empty( $data['options'] ) && is_array( $data['options'] ) ) {
			foreach ( $data['options'] as $name => $value ) {
				if ( in_array( $name, $allowed, true ) ) {
					update_option( $name, $this->sanitize( $value ) );
					++$imported_options;
				}
			}
		}

		do_action( 'tlp_fm_settings_imported', $settings, $data );

		wp_send_json_success(
			[
				'msg'   => sprintf(
					/* translators: %d: number of settings. */
					esc_html__( '%d settings imported. Reloading…', 'tlp-food-menu' ),
					count( $incoming ) + $imported_options
				),
				'count' => count( $incoming ) + $imported_options,
			]
		);
	}
}
