<?php
/**
 * Filter Hook Class.
 *
 * @package RT_FoodMenu
 */

namespace RT\FoodMenu\Controllers\Hooks;

use RT\FoodMenu\Helpers\RenderHelpers;

// Do not allow directly accessing this file.
if ( ! defined( 'ABSPATH' ) ) {
	exit( 'This script cannot be accessed directly.' );
}

/**
 * Filter Hook  Class.
 */
class FilterHooks {
	use \RT\FoodMenu\Traits\SingletonTrait;

	/**
	 * Class.
	 *
	 * @var string
	 */
	public $classes = '';

	protected function init() {

		$this->classes = ! TLPFoodMenu()->has_pro() ? 'rt-pro-field' : '';

		add_filter( 'fmp_image_size', [ $this, 'get_image_sizes' ] );
		add_filter( 'wp_kses_allowed_html', [ $this, 'custom_post_tags' ], 10, 2 );
		add_filter( 'rtfm_add_to_cart_btn', [ $this, 'cartBtn' ], 10, 7 );
		add_filter( 'body_class', [ $this,'add_custom_body_class' ] );
		add_filter( 'rtfm_add_stock_btn', [ $this, 'stockBtn' ], 10, 2 );

		// pro featured
		add_filter( 'tlp_el_end_of_columns_section', [ $this, 'layoutControls' ]);
		add_filter( 'tlp_el_pro_switcher', [ $this, 'el_pro_SetttingsControls' ]);
		add_filter( 'tlp_el_pro_popup', [ $this, 'el_pro_popup' ]);
		add_filter( 'tlp_image_align', [ $this, 'image_align' ], 10, 2 );
		add_filter( 'tlp_el_image_animation', [ $this, 'image_animation' ], 10, 2 );

	}

	public function get_image_sizes( $imgSize ) {
		$imgSize['full'] = esc_html__( 'Full Size', 'tlp-food-menu' );

		return $imgSize;
	}

	/**
	 * Add script to allowed wp_kses_post tags
	 *
	 * @param array  $tags Allowed tags, attributes, and/or entities.
	 * @param string $context Context to judge allowed tags by. Allowed values are 'post'.
	 *
	 * @return array
	 */
	public function custom_post_tags( $tags, $context ) {

		if ( 'post' === $context ) {
			$tags['style'] = [
				'src' => true,
			];

			$tags['input'] = [
				'type'        => true,
				'class'       => true,
				'name'        => true,
				'step'        => true,
				'min'         => true,
				'title'       => true,
				'size'        => true,
				'pattern'     => true,
				'inputmode'   => true,
				'value'       => true,
				'id'          => true,
				'placeholder' => true,
			];

			$tags['iframe'] = [
				'src'             => true,
				'height'          => true,
				'width'           => true,
				'frameborder'     => true,
				'allowfullscreen' => true,
			];
		}

		return $tags;
	}

	/**
	 * Stock btn
	 *
	 * @param number $pid .
	 * @return string|null
	 */
	public function stockBtn( $pid = null ) {
		if ( $pid ) {
			$pid = absint( $pid );
		} else {
			$pid = get_the_ID();
		}

		$stock     = get_post_meta( $pid, '_stock_status', true );
		$stockHtml = null;

		if ( 'outofstock' === $stock ) {
			$stockHtml = '<div class="fmp-outofstock">' . esc_html__( 'Out of stock', 'tlp-food-menu' ) . '</div>';
		}

		return $stockHtml;
	}

	public function cartBtn( $content, $link, $id, $type, $text, $items, $popup = false ) {
		if ( TLPFoodMenu()->has_pro() || 'variable' === $type ) {
			return $content;
		}
		$text = esc_html__( 'Add to Cart', 'tlp-food-menu' );
		$add_to_cart_button = sprintf(
			'<a href="?add-to-cart=%2$d" data-quantity="1" class="button product_type_simple add_to_cart_button ajax_add_to_cart fmp-wc-add-to-cart-btn" data-product_id="%2$d" data-type="%3$s">%4$s<span></span></a>',
			esc_url( $link ),
			absint( $id ),
			esc_html( $type ),
			esc_html( $text )
		);

		$content = sprintf(
			'<div class="fmp-wc-add-to-cart-wrap">%s</div>',
			$add_to_cart_button
		);

		return $content;
	}

	/**
	 * Add custom body class
	 *
	 * @param array $classes .
	 * @return array
	 */
	public function add_custom_body_class( $classes ) {
		$classes[] = 'fmp-food-menu';
		return $classes;
	}

	/**
	 * Layout Controls
	 *
	 * @param object $obj Variable.
	 *
	 * @return array
	 */

	public function layoutControls( $obj ) {
		$obj->elControls[] = [
			'type'        => 'select2',
			'id'          => 'tlp_el_grid_style_promo',
			'label'       => __( 'Masonry Style', 'tlp-food-menu' ),
			'options'     => [
				'even'    => __( 'Even', 'tlp-food-menu' ),
				'masonry' => __( 'Masonry', 'tlp-food-menu' ),
			],
			'default'     => 'even',
			'description' => __( 'Please select the grid style.', 'tlp-food-menu' ),
			'classes'     => $this->classes,
		];
		return $obj->elControls;
	}
	/**
	 * Layout Controls
	 *
	 * @param object $obj Variable.
	 *
	 * @return array
	 */

	public function el_pro_SetttingsControls( $obj ) {

		$obj->elControls[] = [
			'type'        => 'switch',
			'id'          => 'fmp_readmore_switch',
			'label'       => esc_html__( 'Read More ?', 'tlp-food-menu' ),
			'description' => esc_html__( 'Switch on to show read more button.', 'tlp-food-menu' ),
			'label_on'    => esc_html__( 'On', 'tlp-food-menu' ),
			'label_off'   => esc_html__( 'Off', 'tlp-food-menu' ),
			'default'     => 'no',
			'classes'     => $this->classes,
			'separator'      => 'before',
		];

		$obj->elControls[] = [
			'type'        => 'text',
			'id'          => 'fmp_readmore_text',
			'label'       => esc_html__( 'Change Readmore buttontext', 'tlp-food-menu' ),
			'default'     => 'Read More',
			'description' => esc_html__( 'Please change "Read MOore" text', 'tlp-food-menu' ),
			'condition'   => [ 'fmp_readmore_switch' => 'yes' ],
		];

		$obj->elControls[] = [
			'type'        => 'switch',
			'id'          => 'fmp_stock_status_switch',
			'label'       => esc_html__( 'Stock Status ?', 'tlp-food-menu' ),
			'description' => esc_html__( 'Switch on to show stock status.', 'tlp-food-menu' ),
			'label_on'    => esc_html__( 'On', 'tlp-food-menu' ),
			'label_off'   => esc_html__( 'Off', 'tlp-food-menu' ),
			'default'     => 'no',
			'classes'     => $this->classes,
			'separator'      => 'before',
			'condition'   => [ 'fmp_source' => 'product' ],
		];

		$obj->elControls[] = [
			'type'        => 'switch',
			'id'          => 'fmp_addtocart_switch',
			'label'       => esc_html__( 'Add To cart ?', 'tlp-food-menu' ),
			'description' => esc_html__( 'Switch on to show Add to cart ( wooCommerce ).', 'tlp-food-menu' ),
			'label_on'    => esc_html__( 'On', 'tlp-food-menu' ),
			'label_off'   => esc_html__( 'Off', 'tlp-food-menu' ),
			'default'     => 'yes',
			'separator'      => 'before',
			'condition'   => [ 'fmp_source' => 'product' ],
		];

		$obj->elControls[] = [
			'type'        => 'switch',
			'id'          => 'fmp_quantity_switch',
			'label'       => esc_html__( 'Quantity ?', 'tlp-food-menu' ),
			'description' => esc_html__( 'Switch on to show quantity ( wooCommerce ).', 'tlp-food-menu' ),
			'label_on'    => esc_html__( 'On', 'tlp-food-menu' ),
			'label_off'   => esc_html__( 'Off', 'tlp-food-menu' ),
			'default'     => 'no',
			'classes'     => $this->classes,
			'separator'      => 'before',
			'condition'   => [ 'fmp_source' => 'product' ],
		];

		return $obj->elControls;
	}


	/**
	 * Layout Controls
	 *
	 * @param object $obj Variable.
	 *
	 * @return array
	 */

	public function el_pro_popup( $obj ) {

		$obj->elControls[] = [
			'type'        => 'switch',
			'id'          => 'fmp_detail_page_popup',
			'label'       => esc_html__( 'Enable Details Page Popup ?', 'tlp-food-menu' ),
			'description' => esc_html__( 'Switch on to enable popup to detail page.', 'tlp-food-menu' ),
			'label_on'    => esc_html__( 'On', 'tlp-food-menu' ),
			'label_off'   => esc_html__( 'Off', 'tlp-food-menu' ),
			'default'     => 'no',
			'classes'     => $this->classes,
			'condition'       => [
				'fmp_detail_page_link' => [ 'yes' ],
			],
		];
		return $obj->elControls;
	}

	/**
	 * Layout Controls
	 *
	 * @param object $obj Variable.
	 *
	 * @return array
	 */

	public function image_align( $obj ) {
		$obj->elControls[] = [
			'type'        => \Elementor\Controls_Manager::CHOOSE,
			'id'          => 'tlp_el_image_align_promo',
			'label'       => __( 'Image Alignment', 'tlp-food-menu' ),
			'options' => [
				'flex-start' => [
					'title' => esc_html__( 'Left', 'textdomain' ),
					'icon' => 'eicon-text-align-left',
				],
				'center' => [
					'title' => esc_html__( 'Center', 'textdomain' ),
					'icon' => 'eicon-text-align-center',
				],
				'flex-end' => [
					'title' => esc_html__( 'Right', 'textdomain' ),
					'icon' => 'eicon-text-align-right',
				],
			],
			'default' => 'center',
			'toggle' => true,
			'selectors' => [
				'{{WRAPPER}} .fmp-wrapper .fmp-image-wrap, {{WRAPPER}} .fmp-box .fmp-img-wrapper' => 'align-self: {{VALUE}};',
			],
			'classes'     => $this->classes,
		];
		return $obj->elControls;
	}

	/**
	 * Layout Controls
	 *
	 * @param object $obj Variable.
	 *
	 * @return array
	 */

	public function image_animation( $obj ) {
		$obj->elControls[] = [
			'type'        => 'select2',
			'id'          => 'tlp_el_image_animation',
			'label'       => __( 'Image Animation', 'tlp-food-menu' ),
			'options'     => [
				'none'    => __( 'None', 'tlp-food-menu' ),
				'zoom_in'    => __( 'Zoom In', 'tlp-food-menu' ),
				'zoom_out'    => __( 'Zoom Out', 'tlp-food-menu' ),
			],
			'default'     => 'none',
			'description' => __( 'Please select thme', 'tlp-food-menu' ),
			'classes'     => $this->classes,
		];
		return $obj->elControls;
	}
}
