<?php
// phpcs:disable WordPress.NamingConventions.PrefixAllGlobals -- Established plugin: public namespace, hook names, functions and theme-overridable template variables must stay unchanged for backward compatibility.
/**
 * Main MiniCart class.
 *
 * @package RT_FoodMenu
 */

namespace RT\FoodMenu\Controllers\MiniCart;

use RT\FoodMenu\Helpers\Fns;
use RT\FoodMenu\Traits\SingletonTrait;

defined( 'ABSPATH' ) || exit();
//phpcs:disable PluginCheck.CodeAnalysis.ImageFunctions.NonEnqueuedImage

/**
 * Main FilterHooks class.
 */
class MiniCart {

    /**
     * Singleton Trait.
     */
    use SingletonTrait;

    /**
     * @var array|mixed
     */
    private $options;

    /**
     * Class constructor
     */
    public function init() {
        $this->options    = Fns::get_settings_option();
        $enable_mini_cart = $this->options['enable_mini_cart'] ?? 'on';
        if ( TLPFoodMenu()->isWcActive() && ! empty( $enable_mini_cart ) && 'on' == $enable_mini_cart ) {
            MiniCartHooks::get_instance();
            add_action( 'wp_enqueue_scripts', [ $this, 'enqueue_public_scripts' ], 99 );
            add_action( 'wp_footer', [ $this, 'render' ] );
        }
    }

    /**
     * Public CSS
     *
     * @return void
     */
    public function enqueue_public_scripts() {
        $opt                       = $this->options;
        $dynamic_css               = '';
        $mini_cart_custom_selector = $opt['mini_cart_custom_selector'] ?? '';
        $nonce                     = wp_create_nonce( Fns::nonceText() );

        // woocommerce js file.
        wp_enqueue_script( 'wc-cart-fragments' );

        // Wipe WC's cached mini-cart fragments before the wc-cart-fragments
        // script reads them, so the page always uses the freshly server-rendered
        // mini-cart HTML and never a stale copy from sessionStorage.
        $clear_fragments_js = "(function(){try{var keys=[];for(var i=0;i<sessionStorage.length;i++){var k=sessionStorage.key(i);if(k&&(k.indexOf('wc_fragments_')===0||k==='wc_cart_hash'||k==='wc_cart_created')){keys.push(k);}}keys.forEach(function(k){sessionStorage.removeItem(k);});}catch(e){}})();";
        wp_add_inline_script( 'wc-cart-fragments', $clear_fragments_js, 'before' );
        // mini cart js.
        wp_enqueue_script( 'fm-ajax-minicart' );
        wp_localize_script(
                'fm-ajax-minicart',
                'fmpMiniCartParamsPro',
                [
                        'nonceID'                => esc_attr( Fns::nonceId() ),
                        'nonce'                  => esc_attr( $nonce ),
                        'ajaxurl'                => esc_url( admin_url( 'admin-ajax.php' ) ),
                        'miniCartCustomSelector' => $mini_cart_custom_selector,
                ]
        );

        // Float button style.
        $pre = '#fmp-cart-float-menu';
        if ( ! empty( $opt['mini_cart_float_btn_radius'] ) ) {
            $dynamic_css .= "$pre {border-radius:{$opt['mini_cart_float_btn_radius']};overflow:hidden}";
        }
        if ( ! empty( $opt['mini_cart_float_bg'] ) ) {
            $dynamic_css .= "{$pre} {--fmp-float-bg:{$opt['mini_cart_float_bg']};}";
        }
        if ( ! empty( $opt['mini_cart_float_bg_hover'] ) ) {
            $dynamic_css .= "{$pre} {--fmp-float-bg-hover:{$opt['mini_cart_float_bg_hover']};}";
        }
        if ( ! empty( $opt['mini_cart_btn_width'] ) ) {
            $dynamic_css .= "$pre {min-width:{$opt['mini_cart_btn_width']}px}";
        }

        if ( ! empty( $dynamic_css ) ) {
            wp_add_inline_style( 'fmp-frontend', $dynamic_css );
        }
    }

    /**
     * Mini cart markup
     *
     * @return void
     */
    public function render() {
        add_filter( 'woocommerce_widget_cart_is_hidden', '__return_true' );

        if ( ! apply_filters( 'tlp_mini_cart_enabled', true ) ) {
            return;
        }

        if ( is_cart() || is_checkout() ) {
            return;
        }

        // WC()->cart is only initialized on the frontend; on admin screens
        // (e.g. widgets.php) it is null, so bail before touching the cart.
        if ( is_admin() || ! ( WC()->cart instanceof \WC_Cart ) ) {
            return;
        }

        $exclude_pages = $this->options['mini_cart_exclude_pages'] ?? [];
        if ( ! empty( $exclude_pages ) && is_array( $exclude_pages ) ) {
            $excluded_page_ids   = [];
            $excluded_post_types = [];
            foreach ( $exclude_pages as $item ) {
                if ( 0 === strpos( $item, 'page_' ) ) {
                    $excluded_page_ids[] = (int) substr( $item, 5 );
                } elseif ( 0 === strpos( $item, 'pt_' ) ) {
                    $excluded_post_types[] = substr( $item, 3 );
                }
            }

            if ( ! empty( $excluded_page_ids ) && is_page( $excluded_page_ids ) ) {
                return;
            }

            if ( ! empty( $excluded_post_types ) ) {
                $current_post_type = '';
                if ( is_singular() ) {
                    $current_post_type = get_post_type();
                } elseif ( is_post_type_archive() ) {
                    $current_post_type = get_query_var( 'post_type' );
                    if ( is_array( $current_post_type ) ) {
                        $current_post_type = reset( $current_post_type );
                    }
                } elseif ( is_tax() || is_category() || is_tag() ) {
                    $term = get_queried_object();
                    if ( $term && ! empty( $term->taxonomy ) ) {
                        $tax_obj = get_taxonomy( $term->taxonomy );
                        if ( $tax_obj && ! empty( $tax_obj->object_type ) ) {
                            foreach ( $tax_obj->object_type as $linked_pt ) {
                                if ( in_array( $linked_pt, $excluded_post_types, true ) ) {
                                    return;
                                }
                            }
                        }
                    }
                } elseif ( is_home() ) {
                    $current_post_type = 'post';
                }

                if ( $current_post_type && in_array( $current_post_type, $excluded_post_types, true ) ) {
                    return;
                }
            }
        }

        $opt = $this->options;

        $count_label         = WC()->cart->get_cart_contents_count() < 2 ? __( 'Item', 'tlp-food-menu' ) : __( 'Items', 'tlp-food-menu' );
        $cart_drawer_classes = ' ' . ( $opt['mini_cart_drawer_style'] ?? 'style1' );
        $cart_drawer_classes .= ' ' . ( $opt['mini_cart_open_style'] ?? 'open-always' );

        $fmp_float_classes = ' ' . ( $opt['mini_cart_position'] ?? 'left_center' );
        $fmp_float_classes .= ' ' . ( $opt['mini_cart_float_btn_style'] ?? 'style1' );

        // Offset mini cart above location button when both share the same bottom position.
        // Floating location button defaults to on (see FoodLocation::init) —
        // absent value means enabled; only an explicit '' disables it.
        $location_enabled  = ! empty( $opt['fmp_food_location_popup'] ) && 'on' === ( $opt['fmp_floating_location_btn'] ?? 'on' );
        $location_position = $opt['fmp_location_float_position'] ?? 'right_bottom';
        $mini_cart_pos     = $opt['mini_cart_position'] ?? 'left_center';

        if ( $location_enabled && $mini_cart_pos === $location_position ) {
            $fmp_float_classes .= ' fmp-mini-cart-offset';
        }
        $show_on_mobile    = $opt['mini_cart_show_on_mobile'] ?? 'on';
        if ( empty( $show_on_mobile ) && 'on' !== $show_on_mobile ) {
            $fmp_float_classes   .= ' fmp-hide-mobile';
            $cart_drawer_classes .= ' fmp-hide-mobile';
        }

        $default_image_path = TLPFoodMenu()->assets_url() . 'images/mini-cart-loading.gif';
        if ( ! empty( $opt['mini_cart_loading_image'] ) ) {
            $loading_json    = json_decode( stripslashes( $opt['mini_cart_loading_image'] ), true );
            $loading_img_src = $loading_json['source'] ?? $default_image_path;
        } else {
            $loading_img_src = $default_image_path;
        }

        $has_ovelay = Fns::get_options_by_default_val( $opt, 'mini_cart_overlay_visibility', 'on' );
        ?>
        <div id="fmp-cart-float-menu" class="fmp-cart-float-menu <?php echo esc_attr( $fmp_float_classes ); ?>">
            <div class="fmp-cart-float-inner">
				<span class="cart-icon">
					<span class="cart-icon-svg"></span>
					<span class="cart-number-wrapper">
						<span class="fmp-cart-icon-num">
							<?php Fns::print_html( WC()->cart->get_cart_contents_count() ); ?>
						</span>
						<span class="item-label"><?php echo esc_html( $count_label ); ?></span>
					</span>
				</span>
                <span class="fmp-cart-icon-total">
					<?php echo wc_price( WC()->cart->get_cart_contents_total() ); //phpcs:ignore ?>
				</span>
            </div>
        </div>

        <!-- Minicart Drawer -->
        <div class="fmp-drawer-container fmp-minicart-drawer <?php echo esc_attr( $cart_drawer_classes ); ?>">
            <span class="close"></span>
            <div id="fmp-side-content-area-id">
                <img class="loading-cart" src="<?php echo esc_url( $loading_img_src ); ?>" alt="<?php echo esc_attr__( 'Loadding...', 'tlp-food-menu' ); ?>">
            </div>
        </div>

        <?php if ( 'on' === $has_ovelay ) : ?>
            <div class="drawer-overlay"></div>
        <?php
        endif;
    }

}
