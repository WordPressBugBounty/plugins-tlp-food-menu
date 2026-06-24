<?php

namespace RT\FoodMenu\Controllers\Reservation;

defined( 'ABSPATH' ) || exit();

use RT\FoodMenu\Traits\SingletonTrait;
use RT\FoodMenu\Helpers\Fns;

/**
 * Reservation admin list table customizations.
 */
class ReservationTableList {

	use SingletonTrait;

	public $dateFormat;

	/**
	 * Init.
	 *
	 * @return void
	 */
	public function init() {
		add_action( 'pre_get_posts', [ $this, 'query_modify' ], 9999 );
		add_action( 'manage_fmp_reservation_posts_custom_column', [ $this, 'reservation_custom_column' ], 10, 2 );
		add_filter( 'manage_fmp_reservation_posts_columns', [ $this, 'reservation_post_column' ] );
		add_filter( 'page_row_actions', [ $this, 'post_row_actions' ], 10, 2 );
		add_filter( 'manage_edit-fmp_reservation_sortable_columns', [ $this, 'make_reservation_date_sortable' ] );
		add_filter( 'fmp/resi/reservation_details', [ $this, 'reservation_details' ] );
		add_action( 'restrict_manage_posts', [ $this, 'add_reservation_filter_dropdown' ] );
		add_action( 'admin_head', [ $this, 'reservation_table_list_css' ] );
		add_action( 'admin_head', [ $this, 'remove_admin_notices_on_reservation_page' ] );
		add_filter( 'bulk_actions-edit-fmp_reservation', [ $this, 'remove_bulk_edit' ] );
		add_filter( 'wp_untrash_post_status', [ $this, 'force_reservation_untrash_status' ], 10, 3 );

		$this->dateFormat = get_option( 'date_format' );
	}

	/**
	 * Force restored reservations back to publish.
	 *
	 * @param string $new_status .
	 * @param int    $post_id .
	 * @param string $previous_status .
	 *
	 * @return string
	 */
	public function force_reservation_untrash_status( $new_status, $post_id, $previous_status ) {
		if ( 'fmp_reservation' === get_post_type( $post_id ) ) {
			return 'publish';
		}

		return $new_status;
	}

	public function query_modify( $query ) {
		if ( ! is_admin() || ! $query->is_main_query() || 'fmp_reservation' !== $query->get( 'post_type' ) ) {
			return;
		}

		if ( ! isset( $_GET['orderby'] ) ) {
			$query->set( 'orderby', 'date' );
			$query->set( 'order', 'DESC' );
		}

		$meta_query = [];

		//phpcs:ignore WordPress.Security.NonceVerification.Recommended
		if ( ! empty( $_GET['location_filter'] ) ) {
			$meta_query[] = [
				'key'     => 'fmp_resi_meta_location',
				'value'   => absint( $_GET['location_filter'] ),
				'compare' => '=',
			];
		}

		if ( ! empty( $_GET['status_meta_filter'] ) ) {
			$status       = sanitize_text_field( wp_unslash( $_GET['status_meta_filter'] ) );
			$meta_query[] = [
				'key'     => 'fmp_resi_meta_status',
				'value'   => $status,
				'compare' => '=',
			];
		}

		if ( ! empty( $_GET['created_date_start'] ) && ! empty( $_GET['created_date_end'] ) ) {
			$start_date = sanitize_text_field( wp_unslash( $_GET['created_date_start'] ) );
			$end_date   = sanitize_text_field( wp_unslash( $_GET['created_date_end'] ) );

			$start_date = date( 'Y-m-d', strtotime( $start_date ) );
			$end_date   = date( 'Y-m-d', strtotime( $end_date ) );

			$query->set( 'date_query', [
				[
					'after'     => $start_date,
					'before'    => $end_date,
					'inclusive' => true,
				],
			] );
		}

		if ( ! empty( $_GET['booking_date_start'] ) && ! empty( $_GET['booking_date_end'] ) ) {
			$start_date = strtotime( sanitize_text_field( wp_unslash( $_GET['booking_date_start'] ) ) );
			$end_date   = strtotime( sanitize_text_field( wp_unslash( $_GET['booking_date_end'] ) ) );

			$meta_query[] = [
				'relation' => 'AND',
				[
					'key'     => 'fmp_resi_meta_date',
					'value'   => $start_date,
					'compare' => '>=',
					'type'    => 'CHAR',
				],
				[
					'key'     => 'fmp_resi_meta_date',
					'value'   => $end_date,
					'compare' => '<=',
					'type'    => 'CHAR',
				],
			];
		}

		if ( ! empty( $meta_query ) ) {
			$query->set( 'meta_query', $meta_query );
		}
	}

	/**
	 * Filter dropdowns on the reservation list.
	 *
	 * @return void
	 */
	public function add_reservation_filter_dropdown() {
		global $post_type, $wpdb, $pagenow;

		if ( 'fmp_reservation' !== $post_type || 'edit.php' !== $pagenow ) {
			return;
		}

		$status_meta_key    = 'fmp_resi_meta_status';
		$status_meta_values = $wpdb->get_col(
			$wpdb->prepare(
				"SELECT DISTINCT meta_value FROM {$wpdb->postmeta} WHERE meta_key = %s",
				$status_meta_key
			)
		);
		$status_meta_values = array_filter( $status_meta_values );

		//phpcs:ignore WordPress.Security.NonceVerification.Recommended
		$status_meta_filter = ! empty( $_GET['status_meta_filter'] ) ? sanitize_text_field( wp_unslash( $_GET['status_meta_filter'] ) ) : '';

		if ( $status_meta_values ) {
			echo '<select name="status_meta_filter">';
			echo '<option value="">' . esc_html__( 'Status', 'tlp-food-menu' ) . '</option>';
			foreach ( $status_meta_values as $status ) {
				$selected = selected( $status_meta_filter, $status, false );
				echo sprintf(
					'<option value="%s" %s>%s</option>',
					esc_attr( $status ),
					esc_attr( $selected ),
					esc_html( $status )
				);
			}
			echo '</select>';
		}

		$locations = get_terms( [
			'taxonomy'   => 'tpl-food-location',
			'hide_empty' => false,
		] );
		//phpcs:ignore WordPress.Security.NonceVerification.Recommended
		$selected_location = ! empty( $_GET['location_filter'] ) ? absint( $_GET['location_filter'] ) : '';

		if ( ! is_wp_error( $locations ) && ! empty( $locations ) ) {
			echo '<select name="location_filter">';
			echo '<option value="">' . esc_html__( 'All Locations', 'tlp-food-menu' ) . '</option>';
			foreach ( $locations as $loc ) {
				$sel = selected( $selected_location, $loc->term_id, false );
				echo sprintf(
					'<option value="%s" %s>%s</option>',
					esc_attr( $loc->term_id ),
					esc_attr( $sel ),
					esc_html( $loc->name )
				);
			}
			echo '</select>';
		}

		$creating_date_start = ! empty( $_GET['created_date_start'] ) ? sanitize_text_field( wp_unslash( $_GET['created_date_start'] ) ) : '';
		$creating_date_end   = ! empty( $_GET['created_date_end'] ) ? sanitize_text_field( wp_unslash( $_GET['created_date_end'] ) ) : '';
		?>
		<div class="fmp-filter-date-picker-range-wrap">
			<span class="date-label"><?php echo esc_html__( 'Created Date', 'tlp-food-menu' ); ?></span>
			<input type="text" name="created_date_start" class="fmp_date_start" value="<?php echo esc_attr( $creating_date_start ); ?>"
				   placeholder="<?php esc_attr_e( 'Start date', 'tlp-food-menu' ); ?>" id="created_date_start"/>
			<input type="text" name="created_date_end" class="fmp_date_end" value="<?php echo esc_attr( $creating_date_end ); ?>"
				   placeholder="<?php esc_attr_e( 'End date', 'tlp-food-menu' ); ?>" id="created_date_end">
		</div>
		<?php
		$selected_booking_date_start = ! empty( $_GET['booking_date_start'] ) ? sanitize_text_field( wp_unslash( $_GET['booking_date_start'] ) ) : '';
		$selected_booking_date_end   = ! empty( $_GET['booking_date_end'] ) ? sanitize_text_field( wp_unslash( $_GET['booking_date_end'] ) ) : '';
		?>
		<div class="fmp-filter-date-picker-range-wrap">
			<span class="date-label"><?php echo esc_html__( 'Reservation Date', 'tlp-food-menu' ); ?></span>
			<input type="text" name="booking_date_start" class="fmp_date_start" value="<?php echo esc_attr( $selected_booking_date_start ); ?>"
				   placeholder="<?php esc_attr_e( 'Start date', 'tlp-food-menu' ); ?>" id="booking_date_start"/>
			<input type="text" name="booking_date_end" class="fmp_date_end" value="<?php echo esc_attr( $selected_booking_date_end ); ?>"
				   placeholder="<?php esc_attr_e( 'End date', 'tlp-food-menu' ); ?>" id="booking_date_end">
		</div>
		<?php
	}

	/**
	 * Reservation details panel renderer.
	 *
	 * @return false|void
	 */
	public function reservation_details() {
		if ( ! is_admin() || empty( $_GET['action'] ) || 'resi_details' !== sanitize_text_field( wp_unslash( $_GET['action'] ) ) ) {
			return false;
		}

		if ( empty( $_REQUEST['_wpnonce'] ) || ! wp_verify_nonce( sanitize_text_field( wp_unslash( $_REQUEST['_wpnonce'] ) ), 'fmp-resi-details' ) ) {
			return false;
		}

		$id       = ! empty( $_GET['post_id'] ) ? absint( $_GET['post_id'] ) : 0;
		$resi_arr = Fns::reservation_fields_array();

		Fns::print_html( $this->generate_reservation_details_html( $id, $resi_arr ) );
	}

	/**
	 * @param int   $id .
	 * @param array $resi_arr .
	 *
	 * @return string
	 */
	private function generate_reservation_details_html( $id, $resi_arr ) {
		ob_start();
		?>
		<div class="fmp-reservation-details-wrap">
			<h2 class="fmp-page-heading"><?php echo esc_html__( 'Reservation Details', 'tlp-food-menu' ); ?></h2>
			<div class="fmp-reservation-details">
				<table>
					<?php
					foreach ( $resi_arr as $key => $value ) :
						$field = get_post_meta( $id, $key, true );
						if ( ! empty( $field ) ) :
							?>
							<tr>
								<td>
									<strong><?php echo esc_html( $value ); ?></strong>
								</td>
								<td>
									<?php
									if ( 'fmp_resi_meta_seat' === $key ) {
										$total_seat        = esc_html__( 'Total Guest: ', 'tlp-food-menu' ) . $field . '<br>';
										$booked_seats_info = apply_filters( 'fmp/reservation/booked_seats_info', [], (int) $id );
										if ( ! empty( $booked_seats_info ) && is_array( $booked_seats_info ) ) {
											$total_seat .= join( '; <br>', $booked_seats_info );
										}
										Fns::print_html( $total_seat );
									} else {
										echo esc_html( $field );
									}
									?>
								</td>
							</tr>
						<?php
						endif;
					endforeach;
					?>
				</table>
			</div>
		</div>
		<?php
		return ob_get_clean();
	}

	/**
	 * Strip row actions we don't support.
	 *
	 * @param array  $actions .
	 * @param object $post .
	 *
	 * @return array
	 */
	public function post_row_actions( $actions, $post ) {
		if ( 'fmp_reservation' === $post->post_type ) {
			unset( $actions['view'] );
			unset( $actions['inline hide-if-no-js'] );
		}

		return $actions;
	}

	/**
	 * Custom column set.
	 *
	 * @param array $columns .
	 *
	 * @return array
	 */
	public function reservation_post_column( $columns ) {
		unset( $columns['date'] );
		unset( $columns['title'] );
		$columns['fmp_meta_invoice']  = esc_html__( 'Booking ID', 'tlp-food-menu' );
		$columns['fmp_created_date']  = esc_html__( 'Created Date', 'tlp-food-menu' );
		$columns['fmp_meta_date']     = esc_html__( 'Reservation Date', 'tlp-food-menu' );
		$columns['fmp_meta_location'] = esc_html__( 'Location', 'tlp-food-menu' );
		$columns['fmp_meta_name']     = esc_html__( 'Contact Details', 'tlp-food-menu' );
		$columns['fmp_meta_seat']     = esc_html__( 'Seat', 'tlp-food-menu' );
		$columns['fmp_meta_message']  = esc_html__( 'Message', 'tlp-food-menu' );
		$columns['fmp_meta_status']   = esc_html__( 'Status', 'tlp-food-menu' );

		return apply_filters( 'fmp/reservation/column_extra_field_title', $columns );
	}

	public function make_reservation_date_sortable( $columns ) {
		$columns['fmp_meta_date']    = 'fmp_meta_date';
		$columns['fmp_created_date'] = 'fmp_created_date';

		return $columns;
	}

	/**
	 * Custom column values.
	 *
	 * @param string $column .
	 * @param int    $post_id .
	 *
	 * @return void
	 */
	public function reservation_custom_column( $column, $post_id ) {
		switch ( $column ) {
			case 'fmp_meta_location':
				$location_id = get_post_meta( $post_id, 'fmp_resi_meta_location', true );
				if ( $location_id ) {
					$term = get_term( absint( $location_id ), 'tpl-food-location' );
					if ( $term && ! is_wp_error( $term ) ) {
						echo esc_html( $term->name );
					} else {
						echo '—';
					}
				} else {
					echo '—';
				}
				break;
			case 'fmp_meta_invoice':
				$booking_id = get_post_meta( $post_id, 'fmp_resi_meta_invoice', true );
				printf( "<input style='background:#FFF;border-color: #a9a9a9;width: 110px;text-align: center;padding: 0 3px;' type='text' onclick='copyText(this)' readonly value='%s'>", esc_html( $booking_id ) );
				break;
			case 'fmp_meta_name':
				echo '<strong>';
				echo esc_html( get_post_meta( $post_id, 'fmp_resi_meta_name', true ) );
				echo '</strong>';
				echo '<br>';

				$email = get_post_meta( $post_id, 'fmp_resi_meta_email', true );
				if ( ! empty( $email ) ) {
					echo '<a href="mailto:' . esc_attr( $email ) . '">' . esc_html( $email ) . '</a>';
				}
				echo '<br>';

				$phone = get_post_meta( $post_id, 'fmp_resi_meta_phone', true );
				if ( ! empty( $phone ) ) {
					echo '<a href="tel:' . esc_attr( $phone ) . '">' . esc_html( $phone ) . '</a>';
				}
				break;
			case 'fmp_created_date':
				echo esc_html( get_the_date( $this->dateFormat, $post_id ) );
				echo '<br>';
				echo esc_html( get_the_date( 'h:i A', $post_id ) );
				break;
			case 'fmp_meta_date':
				$booking_date       = get_post_meta( $post_id, 'fmp_resi_meta_date', true );
				$booking_start_time = str_replace( ' ', '', get_post_meta( $post_id, 'fmp_resi_meta_start_time', true ) );
				$booking_end_time   = str_replace( ' ', '', get_post_meta( $post_id, 'fmp_resi_meta_end_time', true ) );

				$today_date   = strtotime( date( 'Y-m-d' ) );
				$booking_date = is_numeric( $booking_date ) ? (int) $booking_date : strtotime( $booking_date );
				$booking_date = strtotime( date( 'Y-m-d', $booking_date ) );
				printf(
					"<div class='booking-date'>%s %s</div>",
					$booking_date === $today_date ? '<span class="fmp-current-time"></span>' : '',
					esc_html( date_i18n( $this->dateFormat, $booking_date ) )
				);
				Fns::print_html( $booking_start_time . ' - ' . $booking_end_time );
				break;
			case 'fmp_meta_seat':
				$total_seat        = get_post_meta( $post_id, 'fmp_resi_meta_seat', true );
				$booked_seats_info = apply_filters( 'fmp/reservation/booked_seats_info', [], (int) $post_id );

				echo "<div class='fmp-res-seats'>";
				$total_seat_html = sprintf(
					"<div class='total-guest'><span>%s <strong>%s</strong></span></div>",
					esc_html__( 'Total Guest: ', 'tlp-food-menu' ),
					esc_html( $total_seat )
				);
				if ( ! empty( $booked_seats_info ) && is_array( $booked_seats_info ) ) {
					$total_seat_html .= join( '<br>', $booked_seats_info );
				}
				Fns::print_html( $total_seat_html );
				echo '</div>';
				break;
			case 'fmp_meta_message':
				$message = get_post_meta( $post_id, 'fmp_resi_meta_message', true );
				echo esc_html( wp_trim_words( $message, 33, '...' ) );
				break;
			case 'fmp_meta_status':
				$status           = get_post_meta( $post_id, 'fmp_resi_meta_status', true );
				$status_class     = strtolower( str_replace( ' ', '', $status ) );
				$available_status = Fns::get_reservation_status();

				echo "<span class='fmp-status " . esc_attr( $status_class ) . "'>";
				echo "<select class='fmp_resi_meta_status_change_table' style='margin:5px 0 0' name='fmp_resi_meta_status' data-pid='" . esc_attr( $post_id ) . "'>";
				foreach ( $available_status as $key => $value ) {
					$selected = $status === $key ? 'selected' : '';
					printf(
						"<option value='%s' %s>%s</option>",
						esc_attr( $key ),
						esc_attr( $selected ),
						esc_html( $value )
					);
				}
				echo '</select>';
				echo '</span>';

				break;
		}
	}

	public function reservation_table_list_css() {
		$screen = get_current_screen();
		if ( $screen && 'fmp_reservation' === $screen->post_type && 'edit' === $screen->base ) {
			echo '<style>
            @media (min-width: 1800px){
                .column-fmp_meta_invoice { width: 220px; }
                .column-fmp_meta_date { width: 220px; }
                .column-fmp_created_date { width: 220px; }
                .column-fmp_meta_status { width: 140px; }
                .column-fmp_meta_name { width: 260px; }
                .column-fmp_meta_invoice { width: 130px; }
                .column-fmp_meta_message { width: 300px; }
            }
            </style>';
		}
	}

	/**
	 * Remove bulk edit, keep trash.
	 *
	 * @param array $actions .
	 *
	 * @return array
	 */
	public function remove_bulk_edit( $actions ) {
		unset( $actions['edit'] );

		return $actions;
	}

	public function remove_admin_notices_on_reservation_page() {
		//phpcs:ignore WordPress.Security.NonceVerification.Recommended
		if ( ! empty( $_GET['post_type'] ) && 'fmp_reservation' === $_GET['post_type'] ) {
			remove_all_actions( 'admin_notices' );
			remove_all_actions( 'all_admin_notices' );
		}
	}
}
