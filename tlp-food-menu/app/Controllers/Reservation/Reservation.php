<?php
// phpcs:disable WordPress.NamingConventions.PrefixAllGlobals -- Established plugin: public namespace, hook names, functions and theme-overridable template variables must stay unchanged for backward compatibility.

namespace RT\FoodMenu\Controllers\Reservation;

defined( 'ABSPATH' ) || exit();

use RT\FoodMenu\Traits\SingletonTrait;
use RT\FoodMenu\Helpers\Fns;

/**
 * Reservation main controller.
 *
 * Handles enqueueing, capacity checks, status updates and email notifications
 * for normal (form-based) reservations. Visual table-layout behavior is layered
 * on top by Pro via the `fmp/reservation/*` filters.
 */
class Reservation {

	use SingletonTrait;

	/**
	 * Cached settings array.
	 *
	 * @var array
	 */
	private $options;

	/**
	 * Init hooks.
	 *
	 * @return void
	 */
	public function init() {
		$this->options = Fns::get_settings_option();
		if ( ! is_array( $this->options ) ) {
			$this->options = [];
		}

		wp_enqueue_script( 'fmp-timepicker' );
		wp_enqueue_style( 'fmp-timepicker' );

		add_action( 'admin_enqueue_scripts', [ $this, 'admin_enqueue_assets' ] );
		add_action( 'wp_enqueue_scripts', [ $this, 'frontend_enqueue_assets' ] );

		add_action( 'wp_ajax_booking_seat_capacity', [ $this, 'booking_seat_capacity' ] );
		add_action( 'wp_ajax_nopriv_booking_seat_capacity', [ $this, 'booking_seat_capacity' ] );

		// Status change is an admin-only operation (reservation list-table dropdown).
		// Intentionally NOT registered for nopriv — a logged-out visitor must never change reservation status.
		add_action( 'wp_ajax_save_fmp_resi_meta_status', [ $this, 'save_fmp_resi_meta_status' ] );

		add_action( 'fmp_reservation_change_email_hook', [ $this, 'fmp_reservation_change_email_hook' ], 10, 2 );

		ReservationSubmit::get_instance();
		ReservationTableList::get_instance();
		ReservationMeta::get_instance();
	}

	/**
	 * Frontend enqueue.
	 *
	 * @return void
	 */
	public function frontend_enqueue_assets() {
		if ( 'on' !== ( $this->options['fmp_food_reservation_status'] ?? '' ) ) {
			return;
		}

		wp_enqueue_script( 'fmp-timepicker' );
		wp_enqueue_style( 'fmp-timepicker' );
		wp_enqueue_script( 'fmp-flatpickr' );
		wp_enqueue_style( 'fmp-flatpickr' );
		wp_enqueue_script( 'fmp-intl-tel-input' );
		wp_enqueue_style( 'fmp-intl-tel-input' );
		wp_enqueue_script( 'fmp-form-validation' );
		wp_enqueue_script( 'fmp-reservation' );
		wp_localize_script(
			'fmp-reservation',
			'fmpResiParams',
			$this->reservation_data_obj()
		);
	}

	/**
	 * Admin enqueue.
	 *
	 * @return void
	 */
	public function admin_enqueue_assets() {
		wp_enqueue_script( 'fmp-timepicker' );
		wp_enqueue_style( 'fmp-timepicker' );
		wp_enqueue_script( 'fmp-flatpickr' );
		wp_enqueue_script( 'fmp-range-flatpickr' );
		wp_enqueue_style( 'fmp-flatpickr' );
		wp_enqueue_script( 'fmp-form-validation' );

		wp_enqueue_style( 'fm-admin' );

		wp_enqueue_script( 'fmp-admin-reservation' );
		wp_enqueue_script( 'fmp-reservation' );
		wp_localize_script(
			'fmp-reservation',
			'fmpResiParams',
			$this->reservation_data_obj()
		);
	}

	/**
	 * Build the localized data object for the reservation form JS.
	 *
	 * @return array
	 */
	public function reservation_data_obj() {
		$settings          = $this->options;
		$fm_date_format    = get_option( 'date_format' ) ?? 'Y-m-d';
		$fm_time_format    = get_option( 'time_format' ) ?? 'H:i';
		$current_date_time = current_datetime();
		$current_date      = $current_date_time->format( $fm_date_format );
		$current_time      = $current_date_time->format( $fm_time_format );
		$nonce             = wp_create_nonce( Fns::nonceText() );

		$new_fmp_resi_weekly_schedule = Fns::get_setting( 'new_fmp_resi_weekly_schedule', [] );
		$reservation_weekly_holiday   = Fns::get_offday_schedule( $new_fmp_resi_weekly_schedule );
		$weekly_schedule_start_time   = Fns::get_schedule_time( $new_fmp_resi_weekly_schedule );
		$weekly_schedule_end_time     = Fns::get_schedule_time( $new_fmp_resi_weekly_schedule, 'end' );
		$weekly_schedule_slots        = Fns::get_schedule_slots( $new_fmp_resi_weekly_schedule );

		$data = [
			'resi_time_interval'              => ! empty( $settings['fmp_resi_time_interval'] ) ? $settings['fmp_resi_time_interval'] : 30,
			'resi_weekly_schedule'            => $settings['fmp_resi_weekly_schedule'] ?? [],
			'resi_weekly_schedule_start_time' => $weekly_schedule_start_time,
			'resi_weekly_schedule_end_time'   => $weekly_schedule_end_time,
			'resi_weekly_schedule_slots'      => $weekly_schedule_slots,
			'resi_weekly_holiday'             => $reservation_weekly_holiday,
			'current_time'                    => $current_time,
			'current_date'                    => $current_date,
			'date_format'                     => $fm_date_format,
			'time_format'                     => $fm_time_format,
			'nonceID'                         => esc_attr( Fns::nonceId() ),
			'nonce'                           => esc_attr( $nonce ),
			'ajaxurl'                         => esc_url( admin_url( 'admin-ajax.php' ) ),
			'default_country'                 => TLPFoodMenu()->has_pro()
				? strtolower( sanitize_text_field( $settings['fmp_resi_default_country'] ?? 'us' ) )
				: 'us',
			'allowed_countries'               => TLPFoodMenu()->has_pro()
				? array_values( array_map(
					'strtolower',
					array_filter(
						(array) ( $settings['fmp_resi_allowed_countries'] ?? [] ),
						'is_string'
					)
				) )
				: [],
			'utils_script'                    => TLPFoodMenu()->assets_url() . 'vendor/intl-tel-input/utils.js',
			'validation_message'              => [
				'required_text'       => esc_html__( 'Kindly fill out this field.', 'tlp-food-menu' ),
				'required_date'       => esc_html__( 'Please select a booking date.', 'tlp-food-menu' ),
				'required_start_time' => esc_html__( 'Please select a start time.', 'tlp-food-menu' ),
				'required_end_time'   => esc_html__( 'Please select an end time.', 'tlp-food-menu' ),
				'required_guest'      => esc_html__( 'Please select the number of guests.', 'tlp-food-menu' ),
				'required_name'       => esc_html__( 'Please enter your name.', 'tlp-food-menu' ),
				'required_email'      => esc_html__( 'Please enter your email address.', 'tlp-food-menu' ),
				'required_phone'      => esc_html__( 'Please enter your phone number.', 'tlp-food-menu' ),
				'required_booking_id' => esc_html__( 'Please enter your booking ID.', 'tlp-food-menu' ),
				'email'               => esc_html__( 'Please enter a valid email address.', 'tlp-food-menu' ),
				'phone'               => esc_html__( 'Please enter a valid phone number for the selected country.', 'tlp-food-menu' ),
				'end_time'            => esc_html__( 'End time must be greater than start time.', 'tlp-food-menu' ),
				'table_layout'        => [],
				'guest_select_text'   => esc_html__( 'Select total guests', 'tlp-food-menu' ),
			],
			'modal'                           => [
				'title'            => esc_html__( 'Thank you!', 'tlp-food-menu' ),
				'ok_text'          => esc_html__( 'OK', 'tlp-food-menu' ),
				'close_label'      => esc_html__( 'Close', 'tlp-food-menu' ),
				'booking_id_label' => esc_html__( 'Booking ID', 'tlp-food-menu' ),
				'copy_label'       => esc_html__( 'Copy booking ID', 'tlp-food-menu' ),
				'copied_label'    => esc_html__( 'Copied!', 'tlp-food-menu' ),
			],
		];

		// Pro can inject location-specific schedule, global visual-table data, etc.
		return apply_filters( 'fmp/reservation/localize_data', $data, $settings );
	}

	/**
	 * Reservation capacity status for a given slot.
	 *
	 * @param string $selected_date .
	 * @param string $start_time .
	 * @param string $end_time .
	 * @param string $type           '' for normal, 'table_layout' for visual-table (Pro).
	 * @param int    $location_id .
	 *
	 * @return array
	 */
	public function resi_capacity_status( $selected_date = null, $start_time = '', $end_time = '', $type = '', $location_id = 0 ) {
		$settings = $this->options;
		$response = [
			'capacity' => 30,
			'status'   => 'open',
		];

		$seat_capacity    = ! empty( $settings['fmp_resi_seat_capacity'] )
			? (int) $settings['fmp_resi_seat_capacity']
			: 30;
		$max_guest_global = $settings['fmp_resi_max_guest'] ?? 0;

		$capacity_context = apply_filters(
			'fmp/reservation/capacity_context',
			[
				'seat_capacity' => $seat_capacity,
				'max_guest'     => (int) $max_guest_global,
			],
			$location_id,
			$selected_date,
			$start_time
		);
		$seat_capacity    = (int) $capacity_context['seat_capacity'];
		$max_guest_global = (int) $capacity_context['max_guest'];

		if ( ! empty( $selected_date ) && ! empty( $start_time ) ) {
			$slot_capacity = $this->get_slot_seat_capacity( $selected_date, $start_time, $location_id );
			if ( $slot_capacity > 0 ) {
				$seat_capacity = $slot_capacity;
			}
		}

		if ( ! empty( $seat_capacity ) ) {
			$reservation_data = $this->get_all_reservation(
				[
					'capacity'      => $seat_capacity,
					'selected_date' => $selected_date,
				],
				$start_time,
				$end_time,
				$type,
				$location_id
			);

			if ( ! empty( $type ) ) {
				$response['date_booked_ids']       = $reservation_data['date_booked_ids'];
				$response['date_booked_table_ids'] = $reservation_data['date_booked_table_ids'];
				$reserved_seats                    = $reservation_data['total_seat'];
				$response['date_booked_total']     = $reserved_seats;
			} else {
				$reserved_seats = $reservation_data;
			}

			if ( $reserved_seats >= $seat_capacity ) {
				$response['status']  = 'closed';
				$response['message'] = esc_html__( 'Fully booked. Try another time.', 'tlp-food-menu' );
			}

			$available_capacity = absint( $seat_capacity - $reserved_seats );
			$max_guest_capacity = $max_guest_global > 0 ? absint( $max_guest_global ) : $available_capacity;

			$response['capacity']     = $available_capacity;
			$response['max_capacity'] = min( $max_guest_capacity, $available_capacity );
		}

		return $response;
	}

	/**
	 * Look up all reservations overlapping a given slot, optionally filtered by
	 * a Pro-supplied type (e.g. 'table_layout' to include pending bookings).
	 *
	 * Returns the seat total for normal lookups and the structured booking data
	 * (seats + chair/table ID lists) when a type is requested.
	 *
	 * @param array  $data            Lookup args (selected_date, capacity).
	 * @param string $start_time .
	 * @param string $end_time .
	 * @param string $type            '' or 'table_layout'.
	 * @param int    $location_id .
	 *
	 * @return int|array
	 */
	public function get_all_reservation( $data = [], $start_time = '', $end_time = '', $type = '', $location_id = 0 ) {
		$total_seat            = 0;
		$date_booked_ids       = [];
		$date_booked_table_ids = [];

		$booking_date = ! empty( $data['selected_date'] ) ? $data['selected_date'] : gmdate( defined( 'TLP_FOOD_MENU_DEFAULT_DATE_FORMAT' ) ? TLP_FOOD_MENU_DEFAULT_DATE_FORMAT : 'Y-m-d' );
		$booking_date = strtotime( $booking_date );

		/**
		 * Filter the post-meta statuses counted toward booked-seat totals.
		 * Pro adds 'pending' for type='table_layout' so unconfirmed visual-table
		 * bookings still block a chair.
		 */
		$allowed_statuses = apply_filters(
			'fmp/reservation/counted_statuses',
			[ 'confirmed', 'completed' ],
			$type
		);

		$meta_query = [
			[
				'key'     => 'fmp_resi_meta_status',
				'value'   => $allowed_statuses,
				'compare' => 'IN',
			],
			[
				'key'     => 'fmp_resi_meta_date',
				'value'   => $booking_date,
				'compare' => '=',
			],
		];

		if ( $location_id > 0 ) {
			$meta_query[] = [
				'key'     => 'fmp_resi_meta_location',
				'value'   => $location_id,
				'compare' => '=',
			];
		}

		$args = apply_filters(
			'fmp/reservation/all_query_args',
			[
				'post_type'   => 'fmp_reservation',
				'numberposts' => - 1,
				'post_status' => 'publish',
				'fields'      => 'ids',
				'meta_query'  => $meta_query, //phpcs:ignore WordPress.DB.SlowDBQuery.slow_db_query_meta_query
			],
			$type,
			$location_id
		);

		$all_reservations = get_posts( $args );

		$req_start = Fns::parse_time_to_seconds( $start_time ?: '12:00 AM' );
		$req_end   = Fns::parse_time_to_seconds( $end_time ?: '11:30 PM' );

		foreach ( $all_reservations as $reservation ) {
			$saved_start_raw  = get_post_meta( $reservation, 'fmp_resi_meta_start_time', true );
			$saved_end_raw    = get_post_meta( $reservation, 'fmp_resi_meta_end_time', true );
			$saved_start_time = Fns::parse_time_to_seconds( $saved_start_raw ?: '12:00 AM' );
			$saved_end_time   = Fns::parse_time_to_seconds( $saved_end_raw ?: '11:30 PM' );

			if ( $this->should_calculate_seats( $saved_start_time, $saved_end_time, $req_start, $req_end ) ) {
				$total_seat += (int) get_post_meta( $reservation, 'fmp_resi_meta_seat', true );

				$this->merge_meta_ids( $reservation, 'fmp_resi_meta_booked_ids', $date_booked_ids );
				$this->merge_meta_ids( $reservation, 'fmp_resi_meta_booked_table_ids', $date_booked_table_ids );
			}
		}

		$date_booking_data = [
			'total_seat'            => $total_seat,
			'date_booked_ids'       => $date_booked_ids,
			'date_booked_table_ids' => $date_booked_table_ids,
		];

		return empty( $type ) ? $total_seat : $date_booking_data;
	}

	/**
	 * @param int $saved_start_time .
	 * @param int $saved_end_time .
	 * @param int $start_time .
	 * @param int $end_time .
	 *
	 * @return bool
	 */
	private function should_calculate_seats( $saved_start_time, $saved_end_time, $start_time, $end_time ) {
		return $saved_start_time < $end_time && $saved_end_time > $start_time;
	}

	/**
	 * @param int    $post_id .
	 * @param string $meta_key .
	 * @param array  $merged_ids .
	 *
	 * @return void
	 */
	private function merge_meta_ids( $post_id, $meta_key, &$merged_ids ) {
		$ids = get_post_meta( $post_id, $meta_key, true );

		if ( ! empty( $ids ) ) {
			$ids = maybe_unserialize( $ids );

			if ( is_array( $ids ) && count( $ids ) > 0 ) {
				$merged_ids[] = $ids;
			}
		}
	}

	/**
	 * AJAX: booking form seat capacity check.
	 *
	 * @return void
	 */
	public function booking_seat_capacity() {
		$responseData = [
			'message' => '',
			'status'  => 'open',
		];
		$errorMessage = esc_html__( 'Something went wrong', 'tlp-food-menu' );

		$postData = filter_input_array( INPUT_POST, FILTER_SANITIZE_SPECIAL_CHARS );

		if ( empty( $postData['fmp_action'] ) || 'fmp_seat_qty' !== $postData['fmp_action'] ) {
			wp_send_json_error( [
				'success' => false,
				'message' => [ $errorMessage ],
				'data'    => $responseData,
			] );
		}

		$successMessage = '';

		if ( ! empty( $postData['selected_date'] ) ) {
			$startTime       = ! empty( $postData['start_time'] ) ? $postData['start_time'] : '';
			$endTime         = ! empty( $postData['end_time'] ) ? $postData['end_time'] : '';
			$reservationType = ! empty( $postData['type'] ) ? $postData['type'] : '';

			if ( ! ( $startTime && $endTime ) ) {
				wp_send_json_error( [
					'success' => false,
					'message' => [ $errorMessage ],
				] );
			}
			$locationId   = ! empty( $postData['location_id'] ) ? absint( $postData['location_id'] ) : 0;
			$responseData = $this->resi_capacity_status(
				$postData['selected_date'],
				$startTime,
				$endTime,
				$reservationType,
				$locationId
			);

			$successMessage = esc_html__( 'Everything is okay', 'tlp-food-menu' );
		}

		wp_send_json_success( [
			'success' => true,
			'message' => [ $successMessage ],
			'data'    => $responseData,
		] );
	}

	/**
	 * AJAX: change reservation status.
	 *
	 * @return void
	 */
	public function save_fmp_resi_meta_status() {
		if ( ! wp_verify_nonce( Fns::getNonce(), Fns::nonceText() ) ) {
			wp_send_json_error( [ 'message' => 'Invalid nonce' ] );
		}

		$post_id    = isset( $_POST['post_id'] ) ? intval( $_POST['post_id'] ) : 0;
		$meta_value = isset( $_POST['meta_value'] ) ? sanitize_text_field( wp_unslash( $_POST['meta_value'] ) ) : '';

		if ( $post_id <= 0 || empty( $meta_value ) ) {
			wp_send_json_error( [ 'message' => 'Invalid or missing parameters' ] );
		}

		// Authorization. The nonce alone is not an authorization boundary: it is localized on the
		// public reservation page and is not session-bound for logged-out visitors. Only a user who
		// can edit this specific reservation may change its status, and the target must be a reservation.
		if ( 'fmp_reservation' !== get_post_type( $post_id ) ) {
			wp_send_json_error( [ 'message' => 'Invalid reservation' ] );
		}

		if ( ! current_user_can( 'edit_post', $post_id ) ) {
			wp_send_json_error( [ 'message' => 'You are not allowed to change this reservation status.' ] );
		}

		update_post_meta( $post_id, 'fmp_resi_meta_status', $meta_value );

		do_action( 'fmp_reservation_change_email_hook', $post_id, $meta_value );

		wp_send_json_success( [ 'message' => 'Status updated successfully' ] );
	}

	/**
	 * Email customer when reservation status changes.
	 *
	 * @param int    $post_id .
	 * @param string $newReservation .
	 *
	 * @return void
	 */
	public function fmp_reservation_change_email_hook( $post_id, $newReservation ) {
		$booking_id      = get_post_meta( $post_id, 'fmp_resi_meta_invoice', true );
		$customer_email  = get_post_meta( $post_id, 'fmp_resi_meta_email', true );
		$customer_name   = get_post_meta( $post_id, 'fmp_resi_meta_name', true );
		$sender_mail     = ! empty( $this->options['fmp_resi_sender_email'] ) ? $this->options['fmp_resi_sender_email'] : get_bloginfo( 'admin_email' );

		$mail_subject    = esc_html__( 'Hello ', 'tlp-food-menu' ) . ' ' . $customer_name;
		$args['msg']     = sprintf(
			/* translators: 1: new status, 2: booking id */
			esc_html__( 'We would like to inform you that your reservation status has been updated to <strong>%1$s</strong> under the booking id - <strong>%2$s</strong>', 'tlp-food-menu' ),
			$newReservation,
			$booking_id
		);
		$resi_date       = get_post_meta( $post_id, 'fmp_resi_meta_date', true );
		$resi_start_time = get_post_meta( $post_id, 'fmp_resi_meta_start_time', true );
		$resi_end_time   = get_post_meta( $post_id, 'fmp_resi_meta_end_time', true );

		$args['status']       = $newReservation;
		$args['resi_id']      = $post_id;
		$args['invoice']      = $booking_id;
		$resi_date            = is_numeric( $resi_date ) ? (int) $resi_date : strtotime( $resi_date );
		$args['booking_date'] = gmdate( get_option( 'date_format' ), $resi_date ) . ' (' . $resi_start_time . ' to ' . $resi_end_time . ')';
		$mail_body            = Fns::mail_body_markup( $args, 'user' );

		Fns::send_email( [
			'to'        => $customer_email,
			'subject'   => $mail_subject,
			'mail_body' => $mail_body,
			'from'      => $sender_mail,
			'from_name' => get_bloginfo( 'name' ),
		] );
	}

	/**
	 * Per-slot seat capacity from the weekly schedule. Returns 0 when no
	 * matching slot has a `max_order` override.
	 *
	 * @param string $selected_date .
	 * @param string $start_time .
	 * @param int    $location_id .
	 *
	 * @return int
	 */
	private function get_slot_seat_capacity( $selected_date, $start_time, $location_id = 0 ) {
		$schedule = apply_filters( 'fmp/reservation/slot_schedule', null, $location_id );

		if ( null === $schedule ) {
			$schedule = Fns::get_setting( 'new_fmp_resi_weekly_schedule', [] );
		}

		if ( empty( $schedule ) ) {
			return 0;
		}

		$timestamp = strtotime( $selected_date );
		if ( ! $timestamp ) {
			return 0;
		}

		$day_name = strtolower( wp_date( 'l', $timestamp ) );
		$day_data = $schedule[ $day_name ] ?? [];
		$slots    = $day_data['slots'] ?? [];

		if ( empty( $slots ) || ! is_array( $slots ) ) {
			return 0;
		}

		$start_minutes = $this->time_to_minutes( $start_time );

		foreach ( $slots as $slot ) {
			$slot_start = $this->time_to_minutes( $slot['start'] ?? '' );
			$slot_end   = $this->time_to_minutes( $slot['end'] ?? '' );

			if ( $start_minutes >= $slot_start && $start_minutes < $slot_end && ! empty( $slot['max_order'] ) ) {
				return (int) $slot['max_order'];
			}
		}

		return 0;
	}

	/**
	 * Convert a time string ("14:00" or "2:00 PM") to minutes since midnight.
	 *
	 * @param string $time_str .
	 *
	 * @return int
	 */
	private function time_to_minutes( $time_str ) {
		if ( empty( $time_str ) ) {
			return 0;
		}

		$time_str = trim( $time_str );

		if ( preg_match( '/^(\d{1,2}):(\d{2})$/', $time_str, $m ) ) {
			return (int) $m[1] * 60 + (int) $m[2];
		}

		if ( preg_match( '/^(\d{1,2}):(\d{2})\s*(am|pm)$/i', $time_str, $m ) ) {
			$h   = (int) $m[1];
			$min = (int) $m[2];
			if ( 'pm' === strtolower( $m[3] ) && 12 !== $h ) {
				$h += 12;
			} elseif ( 'am' === strtolower( $m[3] ) && 12 === $h ) {
				$h = 0;
			}

			return $h * 60 + $min;
		}

		return 0;
	}
}
