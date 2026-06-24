<?php

namespace RT\FoodMenu\Controllers\Reservation;

defined( 'ABSPATH' ) || exit();

use RT\FoodMenu\Helpers\Fns;

/**
 * Reservation settings field definitions.
 */
class ReservationFns {

	/**
	 * All reservation settings fields.
	 *
	 * @return array
	 */
	public static function settings_fields() {
		return array_merge(
			self::general_settings_fields(),
			self::schedule_settings_fields(),
			self::email_settings_fields()
		);
	}

	/**
	 * General settings.
	 *
	 * @return array
	 */
	public static function general_settings_fields() {
		$settings = Fns::get_settings_option();
		if ( ! is_array( $settings ) ) {
			$settings = [];
		}

		return apply_filters(
			'fmp/reservation_general_settings/fields',
			[
				'fmp_resi_enable_cancel_form' => [
					'label'       => esc_html__( 'Enable Cancelled Form?', 'tlp-food-menu' ),
					'type'        => 'switch',
					'description' => esc_html__( 'Allow users to easily cancel their reservations through a dedicated cancellation form.', 'tlp-food-menu' ),
					'value'       => ! empty( $settings['fmp_resi_enable_cancel_form'] ) ? $settings['fmp_resi_enable_cancel_form'] : 'on',
				],
			]
		);
	}

	/**
	 * Schedule settings.
	 *
	 * @return array
	 */
	public static function schedule_settings_fields() {
		$settings = Fns::get_settings_option();
		if ( ! is_array( $settings ) ) {
			$settings = [];
		}

		// Pro adds the visual-table line to the shortcode hint when present.
		$shortcode_hint = "In Reservation page, use <input style='width:auto;display: inline;text-align: center' type='text' value='[fm_reservation_form]' onclick='copyText(this)' readonly> shortcode for normal reservation.";
		$shortcode_hint = apply_filters( 'fmp/reservation/shortcode_hint', $shortcode_hint, $settings );

		return apply_filters(
			'fmp/reservation_schedule_settings/fields',
			[
				'fmp_resi_information' => [
					'type'        => 'html',
					'description' => $shortcode_hint,
				],

				'fmp_resi_seat_capacity' => [
					'label'       => esc_html__( 'Seat Capacity', 'tlp-food-menu' ),
					'type'        => 'number',
					'description' => esc_html__( 'Enter total seat capacity', 'tlp-food-menu' ),
					'value'       => ! empty( $settings['fmp_resi_seat_capacity'] ) ? $settings['fmp_resi_seat_capacity'] : 30,
				],

				'fmp_resi_min_guest' => [
					'label'       => esc_html__( 'Min Guest', 'tlp-food-menu' ),
					'type'        => 'number',
					'description' => esc_html__( 'Enter minimum guests number for reservation.', 'tlp-food-menu' ),
					'value'       => ! empty( $settings['fmp_resi_min_guest'] ) ? $settings['fmp_resi_min_guest'] : 1,
				],

				'fmp_resi_max_guest' => [
					'label'       => esc_html__( 'Max Guest', 'tlp-food-menu' ),
					'type'        => 'number',
					'description' => esc_html__( 'Enter maximum guests number for reservation', 'tlp-food-menu' ),
					'value'       => ! empty( $settings['fmp_resi_max_guest'] ) ? $settings['fmp_resi_max_guest'] : 30,
				],

				'fmp_resi_weekly_schedule' => [
					'id'       => 'fmp_resi_weekly_schedule',
					'type'     => 'array',
					'multiple' => true,
				],

				'fmp_resi_weekly_schedule_start_time' => [
					'id'       => 'fmp_resi_weekly_schedule_start_time',
					'type'     => 'array',
					'multiple' => true,
				],

				'fmp_resi_weekly_schedule_end_time' => [
					'id'       => 'fmp_resi_weekly_schedule_end_time',
					'type'     => 'array',
					'multiple' => true,
				],

				'fmp_resi_weekly_holiday' => [
					'id'       => 'fmp_resi_weekly_holiday',
					'type'     => 'array',
					'multiple' => true,
				],

				'fmp_resi_time_interval' => [
					'id'          => 'fmp_resi_time_interval',
					'type'        => 'number',
					'value'       => ! empty( $settings['fmp_resi_time_interval'] ) ? $settings['fmp_resi_time_interval'] : 30,
					'label'       => esc_html__( 'Time Interval', 'tlp-food-menu' ),
					'description' => esc_html__( 'Enter flexible time intervals to dynamically generate time slots in the reservation form.', 'tlp-food-menu' ),
				],

				'fmp_resi_default_status' => [
					'label'       => esc_html__( 'Default Reservation Status', 'tlp-food-menu' ),
					'type'        => 'select',
					'description' => esc_html__( 'Choose default reservation status.', 'tlp-food-menu' ),
					'value'       => ! empty( $settings['fmp_resi_default_status'] ) ? $settings['fmp_resi_default_status'] : 'confirmed',
					'options'     => Fns::get_reservation_status(),
					'default'     => 'confirmed',
					'class'       => 'fmp-select2',
				],
			]
		);
	}

	/**
	 * Email settings.
	 *
	 * @return array
	 */
	public static function email_settings_fields() {
		$settings = Fns::get_settings_option();
		if ( ! is_array( $settings ) ) {
			$settings = [];
		}

		return apply_filters(
			'fmp/reservation_email_settings/fields',
			[
				'fmp_resi_sender_email' => [
					'label'       => esc_html__( 'Sender Email Address', 'tlp-food-menu' ),
					'type'        => 'text',
					'description' => esc_html__( 'Emails will be sent to both the admin and the user from the following email address.', 'tlp-food-menu' ),
					'value'       => $settings['fmp_resi_sender_email'] ?? get_bloginfo( 'admin_email' ),
				],

				'fmp_resi_receive_email' => [
					'label'       => esc_html__( 'Admin Receive Email', 'tlp-food-menu' ),
					'type'        => 'text',
					'description' => esc_html__( "If 'Sender Email Address' is not set, both admin and user emails will be sent to this address.", 'tlp-food-menu' ),
					'value'       => $settings['fmp_resi_receive_email'] ?? '',
				],
				'fmp_resi_user_pending_notify' => [
					'label'       => esc_html__( 'User Reservation Pending Notification?', 'tlp-food-menu' ),
					'type'        => 'switch',
					'description' => esc_html__( 'Notify user upon new reservation.', 'tlp-food-menu' ),
					'value'       => $settings['fmp_resi_user_pending_notify'] ?? 'on',
				],
				'fmp_resi_admin_pending_notify' => [
					'label'       => esc_html__( 'Admin Reservation Pending Notification?', 'tlp-food-menu' ),
					'type'        => 'switch',
					'description' => esc_html__( 'Notify admin upon new reservation.', 'tlp-food-menu' ),
					'value'       => $settings['fmp_resi_pending_notify'] ?? 'on',
				],

				'fmp_resi_user_confirm_notify' => [
					'label'       => esc_html__( 'User Reservation Confirm Notification?', 'tlp-food-menu' ),
					'type'        => 'switch',
					'description' => esc_html__( 'Notify user upon confirm reservation.', 'tlp-food-menu' ),
					'value'       => $settings['fmp_resi_user_confirm_notify'] ?? 'on',
				],
				'fmp_resi_admin_confirm_notify' => [
					'label'       => esc_html__( 'Admin Reservation Confirm Notification?', 'tlp-food-menu' ),
					'type'        => 'switch',
					'description' => esc_html__( 'Notify admin upon confirm reservation.', 'tlp-food-menu' ),
					'value'       => $settings['fmp_resi_confirm_notify'] ?? 'on',
				],

				'fmp_resi_user_cancel_notify' => [
					'label'       => esc_html__( 'User Reservation Cancelled Notification?', 'tlp-food-menu' ),
					'type'        => 'switch',
					'description' => esc_html__( 'Notify user upon cancelled reservation.', 'tlp-food-menu' ),
					'value'       => $settings['fmp_resi_user_cancel_notify'] ?? 'on',
				],
				'fmp_resi_admin_cancel_notify' => [
					'label'       => esc_html__( 'Admin Reservation Cancelled Notification?', 'tlp-food-menu' ),
					'type'        => 'switch',
					'description' => esc_html__( 'Notify admin upon cancelled reservation.', 'tlp-food-menu' ),
					'value'       => $settings['fmp_resi_cancel_notify'] ?? 'on',
				],
				'fmp_resi_pending_msg' => [
					'label'       => esc_html__( 'Pending Message Text', 'tlp-food-menu' ),
					'type'        => 'text',
					'description' => esc_html__( 'Reservation pending message text', 'tlp-food-menu' ),
					'value'       => ! empty( $settings['fmp_resi_pending_msg'] ) ? $settings['fmp_resi_pending_msg'] : Fns::get_reservation_message( [], 'pending' ),
				],
				'fmp_resi_confirm_msg' => [
					'label'       => esc_html__( 'Confirmed Message Text', 'tlp-food-menu' ),
					'type'        => 'text',
					'description' => esc_html__( 'Reservation confirmation message text', 'tlp-food-menu' ),
					'value'       => ! empty( $settings['fmp_resi_confirm_msg'] ) ? $settings['fmp_resi_confirm_msg'] : Fns::get_reservation_message( [], 'confirm' ),
				],
				'fmp_resi_cancel_msg' => [
					'label'       => esc_html__( 'Cancelled Message Text', 'tlp-food-menu' ),
					'type'        => 'text',
					'description' => esc_html__( 'Reservation cancelled message text', 'tlp-food-menu' ),
					'value'       => ! empty( $settings['fmp_resi_cancel_msg'] ) ? $settings['fmp_resi_cancel_msg'] : Fns::get_reservation_message( [], 'cancel' ),
				],
			]
		);
	}

	/**
	 * Generate time-interval options for selects.
	 *
	 * @return array
	 */
	public static function generateIntervalArray() {
		$intervals     = apply_filters( 'fmp_reservation_time_interval', [ 10, 15, 20, 25, 30, 35, 40, 45, 50, 55, 60 ] );
		$interval_time = [];
		foreach ( $intervals as $value ) {
			$interval_time[ $value ] = $value;
		}

		return $interval_time;
	}
}
