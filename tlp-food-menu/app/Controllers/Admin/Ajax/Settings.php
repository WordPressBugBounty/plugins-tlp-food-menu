<?php
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
		add_action( 'wp_ajax_rt_select2_object_search', [ $this, 'select2_ajax_posts_filter_autocomplete' ] );
	}

	/**
	 * Ajax Response.
	 *
	 * @return void
	 */
	public function response() {
		$error = true;

		if ( wp_verify_nonce( Fns::getNonce(), Fns::nonceText() ) ) {
			unset( $_REQUEST['fmp_nonce'] );
			unset( $_REQUEST['_wp_http_referer'] );
			unset( $_REQUEST['action'] );

			$data  = [];
			$matas = Fns::fmpAllSettingsFields();

			foreach ( $matas as $key => $field ) {
				$rValue       = ! empty( $_REQUEST[ $key ] ) ? $_REQUEST[ $key ] : null;
				$value        = Fns::sanitize( $field, $rValue );
				$data[ $key ] = $value;
			}

			$settings = get_option( TLPFoodMenu()->options['settings'] );

			if ( ! empty( $settings['slug'] ) && $_REQUEST['slug'] && $settings['slug'] !== $_REQUEST['slug'] ) {
				update_option( TLPFoodMenu()->options['flash'], true );
			}
			update_option( TLPFoodMenu()->options['settings'], $data );

			$error = false;
			$msg   = esc_html__( 'Settings successfully updated', 'tlp-food-menu' );
		} else {
			$msg = esc_html__( 'Security Error !!', 'tlp-food-menu' );
		}

		$response = [
			'error' => $error,
			'msg'   => $msg,
		];

		wp_send_json( $response );

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

		if ( ! wp_verify_nonce( Fns::getNonce(), Fns::nonceText() ) ) {
			wp_send_json_error();
		}

		$query_per_page = $_GET['per_page'] ?? 5;
		$post_type      = 'post';
		$source_name    = 'post_type';
		$paged          = $_GET['page'] ?? 1;

		if ( ! empty( $_GET['post_type'] ) ) {
			$post_type = sanitize_text_field( $_GET['post_type'] );
		}

		if ( ! empty( $_GET['source_name'] ) ) {
			$source_name = sanitize_text_field( $_GET['source_name'] );
		}

		$search  = ! empty( $_GET['search'] ) ? sanitize_text_field( $_GET['search'] ) : '';
		$results = $post_list = [];
		switch ( $source_name ) {
			case 'taxonomy':
				$args = [
					'hide_empty' => false,
					'orderby'    => 'name',
					'order'      => 'ASC',
					'search'     => $search,
					'number'     => '5',
				];

				if ( $post_type !== 'all' ) {
					$args['taxonomy'] = $post_type;
				}

				$post_list = wp_list_pluck( get_terms( $args ), 'name', 'term_id' );
				break;
			case 'user':
				$users = [];

				foreach ( get_users( [ 'search' => "*{$search}*" ] ) as $user ) {
					$user_id           = $user->ID;
					$user_name         = $user->display_name;
					$users[ $user_id ] = $user_name;
				}

				$post_list = $users;
				break;
			default:
				$post_list = $this->get_query_data( $post_type, $query_per_page, $search, $paged );
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
	public function get_query_data( $post_type = 'any', $limit = 10, $search = '', $paged = 1 ) {
		global $wpdb;
		$where = '';
		$data  = [];

		if ( -1 == $limit ) {
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
