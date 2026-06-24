<?php
/**
 * Public Controller Class.
 *
 * @package RT_FoodMenu
 */

namespace RT\FoodMenu\Controllers;

use RT\FoodMenu\Widgets;
use RT\FoodMenu\Abstracts\Controller;
use RT\FoodMenu\Controllers\Hooks;
use RT\FoodMenu\Controllers\Frontend;
use RT\FoodMenu\Controllers\Tip\Tip;
use RT\FoodMenu\Controllers\SpecialMenu\SpecialMenu;
use RT\FoodMenu\Controllers\Discount\Discount;

// Do not allow directly accessing this file.
if ( ! defined( 'ABSPATH' ) ) {
	exit( 'This script cannot be accessed directly.' );
}

/**
 * Admin Controller Class.
 */
class FrontendController extends Controller {
	use \RT\FoodMenu\Traits\SingletonTrait;

	/**
	 * Classes to include.
	 *
	 * @return array
	 */
	public function classes() {
		$classes = [];

		$classes[] = Hooks\ActionHooks::class;
		$classes[] = Hooks\FilterHooks::class;
		$classes[] = Hooks\DashboardHooks::class;
		$classes[] = Widgets\Vc\VcAddon::class;
		$classes[] = Frontend\Shortcode::class;
		$classes[] = Frontend\Template::class;
		$classes[] = Frontend\Styles::class;
		$classes[] = Frontend\ElementorAddons::class;
		$classes[] = Frontend\FoodLocation::class;
		$classes[] = Tip::class;
		$classes[] = SpecialMenu::class;
		$classes[] = Discount::class;

		return $classes;
	}
}
