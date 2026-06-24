<?php

namespace RT\FoodMenu\Controllers\Reservation;

defined( 'ABSPATH' ) || exit();

use RT\FoodMenu\Traits\SingletonTrait;
use RT\FoodMenu\Helpers\Fns;

/**
 * Reservation form submit / cancel AJAX handlers.
 */
class ReservationSubmit {

	use SingletonTrait;

	/**
	 * Cached settings.
	 *
	 * @var array
	 */
	private $options;

	/**
	 * Reservation post type.
	 *
	 * @var string
	 */
	private $post_type = 'fmp_reservation';

	/**
	 * Init.
	 *
	 * @return void
	 */
	public function init() {
		$this->options = Fns::get_settings_option();
		if ( ! is_array( $this->options ) ) {
			$this->options = [];
		}

		add_action( 'wp_ajax_reservation_form_submit', [ $this, 'reservation_form_submit' ] );
		add_action( 'wp_ajax_nopriv_reservation_form_submit', [ $this, 'reservation_form_submit' ] );
	}

	/**
	 * AJAX entry point — dispatches by `fmp_action`.
	 *
	 * @return void
	 */
	public function reservation_form_submit() {
		if ( ! wp_verify_nonce( Fns::getNonce(), Fns::nonceText() ) ) {
			return;
		}

		if ( empty( $_POST['fmp_action'] ) ) {
			return;
		}

		$action = sanitize_text_field( wp_unslash( $_POST['fmp_action'] ) );

		if ( 'fmp-reservation' === $action ) {
			$this->make_reservation();
		}

		if ( 'fmp-reservation-cancel' === $action ) {
			$this->cancel_reservation();
		}
	}

	/**
	 * Create a new reservation.
	 *
	 * Visual table-layout (Pro) attaches extra validation + meta via the
	 * `fmp/reservation/pre_insert` and `fmp/reservation/extra_meta` filters.
	 *
	 * @return void
	 */
	public function make_reservation() {
		$fmp_resi_default_status = ! empty( $this->options['fmp_resi_default_status'] ) ? $this->options['fmp_resi_default_status'] : 'confirmed';
		$form_data               = filter_input_array( INPUT_POST, FILTER_SANITIZE_SPECIAL_CHARS );

		$resi_name       = ! empty( $form_data['fmp_name'] ) ? sanitize_text_field( $form_data['fmp_name'] ) : '';
		$resi_content    = ! empty( $form_data['fmp_message'] ) ? sanitize_text_field( $form_data['fmp_message'] ) : '';
		$resi_email_raw  = ! empty( $form_data['fmp_email'] ) ? sanitize_text_field( $form_data['fmp_email'] ) : '';
		$resi_email      = is_email( $resi_email_raw ) ? sanitize_email( $resi_email_raw ) : '';
		$resi_date       = ! empty( $form_data['fmp_date'] ) ? sanitize_text_field( $form_data['fmp_date'] ) : '';
		$resi_start_time = ! empty( $form_data['fmp_start_time'] ) ? sanitize_text_field( $form_data['fmp_start_time'] ) : '';
		$resi_end_time   = ! empty( $form_data['fmp_end_time'] ) ? sanitize_text_field( $form_data['fmp_end_time'] ) : '';
		$resi_guest_no   = ! empty( $form_data['fmp_guest_nub'] ) ? sanitize_text_field( $form_data['fmp_guest_nub'] ) : '';
		$resi_phone      = ! empty( $form_data['fmp_phone'] ) ? preg_replace( '/[^0-9+-]/', '', sanitize_text_field( $form_data['fmp_phone'] ) ) : '';
		$resi_location   = ! empty( $form_data['fmp_location_id'] ) ? absint( $form_data['fmp_location_id'] ) : 0;

		$meta_data = [
			'fmp_resi_meta_date'       => strtotime( $resi_date ),
			'fmp_resi_meta_start_time' => $resi_start_time,
			'fmp_resi_meta_end_time'   => $resi_end_time,
			'fmp_resi_meta_seat'       => $resi_guest_no,
			'fmp_resi_meta_name'       => $resi_name,
			'fmp_resi_meta_email'      => $resi_email,
			'fmp_resi_meta_phone'      => $resi_phone,
			'fmp_resi_meta_message'    => $resi_content,
		];

		if ( $resi_location > 0 ) {
			$meta_data['fmp_resi_meta_location'] = $resi_location;
		}

		$context = [
			'form_data'     => $form_data,
			'resi_date'     => $resi_date,
			'resi_start'    => $resi_start_time,
			'resi_end'      => $resi_end_time,
			'resi_guest'    => $resi_guest_no,
			'resi_location' => $resi_location,
		];

		/**
		 * Filter the pre-insert state. Pro's table-layout hook short-circuits this
		 * with ['error' => true, 'message' => '...'] when a chair conflict is
		 * detected, or returns ['skip_seat_check' => true] for visual-table bookings
		 * (which use chair-overlap validation, not seat-capacity).
		 *
		 * @param array $state ['meta_data' => array, 'skip_seat_check' => bool, 'error' => bool, 'message' => string]
		 * @param array $context Form context (form_data + parsed fields).
		 */
		$state = apply_filters( 'fmp/reservation/pre_insert', [
			'meta_data'       => $meta_data,
			'skip_seat_check' => false,
			'error'           => false,
			'message'         => '',
		], $context );

		if ( ! empty( $state['error'] ) ) {
			wp_send_json_error( [
				'status_code' => 400,
				'message'     => $state['message'] ?? esc_html__( 'Reservation could not be completed.', 'tlp-food-menu' ),
			] );
		}

		$meta_data       = is_array( $state['meta_data'] ?? null ) ? $state['meta_data'] : $meta_data;
		$skip_seat_check = ! empty( $state['skip_seat_check'] );

		$missing_fields = [];

		if ( empty( $resi_date ) ) {
			$missing_fields[] = esc_html__( 'Date', 'tlp-food-menu' );
		}
		if ( empty( $resi_start_time ) ) {
			$missing_fields[] = esc_html__( 'Start Time', 'tlp-food-menu' );
		}
		if ( empty( $resi_end_time ) ) {
			$missing_fields[] = esc_html__( 'End Time', 'tlp-food-menu' );
		}
		if ( empty( $resi_guest_no ) ) {
			$missing_fields[] = esc_html__( 'Guest Number', 'tlp-food-menu' );
		}
		if ( empty( $resi_name ) ) {
			$missing_fields[] = esc_html__( 'Name', 'tlp-food-menu' );
		}
		if ( empty( $resi_email_raw ) ) {
			$missing_fields[] = esc_html__( 'Email', 'tlp-food-menu' );
		} elseif ( empty( $resi_email ) ) {
			$missing_fields[] = esc_html__( 'Email (invalid format)', 'tlp-food-menu' );
		}
		if ( empty( $resi_phone ) ) {
			$missing_fields[] = esc_html__( 'Phone', 'tlp-food-menu' );
		}

		if ( ! empty( $missing_fields ) ) {
			wp_send_json_error( [
				'status_code' => 400,
				'message'     => sprintf(
					/* translators: %s: comma-separated list of missing field names */
					esc_html__( 'The following fields are required: %s', 'tlp-food-menu' ),
					implode( ', ', $missing_fields )
				),
			] );
		}

		if ( ! $skip_seat_check ) {
			$capacity_check = Reservation::get_instance()->resi_capacity_status(
				$resi_date,
				$resi_start_time,
				$resi_end_time,
				'',
				$resi_location
			);

			if ( 'closed' === $capacity_check['status'] ) {
				wp_send_json_error( [
					'status_code' => 400,
					'message'     => esc_html__( 'Sorry, all seats are booked for this time slot. Please choose a different time.', 'tlp-food-menu' ),
				] );
			}

			$available_seats = isset( $capacity_check['capacity'] ) ? (int) $capacity_check['capacity'] : 0;

			if ( (int) $resi_guest_no > $available_seats ) {
				wp_send_json_error( [
					'status_code' => 400,
					'message'     => sprintf(
						/* translators: %d: number of available seats */
						esc_html__( 'Only %d seat(s) available for this time slot. Please reduce the number of guests or choose a different time.', 'tlp-food-menu' ),
						$available_seats
					),
				] );
			}
		}

		$post_slug = sanitize_title_with_dashes( $resi_name, '', 'save' );
		$post_slug = sanitize_text_field( $post_slug );

		$post_id = wp_insert_post( [
			'post_type'      => $this->post_type,
			'post_name'      => $post_slug,
			'post_title'     => $resi_name,
			'post_content'   => $resi_content,
			'post_status'    => 'publish',
			'comment_status' => 'closed',
		] );

		if ( ! $post_id ) {
			wp_send_json_error( [
				'status_code' => 400,
				'message'     => esc_html__( 'Failed to place booking. Please try again', 'tlp-food-menu' ),
			] );
		}

		$booking_id                         = Fns::generate_invoice_number( $post_id );
		$meta_data['fmp_resi_meta_invoice'] = $booking_id;
		$meta_data['fmp_resi_meta_status']  = $fmp_resi_default_status;

		foreach ( $meta_data as $key => $value ) {
			update_post_meta( $post_id, $key, $value );
		}

		if ( 'confirmed' === $meta_data['fmp_resi_meta_status'] ) {
			$message = Fns::get_reservation_message( $this->options, 'confirm', $booking_id );
		} else {
			$message = Fns::get_reservation_message( $this->options, 'pending', $booking_id );
		}

		$mail_args = [
			'ID'           => $post_id,
			'booking_date' => $resi_date . ' (' . $resi_start_time . ' to ' . $resi_end_time . ')',
		];

		Fns::send_email_notify( $mail_args, $resi_email, $booking_id, $meta_data['fmp_resi_meta_status'] );

		wp_send_json_success( [
			'status_code' => 200,
			'message'     => $message,
		] );
	}

	/**
	 * Process a cancellation request.
	 *
	 * @return void
	 */
	public function cancel_reservation() {
		$form_data        = filter_input_array( INPUT_POST, FILTER_SANITIZE_SPECIAL_CHARS );
		$booking_id       = ! empty( $form_data['fmp_booking_id'] ) ? sanitize_text_field( $form_data['fmp_booking_id'] ) : '';
		$cancel_email_raw = ! empty( $form_data['fmp_cancel_email'] ) ? sanitize_text_field( $form_data['fmp_cancel_email'] ) : '';
		$cancel_email     = is_email( $cancel_email_raw ) ? sanitize_email( $cancel_email_raw ) : '';
		$cancel_phone     = ! empty( $form_data['fmp_cancel_phone'] ) ? preg_replace( '/[^0-9+-]/', '', sanitize_text_field( $form_data['fmp_cancel_phone'] ) ) : '';
		$resi_status      = 'cancelled';
		$cancel_missing   = [];

		if ( empty( $booking_id ) ) {
			$cancel_missing[] = esc_html__( 'Booking ID', 'tlp-food-menu' );
		}
		if ( empty( $cancel_email_raw ) ) {
			$cancel_missing[] = esc_html__( 'Email', 'tlp-food-menu' );
		} elseif ( empty( $cancel_email ) ) {
			$cancel_missing[] = esc_html__( 'Email (invalid format)', 'tlp-food-menu' );
		}
		if ( empty( $cancel_phone ) ) {
			$cancel_missing[] = esc_html__( 'Phone', 'tlp-food-menu' );
		}

		if ( ! empty( $cancel_missing ) ) {
			wp_send_json_error( [
				'status_code' => 400,
				'message'     => [
					sprintf(
						/* translators: %s: comma-separated list of missing field names */
						esc_html__( 'The following fields are required: %s', 'tlp-food-menu' ),
						implode( ', ', $cancel_missing )
					),
				],
			] );
		}

		$booking_post = get_posts( [
			'post_type'      => $this->post_type,
			'posts_per_page' => 1,
			//phpcs:ignore WordPress.DB.SlowDBQuery.slow_db_query_meta_query
			'meta_query'     => [
				[
					'key'   => 'fmp_resi_meta_invoice',
					'value' => $booking_id,
				],
				[
					'key'   => 'fmp_resi_meta_email',
					'value' => $cancel_email,
				],
			],
		] );

		if ( ! $booking_post || is_wp_error( $booking_post ) ) {
			wp_send_json_error( [
				'status_code' => 401,
				'message'     => [ esc_html__( 'No reservation found please provide the details', 'tlp-food-menu' ) ],
			] );
		}

		$booking_post_id = $booking_post[0]->ID;
		update_post_meta( $booking_post_id, 'fmp_resi_meta_status', 'cancelled' );

		Fns::send_email_notify( [ 'ID' => $booking_post_id ], $cancel_email, $booking_id, $resi_status );

		wp_send_json_success( [
			'status_code' => 200,
			'message'     => [ esc_html__( 'Your request for cancellation has been processed successfully.', 'tlp-food-menu' ) ],
		] );
	}
}
