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
				'enable_mini_cart'                 => [
					'label'       => esc_html__( 'Enable Mini Cart?', 'food-menu-pro' ),
					'type'        => 'switch',
					'description' => esc_html__( 'Switch on to enable mini cart.', 'tlp-food-menu' ),
					'value'       => $settings['enable_mini_cart'] ?? 'on',
				],

				'mini_cart_title'                  => [
					'label' => esc_html__( 'Cart Drawer Settings', 'food-menu-pro' ),
					'type'  => 'title',
				],

				'mini_cart_drawer_style'           => [
					'id'      => 'mini_cart_drawer_style',
					'type'    => 'select',
					'class'   => 'fmp-select2',
					'value'   => $settings['mini_cart_drawer_style'] ?? 'style1',
					'label'   => esc_html__( 'Drawer Style', 'food-menu-pro' ),
					'options' => [
						'style1' => esc_html__( 'Style - 01', 'food-menu-pro' ),
						'style2' => esc_html__( 'Style - 02', 'food-menu-pro' ),
						'style3' => esc_html__( 'Style - 03', 'food-menu-pro' ),
					],
				],

				'mini_cart_open_style'             => [
					'id'      => 'mini_cart_open_style',
					'type'    => 'select',
					'class'   => 'fmp-select2',
					'value'   => $settings['mini_cart_open_style'] ?? 'open-always',
					'label'   => esc_html__( 'Drawer Opening Behavior', 'food-menu-pro' ),
					'options' => [
						'open-always'  => esc_html__( 'Open each time after add to the cart', 'food-menu-pro' ),
						'open-onclick' => esc_html__( 'Open when floating button is clicked', 'food-menu-pro' ),
					],
				],
				'mini_cart_position'               => [
					'id'          => 'mini_cart_position',
					'type'        => 'select',
					'class'       => 'fmp-select2',
					'value'       => $settings['mini_cart_open_style'] ?? 'left_center',
					'label'       => esc_html__( 'Mini Cart Position ', 'food-menu-pro' ),
					'description' => esc_html__( 'You can manage mini_cart position.', 'food-menu-pro' ),
					'options'     => [
						'left_center'  => esc_html__( 'left Center', 'food-menu-pro' ),
						'left_bottom'  => esc_html__( 'left Bottom', 'food-menu-pro' ),
						'right_center' => esc_html__( 'Right Center', 'food-menu-pro' ),
						'right_bottom' => esc_html__( 'Right Bottom', 'food-menu-pro' ),
					],
				],

				'mini_cart_extra_field_visibility' => [
					'id'          => 'mini_cart_extra_field_visibility',
					'type'        => 'switch',
					'label'       => esc_html__( 'Price extra field Visibility', 'food-menu-pro' ),
					'description' => esc_html__( 'Enable to showing all cost (Shipping, Tax etc) in price table. Otherwise only Subtotal will show.', 'food-menu-pro' ),
					'value'       => $settings['mini_cart_extra_field_visibility'] ?? 'on',
				],

				'mini_cart_coupon_visibility'      => [
					'id'          => 'mini_cart_coupon_visibility',
					'type'        => 'switch',
					'label'       => esc_html__( 'Coupon Form Visibility', 'food-menu-pro' ),
					'description' => esc_html__( 'You may show / hide coupon input box on the mini-cart footer area', 'food-menu-pro' ),
					'value'       => $settings['mini_cart_coupon_visibility'] ?? '',
				],

				'mini_cart_empty_text'             => [
					'id'          => 'mini_cart_empty_text',
					'type'        => 'text',
					'label'       => esc_html__( 'Empty Cart Message', 'food-menu-pro' ),
					'description' => esc_html__( 'Enter empty cart message. E.g: No products in the cart', 'food-menu-pro' ),
					'value'       => $settings['mini_cart_empty_text'] ?? esc_html__( 'No products in the cart.', 'food-menu-pro' ),
				],

				'mini_cart_go_shopping_btn_text'   => [
					'id'          => 'mini_cart_go_shopping_btn_text',
					'type'        => 'text',
					'label'       => esc_html__( 'Shop Button Text', 'food-menu-pro' ),
					'description' => esc_html__( 'Change Go Shopping Button text', 'food-menu-pro' ),
					'value'       => $settings['mini_cart_go_shopping_btn_text'] ?? esc_html__( 'Go Shopping', 'food-menu-pro' ),
				],

				'mini_cart_float_button_heading'   => [
					'id'    => 'mini_cart_float_button_heading',
					'type'  => 'title',
					'label' => esc_html__( 'Float Button Settings', 'food-menu-pro' ),
				],

				'mini_cart_float_btn_style'        => [
					'id'      => 'mini_cart_float_btn_style',
					'type'    => 'select',
					'class'   => 'fmp-select2',
					'value'   => $settings['mini_cart_float_btn_style'] ?? 'style1',
					'label'   => esc_html__( 'Float Button Style', 'food-menu-pro' ),
					'options' => [
						'style1' => esc_html__( 'Style # 01', 'food-menu-pro' ),
						'style2' => esc_html__( 'Style # 02', 'food-menu-pro' ),
						'style3' => esc_html__( 'Style # 03', 'food-menu-pro' ),
						'style4' => esc_html__( 'Style # 04', 'food-menu-pro' ),
					],
				],

				'mini_cart_others_settings'        => [
					'id'    => 'mini_cart_others_settings',
					'type'  => 'title',
					'label' => esc_html__( 'Others Settings', 'food-menu-pro' ),
				],
				'mini_cart_overlay_visibility'     => [
					'id'          => 'mini_cart_overlay_visibility',
					'type'        => 'switch',
					'label'       => esc_html__( 'Overlay Visibility', 'food-menu-pro' ),
					'description' => esc_html__( 'Enable this option to show the overlay', 'food-menu-pro' ),
					'value'       => $settings['mini_cart_overlay_visibility'] ?? 'on',
				],

				'mini_cart_show_on_mobile'         => [
					'id'          => 'mini_cart_show_on_mobile',
					'type'        => 'switch',
					'label'       => esc_html__( 'Show On Mobile', 'food-menu-pro' ),
					'description' => esc_html__( 'Enable this option to Show On Mobile.', 'food-menu-pro' ),
					'value'       => $settings['mini_cart_show_on_mobile'] ?? 'on',
				],

				'mini_cart_custom_selector'        => [
					'id'          => 'mini_cart_custom_selector',
					'type'        => 'text',
					'label'       => esc_html__( 'Custom Class to Open Mini Cart', 'food-menu-pro' ),
					'description' => esc_html__( 'If you would like to open the mini-cart by custom button on click just add the class name with comma separator. E.g: .icon-area-content a, span.cart-btn', 'food-menu-pro' ),
					'value'       => $settings['mini_cart_custom_selector'] ?? '',
				],
				'mini_cart_style'                  => [
					'label' => esc_html__( 'Mini Cart Style', 'food-menu-pro' ),
					'type'  => 'title',
				],
				'mini_cart_primary'                => [
					'id'    => 'mini_cart_primary',
					'label' => esc_html__( 'Drawer Primary Color', 'food-menu-pro' ),
					'type'  => 'colorpicker',
					'value' => $settings['mini_cart_primary'] ?? '',
				],

				'mini_cart_secondary'              => [
					'id'    => 'mini_cart_secondary',
					'label' => esc_html__( 'Drawer Secondary Color', 'food-menu-pro' ),
					'type'  => 'colorpicker',
					'value' => $settings['mini_cart_secondary'] ?? '#505B74',
				],
				'mini_cart_btn_style'              => [
					'label' => esc_html__( 'Floating Button Style', 'food-menu-pro' ),
					'type'  => 'title',
				],
				'mini_cart_float_bg'               => [
					'id'    => 'mini_cart_float_bg',
					'label' => esc_html__( 'Button Background', 'food-menu-pro' ),
					'type'  => 'colorpicker',
					'value' => $settings['mini_cart_float_bg'] ?? '',
				],

				'mini_cart_float_bg_hover'         => [
					'id'    => 'mini_cart_float_bg_hover',
					'label' => esc_html__( 'Button Background - Hover', 'food-menu-pro' ),
					'type'  => 'colorpicker',
					'value' => $settings['mini_cart_float_bg_hover'] ?? '',
				],

				'mini_cart_btn_width'              => [
					'id'          => 'mini_cart_btn_width',
					'type'        => 'number',
					'sanitize_fn' => 'absint',
					'description' => esc_html__( 'If you need you can enter float button width', 'food-menu-pro' ),
					'label'       => esc_html__( 'Float Button Min Width', 'food-menu-pro' ),
					'value'       => $settings['mini_cart_btn_width'] ?? '',
				],

				'mini_cart_float_btn_radius'       => [
					'id'          => 'mini_cart_float_btn_radius',
					'type'        => 'text',
					'placeholder' => '10px',
					'label'       => esc_html__( 'Border Radius', 'food-menu-pro' ),
					'description' => esc_html__( 'Enter Border Radius. Ex. 10px | 5px 5px 5px 5px | 0 5px 5px 0', 'food-menu-pro' ),
					'value'       => $settings['mini_cart_float_btn_radius'] ?? '',
				],

			]
		);
	}
}
