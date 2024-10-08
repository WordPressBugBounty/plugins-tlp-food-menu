<?php
/**
 * Settings Page.
 *
 * @package RT_FoodMenu
 */

use RT\FoodMenu\Helpers\Fns;
use RT\FoodMenu\Helpers\Options;
use RT\FoodMenu\Controllers\MiniCart\MiniCartFns;

// Do not allow directly accessing this file.
if ( ! defined( 'ABSPATH' ) ) {
	exit( 'This script cannot be accessed directly.' );
}

$settings = get_option( TLPFoodMenu()->options['settings'] );
?>

<div class="wrap">
	<h2><?php esc_html_e( 'Food Menu Settings', 'tlp-food-menu' ); ?></h2>
	<div class="rt-settings-container">
		<div class="rt-setting-title">
			<h3><?php esc_html_e( 'General settings', 'tlp-food-menu' ); ?></h3>
		</div>
		<div class="rt-setting-content">
			<form id="fmp-settings-form">
				<?php
				$tabs = [
					'general'    => [
						'id'      => 'general',
						'title'   => esc_html__( 'General', 'tlp-food-menu' ),
						'icon'    => 'dashicons-admin-settings',
						'content' => Fns::renderView( 'general-settings', [], true ),
					],
					'details'    => [
						'id'      => 'detail-page-settings',
						'title'   => esc_html__( 'Detail page settings', 'tlp-food-menu' ),
						'icon'    => 'dashicons-media-default',
						'content' => Fns::rtFieldGenerator( Options::detailPageSettings() ),
					],
					'minicart'   => [
						'id'      => 'mini-cart',
						'title'   => esc_html__( 'Mini Cart', 'food-menu-pro' ),
						'icon'    => 'dashicons-cart',
						'content' => Fns::rtFieldGenerator( MiniCartFns::settings_field() ),
					],
					'promotions' => [
						'id'      => 'promotions',
						'title'   => apply_filters( 'tlp_fm_promotion_tab_title', 'Plugin & Themes (Pro)' ),
						'icon'    => 'dashicons-megaphone',
						'content' => Fns::get_product_list_html( Options::promotionsFields() ),
					],
				];

				$tabs = apply_filters( 'tlp_fm_settings_tab', $tabs );

				$tabList    = '';
				$tabContent = '';

				foreach ( $tabs as $tab ) {
					$tabList .= '<li><a href="#' . $tab['id'] . '"><i class="dashicons ' . $tab['icon'] . '"></i>' . $tab['title'] . '</a></li>';

					$tabContent     .= '<div id="' . $tab['id'] . '" class="rt-tab-content"><div class="tab-content">';
						$tabContent .= $tab['content'];
					$tabContent     .= '</div></div>';
				}

				$html  = null;
				$html .= '<div id="settings-tabs" class="rt-tabs rt-tab-container">';

				$html .= '<div class="rt-tab-nav-area">';
				$html .= '<ul class="tab-nav rt-tab-nav">';
				$html .= $tabList;
				$html .= '</ul>';
				$html .= '</div>';

				$html .= '<div class="rt-tab-content-area">';
				$html .= $tabContent;
				$html .= '<p class="submit"><input type="submit" name="submit" id="fmp-saveButton" class="rt-admin-btn button button-primary" value="' . esc_attr__( 'Save Changes', 'tlp-food-menu' ) . '"></p>';
				$html .= '</div>';
				$html .= '</div>';

				Fns::print_html( $html, true );
				?>

				<?php wp_nonce_field( Fns::nonceText(), Fns::nonceId() ); ?>
			</form>
			<div class="rt-response"></div>
		</div>
	</div>
</div>
