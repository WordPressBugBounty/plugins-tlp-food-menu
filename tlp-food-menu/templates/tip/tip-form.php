<?php
/**
 * Tip form for frontend in cart and order page.
 */

defined( 'ABSPATH' ) || exit;

$settings = get_option( TLPFoodMenu()->options['settings'] );

$tip_types        = [];
$allow_tip_for    = $settings['allow_tip_for'] ?? 'both';
$tip_heading_text = $settings['tip_heading_text'] ?? esc_html__( 'Do you want to provide a tip?', 'tlp-food-menu' );

$tip_selected_type = ( 'percentage' === $allow_tip_for ) ? 'percentage' : 'fixed';

if ( 'fixed' === $allow_tip_for || 'both' === $allow_tip_for ) {
	$tip_types['fixed'] = esc_html__( 'Fixed', 'tlp-food-menu' );
}

if ( 'percentage' === $allow_tip_for || 'both' === $allow_tip_for ) {
	$tip_types['percentage'] = esc_html__( 'Percentage(%)', 'tlp-food-menu' );
}

$tip_fixed_amount      = 0;
$tip_percentage_amount = 0;
$tip_added             = 0;
$tip_session_data      = WC()->session->get( 'fmp_tip' );

if ( ! empty( $tip_session_data ) ) {
	$tip_added             = $tip_session_data['tip_added'];
	$tip_selected_type     = $tip_session_data['tip_selected_type'];
	$tip_fixed_amount      = $tip_session_data['tip_fixed_amount'];
	$tip_percentage_amount = $tip_session_data['tip_percentage_amount'];
}
?>

<div class="fmp-tip-container" id="fmp-tip-container-id">

	<div class="fmp-tip-title">
		<h3><?php echo esc_html( $tip_heading_text ); ?></h3>
	</div>

	<div class="fmp-tip-wrapper" id="fmp-tip-wrapper">
		<div class="fmp-tip-input-field-wrap">
			<div class="fmp-tip-type-wrap">
				<select name="fmp_tip_type" class="fmp-tip-type fmp-input-control">
					<?php foreach ( $tip_types as $type_key => $type_name ) : ?>
						<option value='<?php echo esc_attr( $type_key ); ?>' <?php selected( $tip_selected_type, $type_key, true ); ?>><?php echo esc_html( $type_name ); ?></option>
					<?php endforeach; ?>
				</select>
			</div>

			<?php if ( 'both' === $allow_tip_for || 'fixed' === $allow_tip_for ) : ?>
				<div class="fmp-fixed-field-wrap" style="<?php echo ( 'fixed' !== $tip_selected_type ) ? 'display: none;' : ''; ?>">
					<input type="number" name="fmp_fixed_amount" min="0" value="<?php echo esc_attr( $tip_fixed_amount ); ?>" class="fmp-fixed-amount fmp-input-control" />
				</div>
			<?php endif; ?>

			<?php if ( 'both' === $allow_tip_for || 'percentage' === $allow_tip_for ) : ?>
				<div class="fmp-percentage-field-wrap" style="<?php echo ( 'percentage' !== $tip_selected_type ) ? 'display: none;' : ''; ?>">
					<input type="number" name="fmp_percentage_amount" min="0" value="<?php echo esc_attr( $tip_percentage_amount ); ?>" class="fmp-percentage-amount fmp-input-control" />
				</div>
			<?php endif; ?>
		</div>

		<div class="fmp-tip-button-wrap">
			<input type="button" name="fmp_add_tip" class="fmp-btn fmp-add-tip <?php echo ( 0 === $tip_added ) ? 'fmp-add-tip-disabled' : ''; ?>" <?php echo ( 0 === $tip_added ) ? 'disabled' : ''; ?> value="<?php echo esc_html__( 'Add Tip', 'tlp-food-menu' ); ?>" />
			<input type="button" name="fmp_remove_tip" class="fmp-btn fmp-remove-tip" style="<?php echo ( ! $tip_session_data ) ? 'display: none;' : ''; ?>" value="<?php echo esc_html__( 'Remove Tip', 'tlp-food-menu' ); ?>" />
		</div>

		<div class="fmp-tip-msg-wrap">
			<span class="fmp-tip-msg"></span>
		</div>

	</div>
</div>
