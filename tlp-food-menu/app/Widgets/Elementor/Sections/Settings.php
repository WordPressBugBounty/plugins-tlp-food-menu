<?php
/**
 * Elementor Settings Class.
 *
 * This class contains all the controls for Settings tab.
 *
 * @package RT_Team
 */
namespace RT\FoodMenu\Widgets\Elementor\Sections;

use RT\FoodMenu\Helpers\Fns;
use RT\FoodMenu\Helpers\Options;

// Do not allow directly accessing this file.
if ( ! defined( 'ABSPATH' ) ) {
    exit( 'This script cannot be accessed directly.' );
}

/**
 * Elementor Settings Class.
 */
class Settings{
    /**
     * Tab name.
     *
     * @access private
     * @static
     *
     * @var array
     */
    private static $tab = \Elementor\Controls_Manager::TAB_SETTINGS;

    /**
     * Slider section
     *
     * @param object $obj Reference object.
     * @return static
     */
    public static function slider( $obj ) {

        $obj->startSection( 'slider_section', esc_html__( 'Slider Settings', 'tlp-food-menu' ), self::$tab );
        $obj->elHeading( 'slider_control_note', esc_html__( 'Controls', 'tlp-food-menu' ) );

        $obj->elControls[] = [
            'type'        => 'switch',
            'id'          => 'fmp_slider_loop',
            'label'       => esc_html__( 'Infinite Loop', 'tlp-food-menu' ),
            'label_on'    => esc_html__( 'On', 'tlp-food-menu' ),
            'label_off'   => esc_html__( 'Off', 'tlp-food-menu' ),
            'description' => esc_html__( 'Switch on to enable slider infinite loop.', 'tlp-food-menu' ),
        ];

        $obj->elControls[] = [
            'type'        => 'switch',
            'id'          => 'fmp_slider_nav',
            'label'       => esc_html__( 'Navigation Arrows', 'tlp-food-menu' ),
            'label_on'    => esc_html__( 'On', 'tlp-food-menu' ),
            'label_off'   => esc_html__( 'Off', 'tlp-food-menu' ),
            'description' => esc_html__( 'Switch on to enable slider navigation arrows.', 'tlp-food-menu' ),
            'default'     => 'yes',
        ];

        $obj->elControls[] = [
            'type'        => 'select2',
            'id'          => 'slider_nav_position',
            'label'       => sprintf(
                '%s <br>%s', 'tlp-food-menu',
                esc_html__( 'Navigation Arrows', 'tlp-food-menu' ),
                esc_html__( 'Position', 'tlp-food-menu' )
            ),
            'options'     => [
                'top'      => esc_html__( 'Top', 'tlp-food-menu' ),
                'standard' => esc_html__( 'Middle', 'tlp-food-menu' ),
                'bottom'   => esc_html__( 'Bottom', 'tlp-food-menu' ),
            ],
            'description' => esc_html__( 'Please select the slider arrows position.', 'tlp-food-menu' ),
            'default'     => 'top',
        ];

        $obj->elControls[] = [
            'type'        => 'switch',
            'id'          => 'fmp_slider_pagi',
            'label'       => esc_html__( 'Dot Pagination', 'tlp-food-menu' ),
            'label_on'    => esc_html__( 'On', 'tlp-food-menu' ),
            'label_off'   => esc_html__( 'Off', 'tlp-food-menu' ),
            'description' => esc_html__( 'Switch on to enable slider dot pagination.', 'tlp-food-menu' ),
            'default'     => 'yes',
        ];

        $obj->elControls[] = [
            'type'        => 'switch',
            'id'          => 'fmp_slider_auto_height',
            'label'       => esc_html__( 'Auto Height', 'tlp-food-menu' ),
            'label_on'    => esc_html__( 'On', 'tlp-food-menu' ),
            'label_off'   => esc_html__( 'Off', 'tlp-food-menu' ),
            'description' => esc_html__( 'Switch on to enable slider dynamic height.', 'tlp-food-menu' ),
        ];

        $obj->elControls[] = [
            'type'        => 'switch',
            'id'          => 'fmp_slider_lazy_load',
            'label'       => esc_html__( 'Image Lazy Load', 'tlp-food-menu' ),
            'label_on'    => esc_html__( 'On', 'tlp-food-menu' ),
            'label_off'   => esc_html__( 'Off', 'tlp-food-menu' ),
            'description' => esc_html__( 'Switch on to enable slider image lazy load.', 'tlp-food-menu' ),
        ];

        $obj->elControls[] = [
            'type'        => 'number',
            'id'          => 'fmp_slide_speed',
            'label'       => esc_html__( 'Slide Speed (in ms)', 'tlp-food-menu' ),
            'description' => esc_html__( 'Please enter the duration of transition between slides (in ms).', 'tlp-food-menu' ),
            'default'     => 2000,
            'separator'   => 'after',
        ];

        $obj->elHeading( 'slider_autoplay_note', esc_html__( 'Autoplay', 'tlp-food-menu' ) );

        $obj->elControls[] = [
            'type'        => 'switch',
            'id'          => 'fmp_slide_autoplay',
            'label'       => esc_html__( 'Enable Autoplay?', 'tlp-food-menu' ),
            'label_on'    => esc_html__( 'On', 'tlp-food-menu' ),
            'label_off'   => esc_html__( 'Off', 'tlp-food-menu' ),
            'description' => esc_html__( 'Switch on to enable slider autoplay.', 'tlp-food-menu' ),
            'default'     => 'yes',
        ];

        $obj->elControls[] = [
            'type'        => 'switch',
            'id'          => 'fmp_pause_hover',
            'label'       => esc_html__( 'Pause on Mouse Hover?', 'tlp-food-menu' ),
            'label_on'    => esc_html__( 'On', 'tlp-food-menu' ),
            'label_off'   => esc_html__( 'Off', 'tlp-food-menu' ),
            'description' => esc_html__( 'Switch on to enable slider autoplay pause on mouse hover.', 'tlp-food-menu' ),
            'default'     => 'yes',
        ];

        $obj->elControls[] = [
            'type'        => 'number',
            'id'          => 'fmp_autoplay_timeout',
            'label'       => esc_html__( 'Autoplay Delay (in ms)', 'tlp-food-menu' ),
            'options'     => Fns::get_shortCode_list(),
            'default'     => 5000,
            'description' => esc_html__( 'Please select autoplay interval delay (in ms).', 'tlp-food-menu' ),
            'condition'   => [ 'slide_autoplay' => 'yes' ],
        ];

        $obj->endSection();

        return new static();
    }

    /**
     * Filter section
     *
     * @param object $obj Reference object.
     * @return static
     */
//    public static function filter( $obj ) {
//        $obj->startSection(
//            'filter_section',
//            esc_html__( 'Filters (Front-End)', 'tlp-food-menu' ),
//            self::$tab,
//            [],
//            [ $obj->elPrefix . 'layout!' => [ 'special01' ] ]
//        );
//
//        //$obj->elControls = Fns::filter( $obj->elPrefix . 'filter_section', $obj );
//
//        $obj->endSection();
//
//        return new static();
//    }

	public static function filter( $filterName, $obj ) {
		return array_merge( apply_filters( $filterName, $obj ) );
	}

    /**
     * Filter section
     *
     * @param object $obj Reference object.
     * @return static
     */
	/**
	 * Layout section
	 *
	 * @param object $obj Reference object.
	 *
	 * @return static
	 */
	public static function isotopeLayout( $obj ) {

		$obj->startSection( 'isotope_layout', esc_html__( 'Isotope Filter', 'tlp-food-menu' ), self::$tab );
		$obj->elControls[] = [
			'type'        => 'switch',
			'id'          => 'fmp_isotope_filter',
			'label'       => esc_html__( 'Enable Isotope Filter Button ?', 'tlp-food-menu' ),
			'description' => esc_html__( 'Switch on to display show isotope filter button.', 'tlp-food-menu' ),
			'label_on'    => esc_html__( 'On', 'tlp-food-menu' ),
			'label_off'   => esc_html__( 'Off', 'tlp-food-menu' ),
			'default'     => 'yes',
		];

		$obj->elControls[] = [
			'type'           => 'select',
			'id'             => 'menu_type',
			'label'          => esc_html__( 'Isotope Filter Style Type', 'tlp-food-menu' ),
			'description'    => esc_html__( 'Please select the isotope filter style type.', 'tlp-food-menu' ),
			'options'        => [
				'type-1' => esc_html__( 'Type 1', 'tlp-food-menu' ),
				'type-2'  => esc_html__( 'Type 2', 'tlp-food-menu' ),
				'type-3'  => esc_html__( 'Type 3', 'tlp-food-menu' ),
			],
			'default'        => 'type-1',
			'condition'   => [ 'fmp_isotope_filter' => [ 'yes' ] ],
		];

		$obj->elControls[] = [
			'type'        => 'switch',
			'id'          => 'fmp_switch_all_button',
			'label'       => esc_html__( 'Display "Show All Button" ?', 'tlp-food-menu' ),
			'description' => esc_html__( 'Switch on to display show all button.', 'tlp-food-menu' ),
			'label_on'    => esc_html__( 'On', 'tlp-food-menu' ),
			'label_off'   => esc_html__( 'Off', 'tlp-food-menu' ),
			'condition'   => [ 'fmp_isotope_filter' => [ 'yes' ] ],
			'default'     => 'yes',
		];
		$obj->elControls[] = [
			'type'        => 'text',
			'id'          => 'fmp_change_btn_text',
			'label'       => esc_html__( 'Change Button Text', 'tlp-food-menu' ),
			'options'     => Fns::get_shortCode_list(),
			'default'     => 'All Button',
			'description' => esc_html__( 'Please change "All Button" text', 'tlp-food-menu' ),
			'condition'   => [ 'fmp_switch_all_button' => 'yes' ],
		];

		$obj->elControls[] = [
			'type'        => 'switch',
			'id'          => 'fmp_filer_search',
			'label'       => esc_html__( 'Display Filer Search Button ?', 'tlp-food-menu' ),
			'description' => esc_html__( 'Switch on to display show display search button.', 'tlp-food-menu' ),
			'label_on'    => esc_html__( 'On', 'tlp-food-menu' ),
			'label_off'   => esc_html__( 'Off', 'tlp-food-menu' ),
		];

		$obj->endSection();
		return new static();
	}

    /**
     * Content limit section
     *
     * @param object $obj Reference object.
     * @return static
     */
    public static function contentLimit( $obj ) {

        $obj->startSection( 'content_limit_section', esc_html__( 'Content Limit', 'tlp-food-menu' ), self::$tab );
        $obj->elControls[] = [
            'type'        => 'number',
            'id'          => 'fmp_excerpt_limit',
            'label'       => esc_html__( 'Content limit', 'tlp-food-menu' ),
            'description' => esc_html__( 'Limits the short content text (word limit). Leave it blank for full text.', 'tlp-food-menu' ),
        ];

        $obj->endSection();

        return new static();
    }

    /**
     * Pagination section
     *
     * @param object $obj Reference object.
     * @return static
     */
    public static function links( $obj ) {
        $obj->startSection( 'links_section', esc_html__( 'Details Page', 'tlp-food-menu' ), self::$tab );

        $obj->elControls[] = [
            'type'        => 'switch',
            'id'          => 'fmp_detail_page_link',
            'label'       => esc_html__( 'Link to Detail Page?', 'tlp-food-menu' ),
            'description' => esc_html__( 'Switch on to enable linking to detail page.', 'tlp-food-menu' ),
            'label_on'    => esc_html__( 'On', 'tlp-food-menu' ),
            'label_off'   => esc_html__( 'Off', 'tlp-food-menu' ),
            'default'     => 'yes',
        ];

	    $obj->elControls[] = [
		    'type'            => 'select',
		    'id'              => 'fmp_detail_link',
		    'label'           => esc_html__( 'Select Image Size', 'tlp-food-menu' ),
		    'options'         => [
				'link'    => esc_html__( 'Same Window', 'tlp-food-menu' ),
				'target'  => esc_html__( 'New Window', 'tlp-food-menu' ),
		    ],
		    'default'         => 'link',
		    'label_block'     => true,
		    'condition'       => [
				'fmp_detail_page_link' => [ 'yes' ],
		    ],
		    'separator'      => 'after',
	    ];

	    $obj->elControls = self::filter( 'tlp_el_pro_popup', $obj );


        $obj->endSection();

        return new static();
    }

    /**
     * Visibility section
     *
     * @param object $obj Reference object.
     * @return static
     */
    public static function ContentVisibility( $obj ) {
        $layoutCondition = [ $obj->elPrefix . 'layout!' => [ 'layout-el-4', 'layout7', 'layout-el-8', 'layout9', 'layout-el-10', 'layout11', 'layout12', 'layout13', 'layout14', 'layout15', 'carousel-el-2', 'carousel3', 'carousel4', 'carousel5', 'carousel6', 'carousel7', 'carousel8', 'carousel9', 'carousel11', 'isotope1', 'isotope3', 'isotope4', 'isotope5', 'isotope6', 'isotope7', 'isotope8', 'isotope9', 'isotope10' ] ];

        $obj->startSection( 'visibility_section', esc_html__( 'Content Visibility', 'tlp-food-menu' ), self::$tab );

        $obj->elControls[] = [
            'type'        => 'switch',
            'id'          => 'fmp_title_switch',
            'label'       => esc_html__( 'Show Title?', 'tlp-food-menu' ),
            'description' => esc_html__( 'Switch on to show food menu title.', 'tlp-food-menu' ),
            'label_on'    => esc_html__( 'On', 'tlp-food-menu' ),
            'label_off'   => esc_html__( 'Off', 'tlp-food-menu' ),
            'default'     => 'yes',
        ];

        $obj->elControls[] = [
            'type'        => 'switch',
            'id'          => 'fmp_price_switch',
            'label'       => esc_html__( 'Show Price?', 'tlp-food-menu' ),
            'description' => esc_html__( 'Switch on to show food menu price.', 'tlp-food-menu' ),
            'label_on'    => esc_html__( 'On', 'tlp-food-menu' ),
            'label_off'   => esc_html__( 'Off', 'tlp-food-menu' ),
            'default'     => 'yes',
        ];

        $obj->elControls[] = [
            'type'        => 'switch',
            'id'          => 'fmp_content_switch',
            'label'       => esc_html__( 'Show Excerpt?', 'tlp-food-menu' ),
            'description' => esc_html__( 'Switch on to show food menu excerpt.', 'tlp-food-menu' ),
            'label_on'    => esc_html__( 'On', 'tlp-food-menu' ),
            'label_off'   => esc_html__( 'Off', 'tlp-food-menu' ),
            'default'     => 'yes',
            'separator'      => 'after',
        ];

	    $obj->elControls = self::filter( 'tlp_el_pro_switcher', $obj );

        $obj->endSection();

        return new static();
    }

    /**
     * Visibility section
     *
     * @param object $obj Reference object.
     * @return static
     */
    public static function category_settinngs( $obj ) {

        $obj->startSection( 'category_settinngs', esc_html__( 'Category Settings', 'tlp-food-menu' ), self::$tab );

	    $obj->elControls[] = [
		    'type'            => 'select',
		    'id'              => 'fmp_cat_style',
		    'label'           => esc_html__( 'Select Category Title Type', 'tlp-food-menu' ),
		    'options'         => [
			    'type-1'    => esc_html__( 'Type 1', 'tlp-food-menu' ),
			    'type-2'    => esc_html__( 'Type 2', 'tlp-food-menu' ),
			    'type-3'    => esc_html__( 'Type 3', 'tlp-food-menu' ),
			    'type-4'    => esc_html__( 'Type 4', 'tlp-food-menu' ),
		    ],
	    ];

        $obj->endSection();

        return new static();
    }
}