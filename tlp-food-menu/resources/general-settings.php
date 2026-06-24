<?php
/**
 * General settings page
 */

use RT\FoodMenu\Helpers\Fns;
use RT\FoodMenu\Helpers\Options;

// Do not allow directly accessing this file.
if ( ! defined( 'ABSPATH' ) ) {
	exit( 'This script cannot be accessed directly.' );
}

echo Fns::rtFieldGenerator( Options::generalSettings() ); //phpcs:ignore
echo Fns::rtFieldGenerator( Options::generalSettings2() ); //phpcs:ignore
?>

<div class="rt-field-wrapper" id="tlp-date-time-format">
	<div class="rt-label">
		<label><?php echo esc_html__( 'Date & Time Format', 'tlp-food-menu' ); ?></label>
	</div>
	<div class="rt-field">

		<p><?php echo esc_html__( 'Date format for delivery & pickup orders', 'tlp-food-menu' ); ?></p>
		
		<?php
		$timezone_str    = get_option( 'timezone_string' );
		$timezone_format = _x( TLP_FOOD_MENU_DEFAULT_DATE_FORMAT . ' ' . TLP_FOOD_MENU_DEFAULT_TIME_FORMAT, 'timezone date format' ); //phpcs:ignore
		?>

		<p>
			<?php esc_html_e( 'Selected current timezone is ', 'tlp-food-menu' ); ?>
			<code><?php echo esc_html( $timezone_str ); ?></code><?php echo '.'; ?>
			<?php esc_html_e( 'Universal time is ', 'tlp-food-menu' ); ?>
			<code><?php echo esc_html( date_i18n( $timezone_format, false, true ) ); ?></code>
		</p>

		<a href="<?php echo esc_url( admin_url( 'options-general.php#timezone_string' ) ); ?>" target="_blank" class="fm-btn-text">
			<?php esc_html_e( 'Update Date & Time Format', 'tlp-food-menu' ); ?>
		</a>

	</div>
</div>
