<?php
// phpcs:disable WordPress.NamingConventions.PrefixAllGlobals -- Established plugin: public namespace, hook names, functions and theme-overridable template variables must stay unchanged for backward compatibility.
if ( ! defined( 'ABSPATH' ) ) {
	exit;
}
$cancelFormShowHide = ! empty( $settings['fmp_resi_enable_cancel_form'] ) ? $settings['fmp_resi_enable_cancel_form'] : 'on';
?>

<div class="fmp-resi-container">

	<div class="fmp-resi-error-msg"></div>
	<div class="fmp-resi-success-msg"></div>

	<form id="fmp-resi-booking-form" method="post" action="" class="fmp-resi-form fmp-resi-booking-form">
		<input type="hidden" name="fmp_resi_action" value="fmp-reservation">
		<input type="hidden" name="fmp_location_id" id="fmp_resi_location_id" value="" />
		<div class='fmp-field'>
			<label for='fmp_resi_date'>
				<?php echo esc_html__( 'Date', 'tlp-food-menu' ); ?>
				<span class='fmp-required'>*</span>
			</label>
			<input type='text' class='fmp-form-control' id='fmp_resi_date' name='fmp_resi_date' value='' placeholder="<?php echo esc_attr__( 'Booking date', 'tlp-food-menu' ); ?>" required/>
		</div>

		<div class="fmp-row">
			<div class="fmp-col-lg-6 fmp-col-md-6 fmp-col-sm-6 fmp-col-xs-12">
				<div class='fmp-field'>
					<label for='fmp_resi_start_time'>
						<?php echo esc_html__( 'Start Time', 'tlp-food-menu' ); ?>
						<span class='fmp-required'>*</span>
					</label>
					<input type='text' class='fmp-form-control' id='fmp_resi_start_time' name='fmp_resi_start_time' placeholder="<?php echo esc_attr__( 'Start Time', 'tlp-food-menu' ); ?>"
							value='' required/>
				</div>
			</div>
			<div class="fmp-col-lg-6 fmp-col-md-6 fmp-col-sm-6 fmp-col-xs-12">
				<div class='fmp-field'>
					<label for='fmp_resi_end_time'>
						<?php echo esc_html__( 'End Time', 'tlp-food-menu' ); ?>
						<span class='fmp-required'>*</span>
					</label>
					<input type='text' class='fmp-form-control' id='fmp_resi_end_time' name='fmp_resi_end_time' value='' placeholder="<?php echo esc_attr__( 'End Time', 'tlp-food-menu' ); ?>"
							required/>
				</div>
			</div>
		</div>

		<div class='fmp-field'>
			<label for='fmp_resi_guest'>
				<?php echo esc_html__( 'Guest Number', 'tlp-food-menu' ); ?>
				<span class='fmp-required'>*</span>
			</label>

			<select name='fmp_resi_guest' id='fmp_resi_guest' class='fmp-form-control' required>
				<option value="" disabled selected hidden><?php echo esc_html__( 'Select total guests', 'tlp-food-menu' ); ?></option>
				<?php
				$guest_count = \RT\FoodMenu\Helpers\Fns::get_guest_limit();
				foreach ( $guest_count as $i ) {
					?>
					<option value='<?php echo esc_attr( $i ); ?>'>
						<?php echo esc_html( $i ); ?>
					</option>
				<?php } ?>
			</select>
		</div>

		<div class='fmp-field'>
			<label for='fmp_resi_name'>
				<?php echo esc_html__( 'Name', 'tlp-food-menu' ); ?>
				<span class='fmp-required'>*</span>
			</label>
			<input type='text' class='fmp-form-control' id='fmp_resi_name' name='fmp_resi_name' value='' placeholder="<?php echo esc_attr__( 'Enter your name', 'tlp-food-menu' ); ?>" required/>
		</div>


		<div class='fmp-field'>
			<label for='fmp_resi_email'>
				<?php echo esc_html__( 'Email', 'tlp-food-menu' ); ?>
				<span class='fmp-required'>*</span>
			</label>
			<input type='email' class='fmp-form-control' id='fmp_resi_email' name='fmp_resi_email' value='' placeholder="<?php echo esc_attr__( 'Enter your email', 'tlp-food-menu' ); ?>" required/>
		</div>

		<div class='fmp-field'>
			<label for='fmp_resi_phone'>
				<?php echo esc_html__( 'Phone', 'tlp-food-menu' ); ?>
				<span class='fmp-required'>*</span>
			</label>
			<input type='tel' class='fmp-form-control' id='fmp_resi_phone' name='fmp_resi_phone' value='' inputmode='tel' autocomplete='tel' maxlength='20' placeholder="<?php echo esc_attr__( 'Enter your phone', 'tlp-food-menu' ); ?>" required/>
		</div>

		<div class='fmp-field'>
			<label for='fmp_resi_message'>
				<?php echo esc_html__( 'Message', 'tlp-food-menu' ); ?>
			</label>
			<textarea class='fmp-form-control' id='fmp_resi_message' name='fmp_resi_message' placeholder="<?php echo esc_attr__( 'Enter your message', 'tlp-food-menu' ); ?>"></textarea>
		</div>

		<div class="fmp-field fmp-resi-submit-btn-wrap">
			<button type="submit" class="fmp-resi-submit-btn" data-form-type="booking-form"><?php echo esc_html__( 'Request Booking', 'tlp-food-menu' ); ?></button>

			<?php if ( ! empty( $cancelFormShowHide ) ) : ?>
				<span class="fmp-resi-request-cancel"><?php echo esc_html__( 'Request Cancel', 'tlp-food-menu' ); ?></span>
			<?php endif; ?>
		</div>
	</form>

</div>
