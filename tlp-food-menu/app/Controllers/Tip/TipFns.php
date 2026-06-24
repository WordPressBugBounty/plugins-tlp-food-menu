<?php
/**
 * Main TipFns class.
 *
 * @package RT_FoodMenu
 */

namespace RT\FoodMenu\Controllers\Tip;

defined( 'ABSPATH' ) || exit();

/**
 * Main TipFns class.
 */
class TipFns {

	/**
	 * Tip setting fields.
	 *
	 * @return array
	 */
	public static function settings_field() {

		$settings = get_option( TLPFoodMenu()->options['settings'], [] );

		return apply_filters(
			'fmp/tip_settings/fields',
			[
				'enable_tip'       => [
					'label'       => esc_html__( 'Enable Tip?', 'tlp-food-menu' ),
					'type'        => 'switch',
					'description' => esc_html__( 'Enable tip/donation option at checkout and cart?', 'tlp-food-menu' ),
					'value'       => $settings['enable_tip'] ?? 'on',
				],

				'show_tip_form'    => [
					'id'      => 'show_tip_form',
					'type'    => 'select',
					'class'   => 'fmp-select2',
					'value'   => $settings['show_tip_form'] ?? 'both',
					'label'   => esc_html__( 'Display Tip Form', 'tlp-food-menu' ),
					'options' => [
						'checkout' => esc_html__( 'Checkout Page', 'tlp-food-menu' ),
						'cart'     => esc_html__( 'Cart Page', 'tlp-food-menu' ),
						'both'     => esc_html__( 'Both', 'tlp-food-menu' ),
					],
				],

				'allow_tip_for'    => [
					'id'      => 'allow_tip_for',
					'type'    => 'select',
					'class'   => 'fmp-select2',
					'value'   => $settings['allow_tip_for'] ?? 'both',
					'label'   => esc_html__( 'Allow tip for', 'tlp-food-menu' ),
					'options' => [
						'both'       => esc_html__( 'Both', 'tlp-food-menu' ),
						'fixed'      => esc_html__( 'Fixed', 'tlp-food-menu' ),
						'percentage' => esc_html__( 'Percentage', 'tlp-food-menu' ),
					],
				],

				'tip_heading_text' => [
					'id'          => 'tip_heading_text',
					'type'        => 'text',
					'label'       => esc_html__( 'Tip Heading Text', 'tlp-food-menu' ),
					'description' => esc_html__( 'Tip heading text. E.g: Do you want to provide a tip?', 'tlp-food-menu' ),
					'value'       => $settings['tip_heading_text'] ?? esc_html__( 'Do you want to provide a tip?', 'tlp-food-menu' ),
				],

			]
		);
	}
}
