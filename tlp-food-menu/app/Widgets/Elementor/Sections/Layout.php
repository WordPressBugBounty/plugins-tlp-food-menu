<?php
/**
 * Elementor Layout Class.
 *
 * This class contains all the controls for Layout tab.
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
 * Elementor Layout Class.
 */
class Layout {
	/**
	 * Tab name.
	 *
	 * @access private
	 * @static
	 *
	 * @var array
	 */
	private static $tab = \Elementor\Controls_Manager::TAB_LAYOUT;


	/**
	 * Adding filter hook.
	 *
	 * @param string $filterName Filter hook name.
	 * @param string $var Variable to apply.
	 * @param object $obj Reference object.
	 *
	 * @return mixed
	 */

	public static function filter( $filterName, $obj ) {
		return array_merge( apply_filters( $filterName, $obj ) );
	}

	public static function shortcodeList( $obj ) {
		$obj->startSection( 'content_section', esc_html__( 'Food Menu', 'tlp-food-menu' ), self::$tab );
		$obj->elControls[] = [
			'type'        => 'select2',
			'id'          => 'short_code_id',
			'label'       => esc_html__( 'Select Shortcode', 'tlp-food-menu' ),
			'label_block' => true,
			'options'     => Fns::get_shortCode_list(),
		];
		$obj->endSection();

		return new static();
	}


	/**
	 * Layout section
	 *
	 * @param object $obj Reference object.
	 *
	 * @return static
	 */
	public static function grid_layout( $obj, $prefix = 'gridbycat' ) {
		$status = ! TLPFoodMenu()->has_pro();
		$layout_name = sprintf( esc_html__( '%s Layouts', 'tlp-food-menu' ), ucfirst( $prefix ) );
		$obj->startSection( 'layout_section', $layout_name, self::$tab );

		if ( 'list' == $prefix ) {
			$layout = apply_filters( 'fm_el_layout', [
				'layout1' => [
					'title' => esc_html__( 'Layout 1', 'tlp-food-menu' ),
					'img'   => TLPFoodMenu()->assets_url() . 'images/layouts/list-layout-1.png',
				],
				'layout2' => [
					'title' => esc_html__( 'Layout 2', 'tlp-food-menu' ),
					'img'   => TLPFoodMenu()->assets_url() . 'images/layouts/list-layout-2.png',
				],
				'layout3' => [
					'title' => esc_html__( 'Layout 3', 'tlp-food-menu' ),
					'img'   => TLPFoodMenu()->assets_url() . 'images/layouts/list-layout-3.png',
				],
				'layout4' => [
					'title' => esc_html__( 'Layout 4', 'tlp-food-menu' ),
					'img'   => TLPFoodMenu()->assets_url() . 'images/layouts/list-layout-4.png',
				],
				'layout5' => [
					'title' => esc_html__( 'Layout 5', 'tlp-food-menu' ),
					'img'   => TLPFoodMenu()->assets_url() . 'images/layouts/list-layout-5.png',
					'is_pro' => $status,
				],
				'layout6' => [
					'title' => esc_html__( 'Layout 6', 'tlp-food-menu' ),
					'img'   => TLPFoodMenu()->assets_url() . 'images/layouts/list-layout-6.png',
					'is_pro' => $status,
				],
				'layout7' => [
					'title' => esc_html__( 'Layout 7', 'tlp-food-menu' ),
					'img'   => TLPFoodMenu()->assets_url() . 'images/layouts/list-layout-7.png',
					'is_pro' => $status,
				],
			]);
		} elseif ( 'grid' == $prefix ){
			$layout = [
				'layout1' => [
					'title' => esc_html__( 'Layout 1', 'tlp-food-menu' ),
					'img'   => TLPFoodMenu()->assets_url() . 'images/layouts/grid-layout-1.png',
				],
				'layout2' => [
					'title' => esc_html__( 'Layout 2', 'tlp-food-menu' ),
					'img'   => TLPFoodMenu()->assets_url() . 'images/layouts/grid-layout-2.png',
				],
				'layout3' => [
					'title' => esc_html__( 'Layout 3', 'tlp-food-menu' ),
					'img'   => TLPFoodMenu()->assets_url() . 'images/layouts/grid-layout-3.png',
				],
				'layout4' => [
					'title' => esc_html__( 'Layout 4', 'tlp-food-menu' ),
					'img'   => TLPFoodMenu()->assets_url() . 'images/layouts/grid-layout-4.png',
				],
				'layout5' => [
					'title' => esc_html__( 'Layout 5', 'tlp-food-menu' ),
					'img'   => TLPFoodMenu()->assets_url() . 'images/layouts/grid-layout-5.png',
				],
			];
		} elseif ( 'slider' == $prefix ){
			$layout = [
				'layout1' => [
					'title' => esc_html__( 'Layout 1', 'tlp-food-menu' ),
					'img'   => TLPFoodMenu()->assets_url() . 'images/layouts/carousel-1.png',
				],
				'layout2' => [
					'title' => esc_html__( 'Layout 2', 'tlp-food-menu' ),
					'img'   => TLPFoodMenu()->assets_url() . 'images/layouts/carousel-2.png',
				],
				'layout3' => [
					'title' => esc_html__( 'Layout 3', 'tlp-food-menu' ),
					'img'   => TLPFoodMenu()->assets_url() . 'images/layouts/carousel-3.png',
				],
				'layout4' => [
					'title' => esc_html__( 'Layout 4', 'tlp-food-menu' ),
					'img'   => TLPFoodMenu()->assets_url() . 'images/layouts/carousel-4.png',
				],
			];
		} elseif ( 'isotope' == $prefix ){
			$layout = [
				'layout1' => [
					'title' => esc_html__( 'Layout 1', 'tlp-food-menu' ),
					'img'   => TLPFoodMenu()->assets_url() . 'images/layouts/isotope-1.png',
				],
				'layout2' => [
					'title' => esc_html__( 'Layout 2', 'tlp-food-menu' ),
					'img'   => TLPFoodMenu()->assets_url() . 'images/layouts/isotope-2.png',
				],
				'layout3' => [
					'title' => esc_html__( 'Layout 3', 'tlp-food-menu' ),
					'img'   => TLPFoodMenu()->assets_url() . 'images/layouts/isotope-3.png',
				],
				'layout4' => [
					'title' => esc_html__( 'Layout 4', 'tlp-food-menu' ),
					'img'   => TLPFoodMenu()->assets_url() . 'images/layouts/isotope-4.png',
				],
			];
		} else {
			$layout = [
				'layout1' => [
					'title' => esc_html__( 'Layout 1', 'tlp-food-menu' ),
					'img'   => TLPFoodMenu()->assets_url() . 'images/layouts/grid-by-category-1.png',
				],
				'layout2' => [
					'title' => esc_html__( 'Layout 2', 'tlp-food-menu' ),
					'img'   => TLPFoodMenu()->assets_url() . 'images/layouts/grid-by-category-4.png',
				],
				'layout3' => [
					'title' => esc_html__( 'Layout 3', 'tlp-food-menu' ),
					'img'   => TLPFoodMenu()->assets_url() . 'images/layouts/grid-by-category-3.png',
				],
				'layout4' => [
					'title' => esc_html__( 'Layout 4', 'tlp-food-menu' ),
					'img'   => TLPFoodMenu()->assets_url() . 'images/layouts/grid-by-category-5.png',
				],
				'layout5' => [
					'title' => esc_html__( 'Layout 5', 'tlp-food-menu' ),
					'img'   => TLPFoodMenu()->assets_url() . 'images/layouts/grid-by-category-2.png',
					'is_pro' => $status,
				],
				'layout6' => [
					'title' => esc_html__( 'Layout 6', 'tlp-food-menu' ),
					'img'   => TLPFoodMenu()->assets_url() . 'images/layouts/grid-by-category-6.png',
					'is_pro' => $status,
				],
				'layout7' => [
					'title' => esc_html__( 'Layout 7', 'tlp-food-menu' ),
					'img'   => TLPFoodMenu()->assets_url() . 'images/layouts/grid-by-category-7.png',
					'is_pro' => $status,
				],
			];
		}

		$obj->elControls[] = [
			'type'    => 'rtfm-image-selector',
			'id'      => 'fmp_layout',
			'options' => $layout,
			'default' => 'layout1',
		];

		$obj->endSection();

		return new static();
	}

	/**
	 * Layout section
	 *
	 * @param object $obj Reference object.
	 *
	 * @return static
	 */
	public static function sliderLayout( $obj ) {
		$obj->startSection( 'layout_section', esc_html__( 'Layouts', 'tlp-food-menu' ), self::$tab );

		$obj->elControls[] = [
			'type'    => 'rtfm-image-selector',
			'id'      => $obj->elPrefix . 'layout',
			'options' => Options::scLayouts(),
			'default' => 'carousel-el-1',
		];

		$obj->endSection();

		return new static();
	}


	/**
	 * Layout section
	 *
	 * @param object $obj Reference object.
	 *
	 * @return static
	 */
	public static function columns( $obj ) {

		$obj->startSection( 'columns_section', esc_html__( 'Columns', 'tlp-food-menu' ), self::$tab );

		$obj->elControls[] = [
			'type'           => 'select',
			'id'             => 'fmp_desktop_column',
			'mode'           => 'responsive',
			'label'          => esc_html__( 'Number of Columns', 'tlp-food-menu' ),
			'description'    => esc_html__( 'Please select the number of columns to show per row.', 'tlp-food-menu' ),
		'options'        => [
				'12' => esc_html__( '1 Columns', 'tlp-food-menu' ),
				'6'  => esc_html__( '2 Columns', 'tlp-food-menu' ),
				'4'  => esc_html__( '3 Columns', 'tlp-food-menu' ),
				'3'  => esc_html__( '4 Columns', 'tlp-food-menu' ),
				'2'  => esc_html__( '6 Columns', 'tlp-food-menu' ),
			],
			'default'        => '6',
			'separator'      => 'after',
		];

		$obj->elControls = self::filter( 'tlp_el_end_of_columns_section', $obj );

		$obj->endSection();

		return new static();
	}

	/**
	 * Layout section
	 *
	 * @param object $obj Reference object.
	 *
	 * @return static
	 */
	public static function columnsSlider( $obj ) {
		$obj->startSection( 'columns_section', esc_html__( 'Columns', 'tlp-food-menu' ), self::$tab );
		$obj->elControls[] = [
			'type'           => 'select',
			'id'             => 'fmp_desktop_column',
			'mode'           => 'responsive',
			'label'          => esc_html__( 'Number of Columns', 'tlp-food-menu' ),
			'description'    => esc_html__( 'Please select the number of columns to show per row.', 'tlp-food-menu' ),
			'options'        => [
				'1' => esc_html__( '1 Columns', 'tlp-food-menu' ),
				'2'  => esc_html__( '2 Columns', 'tlp-food-menu' ),
				'3'  => esc_html__( '3 Columns', 'tlp-food-menu' ),
				'4'  => esc_html__( '4 Columns', 'tlp-food-menu' ),
				'5'  => esc_html__( '5 Columns', 'tlp-food-menu' ),
				'6'  => esc_html__( '6 Columns', 'tlp-food-menu' ),
			],
			'default'        => '3',
			'tablet_default' => '2',
			'mobile_default' => '1',
			'required'       => true,
			'label_block'    => true,
			'separator'      => 'after',
		];
		$obj->elControls = self::filter( 'tlp_el_end_of_columns_section', $obj );
		$obj->endSection();
		return new static();
	}


	/**
	 * Filtering section
	 *
	 * @param object $obj Reference object.
	 *
	 * @return static
	 */
	public static function query( $obj ) {

		$obj->startSection( 'query_section', esc_html__( 'Query', 'tlp-food-menu' ), self::$tab );

		$obj->elControls[] = [
			'type'        => 'select',
			'id'          => 'fmp_source',
			'label'       => esc_html__( 'Include Food Menu', 'tlp-food-menu' ),
			'options'     => [
				'food-menu' => __( 'Food Menu', 'tlp-food-menu' ),
				'product'   => __( 'Product ( WooCommerce )', 'tlp-food-menu' ),
			],
			'default'        => 'food-menu',
			'description' => esc_html__( 'Please select the food menu to show. Leave it blank to include all posts.', 'tlp-food-menu' ),
			'multiple'    => true,
			'label_block' => true,
		];

		$obj->startTabGroup( 'query_tab' );
		$obj->startTab( 'query_include_tab', esc_html__( 'Include', 'tlp-food-menu' ) );

		$obj->elControls[] = [
			'type'        => 'select2',
			'id'          => 'fmp_post__in',
			'label'       => esc_html__( 'Include Food Menu', 'tlp-food-menu' ),
			'options'     => Fns::getMenuList(),
			'description' => esc_html__( 'Please select the food menu to show. Leave it blank to include all posts.', 'tlp-food-menu' ),
			'multiple'    => true,
			'label_block' => true,
			'condition'   => [ 'fmp_source' => [ 'food-menu' ] ],
		];

		$obj->elControls[] = [
			'type'        => 'select2',
			'id'          => 'fmp_wc_post__in',
			'label'       => esc_html__( 'Include Products', 'tlp-food-menu' ),
			'options'     => Fns::getMenuListEl(),
			'description' => esc_html__( 'Please select the food menu to show. Leave it blank to include all posts.', 'tlp-food-menu' ),
			'multiple'    => true,
			'label_block' => true,
			'condition'   => [ 'fmp_source' => [ 'product' ] ],
		];

		$obj->endTab();
		$obj->startTab( 'query_exclude_tab', esc_html__( 'Exclude', 'tlp-food-menu' ) );

		$obj->elControls[] = [
			'type'        => 'select2',
			'id'          => 'fmp_post__not_in',
			'label'       => esc_html__( 'Exclude Food Menu', 'tlp-food-menu' ),
			'options'     => Fns::getMenuList(),
			'description' => esc_html__( 'Please select the food menu to exclude. Leave it blank to exclude none.', 'tlp-food-menu' ),
			'multiple'    => true,
			'label_block' => true,
			'condition'   => [ 'fmp_source' => [ 'food-menu' ] ],
		];

		$obj->elControls[] = [
			'type'        => 'select2',
			'id'          => 'fmp_wc_post__not_in',
			'label'       => esc_html__( 'Exclude Food Menu', 'tlp-food-menu' ),
			'options'     => Fns::getMenuListEl(),
			'description' => esc_html__( 'Please select the food menu to exclude. Leave it blank to exclude none.', 'tlp-food-menu' ),
			'multiple'    => true,
			'label_block' => true,
			'condition'   => [ 'fmp_source' => [ 'product' ] ],
		];

		$obj->endTab();
		$obj->endTabGroup();

		$obj->elControls[] = [
			'type'        => 'number',
			'id'          => 'fmp_limit',
			'label'       => esc_html__( 'Post Limit', 'tlp-food-menu' ),
			'default'     => 8,
			'description' => esc_html__( 'The number of posts to show. Set empty to show all posts.', 'tlp-food-menu' ),
		];

		$obj->elHeading( $obj->elPrefix . 'category_note', esc_html__( 'Categories', 'tlp-food-menu' ), 'before' );

		$obj->elControls[] = [
			'type'        => 'select2',
			'id'          => 'fmp_categories',
			'label'       => esc_html__( 'Include Categories', 'tlp-food-menu' ),
			'options'     => Fns::getElAllFmpCategoryList(),
			'description' => esc_html__( 'Please select the food menu category to show. Leave it blank to include all.', 'tlp-food-menu' ),
			'multiple'    => true,
			'label_block' => true,
			'condition'   => [ 'fmp_source' => [ 'food-menu' ] ],
		];

		$obj->elControls[] = [
			'type'        => 'select2',
			'id'          => 'fmp_wc_categories',
			'label'       => esc_html__( 'Include Categories', 'tlp-food-menu' ),
			'options'     => Fns::getElProductAllFmpCategoryList( ),
			'description' => esc_html__( 'Please select the food menu category to show. Leave it blank to include all.', 'tlp-food-menu' ),
			'multiple'    => true,
			'label_block' => true,
			'condition'   => [ 'fmp_source' => [ 'product' ] ],
		];


		//$obj->elControls = self::filter( 'query_tax_filter', $obj );

		$obj->elHeading( $obj->elPrefix . 'sorting_note', esc_html__( 'Sorting', 'tlp-food-menu' ), 'before' );

		$obj->elControls[] = [
			'type'        => 'select2',
			'id'          => 'fmp_order_by',
			'label'       => esc_html__( 'Order By', 'tlp-food-menu' ),
			'description' => esc_html__( 'Please choose to reorder food menu.', 'tlp-food-menu' ),
			'options'     => Options::scOrderBy(),
			'default'     => 'date',
		];

		$obj->elControls[] = [
			'type'        => 'select2',
			'id'          => 'fmp_order',
			'label'       => esc_html__( 'Order', 'tlp-food-menu' ),
			'description' => esc_html__( 'Please choose to reorder food menu.', 'tlp-food-menu' ),
			'options'     => Options::scOrder(),
			'default'     => 'DESC',
		];

		$obj->endSection();

		return new static();
	}

	public static function getElAllFmpCategoryListIsotope( $settings = [] ) {

		$taxonomy = TLPFoodMenu()->taxonomies['category'];
		$terms = [];

		$terms['all'] = ! empty( $settings['fmp_change_btn_text'] ) ? $settings['fmp_change_btn_text'] : 'All Button';


		$termList = get_terms([
			'taxonomy'   => $taxonomy,
			'hide_empty' => 0,
		]);
		if ( is_array( $termList ) && ! empty( $termList ) && empty( $termList['errors'] ) ) {
			foreach ( $termList as $term ) {

				$terms[ $term->term_id ] = "ALl Button";
				$terms[ $term->term_id ] = $term->name;

			}
		}
		return $terms;
	}


	/**
	 * Filtering section
	 *
	 * @param object $obj Reference object.
	 *
	 * @return static
	 */
	public static function queryIsotope( $obj ) {

		$obj->startSection( 'query_section', esc_html__( 'Query', 'tlp-food-menu' ), self::$tab );

		$obj->elControls[] = [
			'type'        => 'select',
			'id'          => 'fmp_source',
			'label'       => esc_html__( 'Include Food Menu', 'tlp-food-menu' ),
			'options'     => [
				'food-menu' => __( 'Food Menu', 'tlp-food-menu' ),
				'product'   => __( 'Product ( WooCommerce )', 'tlp-food-menu' ),
			],
			'default'        => 'food-menu',
			'description' => esc_html__( 'Please select the food menu to show. Leave it blank to include all posts.', 'tlp-food-menu' ),
			'multiple'    => true,
			'label_block' => true,
		];

		$obj->elControls[] = [
			'type'        => 'number',
			'id'          => 'fmp_limit',
			'label'       => esc_html__( 'Post Limit', 'tlp-food-menu' ),
			'default'     => 8,
			'description' => esc_html__( 'The number of posts to show. Set empty to show all posts.', 'tlp-food-menu' ),
		];

		$obj->elControls[] = [
			'type'        => 'select2',
			'id'          => 'fmp_categories',
			'label'       => esc_html__( 'Include Categories', 'tlp-food-menu' ),
			'options'     => Fns::getElAllFmpCategoryList(),
			'description' => esc_html__( 'Please select the food menu category to show. Leave it blank to include all.', 'tlp-food-menu' ),
			'multiple'    => true,
			'label_block' => true,
			'condition'   => [ 'fmp_source' => [ 'food-menu' ] ],
		];

		$obj->elControls[] = [
			'type'        => 'select2',
			'id'          => 'fmp_wc_categories_include',
			'label'       => esc_html__( 'Include Categories', 'tlp-food-menu' ),
			'options'     => Fns::getElProductAllFmpCategoryList( ),
			'description' => esc_html__( 'Please select the food menu category to show. Leave it blank to include all.', 'tlp-food-menu' ),
			'multiple'    => true,
			'label_block' => true,
			'condition'   => [ 'fmp_source' => [ 'product' ] ],
		];

		$obj->elHeading( $obj->elPrefix . 'category_note', esc_html__( 'Categories Select Menu', 'tlp-food-menu' ), 'before' );


		$obj->elControls[] = [
			'type'        => 'select',
			'id'          => 'fmp_categories_isotope',
			'label'       => esc_html__( 'Isotope Filter (Default Selected Item)', 'tlp-food-menu' ),
			'options'     => Fns::getElAllFmpCategoryListIsotope(),
			'description' => esc_html__( 'Please select the default selected filter term (Selected item).', 'tlp-food-menu' ),
			'default'     => 'all',
			'condition'   => [ 'fmp_source' => [ 'food-menu' ] ],
		];


		$obj->elControls[] = [
			'type'        => 'select',
			'id'          => 'fmp_wc_categories_isotope',
			'label'       => esc_html__( 'Isotope Filter (Default Selected Item)', 'tlp-food-menu' ),
			'options'     => Fns::getElProductAllFmpCategoryListIsotope(),
			'description' => esc_html__( 'Please select the default selected filter term (Selected item).', 'tlp-food-menu' ),
			'default'     => 'all',
			'condition'   => [ 'fmp_source' => [ 'product' ] ],
		];

		$obj->elHeading( $obj->elPrefix . 'sorting_note', esc_html__( 'Sorting', 'tlp-food-menu' ), 'before' );

		$obj->elControls[] = [
			'type'        => 'select2',
			'id'          => 'fmp_order_by',
			'label'       => esc_html__( 'Order By', 'tlp-food-menu' ),
			'description' => esc_html__( 'Please choose to reorder food menu.', 'tlp-food-menu' ),
			'options'     => Options::scOrderBy(),
			'default'     => 'date',
		];

		$obj->elControls[] = [
			'type'        => 'select2',
			'id'          => 'fmp_order',
			'label'       => esc_html__( 'Order', 'tlp-food-menu' ),
			'description' => esc_html__( 'Please choose to reorder food menu.', 'tlp-food-menu' ),
			'options'     => Options::scOrder(),
			'default'     => 'DESC',
		];

		$obj->endSection();

		return new static();
	}


	/**
	 * Pagination section
	 *
	 * @param object $obj Reference object.
	 *
	 * @return static
	 */
	public static function pagination( $obj ) {
		$condition = [
			$obj->elPrefix . 'show_pagination' => [ 'yes' ],
			$obj->elPrefix . 'pagination_type' => [ 'pagination' ],
		];

		$obj->startSection(
			'pagination_section',
			esc_html__( 'Pagination', 'tlp-food-menu' ),
			self::$tab
		);

		$obj->elControls[] = [
			'type'        => 'switch',
			'id'          => 'fmp_pagination',
			'label'       => esc_html__( 'Enable Pagination?', 'tlp-food-menu' ),
			'description' => esc_html__( 'Switch on to enable pagination.', 'tlp-food-menu' ),
			'label_on'    => esc_html__( 'On', 'tlp-food-menu' ),
			'label_off'   => esc_html__( 'Off', 'tlp-food-menu' ),
		];

		$obj->elControls[] = [
			'type'        => 'select2',
			'id'          => 'fmp_pagination_type',
			'label'       => esc_html__( 'Pagination type', 'tlp-food-menu' ),
			'description' => esc_html__( 'Please choose to reorder food menu.', 'tlp-food-menu' ),
			'options'     => [
				'number-pagination' => esc_html__( 'Numbered Pagination', 'tlp-food-menu' ),
				'ajax-number-pagination' => esc_html__( 'Ajax Numbered Pagination', 'tlp-food-menu' ),
				'ajax-load-more-button' => esc_html__( 'Ajax Load More Button', 'tlp-food-menu' ),
				'ajax-load-more-scroll' => esc_html__( 'Ajax Load More on Scroll', 'tlp-food-menu' ),
			],
			'default'     => 'number-pagination',
			'condition'   => [ 'fmp_pagination' => [ 'yes' ] ],
		];

		$obj->elControls[] = [
			'type'        => 'number',
			'id'          => 'fmp_posts_per_page',
			'label'       => esc_html__( 'Number of Posts Per Page', 'tlp-food-menu' ),
			'default'     => 8,
			'description' => esc_html__( 'Please enter the number of food menu per page to show.', 'tlp-food-menu' ),
			'condition'   => [ 'fmp_pagination' => [ 'yes' ] ],
		];

		$obj->endSection();

		return new static();
	}

	/**
	 * Pagination section
	 *
	 * @param object $obj Reference object.
	 *
	 * @return static
	 */
	public static function isotopPagination( $obj ) {
		$obj->startSection( 'pagination_section', esc_html__( 'Pagination', 'tlp-food-menu' ), self::$tab );

		$obj->elControls = self::filter( $obj->elPrefix . 'after_show_isotope_pagination', $obj );

		$obj->endSection();

		return new static();
	}

	/**
	 * Image section
	 *
	 * @param object $obj Reference object.
	 *
	 * @return static
	 */
	public static function image( $obj, $prefix = 'list' ) {
		$obj->startSection( 'image_section', esc_html__( 'Image', 'tlp-food-menu' ), self::$tab );
		$obj->elControls[] = [
			'type'        => 'switch',
			'id'          => 'fmp_feature_switch',
			'label'       => esc_html__( 'Display Featured Image?', 'tlp-food-menu' ),
			'description' => esc_html__( 'Switch on to display featured image.', 'tlp-food-menu' ),
			'label_on'    => esc_html__( 'On', 'tlp-food-menu' ),
			'label_off'   => esc_html__( 'Off', 'tlp-food-menu' ),
			'default'     => 'yes',
			'separator'      => 'after',
		];

		$obj->elControls[] = [
			'type'        => 'switch',
			'id'          => 'fmp_hover_icon',
			'label'       => esc_html__( 'Enable Hover Icon?', 'tlp-food-menu' ),
			'description' => esc_html__( 'Switch on to enable hover icon.', 'tlp-food-menu' ),
			'label_on'    => esc_html__( 'On', 'tlp-food-menu' ),
			'label_off'   => esc_html__( 'Off', 'tlp-food-menu' ),
			'default'     => 'yes',
			'separator'      => 'after',
		];

		if ( 'list' == $prefix ) {
			$obj->elControls = self::filter( 'tlp_image_align', $obj );
		}



		$obj->elControls[] = [
			'type'            => 'select2',
			'id'              => 'fmp_image_size',
			'label'           => esc_html__( 'Select Image Size', 'tlp-food-menu' ),
			'options'         => Fns::get_image_sizes(),
			'default'         => 'large',
			'label_block'     => true,
			'content_classes' => 'elementor-descriptor',
			'condition'       => [ 'fmp_feature_switch' => [ 'yes' ] ],
			'separator'      => 'before',
		];

		$obj->elControls = self::filter( 'tlp_el_image_animation', $obj );

		$obj->endSection();

		return new static();
	}
}
