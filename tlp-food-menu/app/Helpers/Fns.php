<?php
// phpcs:disable WordPress.NamingConventions.PrefixAllGlobals -- Established plugin: public namespace, hook names, functions and theme-overridable template variables must stay unchanged for backward compatibility.
/**
 * Helpers class.
 *
 * @package RT_FoodMenu
 */

namespace RT\FoodMenu\Helpers;

use RT\FoodMenu\Models\Fields;
use RT\FoodMenu\Models\ReSizer;
use RT\FoodMenu\Controllers\MiniCart\MiniCartFns;

// Do not allow directly accessing this file.
if ( ! defined( 'ABSPATH' ) ) {
	exit( 'This script cannot be accessed directly.' );
}
//phpcs:disable PluginCheck.CodeAnalysis.ImageFunctions.NonEnqueuedImage

/**
 * Helpers class.
 */
class Fns {

	/**
	 * Classes instatiation.
	 *
	 * @param array $classes Classes to init.
	 *
	 * @return void
	 */
	public static function instances( array $classes ) {
		if ( empty( $classes ) ) {
			return;
		}

		foreach ( $classes as $class ) {
			$class::get_instance();
		}
	}

	/**
	 * Nonce verify upon activity
	 *
	 * @return bool
	 */
	public static function verifyNonce() {
		$nonce     = isset( $_REQUEST[ self::nonceId() ] ) ? sanitize_text_field( wp_unslash( $_REQUEST[ self::nonceId() ] ) ) : null;
		$nonceText = self::nonceText();

		if ( ! wp_verify_nonce( $nonce, $nonceText ) ) {
			return false;
		}

		return true;
	}

	public static function get_setting( $name, $default = '' ) {
		$settings = get_option( TLPFoodMenu()->options['settings'], [] );

		if ( ! is_array( $settings ) ) {
			return $default;
		}

		return array_key_exists( $name, $settings ) ? $settings[ $name ] : $default;
	}

	/**
	 * Get Nonce
	 *
	 * @return string|null
	 */
	public static function getNonce() {
		// phpcs:ignore WordPress.Security.NonceVerification.Recommended
		return isset( $_REQUEST[ self::nonceID() ] ) ? sanitize_text_field( wp_unslash( $_REQUEST[ self::nonceID() ] ) ) : null;
	}

	/**
	 * Generate nonce text
	 *
	 * @return string
	 */
	public static function nonceText() {
		return 'fmp_nonce_secret';
	}

	/**
	 * Nonce Id generation
	 *
	 * @return string
	 */
	public static function nonceId() {
		return 'fmp_nonce';
	}

	/**
	 * Render.
	 *
	 * @param string $template_name View name.
	 * @param array $args View args.
	 * @param boolean $return View return.
	 *
	 * @return string|void
	 */
	public static function render( $template_name, $args = [], $return = false ) {

		if ( ! empty( $args ) && is_array( $args ) ) {
			extract( $args );
		}

		$template = [
			$template_name . '.php',
			"tlp-food-menu/{$template_name}.php",
			"food-menu-pro/{$template_name}.php",
		];

		$pro_path = TLPFoodMenu()->pro_templates_path() . $template_name . '.php';

		if ( locate_template( $template ) ) {
			$template_file = locate_template( $template );
		} elseif ( function_exists( 'FMP' ) && file_exists( $pro_path ) ) {
			$template_file = $pro_path;
		} else {
			$template_file = TLPFoodMenu()->templates_path() . $template_name . '.php';
		}

		if ( ! file_exists( $template_file ) ) {
			_doing_it_wrong( __FUNCTION__, sprintf( '<code>%s</code> does not exist.', esc_html( $template_file ) ), '1.7.0' );

			return;
		}

		if ( $return ) {
			ob_start();
			include $template_file;

			return ob_get_clean();
		} else {
			include $template_file;
		}
	}

	/**
	 * Render view.
	 *
	 * @param string $viewName View name.
	 * @param array $args View args.
	 * @param boolean $return View return.
	 *
	 * @return string|void
	 */
	public static function renderView( $viewName, $args = [], $return = false ) {
		$viewName = str_replace( '.', '/', sanitize_file_name( $viewName ) );

		if ( ! empty( $args ) && is_array( $args ) ) {
			extract( $args );
		}

		$view_file = TLPFoodMenu()->plugin_path() . '/resources/' . $viewName . '.php';

		if ( ! file_exists( $view_file ) ) {
			_doing_it_wrong( __FUNCTION__, sprintf( '<code>%s</code> does not exist.', esc_html( $view_file ) ), '1.7.0' );

			return;
		}

		if ( $return ) {
			ob_start();
			include $view_file;

			return ob_get_clean();
		} else {
			include $view_file;
		}
	}

	/**
	 * Decimal Formatting.
	 *
	 * @param string $number Number.
	 * @param boolean $dp DP.
	 * @param boolean $trim_zeros Trim zero.
	 *
	 * @return string
	 */
	public static function format_decimal( $number, $dp = false, $trim_zeros = false ) {
		$locale   = localeconv();
		$decimals = [
			$locale['decimal_point'],
			$locale['mon_decimal_point'],
		];

		if ( $dp !== false ) {
			$dp     = intval( $dp == '' ? self::get_price_decimals() : $dp );
			$number = number_format( floatval( $number ), $dp, '.', '' );
		}

		if ( $trim_zeros && strstr( $number, '.' ) ) {
			$number = rtrim( rtrim( $number, '0' ), '.' );
		}

		return $number;
	}

	/**
	 * Decimal price seperator.
	 *
	 * @return int
	 */
	public static function get_price_decimal_separator() {
		$settings  = get_option( TLPFoodMenu()->options['settings'] );
		$separator = ! empty( $settings['price_decimal_sep'] ) ? stripslashes( $settings['price_decimal_sep'] ) : null;

		return $separator ? $separator : '.';
	}

	/**
	 * Decimal price.
	 *
	 * @return int
	 */
	public static function get_price_decimals() {
		$settings = get_option( TLPFoodMenu()->options['settings'] );

		return ( ! empty( $settings['price_num_decimals'] ) ? ( absint( $settings['price_num_decimals'] ) > 0 ? absint( $settings['price_num_decimals'] ) : 2 ) : 2 );
	}

	/**
	 * This function will generate meta or setting field
	 *
	 * @param array $fields Fields.
	 *
	 * @return null|string
	 */
	public static function rtFieldGenerator( $fields = [] ) {
		$html = null;

		if ( is_array( $fields ) && ! empty( $fields ) ) {
			$fmField = new Fields();

			foreach ( $fields as $fieldKey => $field ) {
				$html .= $fmField->Field( $fieldKey, $field );
			}
		}

		return $html;
	}

	/**
	 * MetaField list for food Page
	 *
	 * @return array
	 */
	public static function singleFoodMetaFields() {
		return array_merge(
			Options::foodGeneralOptions(),
			Options::foodAdvancedOptions()
		);
	}

	/**
	 * MetaField list for food Page
	 *
	 * @return array
	 */
	public static function fmpAllSettingsFields() {
		$allSettings = array_merge(
			Options::generalSettings(),
			Options::generalSettings2(),
			Options::detailPageSettings(),
			MiniCartFns::settings_field(),
			Options::front_end_order_settings()
		);

		return apply_filters( 'rt_fm_setting_fields', $allSettings );
	}

	/**
	 * Generate MetaField Name list for shortCode Page
	 *
	 * @return array
	 */
	public static function fmpScMetaFields() {
		return array_merge(
			Options::scLayoutMetaFields(),
			Options::scResponsiveMetaFields(),
			Options::scPaginationFields(),
			Options::scCategoryTitleFields(),
			Options::scImageMetaFields(),
			Options::scExcerptMetaFields(),
			Options::scDetailsMetaFields(),
			Options::scFilterMetaFields(),
			Options::scItemFields(),
			Options::scStyleGeneralFields(),
			Options::scStyleContentFields(),
			Options::scStyleButtonBgColorFields(),
			Options::scStyleButtonColorFields(),
			Options::scStyleExtraFields()
		);
	}

	/**
	 * Sanitize field value
	 *
	 * @param array $field Field.
	 * @param null $value Value.
	 *
	 * @return array|null
	 * @internal param $value
	 */
	public static function sanitize( $field = [], $value = null ) {
		$newValue = null;

		if ( ! is_array( $field ) ) {
			return $newValue;
		}

		$type = ( ! empty( $field['type'] ) ? $field['type'] : 'text' );

		if ( empty( $field['multiple'] ) ) {
			if ( $type == 'text' || $type == 'number' || $type == 'select' || $type == 'checkbox' || $type == 'radio' ) {
				$newValue = sanitize_text_field( $value );
			} elseif ( $type == 'price' ) {
				$newValue = ( '' === $value ) ? '' : self::format_decimal( $value );
			} elseif ( $type == 'url' ) {
				$newValue = esc_url( $value );
			} elseif ( $type == 'slug' ) {
				$newValue = sanitize_title_with_dashes( $value );
			} elseif ( $type == 'textarea' ) {
				$newValue = sanitize_textarea_field( $value ?? '' );
			} elseif ( $type == 'custom_css' ) {
				$newValue = esc_attr( $value );
			} elseif ( $type == 'colorpicker' ) {
				$newValue = self::sanitize_hex_color( $value );
			} elseif ( $type == 'image_size' ) {
				$newValue = [];

				foreach ( $value as $k => $v ) {
					$newValue[ $k ] = esc_attr( $v );
				}
			} elseif ( $type == 'group' ) {
				$newValue = [];

				foreach ( $value as $k => $v ) {
					if ( $k == 'bg_color' ) {
						$newValue[ $k ] = self::sanitize_hex_color( $v );
					} else {
						$newValue[ $k ] = self::sanitize( [ 'type' => 'text' ], $v );
					}
				}
			} elseif ( $type == 'category-style' ) {
				$newValue = [];

				foreach ( $value as $k => $v ) {
					if ( $k == 'first_color' || $k == 'second_color' ) {
						$newValue[ $k ] = self::sanitize_hex_color( $v );
					} else {
						$newValue[ $k ] = self::sanitize( [ 'type' => 'text' ], $v );
					}
				}
			} elseif ( $type == 'style' ) {
				$newValue = [];

				foreach ( $value as $k => $v ) {
					if ( $k == 'color' ) {
						$newValue[ $k ] = self::sanitize_hex_color( $v );
					} else {
						$newValue[ $k ] = self::sanitize( [ 'type' => 'text' ], $v );
					}
				}
			} else {
				$newValue = sanitize_text_field( $value );
			}
		} else {
			$newValue = [];

			if ( 'array' == $type && ! empty( $value ) && is_array( $value ) ) {
				$newValue = $value;
			} elseif ( ! empty( $value ) ) {
				if ( is_array( $value ) ) {
					foreach ( $value as $key => $val ) {
						if ( $type == 'style' && $key == 0 ) {
							if ( function_exists( 'sanitize_hex_color' ) ) {
								$newValue = sanitize_hex_color( $val );
							} else {
								$newValue[] = self::sanitize_hex_color( $val );
							}
						} else {
							$newValue[] = sanitize_text_field( $val );
						}
					}
				} else {
					$newValue[] = sanitize_text_field( $value );
				}
			}
		}

		return $newValue;
	}

	public static function sanitize_hex_color( $color ) {
		if ( function_exists( 'sanitize_hex_color' ) ) {
			return sanitize_hex_color( $color );
		} else {
			if ( '' === $color ) {
				return '';
			}

			// 3 or 6 hex digits, or the empty string.
			if ( preg_match( '|^#([A-Fa-f0-9]{3}){1,2}$|', $color ) ) {
				return $color;
			}
		}
	}

	/**
	 * Convert hexdec color string to rgb(a) string
	 *
	 * @param string $color Color.
	 * @param float $opacity Opacity.
	 *
	 * @return string
	 */
	public static function rtHex2rgba( $color, $opacity = .5 ) {
		$default = 'rgb(0,0,0)';

		// Return default if no color provided.
		if ( empty( $color ) ) {
			return $default;
		}

		// Sanitize $color if "#" is provided.
		if ( $color[0] == '#' ) {
			$color = substr( $color, 1 );
		}

		// Check if color has 6 or 3 characters and get values.
		if ( strlen( $color ) == 6 ) {
			$hex = [ $color[0] . $color[1], $color[2] . $color[3], $color[4] . $color[5] ];
		} elseif ( strlen( $color ) == 3 ) {
			$hex = [ $color[0] . $color[0], $color[1] . $color[1], $color[2] . $color[2] ];
		} else {
			return $default;
		}

		// Convert hexadec to rgb.
		$rgb = array_map( 'hexdec', $hex );

		// Check if opacity is set(rgba or rgb).
		if ( $opacity ) {
			if ( abs( $opacity ) > 1 ) {
				$opacity = 1.0;
			}

			$output = 'rgba(' . implode( ',', $rgb ) . ',' . $opacity . ')';
		} else {
			$output = 'rgb(' . implode( ',', $rgb ) . ')';
		}

		// Return rgb(a) color string.
		return $output;
	}

	/**
	 *  Get all Category list for food-menu
	 *
	 * @return array
	 */
	public static function getAllFmpCategoryList() {
		global $post;

		$taxonomy = TLPFoodMenu()->taxonomies['category'];

		if ( $post ) {
			$source = get_post_meta( $post->ID, 'fmp_source', true );
			$source = ( $source && in_array( $source, array_keys( Options::scProductSource() ) ) ) ? $source : TLPFoodMenu()->post_type;
			if ( $source == 'product' && TLPFoodMenu()->isWcActive() ) {
				$taxonomy = 'product_cat';
			}
		}
		$terms    = [];
		$termList = get_terms(
			[
				'taxonomy'   => $taxonomy,
				'hide_empty' => 0,
			]
		);

		if ( is_array( $termList ) && ! empty( $termList ) && empty( $termList['errors'] ) ) {
			foreach ( $termList as $term ) {
				$terms[ $term->term_id ] = $term->name;
			}
		}

		return $terms;
	}

	/**
	 *  Get all Category list for food-menu ( elementor )
	 *
	 * @return array
	 */
	public static function getElAllFmpCategoryList( $settings = [] ) {
		$taxonomy = TLPFoodMenu()->taxonomies['category'];
		$terms    = [];
		$termList = get_terms( [
			'taxonomy'   => $taxonomy,
			'hide_empty' => 0,
		] );
		if ( is_array( $termList ) && ! empty( $termList ) && empty( $termList['errors'] ) ) {
			foreach ( $termList as $term ) {
				$terms[ $term->term_id ] = $term->name;
			}
		}

		return $terms;
	}

	/**
	 *  Get all Category list for food-menu ( elementor )
	 *
	 * @return array
	 */

	public static function el_cat_maping( $terms ) {
		$final_trerms = [];
		foreach ( $terms as $tId ) {
			$term_info                           = get_term( $tId );
			$final_trerms[ $term_info->term_id ] = $term_info->name;
		}

		return $final_trerms;
	}


	/**
	 *  Get all Category list for food-menu ( elementor )
	 *
	 * @return array
	 */
	public static function getElProductAllFmpCategoryList( $settings = [] ) {
		$taxonomy = 'product_cat';
		$terms    = [];
		$termList = get_terms( [
			'taxonomy'   => $taxonomy,
			'hide_empty' => 0,
		] );
		if ( is_array( $termList ) && ! empty( $termList ) && empty( $termList['errors'] ) ) {
			foreach ( $termList as $term ) {
				$terms[ $term->term_id ] = $term->name;
			}
		}

		return $terms;
	}

	/**
	 *  Get all Category list for food-menu ( elementor Isotope )
	 *
	 * @return array
	 */
	public static function getElAllFmpCategoryListIsotope( $settings = [] ) {
		$taxonomy     = TLPFoodMenu()->taxonomies['category'];
		$terms        = [];
		$terms['all'] = 'Show All';
		$termList     = get_terms( [
			'taxonomy'   => $taxonomy,
			'hide_empty' => 0,
		] );
		if ( is_array( $termList ) && ! empty( $termList ) && empty( $termList['errors'] ) ) {
			foreach ( $termList as $term ) {
				$terms[ $term->term_id ] = $term->name;
			}
		}

		return $terms;
	}

	/**
	 *  Get all Category list for food-menu ( elementor Isotope )
	 *
	 * @return array
	 */
	public static function getElProductAllFmpCategoryListIsotope( $settings = [] ) {
		$taxonomy     = 'product_cat';
		$terms        = [];
		$terms['all'] = 'Show All';
		$termList     = get_terms( [
			'taxonomy'   => $taxonomy,
			'hide_empty' => 0,
		] );
		if ( is_array( $termList ) && ! empty( $termList ) && empty( $termList['errors'] ) ) {
			foreach ( $termList as $term ) {
				$terms[ $term->term_id ] = $term->name;
			}
		}

		return $terms;
	}

	/**
	 * Get ordered category term IDs for an Isotope layout, matching the exact
	 * order the filter-bar buttons render in (menu order via the `order`/`_order`
	 * term meta, same query the buttons use).
	 *
	 * @param string $taxonomy   Category taxonomy ('product_cat' or 'food-menu-cat').
	 * @param array  $cats       Selected category IDs to restrict to (empty = all).
	 * @param bool   $hide_empty Whether to exclude empty categories.
	 *
	 * @return int[] Ordered term IDs.
	 */
	public static function get_isotope_ordered_term_ids( $taxonomy, array $cats = [], $hide_empty = false ) {
		$meta_key = ( 'product_cat' === $taxonomy ) ? 'order' : '_order';

		$args = [
			'taxonomy'   => $taxonomy,
			'hide_empty' => (bool) $hide_empty,
			'orderby'    => 'meta_value_num',
			'order'      => 'ASC',
			'fields'     => 'ids',
			//phpcs:ignore WordPress.DB.SlowDBQuery.slow_db_query_meta_query
			'meta_query' => [
				'relation' => 'OR',
				[
					'key'     => $meta_key,
					'compare' => 'NOT EXISTS',
				],
				[
					'key'  => $meta_key,
					'type' => 'NUMERIC',
				],
			],
		];

		if ( ! empty( $cats ) ) {
			$args['include'] = array_map( 'intval', (array) $cats );
		}

		$terms = get_terms( $args );

		return ( is_array( $terms ) && empty( $terms['errors'] ) ) ? array_map( 'intval', $terms ) : [];
	}

	/**
	 * Reorder a list of posts so they are grouped by the given ordered category
	 * term IDs. Each post is placed under the first ordered term it belongs to
	 * (so an item in multiple categories appears once, in its first category's
	 * group). Order within a group is preserved (stable), so the active "Order By"
	 * still applies inside each category. Posts matching none of the terms keep
	 * their original order and go last.
	 *
	 * Uses get_the_terms() which reads the object-term cache primed by WP_Query,
	 * so this adds no extra per-post DB queries.
	 *
	 * @param array  $posts            Array of WP_Post objects (e.g. $query->posts).
	 * @param int[]  $ordered_term_ids Ordered category term IDs.
	 * @param string $taxonomy         Category taxonomy.
	 *
	 * @return array Reordered posts.
	 */
	public static function group_posts_by_term_order( array $posts, array $ordered_term_ids, $taxonomy ) {
		if ( empty( $posts ) || empty( $ordered_term_ids ) ) {
			return $posts;
		}

		$ordered_term_ids = array_map( 'intval', $ordered_term_ids );
		$buckets          = array_fill_keys( $ordered_term_ids, [] );
		$leftover         = [];

		foreach ( $posts as $post ) {
			$terms    = get_the_terms( $post->ID, $taxonomy );
			$term_ids = ( $terms && ! is_wp_error( $terms ) ) ? array_map( 'intval', wp_list_pluck( $terms, 'term_id' ) ) : [];

			$placed = false;
			foreach ( $ordered_term_ids as $tid ) {
				if ( in_array( $tid, $term_ids, true ) ) {
					$buckets[ $tid ][] = $post;
					$placed            = true;
					break;
				}
			}

			if ( ! $placed ) {
				$leftover[] = $post;
			}
		}

		$result = [];
		foreach ( $buckets as $group ) {
			foreach ( $group as $post ) {
				$result[] = $post;
			}
		}
		foreach ( $leftover as $post ) {
			$result[] = $post;
		}

		return $result;
	}

	/**
	 * @param $data
	 * @param $total_pages
	 * @param $animation
	 * @param $template
	 *
	 * @return array
	 */
	public static function get_render_data_set( $data, $total_pages, $animation, $template ) {
		$data_set = [
			'fmp_source'              => $data['fmp_source'] ?? 'food-menu',
			'source'                  => ! empty( $data['fmp_source'] ) ? $data['fmp_source'] : 'food-menu',
			'wc'                      => class_exists( 'WooCommerce' ),
			'dCols'                   => $data['fmp_desktop_column'] ?? '0',
			'tCols'                   => $data['fmp_desktop_column_tablet'] ?? '0',
			'mCols'                   => $data['fmp_desktop_column_mobile'] ?? '0',
			'hovericon'               => ( $data['fmp_hover_icon'] === 'yes' ) ? 'yes' : '',
			'grid_style'              => $data['tlp_el_grid_style_promo'],
			'layout'                  => $data['fmp_layout'],
			'imgSize'                 => $data['fmp_image_size'],
			'total_pages'             => $total_pages,
			'template'                => $template,
			'featureImg'              => $data['fmp_feature_switch'],
			'titleswitch'             => $data['fmp_title_switch'],
			'priceswitch'             => $data['fmp_price_switch'],
			'contentswitch'           => $data['fmp_content_switch'],
			'addtocart'               => ! empty( $data['fmp_addtocart_switch'] ) ? $data['fmp_addtocart_switch'] : 'no',
			'quantity'                => ! empty( $data['fmp_quantity_switch'] ) ? $data['fmp_quantity_switch'] : 'no',
			'add_stock'               => ! empty( $data['fmp_stock_status_switch'] ) ? $data['fmp_stock_status_switch'] : 'no',
			'readmore_switch'         => ! empty( $data['fmp_readmore_switch'] ) ? $data['fmp_readmore_switch'] : 'no',
			'readmore_text'           => ! empty( $data['fmp_readmore_text'] ) ? $data['fmp_readmore_text'] : '',
			'items'                   => self::buildItemsFromSwitches( $data ),
			'fmp_el_popup'            => ! empty( $data['fmp_detail_page_popup'] ) ? $data['fmp_detail_page_popup'] : 'no',
			'fmp_pagination_type'     => $data['fmp_pagination_type'],
			'tlp_el_grid_style_promo' => $data['tlp_el_grid_style_promo'],
			'excerpt_limit'           => $data['fmp_excerpt_limit'] ?? 0,
			'fmp_excerpt_limit'       => $data['fmp_excerpt_limit'],
			'detail_link'             => ( ( $data['fmp_detail_page_link'] ?? '' ) === 'yes' ) ? ( ! empty( $data['fmp_detail_link'] ) ? $data['fmp_detail_link'] : 'link' ) : '',
			'tlp_el_image_animation'  => $data['tlp_el_image_animation'],
		];

		return $data_set;
	}

	/**
	 * Build items array from individual Elementor switch settings.
	 *
	 * @param array $data Elementor widget settings.
	 *
	 * @return array
	 */
	public static function buildItemsFromSwitches( $data ) {
		if ( ! empty( $data['fmp_item_fields'] ) && is_array( $data['fmp_item_fields'] ) ) {
			return $data['fmp_item_fields'];
		}

		$items = [];

		if ( ! empty( $data['fmp_addtocart_switch'] ) && 'yes' === $data['fmp_addtocart_switch'] ) {
			$items[] = 'add_to_cart';
		}

		if ( ! empty( $data['fmp_quantity_switch'] ) && 'yes' === $data['fmp_quantity_switch'] ) {
			$items[] = 'quantity';
		}

		if ( ! empty( $data['fmp_stock_status_switch'] ) && 'yes' === $data['fmp_stock_status_switch'] ) {
			$items[] = 'stock';
		}

		return $items;
	}

	/**
	 * Placeholder Image.
	 *
	 * @return string
	 */
	public static function placeholder_img_src() {
		return TLPFoodMenu()->assets_url() . 'images/placeholder.png';
	}

	/**
	 * Image Types.
	 *
	 * @return string
	 */
	public static function get_image_types() {
		return [
			'normal' => esc_html__( 'Normal', 'tlp-food-menu' ),
			'circle' => esc_html__( 'Circle', 'tlp-food-menu' ),
		];
	}

	/**
	 * Image Position.
	 *
	 * @return string
	 */
	public static function get_image_position() {
		return [
			'top'    => esc_html__( 'Top', 'tlp-food-menu' ),
			'center' => esc_html__( 'Center', 'tlp-food-menu' ),
			'bottom' => esc_html__( 'Bottom', 'tlp-food-menu' ),
		];
	}

	/**
	 * Category Title Types.
	 *
	 * @return string
	 */
	public static function get_category_title_types() {
		$types = [
			'default' => esc_html__( 'Layout Default', 'tlp-food-menu' ),
			'type-1'  => esc_html__( 'Type 1', 'tlp-food-menu' ),
			'type-2'  => esc_html__( 'Type 2', 'tlp-food-menu' ),
		];

		if ( TLPFoodMenu()->has_pro() ) {
			$types['type-3'] = esc_html__( 'Type 3', 'tlp-food-menu' );
			$types['type-4'] = esc_html__( 'Type 4', 'tlp-food-menu' );
		}

		return $types;
	}

	/**
	 * Image Types.
	 *
	 * @return string
	 */
	public static function get_details_types() {
		return [
			'newpage' => esc_html__( 'Single Page', 'tlp-food-menu' ),
			'popup'   => esc_html__( 'Pop Up', 'tlp-food-menu' ),
		];
	}

	/**
	 * Image Types.
	 *
	 * @return string
	 */
	public static function get_details_target() {
		return [
			'_self'  => esc_html__( 'Same Tab', 'tlp-food-menu' ),
			'_blank' => esc_html__( 'New Tab', 'tlp-food-menu' ),
		];
	}

	/**
	 * Image Hover.
	 *
	 * @return string
	 */
	public static function get_image_hover() {
		return [
			'zoom_in'  => esc_html__( 'Zoom In', 'tlp-food-menu' ),
			'zoom_out' => esc_html__( 'Zoom Out', 'tlp-food-menu' ),
			'none'     => esc_html__( 'None', 'tlp-food-menu' ),
		];
	}

	/**
	 * Get Image Sizes.
	 *
	 * @return array
	 */
	public static function get_image_sizes() {
		global $_wp_additional_image_sizes;

		$sizes = [];

		foreach ( get_intermediate_image_sizes() as $_size ) {
			if ( in_array( $_size, [ 'thumbnail', 'medium', 'large' ] ) ) {
				$sizes[ $_size ]['width']  = get_option( "{$_size}_size_w" );
				$sizes[ $_size ]['height'] = get_option( "{$_size}_size_h" );
				$sizes[ $_size ]['crop']   = (bool) get_option( "{$_size}_crop" );
			} elseif ( isset( $_wp_additional_image_sizes[ $_size ] ) ) {
				$sizes[ $_size ] = [
					'width'  => $_wp_additional_image_sizes[ $_size ]['width'],
					'height' => $_wp_additional_image_sizes[ $_size ]['height'],
					'crop'   => $_wp_additional_image_sizes[ $_size ]['crop'],
				];
			}
		}

		$imgSize = [];
		foreach ( $sizes as $key => $img ) {
			$imgSize[ $key ] = ucfirst( $key ) . " ({$img['width']}*{$img['height']})";
		}

		return apply_filters( 'fmp_image_size', $imgSize );
	}

	/**
	 * Get Currency List.
	 *
	 * @return array
	 */
	public static function getCurrencyList() {
		$currencyList = [];

		foreach ( Options::currency_list() as $key => $currency ) {
			$currencyList[ $key ] = $currency['name'] . ' (' . $currency['symbol'] . ')';
		}

		return $currencyList;
	}

	/**
	 * Excerpt Max Character Length.
	 *
	 * @param int $charLength Character Length.
	 *
	 * @return string
	 */
	public static function the_excerpt_max_charlength( $charLength ) {
		$excerpt = get_the_excerpt();
		$html    = null;

		$charLength ++;

		if ( mb_strlen( $excerpt ) > $charLength ) {
			$subex   = mb_substr( $excerpt, 0, $charLength - 5 );
			$exwords = explode( ' ', $subex );
			$excut   = - ( mb_strlen( $exwords[ count( $exwords ) - 1 ] ) );

			if ( $excut < 0 ) {
				$html .= mb_substr( $subex, 0, $excut );
			} else {
				$html .= $subex;
			}
		} else {
			$html .= $excerpt;
		}

		return $html;
	}

	/**
	 * Word Limit.
	 *
	 * @param string $string Word.
	 * @param int $word_limit Limit.
	 *
	 * @return string
	 */
	public static function string_limit_words( $string, $word_limit ) {
		$words = explode( ' ', $string );

		return implode( ' ', array_slice( $words, 0, $word_limit ) );
	}

	/**
	 * Get Price
	 *
	 * @param int $id Post ID.
	 *
	 * @return string
	 */
	public static function getPrice( $id = null ) {
		if ( $id ) {
			$id = absint( $id );
		} else {
			global $post;
			$id = $post->ID;
		}

		$regular_price = get_post_meta( $id, '_regular_price', true );

		if ( ! TLPFoodMenu()->has_pro() ) {
			$settings        = get_option( TLPFoodMenu()->options['settings'] );
			$trailing_zeroes = ! empty( $settings['trailing_zeroes'] ) ? 1 : 0;

			if ( ! empty( $trailing_zeroes ) ) {
				return (int) $regular_price;
			}
		}

		return $regular_price;
	}

	/**
	 * Get Currency.
	 *
	 * @return string
	 */
	public static function getCurrency() {
		$settings = get_option( TLPFoodMenu()->options['settings'] );
		$currency = ( ! empty( $settings['currency'] ) ? esc_attr( $settings['currency'] ) : 'USD' );

		return $currency;
	}

	/**
	 * Get Currency Symbol.
	 *
	 * @return string
	 */
	public static function getCurrencySymbol() {
		$currency = self::getCurrency();
		$cList    = Options::currency_list();

		return $cList[ $currency ]['symbol'];
	}

	/**
	 * Get Currency Position
	 *
	 * @return string
	 */
	public static function getCurrencyPosition() {
		$settings = get_option( TLPFoodMenu()->options['settings'] );

		return ( ! empty( $settings['currency_position'] ) ? esc_attr( $settings['currency_position'] ) : 'left' );
	}

	/**
	 * Get Price with Label
	 *
	 * @return string
	 */
	public static function getPriceWithLabel() {
		$price = self::getPrice();

		if ( $price ) {
			$symbol    = self::getCurrencySymbol();
			$currencyP = self::getCurrencyPosition();

			switch ( $currencyP ) {
				case 'left':
					$price = $symbol . $price;
					break;

				case 'right':
					$price = $price . $symbol;
					break;

				case 'left_space':
					$price = $symbol . ' ' . $price;
					break;

				case 'right_space':
					$price = $price . ' ' . $symbol;
					break;

				default:
					break;
			}
		}

		return apply_filters( 'rtfm_food_price_modifier', $price, get_the_ID() );
	}

	public static function strip_tags_content( $text, $limit = 0, $tags = '', $invert = false ) {
		preg_match_all( '/<(.+?)[\s]*\/?[\s]*>/si', trim( $tags ), $tags );
		$tags = array_unique( $tags[1] );

		if ( is_array( $tags ) and count( $tags ) > 0 ) {
			if ( $invert == false ) {
				$text = preg_replace(
					'@<(?!(?:' . implode( '|', $tags ) . ')\b)(\w+)\b.*?>.*?</\1>@si',
					'',
					$text
				);
			} else {
				$text = preg_replace( '@<(' . implode( '|', $tags ) . ')\b.*?>.*?</\1>@si', '', $text );
			}
		} elseif ( $invert == false ) {
			$text = preg_replace( '@<(\w+)\b.*?>.*?</\1>@si', '', $text );
		}
		if ( $limit > 0 && strlen( $text ) > $limit ) {
			$text = substr( $text, 0, $limit );
		}

		return $text;
	}

	/**
	 * Call the Image resize model for resize function
	 *
	 * @param            $url
	 * @param null $width
	 * @param null $height
	 * @param null $crop
	 * @param bool|true $single
	 * @param bool|false $upscale
	 *
	 * @return array|bool|string
	 * @throws FmpException
	 */
	public static function rtImageReSize( $url, $width = null, $height = null, $crop = null, $single = true, $upscale = false ) {
		$rtResize = new ReSizer();

		return $rtResize->process( $url, $width, $height, $crop, $single, $upscale );
	}

	public static function getFeatureImage( $post_id = null, $fImgSize = 'medium', $defaultImgId = 0, $customImgSize = [], $lazy = false ) {
		$imgHtml = $imgSrc = $attachment_id = null;
		$cSize   = false;

		if ( $fImgSize == 'fmp_custom' ) {
			$fImgSize = 'full';
			$cSize    = true;
		}

		$aID        = get_post_thumbnail_id( $post_id );
		$post_title = get_the_title( $post_id );
		$img_alt    = trim( wp_strip_all_tags( get_post_meta( $aID, '_wp_attachment_image_alt', true ) ) );
		$alt_tag    = ! empty( $img_alt ) ? $img_alt : trim( wp_strip_all_tags( $post_title ) );
		$lazy_class = $lazy ? ' swiper-lazy' : '';
		$attr       = [
			'class' => 'fmp-feature-img' . $lazy_class,
			'alt'   => $alt_tag,
		];

		$actual_dimension = wp_get_attachment_metadata( $aID, true );

		if ( empty( $actual_dimension ) && $defaultImgId ) {
			$actual_dimension = wp_get_attachment_metadata( $defaultImgId, true );
		}

		$actual_w = ! empty( $actual_dimension['width'] ) ? $actual_dimension['width'] : '';
		$actual_h = ! empty( $actual_dimension['height'] ) ? $actual_dimension['height'] : '';

		if ( $aID ) {
			$imgHtml       = wp_get_attachment_image( $aID, $fImgSize, false, $attr );
			$attachment_id = $aID;
		}

		if ( ! $imgHtml && $defaultImgId ) {
			$imgHtml       = wp_get_attachment_image( $defaultImgId, $fImgSize, false, $attr );
			$attachment_id = $defaultImgId;
		}

		if ( $imgHtml && $cSize ) {
			preg_match( '@src="([^"]+)"@', $imgHtml, $match );

			$imgSrc = array_pop( $match );
			$w      = ! empty( $customImgSize['width'] ) ? absint( $customImgSize['width'] ) : null;
			$h      = ! empty( $customImgSize['height'] ) ? absint( $customImgSize['height'] ) : null;
			$c      = ! empty( $customImgSize['crop'] ) && $customImgSize['crop'] == 'soft' ? false : true;

			if ( $w && $h ) {
				if ( $w >= $actual_w || $h >= $actual_h ) {
					$w = 150;
					$h = 150;
					$c = true;
				}

				$image = self::rtImageReSize( $imgSrc, $w, $h, $c, false );

				if ( ! empty( $image ) ) {
					if ( $lazy ) {
						[ $src, $width, $height ] = $image;

						$hwstring         = image_hwstring( $width, $height );
						$attachment       = get_post( $attachment_id );
						$attr             = apply_filters( 'wp_get_attachment_image_attributes', $attr, $attachment, $fImgSize );
						$attr['data-src'] = $src;
						$attr             = array_map( 'esc_attr', $attr );
						$imgHtml          = rtrim( "<img $hwstring" );

						foreach ( $attr as $name => $value ) {
							$imgHtml .= " $name=" . '"' . $value . '"';
						}

						$imgHtml .= ' />';
					} else {
						[ $src, $width, $height ] = $image;

						$hwstring    = image_hwstring( $width, $height );
						$attachment  = get_post( $attachment_id );
						$attr        = apply_filters( 'wp_get_attachment_image_attributes', $attr, $attachment, $fImgSize );
						$attr['src'] = $src;
						$attr        = array_map( 'esc_attr', $attr );
						$imgHtml     = rtrim( "<img $hwstring" );

						foreach ( $attr as $name => $value ) {
							$imgHtml .= " $name=" . '"' . $value . '"';
						}

						$imgHtml .= ' />';
					}
				}
			}
		}

		if ( ! $imgHtml ) {
			$hwstring      = image_hwstring( 160, 160 );
			$attr          = isset( $attr['src'] ) ? apply_filters( 'wp_get_attachment_image_attributes', $attr, false, $fImgSize ) : [];
			$attr['class'] = 'default-img';
			$attr['src']   = esc_url( self::placeholder_img_src() );
			$attr['alt']   = esc_html__( 'Default Image', 'tlp-food-menu' );
			$imgHtml       = rtrim( "<img $hwstring" );

			foreach ( $attr as $name => $value ) {
				$imgHtml .= " $name=" . '"' . $value . '"';
			}

			$imgHtml .= ' />';
		}

		if ( $lazy ) {
			$imgHtml = $imgHtml . '<div class="swiper-lazy-preloader swiper-lazy-preloader"></div>';
		}

		return $imgHtml . '<i class="fmp-image-icon"></i>';
	}

	public static function getAttachedImage( $attach_id, $fImgSize = 'medium', $customImgSize = [] ) {
		$imgSrc = $image = null;
		$cSize  = false;

		if ( $fImgSize == 'fmp_custom' ) {
			$fImgSize = 'full';
			$cSize    = true;
		}

		if ( $attach_id ) {
			$image  = wp_get_attachment_image( $attach_id, $fImgSize );
			$imageS = wp_get_attachment_image_src( $attach_id, $fImgSize );
			$imgSrc = ! empty( $imageS[0] ) ? $imageS[0] : '';
		} else {
			$imgSrc = self::placeholder_img_src();
			$image  = "<img src='{$imgSrc}' />";
		}

		if ( $imgSrc && $cSize ) {
			$w = ( ! empty( $customImgSize['width'] ) ? absint( $customImgSize['width'] ) : null );
			$h = ( ! empty( $customImgSize['height'] ) ? absint( $customImgSize['height'] ) : null );
			$c = ( ! empty( $customImgSize['crop'] ) && $customImgSize['crop'] == 'soft' ? false : true );

			if ( $w && $h ) {
				$imgSrc = self::rtImageReSize( $imgSrc, $w, $h, $c );
				$image  = '<img src="' . esc_url( $imgSrc ) . '" />';
			}
		}

		return $image;
	}

	/**
	 * Returns the product categories.
	 *
	 * @param        $id
	 * @param string $sep (default: ', ')
	 * @param string $before (default: '')
	 * @param string $after (default: '')
	 *
	 * @return string
	 */
	public static function get_categories( $id, $sep = ', ', $before = '', $after = '' ) {
		return get_the_term_list( $id, TLPFoodMenu()->taxonomies['category'], $before, $sep, $after );
	}

	public static function get_shortCode_list() {
		$scList = null;
		$scQ    = get_posts(
			[
				'post_type'      => TLPFoodMenu()->shortCodePT,
				'order_by'       => 'title',
				'order'          => 'ASC',
				'post_status'    => 'publish',
				'posts_per_page' => - 1,
			]
		);

		if ( ! empty( $scQ ) ) {
			foreach ( $scQ as $sc ) {
				$scList[ $sc->ID ] = $sc->post_title;
			}
		}

		return $scList;
	}

	/**
	 * Get a list of all published Food Menu posts.
	 *
	 * Retrieves all posts of the custom post type defined by TLPFoodMenu()->post_type,
	 * ordered by title in ascending order, and returns them as an associative array
	 * with post IDs as keys and post titles as values.
	 *
	 * @return array Associative array of menu items [post_id => post_title].
	 */

	public static function getMenuList() {
		$lists = [];
		$listQ = get_posts(
			[
				'post_type'      => TLPFoodMenu()->post_type,
				'post_status'    => 'publish',
				'posts_per_page' => - 1,
				'orderby'        => 'title',
				'order'          => 'ASC',
			]
		);
		if ( ! empty( $listQ ) && is_array( $listQ ) ) {
			foreach ( $listQ as $list ) {
				$lists[ $list->ID ] = $list->post_title;
			}
		}

		return $lists;
	}

	/**
	 * Get a list of all published Food Menu posts.
	 *
	 * Retrieves all posts of the custom post type defined by TLPFoodMenu()->post_type,
	 * ordered by title in ascending order, and returns them as an associative array
	 * with post IDs as keys and post titles as values.
	 *
	 * @return array Associative array of menu items [post_id => post_title].
	 */

	public static function getMenuListEl() {
		$lists = [];
		$listQ = get_posts(
			[
				'post_type'      => 'product',
				'post_status'    => 'publish',
				'posts_per_page' => - 1,
				'orderby'        => 'title',
				'order'          => 'ASC',
			]
		);
		if ( ! empty( $listQ ) && is_array( $listQ ) ) {
			foreach ( $listQ as $list ) {
				$lists[ $list->ID ] = $list->post_title;
			}
		}

		return $lists;
	}

	/**
	 * Promotion Product
	 *
	 * @param array $products Products.
	 *
	 * @return string
	 */
	public static function get_product_list_html( $products = [] ) {
		$html = null;

		if ( ! empty( $products ) ) {
			foreach ( $products as $type => $list ) {
				if ( ! empty( $list ) ) {
					$htmlProducts = null;
					foreach ( $list as $product ) {
						$image_url       = isset( $product['image_url'] ) ? $product['image_url'] : null;
						$image_thumb_url = isset( $product['image_thumb_url'] ) ? $product['image_thumb_url'] : null;
						$image_thumb_url = $image_thumb_url ? $image_thumb_url : $image_url;
						$price           = isset( $product['price'] ) ? $product['price'] : null;
						$title           = isset( $product['title'] ) ? $product['title'] : null;
						$url             = isset( $product['url'] ) ? $product['url'] : null;
						$buy_url         = isset( $product['buy_url'] ) ? $product['buy_url'] : null;
						$buy_url         = $buy_url ? $buy_url : $url;
						$doc_url         = isset( $product['doc_url'] ) ? $product['doc_url'] : null;
						$demo_url        = isset( $product['demo_url'] ) ? $product['demo_url'] : null;
						$feature_list    = null;

						$info_html = sprintf(
							'<div class="rt-product-info">%s%s%s</div>',
							$title ? sprintf( "<h3 class='rt-product-title'><a href='%s' target='_blank'>%s%s</a></h3>", esc_url( $url ), $title, $price ? ' ($' . $price . ')' : null ) : null,
							$feature_list,
							$buy_url || $demo_url || $doc_url ?
								sprintf(
									'<div class="rt-product-action">%s%s%s</div>',
									$buy_url ? sprintf( '<a class="rt-admin-btn button-primary" href="%s" target="_blank">%s</a>', esc_url( $buy_url ), esc_html__( 'Buy', 'tlp-food-menu' ) ) : null,
									$demo_url ? sprintf( '<a class="rt-admin-btn" href="%s" target="_blank">%s</a>', esc_url( $demo_url ), esc_html__( 'Demo', 'tlp-food-menu' ) ) : null,
									$doc_url ? sprintf( '<a class="rt-admin-btn" href="%s" target="_blank">%s</a>', esc_url( $doc_url ), esc_html__( 'Documentation', 'tlp-food-menu' ) ) : null
								)
								: null
						);

						$htmlProducts .= sprintf(
							'<div class="rt-product">%s%s</div>',
							$image_thumb_url ? sprintf(
								'<div class="rt-media"><img src="%s" alt="%s" /></div>',
								esc_url( $image_thumb_url ),
								esc_html( $title )
							) : null,
							$info_html
						);
					}

					$html .= sprintf( '<div class="rt-product-list">%s</div>', $htmlProducts );
				}
			}
		}

		return $html;
	}

	/**
	 * Returns true when viewing a product taxonomy archive.
	 *
	 * @return boolean
	 */
	public static function is_food_taxonomy() {
		return is_tax( get_object_taxonomies( TLPFoodMenu()->post_type ) );
	}

	/**
	 * Prints HTMl.
	 *
	 * @param string $html HTML.
	 * @param bool $allHtml All HTML.
	 *
	 * @return mixed
	 */
	public static function print_html( $html, $allHtml = false ) {
		if ( $allHtml ) {
			echo stripslashes_deep( $html ); //phpcs:ignore
		} else {
			echo wp_kses_post( stripslashes_deep( $html ) );
		}
	}

	/**
	 * Allowed HTML for wp_kses.
	 *
	 * @param string $level Tag level.
	 *
	 * @return mixed
	 */
	public static function allowedHtml( $level = 'basic' ) {
		$allowed_html = [];

		switch ( $level ) {
			case 'basic':
				$allowed_html = [
					'b'      => [
						'class' => [],
						'id'    => [],
					],
					'i'      => [
						'class' => [],
						'id'    => [],
					],
					'u'      => [
						'class' => [],
						'id'    => [],
					],
					'br'     => [
						'class' => [],
						'id'    => [],
					],
					'em'     => [
						'class' => [],
						'id'    => [],
					],
					'span'   => [
						'class' => [],
						'id'    => [],
					],
					'strong' => [
						'class' => [],
						'id'    => [],
					],
					'hr'     => [
						'class' => [],
						'id'    => [],
					],
					'p'      => [
						'class' => [],
						'id'    => [],
					],
					'div'    => [
						'class' => [],
						'id'    => [],
					],
					'a'      => [
						'href'   => [],
						'title'  => [],
						'class'  => [],
						'id'     => [],
						'target' => [],
					],
				];
				break;

			case 'advanced':
				$allowed_html = [
					'b'      => [
						'class' => [],
						'id'    => [],
					],
					'i'      => [
						'class' => [],
						'id'    => [],
					],
					'u'      => [
						'class' => [],
						'id'    => [],
					],
					'br'     => [
						'class' => [],
						'id'    => [],
					],
					'em'     => [
						'class' => [],
						'id'    => [],
					],
					'span'   => [
						'class' => [],
						'id'    => [],
					],
					'strong' => [
						'class' => [],
						'id'    => [],
					],
					'hr'     => [
						'class' => [],
						'id'    => [],
					],
					'a'      => [
						'href'   => [],
						'title'  => [],
						'class'  => [],
						'id'     => [],
						'target' => [],
					],
					'input'  => [
						'type'  => [],
						'name'  => [],
						'class' => [],
						'value' => [],
					],
				];
				break;

			case 'image':
				$allowed_html = [
					'img' => [
						'src'      => [],
						'data-src' => [],
						'alt'      => [],
						'height'   => [],
						'width'    => [],
						'class'    => [],
						'id'       => [],
						'style'    => [],
						'srcset'   => [],
						'loading'  => [],
						'sizes'    => [],
					],
					'div' => [
						'class' => [],
					],
				];
				break;

			case 'anchor':
				$allowed_html = [
					'a' => [
						'href'  => [],
						'title' => [],
						'class' => [],
						'id'    => [],
						'style' => [],
					],
				];
				break;

			default:
				// code...
				break;
		}

		return $allowed_html;
	}

	/**
	 * Definition for wp_kses.
	 *
	 * @param string $string String to check.
	 * @param string $level Tag level.
	 *
	 * @return mixed
	 */
	public static function htmlKses( $string, $level ) {
		if ( empty( $string ) ) {
			return;
		}

		return wp_kses( $string, self::allowedHtml( $level ) );
	}

	/**
	 * @param array $group options.
	 * @param string $option_key option key.
	 * @param string $default_value option default value.
	 *
	 * @return mixed|string
	 */
	public static function get_options_by_default_val( $group, $option_key, $default_value = '' ) {
		if ( ! $option_key || ! isset( $group[ $option_key ] ) ) {
			return $default_value;
		}

		return $group[ $option_key ];
	}

	/**
	 * Get food location terms.
	 *
	 * @param string $default_options .
	 * @param string $no_options .
	 * @param string $value_type .
	 * @param boolean $number .
	 *
	 * @return array
	 */
	public static function get_location_data( $default_options = '', $no_options = '', $value_type = 'key', $number = false ) {
		$default_options = esc_html__( 'Select Delivery Location', 'tlp-food-menu' );
		$no_options      = esc_html__( 'No Delivery Location is Set', 'tlp-food-menu' );

		// Get food locations.
		$fmp_location = get_terms(
			[
				'taxonomy'   => 'tpl-food-location',
				'hide_empty' => 0,
				'orderby'    => 'DESC',
				// 'parent'     => 0,
				'number'     => $number,
			]
		);

		$fmp_location_arr = [ '' => $default_options ];

		if ( ! empty( $fmp_location ) ) {
			foreach ( $fmp_location as $value ) {
				$key                      = ( 'key' === $value_type ) ? $value->slug : ( ( 'id' === $value_type ) ? $value->term_id : $value->name );
				$fmp_location_arr[ $key ] = $value->name;
			}
		}

		return $fmp_location_arr;
	}

	/**
	 * Render Html.
	 *
	 * @param string $content .
	 *
	 * @return mixed|string
	 */
	public static function fmp_render( $content ) {
		if ( '' == $content ) {
			return '';
		}

		return $content;
	}

	/**
	 * Get settings Options.
	 *
	 * @param string $key .
	 *
	 * @return array
	 */
	public static function get_settings_option( $key = null ) {
		$options = TLPFoodMenu()->options;
		$key     = ( null === $key && isset( $options['settings'] ) ) ? $options['settings'] : $key;

		return get_option( $key );
	}

	/**
	 * Product discount price.
	 *
	 * @param array $args .
	 *
	 * @return array|void
	 */
	public static function discount_price( $args ) {
		$defaults = [
			'product_id'    => null,
			'data'          => '',
			'product_price' => null,
			'addons_price'  => 0,
		];
		$args     = wp_parse_args( $args, $defaults );

		if ( ! class_exists( '\\RT\\FoodMenu\\Controllers\\Discount\\Discount' ) ) {
			return;
		}

		$fmp_check_discount = \RT\FoodMenu\Controllers\Discount\Discount::get_instance()->check_discount_of_product( $args['product_id'], null );
		if ( is_array( $fmp_check_discount ) && ! empty( $fmp_check_discount['percentage'] ) ) {
			$product    = wc_get_product( $args['product_id'] );
			$main_price = empty( $args['product_price'] ) ? ( wc_get_price_excluding_tax( $product ) ?: wc_get_price_including_tax( $product ) ) : $args['product_price'];
			$percentage = $fmp_check_discount['percentage'];
			$price_after_discount = (float) ( $percentage / 100 ) * (float) $main_price;
			$new_price            = (float) $main_price - (float) $price_after_discount;

			$addons_new_price     = $args['addons_price'];
			$settings             = self::get_settings_option();
			$discount_apply_addon = ! empty( $settings['fmp_applicable_discount'] ) && 'addon_total' === $settings['fmp_applicable_discount'];
			if ( $args['addons_price'] > 0 && $discount_apply_addon ) {
				$addons_price_discounted = (float) ( $percentage / 100 ) * (float) $args['addons_price'];
				$addons_new_price        = (float) $args['addons_price'] - (float) $addons_price_discounted;
			}

			if ( 'fmp_cart' == $args['data'] || 'fmp_cart_sub_total' == $args['data'] ) {
				return [
					'new_price'           => $new_price,
					'addons_new_price'    => $addons_new_price,
					'discount_percentage' => $percentage,
				];
			}

			return [
				'main_price'           => $main_price,
				'new_price'            => $new_price,
				'price_after_discount' => wc_price( $new_price ),
				'discount_percentage'  => $percentage,
				'discount_apply_to'    => $settings['fmp_applicable_discount'] ?? '',
				'addons_new_price'     => $addons_new_price,
			];
		}
	}

	/**
	 * Sanitize Recursive Array
	 *
	 * @param $input
	 *
	 * @return array|mixed|string
	 */
	public static function sanitize_recursive_array( $input ) {
		if ( is_array( $input ) ) {
			return array_map( [ __CLASS__, 'sanitize_recursive_array' ], $input );
		} else {
			return sanitize_text_field( wp_unslash( $input ) );
		}
	}


	/**
	 * Register Elementor widget controls.
	 *
	 * Adds different control fields into the widget settings.
	 *
	 * @param array $fields Control fields to add.
	 * @param object $obj Object in which controls are adding.
	 *
	 * @return void
	 *
	 * @access public
	 */
	public static function addElControls( $fields, $obj ) {
		foreach ( $fields as $field ) {
			if ( ! empty( $field['type'] ) ) {
				$field['type'] = self::elFields( $field['type'] );
			}
			if ( isset( $field['mode'] ) && 'section_start' === $field['mode'] ) {
				$id = $field['id'];
				unset( $field['id'] );
				unset( $field['mode'] );
				$obj->start_controls_section( $id, $field );
			} elseif ( isset( $field['mode'] ) && 'section_end' === $field['mode'] ) {
				$obj->end_controls_section();
			} elseif ( isset( $field['mode'] ) && 'tabs_start' === $field['mode'] ) {
				$id = $field['id'];
				unset( $field['id'] );
				unset( $field['mode'] );
				$obj->start_controls_tabs( $id );
			} elseif ( isset( $field['mode'] ) && 'tabs_end' === $field['mode'] ) {
				$obj->end_controls_tabs();
			} elseif ( isset( $field['mode'] ) && 'tab_start' === $field['mode'] ) {
				$id = $field['id'];
				unset( $field['id'] );
				unset( $field['mode'] );
				$obj->start_controls_tab( $id, $field );
			} elseif ( isset( $field['mode'] ) && 'tab_end' === $field['mode'] ) {
				$obj->end_controls_tab();
			} elseif ( isset( $field['mode'] ) && 'group' === $field['mode'] ) {
				$type          = $field['type'];
				$field['name'] = $field['id'];
				unset( $field['mode'] );
				unset( $field['type'] );
				unset( $field['id'] );
				$obj->add_group_control( $type, $field );
			} elseif ( isset( $field['mode'] ) && 'responsive' === $field['mode'] ) {
				$id = $field['id'];
				unset( $field['id'] );
				unset( $field['mode'] );
				$obj->add_responsive_control( $id, $field );
			} else {
				$id = $field['id'];
				unset( $field['id'] );
				$obj->add_control( $id, $field );
			}
		}
	}

	/**
	 * Elementor Fields.
	 *
	 * @param string $type Control type.
	 *
	 * @return object
	 */
	private static function elFields( $type ) {
		$controls = \Elementor\Controls_Manager::class;

		switch ( $type ) {
			case 'text':
				$type = $controls::TEXT;
				break;

			case 'html':
				$type = $controls::RAW_HTML;
				break;

			case 'select':
				$type = $controls::SELECT;
				break;

			case 'select2':
				$type = $controls::SELECT2;
				break;

			case 'number':
				$type = $controls::NUMBER;
				break;

			case 'image-dimensions':
				$type = $controls::IMAGE_DIMENSIONS;
				break;

			case 'dimensions':
				$type = $controls::DIMENSIONS;
				break;

			case 'media':
				$type = $controls::MEDIA;
				break;

			case 'switch':
				$type = $controls::SWITCHER;
				break;

			case 'color':
				$type = $controls::COLOR;
				break;

			case 'choose':
				$type = $controls::CHOOSE;
				break;

			case 'slider':
				$type = $controls::SLIDER;
				break;

			case 'typography':
				$type = \Elementor\Group_Control_Typography::get_type();
				break;

			case 'border':
				$type = \Elementor\Group_Control_Border::get_type();
				break;

			case 'shadow':
				$type = \Elementor\Group_Control_Box_Shadow::get_type();
				break;
		}

		return $type;
	}

	public static function get_all_wp_roles() {
		// Check if the wp_roles() function exists, which it should in any standard WordPress environment.
		if ( function_exists( 'wp_roles' ) ) {
			// Get the WP_Roles instance. It automatically creates it if it doesn't exist.
			$wp_roles = wp_roles();

			// Return an array of role slugs and translated names.
			$default_roles = [ '' => __( '-Select-', 'tlp-food-menu' ) ];
			$all_roles     = $wp_roles->get_names();

			return array_merge( $default_roles, $all_roles );
		}

		return [];
	}

	public static function get_all_page_list() {
		$args = [
			'sort_order'  => 'ASC',
			'sort_column' => 'post_title',
			'post_status' => 'publish' // Only get published pages
		];

		$pages_array = get_pages( $args );

		$simple_pages_array = [ '' => __( '-Select-', 'tlp-food-menu' ) ];
		foreach ( $pages_array as $page ) {
			$simple_pages_array[ $page->ID ] = "[ " . $page->ID . " ] " . $page->post_title;
		}

		return $simple_pages_array;
	}

	public static function is_black_friday_active() {
		// Black Friday valid between November 10 – Jan 5
		$currentYear = gmdate( 'Y' );
		$now         = current_time( 'timestamp', true );
		$start       = strtotime( "{$currentYear}-11-10" );
		$end         = strtotime( ( $currentYear + 1 ) . '-01-06' );

		$is_active = $now >= $start && $now <= $end;

		// If dismissed manually, consider inactive
		if ( get_option( 'rtfm_ny_2025' ) == '1' ) {
			$is_active = false;
		}

		return $is_active;

	}

	public static function is_inventory_activate() {
		return self::get_setting( 'fmp_enable_frontend_inventory', false ) &&
		       self::get_setting( 'fm_food_menu_type' ) === 'online_ordering';
	}

	public static function admin_pages() {
		$feature_list = [
			[
				'label' => 'All Foods',
				'url'   => admin_url() . 'edit.php?post_type=food-menu',
				'icon'  => 'List',
				'type'  => 'page',
			],
			[
				'label' => 'Add Food',
				'url'   => admin_url() . 'post-new.php?post_type=food-menu',
				'icon'  => 'Plus',
				'type'  => 'page',
			],
			[
				'label' => 'Categories',
				'url'   => admin_url() . 'edit-tags.php?taxonomy=food-menu-cat&post_type=food-menu',
				'icon'  => 'Tag',
				'type'  => 'page',
			],
			[
				'label' => 'Shortcode Generator',
				'url'   => admin_url() . 'edit.php?post_type=fmsc',
				'icon'  => 'Code',
				'type'  => 'page',
			],
			[
				'label' => 'Settings',
				'url'   => admin_url() . "edit.php?post_type=food-menu&page=food_menu_settings",
				'icon'  => 'SlidersHorizontal',
				'type'  => 'page',
			],
			[
				'label' => 'Get Help',
				'url'   => admin_url() . "edit.php?post_type=food-menu&page=rtfm_get_help",
				'icon'  => 'HelpCircle',
				'type'  => 'page',
			],
		];

		$settings = get_option( TLPFoodMenu()->options['settings'] );

		if ( TLPFoodMenu()->has_pro() ) {
			$feature_list[] = [
				'label' => 'Tags',
				'url'   => admin_url() . 'edit-tags.php?taxonomy=food-menu-tag&post_type=food-menu',
				'icon'  => 'Tag',
				'type'  => 'page',
			];

			$feature_list[] = [
				'label' => 'Ingredients',
				'url'   => admin_url() . 'edit-tags.php?taxonomy=food-menu-ingredient&post_type=food-menu',
				'icon'  => 'Leaf',
				'type'  => 'page',
			];

			$feature_list[] = [
				'label' => 'Nutrition',
				'url'   => admin_url() . 'edit-tags.php?taxonomy=food-menu-nutrition&post_type=food-menu',
				'icon'  => 'Heart',
				'type'  => 'page',
			];

			$feature_list[] = [
				'label' => 'Unit',
				'url'   => admin_url() . 'edit-tags.php?taxonomy=food-menu-unit&post_type=food-menu',
				'icon'  => 'Ruler',
				'type'  => 'page',
			];

			$feature_list[] = [
				'label' => 'Product Addons',
				'url'   => admin_url() . 'edit.php?post_type=food-menu&page=product_addons',
				'icon'  => 'PackagePlus',
				'type'  => 'page',
			];

			if ( ! empty( $settings['fmp_food_reservation_status'] ) ) {
				$feature_list[] = [
					'label' => 'Reservation Table Layout',
					'url'   => admin_url() . 'edit.php?post_type=food-menu&page=table_layout',
					'icon'  => 'LayoutGrid',
					'type'  => 'page',
				];
				$feature_list[] = [
					'label' => 'All Reservation',
					'url'   => admin_url() . 'edit.php?post_type=fmp_reservation',
					'icon'  => 'CalendarCheck',
					'type'  => 'page',
				];
			} else {
				$feature_list[] = [
					'label' => 'Enable Reservation',
					'url'   => admin_url() . 'edit.php?post_type=food-menu&page=food_menu_settings_updated#/reservation',
					'icon'  => 'Calendar',
					'type'  => 'page',
				];
			}
		}

		return apply_filters( 'tlpfm_admin_pages', $feature_list );
	}

	/**
	 * Convert a hex color to HSL components.
	 *
	 * @param string $hex Hex color (e.g. #e60000).
	 *
	 * @return array|null [h, s, l] where h is 0-360, s and l are 0-100, or null on invalid input.
	 */
	public static function hex_to_hsl( $hex ) {
		$hex = ltrim( $hex, '#' );

		if ( strlen( $hex ) !== 6 ) {
			return null;
		}

		$r = hexdec( substr( $hex, 0, 2 ) ) / 255;
		$g = hexdec( substr( $hex, 2, 2 ) ) / 255;
		$b = hexdec( substr( $hex, 4, 2 ) ) / 255;

		$max   = max( $r, $g, $b );
		$min   = min( $r, $g, $b );
		$delta = $max - $min;
		$l     = ( $max + $min ) / 2;

		if ( 0.0 === $delta ) {
			$h = 0;
			$s = 0;
		} else {
			$s = $l > 0.5 ? $delta / ( 2 - $max - $min ) : $delta / ( $max + $min );

			if ( $max === $r ) {
				$h = fmod( ( $g - $b ) / $delta, 6 );
			} elseif ( $max === $g ) {
				$h = ( $b - $r ) / $delta + 2;
			} else {
				$h = ( $r - $g ) / $delta + 4;
			}

			$h *= 60;
			if ( $h < 0 ) {
				$h += 360;
			}
		}

		return [
			round( $h, 1 ),
			round( $s * 100, 1 ),
			round( $l * 100, 1 ),
		];
	}

	/**
	 * Whether the reservation feature is enabled in settings.
	 *
	 * @return bool
	 */
	public static function enable_reservation() {
		return 'on' === self::get_setting( 'fmp_food_reservation_status', '' );
	}

	/**
	 * Available reservation statuses.
	 *
	 * @return array
	 */
	public static function get_reservation_status() {
		return [
			'pending'   => __( 'Pending', 'tlp-food-menu' ),
			'confirmed' => __( 'Confirmed', 'tlp-food-menu' ),
			'cancelled' => __( 'Cancelled', 'tlp-food-menu' ),
			'completed' => __( 'Completed', 'tlp-food-menu' ),
		];
	}

	/**
	 * Day-name → JS day index map (0=Sunday).
	 *
	 * @return int[]
	 */
	public static function day_map_index() {
		return [
			'sunday'    => 0,
			'monday'    => 1,
			'tuesday'   => 2,
			'wednesday' => 3,
			'thursday'  => 4,
			'friday'    => 5,
			'saturday'  => 6,
		];
	}

	/**
	 * Compute the list of off-days for a weekly schedule (JS day indexes).
	 *
	 * @param array $schedule .
	 *
	 * @return array
	 */
	public static function get_offday_schedule( $schedule ) {
		if ( ! is_array( $schedule ) ) {
			return [];
		}
		$day_map = self::day_map_index();
		$offdays = [];

		foreach ( $schedule as $day => $data ) {
			if ( ! isset( $day_map[ $day ] ) ) {
				continue;
			}

			if ( ( $data['is_open'] ?? 'yes' ) === 'no' ) {
				$offdays[] = $day_map[ $day ];
				continue;
			}

			$slots = $data['slots'] ?? [];
			if ( ! empty( $slots ) && is_array( $slots ) ) {
				$has_valid_slot = false;
				foreach ( $slots as $slot ) {
					if ( ! empty( $slot['start'] ) && ! empty( $slot['end'] ) ) {
						$has_valid_slot = true;
						break;
					}
				}
				if ( ! $has_valid_slot ) {
					$offdays[] = $day_map[ $day ];
				}
			}
		}

		return $offdays;
	}

	/**
	 * Per-day boundary times for a weekly schedule.
	 *
	 * @param array  $schedule .
	 * @param string $boundary 'start' or 'end'.
	 *
	 * @return array
	 */
	public static function get_schedule_time( $schedule, $boundary = 'start' ) {
		if ( empty( $schedule ) ) {
			return [];
		}
		$day_map    = self::day_map_index();
		$days_time  = [];
		$slot_index = $boundary === 'start' ? 'start' : 'end';
		foreach ( $day_map as $day => $day_index ) {
			$day_data = $schedule[ $day ] ?? [];
			$slots    = $day_data['slots'] ?? [];

			if ( empty( $slots ) || ! is_array( $slots ) ) {
				$days_time[] = '';
				continue;
			}

			$times = array_filter( array_column( $slots, $slot_index ) );
			if ( empty( $times ) ) {
				$days_time[] = '';
			} elseif ( $boundary === 'start' ) {
				$days_time[] = min( $times );
			} else {
				$days_time[] = max( $times );
			}
		}

		return $days_time;
	}

	/**
	 * Per-day raw slot array for a weekly schedule (indexed by JS day number).
	 *
	 * @param array $schedule .
	 *
	 * @return array
	 */
	public static function get_schedule_slots( $schedule ) {
		if ( empty( $schedule ) ) {
			return [];
		}
		$day_map    = self::day_map_index();
		$days_slots = [];
		foreach ( $day_map as $day => $day_index ) {
			$day_data                 = $schedule[ $day ] ?? [];
			$slots                    = $day_data['slots'] ?? [];
			$days_slots[ $day_index ] = is_array( $slots ) ? array_values( $slots ) : [];
		}

		return $days_slots;
	}

	/**
	 * Parse a time string into seconds since midnight.
	 *
	 * Handles WP time formats including H:i, h:i A, G\hi ("12h30"), H.i, etc.
	 * Extracts the first two numeric groups regardless of separator.
	 *
	 * @param string $time_str .
	 *
	 * @return int Seconds since midnight (0–86399).
	 */
	public static function parse_time_to_seconds( $time_str ) {
		if ( empty( $time_str ) ) {
			return 0;
		}

		if ( ! preg_match_all( '/\d+/', (string) $time_str, $m ) || empty( $m[0] ) ) {
			return 0;
		}

		$hours   = (int) $m[0][0];
		$minutes = isset( $m[0][1] ) ? (int) $m[0][1] : 0;

		$upper = strtoupper( (string) $time_str );
		if ( false !== strpos( $upper, 'PM' ) && 12 !== $hours ) {
			$hours += 12;
		} elseif ( false !== strpos( $upper, 'AM' ) && 12 === $hours ) {
			$hours = 0;
		}

		return ( $hours * 3600 ) + ( $minutes * 60 );
	}

	/**
	 * Inclusive guest-number range based on saved seat capacity / min / max.
	 *
	 * @return array<int,int>
	 */
	public static function get_guest_limit() {
		$settings         = self::get_settings_option();
		$settings         = is_array( $settings ) ? $settings : [];
		$seat_capacity    = ! empty( $settings['fmp_resi_seat_capacity'] )
			? (int) $settings['fmp_resi_seat_capacity']
			: 30;
		$min_guest_number = ! empty( $settings['fmp_resi_min_guest'] ) ? (int) $settings['fmp_resi_min_guest'] : 1;
		$max_guest_number = ! empty( $settings['fmp_resi_max_guest'] ) ? (int) $settings['fmp_resi_max_guest'] : $seat_capacity;

		$min_guest_number = max( 1, $min_guest_number );
		$max_guest_number = max( $min_guest_number, $max_guest_number );

		$guest_limit = [];
		for ( $guest_number = $min_guest_number; $guest_number <= $max_guest_number; $guest_number ++ ) {
			$guest_limit[ $guest_number ] = $guest_number;
		}

		return $guest_limit;
	}

	/**
	 * Generate a short invoice/booking ID for a reservation post.
	 *
	 * @param int $postId .
	 *
	 * @return string
	 */
	public static function generate_invoice_number( $postId ) {
		$prefix = apply_filters( 'fmp_booking_id_prefix', 'fm' );

		return $prefix . $postId;
	}

	/**
	 * Default labels for reservation detail fields.
	 *
	 * @return array
	 */
	public static function reservation_fields_array() {
		return [
			'fmp_resi_meta_invoice'    => __( 'Booking ID', 'tlp-food-menu' ),
			'fmp_resi_meta_date'       => __( 'Reservation Date', 'tlp-food-menu' ),
			'fmp_resi_meta_start_time' => __( 'Start Time', 'tlp-food-menu' ),
			'fmp_resi_meta_end_time'   => __( 'End Time', 'tlp-food-menu' ),
			'fmp_resi_meta_seat'       => __( 'Seat', 'tlp-food-menu' ),
			'fmp_resi_meta_name'       => __( 'Name', 'tlp-food-menu' ),
			'fmp_resi_meta_email'      => __( 'Email', 'tlp-food-menu' ),
			'fmp_resi_meta_phone'      => __( 'Phone', 'tlp-food-menu' ),
			'fmp_resi_meta_message'    => __( 'Message', 'tlp-food-menu' ),
			'fmp_resi_meta_status'     => __( 'Status', 'tlp-food-menu' ),
		];
	}

	/**
	 * Default / configured reservation status message.
	 *
	 * @param array  $settings  .
	 * @param string $type      'pending' | 'confirm' | 'cancel'.
	 * @param string $booking_id Optional booking ID to append.
	 *
	 * @return string
	 */
	public static function get_reservation_message( $settings, $type, $booking_id = '' ) {
		$default_messages = [
			'pending' => esc_html__( 'Thank you for your booking. Your reservation is currently pending. We will notify you once it has been confirmed.', 'tlp-food-menu' ),
			'confirm' => esc_html__( 'Thank you for your booking. Your reservation has been successfully confirmed.', 'tlp-food-menu' ),
			'cancel'  => esc_html__( 'Your cancellation request has been successfully processed.', 'tlp-food-menu' ),
		];

		$settings_keys = [
			'pending' => 'fmp_resi_pending_msg',
			'confirm' => 'fmp_resi_confirm_msg',
			'cancel'  => 'fmp_resi_cancel_msg',
		];

		$message = ! empty( $settings[ $settings_keys[ $type ] ] ) ? $settings[ $settings_keys[ $type ] ] : $default_messages[ $type ];

		if ( $booking_id ) {
			$message .= " - Booking ID: $booking_id";
		}

		return $message;
	}

	/**
	 * Send a reservation email via wp_mail.
	 *
	 * @param array $args .
	 *
	 * @return bool|mixed
	 */
	public static function send_email( $args ) {
		$body      = wpautop( html_entity_decode( $args['mail_body'] ) );
		$from_name = html_entity_decode( $args['from_name'] );
		$headers   = [
			'Content-Type: text/html; charset=UTF-8',
			'From: ' . $from_name . ' <' . $args['from'] . '>',
		];

		return wp_mail( $args['to'], $args['subject'], $body, $headers );
	}

	/**
	 * Build the HTML body for a reservation email.
	 *
	 * @param array  $args    .
	 * @param string $context 'client' | 'admin'.
	 *
	 * @return string
	 */
	public static function mail_body_markup( $args, $context = 'client' ) {
		$total_seat   = '';
		if ( ! empty( $args['resi_id'] ) ) {
			$booked_seats_info = apply_filters( 'fmp/reservation/booked_seats_info', [], (int) $args['resi_id'] );
			if ( ! empty( $booked_seats_info ) && is_array( $booked_seats_info ) ) {
				$total_seat = join( '<br>', $booked_seats_info );
			}
		}
		$booking_date = $args['booking_date'] ?? '';
		$rows         = '';

		$color = '';
		if ( ! empty( $args['status'] ) ) {
			$status = $args['status'];
			if ( 'confirmed' === $status ) {
				$color = '#28a745';
			} elseif ( 'pending' === $status ) {
				$color = '#F3B604';
			} elseif ( 'cancelled' === $status ) {
				$color = '#6c757d';
			} elseif ( 'completed' === $status ) {
				$color = '#007bff';
			}
		}

		if ( 'admin' === $context && ! empty( $args['recipient'] ) ) {
			$rows .= '
	<tr>
	  <td style="vertical-align: top;padding: 10px 0; border-bottom: 1px solid #eeeeee; font-weight: bold; color: #333333;">Customer Info:</td>
	  <td style="vertical-align: top;padding: 10px 0; border-bottom: 1px solid #eeeeee; color: #555555;">' . esc_html( $args['recipient'] ) . '<br>' . esc_html( $args['resi_email'] ?? '' ) . '<br>' . esc_html( $args['fmp_phone'] ?? '' ) . '</td>
	</tr>';
		}

		if ( ! empty( $args['invoice'] ) ) {
			$rows .= '
	<tr>
	  <td style="vertical-align: top;padding: 10px 0; border-bottom: 1px solid #eeeeee; font-weight: bold; color: #333333;">Reservation ID:</td>
	  <td style="vertical-align: top;padding: 10px 0; border-bottom: 1px solid #eeeeee; color: #555555;">' . esc_html( $args['invoice'] ) . '</td>
	</tr>';
		}

		if ( ! empty( $booking_date ) ) {
			$rows .= '
	<tr>
	  <td style="vertical-align: top;padding: 10px 0; border-bottom: 1px solid #eeeeee; font-weight: bold; color: #333333;">Booking Date:</td>
	  <td style="vertical-align: top;padding: 10px 0; border-bottom: 1px solid #eeeeee; color: #555555;">' . esc_html( $booking_date ) . '</td>
	</tr>';
		}

		if ( ! empty( $total_seat ) ) {
			$rows .= '
	<tr>
	  <td style="vertical-align: top;padding: 10px 0; border-bottom: 1px solid #eeeeee; font-weight: bold; color: #333333;">Seats:</td>
	  <td style="vertical-align: top;padding: 10px 0; border-bottom: 1px solid #eeeeee; color: #555555;">' . wp_kses_post( $total_seat ) . '</td>
	</tr>';
		}

		return '
<!DOCTYPE html>
<html>
<head>
  <meta charset="UTF-8">
  <title>Reservation Confirmation</title>
</head>
<body style="font-family: Arial, sans-serif; background-color: #f4f4f4; padding: 20px; margin: 0;">
  <table width="100%" cellpadding="0" cellspacing="0" style="max-width: 600px; margin: auto; background-color: #ffffff; border-radius: 8px; box-shadow: 0 2px 5px rgba(0,0,0,0.1); overflow: hidden;">
    <tr>
      <td style="vertical-align: top;background-color: ' . $color . '; padding: 20px; color: #ffffff; text-align: center;">
        <h2 style="margin: 0;">Reservation Details</h2>
      </td>
    </tr>
    <tr>
      <td style="vertical-align: top;padding: 20px;">
        <p style="font-size: 16px; color: #555555;">' . nl2br( esc_html( $args['msg'] ?? '' ) ) . '</p>

        <table cellpadding="0" cellspacing="0" width="100%" style="margin-top: 20px; border-collapse: collapse;">'
		       . $rows .
		       '</table>
      </td>
    </tr>
    <tr>
      <td style="vertical-align: top;padding: 20px; text-align: center; font-size: 12px; color: #888888;">
        &copy; ' . gmdate( 'Y' ) . ' ' . esc_html( get_bloginfo( 'name' ) ) . '. All rights reserved.
      </td>
    </tr>
  </table>
</body>
</html>';
	}

	/**
	 * Send admin and/or user notification for a reservation status change.
	 *
	 * @param array $settings .
	 * @param array $args     .
	 *
	 * @return void
	 */
	public static function send_email_notify_admin_user( $settings, $args ) {
		$sender_mail  = ! empty( $settings['fmp_resi_sender_email'] ) ? $settings['fmp_resi_sender_email'] : get_bloginfo( 'admin_email' );
		$receive_mail = ! empty( $settings['fmp_resi_receive_email'] ) ? $settings['fmp_resi_receive_email'] : $sender_mail;

		$admin_on = isset( $args['admin_notify'] ) && 'off' !== $args['admin_notify'];
		$user_on  = isset( $args['user_notify'] ) && 'off' !== $args['user_notify'];

		if ( $admin_on ) {
			$args['msg'] = sprintf( 'Hi Admin, you have a new reservation request from %s.', $args['resi_email'] );

			if ( 'cancelled' === $args['status'] ) {
				$args['msg'] = sprintf( 'You have received a reservation cancellation request from %s.', $args['resi_email'] );
			}

			$mail_body      = self::mail_body_markup( $args, 'admin' );
			$mail_to        = $receive_mail;
			$mail_from      = ! empty( $settings['fmp_resi_sender_email'] ) ? $settings['fmp_resi_sender_email'] : $receive_mail;
			$mail_subject   = esc_html__( 'New Reservation Request', 'tlp-food-menu' );
			$mail_from_name = esc_html__( 'Admin', 'tlp-food-menu' );

			self::send_email( [
				'to'        => $mail_to,
				'subject'   => $mail_subject,
				'mail_body' => $mail_body,
				'from'      => $mail_from,
				'from_name' => $mail_from_name,
			] );
		}

		if ( $user_on ) {
			$args['msg']    = $args['message'];
			$mail_to        = $args['resi_email'];
			$mail_subject   = esc_html__( 'Hello ', 'tlp-food-menu' ) . ' ' . ( $args['recipient'] ?? '' );
			$mail_from      = ! empty( $settings['fmp_resi_sender_email'] ) ? $settings['fmp_resi_sender_email'] : $receive_mail;
			$mail_from_name = $sender_mail;
			$mail_body      = self::mail_body_markup( $args );

			self::send_email( [
				'to'        => $mail_to,
				'subject'   => $mail_subject,
				'mail_body' => $mail_body,
				'from'      => $mail_from,
				'from_name' => $mail_from_name,
			] );
		}
	}

	/**
	 * Dispatch reservation status email by current status.
	 *
	 * @param array  $mail_args .
	 * @param string $resi_email .
	 * @param string $invoice_no .
	 * @param string $resi_status .
	 *
	 * @return void
	 */
	public static function send_email_notify( $mail_args, $resi_email, $invoice_no, $resi_status ) {
		if ( empty( $resi_status ) || empty( $invoice_no ) ) {
			return;
		}

		$settings  = self::get_settings_option();
		$notify_on = static function ( $value ) {
			return 'off' === $value ? 'off' : 'on';
		};

		$status_map = [
			'cancelled' => [
				'message_type'  => 'cancel',
				'admin_setting' => 'fmp_resi_admin_cancel_notify',
				'user_setting'  => 'fmp_resi_user_cancel_notify',
			],
			'confirmed' => [
				'message_type'  => 'confirm',
				'admin_setting' => 'fmp_resi_admin_confirm_notify',
				'user_setting'  => 'fmp_resi_user_confirm_notify',
			],
			'pending'   => [
				'message_type'  => 'pending',
				'admin_setting' => 'fmp_resi_admin_pending_notify',
				'user_setting'  => 'fmp_resi_user_pending_notify',
			],
		];

		if ( ! isset( $status_map[ $resi_status ] ) ) {
			return;
		}

		$map     = $status_map[ $resi_status ];
		$message = self::get_reservation_message( $settings, $map['message_type'] );
		$args    = [
			'recipient'    => get_post_meta( $mail_args['ID'], 'fmp_resi_meta_name', true ),
			'resi_id'      => $mail_args['ID'],
			'booking_date' => $mail_args['booking_date'] ?? '',
			'invoice'      => $invoice_no,
			'resi_email'   => $resi_email,
			'message'      => $message,
			'admin_notify' => $notify_on( $settings[ $map['admin_setting'] ] ?? '' ),
			'user_notify'  => $notify_on( $settings[ $map['user_setting'] ] ?? '' ),
			'status'       => $resi_status,
		];
		self::send_email_notify_admin_user( $settings, $args );
	}

	/**
	 * Booking-overlap helper used by the visual table layout (Pro).
	 *
	 * Returns capacity/booked IDs for the given slot. Kept in free so Pro's
	 * table-layout integration can call it without duplicating the query.
	 *
	 * @param string $selected_date .
	 * @param string $start_time .
	 * @param string $end_time .
	 *
	 * @return array
	 */
	public static function get_booking_data( $selected_date = '', $start_time = '', $end_time = '' ) {
		$booking_data = [
			'booking_open'     => 0,
			'booked_total'     => 0,
			'capacity'         => 0,
			'booked_ids'       => [],
			'booked_table_ids' => [],
		];

		if ( empty( $selected_date ) || ! class_exists( '\\RT\\FoodMenu\\Controllers\\Reservation\\Reservation' ) ) {
			return $booking_data;
		}

		$reservation              = \RT\FoodMenu\Controllers\Reservation\Reservation::get_instance()->resi_capacity_status( $selected_date, $start_time, $end_time, 'table_layout', 0 );
		$booking_data['capacity'] = $reservation['capacity'];

		if ( in_array( $reservation['status'], [ 'open', 'closed' ], true ) ) {
			$booking_data['booking_open']     = 'open' === $reservation['status'] ? 1 : 0;
			$booking_data['booked_total']     = $reservation['date_booked_total'] ?? 0;
			$booking_data['booked_ids']       = ! empty( $reservation['date_booked_ids'] ) ? array_merge( ...$reservation['date_booked_ids'] ) : [];
			$booking_data['booked_table_ids'] = ! empty( $reservation['date_booked_table_ids'] ) ? array_merge( ...$reservation['date_booked_table_ids'] ) : [];
		}

		return $booking_data;
	}
}

