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
    <div class="rt-settings-container settings-container">
        <div class="rt-setting-title">
            <h3><?php esc_html_e( 'Food Menu Settings', 'tlp-food-menu' ); ?></h3>
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
						'title'   => esc_html__( 'Mini Cart', 'tlp-food-menu' ),
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
					$tabList .= '<li class="tab-' . $tab['id'] . '"><a href="#' . $tab['id'] . '"><i class="dashicons ' . $tab['icon'] . '"></i>' . $tab['title'] . '</a></li>';

					$tabContent .= '<div id="' . $tab['id'] . '" class="rt-tab-content"><div class="tab-content">';
					$tabContent .= $tab['content'];
					$tabContent .= '</div></div>';
				}

				$html = null;
				$html .= '<div id="settings-tabs" class="rt-tabs rt-tab-container">';

				$html .= '<div class="rt-tab-nav-area">';

				$html .= '<ul class="tab-nav rt-tab-nav">';
				$html .= $tabList;
				$html .= '</ul>';



				$html .= '</div>';

                //Tab Content Settings Area

				$html .= '<div class="rt-tab-content-area">';
				$html .= $tabContent;
				$html .= '<p class="submit"><input type="submit" name="submit" id="fmp-saveButton" class="rt-admin-btn button button-primary" value="' . esc_attr__( 'Save Changes', 'tlp-food-menu' ) . '"></p>';
				$html .= '</div>';



				// Feature List

                $html .= '<div class="rt-promo-area">';
                $html .= '<div class="rt-sidebar-image-area">';
                $html .= '<div class="rt_sidebar_image">';
                    $html .= '<img style="width:100%" src="' . esc_url( TLPFoodMenu()->assets_url() ) . 'images/setting-sidebar.png" alt="The food menu">';
                $html .= '</div>';
                $html .= '<div class="rt-sidebar-content">';
                $html .= '<h3>' . esc_html__( 'Pro Features', 'tlp-food-menu' ) . '</h3>';
                $html .= '<ul class="rt-feature-list">';
                $html .= '<li><i class="dashicons dashicons-saved"></i> ' . esc_html__( 'Online Ordering', 'tlp-food-menu' ) . '</li>';
                $html .= '<li><i class="dashicons dashicons-saved"></i> ' . esc_html__( 'Product Addon', 'tlp-food-menu' ) . '</li>';
                $html .= '<li><i class="dashicons dashicons-saved"></i> ' . esc_html__( 'Pickup & Delivery', 'tlp-food-menu' ) . '</li>';
                $html .= '<li><i class="dashicons dashicons-saved"></i> ' . esc_html__( 'Table Reservation', 'tlp-food-menu' ) . '</li>';
                $html .= '<li><i class="dashicons dashicons-saved"></i> ' . esc_html__( '20+ Menu Layouts', 'tlp-food-menu' ) . '</li>';
                $html .= '<li><i class="dashicons dashicons-saved"></i> ' . esc_html__( 'Special Menu', 'tlp-food-menu' ) . '</li>';
                $html .= '<li><i class="dashicons dashicons-saved"></i> ' . esc_html__( 'More Features...', 'tlp-food-menu' ) . '</li>';
                $html .= '</ul>';
                if ( Fns::is_black_friday_active() ) {
                    $html .= '<div class="offer black-friday-offer">';
                    $html .= '<a href="https://www.radiustheme.com/downloads/food-menu-pro-wordpress/?utm_source=Food_menu_dashboard&utm_medium=side_banner&utm_campaign=free" target="_blank">';
                    $html .= '<img src="' . esc_url( TLPFoodMenu()->assets_url() ) . 'images/black-friday-ribbon.svg" alt="The food menu">';
                    $html .= '</a>';
                    $html .= '</div>';
                }
                // Button
                $html .= '<div class="rt-sidebar-btn">';
                $html .= '<a class="rt-admin-btn button-primary" href="https://www.radiustheme.com/downloads/food-menu-pro-wordpress/?utm_source=Food_menu_dashboard&utm_medium=side_banner&utm_campaign=free" target="_blank">' . esc_html__( 'Get The Deal!', 'tlp-food-menu' ) . '</a>';
                $html .= '</div>';
                $html .= '</div>';
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
