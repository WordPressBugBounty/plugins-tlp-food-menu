<?php
// phpcs:disable WordPress.NamingConventions.PrefixAllGlobals -- Established plugin: public namespace, hook names, functions and theme-overridable template variables must stay unchanged for backward compatibility.
/**
 * Dashboard API Class.
 *
 * @package RT_FoodMenu
 */

namespace RT\FoodMenu\Controllers\Api;

// Do not allow directly accessing this file.
use RT\FoodMenu\Helpers\Fns;
use RT\FoodMenuPro\Helpers\FnsPro;

if ( ! defined( 'ABSPATH' ) ) {
	exit( 'This script cannot be accessed directly.' );
}

/**
 * Dashboard API Class.
 */
class DashboardApi {

	/**
	 * Constructor.
	 */
	public function __construct() {
		add_action( 'rest_api_init', [ $this, 'register_routes' ] );
	}

	/**
	 * Register REST API routes.
	 *
	 * @return void
	 */
	public function register_routes() {
		register_rest_route(
			'fmp/v1',
			'/dashboard/stats',
			[
				'methods'             => \WP_REST_Server::READABLE,
				'callback'            => [ $this, 'get_stats' ],
				'permission_callback' => [ $this, 'check_permission' ],
			]
		);

		register_rest_route(
			'fmp/v1',
			'/dashboard/update-order-status',
			[
				'methods'             => \WP_REST_Server::CREATABLE,
				'callback'            => [ $this, 'update_order_status' ],
				'permission_callback' => [ $this, 'check_permission' ],
			]
		);
	}

	/**
	 * Permission check.
	 *
	 * @return bool
	 */
	public function check_permission() {
		return current_user_can( 'manage_options' );
	}

	/**
	 * GET stats endpoint.
	 *
	 * @param \WP_REST_Request $request Request object.
	 *
	 * @return \WP_REST_Response
	 */
	public function get_stats( $request ) {
		$period     = $request->get_param( 'period' ) ? sanitize_text_field( $request->get_param( 'period' ) ) : 'weekly';
		$start_date = $request->get_param( 'start_date' ) ? sanitize_text_field( $request->get_param( 'start_date' ) ) : '';
		$end_date   = $request->get_param( 'end_date' ) ? sanitize_text_field( $request->get_param( 'end_date' ) ) : '';

		// Get current user info.
		$current_user = wp_get_current_user();


		//Get avatar
		$email = $current_user->user_email;
		$hash  = md5( strtolower( trim( $email ) ) );

		$gravatar = "https://www.gravatar.com/avatar/$hash?d=404&s=96";

		$response = wp_remote_head( $gravatar );

		if ( ! is_wp_error( $response ) && wp_remote_retrieve_response_code( $response ) === 200 ) {
			$avatar = get_avatar_url( $current_user->ID, [ 'size' => 96 ] );
		} else {
			$avatar = null;
		}


		$user_data    = [
			'display_name' => $current_user->display_name,
			'avatar'       => $avatar,
			'profile_url'  => get_edit_profile_url( $current_user->ID ),
		];

		// Get stats.
		$stats = $this->get_dashboard_stats( $start_date, $end_date );

		// Get revenue chart data.
		$revenue = $this->get_revenue_data( $period, $start_date, $end_date );

		// Get reservations.
		$reservations = $this->get_reservations_data();

		// Get orders.
		$orders = $this->get_orders_data();

		return new \WP_REST_Response(
			[
				'logo'         => '',
				'user'         => $user_data,
				'stats'        => $stats,
				'revenue'      => $revenue,
				'reservations' => $reservations,
				'orders'       => $orders,
			],
			200
		);
	}

	/**
	 * POST update-order-status endpoint.
	 *
	 * @param \WP_REST_Request $request Request object.
	 *
	 * @return \WP_REST_Response
	 */
	public function update_order_status( $request ) {
		$body     = $request->get_json_params();
		$order_id = isset( $body['order_id'] ) ? absint( $body['order_id'] ) : 0;
		$status   = isset( $body['status'] ) ? sanitize_text_field( $body['status'] ) : '';

		if ( ! $order_id || ! $status ) {
			return new \WP_REST_Response( [ 'error' => 'Missing required fields' ], 400 );
		}

		// Validate against all registered WC statuses.
		$valid_statuses = function_exists( 'wc_get_order_statuses' )
			? array_map( function ( $key ) { return str_replace( 'wc-', '', $key ); }, array_keys( wc_get_order_statuses() ) )
			: [ 'pending', 'processing', 'on-hold', 'completed', 'cancelled', 'refunded', 'failed' ];

		if ( ! in_array( $status, $valid_statuses, true ) ) {
			return new \WP_REST_Response( [ 'error' => 'Invalid status' ], 400 );
		}

		if ( function_exists( 'wc_get_order' ) ) {
			$order = wc_get_order( $order_id );
			if ( $order ) {
				$order->update_status( $status );
			}
		}

		return new \WP_REST_Response( [ 'success' => true ], 200 );
	}

	/**
	 * Get dashboard stats.
	 *
	 * @param string $start_date Start date.
	 * @param string $end_date End date.
	 *
	 * @return array
	 */
	private function get_dashboard_stats( $start_date = '', $end_date = '' ) {
		$stats = [
			'revenue'      => 0,
			'customers'    => 0,
			'orders'       => 0,
			'reservations' => 0,
		];

		// Get WooCommerce stats if active.
		if ( function_exists( 'wc_get_orders' ) ) {
			$args = [
				'status' => [ 'wc-completed', 'wc-processing' ],
				'type'   => 'shop_order',
				'limit'  => - 1,
				'return' => 'ids',
			];

			if ( $start_date && $end_date ) {
				$args['date_created'] = $start_date . '...' . $end_date;
			}

			$order_ids = wc_get_orders( $args );

			$stats['orders'] = count( $order_ids );

			// Calculate revenue.
			foreach ( $order_ids as $order_id ) {
				$order = wc_get_order( $order_id );
				if ( $order ) {
					$stats['revenue'] += (float) $order->get_total();
				}
			}

			// Get unique customers.
			$customer_emails = [];
			$all_orders      = wc_get_orders(
				[
					'status' => [ 'wc-completed', 'wc-processing', 'wc-pending' ],
					'type'   => 'shop_order',
					'limit'  => - 1,
				]
			);

			foreach ( $all_orders as $order ) {
				$email = $order->get_billing_email();
				if ( $email ) {
					$customer_emails[ $email ] = true;
				}
			}
			$stats['customers'] = count( $customer_emails );
		}

		// Count reservations from custom post type if it exists.
		$reservation_count = wp_count_posts( 'fmp_reservation' );
		if ( $reservation_count && isset( $reservation_count->publish ) && (int) $reservation_count->publish > 0 ) {
			$stats['reservations'] = (int) $reservation_count->publish;
		}

		return $stats;
	}

	/**
	 * Get revenue chart data.
	 *
	 * @param string $period Period type (weekly, monthly, yearly).
	 * @param string $start_date Start date.
	 * @param string $end_date End date.
	 *
	 * @return array
	 */
	private function get_revenue_data( $period = 'weekly', $start_date = '', $end_date = '' ) {
		$data = [];

		if ( function_exists( 'wc_get_orders' ) ) {
			if ( 'weekly' === $period ) {
				$data = $this->get_weekly_revenue( $start_date, $end_date );
			} elseif ( 'monthly' === $period ) {
				$data = $this->get_monthly_revenue();
			} elseif ( 'yearly' === $period ) {
				$data = $this->get_yearly_revenue();
			}
		}

		return $data;
	}

	/**
	 * Get weekly revenue from WooCommerce.
	 *
	 * @param string $start_date Start date.
	 * @param string $end_date End date.
	 *
	 * @return array
	 */
	private function get_weekly_revenue( $start_date = '', $end_date = '' ) {
		$data = [];

		$end   = $end_date ? new \DateTime( $end_date ) : new \DateTime();
		$start = $start_date ? new \DateTime( $start_date ) : ( clone $end )->modify( '-6 days' );

		$current = clone $start;
		while ( $current <= $end ) {
			$date_str = $current->format( 'Y-m-d' );

			$orders = wc_get_orders(
				[
					'status'       => [ 'wc-completed', 'wc-processing' ],
					'type'         => 'shop_order',
					'date_created' => $date_str . '...' . $date_str,
					'limit'        => - 1,
				]
			);

			$online  = 0;
			$offline = 0;

			foreach ( $orders as $order ) {
				$payment_method = $order->get_payment_method();
				$total          = (float) $order->get_total();

				if ( in_array( $payment_method, [ 'cod', 'cheque' ], true ) ) {
					$offline += $total;
				} else {
					$online += $total;
				}
			}

			$data[] = [
				'label'    => $current->format( 'l' ),
				'subLabel' => $current->format( 'd,M y' ),
				'online'   => $online,
				'offline'  => $offline,
			];

			$current->modify( '+1 day' );
		}

		return $data;
	}

	/**
	 * Get monthly revenue.
	 *
	 * @return array
	 */
	private function get_monthly_revenue() {
		$data = [];
		$year = gmdate( 'Y' );

		for ( $month = 1; $month <= 12; $month ++ ) {
			$start_date = sprintf( '%s-%02d-01', $year, $month );
			$end_date   = gmdate( 'Y-m-t', strtotime( $start_date ) );

			$orders = wc_get_orders(
				[
					'status'       => [ 'wc-completed', 'wc-processing' ],
					'type'         => 'shop_order',
					'date_created' => $start_date . '...' . $end_date,
					'limit'        => - 1,
				]
			);

			$online  = 0;
			$offline = 0;

			foreach ( $orders as $order ) {
				$payment_method = $order->get_payment_method();
				$total          = (float) $order->get_total();

				if ( in_array( $payment_method, [ 'cod', 'cheque' ], true ) ) {
					$offline += $total;
				} else {
					$online += $total;
				}
			}

			$data[] = [
				'label'   => gmdate( 'M', strtotime( $start_date ) ),
				'online'  => $online,
				'offline' => $offline,
			];
		}

		return $data;
	}

	/**
	 * Get yearly revenue.
	 *
	 * @return array
	 */
	private function get_yearly_revenue() {
		$data         = [];
		$current_year = (int) gmdate( 'Y' );

		for ( $year = $current_year - 4; $year <= $current_year; $year ++ ) {
			$start_date = $year . '-01-01';
			$end_date   = $year . '-12-31';

			$orders = wc_get_orders(
				[
					'status'       => [ 'wc-completed', 'wc-processing' ],
					'type'         => 'shop_order',
					'date_created' => $start_date . '...' . $end_date,
					'limit'        => - 1,
				]
			);

			$online  = 0;
			$offline = 0;

			foreach ( $orders as $order ) {
				$payment_method = $order->get_payment_method();
				$total          = (float) $order->get_total();

				if ( in_array( $payment_method, [ 'cod', 'cheque' ], true ) ) {
					$offline += $total;
				} else {
					$online += $total;
				}
			}

			$data[] = [
				'label'   => (string) $year,
				'online'  => $online,
				'offline' => $offline,
			];
		}

		return $data;
	}

	/**
	 * Get reservations data.
	 *
	 * @return array
	 */
	private function get_reservations_data() {

		$reservations = [];

		if ( ! tlpFoodMenu()->has_pro() ) {
			return $reservations;
		}

		// Try to get from custom post type.
		$posts = get_posts(
			[
				'post_type'      => 'fmp_reservation',
				'posts_per_page' => 5,
				'orderby'        => 'date',
				'order'          => 'DESC',
				'post_status'    => 'publish',
			]
		);

		if ( ! empty( $posts ) ) {
			foreach ( $posts as $post ) {
				$post_id           = $post->ID;
				$booking_date       = get_post_meta( $post_id, 'fmp_resi_meta_date', true );
				$booking_start_time = str_replace( ' ', '', get_post_meta( $post_id, 'fmp_resi_meta_start_time', true ) );
				$booking_end_time   = str_replace( ' ', '', get_post_meta( $post_id, 'fmp_resi_meta_end_time', true ) );
				$booking_date = is_numeric( $booking_date ) ? (int) $booking_date : strtotime( $booking_date );
				$booking_date = strtotime( gmdate( 'Y-m-d', $booking_date ) );
				$reservations[]    = [
					'id'            => $post_id,
					'customer_name' => get_post_meta( $post_id, '_fmp_customer_name', true ) ?: $post->post_title,
					'booked_seats'  => join( '<br>', FnsPro::get_booked_seats_info( $post_id ) ),
					'time'          => $booking_start_time . ' - ' . $booking_end_time,
					'date'          => date_i18n( get_option( 'date_format' ), strtotime( $booking_date ) ),
				];
			}
		}

		return $reservations;
	}

	/**
	 * Get orders data.
	 *
	 * @return array
	 */
	private function get_orders_data() {
		$orders = [];

		// Try WooCommerce orders first.
		if ( function_exists( 'wc_get_orders' ) ) {
			$wc_orders = wc_get_orders(
				[
					'limit'   => 7,
					'type'    => 'shop_order',
					'orderby' => 'date',
					'order'   => 'DESC',
				]
			);
			foreach ( $wc_orders as $order ) {
				$status         = $order->get_status();
				$payment_method = $order->get_payment_method();
				$service_type   = in_array( $payment_method, [ 'cod', 'cheque' ], true ) ? 'Offline' : 'Online';

				$orders[] = [
					'id'             => $order->get_id(),
					'order_id'       => $order->get_id(),
					'customer_name'  => $order->get_billing_first_name() . ' ' . $order->get_billing_last_name(),
					'customer_email' => $order->get_billing_email(),
					'time'           => $order->get_date_created() ? $order->get_date_created()->date( 'h.ia' ) : '',
					'date'           => $order->get_date_created() ? $order->get_date_created()->date( 'Y-m-d' ) : '',
					'amount'         => $order->get_total(),
					'service_type'   => $service_type,
					'status'         => $status,
				];
			}
		}

		return $orders;
	}
}
