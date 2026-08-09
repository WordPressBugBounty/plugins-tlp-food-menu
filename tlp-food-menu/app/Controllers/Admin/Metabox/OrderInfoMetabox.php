<?php
// phpcs:disable WordPress.NamingConventions.PrefixAllGlobals -- Established plugin: public namespace, hook names, functions and theme-overridable template variables must stay unchanged for backward compatibility.
/**
 * Order Information Metabox (Free).
 *
 * Slim, location-focused panel on the WooCommerce admin order edit page.
 * Pro replaces this with its richer version that also surfaces pickup/delivery,
 * tip, custom fields, etc. (see Pro `OrderInfoMetabox`).
 *
 * @package RT_FoodMenu
 */

namespace RT\FoodMenu\Controllers\Admin\Metabox;

defined( 'ABSPATH' ) || exit;

use RT\FoodMenu\Traits\SingletonTrait;

/**
 * Order Information Metabox.
 */
class OrderInfoMetabox {

	use SingletonTrait;

	/**
	 * Init.
	 *
	 * @return void
	 */
	protected function init() {
		add_action( 'add_meta_boxes', [ $this, 'register_metabox' ], 10, 2 );
		// Save handler — fires for both HPOS and legacy CPT order saves.
		add_action( 'woocommerce_process_shop_order_meta', [ $this, 'save' ], 30, 2 );
	}

	/**
	 * Register the metabox on the WC order edit screen (HPOS + legacy CPT).
	 *
	 * @param string                  $post_type     Screen/post type.
	 * @param \WP_Post|\WC_Order|null $post_or_order Post (legacy) or order (HPOS).
	 *
	 * @return void
	 */
	public function register_metabox( $post_type, $post_or_order ) {
		$screens = [ 'shop_order' ];
		if ( function_exists( 'wc_get_page_screen_id' ) ) {
			$hpos = wc_get_page_screen_id( 'shop-order' );
			if ( $hpos ) {
				$screens[] = $hpos;
			}
		}

		if ( ! in_array( $post_type, $screens, true ) ) {
			return;
		}

		$order = $post_or_order instanceof \WC_Order ? $post_or_order : wc_get_order( $post_or_order );
		if ( ! $order ) {
			return;
		}

		add_meta_box(
			'fmp_order_info_metabox',
			esc_html__( 'Food Menu - Order Information', 'tlp-food-menu' ),
			[ $this, 'render' ],
			$post_type,
			'normal',
			'high'
		);
	}

	/**
	 * Render the metabox.
	 *
	 * @param \WP_Post|\WC_Order $post_or_order Post (legacy) or order (HPOS).
	 *
	 * @return void
	 */
	public function render( $post_or_order ) {
		$order = $post_or_order instanceof \WC_Order ? $post_or_order : wc_get_order( $post_or_order );
		if ( ! $order ) {
			return;
		}

		$location          = $order->get_meta( 'fmp_location_name' );
		$selected_location = (int) $order->get_meta( 'fmp_location_id' );

		$terms = taxonomy_exists( 'tpl-food-location' )
			? get_terms( [
				'taxonomy'   => 'tpl-food-location',
				'hide_empty' => false,
				'parent'     => 0,
			] )
			: [];
		$terms = ( is_wp_error( $terms ) || ! is_array( $terms ) ) ? [] : $terms;

		$input_style = 'width:100%;box-sizing:border-box;padding:8px 10px;border:1px solid #e5e7eb;border-radius:6px;background:#fff;font-size:14px;color:#111827;line-height:1.4;outline:none;';
		$label_style = 'font-size:11px;color:#6b7280;text-transform:uppercase;letter-spacing:0.04em;font-weight:600;margin-bottom:6px;display:block;';
		?>
		<div class="fmp-order-info" style="font-size:13px;color:#111827;line-height:1.5;padding-top:6px;">
			<?php wp_nonce_field( 'fmp_order_info_save', 'fmp_order_info_nonce' ); ?>

			<?php if ( $location ) : ?>
				<div style="margin-bottom:14px;">
					<div style="<?php echo esc_attr( $label_style ); ?>"><?php esc_html_e( 'Location', 'tlp-food-menu' ); ?></div>
					<span style="display:inline-flex;align-items:center;gap:6px;padding:6px 12px;background:#fff7ed;color:#c2410c;border:1px solid #fed7aa;border-radius:6px;font-weight:600;">
						<span style="width:6px;height:6px;border-radius:50%;background:#ea580c;display:inline-block;"></span>
						<?php echo esc_html( $location ); ?>
					</span>
				</div>
			<?php endif; ?>

			<div>
				<label for="fmp_location_id" style="<?php echo esc_attr( $label_style ); ?>">
					<?php echo $location ? esc_html__( 'Update Location', 'tlp-food-menu' ) : esc_html__( 'Location', 'tlp-food-menu' ); ?>
				</label>
				<?php if ( ! empty( $terms ) ) : ?>
					<select id="fmp_location_id" name="fmp_location_id" style="<?php echo esc_attr( $input_style ); ?>">
						<option value=""><?php esc_html_e( '— None —', 'tlp-food-menu' ); ?></option>
						<?php
						foreach ( $terms as $parent_term ) {
							$children = get_term_children( $parent_term->term_id, 'tpl-food-location' );
							if ( ! empty( $children ) && ! is_wp_error( $children ) ) {
								echo "<optgroup label='" . esc_attr( $parent_term->name ) . "'>";
								foreach ( $children as $child_id ) {
									$child = get_term( $child_id, 'tpl-food-location' );
									if ( $child && ! is_wp_error( $child ) ) {
										printf(
											'<option value="%1$d"%2$s>%3$s</option>',
											(int) $child->term_id,
											selected( $selected_location, (int) $child->term_id, false ),
											esc_html( $child->name )
										);
									}
								}
								echo '</optgroup>';
							} else {
								printf(
									'<option value="%1$d"%2$s>%3$s</option>',
									(int) $parent_term->term_id,
									selected( $selected_location, (int) $parent_term->term_id, false ),
									esc_html( $parent_term->name )
								);
							}
						}
						?>
					</select>
				<?php else : ?>
					<p style="margin:0;padding:10px 12px;background:#fef3c7;border:1px solid #fde68a;border-radius:6px;font-size:12px;color:#92400e;line-height:1.4;">
						<?php
						printf(
							/* translators: %s: link to the Food Locations taxonomy edit screen. */
							wp_kses_post( __( 'No locations found. <a href="%s">Add a location</a> first.', 'tlp-food-menu' ) ),
							esc_url( admin_url( 'edit-tags.php?taxonomy=tpl-food-location&post_type=product' ) )
						);
						?>
					</p>
				<?php endif; ?>
				<p style="margin:8px 0 0;font-size:12px;color:#9ca3af;line-height:1.4;">
					<?php esc_html_e( 'Changes are saved when you click the main "Update" button.', 'tlp-food-menu' ); ?>
				</p>
			</div>
		</div>
		<?php
	}

	/**
	 * Save handler — runs on both HPOS and legacy CPT order saves.
	 *
	 * @param int                $order_id Order ID.
	 * @param \WC_Order|\WP_Post $order    Order (HPOS) or post (legacy).
	 *
	 * @return void
	 */
	public function save( $order_id, $order ) {
		// phpcs:ignore WordPress.Security.NonceVerification.Missing
		if ( empty( $_POST['fmp_order_info_nonce'] ) || ! wp_verify_nonce( sanitize_key( wp_unslash( $_POST['fmp_order_info_nonce'] ) ), 'fmp_order_info_save' ) ) {
			return;
		}

		if ( ! current_user_can( 'edit_shop_orders' ) ) {
			return;
		}

		$wc_order = $order instanceof \WC_Order ? $order : wc_get_order( $order_id );
		if ( ! $wc_order ) {
			return;
		}

		// phpcs:ignore WordPress.Security.NonceVerification.Missing
		$location_id = isset( $_POST['fmp_location_id'] ) ? absint( $_POST['fmp_location_id'] ) : 0;

		$location_name = '';
		if ( $location_id && taxonomy_exists( 'tpl-food-location' ) ) {
			$term = get_term( $location_id, 'tpl-food-location' );
			if ( $term && ! is_wp_error( $term ) ) {
				$location_name = $term->name;
			} else {
				$location_id = 0; // Term no longer exists — clear.
			}
		}

		$wc_order->update_meta_data( 'fmp_location_id', $location_id );
		$wc_order->update_meta_data( 'fmp_location_name', $location_name );
		$wc_order->save();
	}
}
