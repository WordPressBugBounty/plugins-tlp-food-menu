<?php
/**
 * Special Menu Fns class — legacy settings-tab field definitions.
 *
 * The primary settings UI lives in
 * `src/settings/components/sections/SpecialMenuSettings.jsx` (React); this
 * class is retained for the legacy PHP admin tab and for any third-party
 * integrations that consume the `fmp/special_menu_settings/fields` filter.
 *
 * @package RT_FoodMenu
 */

namespace RT\FoodMenu\Controllers\SpecialMenu;

use RT\FoodMenu\Helpers\Fns;

defined( 'ABSPATH' ) || exit();

/**
 * Special Menu Fns class.
 */
class SpecialMenuFns {

	/**
	 * Menu Settings fields.
	 *
	 * @return array
	 */
	public static function settings_fields() {
		$settings = Fns::get_settings_option();

		return apply_filters(
			'fmp/special_menu_settings/fields',
			[
				'fmp_enable_menu'             => [
					'label'       => esc_html__( 'Enable Special Menu?', 'tlp-food-menu' ),
					'type'        => 'switch',
					'description' => esc_html__( 'Enable special menu?', 'tlp-food-menu' ),
					'value'       => $settings['fmp_enable_menu'] ?? 'on',
				],
				'fmp_menu_popup_duration'     => [
					'label'       => esc_html__( 'Popup End Date', 'tlp-food-menu' ),
					'type'        => 'text',
					'description' => esc_html__( 'Special menu popup available from the current date to the end date', 'tlp-food-menu' ),
					'value'       => $settings['fmp_menu_popup_duration'] ?? '',
				],
				'fmp_menu_preset'             => [
					'label'       => esc_html__( 'Preset Style', 'tlp-food-menu' ),
					'type'        => 'select',
					'class'       => 'fmp-select2',
					'description' => esc_html__( 'Choose a visual style for the popup. 1 is the classic yellow layout; 2-4 are new variations.', 'tlp-food-menu' ),
					'value'       => $settings['fmp_menu_preset'] ?? '1',
					'options'     => [
						'1' => esc_html__( 'Preset 1 — Classic Yellow', 'tlp-food-menu' ),
						'2' => esc_html__( 'Preset 2 — Dark Modern', 'tlp-food-menu' ),
						'3' => esc_html__( 'Preset 3 — Light Minimal', 'tlp-food-menu' ),
						'4' => esc_html__( 'Preset 4 — Gradient Banner', 'tlp-food-menu' ),
					],
				],
				'fmp_menu_title'              => [
					'label'       => esc_html__( 'Special Menu Title', 'tlp-food-menu' ),
					'type'        => 'text',
					'description' => esc_html__( 'Special menu day title', 'tlp-food-menu' ),
					'value'       => $settings['fmp_menu_title'] ?? '',
				],
				'fmp_menu_offer'              => [
					'label'       => esc_html__( 'Discount / Offer', 'tlp-food-menu' ),
					'type'        => 'text',
					'description' => esc_html__( 'Special menu discount or offer Eg: 30% off', 'tlp-food-menu' ),
					'value'       => $settings['fmp_menu_offer'] ?? '',
				],
				'fmp_menu_duration'           => [
					'label'       => esc_html__( 'Special Menu Text', 'tlp-food-menu' ),
					'type'        => 'text',
					'description' => esc_html__( 'Special menu discount duration Eg: Special menu offer in 3 day', 'tlp-food-menu' ),
					'value'       => $settings['fmp_menu_duration'] ?? '',
				],

				'fmp_special_menus'           => [
					'label'       => esc_html__( 'Include Menus', 'tlp-food-menu' ),
					'type'        => 'select',
					'multiple'    => true,
					'class'       => 'fmp-ajax-select2',
					'post_type'   => 'product',
					'source_name' => 'post_type',
					'value'       => $settings['fmp_special_menus'] ?? [],
				],

				'fmp_menu_button_text'        => [
					'label'       => esc_html__( 'Button Text', 'tlp-food-menu' ),
					'type'        => 'text',
					'description' => esc_html__( 'Button text for special menu popup', 'tlp-food-menu' ),
					'value'       => $settings['fmp_menu_button_text'] ?? '',
				],

				'fmp_menu_button_link'        => [
					'label'       => esc_html__( 'Button Link', 'tlp-food-menu' ),
					'type'        => 'text',
					'description' => esc_html__( 'The button serves as a link to the specified page', 'tlp-food-menu' ),
					'value'       => $settings['fmp_menu_button_link'] ?? '',
				],

				'fmp_menu_modal_style'        => [
					'label' => esc_html__( 'Modal Style', 'tlp-food-menu' ),
					'type'  => 'title',
				],
				'fmp_menu_modal_width'        => [
					'id'          => 'fmp_menu_modal_width',
					'type'        => 'number',
					'sanitize_fn' => 'absint',
					'description' => esc_html__( 'If you need you can enter special menu modal width', 'tlp-food-menu' ),
					'label'       => esc_html__( 'Modal Min Width', 'tlp-food-menu' ),
					'value'       => $settings['fmp_menu_modal_width'] ?? '',
				],
				'fmp_menu_modal_height'       => [
					'id'          => 'fmp_menu_modal_height',
					'type'        => 'number',
					'sanitize_fn' => 'absint',
					'description' => esc_html__( 'If you need you can enter special menu modal height', 'tlp-food-menu' ),
					'label'       => esc_html__( 'Modal Min Height', 'tlp-food-menu' ),
					'value'       => $settings['fmp_menu_modal_height'] ?? '',
				],

				'fmp_menu_modal_title_color'  => [
					'id'    => 'fmp_menu_modal_title_color',
					'label' => esc_html__( 'Special Menu Title Color', 'tlp-food-menu' ),
					'type'  => 'colorpicker',
					'value' => $settings['fmp_menu_modal_title_color'] ?? '',
				],

				'fmp_menu_modal_bg'           => [
					'id'    => 'fmp_menu_modal_bg',
					'label' => esc_html__( 'Special Menu BG', 'tlp-food-menu' ),
					'type'  => 'colorpicker',
					'value' => $settings['fmp_menu_modal_bg'] ?? '',
				],

				'fmp_menu_modal_btn_bg'       => [
					'id'    => 'fmp_menu_modal_btn_bg',
					'label' => esc_html__( 'Button Background', 'tlp-food-menu' ),
					'type'  => 'colorpicker',
					'value' => $settings['fmp_menu_modal_btn_bg'] ?? '',
				],

				'fmp_menu_modal_btn_bg_hover' => [
					'id'    => 'fmp_menu_modal_btn_bg_hover',
					'label' => esc_html__( 'Button BG - Hover', 'tlp-food-menu' ),
					'type'  => 'colorpicker',
					'value' => $settings['fmp_menu_modal_btn_bg_hover'] ?? '',
				],

			]
		);
	}
}
