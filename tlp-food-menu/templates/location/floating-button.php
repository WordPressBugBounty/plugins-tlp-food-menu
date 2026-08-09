<?php
// phpcs:disable WordPress.NamingConventions.PrefixAllGlobals -- Established plugin: public namespace, hook names, functions and theme-overridable template variables must stay unchanged for backward compatibility.
/**
 * Floating button component
 *
 * @package RT_FoodMenu
 *
 * @var array $position
 */

defined( 'ABSPATH' ) || exit;

if ( ! apply_filters( 'tlp_floating_location_btn', true ) ) {
	return;
}
?>
<div id="fmp-location-float-btn" class="fmp-location-float-btn <?php echo esc_attr( $position ); ?>" style="display:none">
	<svg class="fmp-location-float-icon" xmlns="http://www.w3.org/2000/svg" width="18" height="18" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round">
		<path d="M21 10c0 7-9 13-9 13s-9-6-9-13a9 9 0 0 1 18 0z"/>
		<circle cx="12" cy="10" r="3"/>
	</svg>
	<span class="fmp-location-float-name"></span>
	<span class="fmp-location-float-change"><?php echo esc_html__( 'Change', 'tlp-food-menu' ); ?></span>
</div>
