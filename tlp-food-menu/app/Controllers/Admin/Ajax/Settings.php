<?php
// phpcs:disable WordPress.NamingConventions.PrefixAllGlobals -- Established plugin: public namespace, hook names, functions and theme-overridable template variables must stay unchanged for backward compatibility.
/**
 * Settings Ajax Class.
 *
 * @package RT_FoodMenu
 */

namespace RT\FoodMenu\Controllers\Admin\Ajax;

use RT\FoodMenu\Helpers\Fns;

// Do not allow directly accessing this file.
if ( ! defined( 'ABSPATH' ) ) {
	exit( 'This script cannot be accessed directly.' );
}

/**
 * Settings Ajax Class.
 */
class Settings {

	use \RT\FoodMenu\Traits\SingletonTrait;

	private $form_setting;

	/**
	 * Class Init.
	 *
	 * @return void
	 */
	protected function init() {
		add_action( 'wp_ajax_fmpSettingsUpdate', [ $this, 'response' ] );
		add_action( 'wp_ajax_fmpNewSettingsUpdate', [ $this, 'new_settings_response' ] );
		add_action( 'wp_ajax_rt_select2_object_search', [ $this, 'select2_ajax_posts_filter_autocomplete' ] );
	}

	/**
	 * Fields that require a page refresh when changed.
	 */
	private static $refresh_fields = [
		'fm_food_menu_type',
		'fmp_enable_frontend_inventory',
		'fmp_enable_reservation',
		'fmp_enable_frontend_order',
		'fmp_food_location_popup',
		'fmp_food_reservation_status',
		'fmp_enable_product_addons',
	];

	protected function should_refresh_if_change( array $new_data ): bool {
		$old_settings = get_option( TLPFoodMenu()->options['settings'], [] );

		foreach ( self::$refresh_fields as $key ) {
			$old_value = isset( $old_settings[ $key ] ) ? (string) $old_settings[ $key ] : '';
			$new_value = isset( $new_data[ $key ] ) ? (string) $new_data[ $key ] : '';

			if ( $old_value !== $new_value ) {
				return true;
			}
		}

		return false;
	}

	/**
	 * Ajax Response.
	 *
	 * @return void
	 */
	public function response() {
		$error = true;

		$new_data = [
			'fmp_enable_frontend_order'     => isset( $_REQUEST['fmp_enable_frontend_order'] ) ? sanitize_text_field( wp_unslash( $_REQUEST['fmp_enable_frontend_order'] ) ) : '',
			'fmp_enable_frontend_inventory' => isset( $_REQUEST['fmp_enable_frontend_inventory'] ) ? sanitize_text_field( wp_unslash( $_REQUEST['fmp_enable_frontend_inventory'] ) ) : '',
			'fmp_food_reservation_status'   => isset( $_REQUEST['fmp_food_reservation_status'] ) ? sanitize_text_field( wp_unslash( $_REQUEST['fmp_food_reservation_status'] ) ) : '',
		];

		$should_refresh = $this->should_refresh_if_change( $new_data );

		if ( ! current_user_can( 'manage_options' ) ) {
			$response = [
				'error' => true,
				'msg'   => 'You are not allowed to modify settings',
			];

			wp_send_json( $response );

			die();
		}

		if ( wp_verify_nonce( Fns::getNonce(), Fns::nonceText() ) ) {
			unset( $_REQUEST['fmp_nonce'] );
			unset( $_REQUEST['_wp_http_referer'] );
			unset( $_REQUEST['action'] );

			$data  = [];
			$matas = Fns::fmpAllSettingsFields();

			//phpcs:disable
			foreach ( $matas as $key => $field ) {
				/**
				 * Old code before sanitization. Should remove later if everything is fine
				 * $rValue = ! empty( $_REQUEST[ $key ] ) ? $_REQUEST[ $key ] : null;
				 */

				$rValue = '';
				if ( ! empty( $_REQUEST[ $key ] ) ) {
					$rValue = Fns::sanitize_recursive_array( wp_unslash( $_REQUEST[ $key ] ) );
				}
				$value        = Fns::sanitize( $field, $rValue );
				$data[ $key ] = $value;
			}

			$settings = get_option( TLPFoodMenu()->options['settings'] );

			if ( ! empty( $settings['slug'] ) && $_REQUEST['slug'] && $settings['slug'] !== $_REQUEST['slug'] ) {
				update_option( TLPFoodMenu()->options['flash'], true );
			}

			// Preserve new schedule keys that are not in the old whitelist.
			$preserve_keys = [
				'new_fmp_pickup_weekly_schedule',
				'new_fmp_delivery_weekly_schedule',
				'new_fmp_dinein_weekly_schedule',
				'new_fmp_resi_weekly_schedule',
				'fmp_dinein_tables',
			];

			foreach ( $preserve_keys as $pkey ) {
				if ( ! isset( $data[ $pkey ] ) && isset( $settings[ $pkey ] ) ) {
					$data[ $pkey ] = $settings[ $pkey ];
				}
			}

			update_option( TLPFoodMenu()->options['settings'], $data );

			$error = false;
			$msg   = esc_html__( 'Settings successfully updated', 'tlp-food-menu' );
			//phpcs:enable
		} else {
			$msg = esc_html__( 'Security Error !!', 'tlp-food-menu' );
		}

		$response = [
			'error'          => $error,
			'msg'            => $msg,
			'should_refresh' => $should_refresh ? 'YES' : 'NO',
		];

		wp_send_json( $response );

		die();
	}

	/**
	 * New Settings Ajax Response.
	 * Saves all submitted settings independently without the PHP whitelist.
	 *
	 * @return void
	 */
	public function new_settings_response() {
		if ( ! current_user_can( 'manage_options' ) ) {
			wp_send_json(
				[
					'error' => true,
					'msg'   => 'You are not allowed to modify settings',
				]
			);
			die();
		}

		if ( ! wp_verify_nonce( Fns::getNonce(), Fns::nonceText() ) ) {
			wp_send_json(
				[
					'error' => true,
					'msg'   => esc_html__( 'Security Error !!', 'tlp-food-menu' ),
				]
			);
			die();
		}

		// phpcs:disable
		$skip_keys = [ 'action', 'fmp_nonce', '_wp_http_referer' ];

		$new_data = [];
		foreach ( $_REQUEST as $key => $value ) {
			if ( in_array( $key, $skip_keys, true ) ) {
				continue;
			}
			$new_data[ sanitize_text_field( $key ) ] = Fns::sanitize_recursive_array( wp_unslash( $value ) );
		}

		$should_refresh = $this->should_refresh_if_change( $new_data );

		// Merge with existing settings so old settings are preserved.
		$existing = get_option( TLPFoodMenu()->options['settings'], [] );
		$data     = array_merge( $existing, $new_data );

		/**
		 * Keys the general settings form must never write. These are owned by
		 * dedicated, server-validated endpoints (e.g. license activation) — the
		 * `readonly` attribute on the field is only a UI hint and can be removed
		 * client-side, so we protect them here on the server. Any posted value
		 * for these keys is discarded in favour of the stored value.
		 *
		 * @param array $keys Protected option keys.
		 */
		$protected_keys = apply_filters( 'fmp_settings_protected_keys', [ 'license_key', 'license_status' ] );

		foreach ( $protected_keys as $protected_key ) {
			if ( array_key_exists( $protected_key, $existing ) ) {
				$data[ $protected_key ] = $existing[ $protected_key ];
			} else {
				unset( $data[ $protected_key ] );
			}
		}

		update_option( TLPFoodMenu()->options['settings'], $data );

		/**
		 * Fires after the settings are saved via AJAX, regardless of whether the value actually changed.
		 *
		 * Unlike `update_option_{name}` (which only fires on actual change), this action always runs
		 * when the user clicks Save. Useful for syncs that need to stay consistent with current settings
		 * even if WordPress thinks nothing changed.
		 *
		 * @param array $existing Previous settings value.
		 * @param array $data     New settings value (merged).
		 */
		do_action( 'fmp_settings_saved', $existing, $data );
		// phpcs:enable

		wp_send_json(
			[
				'error'          => false,
				'msg'            => esc_html__( 'Settings successfully updated', 'tlp-food-menu' ),
				'should_refresh' => $should_refresh ? 'YES' : 'NO',
			]
		);
		die();
	}

	/**
	 * Sanitize field
	 *
	 * @param array $form_setting .
	 */
	public function fmp_sanitize( $form_setting ) {
		foreach ( $form_setting as $key => $value ) {
			$this->form_setting[ $key ] = $value;
		}
	}

	/**
	 * Ajax callback for rt-select2
	 *
	 * @return void
	 */
	public function select2_ajax_posts_filter_autocomplete() {

		if ( ! current_user_can( 'manage_options' ) ) {
			wp_send_json_error();
		}

		if ( ! wp_verify_nonce( Fns::getNonce(), Fns::nonceText() ) ) {
			wp_send_json_error();
		}

		$query_per_page = ! empty( $_GET['per_page'] ) ? absint( $_GET['per_page'] ) : 5;
		$post_type      = 'post';
		$source_name    = 'post_type';
		$paged          = ! empty( $_GET['page'] ) ? absint( $_GET['page'] ) : 1;

		if ( ! empty( $_GET['post_type'] ) ) {
			$post_type = sanitize_text_field( wp_unslash( $_GET['post_type'] ) );
		}

		if ( ! empty( $_GET['source_name'] ) ) {
			$source_name = sanitize_text_field( wp_unslash( $_GET['source_name'] ) );
		}

		$search  = ! empty( $_GET['search'] ) ? sanitize_text_field( wp_unslash( $_GET['search'] ) ) : '';
		$include = ! empty( $_GET['include'] ) ? array_map( 'absint', explode( ',', sanitize_text_field( wp_unslash( $_GET['include'] ) ) ) ) : [];
		// Optional WC product type restriction (e.g. 'simple'). Empty = no filter.
		$product_type = ! empty( $_GET['product_type'] ) ? sanitize_text_field( wp_unslash( $_GET['product_type'] ) ) : '';
		$results = $post_list = [];
		switch ( $source_name ) {
			case 'taxonomy':
				$args = [
					'hide_empty' => false,
					'orderby'    => 'name',
					'order'      => 'ASC',
					'search'     => $search,
					'number'     => $query_per_page,
				];

				if ( ! empty( $include ) ) {
					$args['include'] = $include;
					$args['number']  = 0;
					unset( $args['search'] );
				}

				if ( $post_type !== 'all' ) {
					$args['taxonomy'] = $post_type;
				}

				$post_list = wp_list_pluck( get_terms( $args ), 'name', 'term_id' );
				break;
			case 'user':
				$user_args = [
					'number' => $query_per_page,
					'paged'  => $paged,
					'fields' => [ 'ID', 'display_name', 'user_email' ],
				];

				if ( ! empty( $include ) ) {
					$user_args['include'] = $include;
					$user_args['number']  = count( $include );
					unset( $user_args['paged'] );
				} elseif ( ! empty( $search ) ) {
					$user_args['search']         = "*{$search}*";
					$user_args['search_columns'] = [ 'user_login', 'user_email', 'display_name' ];
				}

				$users = get_users( $user_args );

				foreach ( $users as $user ) {
					$post_list[ $user->ID ] = sprintf( '%s (%s) — #%d', $user->display_name, $user->user_email, $user->ID );
				}

				break;
			default:
				if ( ! empty( $include ) ) {
					$get_posts_args = [
						'post_type'      => $post_type,
						'post__in'       => $include,
						'posts_per_page' => count( $include ),
						'post_status'    => 'publish',
					];

					if ( ! empty( $product_type ) ) {
						$get_posts_args['tax_query'] = [ //phpcs:ignore WordPress.DB.SlowDBQuery.slow_db_query_tax_query
							[
								'taxonomy' => 'product_type',
								'field'    => 'slug',
								'terms'    => $product_type,
							],
						];
					}

					$posts = get_posts( $get_posts_args );
					foreach ( $posts as $p ) {
						$post_list[ $p->ID ] = $p->post_title;
					}
				} else {
					$post_list = $this->get_query_data( $post_type, $query_per_page, $search, $paged, $product_type );
				}
		}

		$pagination = true;
		if ( count( $post_list ) < $query_per_page ) {
			$pagination = false;
		}
		if ( ! empty( $post_list ) ) {
			foreach ( $post_list as $key => $item ) {
				$results[] = [
					'text' => $item,
					'id'   => $key,
				];
			}
		}
		wp_send_json(
			[
				'results'    => $results,
				'pagination' => [ 'more' => $pagination ],
			]
		);
	}

	/**
	 * Ajax callback for rt-select2
	 *
	 * @param string $post_type .
	 * @param number $limit .
	 * @param string $search .
	 * @param number $paged ..
	 *
	 * @return array
	 */
	public function get_query_data( $post_type = 'any', $limit = 10, $search = '', $paged = 1, $product_type = '' ) {
		global $wpdb;
		$where = '';
		$data  = [];

		if ( - 1 == $limit ) {
			$limit = '';
		} elseif ( 0 == $limit ) {
			$limit = 'limit 0,1';
		} else {
			$offset = 0;
			if ( $paged ) {
				$offset = ( $paged - 1 ) * $limit;
			}
			$limit = $wpdb->prepare( ' limit %d, %d', esc_sql( $offset ), esc_sql( $limit ) );
		}

		if ( 'any' === $post_type ) {
			$in_search_post_types = get_post_types( [ 'exclude_from_search' => false ] );
			if ( empty( $in_search_post_types ) ) {
				$where .= ' AND 1=0 ';
			} else {
				$where .= " AND {$wpdb->posts}.post_type IN ('" . join(
						"', '",
						array_map( 'esc_sql', $in_search_post_types )
					) . "')";
			}
		} elseif ( ! empty( $post_type ) ) {
			$where .= $wpdb->prepare( " AND {$wpdb->posts}.post_type = %s", esc_sql( $post_type ) );
		}

		if ( ! empty( $search ) ) {
			$where .= $wpdb->prepare( " AND {$wpdb->posts}.post_title LIKE %s", '%' . esc_sql( $search ) . '%' );
		}

		// Restrict to a WC product type (e.g. 'simple') via the product_type taxonomy.
		if ( ! empty( $product_type ) ) {
			$where .= $wpdb->prepare(
				" AND {$wpdb->posts}.ID IN (
					SELECT tr.object_id FROM {$wpdb->term_relationships} tr
					INNER JOIN {$wpdb->term_taxonomy} tt ON tr.term_taxonomy_id = tt.term_taxonomy_id
					INNER JOIN {$wpdb->terms} t ON tt.term_id = t.term_id
					WHERE tt.taxonomy = 'product_type' AND t.slug = %s
				)",
				$product_type
			);
		}

		$query   = "select post_title,ID  from $wpdb->posts where post_status = 'publish' {$where} {$limit}";
		$results = $wpdb->get_results( $query ); //phpcs:ignore

		if ( ! empty( $results ) ) {
			foreach ( $results as $row ) {
				$data[ $row->ID ] = $row->post_title . ' [#' . $row->ID . ']';
			}
		}

		return $data;
	}
}
