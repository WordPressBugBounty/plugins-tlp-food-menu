<?php
if ( ! defined( 'ABSPATH' ) ) {
	exit;
}
?>
<div class="fmp-resi-cancel-modal" role="dialog" aria-modal="true" aria-hidden="true" aria-labelledby="fmp-resi-cancel-modal-title">
	<div class="fmp-resi-cancel-modal-dialog" role="document">
		<div class="fmp-resi-cancel-modal-header">
			<div class="fmp-resi-cancel-modal-heading">
				<span class="fmp-resi-cancel-modal-icon" aria-hidden="true">
					<svg width="22" height="22" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.2" stroke-linecap="round" stroke-linejoin="round">
						<circle cx="12" cy="12" r="10"/>
						<line x1="15" y1="9" x2="9" y2="15"/>
						<line x1="9" y1="9" x2="15" y2="15"/>
					</svg>
				</span>
				<div>
					<h3 id="fmp-resi-cancel-modal-title" class="fmp-resi-cancel-modal-title"><?php echo esc_html__( 'Cancel Reservation', 'tlp-food-menu' ); ?></h3>
					<p class="fmp-resi-cancel-modal-subtitle"><?php echo esc_html__( 'Enter your booking details to request a cancellation.', 'tlp-food-menu' ); ?></p>
				</div>
			</div>
			<button type="button" class="fmp-resi-cancel-modal-close" aria-label="<?php echo esc_attr__( 'Close', 'tlp-food-menu' ); ?>">&times;</button>
		</div>
		<div class="fmp-resi-cancel-modal-body">
			<div class="fmp-resi-container">

				<form id="fmp-resi-cancel-form" method="post" class="fmp-resi-form fmp-resi-cancel-form">
					<input type="hidden" name="fmp_resi_action" value="fmp-reservation-cancel">
					<div class='fmp-field'>
						<label for='fmp_resi_cancel_booking_id'>
							<?php echo esc_html__( 'Booking ID', 'tlp-food-menu' ); ?>
							<span class='fmp-required'>*</span>
						</label>
						<input type='text' class='fmp-form-control' id='fmp_resi_cancel_booking_id' name='fmp_resi_cancel_booking_id'
								value='' placeholder="<?php echo esc_attr__( 'Enter booking ID', 'tlp-food-menu' ); ?>" required/>
					</div>

					<div class='fmp-field'>
						<label for='fmp_resi_cancel_email'>
							<?php echo esc_html__( 'Email', 'tlp-food-menu' ); ?>
							<span class='fmp-required'>*</span>
						</label>
						<input type='text' class='fmp-form-control' id='fmp_resi_cancel_email' name='fmp_resi_cancel_email'
								value='' placeholder="<?php echo esc_attr__( 'Enter email', 'tlp-food-menu' ); ?>" required/>
					</div>

					<div class='fmp-field'>
						<label for='fmp_resi_cancel_phone'>
							<?php echo esc_html__( 'Phone', 'tlp-food-menu' ); ?>
							<span class='fmp-required'>*</span>
						</label>
						<input type='tel' class='fmp-form-control' id='fmp_resi_cancel_phone' name='fmp_resi_cancel_phone'
								value='' inputmode='tel' autocomplete='tel' maxlength='20' placeholder="<?php echo esc_attr__( 'Enter phone', 'tlp-food-menu' ); ?>" required/>
					</div>

					<div class='fmp-field'>
						<label for='fmp_resi_cancel_message'>
							<?php echo esc_html__( 'Message', 'tlp-food-menu' ); ?>
						</label>
						<textarea class='fmp-form-control' id='fmp_resi_cancel_message' name='fmp_resi_cancel_message' placeholder="<?php echo esc_attr__( 'Enter the reason', 'tlp-food-menu' ); ?>"></textarea>
					</div>

					<div class="fmp-field fmp-resi-cansel-submit-btn-wrap">
						<button type="submit" class="fmp-resi-cancel-submit-btn" data-form-type="cancel-form"><?php echo esc_html__( 'Request Cancel', 'tlp-food-menu' ); ?></button>
						<span class="fmp-resi-request-booking"><?php echo esc_html__( 'Cancel', 'tlp-food-menu' ); ?></span>
					</div>
				</form>

			</div>
		</div>
	</div>
</div>
