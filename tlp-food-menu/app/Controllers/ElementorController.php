<?php
/**
 * Widget Controller Class.
 *
 * @package RT_FoodMenu
 */

namespace RT\FoodMenu\Controllers;

use RT\FoodMenu\Widgets;

// Do not allow directly accessing this file.
if ( ! defined( 'ABSPATH' ) ) {
	exit( 'This script cannot be accessed directly.' );
}

use RT\FoodMenu\Widgets\Elementor\GridLayout;

/**
 * Widget Controller Class.
 */
class ElementorController {

	use \RT\FoodMenu\Traits\SingletonTrait;

	/**
	 * Class Init.
	 *
	 * @return void
	 */
	protected function init() {
		add_action( 'elementor/widgets/register', [ $this, 'registerWidgets' ] );
		add_action( 'elementor/elements/categories_registered', [ $this, 'widget_category' ] );
		add_action( 'wp_enqueue_scripts', [ $this, 'enqueue_scripts' ] );
	}

	/**
	 * Registers Elementor Widgets.
	 *
	 * @param \Elementor\Widgets_Manager $widgets_manager Elementor widgets manager.
	 *
	 * @return void
	 */
	public function registerWidgets( $widgets_manager ) {
		$widgets = apply_filters(
			'rtfm_elementor_widget_lists',
			[
				GridLayout::class,
			]
		);

		foreach ( $widgets as $widget ) {
			$widgets_manager->register( new $widget() );
		}
	}

	/**
	 * Widget category
	 *
	 * @param object $elements_manager Manager.
	 *
	 * @return void
	 */
	public function widget_category( $elements_manager ) {
		$categories['rtfm-food-menu'] = [
			'title' => esc_html__( 'Food Menu', 'the-post-grid' ),
			'icon'  => 'fa fa-plug',
		];

		$get_all_categories = $elements_manager->get_categories();
		$categories         = array_merge( $categories, $get_all_categories );
		$set_categories     = function( $categories ) {
			$this->categories = $categories;
		};

		$set_categories->call( $elements_manager, $categories );
	}

	public function enqueue_scripts() {

	}

}
