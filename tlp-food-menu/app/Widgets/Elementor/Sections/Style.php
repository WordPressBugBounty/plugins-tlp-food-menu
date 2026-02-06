<?php
/**
 * Elementor Settings Class.
 *
 * This class contains all the controls for Settings tab.
 *
 * @package RT_FoodMenu
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
class Style {
    /**
     * Tab name.
     *
     * @access private
     * @static
     *
     * @var array
     */
    private static $tab = \Elementor\Controls_Manager::TAB_STYLE;

    /**
     * Color Title section
     *
     * @param object $obj Reference object.
     * @return static
     */
    public static function titleSection( $obj ) {
        $obj->startSection( 'title_style_section', esc_html__( 'Title', 'tlp-food-menu' ), self::$tab );

        $obj->elControls[] = [
            'mode'     => 'group',
            'type'     => 'typography',
            'id'       => 'fmp_title_typography',
            'selector' => '{{WRAPPER}} .fmp-title h3',
        ];

	    $obj->elControls[] = [
		    'type'      => 'color',
		    'id'        => 'fmp_title_color',
		    'label'     => esc_html__( 'Color', 'tlp-food-menu' ),
		    'selectors' => [
			    '{{WRAPPER}} .fmp-title h3, {{WRAPPER}} .fmp-title h3 a' => 'color: {{VALUE}}',
		    ],
	    ];

	    $obj->elControls[] = [
		    'type'      => 'color',
		    'id'        => 'fmp_title_hover_color',
		    'label'     => esc_html__( 'Hover color', 'tlp-food-menu' ),
		    'selectors' => [
			    '{{WRAPPER}} .fmp-title h3:hover, {{WRAPPER}} .fmp-title h3 a:hover' => 'color: {{VALUE}}',
		    ],
	    ];

	    $obj->elControls[] = [
		    'type'      => 'color',
		    'id'        => 'fmp_title_border_color5',
		    'label'     => esc_html__( 'Border color', 'tlp-food-menu' ),
		    'selectors' => [
			    '{{WRAPPER}} .fmp-layout5 .fmp-content-wrap .fmp-title:before' => 'background-color: {{VALUE}}',
		    ],
		    'condition' => [
			    'fmp_layout' => [ 'layout5'],
		    ],
	    ];

	    $obj->elControls[] = [
		    'mode'     => 'group',
		    'type'     => 'border',
		    'label'    => esc_html__( 'Border', 'tlp-food-menu' ),
		    'id'       => 'fmp_title_border',
		    'selector' => '{{WRAPPER}} .fmp-wrapper .fmp-title',
	    ];

        $obj->endSection();

        return new static();
    }

	/**
	 * Isotope Filter Style Section
	 *
	 * @param object $obj Reference object.
	 * @return static
	 */
	public static function filterButtons( $obj ) {
		$obj->startSection( 'fiilter_button_style', esc_html__( 'Filter Button', 'tlp-food-menu' ), self::$tab );
		$obj->elControls[] = [
			'mode'     => 'group',
			'type'     => 'typography',
			'id'       => 'fmp_filter_buttons_typography',
			'selector' => '{{WRAPPER}} .fmp-iso-filter button',
		];

		$obj->elControls[] = [
			'mode'      => 'responsive',
			'id'        => 'fmp_filter_buttons_alignment',
			'type'      => 'choose',
			'label'     => esc_html__( 'Alignment', 'tlp-food-menu' ),
			'options'   => [
				'left'   => [
					'title' => esc_html__( 'Left', 'tlp-food-menu' ),
					'icon'  => 'eicon-text-align-left',
				],
				'center' => [
					'title' => esc_html__( 'Center', 'tlp-food-menu' ),
					'icon'  => 'eicon-text-align-center',
				],
				'right'  => [
					'title' => esc_html__( 'Right', 'tlp-food-menu' ),
					'icon'  => 'eicon-text-align-right',
				],
			],
			'selectors' => [
				'{{WRAPPER}} .fmp-elementor-isotope .fmp-iso-filter' => 'text-align: {{VALUE}}',
			],
		];

		$obj->elHeading( 'fmp_filter_buttons_colors_note', esc_html__( 'Colors', 'tlp-food-menu' ), 'before' );

		$obj->startTabGroup( 'filter_button_color_tabs' );

		$obj->startTab( 'filter_button_color_tab', esc_html__( 'Normal', 'tlp-food-menu' ) );

		$obj->elControls[] = [
			'type'      => 'color',
			'id'        => 'fmp_filter_button_color',
			'label'     => esc_html__( 'Button Color', 'tlp-food-menu' ),
			'selectors' => [
				'{{WRAPPER}} .fmp-elementor-isotope .button-group button' => 'color: {{VALUE}}',
			],
		];

		$obj->elControls[] = [
			'type'      => 'color',
			'id'        => 'fmp_filter_button_bg_color',
			'label'     => esc_html__( 'Button Background Color', 'tlp-food-menu' ),
			'selectors' => [
				'{{WRAPPER}} .fmp-elementor-isotope .button-group button' => 'background-color: {{VALUE}}',
			],
		];

		$obj->endTab();
		$obj->startTab( 'filter_button_hover_color_tab', esc_html__( 'Hover', 'tlp-food-menu' ) );

		$obj->elControls[] = [
			'type'      => 'color',
			'id'        => 'fmp_filter_button_hover_color',
			'label'     => esc_html__( 'Hover Color', 'tlp-food-menu' ),
			'selectors' => [
				'{{WRAPPER}} .fmp-elementor-isotope .button-group button:hover' => 'color: {{VALUE}}',
			],
		];

		$obj->elControls[] = [
			'type'      => 'color',
			'id'        => 'fmp_filter_button_hover_bg_color',
			'label'     => esc_html__( 'Hover Background Color', 'tlp-food-menu' ),
			'selectors' => [
				'{{WRAPPER}} .fmp-elementor-isotope .button-group button:hover:after' => 'border-top-color: {{VALUE}}',
				'{{WRAPPER}} .fmp-elementor-isotope .button-group button:hover:before' => 'background: {{VALUE}}',
			],
		];

		$obj->endTab();
		$obj->startTab( 'filter_button_active_color_tab', esc_html__( 'Active', 'tlp-food-menu' ) );

		$obj->elControls[] = [
			'type'      => 'color',
			'id'        => 'fmp_filter_button_active_color',
			'label'     => esc_html__( 'Active Color', 'tlp-food-menu' ),
			'selectors' => [
				'{{WRAPPER}} .fmp-elementor-isotope .button-group .selected' => 'color: {{VALUE}}',
			],
		];

		$obj->elControls[] = [
			'type'      => 'color',
			'id'        => 'fmp_filter_button_active_bg_color',
			'label'     => esc_html__( 'Active Background Color', 'tlp-food-menu' ),
			'selectors' => [
				'{{WRAPPER}} .fmp-elementor-isotope .button-group .selected:before' => 'background: {{VALUE}}',
				'{{WRAPPER}} .fmp-elementor-isotope .button-group .selected:after' => 'border-top-color: {{VALUE}}',
			],
		];

		$obj->endTab();
		$obj->endTabGroup();

		$obj->elHeading( 'fmp_filter_buttons_border_note', esc_html__( 'Border', 'tlp-food-menu' ), 'before' );

		$obj->elControls[] = [
			'mode'     => 'group',
			'type'     => 'border',
			'id'       => 'fmp_filter_button_border',
			'selector' => '{{WRAPPER}} .fmp-elementor-isotope .button-group button',
		];

		$obj->elControls[] = [
			'type'      => 'color',
			'id'        => 'fmp_filter_button_hover_border_color',
			'label'     => esc_html__( 'Hover Border Color', 'tlp-food-menu' ),
			'condition' => [ 'fmp_filter_button_border_border!' => [ '' ] ],
			'selectors' => [
				'{{WRAPPER}} .fmp-elementor-isotope .button-group button:hover' => 'border-color: {{VALUE}}',
			],
		];

		$obj->elControls[] = [
			'type'      => 'color',
			'id'        => 'fmp_filter_button_active_border_color',
			'label'     => esc_html__( 'Active Border Color', 'tlp-food-menu' ),
			'condition' => [ 'fmp_filter_button_border_border!' => [ '' ] ],
			'selectors' => [
				'{{WRAPPER}} .fmp-elementor-isotope .button-group .selected' => 'border-color: {{VALUE}}',
				'{{WRAPPER}} .fmp-iso-filter.type-3 button::before' => 'background: {{VALUE}}',
			],
		];

		$obj->elControls[] = [
			'mode'       => 'responsive',
			'type'       => 'dimensions',
			'id'         => 'fmp_filter_button_border_radius',
			'label'      => esc_html__( 'Border Radius', 'tlp-food-menu' ),
			'size_units' => [ 'px', '%' ],
			'default'    => [
				'unit'     => 'px',
				'isLinked' => true,
			],
			'selectors'  => [
				'{{WRAPPER}} .fmp-elementor-isotope .button-group button' => 'border-radius: {{TOP}}{{UNIT}} {{RIGHT}}{{UNIT}} {{BOTTOM}}{{UNIT}} {{LEFT}}{{UNIT}}; overflow: hidden;',
			],
		];

		$obj->elHeading( 'fmp_filter_buttons_spacing_note', esc_html__( 'Spacing', 'tlp-food-menu' ), 'before' );

		$obj->elControls[] = [
			'mode'       => 'responsive',
			'type'       => 'dimensions',
			'id'         => 'fmp_filter_buttons_wrapper_padding',
			'label'      => esc_html__( 'Wrapper Padding', 'tlp-food-menu' ),
			'size_units' => [ 'px', '%', 'em' ],
			'selectors'  => [
				'{{WRAPPER}} .fmp-elementor-isotope .button-group' => 'margin: {{TOP}}{{UNIT}} {{RIGHT}}{{UNIT}} {{BOTTOM}}{{UNIT}} {{LEFT}}{{UNIT}};',
			],
		];

		$obj->elControls[] = [
			'mode'       => 'responsive',
			'type'       => 'dimensions',
			'id'         => 'fmp_filter_buttons_padding',
			'label'      => esc_html__( 'Padding', 'tlp-food-menu' ),
			'size_units' => [ 'px', '%', 'em' ],
			'selectors'  => [
				'{{WRAPPER}} .fmp-elementor-isotope .button-group button' => 'padding: {{TOP}}{{UNIT}} {{RIGHT}}{{UNIT}} {{BOTTOM}}{{UNIT}} {{LEFT}}{{UNIT}};',
			],
		];

		$obj->elControls[] = [
			'mode'       => 'responsive',
			'type'       => 'dimensions',
			'id'         => 'fmp_filter_buttons_margin',
			'label'      => esc_html__( 'Margin', 'tlp-food-menu' ),
			'size_units' => [ 'px', '%', 'em' ],
			'selectors'  => [
				'{{WRAPPER}} .fmp-elementor-isotope .button-group button' => 'margin: {{TOP}}{{UNIT}} {{RIGHT}}{{UNIT}} {{BOTTOM}}{{UNIT}} {{LEFT}}{{UNIT}};',
			],
		];

		$obj->endSection();

		return new static();
	}

	/**
	 * Color Title section
	 *
	 * @param object $obj Reference object.
	 * @return static
	 */
	public static function searchButton( $obj ) {

		$obj->startSection(
			'fmp_search_button_sectoin',
			esc_html__( 'Search Button', 'tlp-food-menu' ),
			self::$tab,
		);

		$obj->elControls[] = [
			'mode'     => 'group',
			'type'     => 'typography',
			'id'       => 'fmp_search_typography',
			'selector' => '{{WRAPPER}} .iso-search .iso-search-input',
		];

		$obj->elControls[] = [
			'type'      => 'color',
			'id'        => 'fmp_search_color',
			'label'     => esc_html__( 'Placeholder Color', 'tlp-food-menu' ),
			'selectors' => [
				'{{WRAPPER}} .iso-search .iso-search-input::placeholder' => 'color: {{VALUE}}',
			],
		];

		$obj->elControls[] = [
			'type'      => 'color',
			'id'        => 'fmp_search_txt_color',
			'label'     => esc_html__( 'Text Color', 'tlp-food-menu' ),
			'selectors' => [
				'{{WRAPPER}} .iso-search .iso-search-input' => 'color: {{VALUE}}',
			],
		];

		$obj->elControls[] = [
			'type'      => 'color',
			'id'        => 'fmp_search_bg_color',
			'label'     => esc_html__( 'BG Color', 'tlp-food-menu' ),
			'selectors' => [
				'{{WRAPPER}} .iso-search .iso-search-input' => 'background: {{VALUE}}',
			],
		];

		$obj->elControls[] = [
			'mode'     => 'group',
			'type'     => 'border',
			'label'    => esc_html__( 'Border', 'tlp-food-menu' ),
			'id'       => 'fmp_search_border',
			'selector' => '{{WRAPPER}} .iso-search .iso-search-input',
		];

		$obj->endSection();

		return new static();
	}


    /**
     * Color Image section
     *
     * @param object $obj Reference object.
     * @return static
     */
    public static function priceSection( $obj ) {
        $obj->startSection( 'price_style_section', esc_html__( 'Price', 'tlp-food-menu' ), self::$tab );

	    $obj->elControls[] = [
		    'mode'     => 'group',
		    'type'     => 'typography',
		    'id'       => 'fmp_price_typography',
		    'selector' => '{{WRAPPER}} .fmp-wrapper .price, {{WRAPPER}} .fmp-price-wrapper .fmp-price .amount',
	    ];

	    $obj->elControls[] = [
		    'type'      => 'color',
		    'id'        => 'fmp_price_color',
		    'label'     => esc_html__( 'Color', 'tlp-food-menu' ),
		    'selectors' => [
			    '{{WRAPPER}} .fmp-wrapper .price, {{WRAPPER}} .fmp-price-wrapper .fmp-price .amount' => 'color: {{VALUE}}',
		    ],
	    ];

	    $obj->elControls[] = [
		    'type'      => 'color',
		    'id'        => 'fmp_title_bg_color7',
		    'label'     => esc_html__( 'Background Color', 'tlp-food-menu' ),
		    'selectors' => [
			    '{{WRAPPER}} .fmp-content-wraper7 .price' => 'background-color: {{VALUE}}',
		    ],
		    'condition' => [
			    'fmp_layout' => [ 'layout7'],
		    ],
	    ];

	    $obj->elControls[] = [
		    'type'      => 'color',
		    'id'        => 'fmp_title_bg_color_grid1',
		    'label'     => esc_html__( 'BG Color (Only Grid Layout 1) ', 'tlp-food-menu' ),
		    'selectors' => [
			    '{{WRAPPER}} .fmp-price-wrapper .fmp-price' => 'background: {{VALUE}}',
			    '{{WRAPPER}} .fmp-price-wrapper .fmp-price::before' => 'border-right-color: {{VALUE}}',
			    '{{WRAPPER}} .fmp-price-wrapper .fmp-price::after' => 'border-top-color: {{VALUE}}',
			    '{{WRAPPER}} .fmp-price-wrapper span.fmp-price::after' => 'border-left-color: {{VALUE}}',
		    ],
		    'condition' => [
			    'fmp_layout' => [ 'layout1'],
		    ],
	    ];

	    $obj->elControls[] = [
		    'type'      => 'color',
		    'id'        => 'fmp_title_bg_color_grid4',
		    'label'     => esc_html__( 'BG Color (Only Grid Layout 4) ', 'tlp-food-menu' ),
		    'selectors' => [
			    '{{WRAPPER}} .fmp-price-wrapper .fmp-price::after' => 'background: {{VALUE}}',
		    ],
		    'condition' => [
			    'fmp_layout' => [ 'layout4'],
		    ],
	    ];

	    $obj->elControls[] = [
		    'type'      => 'color',
		    'id'        => 'fmp_title_bg_color_grid5',
		    'label'     => esc_html__( 'BG Color (Only Grid Layout 5) ', 'tlp-food-menu' ),
		    'selectors' => [
			    '{{WRAPPER}} .fmp-box-wrapper .fmp-price-wrapper .fmp-price' => 'background: {{VALUE}}',
		    ],
		    'condition' => [
			    'fmp_layout' => [ 'layout5'],
		    ],
	    ];
	    $obj->elControls[] = [
		    'mode'     => 'group',
		    'type'     => 'border',
		    'label'    => esc_html__( 'Border (Only Grid Layout 5)', 'tlp-food-menu' ),
		    'id'       => 'fmp_sec_border5',
		    'selector' => '{{WRAPPER}} .fmp-price-wrapper .fmp-price',
		    'condition' => [
			    'fmp_layout' => [ 'layout5' ],
		    ],
	    ];

        $obj->endSection();

        return new static();
    }

    /**
     * Color Image section
     *
     * @param object $obj Reference object.
     * @return static
     */
    public static function excerptSection( $obj ) {
        $obj->startSection( 'excerpt_style_section', esc_html__( 'Excerpt', 'tlp-food-menu' ), self::$tab );

	    $obj->elControls[] = [
		    'mode'     => 'group',
		    'type'     => 'typography',
		    'id'       => 'fmp_content_typography',
		    'selector' => '{{WRAPPER}} .fmp-wrapper .fmp-body p, {{WRAPPER}} .fmp-wrapper .fmp-body',
	    ];

	    $obj->elControls[] = [
		    'type'      => 'color',
		    'id'        => 'fmp_content_color',
		    'label'     => esc_html__( 'Color', 'tlp-food-menu' ),
		    'selectors' => [
			    '{{WRAPPER}} .fmp-body p' => 'color: {{VALUE}}',
			    '{{WRAPPER}} .fmp-body' => 'color: {{VALUE}}',
		    ],
	    ];

        $obj->endSection();

        return new static();
    }

	/**
	 * Color Image section
	 *
	 * @param object $obj Reference object.
	 * @return static
	 */
	public static function imageSection( $obj ) {
		$obj->startSection( 'image_style_section', esc_html__( 'Image', 'tlp-food-menu' ), self::$tab );
		$obj->elControls[] = [
			'mode'       => 'responsive',
			'type'       => 'dimensions',
			'id'         => 'fmp_image_border_radius',
			'label'      => esc_html__( 'Border Radius', 'tlp-food-menu' ),
			'size_units' => [ 'px', '%', 'em' ],
			'selectors'  => [
				'{{WRAPPER}} .fmp-image-wrap, {{WRAPPER}} .fmp-layout8 .fmp-box .fmp-image-wrap > a' => 'border-radius: {{TOP}}{{UNIT}} {{RIGHT}}{{UNIT}} {{BOTTOM}}{{UNIT}} {{LEFT}}{{UNIT}};',
			],
		];
		$obj->endSection();
		return new static();
	}

	/**
	 * Category Style
	 *
	 * @param object $obj Reference object.
	 * @return static
	 */
	public static function catSection( $obj ) {

		$obj->startSection( 'image_cat_section', esc_html__( 'Category', 'tlp-food-menu' ), self::$tab );

		$obj->elControls[] = [
			'mode'     => 'group',
			'type'     => 'typography',
			'id'       => 'fmp_cat_typography',
			'selector' => '{{WRAPPER}} .fmp-category-title-wrapper .fmp-category-title span',
		];

		$obj->elControls[] = [
			'type'      => 'color',
			'id'        => 'fmp_cat_color',
			'label'     => esc_html__( 'Color', 'tlp-food-menu' ),
			'selectors' => [
				'{{WRAPPER}} .fmp-category-title-wrapper .fmp-category-title' => 'color: {{VALUE}}',
			],
		];

		$obj->elControls[] = [
			'type'      => 'color',
			'id'        => 'fmp_cat_bg_color',
			'label'     => esc_html__( 'Background Color', 'tlp-food-menu' ),
			'selectors' => [
				'{{WRAPPER}} .fmp-category-title-wrapper .fmp-category-title:before' => 'background: {{VALUE}}',
				'{{WRAPPER}} .fmp-layout7 .fmp-category-title-wrapper' => 'background: {{VALUE}} !important',
			],
			'condition'       => [

				'fmp_layout!' => [ 'layout3', 'layout6' ]
			],
		];

		$obj->elControls[] = [
			'type'      => 'heading',
			'id'        => 'only_image_change',
			'label'     => esc_html__( 'Only Image Change', 'tlp-food-menu' ),
			'condition'       => [
				'fmp_cat_style!' => [ 'type-2'],
				'fmp_layout!' => [ 'layout3', 'layout6', 'layout7' ]
			],
		];

		$obj->elControls[] = [
			'mode'            => 'group',
			'type'            => 'background',
			'id'              => 'fmp_cat_gradient_background',
			'selector'        => '{{WRAPPER}} .fmp-category-title-wrapper .fmp-category-title:before',
			'title'           => esc_html__( 'Background Image', 'tlp-food-menu' ),
			'background_type' => 'classic',
			'types'           => [ 'classic' ],
			'fields_options'  => array(
				'background_color' => array( 'default' => '' ),
				'background_image' => array( 'default' => '' ),
				'background_gradient' => array( 'default' => '' ),
			),
			'condition'       => [
				'fmp_cat_style!' => [ 'type-2'],
				'fmp_layout!' => [ 'layout3', 'layout6', 'layout7' ]
			],


		];

		$obj->elControls[] = [
			'type'      => 'color',
			'id'        => 'fmp_border_color',
			'label'     => esc_html__( 'Border Color', 'tlp-food-menu' ),
			'selectors' => [
				'{{WRAPPER}} .fmp-category-title-wrapper .fmp-category-title::before' => 'background-color: {{VALUE}}',
			],
			'condition'       => [
				'fmp_cat_style!' => [ 'type-1', 'type-3', 'type-4' ],
				'fmp_layout' => [ 'layout3', 'layout6' ]
			],
		];

		$obj->endSection();
		return new static();
	}


    /**
     * Color Global section
     *
     * @param object $obj Reference object.
     * @return static
     */
    public static function globaltSection( $obj ) {
        $obj->startSection( 'global_style_section', esc_html__( 'Global Style', 'tlp-food-menu' ), self::$tab );

	    $obj->elControls[] = [
		    'type'      => 'color',
		    'id'        => 'fmp_sec_color',
		    'label'     => esc_html__( 'Background Color', 'tlp-food-menu' ),
		    'selectors' => [
			    '{{WRAPPER}} .fmp-food-item, {{WRAPPER}} .fmp-el-global' => 'background-color: {{VALUE}}',
		    ],
		    'condition' => [
				'fmp_layout!' => [ 'layout5' ]
		    ],
	    ];

	    $obj->elControls[] = [
		    'type'      => 'color',
		    'id'        => 'fmp_sec_color5',
		    'label'     => esc_html__( 'Background Color', 'tlp-food-menu' ),
		    'selectors' => [
			    '{{WRAPPER}} .fmp-layout5 .fmp-innner-wrap:before' => 'background-color: {{VALUE}}',
			    '{{WRAPPER}} .fmp-el-global' => 'background-color: {{VALUE}}',
		    ],
		    'condition' => [
				'fmp_layout' => [ 'layout5' ]
		    ],
	    ];

	    $obj->elControls[] = [
		    'type'      => 'color',
		    'id'        => 'fmp_border_color5',
		    'label'     => esc_html__( 'Border Color', 'tlp-food-menu' ),
		    'selectors' => [
			    '{{WRAPPER}} .fmp-layout5 .fmp-row::after' => 'background: {{VALUE}}',
		    ],
		    'condition' => [
				'fmp_layout' => [ 'layout5' ]
		    ],
	    ];

	    $obj->elControls[] = [
		    'mode'     => 'group',
		    'type'     => 'border',
		    'label'    => esc_html__( 'Border', 'tlp-food-menu' ),
		    'id'       => 'fmp_sec_border',
		    'selector' => '{{WRAPPER}} .fmp-food-item, {{WRAPPER}} .fmp-el-global',
		    'condition' => [
			    'fmp_layout!' => [ 'layout5', 'layout6' ],
		    ],
	    ];

	    $obj->elControls[] = [
		    'mode'     => 'group',
		    'type'     => 'border',
		    'label'    => esc_html__( 'Border', 'tlp-food-menu' ),
		    'id'       => 'fmp_sec_border6',
		    'selector' => '{{WRAPPER}} .fmp-grid-by-cat-free.fmp-layout6 .fmp-innner-wrap',
		    'condition' => [
			    'fmp_layout' => [  'layout6' ],
		    ],
	    ];

	    $obj->elControls[] = [
		    'mode'       => 'responsive',
		    'type'       => 'dimensions',
		    'id'         => 'fmp_sec_border_radius',
		    'label'      => esc_html__( 'Border Radius', 'tlp-food-menu' ),
		    'size_units' => [ 'px', '%', 'em' ],
		    'selectors'  => [
			    '{{WRAPPER}} .fmp-food-item, {{WRAPPER}} .fmp-el-global' => 'border-radius: {{TOP}}{{UNIT}} {{RIGHT}}{{UNIT}} {{BOTTOM}}{{UNIT}} {{LEFT}}{{UNIT}};',
		    ],
		    'condition' => [
			    'fmp_layout!' => [ 'layout5' ]
		    ],
	    ];

	    $obj->elControls[] = [
		    'mode'       => 'responsive',
		    'type'       => 'dimensions',
		    'id'         => 'fmp_sec_border_radius5',
		    'label'      => esc_html__( 'Border Radius', 'tlp-food-menu' ),
		    'size_units' => [ 'px', '%', 'em' ],
		    'selectors'  => [
			    '{{WRAPPER}} .fmp-layout5 .fmp-innner-wrap:before, {{WRAPPER}} .fmp-el-global' => 'border-radius: {{TOP}}{{UNIT}} {{RIGHT}}{{UNIT}} {{BOTTOM}}{{UNIT}} {{LEFT}}{{UNIT}};',
		    ],
		    'condition' => [
			    'fmp_layout' => [ 'layout5' ]
		    ],
	    ];

	    $obj->elControls[] = [
		    'mode'       => 'responsive',
		    'type'       => 'dimensions',
		    'id'         => 'fmp_padding',
		    'label'      => esc_html__( 'Padding', 'tlp-food-menu' ),
		    'size_units' => [ 'px', '%', 'em' ],
		    'selectors'  => [
			    '{{WRAPPER}} .fmp-food-item, {{WRAPPER}} .fmp-el-global' => 'padding: {{TOP}}{{UNIT}} {{RIGHT}}{{UNIT}} {{BOTTOM}}{{UNIT}} {{LEFT}}{{UNIT}};',
		    ],
		    'condition' => [
			    'fmp_layout!' => [ 'layout5', 'layout6' ],
		    ],
	    ];

	    $obj->elControls[] = [
		    'mode'       => 'responsive',
		    'type'       => 'dimensions',
		    'id'         => 'fmp_margin',
		    'label'      => esc_html__( 'Margin', 'tlp-food-menu' ),
		    'size_units' => [ 'px', '%', 'em' ],
		    'selectors'  => [
			    '{{WRAPPER}} .fmp-food-item, {{WRAPPER}} .fmp-el-global' => 'margin: {{TOP}}{{UNIT}} {{RIGHT}}{{UNIT}} {{BOTTOM}}{{UNIT}} {{LEFT}}{{UNIT}};',
		    ],
		    'condition' => [
			    'fmp_layout!' => [ 'layout5', 'layout6' ],
		    ],
	    ];

	    $obj->elControls[] = [
		    'mode'       => 'responsive',
		    'type'       => 'dimensions',
		    'id'         => 'fmp_padding6',
		    'label'      => esc_html__( 'Padding', 'tlp-food-menu' ),
		    'size_units' => [ 'px', '%', 'em' ],
		    'selectors'  => [
			    '{{WRAPPER}} .fmp-layout6 .fmp-innner-wrap' => 'padding: {{TOP}}{{UNIT}} {{RIGHT}}{{UNIT}} {{BOTTOM}}{{UNIT}} {{LEFT}}{{UNIT}};',
		    ],
		    'condition' => [
			    'fmp_layout' => [ 'layout6' ],
		    ],
	    ];

	    $obj->elControls[] = [
		    'mode'       => 'responsive',
		    'type'       => 'dimensions',
		    'id'         => 'fmp_margin6',
		    'label'      => esc_html__( 'Margin', 'tlp-food-menu' ),
		    'size_units' => [ 'px', '%', 'em' ],
		    'selectors'  => [
			    '{{WRAPPER}} .fmp-layout6 .fmp-innner-wrap' => 'margin: {{TOP}}{{UNIT}} {{RIGHT}}{{UNIT}} {{BOTTOM}}{{UNIT}} {{LEFT}}{{UNIT}};',
		    ],
		    'condition' => [
			    'fmp_layout' => [ 'layout6' ],
		    ],
	    ];

	    $obj->elControls[] = [
		    'mode'     => 'group',
		    'type'     => 'shadow',
		    'label'    => esc_html__( 'Box Shadow', 'tlp-food-menu' ),
		    'id'       => 'fmp_box_shadow',
		    'selector' => '{{WRAPPER}} .fmp-food-item, {{WRAPPER}} .fmp-el-global',
		    'condition' => [
			    'fmp_layout!' => [ 'layout5', 'layout6' ],
		    ],
	    ];

        $obj->endSection();

        return new static();
    }

	/**
	 * Pagination Style Section
	 *
	 * @param object $obj Reference object.
	 * @return static
	 */
	public static function pagination( $obj ) {
		$condition = [
			'fmp_pagination' => [ 'yes' ],
		];

		$activeCondition = [

		];

		$obj->startSection( 'buttons_section', esc_html__( 'Pagination Style', 'tlp-food-menu' ), self::$tab, [], $condition );

		$obj->elControls[] = [
			'mode'     => 'group',
			'type'     => 'typography',
			'id'       => 'tlp_buttons_typography',
			'exclude'  => [ 'font_family', 'word_spacing', 'letter_spacing', 'text_transform', 'font_style', 'text_decoration' ],
			'selector' => '{{WRAPPER}} .fmp-load-more-btn, {{WRAPPER}} .fmp-wrapper .fmp-pagination li, {{WRAPPER}} .fmp-wrapper .fmp-pagination .page-num, {{WRAPPER}} .rt-elementor-container .rt-pagination-wrap .rt-loadmore-btn',
		];

		$obj->elControls[] = [
			'mode'      => 'responsive',
			'id'        => 'tlp_buttons_alignment',
			'type'      => 'choose',
			'label'     => esc_html__( 'Alignment', 'tlp-food-menu' ),
			'options'   => [
				'flex-start' => [
					'title' => esc_html__( 'Left', 'tlp-food-menu' ),
					'icon'  => 'eicon-text-align-left',
				],
				'center'     => [
					'title' => esc_html__( 'Center', 'tlp-food-menu' ),
					'icon'  => 'eicon-text-align-center',
				],
				'flex-end'   => [
					'title' => esc_html__( 'Right', 'tlp-food-menu' ),
					'icon'  => 'eicon-text-align-right',
				],
			],
			'selectors' => [
				'{{WRAPPER}} .fmp-wrapper .fmp-pagination, {{WRAPPER}} .fmp-wrapper .fmp-pagination ul, {{WRAPPER}} .rt-elementor-container .rt-pagination-wrap' => 'justify-content: {{VALUE}}',
			],
		];

		$obj->elHeading( 'tlp_buttons_colors_note', esc_html__( 'Colors', 'tlp-food-menu' ), 'before' );

		$obj->startTabGroup( 'button_color_tabs' );
		$obj->startTab( 'button_color_tab', esc_html__( 'Normal', 'tlp-food-menu' ) );

		$obj->elControls[] = [
			'type'      => 'color',
			'id'        => 'tlp_button_color',
			'label'     => esc_html__( 'Color', 'tlp-food-menu' ),
			'selectors' => [
				'{{WRAPPER}} .fmp-wrapper .fmp-pagination .fmp-load-more-btn, {{WRAPPER}} .fmp-wrapper .fmp-pagination li, {{WRAPPER}} .fmp-wrapper .fmp-pagination .page-num' => 'color: {{VALUE}}',
			],
		];

		$obj->elControls[] = [
			'type'      => 'color',
			'id'        => 'tlp_button_bg_color',
			'label'     => esc_html__( 'Background Color', 'tlp-food-menu' ),
			'selectors' => [
				'{{WRAPPER}} .ajax-el-number-pagination a, {{WRAPPER}} .fmp-pagination ul.pagination-list li a::before' => 'background-color: {{VALUE}}',
				'{{WRAPPER}} .fmp-wrapper .fmp-pagination .fmp-load-more-btn' => 'background: {{VALUE}}',
			],
		];

		$obj->endTab();
		$obj->startTab( 'button_hover_color_tab', esc_html__( 'Hover', 'tlp-food-menu' ) );

		$obj->elControls[] = [
			'type'      => 'color',
			'id'        => 'tlp_button_hover_color',
			'label'     => esc_html__( 'Hover Color', 'tlp-food-menu' ),
			'selectors' => [
				'{{WRAPPER}} .fmp-wrapper .fmp-pagination .fmp-load-more-btn:hover, {{WRAPPER}} .fmp-wrapper .fmp-pagination li:hover, {{WRAPPER}} .fmp-wrapper .fmp-pagination .page-num:hover' => 'color: {{VALUE}}',
			],
		];

		$obj->elControls[] = [
			'type'      => 'color',
			'id'        => 'tlp_button_hover_bg_color',
			'label'     => esc_html__( 'Hover Background Color', 'tlp-food-menu' ),
			'selectors' => [
				'{{WRAPPER}} .fmp-wrapper .fmp-pagination .fmp-load-more-btn:hover' => 'background: {{VALUE}}',
				'{{WRAPPER}} .ajax-el-number-pagination a:before' => 'background: {{VALUE}}',
				'{{WRAPPER}} .fmp-pagination ul.pagination-list li a::after' => 'background: {{VALUE}}',
			],
		];

		$obj->endTab();

		$obj->startTab( 'button_active_color_tab', esc_html__( 'Active', 'tlp-food-menu' ), [], $activeCondition );

		$obj->elControls[] = [
			'type'      => 'color',
			'id'        => 'tlp_button_active_color',
			'label'     => esc_html__( 'Active Color', 'tlp-food-menu' ),
			'selectors' => [
				'{{WRAPPER}} .fmp-wrapper .fmp-pagination li.active span, {{WRAPPER}} .fmp-wrapper .fmp-pagination .page-num.active' => 'color: {{VALUE}} !important',
			],
			'condition' => [ 'fmp_pagination_type!' => [ 'ajax-load-more-button' ] ],
		];

		$obj->elControls[] = [
			'type'      => 'color',
			'id'        => 'tlp_button_active_bg_color',
			'label'     => esc_html__( 'Active Background Color', 'tlp-food-menu' ),
			'selectors' => [
				'{{WRAPPER}} .fmp-pagination ul.pagination-list li.active span::before, {{WRAPPER}} .fmp-wrapper .fmp-pagination li.active span:after, {{WRAPPER}} .fmp-wrapper .fmp-pagination .page-num.active:before' => 'background: {{VALUE}}',
			],
			'condition' => [ 'fmp_pagination_type!' => [ 'ajax-load-more-button' ] ],
		];

		$obj->endTab( [], $activeCondition );
		$obj->endTabGroup();

		$obj->elHeading( 'tlp_buttons_border_note', esc_html__( 'Border', 'tlp-food-menu' ), 'before' );

		$obj->elControls[] = [
			'mode'     => 'group',
			'type'     => 'border',
			'id'       => 'tlp_button_border',
			'selector' => '{{WRAPPER}} .fmp-wrapper .fmp-pagination .fmp-load-more-btn, {{WRAPPER}} .fmp-wrapper .fmp-pagination .page-num, {{WRAPPER}} .fmp-wrapper .fmp-pagination li',
		];

		$obj->elControls[] = [
			'type'      => 'color',
			'id'        => 'tlp_button_hover_border_color',
			'label'     => __( 'Hover Border Color', 'tlp-food-menu' ),
			'condition' => [ 'tlp_button_border_border!' => [ '' ] ],
			'selectors' => [
				'{{WRAPPER}} .fmp-wrapper .fmp-pagination .fmp-load-more-btn, {{WRAPPER}} .fmp-wrapper .fmp-pagination .page-num:hover, {{WRAPPER}} .fmp-wrapper .fmp-pagination li:hover' => 'border-color: {{VALUE}}',
			],
		];

		$obj->elControls[] = [
			'type'      => 'color',
			'id'        => 'tlp_button_active_border_color',
			'label'     => esc_html__( 'Active Border Color', 'tlp-food-menu' ),
			'condition' => [
				'tlp_button_border_border!' => [ '' ],
			],
			'selectors' => [
				'{{WRAPPER}} .fmp-wrapper .fmp-pagination .page-num.active, {{WRAPPER}} .fmp-wrapper .fmp-pagination li.active' => 'border-color: {{VALUE}}',
			],
		];

		$obj->elControls[] = [
			'mode'       => 'responsive',
			'type'       => 'dimensions',
			'id'         => 'tlp_button_border_radius',
			'label'      => esc_html__( 'Border Radius', 'tlp-food-menu' ),
			'size_units' => [ 'px', '%' ],
			'default'    => [
				'unit'     => 'px',
				'isLinked' => true,
			],
			'selectors'  => [
				'{{WRAPPER}} .fmp-wrapper .fmp-pagination .fmp-load-more-btn, {{WRAPPER}} .fmp-wrapper .fmp-pagination .page-num, {{WRAPPER}} .fmp-wrapper .fmp-pagination li' => 'border-radius: {{TOP}}{{UNIT}} {{RIGHT}}{{UNIT}} {{BOTTOM}}{{UNIT}} {{LEFT}}{{UNIT}};',
			],
		];

		$obj->elHeading( 'tlp_buttons_spacing_note', esc_html__( 'Spacing', 'tlp-food-menu' ), 'before' );

		$obj->elControls[] = [
			'mode'       => 'responsive',
			'type'       => 'dimensions',
			'id'         => 'tlp_buttons_wrapper_padding',
			'label'      => esc_html__( 'Wrapper Margin', 'tlp-food-menu' ),
			'size_units' => [ 'px', '%', 'em' ],
			'selectors'  => [
				'{{WRAPPER}} .fmp-wrapper .fmp-pagination, {{WRAPPER}} .fmp-pagination ul.pagination-list, {{WRAPPER}} .ajax-el-number-pagination' => 'margin: {{TOP}}{{UNIT}} {{RIGHT}}{{UNIT}} {{BOTTOM}}{{UNIT}} {{LEFT}}{{UNIT}};',
			],
		];


		$obj->elControls[] = [
			'mode'       => 'responsive',
			'type'       => 'dimensions',
			'id'         => 'tlp_buttons_margin',
			'label'      => esc_html__( 'Margin', 'tlp-food-menu' ),
			'size_units' => [ 'px', '%', 'em' ],
			'selectors'  => [
				'{{WRAPPER}} .fmp-pagination ul.pagination-list li' => 'margin: {{TOP}}{{UNIT}} {{RIGHT}}{{UNIT}} {{BOTTOM}}{{UNIT}} {{LEFT}}{{UNIT}};',
			],
			'condition' => [ 'fmp_pagination_type!' => [ 'ajax-load-more-button' ] ],
		];

		$obj->endSection();

		return new static();
	}



	/**
	 * Slider Style section
	 *
	 * @param object $obj Reference object.
	 * @param string $conditions Condition.
	 * @return static
	 */
	public static function sliderStyle( $obj ) {
		$conditions = [
			'relation' => 'or',
			'terms'    => [
				[
					'name'     => 'fmp_slider_nav',
					'operator' => '==',
					'value'    => 'yes',
				],
				[
					'name'     => 'fmp_slider_pagi',
					'operator' => '==',
					'value'    => 'yes',
				],
			],
		];

		$arrow_condition = [ 'fmp_slider_nav' => [ 'yes' ] ];
		$dot_condition   = [ 'fmp_slider_pagi' => [ 'yes' ] ];

		$obj->startSection( 'buttons_section', esc_html__( 'Slider Style', 'tlp-food-menu' ), self::$tab, $conditions );
		$obj->elHeading( 'fmp_buttons_typography_note', esc_html__( 'Arrow Size', 'tlp-food-menu' ), 'null', [], $arrow_condition );

		$obj->elControls[] = [
			'type'      => 'slider',
			'mode'      => 'responsive',
			'id'        => 'fmp_arrow_size',
			'label'     => esc_html__( 'Arrow Size', 'tlp-food-menu' ),
			'range'     => [
				'px' => [
					'min'  => 1,
					'max'  => 100,
					'step' => 1,
				],
			],
			'default'   => [
				'unit' => 'px',
				'size' => 16,
			],
			'selectors' => [
				'{{WRAPPER}} .fmp-carousel .swiper-arrow' => 'font-size: {{SIZE}}{{UNIT}}',
			],
			'condition' => $arrow_condition,
		];

		$obj->elControls[] = [
			'type'      => 'slider',
			'mode'      => 'responsive',
			'id'        => 'fmp_arrow_width',
			'label'     => esc_html__( 'Arrow Width', 'tlp-food-menu' ),
			'range'     => [
				'px'  => [
					'min'  => 1,
					'max'  => 100,
					'step' => 1,
				],
				'em'  => [
					'min'  => 0.1,
					'max'  => 10,
					'step' => 0.1,
				],
				'rem' => [
					'min'  => 0.1,
					'max'  => 10,
					'step' => 0.1,
				],
			],
			'default'   => [
				'unit' => 'px',
				'size' => 36,
			],
			'selectors' => [
				'{{WRAPPER}} .fmp-carousel .swiper-arrow' => 'width: {{SIZE}}{{UNIT}}',
			],
			'condition' => $arrow_condition,
		];

		$obj->elControls[] = [
			'type'      => 'slider',
			'mode'      => 'responsive',
			'id'        => 'fmp_arrow_height',
			'label'     => esc_html__( 'Arrow Height', 'tlp-food-menu' ),
			'range'     => [
				'px' => [
					'min'  => 1,
					'max'  => 100,
					'step' => 1,
				],
			],
			'default'   => [
				'unit' => 'px',
				'size' => 36,
			],
			'selectors' => [
				'{{WRAPPER}} .fmp-carousel .swiper-arrow' => 'height: {{SIZE}}{{UNIT}}',
			],
			'condition' => $arrow_condition,
		];

		$obj->elHeading( 'fmp_dot_size_note', esc_html__( 'Dot Size', 'tlp-food-menu' ), 'before', [], $dot_condition );

		$obj->elControls[] = [
			'type'      => 'slider',
			'mode'      => 'responsive',
			'id'        => 'fmp_dot_width',
			'label'     => esc_html__( 'Dot Width', 'tlp-food-menu' ),
			'range'     => [
				'px'  => [
					'min'  => 1,
					'max'  => 100,
					'step' => 1,
				],
				'em'  => [
					'min'  => 0.1,
					'max'  => 10,
					'step' => 0.1,
				],
				'rem' => [
					'min'  => 0.1,
					'max'  => 10,
					'step' => 0.1,
				],
			],
			'default'   => [
				'unit' => 'px',
				'size' => 10,
			],
			'selectors' => [
				'{{WRAPPER}} .fmp-container-fluid .fmp-carousel.swiper .swiper-pagination-bullet' => 'width: {{SIZE}}{{UNIT}}',
			],
			'condition' => $dot_condition,
		];

		$obj->elControls[] = [
			'type'      => 'slider',
			'mode'      => 'responsive',
			'id'        => 'fmp_dot_height',
			'label'     => esc_html__( 'Dot Height', 'tlp-food-menu' ),
			'range'     => [
				'px' => [
					'min'  => 1,
					'max'  => 100,
					'step' => 1,
				],
			],
			'default'   => [
				'unit' => 'px',
				'size' => 10,
			],
			'selectors' => [
				'{{WRAPPER}} .fmp-container-fluid .fmp-carousel.swiper .swiper-pagination-bullet' => 'height: {{SIZE}}{{UNIT}}',
			],
			'condition' => $dot_condition,
		];

		$obj->elHeading( 'fmp_buttons_colors_note', esc_html__( 'Colors', 'tlp-food-menu' ), 'before' );

		$obj->startTabGroup( 'button_color_tabs' );
		$obj->startTab( 'button_color_tab', esc_html__( 'Normal', 'tlp-food-menu' ) );

		$obj->elControls[] = [
			'type'      => 'color',
			'id'        => 'fmp_button_color',
			'label'     => esc_html__( 'Color', 'tlp-food-menu' ),
			'selectors' => [
				'{{WRAPPER}} .fmp-carousel .swiper-arrow' => 'color: {{VALUE}}',
			],
			'condition' => $arrow_condition,
		];

		$obj->elControls[] = [
			'type'      => 'color',
			'id'        => 'fmp_button_bg_color',
			'label'     => esc_html__( 'Background Color', 'tlp-food-menu' ),
			'selectors' => [
				'{{WRAPPER}} .fmp-carousel .swiper-arrow:before, {{WRAPPER}} .swiper-pagination-bullet' => 'background: {{VALUE}}',
			],
		];

		$obj->elControls[] = [
			'mode'     => 'group',
			'type'     => 'border',
			'id'       => 'fmp_button_border',
			'selector' => '{{WRAPPER}} .fmp-carousel .swiper-arrow, {{WRAPPER}} .swiper-pagination-bullet',
		];

		$obj->endTab();
		$obj->startTab( 'button_hover_color_tab', esc_html__( 'Hover', 'tlp-food-menu' ) );

		$obj->elControls[] = [
			'type'      => 'color',
			'id'        => 'fmp_button_hover_color',
			'label'     => esc_html__( 'Hover Color', 'tlp-food-menu' ),
			'selectors' => [
				'{{WRAPPER}} .fmp-carousel .swiper-arrow:hover' => 'color: {{VALUE}}',
			],
			'condition' => $arrow_condition,
		];

		$obj->elControls[] = [
			'type'      => 'color',
			'id'        => 'fmp_button_hover_bg_color',
			'label'     => esc_html__( 'Hover Background Color', 'tlp-food-menu' ),
			'selectors' => [
				'{{WRAPPER}} .fmp-carousel .swiper-arrow:hover:after, {{WRAPPER}} .fmp-container-fluid .fmp-carousel .swiper-pagination-bullet:hover' => 'background: {{VALUE}}',
			],
		];

		$obj->elControls[] = [
			'type'      => 'color',
			'id'        => 'fmp_button_hover_border_color',
			'label'     => esc_html__( 'Hover Border Color', 'tlp-food-menu' ),
			'selectors' => [
				'{{WRAPPER}} .fmp-carousel .swiper-arrow:hover, .fmp-container-fluid .fmp-carousel .swiper-pagination-bullet:hover' => 'border-color: {{VALUE}}',
			],
		];

		$obj->endTab();
		$obj->startTab( 'button_active_color_tab', esc_html__( 'Active', 'tlp-food-menu' ), [], $dot_condition );

		$obj->elControls[] = [
			'type'      => 'color',
			'id'        => 'fmp_button_active_bg_color',
			'label'     => esc_html__( 'Active Background Color', 'tlp-food-menu' ),
			'selectors' => [
				'{{WRAPPER}} .fmp-container-fluid .fmp-carousel .swiper-pagination-bullet-active' => 'background-color: {{VALUE}}',
			],
		];

		$obj->elControls[] = [
			'type'      => 'color',
			'id'        => 'fmp_button_active_border_color',
			'label'     => esc_html__( 'Active Border Color', 'tlp-food-menu' ),
			'selectors' => [
				'{{WRAPPER}} .fmp-container-fluid .fmp-carousel .swiper-pagination-bullet-active' => 'border-color: {{VALUE}}',
			],
		];

		$obj->endTab();
		$obj->endTabGroup();

		$obj->elHeading( 'fmp_buttons_spacing_note', esc_html__( 'Spacing', 'tlp-food-menu' ), 'before' );

		$obj->elControls[] = [
			'mode'       => 'responsive',
			'type'       => 'dimensions',
			'id'         => 'fmp_buttons_wrapper_padding',
			'label'      => esc_html__( 'Wrapper Margin', 'tlp-food-menu' ),
			'size_units' => [ 'px', '%', 'em' ],
			'selectors'  => [
				'{{WRAPPER}} .fmp-carousel.top-nav .swiper-nav, {{WRAPPER}} .fmp-container-fluid .fmp-carousel .swiper-pagination' => 'margin: {{TOP}}{{UNIT}} {{RIGHT}}{{UNIT}} {{BOTTOM}}{{UNIT}} {{LEFT}}{{UNIT}};',
			],
		];

		$obj->elControls[] = [
			'mode'       => 'responsive',
			'type'       => 'dimensions',
			'id'         => 'fmp_buttons_padding',
			'label'      => esc_html__( 'Padding', 'tlp-food-menu' ),
			'size_units' => [ 'px', '%', 'em' ],
			'selectors'  => [
				'{{WRAPPER}} .fmp-carousel .swiper-arrow, {{WRAPPER}} .swiper-pagination-bullet' => 'padding: {{TOP}}{{UNIT}} {{RIGHT}}{{UNIT}} {{BOTTOM}}{{UNIT}} {{LEFT}}{{UNIT}};',
			],
		];

		$obj->elControls[] = [
			'mode'       => 'responsive',
			'type'       => 'dimensions',
			'id'         => 'fmp_buttons_margin',
			'label'      => esc_html__( 'Margin', 'tlp-food-menu' ),
			'size_units' => [ 'px', '%', 'em' ],
			'selectors'  => [
				'{{WRAPPER}} .fmp-carousel .swiper-arrow, {{WRAPPER}} .swiper-pagination-bullet' => 'margin: {{TOP}}{{UNIT}} {{RIGHT}}{{UNIT}} {{BOTTOM}}{{UNIT}} {{LEFT}}{{UNIT}};',
			],
		];

		$obj->endSection();

		return new static();
	}


	/**
	* Readmore Button Style
	*
	* @param object $obj Reference object.
	* @return static
	*/
	public static function readmoreButton( $obj ) {
		$condition = [
			'fmp_readmore_switch' => [ 'yes' ],
		];

		$obj->startSection( 'readmore_button_style', esc_html__( 'Read More Button', 'tlp-food-menu' ), self::$tab, [], $condition );
		$obj->elControls[] = [
			'mode'     => 'group',
			'type'     => 'typography',
			'id'       => 'fmp_readmore_buttons_typography',
			'selector' => '{{WRAPPER}} .fmp-footer .fmp-btn-read-more, {{WRAPPER}} .fmp-body .fmp-btn-read-more',
		];


		$obj->elHeading( 'fmp_readmore_buttons_colors_note', esc_html__( 'Colors', 'tlp-food-menu' ), 'before' );

		$obj->startTabGroup( 'readmore_button_color_tabs' );

		$obj->startTab( 'filter_readmore_color_tab', esc_html__( 'Normal', 'tlp-food-menu' ) );

		$obj->elControls[] = [
			'type'      => 'color',
			'id'        => 'fmp_readmore_button_color',
			'label'     => esc_html__( 'Button Color', 'tlp-food-menu' ),
			'selectors' => [
				'{{WRAPPER}} .fmp-footer .fmp-btn-read-more' => 'color: {{VALUE}}',
				'{{WRAPPER}} .fmp-body .fmp-btn-read-more' => 'color: {{VALUE}}',
			],
		];

		$obj->elControls[] = [
			'type'      => 'color',
			'id'        => 'fmp_readmore_button_bg_color',
			'label'     => esc_html__( 'Button Background Color', 'tlp-food-menu' ),
			'selectors' => [
				'{{WRAPPER}} .fmp-footer .fmp-btn-read-more:before' => 'background: {{VALUE}}',
				'{{WRAPPER}} .fmp-body .fmp-btn-read-more:before' => 'background: {{VALUE}}',
			],
		];

		$obj->endTab();
		$obj->startTab( 'readmore_button_hover_color_tab', esc_html__( 'Hover', 'tlp-food-menu' ) );

		$obj->elControls[] = [
			'type'      => 'color',
			'id'        => 'fmp_readmore_button_hover_color',
			'label'     => esc_html__( 'Hover Color', 'tlp-food-menu' ),
			'selectors' => [
				'{{WRAPPER}} .fmp-footer .fmp-btn-read-more:hover' => 'color: {{VALUE}}',
				'{{WRAPPER}} .fmp-body .fmp-btn-read-more:hover' => 'color: {{VALUE}}',
			],
		];

		$obj->elControls[] = [
			'type'      => 'color',
			'id'        => 'fmp_readmore_button_hover_bg_color',
			'label'     => esc_html__( 'Hover Background Color', 'tlp-food-menu' ),
			'selectors' => [
				'{{WRAPPER}} .fmp-footer .fmp-btn-read-more:after' => 'background: {{VALUE}}',
				'{{WRAPPER}} .fmp-body .fmp-btn-read-more:after' => 'background: {{VALUE}}',
			],
		];

		$obj->endTab();
		$obj->endTabGroup();

		$obj->elHeading( 'fmp_readmore_buttons_border_note', esc_html__( 'Border', 'tlp-food-menu' ), 'before' );

		$obj->elControls[] = [
			'mode'     => 'group',
			'type'     => 'border',
			'id'       => 'fmp_readmore_button_border',
			'selector' => '{{WRAPPER}} .fmp-footer .fmp-btn-read-more, {{WRAPPER}} .fmp-body .fmp-btn-read-more',
		];

		$obj->elControls[] = [
			'type'      => 'color',
			'id'        => 'fmp_readmore_button_hover_border_color',
			'label'     => esc_html__( 'Hover Border Color', 'tlp-food-menu' ),
			'condition' => [ 'fmp_readmore_button_border_border!' => [ '' ] ],
			'selectors' => [
				'{{WRAPPER}} .fmp-footer .fmp-btn-read-more:hover' => 'border-color: {{VALUE}}',
				'{{WRAPPER}} .fmp-body .fmp-btn-read-more:hover' => 'border-color: {{VALUE}}',
			],
		];

		$obj->elControls[] = [
			'mode'       => 'responsive',
			'type'       => 'dimensions',
			'id'         => 'fmp_readmore_button_border_radius',
			'label'      => esc_html__( 'Border Radius', 'tlp-food-menu' ),
			'size_units' => [ 'px', '%' ],
			'default'    => [
				'unit'     => 'px',
				'isLinked' => true,
			],
			'selectors'  => [
				'{{WRAPPER}} .fmp-footer .fmp-btn-read-more:before, {{WRAPPER}} .fmp-body .fmp-btn-read-more:before, {{WRAPPER}} .fmp-footer .fmp-btn-read-more:after, {{WRAPPER}} .fmp-body .fmp-btn-read-more:after, {{WRAPPER}} .fmp-footer .fmp-btn-read-more, {{WRAPPER}} .fmp-body .fmp-btn-read-more' => 'border-radius: {{TOP}}{{UNIT}} {{RIGHT}}{{UNIT}} {{BOTTOM}}{{UNIT}} {{LEFT}}{{UNIT}}; overflow: hidden;',
			],
		];

		$obj->endSection();

		return new static();
	}


	/**
	 * Add to cart Button Style
	 *
	 * @param object $obj Reference object.
	 * @return static
	 */
	public static function addToCarButton( $obj ) {
		$condition = [
			'fmp_addtocart_switch' => [ 'yes' ],
		];
		$obj->startSection( 'addtocart_button_style', esc_html__( 'Add to Cart Button', 'tlp-food-menu' ), self::$tab, [], $condition );
		$obj->elControls[] = [
			'mode'     => 'group',
			'type'     => 'typography',
			'id'       => 'fmp_addtocart_buttons_typography',
			'selector' => '{{WRAPPER}} .fmp-wrapper .add-to-cart, {{WRAPPER}} .fmp-wrapper .fmp-wc-add-to-cart-btn, {{WRAPPER}} .fmp-add-to-cart .fmp-mini-cart',
		];


		$obj->elHeading( 'fmp_addtocart_buttons_colors_note', esc_html__( 'Colors', 'tlp-food-menu' ), 'before' );

		$obj->startTabGroup( 'addtocart_button_color_tabs' );
		$obj->startTab( 'filter_addtocart_color_tab', esc_html__( 'Normal', 'tlp-food-menu' ) );

		$obj->elControls[] = [
			'type'      => 'color',
			'id'        => 'fmp_addtocart_button_color',
			'label'     => esc_html__( 'Button Color', 'tlp-food-menu' ),
			'selectors' => [
				'{{WRAPPER}} .fmp-add-to-cart .fmp-mini-cart' => 'color: {{VALUE}}',
				'{{WRAPPER}} .fmp-wrapper .fmp-wc-add-to-cart-btn' => 'color: {{VALUE}}',
				'{{WRAPPER}} .fmp-wrapper .add-to-cart' => 'color: {{VALUE}}',
			],
		];

		$obj->elControls[] = [
			'type'      => 'color',
			'id'        => 'fmp_addtocart_button_bg_color',
			'label'     => esc_html__( 'Button Background Color', 'tlp-food-menu' ),
			'selectors' => [
				'{{WRAPPER}} .fmp-add-to-cart .fmp-mini-cart:before' => 'background: {{VALUE}}',
				'{{WRAPPER}} .fmp-wrapper .fmp-wc-add-to-cart-btn:before' => 'background: {{VALUE}}',
				'{{WRAPPER}} .fmp-wrapper .add-to-cart:before' => 'background: {{VALUE}}',
			],
		];

		$obj->endTab();
		$obj->startTab( 'addtocart_button_hover_color_tab', esc_html__( 'Hover', 'tlp-food-menu' ) );

		$obj->elControls[] = [
			'type'      => 'color',
			'id'        => 'fmp_addtocart_button_hover_color',
			'label'     => esc_html__( 'Hover Color', 'tlp-food-menu' ),
			'selectors' => [
				'{{WRAPPER}} .fmp-add-to-cart .fmp-mini-cart:hover' => 'color: {{VALUE}}',
				'{{WRAPPER}} .fmp-wrapper .fmp-wc-add-to-cart-btn:hover' => 'color: {{VALUE}}',
				'{{WRAPPER}} .fmp-wrapper .add-to-cart:hover' => 'color: {{VALUE}}',
			],
		];

		$obj->elControls[] = [
			'type'      => 'color',
			'id'        => 'fmp_addtocart_button_hover_bg_color',
			'label'     => esc_html__( 'Hover Background Color', 'tlp-food-menu' ),
			'selectors' => [
				'{{WRAPPER}} .fmp-add-to-cart .fmp-mini-cart:after' => 'background: {{VALUE}}',
				'{{WRAPPER}} .fmp-wrapper .fmp-wc-add-to-cart-btn:after' => 'background: {{VALUE}}',
				'{{WRAPPER}} .fmp-wrapper .add-to-cart:after' => 'background: {{VALUE}}',
			],
		];

		$obj->endTab();

		$obj->endTabGroup();

		$obj->elHeading( 'fmp_addtocart_buttons_border_note', esc_html__( 'Border', 'tlp-food-menu' ), 'before' );

		$obj->elControls[] = [
			'mode'     => 'group',
			'type'     => 'border',
			'id'       => 'fmp_addtocart_button_border',
			'selector' => '{{WRAPPER}} .fmp-wrapper .add-to-cart, {{WRAPPER}} .fmp-wrapper .fmp-wc-add-to-cart-btn, {{WRAPPER}} .fmp-add-to-cart .fmp-mini-cart',
		];

		$obj->elControls[] = [
			'type'      => 'color',
			'id'        => 'fmp_addtocart_button_hover_border_color',
			'label'     => esc_html__( 'Hover Border Color', 'tlp-food-menu' ),
			'condition' => [ 'fmp_addtocart_button_border_border!' => [ '' ] ],
			'selectors' => [
				'{{WRAPPER}} .fmp-add-to-cart .fmp-mini-cart:hover' => 'border-color: {{VALUE}}',
				'{{WRAPPER}} .fmp-wrapper .add-to-cart:hover' => 'border-color: {{VALUE}}',
			],
		];

		$obj->elControls[] = [
			'mode'       => 'responsive',
			'type'       => 'dimensions',
			'id'         => 'fmp_addtocart_button_border_radius',
			'label'      => esc_html__( 'Border Radius', 'tlp-food-menu' ),
			'size_units' => [ 'px', '%' ],
			'default'    => [
				'unit'     => 'px',
				'isLinked' => true,
			],
			'selectors'  => [
				'{{WRAPPER}} .fmp-add-to-cart .fmp-mini-cart:before, {{WRAPPER}} .fmp-add-to-cart .fmp-mini-cart:after, {{WRAPPER}} .fmp-add-to-cart .fmp-mini-cart' => 'border-radius: {{TOP}}{{UNIT}} {{RIGHT}}{{UNIT}} {{BOTTOM}}{{UNIT}} {{LEFT}}{{UNIT}}; overflow: hidden;',
				'{{WRAPPER}} .fmp-wrapper .fmp-wc-add-to-cart-btn:before, {{WRAPPER}} .fmp-wrapper .fmp-wc-add-to-cart-btn:after, {{WRAPPER}} .fmp-wrapper .fmp-wc-add-to-cart-btn' => 'border-radius: {{TOP}}{{UNIT}} {{RIGHT}}{{UNIT}} {{BOTTOM}}{{UNIT}} {{LEFT}}{{UNIT}}; overflow: hidden;',
				'{{WRAPPER}} .fmp-wrapper .add-to-cart:before, {{WRAPPER}} .fmp-wrapper .add-to-cart:after, {{WRAPPER}} .fmp-wrapper .add-to-cart' => 'border-radius: {{TOP}}{{UNIT}} {{RIGHT}}{{UNIT}} {{BOTTOM}}{{UNIT}} {{LEFT}}{{UNIT}}; overflow: hidden;',
			],
		];

		$obj->endSection();

		return new static();
	}

}