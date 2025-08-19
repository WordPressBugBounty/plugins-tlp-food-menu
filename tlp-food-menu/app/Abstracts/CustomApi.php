<?php

namespace RT\FoodMenu\Abstracts;

defined( 'ABSPATH' ) || exit;

/**
 * Custom api create class
 */
abstract class CustomApi {
	/**
	 * Prefix
	 *
	 * @var string
	 */
	protected $prefix = '';

	/**
	 * Param
	 *
	 * @var string
	 */
	protected $param = '';

	/**
	 * Request data
	 *
	 * @var array
	 */
	protected $request = null;

	/**
	 * Construct function
	 */
	public function __construct() {
		$this->config();
		$this->init();
	}

	/**
	 * Config set param.
	 *
	 * @return mixed
	 */
	abstract public function config();

	/**
	 * Initial function
	 *
	 * @return void
	 */
	public function init() {
		add_action( 'rest_api_init', [ $this,'register_api_route' ] );
	}

	/**
	 * Register api route
	 *
	 * @return void
	 */
	public function register_api_route() {

		$namespace = untrailingslashit( 'fmp/' . $this->prefix );

		// Define the base route for your custom endpoint.
		$base_route = '/(?P<action>\w+)/' . ltrim( $this->param, '/' );

		// Define the options for your custom endpoint.
		$args = [
			'methods'             => \WP_REST_Server::ALLMETHODS,
			'callback'            => [ $this,'handle_custom_request' ],
			'permission_callback' => '__return_true',
		];

		register_rest_route( $namespace, $base_route, $args );
	}

	/**
	 * Route callback function
	 *
	 * @param array $request .
	 * @return method
	 */
	public function handle_custom_request( $request ) {
		$this->request   = $request;
		$is_action_table = 'table_option' == $this->request->get_params()['action'];

		if ( $is_action_table ) {
			$this->request->set_header( 'X-WP-Nonce', wp_create_nonce( 'wp_rest' ) );
		}

		$method_name = strtolower( $this->request->get_method() ) . '_' . $this->request['action'];

		if ( method_exists( $this, $method_name ) ) {
			return $this->{$method_name}();
		}
	}


}
