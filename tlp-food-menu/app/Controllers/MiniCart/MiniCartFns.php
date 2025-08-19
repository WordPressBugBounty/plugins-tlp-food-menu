<?php
/**
 * Main MiniCartFns class.
 *
 * @package RT_FoodMenu
 */

namespace RT\FoodMenu\Controllers\MiniCart;

defined( 'ABSPATH' ) || exit();

/**
 * Main MiniCartFns class.
 */
class MiniCartFns {

	/**
	 * @return array
	 */
	public static function settings_field() {

		$settings = get_option( TLPFoodMenu()->options['settings'] );

		return apply_filters(
			'fmp/minicart_settings/fields',
			[
				'mini_desc' => [
					'type'        => 'html',
					'description' => esc_html__("The mini-cart is intentionally hidden on the cart and checkout pages.", "tlp-food-menu"),
				],
				'enable_mini_cart'                 => [
					'label'       => esc_html__( 'Enable Mini Cart?', 'tlp-food-menu' ),
					'type'        => 'switch',
					'description' => esc_html__( 'Switch on to enable mini cart.', 'tlp-food-menu' ),
					'value'       => $settings['enable_mini_cart'] ?? 'on',
				],

				'mini_cart_title'                  => [
					'label' => esc_html__( 'Cart Drawer Settings', 'tlp-food-menu' ),
					'type'  => 'title',
				],

				'mini_cart_drawer_style'           => [
					'id'      => 'mini_cart_drawer_style',
					'type'    => 'select',
					'class'   => 'fmp-select2',
					'value'   => $settings['mini_cart_drawer_style'] ?? 'style1',
					'label'   => esc_html__( 'Drawer Style', 'tlp-food-menu' ),
					'options' => [
						'style1' => esc_html__( 'Style - 01', 'tlp-food-menu' ),
						'style2' => esc_html__( 'Style - 02', 'tlp-food-menu' ),
						'style3' => esc_html__( 'Style - 03', 'tlp-food-menu' ),
					],
				],

				'mini_cart_open_style'             => [
					'id'      => 'mini_cart_open_style',
					'type'    => 'select',
					'class'   => 'fmp-select2',
					'value'   => $settings['mini_cart_open_style'] ?? 'open-always',
					'label'   => esc_html__( 'Drawer Opening Behavior', 'tlp-food-menu' ),
					'options' => [
						'open-always'  => esc_html__( 'Open each time after add to the cart', 'tlp-food-menu' ),
						'open-onclick' => esc_html__( 'Open when floating button is clicked', 'tlp-food-menu' ),
					],
				],
				'mini_cart_position'               => [
					'id'          => 'mini_cart_position',
					'type'        => 'select',
					'class'       => 'fmp-select2',
					'value'       => $settings['mini_cart_open_style'] ?? 'left_center',
					'label'       => esc_html__( 'Mini Cart Position ', 'tlp-food-menu' ),
					'description' => esc_html__( 'You can manage mini_cart position.', 'tlp-food-menu' ),
					'options'     => [
						'left_center'  => esc_html__( 'left Center', 'tlp-food-menu' ),
						'left_bottom'  => esc_html__( 'left Bottom', 'tlp-food-menu' ),
						'right_center' => esc_html__( 'Right Center', 'tlp-food-menu' ),
						'right_bottom' => esc_html__( 'Right Bottom', 'tlp-food-menu' ),
					],
				],

				'mini_cart_extra_field_visibility' => [
					'id'          => 'mini_cart_extra_field_visibility',
					'type'        => 'switch',
					'label'       => esc_html__( 'Price extra field Visibility', 'tlp-food-menu' ),
					'description' => esc_html__( 'Enable to showing all cost (Shipping, Tax etc) in price table. Otherwise only Subtotal will show.', 'tlp-food-menu' ),
					'value'       => $settings['mini_cart_extra_field_visibility'] ?? 'on',
				],

				'mini_cart_coupon_visibility'      => [
					'id'          => 'mini_cart_coupon_visibility',
					'type'        => 'switch',
					'label'       => esc_html__( 'Coupon Form Visibility', 'tlp-food-menu' ),
					'description' => esc_html__( 'You may show / hide coupon input box on the mini-cart footer area', 'tlp-food-menu' ),
					'value'       => $settings['mini_cart_coupon_visibility'] ?? '',
				],

				'mini_cart_empty_text'             => [
					'id'          => 'mini_cart_empty_text',
					'type'        => 'text',
					'label'       => esc_html__( 'Empty Cart Message', 'tlp-food-menu' ),
					'description' => esc_html__( 'Enter empty cart message. E.g: No products in the cart', 'tlp-food-menu' ),
					'value'       => $settings['mini_cart_empty_text'] ?? esc_html__( 'No products in the cart.', 'tlp-food-menu' ),
				],

				'mini_cart_go_shopping_btn_text'   => [
					'id'          => 'mini_cart_go_shopping_btn_text',
					'type'        => 'text',
					'label'       => esc_html__( 'Shop Button Text', 'tlp-food-menu' ),
					'description' => esc_html__( 'Change Go Shopping Button text', 'tlp-food-menu' ),
					'value'       => $settings['mini_cart_go_shopping_btn_text'] ?? esc_html__( 'Go Shopping', 'tlp-food-menu' ),
				],

				'mini_cart_float_button_heading'   => [
					'id'    => 'mini_cart_float_button_heading',
					'type'  => 'title',
					'label' => esc_html__( 'Float Button Settings', 'tlp-food-menu' ),
				],

				'mini_cart_float_btn_style'        => [
					'id'      => 'mini_cart_float_btn_style',
					'type'    => 'select',
					'class'   => 'fmp-select2',
					'value'   => $settings['mini_cart_float_btn_style'] ?? 'style1',
					'label'   => esc_html__( 'Float Button Style', 'tlp-food-menu' ),
					'options' => [
						'style1' => esc_html__( 'Style # 01', 'tlp-food-menu' ),
						'style2' => esc_html__( 'Style # 02', 'tlp-food-menu' ),
						'style3' => esc_html__( 'Style # 03', 'tlp-food-menu' ),
						'style4' => esc_html__( 'Style # 04', 'tlp-food-menu' ),
					],
				],

				'mini_cart_others_settings'        => [
					'id'    => 'mini_cart_others_settings',
					'type'  => 'title',
					'label' => esc_html__( 'Others Settings', 'tlp-food-menu' ),
				],
				'mini_cart_overlay_visibility'     => [
					'id'          => 'mini_cart_overlay_visibility',
					'type'        => 'switch',
					'label'       => esc_html__( 'Overlay Visibility', 'tlp-food-menu' ),
					'description' => esc_html__( 'Enable this option to show the overlay', 'tlp-food-menu' ),
					'value'       => $settings['mini_cart_overlay_visibility'] ?? 'on',
				],

				'mini_cart_show_on_mobile'         => [
					'id'          => 'mini_cart_show_on_mobile',
					'type'        => 'switch',
					'label'       => esc_html__( 'Show On Mobile', 'tlp-food-menu' ),
					'description' => esc_html__( 'Enable this option to Show On Mobile.', 'tlp-food-menu' ),
					'value'       => $settings['mini_cart_show_on_mobile'] ?? 'on',
				],

				'mini_cart_custom_selector'        => [
					'id'          => 'mini_cart_custom_selector',
					'type'        => 'text',
					'label'       => esc_html__( 'Custom Class to Open Mini Cart', 'tlp-food-menu' ),
					'description' => esc_html__( 'If you would like to open the mini-cart by custom button on click just add the class name with comma separator. E.g: .icon-area-content a, span.cart-btn', 'tlp-food-menu' ),
					'value'       => $settings['mini_cart_custom_selector'] ?? '',
				],
				'mini_cart_style'                  => [
					'label' => esc_html__( 'Mini Cart Style', 'tlp-food-menu' ),
					'type'  => 'title',
				],
				'mini_cart_primary'                => [
					'id'    => 'mini_cart_primary',
					'label' => esc_html__( 'Drawer Primary Color', 'tlp-food-menu' ),
					'type'  => 'colorpicker',
					'value' => $settings['mini_cart_primary'] ?? '',
				],

				'mini_cart_secondary'              => [
					'id'    => 'mini_cart_secondary',
					'label' => esc_html__( 'Drawer Secondary Color', 'tlp-food-menu' ),
					'type'  => 'colorpicker',
					'value' => $settings['mini_cart_secondary'] ?? '#505B74',
				],
				'mini_cart_btn_style'              => [
					'label' => esc_html__( 'Floating Button Style', 'tlp-food-menu' ),
					'type'  => 'title',
				],
				'mini_cart_float_bg'               => [
					'id'    => 'mini_cart_float_bg',
					'label' => esc_html__( 'Button Background', 'tlp-food-menu' ),
					'type'  => 'colorpicker',
					'value' => $settings['mini_cart_float_bg'] ?? '',
				],

				'mini_cart_float_bg_hover'         => [
					'id'    => 'mini_cart_float_bg_hover',
					'label' => esc_html__( 'Button Background - Hover', 'tlp-food-menu' ),
					'type'  => 'colorpicker',
					'value' => $settings['mini_cart_float_bg_hover'] ?? '',
				],

				'mini_cart_btn_width'              => [
					'id'          => 'mini_cart_btn_width',
					'type'        => 'number',
					'sanitize_fn' => 'absint',
					'description' => esc_html__( 'If you need you can enter float button width', 'tlp-food-menu' ),
					'label'       => esc_html__( 'Float Button Min Width', 'tlp-food-menu' ),
					'value'       => $settings['mini_cart_btn_width'] ?? '',
				],

				'mini_cart_float_btn_radius'       => [
					'id'          => 'mini_cart_float_btn_radius',
					'type'        => 'text',
					'placeholder' => '10px',
					'label'       => esc_html__( 'Border Radius', 'tlp-food-menu' ),
					'description' => esc_html__( 'Enter Border Radius. Ex. 10px | 5px 5px 5px 5px | 0 5px 5px 0', 'tlp-food-menu' ),
					'value'       => $settings['mini_cart_float_btn_radius'] ?? '',
				],

			]
		);
	}
}
