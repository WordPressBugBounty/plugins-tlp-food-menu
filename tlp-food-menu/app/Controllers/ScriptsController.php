<?php
/**
 * Scripts Class.
 *
 * @package RT_FoodMenu
 */

namespace RT\FoodMenu\Controllers;

use RT\FoodMenu\Helpers\Fns;

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

		foreach ( $this->scripts as $script ) {
			wp_register_script( $script['handle'], $script['src'], $script['deps'], $version, $script['footer'] );
		}

		add_action( 'admin_enqueue_scripts', [ $this, 'admin_scripts' ] );
		add_action( 'wp_enqueue_scripts', [ $this, 'frontend_script' ] );
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

		// Validate page.
		if ( ! in_array( $pagenow, [ 'edit.php','post.php','post-new.php' ] ) ) {
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
		}

		return $this;
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
