<?php
/**
 * Elementor GridCatLayout Widget Class.
 *
 * @package RT_FoodMenu
 */

namespace RT\FoodMenu\Widgets\Elementor\Elements;
use RT\FoodMenu\Models\QueryArgs;
use RT\FoodMenu\Abstracts\ElementorWidget;
use RT\FoodMenu\Helpers\Fns;
use RT\FoodMenu\Helpers\RenderHelpers;
use RT\FoodMenu\Widgets\Elementor\Sections\{
	Style,
	Layout,
	Settings
};

/**
 * Elementor GridCatLayout Widget Class.
 */
class GridCatLayout extends ElementorWidget {
	/**
	 * Class constructor.
	 *
	 * @param array $data default data.
	 * @param array $args default arg.
	 */
	public function __construct( $data = [], $args = null ) {
		$this->elBase   = 'rtfm-food-grid-by-cat';
		$this->elName   = esc_html__( 'Grid by Category Layouts', 'tlp-food-menu' );
		$this->elIcon   = 'rtfm-elementor-icon eicon-gallery-grid';
		$this->elPrefix = 'gridbycat';
		parent::__construct( $data, $args );
	}

	/**
	 * Controls for layout tab
	 *
	 * @return object
	 */

	protected function layoutTab() {
		$sections = [
			'grid_layout' => [ $this, $this->elPrefix ],
			'columns'     => [ $this ],
			'query'       => [ $this ],
			'image'       => [ $this, $this->elPrefix ],
		];

		foreach ( $sections as $method => $args ) {
			if ( method_exists( Layout::class, $method ) ) {
				call_user_func_array( [ Layout::class, $method ], $args );
			}
		}
		return $this;
	}

	/**
	 * Controls for settings tab
	 *
	 * @return object
	 */
	protected function settingsTab() {
		$sections = [
			'category_settinngs',
			'ContentVisibility',
			'links',
			'contentLimit',
		];
		foreach ( $sections as $section ) {
			Settings::$section( $this );
		}
		return $this;
	}


	/**
	 * Controls for style tab
	 *
	 * @return object
	 */
	protected function styleTab() {

		$sections = apply_filters(
			'fm_grid_layout_section',
			[
				'globaltSection',
				'catSection',
				'titleSection',
				'priceSection',
				'excerptSection',
				'imageSection',
				'readmoreButton',
				'addToCarButton',
			]
		);

		foreach ( $sections as $section ) {
			Style::$section( $this, $this->elPrefix );
		}
		return $this;
	}

	protected function render() {
		\wp_enqueue_script( 'fmp-modal' );
		wp_dequeue_script( 'fmp-frontend' );
		\wp_enqueue_script( 'fmp-frontend' );

		$nonce   = wp_create_nonce( Fns::nonceText() );
		$ajaxurl = '';

		if ( in_array( 'sitepress-multilingual-cms/sitepress.php', get_option( 'active_plugins' ) ) ) {
			$ajaxurl .= admin_url( 'admin-ajax.php?lang=' . ICL_LANGUAGE_CODE );
		} else {
			$ajaxurl .= admin_url( 'admin-ajax.php' );
		}

		wp_localize_script(
			'fmp-frontend',
			'fmp',
			[
				'ajaxurl'     => esc_url( $ajaxurl ),
				'nonceID'     => esc_attr( Fns::nonceID() ),
				'nonce'       => esc_attr( $nonce ),
				'hasPro'      => TLPFoodMenu()->has_pro() ? 'true' : 'false',
				'wc_cart_url' => TLPFoodMenu()->isWcActive() ? wc_get_cart_url() : '',
			]
		);

		$settings = $this->get_settings_for_display();

		if( 'masonry' === $settings['tlp_el_grid_style_promo'] ){
			$this->get_widget_scripts($this->elPrefix);
		}

		$masonry = ( isset( $settings['tlp_el_grid_style_promo'] ) && $settings['tlp_el_grid_style_promo'] === 'masonry' ) ? 'rt-masonry-grid' : '';

		$animation = '';

		if( 'zoom_out' === $settings['tlp_el_image_animation'] ){
			$animation = 'fmp-hover-zoom_out';
		}elseif ( 'zoom_in' === $settings['tlp_el_image_animation'] ) {
			$animation = 'fmp-hover-zoom_in';
		}

		$scID     = (int) $this->get_id();
		$layout   = isset( $settings['fmp_layout'] ) ? $settings['fmp_layout'] : '1';

		switch ( $layout ) {
			case 'layout2':
				$template = 'grid-by-cat-free-2';
				break;
			case 'layout3':
				$template = 'grid-by-cat-free-3';
				break;
			case 'layout4':
				$template = 'grid-by-cat-free-4';
				break;
			case 'layout5':
			case 'layout6':
			case 'layout7':
				if ( defined( 'FOOD_MENU_PRO_VERSION' ) ) {
					switch ( $layout ) {
						case 'layout5':
							$template = 'grid-by-cat-pro-1';
							break;
						case 'layout6':
							$template = 'grid-by-cat-pro-2';
							break;
						case 'layout7':
							$template = 'grid-by-cat-pro-3';
							break;
					}
				} else {
					$template = 'grid-by-cat-free-1';
				}
				break;
			default:
				$template = 'grid-by-cat-free-1';
				break;
		}

		$arg      = [];
		$base_arg = RenderHelpers::metaScBuilderEl( $settings );


		$args = ( new QueryArgs() )->buildArgs( $scID, $base_arg );

		// Set post type if needed
		if ( $base_arg['source'] === 'product' ) {
			$args['post_type'] = 'product';
		}

		$taxonomy = '';
		if ( isset( $base_arg['source'] ) ) {
			switch ( $base_arg['source'] ) {
				case 'food-menu':
					$taxonomy = 'food-menu-cat';
					break;
				case 'product':
					$taxonomy = 'product_cat';
					break;
				default:
					$taxonomy = 'food-menu-cat';
			}
		}

		// Get term IDs
		$term_ids = [];
		if ( ! empty( $base_arg['cats'] ) && is_array( $base_arg['cats'] ) ) {
			$term_ids = $base_arg['cats'];
		} else {

			$all_terms = get_terms( [
				'taxonomy'   => $taxonomy,
				'hide_empty' => true,
			] );

			if ( ! is_wp_error( $all_terms ) ) {
				foreach ( $all_terms as $t ) {
					$term_ids[] = $t->term_id;
				}
			}
		}

		$type = $settings['fmp_cat_style'];

		if( empty( $type ) ){
			if( $layout === 'layout3' || $layout === 'layout6' ){
				$type = 'type-2';
			} elseif ( $layout === 'layout7' ){
				$type = 'type-7';
			} else{
				$type = 'type-1';
			}
		}

		echo "<div class='fmp-image-top fmp-container-fluid fmp-wrapper " . esc_attr( $animation ) . "' id='fmp-container-" . esc_attr( $layout ) . "'>";
		echo "<div class='fmp-grid-by-cat-free fmp-elementor-layout fmp-row fmp-content-loader fmp-" . esc_attr( $layout ) . " fmp-even'>";

		echo "<div class='fmp-grids-wrapper fmp-innner-sections'>";

		if( 'layout6' === $layout || 'layout7' === $layout ){
			echo "<div class='fmp-row ".esc_attr( $masonry )." '>";
		}

		foreach ( $term_ids as $term_id ) {
			$fmp_col = $base_arg['dCols'];

			$fmp_col = !empty( $fmp_col ) ? $fmp_col : 2;

			$term = get_term( $term_id, $taxonomy );

			$catImgSrc = '';

			$cat_thumb_id = get_term_meta( $term->term_id, 'fmp_cat_thumbnail_id', true );

			if ( $taxonomy === 'product_cat' ) {
				$cat_thumb_id = get_term_meta( $term->term_id, 'thumbnail_id', true );
			}

			$catImage = '';
			if ( $cat_thumb_id ) {
				$catImageS = wp_get_attachment_image_src( $cat_thumb_id, 'large' );
				if ( ! empty( $catImageS[0] ) ) {
					$catImgSrc = $catImageS[0];
					$catImage = 'style="background-image: url(' . esc_url( $catImgSrc ) . ');"';
				}
			}

			if ( is_wp_error( $term ) ) {
				continue;
			}

			if( 'layout5' === $layout ){
				echo "<div class='fmp-innner-wrap'>";
			}
			if( 'layout6' === $layout || 'layout7' === $layout  ){
				$masonry_item = isset( $settings['tlp_el_grid_style_promo'] ) ? 'masonry-grid-item' : ' ';
				echo "<div class='fmp-col-lg-".esc_attr( $fmp_col )." fmp-col-md-".esc_attr( $fmp_col )." fmp-col-sm-12 fmp-col-xs-12 ".esc_attr( $masonry_item )."'>";
				echo '<div class="fmp-innner-wrap">';
			}

			echo "<div class='fmp-category-title-wrapper " . esc_attr( $type ) . "' " . ( 'layout7' === $layout && ! empty( $catImage ) ? $catImage : '' ) . '>';

			echo "<h2 class='fmp-category-title'><span>{$term->name}</span></h2>";
			echo "</div>";

			echo '<div class="fmp-col-xs-12 fmp-grids-wrapper fmp-product-inner">';

			 if ( $layout != 'layout6' && $layout != 'layout7' ) {
				echo '<div class="fmp-row '.esc_attr( $masonry ).'">';
			}

			// Clone base args for this term
			$term_args = $args;
			$term_args['tax_query'] = [
				[
					'taxonomy' => $taxonomy,
					'field'    => 'term_id',
					'terms'    => $term_id,
				]
			];

			$query = new \WP_Query( $term_args );

			if ( $query->have_posts() ) {
				while ( $query->have_posts() ) {
					$query->the_post();
					$post_id   = get_the_ID();
					$post_data = [
						'ID'        => $post_id,
						'title'     => get_the_title(),
						'content'   => get_the_content(),
						'excerpt'   => get_the_excerpt(),
						'permalink' => get_permalink(),
						'image'     => get_the_post_thumbnail_url( $post_id, 'full' ),
						'meta'      => [
							'price' => get_post_meta( $post_id, '_regular_price', true ),
						],
						'terms'     => wp_get_post_terms( $post_id, $taxonomy, [ 'fields' => 'names' ] ),
					];

					$arg = array_merge( $base_arg, $post_data, [
						'pID' => $post_id,
					] );

					Fns::render( 'elementor/' . $template, $arg );
				}
				wp_reset_postdata();
			}
			echo '</div>';

			echo '</div>';
			if( 'layout5' === $layout || 'layout6' === $layout || 'layout7' === $layout  ){
				echo '</div>';
			}
		}
		if( 'layout6' === $layout || 'layout7' === $layout  ){
			echo '</div>';
		}
		echo '</div>';
		echo '</div>';
		echo '</div>';
	}
}