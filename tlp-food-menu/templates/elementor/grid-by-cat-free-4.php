<?php
/**
 * Template: Layout 1 (Free).
 *
 * @package RT_FoodMenu
 */

use RT\FoodMenu\Helpers\Fns;
use RT\FoodMenu\Helpers\RenderHelpers;

// Do not allow directly accessing this file.
if ( ! defined( 'ABSPATH' ) ) {
	exit( 'This script cannot be accessed directly.' );
}

$add_to_cart = null;

if ( $source == 'product' && $wc == true ) {
	global $product;
	$product = $_product = wc_get_product( $pID );
	$price   = $_product->get_price_html();
	$pType   = $_product->get_type();
	$pLink   = $_product->get_permalink();
	$add_to_cart_text = $_product->add_to_cart_text();
	$add_to_cart_text = $_product->add_to_cart_text();
	if ( $_product->is_purchasable() ) {
		if ( $_product->is_in_stock() ) {
			ob_start();
			woocommerce_template_loop_add_to_cart();
			$add_to_cart .= apply_filters( 'rtfm_add_to_cart_btn', ob_get_contents(), $pLink, $pID, $pType, $add_to_cart_text, $items, $addtocart, $quantity, $add_stock );
			ob_end_clean();
		}
	}
} else {
	$price = Fns::getPriceWithLabel( $pID );
	if ( TLPFoodMenu()->has_pro() ) {
		$price = \RT\FoodMenuPro\Helpers\FnsPro::fmpHtmlPrice( $pID );
	}
}

//$class   .= ' fmp-item-' . $pID;
$wooClass = 'product' === $source ? ' woo-template' : null;

$fmp_col = !empty( $dCols ) ? $dCols : 4;
$fmp_tCol = !empty( $tCols ) ? $tCols : 6;
$fmp_mCol = !empty( $mCols ) ? $mCols : 12;

$excerpt_text = '';
if ( !empty( $excerpt ) ) {
	$content = $excerpt_limit ? wp_trim_words( $excerpt, $excerpt_limit, '.' ) : $excerpt;
	$excerpt_text = '<p>' . esc_html( $content ) . '</p>';
}
$icon = ( $hovericon === 'yes' ) ? 'fmp-icon-enable' : 'fmp-icon-disable';
$masonry = ( $grid_style === 'masonry' ) ? 'masonry-grid-item' : '';
$target = $detail_link == 'target' ? ' target="_blank"' : '';
$popup = $fmp_el_popup == 'yes' ? 'fmp-popup' : 'fmp-link';
?>
<div class="fmp-col-md-<?php echo esc_attr( $fmp_col );?>  fmp-col-sm-<?php echo esc_attr( $fmp_tCol );?> fmp-col-xs-<?php echo esc_attr( $fmp_mCol );?> <?php echo esc_attr( $icon ) ;?>  <?php echo esc_attr( $masonry )?> fmp-grid-item fmp-ready-animation fmp-item-142">
	<div class='fmp-food-item <?php echo esc_attr( $source ); ?>'>
		<?php
		$html = '';

        if( $featureImg == 'yes' ){
            $image_size = isset( $imgSize ) ? $imgSize : 'full';
            $image = Fns::getFeatureImage( $pID, $image_size );
            if ( !empty( $image ) ) {

                $html .= '<div class="fmp-image-wrap">';

                if( !$detail_link ){
                    $html .= $image;
                } else{
                    $html .= '<a data-id="' . esc_attr( $pID ) . '" class="'.esc_attr( $popup ).'" href="' . get_permalink() . '" target="' . esc_attr( $target ) . '">'.$image.'</a>';
                }
                $html .= '</div>';
            }
        }

		$html .= '<div class="fmp-content-wrap">';
		$html .= '<div class="fmp-title' . $wooClass . '">';

		if ( !empty( $title ) &&  $titleswitch == 'yes'  ) {
			if ( ! $detail_link ) {
				$html .= '<h3>' . esc_html( $title ) . '</h3>';
			} else {
				$html .= '<h3><a data-id="' . esc_attr( $pID ) . '" class="'.esc_attr( $popup ).'" href="' . get_permalink() . '"  target="' . esc_attr( $target ) . '" title="' . get_the_title() . '">' . get_the_title() . '</a></h3>';
			}
		}

		if ( !empty($meta['price']) && $priceswitch == 'yes' ) {
			$html .= '<span class="price">' . wp_kses_post( $price ) . '</span>';
		}

		$html .= '</div>';
		$html .= '<div class="fmp-body">';

		if ( !empty( $content )  && $contentswitch == 'yes' ) {
			$html .= $excerpt_text;
		}

		$html .= '</div>';

		// stock status.
        if ( (TLPFoodMenu()->has_pro() && $readmore_switch == 'yes' ) || ( 'product' === $source && 'yes' === $addtocart ) ) : ?>
        <div class="fmp-footer woo-template">
			<?php
			if ( TLPFoodMenu()->has_pro() && $readmore_switch == 'yes' ) {
				$html .= '<a data-id="' . esc_attr( $pID ) . '" href="' . esc_url( get_permalink() ) . '"  target="' . esc_attr( $target ) . '"  class="fmp-btn-read-more '.esc_attr( $popup ).'">' . esc_html( $readmore_text ) . '</a>';
			}
			if ( 'product' === $source && 'yes' === $addtocart ) {
				$html .= '<div class="fmp-wc-add-to-cart-wrap">';
				if ( ! TLPFoodMenu()->has_pro() || $addtocart === 'yes' ) {
					$html .= stripslashes_deep( $add_to_cart );
				}
				$html .= '</div>';
			}
			?>
        </div>
		<?php endif;

		$html .= '</div>';
		    Fns::print_html( $html );
        ?>
	</div>
</div>

