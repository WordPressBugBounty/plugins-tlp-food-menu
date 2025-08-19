<?php
/**
 * Elementor List Widget Class.
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
 * Elementor List Widget Class.
 */
class ListLayout extends ElementorWidget {
	/**
	 * Class constructor.
	 *
	 * @param array $data default data.
	 * @param array $args default arg.
	 */
	public function __construct( $data = [], $args = null ) {
		$this->elBase   = 'rtfm-food-list';
		$this->elName   = esc_html__( 'List Layouts', 'tlp-food-menu' );
		$this->elIcon   = 'rtfm-elementor-icon eicon-gallery-grid';
		$this->elPrefix = 'list';
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
			'pagination'  => [ $this ],
			'image'       => [ $this ],
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
			'fm_list_layout_section',
			[
				'globaltSection',
				'titleSection',
				'priceSection',
				'excerptSection',
				'imageSection',
				'pagination',
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

		$scID     = (int) $this->get_id();
		$layout     = isset( $settings['fmp_layout'] ) ? $settings['fmp_layout'] : '1';
		$masonry = ( isset( $settings['tlp_el_grid_style_promo'] ) && $settings['tlp_el_grid_style_promo'] === 'masonry' ) ? 'rt-masonry-grid' : '';

		switch ( $layout ){
			case 'layout2':
				$template = 'layout-list-free-2';
				break;
			case 'layout3':
				$template = 'layout-list-free-3';
				break;
			case 'layout4':
				$template = 'layout-list-free-4';
				break;

			case 'layout5':
			case 'layout6':
			case 'layout7':
				if ( defined( 'FOOD_MENU_PRO_VERSION' ) ) {
					switch ( $layout ) {
						case 'layout5':
							$template = 'layout-list-pro-1';
							break;
						case 'layout6':
							$template = 'layout-list-pro-2';
							break;
						case 'layout7':
							$template = 'layout-list-pro-3';
							break;
					}
				} else {
					$template = 'layout-list-free-1';
				}
				break;
			default:
				$template = 'layout-list-free-1';
				break;

		}

        $template = apply_filters('fmp_el_list_layout_template', $template, $layout );

		$animation = '';
		if( 'zoom_out' === $settings['tlp_el_image_animation'] ){
			$animation = 'fmp-hover-zoom_out';
		}elseif ( 'zoom_in' === $settings['tlp_el_image_animation'] ) {
			$animation = 'fmp-hover-zoom_in';
		}

		$arg      = [];

		$base_arg = RenderHelpers::metaScBuilderEl( $settings );

		$args = ( new QueryArgs() )->buildArgs( $scID, $base_arg );

		if ( $base_arg['source'] === 'product' ) {
			$args['post_type'] = 'product';
			if ( ! empty( $args['tax_query'] ) && is_array( $args['tax_query'] ) ) {
				$args['tax_query'][0]['taxonomy'] = 'product_cat';
			}
		}
		$query = new \WP_Query( $args );

		/**
		 * Get Post Data for render post
		 */

		//jqueryn jon
		$query_json = htmlspecialchars( wp_json_encode( $args ), ENT_QUOTES, 'UTF-8' );

		//settings_json
		$ajax_data = Fns::get_render_data_set( $settings, $query->max_num_pages, $animation, $template, );
		$settings_json = htmlspecialchars( wp_json_encode( $ajax_data ), ENT_QUOTES, 'UTF-8' );

		$ajax_class = '';
		if( $base_arg['posts_loading_type'] === 'ajax-number-pagination' || $base_arg['posts_loading_type'] === 'ajax-load-more-button' ){
			$ajax_class = 'fmp-el-ajax-loader';
		}elseif( $base_arg['posts_loading_type'] === 'ajax-load-more-scroll' ){
			$ajax_class = 'fmp-autoloader-scroll fmp-el-ajax-loader';
		}
		echo "<div data-layout='" . esc_attr( $layout ) . "' class='fmp-" . esc_attr( $layout ) . " " . esc_attr( $animation ) . " fmp-container-fluid fmp-wrapper fmp-list-layout ' id='fmp-container-" . esc_attr( $layout ) . " '
		>";

		echo "<div
		
		data-el-query='" . $query_json . "'
		data-el-settings='" . $settings_json . "'>";

		echo "<div class='fmp-row " . esc_attr( $masonry ) . " ".$ajax_class." fmp-elementor-layout fmp-content-loader fmp-" . esc_attr( $layout ) . " fmp-read-more-active'>";

		if ( $query->have_posts() ) :
			while ( $query->have_posts() ) :
				$query->the_post();
				$post_id   = get_the_ID();
				$post_data = array(
					'pID'        => $post_id,
					'title'     => get_the_title(),
					'content'   => get_the_content(),
					'excerpt'   => get_the_excerpt(),
					'permalink' => get_permalink(),
					'image'     => get_the_post_thumbnail_url( $post_id, 'full' ),
					'meta'      => array(
						'price' => get_post_meta( $post_id, '_regular_price', true ),
					),
					'terms'     => wp_get_post_terms( $post_id, 'food-category', array( 'fields' => 'names' ) ),
				);
				$arg = array_merge( $base_arg, $post_data );
				Fns::render( 'elementor/' . $template, $arg );
			endwhile;
			wp_reset_postdata();
		else :
			echo 'No food menu items found.';
		endif;

		echo '</div>';

		//Normal Pagination Code
		if( $base_arg['posts_loading_type'] == 'number-pagination' ) {
			$pagination = ! empty( $base_arg['pagination'] ) && $base_arg['pagination'] == 1;
			if ( $pagination ) {
				$renderPagination = RenderHelpers::renderElPagination(
					$query,
					$base_arg,
					$base_arg['limit'] ?? 2,
				);
				echo '<div class="fmp-col-xs-12">';
                    echo '<div class="fmp-utility">';
                    $loading_type = $base_arg['posts_loading_type'] ?? '';
                    $allowedTypes = [ 'ajax-number-pagination', 'number-pagination', 'ajax-load-more-scroll' ];
                    if ( in_array( $loading_type, $allowedTypes, true ) && ! empty( $renderPagination ) && is_string( $renderPagination ) ) {
                        echo wp_kses_post( $renderPagination );
                    }
                    echo '</div>';
				echo '</div>';
			};
		}

		//Ajax Number Pagination Code
		if ( $base_arg['posts_loading_type'] === 'ajax-number-pagination' ) {
			$total_pages  = $query->max_num_pages;
			$paged   = isset( $_POST['paged'] ) ? intval( $_POST['paged'] ) : 1;
			if ( $total_pages > 1 ) { ?>
				<div class="ajax-el-number-pagination fmp-pagination">
					<?php for ( $i = 1; $i <= $total_pages; $i++ ) :
						$active_class = ( $i === $paged ) ? ' active' : '';
						?>
						<a href="#" class="page-num<?php echo esc_attr( $active_class ); ?>" data-page="<?php echo esc_attr( $i ); ?>">
							<?php echo esc_html( $i ); ?>
						</a>
					<?php endfor; ?>
				</div>
			<?php }
		}

		//Ajax Loadmore Button Pagination Code
		if ( $base_arg['posts_loading_type'] === 'ajax-load-more-button' ) {
			echo '<div class="fmp-pagination"><button class="fmp-load-more-btn">' . esc_html__('Load More', 'food-menu-pro') . '</button></div>';
		}
		echo '</div>';
		echo '</div>'; ?>
        <?php


	}
}