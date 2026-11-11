<?php
// phpcs:disable WordPress.NamingConventions.PrefixAllGlobals -- Established plugin: public namespace, hook names, functions and theme-overridable template variables must stay unchanged for backward compatibility.
/**
 * Price Label Metabox Class.
 *
 * @package RT_FoodMenu
 */

namespace RT\FoodMenu\Controllers\Admin\Metabox;

use RT\FoodMenu\Helpers\Fns;
use RT\FoodMenu\Traits\SingletonTrait;

// Do not allow directly accessing this file.
if ( ! defined( 'ABSPATH' ) ) {
	exit( 'This script cannot be accessed directly.' );
}

/**
 * Optional text shown instead of the price ("12.00-14.50", "Small 5.95 | Large 6.50").
 *
 * Its own box, so it shows with or without Pro (Pro replaces the free price metabox).
 */
class PriceLabel {
	use SingletonTrait;

	/**
	 * Nonce action.
	 */
	const NONCE = 'tlp_fm_price_label';

	/**
	 * Class Init.
	 *
	 * @return void
	 */
	protected function init() {
		add_action( 'add_meta_boxes', [ $this, 'add_meta_box' ] );
		add_action( 'save_post_' . TLPFoodMenu()->post_type, [ $this, 'save' ] );
	}

	/**
	 * Add Meta Box.
	 *
	 * @return void
	 */
	public function add_meta_box() {
		add_meta_box(
			'tlp_fm_price_label',
			esc_html__( 'Price Label', 'tlp-food-menu' ),
			[ $this, 'render' ],
			TLPFoodMenu()->post_type,
			'side'
		);
	}

	/**
	 * Meta Box Callback.
	 *
	 * @param \WP_Post $post Post.
	 * @return void
	 */
	public function render( $post ) {
		wp_nonce_field( self::NONCE, self::NONCE . '_nonce' );
		?>
		<p>
			<input type="text" class="widefat" name="<?php echo esc_attr( Fns::PRICE_LABEL_META ); ?>" value="<?php echo esc_attr( Fns::get_price_label( $post->ID ) ); ?>" placeholder="<?php esc_attr_e( 'e.g. Small 5.95 | Large 6.50', 'tlp-food-menu' ); ?>">
		</p>
		<p class="description">
			<?php esc_html_e( 'Shown on the menu instead of the price, exactly as typed. Leave blank to show the price.', 'tlp-food-menu' ); ?>
		</p>
		<?php
	}

	/**
	 * Save Meta Box.
	 *
	 * @param int $post_id Post ID.
	 * @return void
	 */
	public function save( $post_id ) {
		if ( defined( 'DOING_AUTOSAVE' ) && DOING_AUTOSAVE ) {
			return;
		}

		$nonce = isset( $_POST[ self::NONCE . '_nonce' ] ) ? sanitize_text_field( wp_unslash( $_POST[ self::NONCE . '_nonce' ] ) ) : '';

		if ( ! wp_verify_nonce( $nonce, self::NONCE ) || ! current_user_can( 'edit_post', $post_id ) ) {
			return;
		}

		$label = isset( $_POST[ Fns::PRICE_LABEL_META ] ) ? sanitize_text_field( wp_unslash( $_POST[ Fns::PRICE_LABEL_META ] ) ) : '';

		if ( '' === $label ) {
			delete_post_meta( $post_id, Fns::PRICE_LABEL_META );
		} else {
			update_post_meta( $post_id, Fns::PRICE_LABEL_META, $label );
		}
	}
}
