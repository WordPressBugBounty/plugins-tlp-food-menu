<?php
/**
 * Elementor Shortcodes List Widget Class.
 *
 * This widget is deprecated and will be removed in some future version.
 *
 * @package RT_FoodMenu
 */

namespace RT\FoodMenu\Widgets\Elementor;

use RT\FoodMenu\Helpers\Fns;

// Do not allow directly accessing this file.
if ( ! defined( 'ABSPATH' ) ) {
	exit( 'This script cannot be accessed directly.' );
}

/**
 * Elementor Shortcodes List Widget Class.
 */
class GridLayout extends \Elementor\Widget_Base {
	public function get_name() {
		return 'food-menu-grid';
	}

	public function get_title() {
		return esc_html__( 'Food Menu Grid', 'tlp-food-menu' );
	}

	public function get_icon() {
		return 'eicon-gallery-grid';
	}

	public function get_categories() {
		return [ 'rtfm-food-menu' ];
	}

	protected function register_controls() {
		$this->start_controls_section(
			'content_section',
			[
				'label' => esc_html__( 'Food Menu', 'tlp-food-menu' ),
				'tab'   => \Elementor\Controls_Manager::TAB_CONTENT,
			]
		);

		$this->add_control(
			'food_menu_id',
			[
				'type'    => \Elementor\Controls_Manager::SELECT2,
				'id'      => 'style',
				'label'   => esc_html__( 'Select ShortCode', 'tlp-food-menu' ),
				'options' => Fns::get_shortCode_list(),
			]
		);

		$this->end_controls_section();
	}

	protected function render() {
		$settings = $this->get_settings_for_display();

		echo "Hello World";

	}

}
