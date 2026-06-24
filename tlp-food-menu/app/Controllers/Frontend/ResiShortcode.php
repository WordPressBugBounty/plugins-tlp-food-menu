<?php

namespace RT\FoodMenu\Controllers\Frontend;

defined( 'ABSPATH' ) || exit();

use RT\FoodMenu\Helpers\Fns;
use RT\FoodMenu\Traits\SingletonTrait;

/**
 * Reservation form shortcode.
 *
 * Pro registers the visual-table variant (`fm_visual_table_reservation_form`)
 * separately. This shortcode handles only the normal reservation form.
 */
class ResiShortcode {

	use SingletonTrait;

	/**
	 * Cached settings.
	 *
	 * @var array
	 */
	private $options;

	/**
	 * Class init.
	 *
	 * @return void
	 */
	protected function init() {
		$this->options = Fns::get_settings_option();
		if ( ! is_array( $this->options ) ) {
			$this->options = [];
		}

		add_shortcode( 'fm_reservation_form', [ $this, 'render' ] );
	}

	/**
	 * Render normal reservation form.
	 *
	 * @param array $atts Shortcode attributes.
	 *
	 * @return false|string
	 */
	public function render( $atts ) {
		$settings = $this->options;

		if ( 'on' !== ( $settings['fmp_food_reservation_status'] ?? '' ) ) {
			return $this->disabled_notice();
		}

		ob_start();
		$enable_cancel_form = ! empty( $settings['fmp_resi_enable_cancel_form'] ) ? $settings['fmp_resi_enable_cancel_form'] : 'on';
		$attrs_data         = apply_filters( 'fmp/resi/reservation_form_atts', $atts );
		$data               = [
			'settings'   => $settings,
			'attrs_data' => $attrs_data,
		];
		?>
		<div class="fmp-reservation-form-wrap">
			<?php
			Fns::render( 'reservation/reservation-form', $data );
			if ( ! empty( $enable_cancel_form ) ) {
				Fns::render( 'reservation/cancel-form', $data );
			}
			?>
		</div>
		<?php
		return ob_get_clean();
	}

	/**
	 * Shown when reservation is disabled in settings.
	 *
	 * @return string
	 */
	private function disabled_notice() {
		$is_admin = is_user_logged_in() && current_user_can( 'manage_options' );

		ob_start();
		?>
		<div class="fmp-reservation-form-wrap">
			<div class="fmp-resi-empty-state" style="display:flex;flex-direction:column;align-items:center;justify-content:center;padding:48px 24px;text-align:center;">
				<svg xmlns="http://www.w3.org/2000/svg" width="48" height="48" viewBox="0 0 24 24" fill="none" stroke="#9ca3af" stroke-width="1.5" stroke-linecap="round" stroke-linejoin="round" style="margin-bottom:16px;">
					<circle cx="12" cy="12" r="10"/>
					<line x1="4.93" y1="4.93" x2="19.07" y2="19.07"/>
				</svg>
				<p style="margin:0 0 8px;font-size:16px;font-weight:600;color:#1f2937;">
					<?php echo esc_html__( 'Reservations are currently unavailable', 'tlp-food-menu' ); ?>
				</p>
				<?php if ( $is_admin ) : ?>
					<p style="margin:0 0 20px;font-size:14px;color:#6b7280;max-width:460px;">
						<?php echo esc_html__( 'The reservation feature is disabled in your settings. Only site administrators can see this message.', 'tlp-food-menu' ); ?>
					</p>
					<a href="<?php echo esc_url( admin_url( 'edit.php?post_type=food-menu&page=fmp_settings#/reservation' ) ); ?>" style="display:inline-flex;align-items:center;gap:6px;padding:10px 20px;background:var(--rtfm-primary-color,#fc202e);color:#fff;border-radius:8px;font-size:14px;font-weight:500;text-decoration:none;">
						<?php echo esc_html__( 'Open Reservation Settings', 'tlp-food-menu' ); ?>
					</a>
				<?php else : ?>
					<p style="margin:0;font-size:14px;color:#6b7280;max-width:460px;">
						<?php echo esc_html__( 'Please contact us directly to book a table.', 'tlp-food-menu' ); ?>
					</p>
				<?php endif; ?>
			</div>
		</div>
		<?php
		return ob_get_clean();
	}
}
