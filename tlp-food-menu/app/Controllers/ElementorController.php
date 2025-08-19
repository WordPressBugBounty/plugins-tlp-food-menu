<?php
/**
 * Widget Controller Class.
 *
 * @package RT_FoodMenu
 */

namespace RT\FoodMenu\Controllers;
use RT\FoodMenu\Helpers\Fns;
use RT\FoodMenu\Widgets\Elementor\Controls;

// Do not allow directly accessing this file.
if ( ! defined( 'ABSPATH' ) ) {
	exit( 'This script cannot be accessed directly.' );
}

use RT\FoodMenu\Widgets\Elementor\Controls\ImageSelector;
use RT\FoodMenu\Widgets\Elementor\Elements\GridCatLayout;
use RT\FoodMenu\Widgets\Elementor\Elements\ListLayout;

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
        add_action( 'elementor/controls/register', [ $this, 'registerControls' ] );
		add_action( 'elementor/elements/categories_registered', [ $this, 'widget_category' ] );
        add_action( 'elementor/editor/after_enqueue_scripts', [ $this, 'editorScript' ] );
		add_filter( 'elementor/editor/localize_settings', [ $this, 'promotePremiumWidgets' ] );
        add_action( 'wp_enqueue_scripts', [ $this, 'elementorStyles' ] );
        add_filter( 'query_tax_filter', [ $this, 'taxControls' ] );
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
				GridCatLayout::class,
                ListLayout::class,
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
			'title' => esc_html__( 'Food Menu', 'tlp-food-menu' ),
			'icon'  => 'fa fa-plug',
		];
		$get_all_categories = $elements_manager->get_categories();
		$categories         = array_merge( $categories, $get_all_categories );
		$set_categories     = function( $categories ) {
			$this->categories = $categories;
		};
		$set_categories->call( $elements_manager, $categories );
	}

    /**
     * Registers Custom controls.
     *
     * @param object $controls_manager Controls Manager.
     * @return void
     */
    public function registerControls( $controls_manager ) {
        $controls = apply_filters(
            'rtfm_elementor_custom_controls',
            [
                ImageSelector::class,
            ]
        );

        foreach ( $controls as $control ) {
            $controls_manager->register( new $control() );
        }
    }


    /**
     * Category Controls
     *
     * @param object $obj Variable.
     * @return array
     */
    public function taxControls( $obj ) {

        $obj->elControls[] = [
            'type'        => 'select2',
            'id'          => 'fmp_categories',
            'label'       => __( 'Filter By Categories', 'tlp-food-menu' ),
            'options'     => Fns::getElAllFmpCategoryList( ),
            'description' => __( 'Select the departments you want to filter, Leave it blank for all departments.', 'tlp-food-menu' ),
            'multiple'    => true,
            'label_block' => true,
            'separator'   => 'after',
        ];

        return $obj->elControls;
    }

	/**
	 * Promote premium-only Elementor widgets in the editor for TLP Food Menu plugin.
	 *
	 * This method hooks into the Elementor editor's localized settings and injects
	 * metadata for Pro widgets that are not available in the free version.
	 * These widgets can appear disabled or promotional in the Elementor UI.
	 *
	 * @param array $config The existing configuration array localized for Elementor editor.
	 *
	 * @return array Modified configuration array including promotional Pro widgets.
	 */
	public function promotePremiumWidgets( $config ) {

		// Skip promotion if the Pro version is active.
		if ( TLPFoodMenu()->has_pro() ) {
			return $config;
		}

		// Ensure 'promotionWidgets' key exists and is an array.
		if ( ! isset( $config['promotionWidgets'] ) || ! is_array( $config['promotionWidgets'] ) ) {
			$config['promotionWidgets'] = [];
		}

		$pro_widgets = [
			[
				'name'        => 'rtfm-grid-layout',
				'title'       => esc_html__( 'Grid Layouts', 'tlp-food-menu' ),
				'description' => esc_html__( 'Available in Pro version: Advanced food menu layout', 'tlp-food-menu' ),
				'icon'        => 'rtfm-elementor-icon eicon-gallery-grid',
				'categories'  => '[ "rtfm-food-menu" ]',
			],
			[
				'name'        => 'rtfm-slider-layout',
				'title'       => esc_html__( 'Slider Layouts', 'tlp-food-menu' ),
				'description' => esc_html__( 'Available in Pro version: Advanced food menu layout', 'tlp-food-menu' ),
				'icon'        => 'rtfm-elementor-icon eicon-gallery-grid',
				'categories'  => '[ "rtfm-food-menu" ]',
			],
			[
				'name'        => 'rtfm-isotope-layout',
				'title'       => esc_html__( 'Isotope Filters Layouts', 'tlp-food-menu' ),
				'description' => esc_html__( 'Available in Pro version: Advanced food menu layout', 'tlp-food-menu' ),
				'icon'        => 'rtfm-elementor-icon eicon-gallery-grid',
				'categories'  => '[ "rtfm-food-menu" ]',
			],
		];
		$config['promotionWidgets'] = array_merge( $config['promotionWidgets'], $pro_widgets );
		return $config;
	}

    /**
     * Elementor editor scripts
     *
     * @return void
     */
    public function editorScript() {
	    wp_enqueue_script( 'rtfm-el-editor-scripts', TLPFoodMenu()->assets_url() . 'js/elementor-editor.js', [ 'jquery' ], '1.0.0', true );

	    wp_enqueue_style('rtfm-el-editor-style', TLPFoodMenu()->assets_url() . 'css/elementor-editor.min.css', [], '1.0.0' );

    }

    /**
     * Elementor Style
     *
     * @return void
     */
    public function elementorStyles() {
        wp_enqueue_style('rtfm-el-style', TLPFoodMenu()->assets_url() . 'css/elementor-frontend.min.css', [], '1.0.0' );

    }

}
