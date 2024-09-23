<?php
/**
 * Mini-cart
 *
 * Contains the markup for the mini-cart, used by the cart widget.
 *
 * This template can be overridden by copying it to yourtheme/woocommerce/cart/mini-cart.php.
 *
 * HOWEVER, on occasion WooCommerce will need to update template files and you
 * (the theme developer) will need to copy the new files to your theme to
 * maintain compatibility. We try to do this as little as possible, but it does
 * happen. When this occurs the version of the template file will be bumped and
 * the readme will list any important changes.
 *
 * @see     https://docs.woocommerce.com/document/template-structure/
 * @package WooCommerce\Templates
 * @version 3.7.0
 */

use RT\FoodMenu\Helpers\Fns;

defined( 'ABSPATH' ) || exit;
$settings = get_option( TLPFoodMenu()->options['settings'] );
do_action( 'woocommerce_before_mini_cart' );
$count_label = WC()->cart->get_cart_contents_count() < 2 ? __( 'Cart Item', 'food-menu-pro' ) : __( 'Shopping Cart', 'food-menu-pro' );
$style       = $settings['mini_cart_drawer_style'] ?? 'style1';

?>
<h3 class="mini-cart-header-wrapper">
	<div class="mini-cart-title">
		<?php if ( 'style2' === $style ) : ?>
			<svg width="23" height="25" viewBox="0 0 23 25" fill="none" xmlns="http://www.w3.org/2000/svg">
				<path d="M15.9368 10.7308V5.42308C15.9368 4.25 15.4694 3.12498 14.6374 2.29549C13.8055 1.466 12.6771 1 11.5005 1C10.324 1 9.19558 1.466 8.36363 2.29549C7.53167 3.12498 7.06428 4.25 7.06428 5.42308V10.7308M20.4984 8.38005L21.9925 22.5339C22.0753 23.3183 21.4602 24 20.6687 24H2.3323C2.14563 24.0002 1.96101 23.9612 1.79042 23.8857C1.61984 23.8101 1.46711 23.6996 1.34216 23.5613C1.2172 23.423 1.12282 23.2601 1.06514 23.0831C1.00745 22.9061 0.987762 22.719 1.00734 22.5339L2.50265 8.38005C2.53714 8.05399 2.69149 7.75221 2.93592 7.53289C3.18036 7.31357 3.49758 7.19225 3.82642 7.19231H19.1746C19.856 7.19231 20.4274 7.70538 20.4984 8.38005ZM7.50791 10.7308C7.50791 10.8481 7.46117 10.9606 7.37797 11.0435C7.29478 11.1265 7.18194 11.1731 7.06428 11.1731C6.94663 11.1731 6.83379 11.1265 6.7506 11.0435C6.6674 10.9606 6.62066 10.8481 6.62066 10.7308C6.62066 10.6135 6.6674 10.501 6.7506 10.418C6.83379 10.3351 6.94663 10.2885 7.06428 10.2885C7.18194 10.2885 7.29478 10.3351 7.37797 10.418C7.46117 10.501 7.50791 10.6135 7.50791 10.7308ZM16.3804 10.7308C16.3804 10.8481 16.3336 10.9606 16.2505 11.0435C16.1673 11.1265 16.0544 11.1731 15.9368 11.1731C15.8191 11.1731 15.7063 11.1265 15.6231 11.0435C15.5399 10.9606 15.4931 10.8481 15.4931 10.7308C15.4931 10.6135 15.5399 10.501 15.6231 10.418C15.7063 10.3351 15.8191 10.2885 15.9368 10.2885C16.0544 10.2885 16.1673 10.3351 16.2505 10.418C16.3336 10.501 16.3804 10.6135 16.3804 10.7308Z"
					  stroke="#323846" stroke-width="1.5" stroke-linecap="round" stroke-linejoin="round"/>
			</svg>
		<?php endif; ?>
		<div class="title"><?php echo esc_html( $count_label ); ?></div>
		<span class="cart-count"><?php echo absint( WC()->cart->get_cart_contents_count() ); ?></span>
		<span class="fmp-clear-cart"><?php echo esc_html__( 'Clear All', 'food-menu-pro' ); ?></span>
	</div>
</h3>
<?php if ( ! WC()->cart->is_empty() ) : ?>
	<ul class="woocommerce-mini-cart cart_list product_list_widget <?php echo esc_attr( $args['list_class'] ?? '' ); ?>">
		<?php
		do_action( 'woocommerce_before_mini_cart_contents' );
		foreach ( WC()->cart->get_cart() as $cart_item_key => $cart_item ) {
			$_product   = apply_filters( 'woocommerce_cart_item_product', $cart_item['data'], $cart_item, $cart_item_key );
			$product_id = apply_filters( 'woocommerce_cart_item_product_id', $cart_item['product_id'], $cart_item, $cart_item_key );
			if ( $_product && $_product->exists() && $cart_item['quantity'] > 0 && apply_filters( 'woocommerce_widget_cart_item_visible', true, $cart_item, $cart_item_key ) ) {
				$product_name      = apply_filters( 'woocommerce_cart_item_name', $_product->get_name(), $cart_item, $cart_item_key );
				$thumbnail         = apply_filters( 'woocommerce_cart_item_thumbnail', $_product->get_image(), $cart_item, $cart_item_key );
				$product_price     = apply_filters( 'woocommerce_cart_item_price', WC()->cart->get_product_price( $_product ), $cart_item, $cart_item_key );
				$product_permalink = apply_filters( 'woocommerce_cart_item_permalink', $_product->is_visible() ? $_product->get_permalink( $cart_item ) : '', $cart_item, $cart_item_key );
				?>
				<li class="woocommerce-mini-cart-item <?php echo esc_attr( apply_filters( 'woocommerce_mini_cart_item_class', 'mini_cart_item', $cart_item, $cart_item_key ) ); ?>"
					data-cart_item_key="<?php echo esc_attr( $cart_item_key ); ?>">

					<div class="single-product-wrapper">
						<div class="thumb-wrapper"><?php Fns::print_html( $thumbnail, true ); ?></div>
						<div class="details-wrapper">
							<h4 class="product-title">
								<a title="<?php echo esc_attr( $product_name ); ?>" href="<?php the_permalink( $product_id ); ?>">
									<?php echo esc_html( $product_name ); ?>
								</a>
							</h4>
							<?php echo wc_get_formatted_cart_item_data( $cart_item ); // phpcs:ignore WordPress.Security.EscapeOutput.OutputNotEscaped ?>

							<span class="product-subtotal">
							<?php Fns::print_html( WC()->cart->get_product_subtotal( $_product, $cart_item['quantity'] ), true ); ?>
							</span>

							<div class="fmp-product-quantity-wrap">
								<?php do_action( 'fmp_minicart_quantity', $_product, $cart_item_key, $cart_item ); ?>
							</div>

							<?php
							$remove_icon = '<svg width="12" height="14" viewBox="0 0 12 14" fill="none" xmlns="http://www.w3.org/2000/svg"><path d="M11.5 2.15385H9V1.61538C9 1.18696 8.84196 0.776079 8.56066 0.473135C8.27936 0.170192 7.89782 0 7.5 0H4.5C4.10218 0 3.72064 0.170192 3.43934 0.473135C3.15804 0.776079 3 1.18696 3 1.61538V2.15385H0.5C0.367392 2.15385 0.240215 2.21058 0.146447 2.31156C0.0526785 2.41254 0 2.5495 0 2.69231C0 2.83512 0.0526785 2.97208 0.146447 3.07306C0.240215 3.17404 0.367392 3.23077 0.5 3.23077H1V12.9231C1 13.2087 1.10536 13.4826 1.29289 13.6846C1.48043 13.8865 1.73478 14 2 14H10C10.2652 14 10.5196 13.8865 10.7071 13.6846C10.8946 13.4826 11 13.2087 11 12.9231V3.23077H11.5C11.6326 3.23077 11.7598 3.17404 11.8536 3.07306C11.9473 2.97208 12 2.83512 12 2.69231C12 2.5495 11.9473 2.41254 11.8536 2.31156C11.7598 2.21058 11.6326 2.15385 11.5 2.15385ZM4 1.61538C4 1.47258 4.05268 1.33562 4.14645 1.23463C4.24021 1.13365 4.36739 1.07692 4.5 1.07692H7.5C7.63261 1.07692 7.75979 1.13365 7.85355 1.23463C7.94732 1.33562 8 1.47258 8 1.61538V2.15385H4V1.61538ZM10 12.9231H2V3.23077H10V12.9231ZM5 5.92308V10.2308C5 10.3736 4.94732 10.5105 4.85355 10.6115C4.75979 10.7125 4.63261 10.7692 4.5 10.7692C4.36739 10.7692 4.24021 10.7125 4.14645 10.6115C4.05268 10.5105 4 10.3736 4 10.2308V5.92308C4 5.78027 4.05268 5.64331 4.14645 5.54233C4.24021 5.44135 4.36739 5.38462 4.5 5.38462C4.63261 5.38462 4.75979 5.44135 4.85355 5.54233C4.94732 5.64331 5 5.78027 5 5.92308ZM8 5.92308V10.2308C8 10.3736 7.94732 10.5105 7.85355 10.6115C7.75979 10.7125 7.63261 10.7692 7.5 10.7692C7.36739 10.7692 7.24021 10.7125 7.14645 10.6115C7.05268 10.5105 7 10.3736 7 10.2308V5.92308C7 5.78027 7.05268 5.64331 7.14645 5.54233C7.24021 5.44135 7.36739 5.38462 7.5 5.38462C7.63261 5.38462 7.75979 5.44135 7.85355 5.54233C7.94732 5.64331 8 5.78027 8 5.92308Z" fill="#323846"/></svg>';

							if ( 'style3' === $style ) {
								$remove_icon = '<svg width="10" height="10" viewBox="0 0 10 10" fill="none" xmlns="http://www.w3.org/2000/svg">
                                    <path fill-rule="evenodd" clip-rule="evenodd" d="M0.265878 0.277535C0.436171 0.107107 0.667106 0.0113653 0.9079 0.0113653C1.14869 0.0113653 1.37963 0.107107 1.54992 0.277535L4.99432 3.72572L8.43872 0.277535C8.52249 0.190707 8.6227 0.121451 8.73349 0.0738062C8.84428 0.0261617 8.96344 0.00108325 9.08401 3.43246e-05C9.20459 -0.0010146 9.32417 0.0219868 9.43577 0.0676966C9.54737 0.113407 9.64876 0.180909 9.73403 0.266266C9.81929 0.351623 9.88672 0.453125 9.93238 0.564849C9.97804 0.676573 10.001 0.796282 9.99997 0.91699C9.99892 1.0377 9.97387 1.15699 9.92627 1.2679C9.87868 1.37881 9.8095 1.47913 9.72277 1.56299L6.27837 5.01117L9.72277 8.45935C9.88819 8.63081 9.97972 8.86045 9.97765 9.09881C9.97558 9.33717 9.88008 9.56518 9.71171 9.73373C9.54334 9.90228 9.31558 9.99789 9.07748 9.99996C8.83938 10.002 8.60999 9.91041 8.43872 9.74481L4.99432 6.29662L1.54992 9.74481C1.37865 9.91041 1.14927 10.002 0.911168 9.99996C0.673069 9.99789 0.445308 9.90228 0.27694 9.73373C0.108572 9.56518 0.013068 9.33717 0.010999 9.09881C0.00892994 8.86045 0.100461 8.63081 0.265878 8.45935L3.71028 5.01117L0.265878 1.56299C0.0956364 1.39251 0 1.16132 0 0.920262C0 0.679204 0.0956364 0.448015 0.265878 0.277535Z" fill="black"/></svg>';
							}

							echo apply_filters( // phpcs:ignore WordPress.Security.EscapeOutput.OutputNotEscaped
								'woocommerce_cart_item_remove_link',
								sprintf(
									'<a href="%s" class="remove remove_from_cart_button" aria-label="%s" data-product_id="%s" data-cart_item_key="%s" data-product_sku="%s">%s<span class="rm-text">%s</span></a>',
									esc_url( wc_get_cart_remove_url( $cart_item_key ) ),
									esc_attr__( 'Remove this item', 'food-menu-pro' ),
									esc_attr( $product_id ),
									esc_attr( $cart_item_key ),
									esc_attr( $_product->get_sku() ),
									$remove_icon,
									esc_html__( 'Remove', 'food-menu-pro' )
								),
								$cart_item_key
							);
							?>
						</div>

					</div>
				</li>
				<?php
			}
		}
		do_action( 'woocommerce_mini_cart_contents' );
		?>
	</ul>
	<?php
else :
	?>
	<ul class='woocommerce-mini-cart cart_list product_list_widget empty-cart'>
		<?php
		$default_image_path = TLPFoodMenu()->assets_url() . 'images/empty-cart.jpg';

		if ( ! empty( $settings['mini_cart_empty_image'] ) ) {
			$loading_json    = json_decode( stripslashes( $settings['mini_cart_empty_image'] ), true );
			$loading_img_src = $loading_json['source'] ?? $default_image_path;
		} else {
			$loading_img_src = $default_image_path;
		}
		?>
		<li class="woocommerce-mini-cart__empty-message">
			<img class="empty-cart" width="100" height="100" src="<?php echo esc_url( $loading_img_src ); ?>" alt="<?php echo esc_attr__( 'Loading...', 'food-menu-pro' ); ?>">
			<?php if ( ! empty( $settings['mini_cart_empty_text'] ) ) : ?>
				<span class="fmp-empty-cart-text"><?php echo esc_html( $settings['mini_cart_empty_text'] ); ?></span>
			<?php endif; ?>
			<?php
			$shop_page_url = get_permalink( wc_get_page_id( 'shop' ) );
			if ( ! empty( $settings['mini_cart_go_shopping_btn_text'] ) ) {
				?>
				<a class="fmp-button"
				   href="<?php echo esc_url( $shop_page_url ); ?>"><?php echo esc_html( $settings['mini_cart_go_shopping_btn_text'] ); ?></a>
				<?php
			}
			?>
		</li>
	</ul>
	<?php
endif;
$extra_field_visibility = $settings['mini_cart_extra_field_visibility'] ?? 'on';
$coupon_visibility      = $settings['mini_cart_coupon_visibility'] ?? '';
?>

<!--Minicart footer -->
<div class="mini-cart-bottom">
	<?php if ( 'on' !== $coupon_visibility ) : ?>
		<div class="fmp-apply-coupon-toggle">
			<svg width="20" height="16" viewBox="0 0 20 16" fill="none" xmlns="http://www.w3.org/2000/svg">
				<path d="M17 0C17.7662 0.00024794 18.5032 0.293638 19.06 0.82C19.6164 1.34277 19.9526 2.05801 20 2.82V4.82C19.9994 5.09089 19.9268 5.35676 19.7898 5.59044C19.6527 5.82411 19.4561 6.01721 19.22 6.15H19.12C18.7938 6.30965 18.5173 6.55509 18.32 6.86C18.1262 7.15889 18.0157 7.50412 18 7.86C17.9915 8.20856 18.0742 8.55328 18.24 8.86C18.4123 9.179 18.6681 9.44515 18.98 9.63L19.12 9.71C19.3684 9.82705 19.5809 10.0084 19.7356 10.2352C19.8904 10.462 19.9816 10.7261 20 11V13C19.9998 13.7662 19.7064 14.5032 19.18 15.06C18.6572 15.6164 17.942 15.9526 17.18 16H3C2.23382 15.9998 1.49676 15.7064 0.94 15.18C0.382197 14.6593 0.0487029 13.9421 0.01 13.18V11.18C0.0116443 10.9105 0.0835289 10.6461 0.21856 10.4129C0.353592 10.1796 0.547103 9.98562 0.78 9.85H0.88C1.20618 9.69035 1.48274 9.44491 1.68 9.14C1.87382 8.84111 1.98429 8.49588 2 8.14C2.00853 7.79145 1.9258 7.44672 1.76 7.14C1.59612 6.78524 1.33191 6.48626 1 6.28L0.88 6.2C0.643879 6.09062 0.439326 5.92319 0.285438 5.71335C0.131551 5.5035 0.0333484 5.25808 0 5L0 3C0.00024794 2.23382 0.293638 1.49676 0.82 0.94C1.34071 0.382197 2.0579 0.0487029 2.82 0.01H17V0ZM17 2H3C2.75432 1.99823 2.51659 2.08695 2.33215 2.24926C2.14771 2.41157 2.02948 2.63609 2 2.88V4.53C2.59771 4.88892 3.09348 5.39501 3.44 6C3.78141 6.57342 3.97392 7.22315 4 7.89C4.0232 8.58533 3.86465 9.27468 3.54 9.89C3.22558 10.4976 2.75647 11.0116 2.18 11.38L2 11.47V13C1.99823 13.2457 2.08695 13.4834 2.24926 13.6679C2.41157 13.8523 2.63609 13.9705 2.88 14H17C17.2457 14.0018 17.4834 13.913 17.6679 13.7507C17.8523 13.5884 17.9705 13.3639 18 13.12V11.47C17.4084 11.125 16.9132 10.6367 16.56 10.05C16.2105 9.46186 16.0177 8.79392 16 8.11C15.9768 7.41467 16.1353 6.72532 16.46 6.11C16.7744 5.50238 17.2435 4.98843 17.82 4.62L18 4.53V3C18.0018 2.75432 17.913 2.51659 17.7507 2.33215C17.5884 2.14771 17.3639 2.02948 17.12 2H17ZM8 5C8.24568 4.99823 8.48341 5.08695 8.66785 5.24926C8.85229 5.41157 8.97052 5.63609 9 5.88V10C9.00167 10.2561 8.90503 10.5031 8.73 10.69C8.55051 10.8695 8.31304 10.9794 8.06 11C7.8059 11.0152 7.55555 10.933 7.36 10.77C7.16097 10.6076 7.03206 10.3749 7 10.12V6C7 5.73478 7.10536 5.48043 7.29289 5.29289C7.48043 5.10536 7.73478 5 8 5Z"
					  fill="#828282"/>
			</svg>
			<?php
			printf(
				'<a href="#">%s</a> %s',
				esc_html__( 'Click Here', 'food-menu-pro' ),
				esc_html__( 'Apply Coupon', 'food-menu-pro' ),
			)
			?>
		</div>
	<?php endif; ?>

	<div class="fmp-mini-coupon-form-main">
		<form id="fmp-mini-coupon-form">
			<input type="text" id="coupon-code" name="coupon_code" placeholder="Enter coupon code">
			<button type="submit"><?php echo esc_html__( 'Apply', 'food-menu-pro' ); ?></button>
		</form>
	</div>


	<?php
	$cart_total_class  = ( WC()->customer->has_calculated_shipping() ) ? 'calculated_shipping' : '';
	$cart_total_class .= $extra_field_visibility != 'on' ? ' hide-extra-field' : '';
	?>
	<div class="cart_totals <?php echo esc_attr( $cart_total_class ); ?>">
		<table cellspacing="0" class="shop_table shop_table_responsive">
			<tr class="cart-subtotal">
				<th><?php esc_html_e( 'Subtotal', 'woocommerce' ); ?></th>
				<td data-title="<?php esc_attr_e( 'Subtotal', 'woocommerce' ); ?>">
					<?php
					if ( 'on' === $extra_field_visibility ) {
						wc_cart_totals_subtotal_html();
					} else {
						Fns::print_html( wc_price( WC()->cart->get_cart_contents_total() ), true );
					}
					?>
				</td>
			</tr>

			<?php
			if ( 'on' === $extra_field_visibility ) {
				do_action( 'fmp_minicart_extra_fields' );
			}
			?>
		</table>
	</div>
	<p class="woocommerce-mini-cart__buttons buttons"><?php do_action( 'woocommerce_widget_shopping_cart_buttons' ); ?></p>
	<?php do_action( 'woocommerce_widget_shopping_cart_after_buttons' ); ?>

</div>
<?php do_action( 'woocommerce_after_mini_cart' ); ?>
