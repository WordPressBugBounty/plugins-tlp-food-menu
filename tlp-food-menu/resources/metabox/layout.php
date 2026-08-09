<?php
// phpcs:disable WordPress.NamingConventions.PrefixAllGlobals -- Established plugin: public namespace, hook names, functions and theme-overridable template variables must stay unchanged for backward compatibility.
/**
 * Shortcode Metabox: Layout.
 *
 * @package RT_FoodMenu
 */

use RT\FoodMenu\Helpers\Fns;
use RT\FoodMenu\Helpers\Options;

// Do not allow directly accessing this file.
if ( ! defined( 'ABSPATH' ) ) {
	exit( 'This script cannot be accessed directly.' );
}

Fns::print_html( Fns::rtFieldGenerator( Options::scLayoutMetaFields() ), true );
?>

<div class="rt-responsive-column">
	<?php
	Fns::print_html( Fns::rtFieldGenerator( Options::scResponsiveMetaFields() ), true );
	?>
</div>

<?php
do_action( 'fmp_sc_meta_after_columns' );
?>

<?php $is_pagination_pro_locked = ! TLPFoodMenu()->has_pro(); ?>
<div class="rt-field-wrapper rt-field-group<?php echo $is_pagination_pro_locked ? ' rt-pro-field' : ''; ?>" id="rtfm_pagination">
	<div class="rt-label">
		<?php esc_html_e( 'Pagination Settings', 'tlp-food-menu' ); ?>
		<?php if ( $is_pagination_pro_locked ) : ?>
			<span class="rtfm-tooltip"><?php esc_html_e( 'Pro', 'tlp-food-menu' ); ?></span>
		<?php endif; ?>
	</div>
	<div class="rt-field">
		<?php
		Fns::print_html( Fns::rtFieldGenerator( Options::scPaginationFields() ), true );
		?>
	</div>
</div>
<div class="rt-field-wrapper rt-field-group" id="rtfm_category_title">
	<div class="rt-label">Category Title Settings</div>
	<div class="rt-field">
		<?php
		Fns::print_html( Fns::rtFieldGenerator( Options::scCategoryTitleFields() ), true );
		?>
	</div>
</div>
<div class="rt-field-wrapper rt-field-group">
	<div class="rt-label">Image Settings</div>
	<div class="rt-field">
		<?php
		Fns::print_html( Fns::rtFieldGenerator( Options::scImageMetaFields() ), true );
		?>
	</div>
</div>
<div class="rt-field-wrapper rt-field-group">
	<div class="rt-label">Excerpt Settings</div>
	<div class="rt-field">
		<?php
		Fns::print_html( Fns::rtFieldGenerator( Options::scExcerptMetaFields() ), true );
		?>
	</div>
</div>
<div class="rt-field-wrapper rt-field-group">
	<div class="rt-label">Detail Page</div>
	<div class="rt-field">
		<?php
		Fns::print_html( Fns::rtFieldGenerator( Options::scDetailsMetaFields() ), true );
		?>
	</div>
</div>

<?php
do_action( 'fmp_sc_meta_after_details' );
?>
