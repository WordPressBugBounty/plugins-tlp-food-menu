<?php
/**
 * Action Hook Class.
 *
 * @package RT_FoodMenu
 */

namespace RT\FoodMenu\Controllers\Hooks;

use RT\FoodMenu\Helpers\Fns;
use RT\FoodMenuPro\Helpers\FnsPro;

// Do not allow directly accessing this file.
if ( ! defined( 'ABSPATH' ) ) {
	exit( 'This script cannot be accessed directly.' );
}

/**
 * Action Hook Class.
 */
class ActionHooks {
	use \RT\FoodMenu\Traits\SingletonTrait;

	/**
	 * Class.
	 *
	 * @var string
	 */
	public $classes = '';

	/**
	 * Settings.
	 *
	 * @var array
	 */
	public $settings = [];

	/**
	 * Class Init.
	 *
	 * @return void
	 */
	protected function init() {
		$this->settings = get_option( TLPFoodMenu()->options['settings'] );
		add_action( 'fmp_single_summery', [ $this, 'fmp_single_images' ], 10 );
		add_action( 'fmp_single_summery', [ $this, 'fmp_before_summery' ], 20 );
		add_action( 'fmp_single_summery', [ $this, 'fmp_summery_title' ], 30 );
		add_action( 'fmp_single_summery', [ $this, 'fmp_summery_price' ], 40 );
		add_action( 'fmp_single_summery', [ $this, 'fmp_summery' ], 50 );
		add_action( 'fmp_single_summery', [ $this, 'fmp_summery_meta' ], 60 );
		add_action( 'fmp_single_summery', [ $this, 'fmp_after_summery' ], 70 );
		$enable_food_location = $this->settings['fmp_food_location_popup'] ?? '';
		if ( TLPFoodMenu()->isWcActive() && ! empty( $enable_food_location ) ) {
			// footer load location popup.
			add_action( 'wp_footer', [ $this,'render_food_location_popup' ] );
			// add new checkout page and save meta date.
			add_action( 'woocommerce_checkout_before_customer_details', [ $this,'render_location_form' ] );
			add_action( 'woocommerce_checkout_create_order', [ $this, 'location_update_meta' ] );
			add_action( 'woocommerce_order_details_after_order_table_items', [ $this, 'order_details' ] );
		}
	}

	/**
	 * Order details meta data
	 *
	 * @param object $order .
	 * @return void
	 */
	public function order_details( $order ) {
		$food_location = $order->get_meta( 'fmp_location_name' );
		if ( $food_location ) :
			?>
			<tr>
				<th scope="row"><?php echo esc_html__( 'Food Location:', 'food-menu-pro' ); ?></th>
				<td><?php echo esc_html( $food_location ); ?></td>
			</tr>
			<?php
		endif;
	}

	/**
	 * Update location form data.
	 *
	 * @param object $order .
	 * @return void
	 */
	public function location_update_meta( $order ) {
		$nonce_value = $_REQUEST['woocommerce-process-checkout-nonce'] ?? '';
		if ( ! wp_verify_nonce( $nonce_value, 'woocommerce-process_checkout' ) ) {
			return;
		}
		if ( ! empty( sanitize_text_field( wp_unslash( $_POST['fmp_location_name'] ) ) ) ) {
			$order->update_meta_data( 'fmp_location_name', sanitize_text_field( wp_unslash( $_POST['fmp_location_name'] ) ) );
		}
	}

	/**
	 * Checkout location form.
	 *
	 * @return void
	 */
	public function render_location_form() {
		$order_location = apply_filters( 'fm_order_location_checkout_title', __( 'Food Order Location', 'tlp-food-menu' ) );
		?>
		 <div id="fmp-location-field">
			<div class="fmp-location-title"><?php echo esc_html( $order_location ); ?></div>
			<div class="fmp-location-name"></div>
			<input type="hidden" name="fmp_location_name" class="fmp-location-name" />
		</div>
		<?php
	}

	/**
	 * Popup food location in footer.
	 *
	 * @return void
	 */
	public function render_food_location_popup() {
		?>
			<div class="fmp-location-box-wrap"></div>
			<script type="text/javascript">
				const locationData = localStorage.getItem('fmp_location');

				if ( ( null === locationData ) ){
					jQuery(document).ready(function () {
							jQuery(".fmp-location-box-wrap").html(`
								<div id="fmp-location-modal" class="fmp-popup-modal">
									<div class="modal-content">
										<select name="fmp-location" class="fmp-location">
											<?php
											$fmp_locations = Fns::get_location_data( '', '', 'id' );
											foreach ( $fmp_locations as $key => $value ) {
												$selected = count( $fmp_locations ) <= 2 ? 'selected=selected' : '';
												echo "<option value='" . esc_html( $key ) . "'" . esc_attr( $selected ) . '>' . esc_html( $value ) . '</option>';
											}
											?>
										</select>

										<div class="confirm-msg fmp-hidden"><?php echo esc_html__( 'Save Your Preferred Location', 'tlp-food-menu' ); ?></div>
										<button class="fmp-save-option fmp-btn"><?php echo esc_html__( 'Save', 'tlp-food-menu' ); ?></button>
										<button class="fmp-close fmp-btn"> X </button>
									</div>
								</div>
							`);
					});
				}
			</script>
			<?php
	}

	/**
	 * Single Images.
	 *
	 * @return void
	 */
	public function fmp_single_images() {
		$settings      = get_option( TLPFoodMenu()->options['settings'] );
		$hiddenOptions = ! empty( $settings['hide_options'] ) ? $settings['hide_options'] : [];
		$thumbClass    = has_post_thumbnail() ? 'has-thumbnail' : 'no-thumbnail';

		global $post;

		$html = null;

		if ( ! in_array( 'image', $hiddenOptions ) ) {
			$html .= '<div class="fmp-col-md-5 fmp-col-lg-5 fmp-col-sm-6">';
			$html .= '<div class="fmp-images ' . esc_attr( $thumbClass ) . '" id="fmp-images">';

			if ( TLPFoodMenu()->has_pro() ) {
				$attachments = get_post_meta( $post->ID, '_fmp_image_gallery', true );

				$attachments = is_array( $attachments ) ? $attachments : [];

				if ( has_post_thumbnail() ) {
					array_unshift( $attachments, get_post_thumbnail_id( $post->ID ) );
				}

				if ( ! empty( $attachments ) ) {
					if ( count( $attachments ) > 1 ) {
						$thumbnails = null;
						$slides     = null;

						foreach ( $attachments as $attachment ) {
							$slides     .= "<div class='swiper-slide'>" . Fns::getAttachedImage( $attachment, 'full' ) . '</div>';
							$thumbnails .= "<div class='swiper-slide'>" . Fns::getAttachedImage( $attachment, 'thumbnail' ) . '</div>';
						}

						$slider  = null;
						$slider .= "<div id='fmp-slide-wrapper' class='fmp-single-slider fmp-pre-loader'>";
						$slider .= "<div id='fmp-slider-main' class='rtfm-carousel-main swiper slider-loading'>
										<div class='swiper-wrapper'>{$slides}</div>
										<div class='swiper-nav'>
											<div class='swiper-arrow swiper-button-next'><i class='fa fa-chevron-right'></i></div>
											<div class='swiper-arrow swiper-button-prev'><i class='fa fa-chevron-left'></i></div>
										</div>
									</div>";

						if ( in_array( $post->post_type, [ TLPFoodMenu()->post_type, 'product' ] ) ) {
							$slider .= "<div id='fmp-slider-thumb' class='rtfm-carousel-thumb swiper slider-loading'>
											<div class='swiper-wrapper'>{$thumbnails}</div>
										</div>";
						}

						$slider .= '<div class="fmp-loading-overlay full-op"></div><div class="fmp-loading fmp-ball-clip-rotate"><div></div></div>';
						$slider .= '</div>';

						$html .= $slider;
					} else {
						$html .= "<div class='fmp-single-food-img-wrapper'>";
						$html .= Fns::getAttachedImage( $attachments[0], 'full' );
						$html .= '</div>';
					}
				} else {
					$imgSrc = Fns::placeholder_img_src();
					$html  .= "<div class='fmp-single-food-img-wrapper'>";
					$html  .= '<img class="fmp-single-food-img" alt="Place holder image" src="' . esc_url( $imgSrc ) . '" />';
					$html  .= '</div>';
				}
			} else {
				if ( has_post_thumbnail() ) {
					$html .= get_the_post_thumbnail( $post->ID, [ 500, 500 ] );
				} else {
					$html .= "<img src='" . esc_url( TLPFoodMenu()->assets_url() ) . 'images/demo-100x100.png' . "' alt='" . get_the_title( $post->ID ) . "' />";
				}
			}
			$html .= '</div>'; // #images
			$html .= '</div>';
		}

		Fns::print_html( $html );
	}

	public function fmp_before_summery() {
		$settings      = get_option( TLPFoodMenu()->options['settings'] );
		$hiddenOptions = ! empty( $settings['hide_options'] ) ? $settings['hide_options'] : [];

		if ( in_array( 'image', $hiddenOptions ) ) {
			echo '<div class="fmp-col-md-12 paddingr0 fmp-summery" id="fmp-summery">';
		} else {
			echo '<div class="fmp-col-md-7 fmp-col-lg-7 fmp-col-sm-6 paddingr0 fmp-summery" id="fmp-summery">';
		}
	}

	public function fmp_after_summery() {
		echo '</div>';
	}

	public function fmp_summery_title() {
		?>
		<h2 class><?php the_title(); ?></h2>
		<?php
	}

	public function fmp_summery_price() {
		if ( TLPFoodMenu()->has_pro() ) {
			return;
		}

		$settings      = get_option( TLPFoodMenu()->options['settings'] );
		$hiddenOptions = ! empty( $settings['hide_options'] ) ? $settings['hide_options'] : [];

		if ( ! in_array( 'price', $hiddenOptions ) ) {
			$gTotal = Fns::getPriceWithLabel();
			echo '<div class="offers">' . wp_kses( $gTotal, Fns::allowedHtml() ) . '</div>';
		}
	}

	public function fmp_summery() {
		$settings      = get_option( TLPFoodMenu()->options['settings'] );
		$hiddenOptions = ! empty( $settings['hide_options'] ) ? $settings['hide_options'] : [];

		if ( ! in_array( 'summery', $hiddenOptions ) || ( wp_doing_ajax() && ! in_array( 'description', $hiddenOptions ) ) ) {
			?>
			<div class="fmp-short-description summery entry-summery ">
				<?php
				global $post;

				if ( in_array( $post->post_type, [ TLPFoodMenu()->post_type, 'product' ] ) ) {
					the_content();
				} else {
					if ( ! in_array( 'summery', $hiddenOptions ) ) {
						the_excerpt();
					}

					if ( wp_doing_ajax() && ! in_array( 'description', $hiddenOptions ) ) {
						the_content();
					}
				}
				?>
			</div>
			<?php
		}
	}

	public function fmp_summery_meta() {
		$settings      = get_option( TLPFoodMenu()->options['settings'] );
		$hiddenOptions = ! empty( $settings['hide_options'] ) ? $settings['hide_options'] : [];

		if ( ! in_array( 'taxonomy', $hiddenOptions ) ) {
			global $post;

			$cat       = get_the_terms( $post->ID, TLPFoodMenu()->taxonomies['category'] );
			$cat_count = is_array( $cat ) ? sizeof( $cat ) : 0;
			?>
			<div class="fmp-meta">
				<?php
				do_action( 'fmp_meta_start' );

				Fns::print_html(
					Fns::get_categories(
						$post->ID,
						', ',
						'<span class="posted_in">' . _n(
							'Category:',
							'Categories:',
							$cat_count,
							'tlp-food-menu'
						) . ' ',
						'</span>'
					)
				);

				if ( TLPFoodMenu()->has_pro() ) {
					$tag       = get_the_terms( $post->ID, TLPFoodMenu()->taxonomies['tag'] );
					$tag_count = is_array( $tag ) ? sizeof( $cat ) : 0;

					Fns::print_html(
						FnsPro::get_tags(
							$post->ID,
							', ',
							'<span class="tagged_as">' . _n( 'Tag:', 'Tags:', $tag_count, 'tlp-food-menu' ) . ' ',
							'</span>'
						)
					);
				}

				do_action( 'fmp_meta_end' );
				?>
			</div>
			<?php
		}
	}
}
