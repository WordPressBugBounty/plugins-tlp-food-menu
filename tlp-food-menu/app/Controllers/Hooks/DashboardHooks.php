<?php
// phpcs:disable WordPress.NamingConventions.PrefixAllGlobals -- Established plugin: public namespace, hook names, functions and theme-overridable template variables must stay unchanged for backward compatibility.
/**
 * Action Hook Class.
 *
 * @package RT_FoodMenu
 */

namespace RT\FoodMenu\Controllers\Hooks;

use RT\FoodMenu\Helpers\Fns;
use RT\FoodMenu\Traits\SingletonTrait;
use RT\FoodMenuPro\Helpers\FnsPro;

// Do not allow directly accessing this file.
if ( ! defined( 'ABSPATH' ) ) {
    exit( 'This script cannot be accessed directly.' );
}
//phpcs:disable PluginCheck.CodeAnalysis.ImageFunctions.NonEnqueuedImage

/**
 * Action Hook Class.
 */
class DashboardHooks {

    use SingletonTrait;


    /**
     * Class Init.
     *
     * @return void
     */
    protected function init() {
        $this->dashboard_link_modify();
        add_action( 'in_admin_header', [ $this, 'remove_admin_notice' ], 99 );
    }

    public function remove_admin_notice() {
        $screen = get_current_screen();

        // phpcs:ignore WordPress.Security.NonceVerification.Recommended -- Read-only admin screen check; no form data processed.
        if ( $screen && $screen->post_type === TLPFoodMenu()->post_type && ! empty( $_GET['page'] ) ) {
            remove_all_actions( 'admin_notices' );
            remove_all_actions( 'all_admin_notices' );
        }
    }

    protected function dashboard_link_modify() {
        add_action( 'admin_head', function () {
            $post_type = TLPFoodMenu()->post_type;
            ?>
            <style>
                #adminmenu #menu-posts-food-menu .wp-menu-image img {
                    display: inline;
                }
                #adminmenu #menu-posts-food-menu ul a[href="edit.php?post_type=<?php echo esc_attr( $post_type ); ?>&page=fmp_dashboard"] {
                    position: absolute;
                    top: 0;
                    left: 0;
                    opacity: 0;
                }
            </style>

            <script>
                document.addEventListener('DOMContentLoaded', function () {
                    const link = document.querySelector('#menu-posts-food-menu > a');

                    if (link) {
                        link.href = 'edit.php?post_type=food-menu&page=fmp_dashboard';
                    }
                });
            </script>
            <?php
        } );
    }

}
