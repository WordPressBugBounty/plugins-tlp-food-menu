<?php
// phpcs:disable WordPress.NamingConventions.PrefixAllGlobals -- Established plugin: public namespace, hook names, functions and theme-overridable template variables must stay unchanged for backward compatibility.
/**
 * Food Menu CSV Import / Export.
 *
 * @package RT_FoodMenu
 */

namespace RT\FoodMenu\Controllers\Admin;

use RT\FoodMenu\Helpers\Fns;

// Do not allow directly accessing this file.
if ( ! defined( 'ABSPATH' ) ) {
	exit( 'This script cannot be accessed directly.' );
}

/**
 * CSV import / export for `food-menu` posts, with a column-mapping step so
 * files exported from other plugins can be imported as they are.
 *
 * Fields are a registry (`tlp_fm_import_export_fields` filter) — Pro adds its
 * own fields (sale price, tags, gallery, ingredients…) through that filter.
 */
class ImportExport {

	use \RT\FoodMenu\Traits\SingletonTrait;

	/**
	 * Admin page slug.
	 */
	const PAGE = 'fm_import_export';

	/**
	 * Nonce action.
	 */
	const NONCE = 'tlp_fm_import_export';

	/**
	 * Attachment meta storing the URL an image was imported from.
	 */
	const SOURCE_URL_META = '_tlp_fm_import_source_url';

	/**
	 * Fields cache.
	 *
	 * @var array|null
	 */
	private $fields = null;

	/**
	 * Class Init.
	 *
	 * @return void
	 */
	protected function init() {
		add_action( 'admin_menu', [ $this, 'register_page' ], 20 );
		add_action( 'admin_menu', [ $this, 'move_below_settings' ], 1000 );
		add_action( 'admin_post_tlp_fm_export', [ $this, 'export' ] );
		add_action( 'wp_ajax_tlp_fm_import_upload', [ $this, 'ajax_upload' ] );
		add_action( 'wp_ajax_tlp_fm_import_preview', [ $this, 'ajax_preview' ] );
		add_action( 'wp_ajax_tlp_fm_import_run', [ $this, 'ajax_run' ] );
	}

	/**
	 * Capability required for import / export.
	 *
	 * @return string
	 */
	private function capability() {
		return apply_filters( 'tlp_fm_import_export_capability', 'manage_options' );
	}

	/**
	 * Register submenu page.
	 *
	 * @return void
	 */
	public function register_page() {
		$hook = add_submenu_page(
			'edit.php?post_type=' . TLPFoodMenu()->post_type,
			esc_html__( 'Import / Export', 'tlp-food-menu' ),
			esc_html__( 'Import / Export', 'tlp-food-menu' ),
			$this->capability(),
			self::PAGE,
			[ $this, 'render_page' ],
			14
		);

		add_action( 'load-' . $hook, [ $this, 'enqueue' ] );
	}

	/**
	 * Place the menu item right after "Settings" (submenu positions alone
	 * don't hold, post-type entries are added in between later).
	 *
	 * @return void
	 */
	public function move_below_settings() {
		global $submenu;

		$parent = 'edit.php?post_type=' . TLPFoodMenu()->post_type;

		if ( empty( $submenu[ $parent ] ) ) {
			return;
		}

		$item  = null;
		$items = [];

		foreach ( $submenu[ $parent ] as $entry ) {
			if ( self::PAGE === $entry[2] ) {
				$item = $entry;
			} else {
				$items[] = $entry;
			}
		}

		if ( ! $item ) {
			return;
		}

		$reordered = [];
		$placed    = false;

		foreach ( $items as $entry ) {
			$reordered[] = $entry;

			if ( 'food_menu_settings_updated' === $entry[2] ) {
				$reordered[] = $item;
				$placed      = true;
			}
		}

		if ( ! $placed ) {
			$reordered[] = $item;
		}

		$submenu[ $parent ] = $reordered; // phpcs:ignore WordPress.WP.GlobalVariablesOverride.Prohibited
	}

	/**
	 * Enqueue page assets.
	 *
	 * @return void
	 */
	public function enqueue() {
		add_action(
			'admin_enqueue_scripts',
			function () {
				wp_enqueue_script( 'fm-import-export' );
				wp_localize_script(
					'fm-import-export',
					'fmImportExport',
					[
						'ajaxurl' => admin_url( 'admin-ajax.php' ),
						'nonce'   => wp_create_nonce( self::NONCE ),
						'fields'  => $this->get_field_choices(),
						'i18n'    => [
							'skip'        => esc_html__( '— Do not import —', 'tlp-food-menu' ),
							'uploading'   => esc_html__( 'Uploading…', 'tlp-food-menu' ),
							'importing'   => esc_html__( 'Importing…', 'tlp-food-menu' ),
							'done'        => esc_html__( 'Import complete.', 'tlp-food-menu' ),
							'chooseFile'  => esc_html__( 'Please choose a CSV file.', 'tlp-food-menu' ),
							'noTitle'     => esc_html__( 'Map a column to "Title" (or "ID" / "Slug" when updating) before importing.', 'tlp-food-menu' ),
							'failed'      => esc_html__( 'Request failed. Please try again.', 'tlp-food-menu' ),
							'created'     => esc_html__( 'Created', 'tlp-food-menu' ),
							'updated'     => esc_html__( 'Updated', 'tlp-food-menu' ),
							'skipped'     => esc_html__( 'Skipped', 'tlp-food-menu' ),
							'failedRows'  => esc_html__( 'Failed', 'tlp-food-menu' ),
							'viewItems'   => esc_html__( 'View food items', 'tlp-food-menu' ),
							'importAgain' => esc_html__( 'Import another file', 'tlp-food-menu' ),
							/* translators: %d: number of rows. */
							'rows'        => esc_html__( '%d rows found', 'tlp-food-menu' ),
						],
						'listUrl' => admin_url( 'edit.php?post_type=' . TLPFoodMenu()->post_type ),
					]
				);
			}
		);
	}

	/**
	 * Importable / exportable fields.
	 *
	 * Each field: label, type (post|meta|taxonomy|image|images|custom), key,
	 * optional format (text|html|price|int|status|date|options), options (allowed values),
	 * aliases (header names auto-mapped to it), export_cb / import_cb for `custom`.
	 *
	 * @return array
	 */
	public function get_fields() {
		if ( null !== $this->fields ) {
			return $this->fields;
		}

		$fields = [
			'id'          => [
				'label'   => esc_html__( 'ID', 'tlp-food-menu' ),
				'type'    => 'post',
				'key'     => 'ID',
				'format'  => 'int',
				'aliases' => [ 'id', 'post id', 'item id' ],
			],
			'title'       => [
				'label'   => esc_html__( 'Title', 'tlp-food-menu' ),
				'type'    => 'post',
				'key'     => 'post_title',
				'aliases' => [ 'title', 'name', 'post title', 'item name', 'food name', 'product name', 'item', 'dish' ],
			],
			'slug'        => [
				'label'   => esc_html__( 'Slug', 'tlp-food-menu' ),
				'type'    => 'post',
				'key'     => 'post_name',
				'aliases' => [ 'slug', 'post name', 'permalink' ],
			],
			'content'     => [
				'label'   => esc_html__( 'Content', 'tlp-food-menu' ),
				'type'    => 'post',
				'key'     => 'post_content',
				'format'  => 'html',
				'aliases' => [ 'content', 'description', 'post content', 'body', 'details', 'long description' ],
			],
			'excerpt'     => [
				'label'   => esc_html__( 'Excerpt', 'tlp-food-menu' ),
				'type'    => 'post',
				'key'     => 'post_excerpt',
				'format'  => 'html',
				'aliases' => [ 'excerpt', 'short description', 'summary', 'post excerpt' ],
			],
			'status'      => [
				'label'   => esc_html__( 'Status', 'tlp-food-menu' ),
				'type'    => 'post',
				'key'     => 'post_status',
				'format'  => 'status',
				'aliases' => [ 'status', 'post status', 'published' ],
			],
			'date'        => [
				'label'   => esc_html__( 'Date', 'tlp-food-menu' ),
				'type'    => 'post',
				'key'     => 'post_date',
				'format'  => 'date',
				'aliases' => [ 'date', 'post date', 'publish date', 'published date', 'date published', 'created', 'date created', 'created at' ],
			],
			'menu_order'  => [
				'label'   => esc_html__( 'Menu Order', 'tlp-food-menu' ),
				'type'    => 'post',
				'key'     => 'menu_order',
				'format'  => 'int',
				'aliases' => [ 'menu order', 'order', 'position', 'sort order' ],
			],
			'price'       => [
				'label'   => esc_html__( 'Regular Price', 'tlp-food-menu' ),
				'type'    => 'meta',
				'key'     => '_regular_price',
				'format'  => 'price',
				'aliases' => [ 'price', 'regular price', 'cost', 'amount' ],
			],
			'price_label' => [
				'label'   => esc_html__( 'Price Label', 'tlp-food-menu' ),
				'type'    => 'meta',
				'key'     => Fns::PRICE_LABEL_META,
				'aliases' => [ 'price label', 'price text', 'price note', 'prices', 'sizes' ],
			],
			'categories'  => [
				'label'   => esc_html__( 'Categories', 'tlp-food-menu' ),
				'type'    => 'taxonomy',
				'key'     => TLPFoodMenu()->taxonomies['category'],
				'aliases' => [ 'categories', 'category', 'menu category', 'food category', 'section', 'group' ],
			],
			'image'       => [
				'label'   => esc_html__( 'Featured Image', 'tlp-food-menu' ),
				'type'    => 'image',
				'aliases' => [ 'image', 'images', 'featured image', 'image url', 'photo', 'thumbnail', 'picture' ],
			],
		];

		$this->fields = apply_filters( 'tlp_fm_import_export_fields', $fields );

		return $this->fields;
	}

	/**
	 * Field list for the mapping dropdowns.
	 *
	 * @return array
	 */
	private function get_field_choices() {
		$choices = [];

		foreach ( $this->get_fields() as $key => $field ) {
			$aliases = array_map( [ $this, 'normalize_header' ], array_merge( [ $key, $field['label'] ], $field['aliases'] ?? [] ) );

			$choices[] = [
				'key'     => $key,
				'label'   => wp_strip_all_tags( $field['label'] ),
				'aliases' => array_values( array_unique( $aliases ) ),
			];
		}

		return $choices;
	}

	/**
	 * Normalize a header for matching ("Regular Price (€)" → "regularprice").
	 *
	 * @param string $header Header.
	 * @return string
	 */
	private function normalize_header( $header ) {
		$header = strtolower( remove_accents( (string) $header ) );
		$header = preg_replace( '/\(.*?\)/', '', $header );

		return preg_replace( '/[^a-z0-9]/', '', $header );
	}

	/**
	 * Render admin page.
	 *
	 * @return void
	 */
	public function render_page() {
		$is_online = 'online_ordering' === Fns::get_setting( 'fm_food_menu_type' );
		?>
		<div class="wrap fm-import-export">
			<h1><?php esc_html_e( 'Food Menu Import / Export', 'tlp-food-menu' ); ?></h1>

			<?php if ( $is_online && TLPFoodMenu()->isWcActive() ) : ?>
				<div class="notice notice-info inline">
					<p>
						<?php
						printf(
							/* translators: %s: WooCommerce product importer link. */
							esc_html__( 'Your menu uses Online Ordering, which shows WooCommerce products. To import products, use the %s. This tool imports Food Menu posts.', 'tlp-food-menu' ),
							'<a href="' . esc_url( admin_url( 'edit.php?post_type=product&page=product_importer' ) ) . '">' . esc_html__( 'WooCommerce product importer', 'tlp-food-menu' ) . '</a>'
						);
						?>
					</p>
				</div>
			<?php endif; ?>

			<div class="fm-ie-card">
				<h2><?php esc_html_e( 'Export', 'tlp-food-menu' ); ?></h2>
				<p><?php esc_html_e( 'Download all food items as a CSV file. Use it as a backup, to move items to another site, or as a template for importing.', 'tlp-food-menu' ); ?></p>
				<form method="post" action="<?php echo esc_url( admin_url( 'admin-post.php' ) ); ?>">
					<input type="hidden" name="action" value="tlp_fm_export">
					<?php wp_nonce_field( self::NONCE ); ?>
					<?php submit_button( esc_html__( 'Export CSV', 'tlp-food-menu' ), 'secondary', 'submit', false ); ?>
				</form>
			</div>

			<div class="fm-ie-card" id="fm-import">
				<h2><?php esc_html_e( 'Import', 'tlp-food-menu' ); ?></h2>

				<div class="fm-ie-step" data-step="upload">
					<p><?php esc_html_e( 'Upload a CSV file (e.g. exported from another plugin or a spreadsheet). On the next step you choose which column goes to which food field.', 'tlp-food-menu' ); ?></p>
					<p>
						<input type="file" id="fm-ie-file" accept=".csv,.txt,text/csv">
					</p>
					<p>
						<label>
							<input type="checkbox" id="fm-ie-update">
							<?php esc_html_e( 'Update existing items that match by ID or slug (otherwise matching rows are skipped)', 'tlp-food-menu' ); ?>
						</label>
					</p>
					<p>
						<label>
							<input type="checkbox" id="fm-ie-create-terms" checked>
							<?php esc_html_e( 'Create categories (and other terms) that do not exist yet', 'tlp-food-menu' ); ?>
						</label>
					</p>
					<p>
						<label>
							<input type="checkbox" id="fm-ie-images" checked>
							<?php esc_html_e( 'Download images from URLs into the Media Library', 'tlp-food-menu' ); ?>
						</label>
					</p>
					<p>
						<label for="fm-ie-default-status"><?php esc_html_e( 'Status for new items when the file has none:', 'tlp-food-menu' ); ?></label>
						<select id="fm-ie-default-status">
							<option value="publish"><?php esc_html_e( 'Published', 'tlp-food-menu' ); ?></option>
							<option value="draft"><?php esc_html_e( 'Draft', 'tlp-food-menu' ); ?></option>
						</select>
					</p>
					<p><button type="button" class="button button-primary" id="fm-ie-upload"><?php esc_html_e( 'Continue', 'tlp-food-menu' ); ?></button></p>
				</div>

				<div class="fm-ie-step" data-step="map" hidden>
					<p>
						<?php esc_html_e( 'Choose the food field each column should be imported into. Columns set to "Do not import" are ignored.', 'tlp-food-menu' ); ?>
					</p>
					<p>
						<label for="fm-ie-delimiter"><?php esc_html_e( 'Column separator:', 'tlp-food-menu' ); ?></label>
						<select id="fm-ie-delimiter">
							<option value=",">, <?php esc_html_e( '(comma)', 'tlp-food-menu' ); ?></option>
							<option value=";">; <?php esc_html_e( '(semicolon)', 'tlp-food-menu' ); ?></option>
							<option value="tab"><?php esc_html_e( 'Tab', 'tlp-food-menu' ); ?></option>
							<option value="|">| <?php esc_html_e( '(pipe)', 'tlp-food-menu' ); ?></option>
						</select>
						<span class="description" id="fm-ie-rowcount"></span>
					</p>
					<table class="widefat striped fm-ie-map">
						<thead>
						<tr>
							<th><?php esc_html_e( 'Column in file', 'tlp-food-menu' ); ?></th>
							<th><?php esc_html_e( 'Sample value', 'tlp-food-menu' ); ?></th>
							<th><?php esc_html_e( 'Import as', 'tlp-food-menu' ); ?></th>
						</tr>
						</thead>
						<tbody></tbody>
					</table>
					<p class="description">
						<?php esc_html_e( 'Multiple categories or images: separate with commas. Sub-categories: "Parent > Child". Images: full URLs or Media Library IDs. Price ranges or size prices ("12.00-14.50", "Small 5.95 | Large 6.50") are kept as the Price Label.', 'tlp-food-menu' ); ?>
					</p>
					<p>
						<button type="button" class="button" id="fm-ie-back"><?php esc_html_e( 'Back', 'tlp-food-menu' ); ?></button>
						<button type="button" class="button button-primary" id="fm-ie-run"><?php esc_html_e( 'Run Import', 'tlp-food-menu' ); ?></button>
					</p>
				</div>

				<div class="fm-ie-step" data-step="run" hidden>
					<p class="fm-ie-status"></p>
					<progress class="fm-ie-progress" max="100" value="0"></progress>
					<div class="fm-ie-summary"></div>
					<ul class="fm-ie-errors"></ul>
				</div>

				<div class="fm-ie-message notice notice-error inline" hidden><p></p></div>
			</div>
		</div>
		<style>
			.fm-import-export .fm-ie-card{background:#fff;border:1px solid #dcdcde;border-radius:4px;padding:8px 20px 16px;margin-top:16px;max-width:960px}
			.fm-import-export .fm-ie-map td,.fm-import-export .fm-ie-map th{vertical-align:middle}
			.fm-import-export .fm-ie-map td:nth-child(2){color:#646970;max-width:320px;overflow:hidden;text-overflow:ellipsis;white-space:nowrap}
			.fm-import-export .fm-ie-progress{width:100%;height:18px}
			.fm-import-export .fm-ie-errors{max-height:240px;overflow:auto;color:#b32d2e}
			.fm-import-export .fm-ie-summary span{display:inline-block;margin-right:16px;font-weight:600}
		</style>
		<?php
	}

	/**
	 * Verify an AJAX request.
	 *
	 * @return void
	 */
	private function verify_ajax() {
		if ( ! check_ajax_referer( self::NONCE, 'nonce', false ) || ! current_user_can( $this->capability() ) ) {
			wp_send_json_error( [ 'message' => esc_html__( 'You are not allowed to do this.', 'tlp-food-menu' ) ], 403 );
		}
	}

	/* --------------------------------------------------------------------
	 * Export
	 * ------------------------------------------------------------------ */

	/**
	 * Stream all food items as CSV.
	 *
	 * @return void
	 */
	public function export() {
		if ( ! current_user_can( $this->capability() ) || ! check_admin_referer( self::NONCE ) ) {
			wp_die( esc_html__( 'You are not allowed to do this.', 'tlp-food-menu' ) );
		}

		$fields = array_filter(
			$this->get_fields(),
			function ( $field ) {
				return ! isset( $field['export'] ) || $field['export'];
			}
		);

		nocache_headers();
		header( 'Content-Type: text/csv; charset=utf-8' );
		header( 'Content-Disposition: attachment; filename=food-menu-' . gmdate( 'Y-m-d' ) . '.csv' );

		$out = fopen( 'php://output', 'w' ); // phpcs:ignore WordPress.WP.AlternativeFunctions.file_system_operations_fopen

		// UTF-8 BOM so Excel opens accented text correctly.
		fwrite( $out, "\xEF\xBB\xBF" ); // phpcs:ignore WordPress.WP.AlternativeFunctions.file_system_operations_fwrite
		fputcsv( $out, array_map( 'wp_strip_all_tags', wp_list_pluck( $fields, 'label' ) ), ',', '"', '\\' );

		$page = 1;

		do {
			$query = new \WP_Query(
				[
					'post_type'      => TLPFoodMenu()->post_type,
					'post_status'    => [ 'publish', 'draft', 'pending', 'private', 'future' ],
					'posts_per_page' => 200,
					'paged'          => $page,
					'orderby'        => [
						'menu_order' => 'ASC',
						'ID'         => 'ASC',
					],
					'no_found_rows'  => true,
				]
			);

			if ( empty( $query->posts ) ) {
				break;
			}

			// Featured images: one query for all attachment posts of the page.
			$thumb_ids = [];
			foreach ( $query->posts as $post ) {
				$thumb_ids[] = (int) get_post_thumbnail_id( $post );
			}
			_prime_post_caches( array_filter( $thumb_ids ), false, true );

			foreach ( $query->posts as $post ) {
				$row = [];

				foreach ( $fields as $field ) {
					$row[] = $this->escape_csv_cell( $this->export_value( $post, $field ) );
				}

				fputcsv( $out, $row, ',', '"', '\\' );
			}

			$count = count( $query->posts );
			++$page;

			if ( function_exists( 'wp_cache_flush_runtime' ) ) {
				wp_cache_flush_runtime();
			}
		} while ( 200 === $count );

		fclose( $out ); // phpcs:ignore WordPress.WP.AlternativeFunctions.file_system_operations_fclose
		exit;
	}

	/**
	 * Export value of a field.
	 *
	 * @param \WP_Post $post Post.
	 * @param array    $field Field.
	 * @return string
	 */
	private function export_value( $post, $field ) {
		switch ( $field['type'] ) {
			case 'post':
				return (string) $post->{$field['key']};

			case 'meta':
				$value = get_post_meta( $post->ID, $field['key'], true );

				return is_scalar( $value ) ? (string) $value : '';

			case 'taxonomy':
				return $this->export_terms( $post->ID, $field['key'] );

			case 'image':
				$id = get_post_thumbnail_id( $post );

				return $id ? (string) wp_get_attachment_url( $id ) : '';

			case 'images':
				$ids = array_filter( array_map( 'absint', (array) get_post_meta( $post->ID, $field['key'], true ) ) );

				return implode( ', ', array_filter( array_map( 'wp_get_attachment_url', $ids ) ) );

			case 'custom':
				return isset( $field['export_cb'] ) && is_callable( $field['export_cb'] ) ? (string) call_user_func( $field['export_cb'], $post, $field ) : '';
		}

		return '';
	}

	/**
	 * Terms as "Parent > Child, Other".
	 *
	 * @param int    $post_id Post ID.
	 * @param string $taxonomy Taxonomy.
	 * @return string
	 */
	private function export_terms( $post_id, $taxonomy ) {
		$terms = get_the_terms( $post_id, $taxonomy );

		if ( empty( $terms ) || is_wp_error( $terms ) ) {
			return '';
		}

		$names = [];

		foreach ( $terms as $term ) {
			$path   = [ $term->name ];
			$parent = $term->parent;

			while ( $parent ) {
				$parent_term = get_term( $parent, $taxonomy );

				if ( ! $parent_term || is_wp_error( $parent_term ) ) {
					break;
				}

				array_unshift( $path, $parent_term->name );
				$parent = $parent_term->parent;
			}

			$names[] = str_replace( ',', '\\,', implode( ' > ', $path ) );
		}

		return implode( ', ', $names );
	}

	/**
	 * Guard against spreadsheet formula injection.
	 *
	 * @param string $value Value.
	 * @return string
	 */
	private function escape_csv_cell( $value ) {
		if ( $this->is_formula_cell( $value ) ) {
			$value = "'" . $value;
		}

		return $value;
	}

	/**
	 * Whether a spreadsheet would run this cell as a formula: it starts with = + - @, a tab
	 * or a carriage return — also after leading spaces, which some spreadsheets skip.
	 *
	 * @param string $value Value.
	 * @return bool
	 */
	private function is_formula_cell( $value ) {
		$value = (string) $value;

		if ( '' === $value || is_numeric( $value ) ) {
			return false;
		}

		if ( in_array( $value[0], [ "\t", "\r" ], true ) ) {
			return true;
		}

		$trimmed = ltrim( $value, ' ' );

		return '' !== $trimmed && in_array( $trimmed[0], [ '=', '+', '-', '@' ], true );
	}

	/* --------------------------------------------------------------------
	 * Import — file handling
	 * ------------------------------------------------------------------ */

	/**
	 * Private folder for uploaded import files.
	 *
	 * @return string
	 */
	private function import_dir() {
		$dir = trailingslashit( wp_upload_dir()['basedir'] ) . 'tlp-food-menu-import';

		if ( ! is_dir( $dir ) ) {
			wp_mkdir_p( $dir );
			// phpcs:disable WordPress.WP.AlternativeFunctions.file_system_operations_file_put_contents
			file_put_contents( $dir . '/index.php', '<?php // Silence is golden.' );
			file_put_contents( $dir . '/.htaccess', "Require all denied\nDeny from all\n" );
			// phpcs:enable
		}

		return $dir;
	}

	/**
	 * Resolve an uploaded file from its token.
	 *
	 * @param string $token Token.
	 * @return string|false
	 */
	private function file_from_token( $token ) {
		$token = preg_replace( '/[^a-zA-Z0-9]/', '', (string) $token );

		if ( strlen( $token ) !== 24 ) {
			return false;
		}

		$file = $this->import_dir() . '/' . $token . '.csv';

		return is_readable( $file ) ? $file : false;
	}

	/**
	 * AJAX: receive the CSV file.
	 *
	 * @return void
	 */
	public function ajax_upload() {
		$this->verify_ajax();

		// phpcs:ignore WordPress.Security.ValidatedSanitizedInput.InputNotSanitized -- validated below.
		$file = $_FILES['file'] ?? null;

		if ( empty( $file['tmp_name'] ) || ! empty( $file['error'] ) || ! is_uploaded_file( $file['tmp_name'] ) ) {
			wp_send_json_error( [ 'message' => esc_html__( 'The file could not be uploaded.', 'tlp-food-menu' ) ] );
		}

		$ext = strtolower( pathinfo( sanitize_file_name( $file['name'] ), PATHINFO_EXTENSION ) );

		if ( ! in_array( $ext, [ 'csv', 'txt' ], true ) ) {
			wp_send_json_error( [ 'message' => esc_html__( 'Please upload a .csv file.', 'tlp-food-menu' ) ] );
		}

		$dir = $this->import_dir();

		// Clean up files left behind by abandoned imports.
		foreach ( (array) glob( $dir . '/*.csv' ) as $old ) {
			if ( $old && filemtime( $old ) < time() - DAY_IN_SECONDS ) {
				wp_delete_file( $old );
			}
		}

		$token = wp_generate_password( 24, false );

		if ( ! move_uploaded_file( $file['tmp_name'], $dir . '/' . $token . '.csv' ) ) {
			wp_send_json_error( [ 'message' => esc_html__( 'The file could not be saved on the server.', 'tlp-food-menu' ) ] );
		}

		wp_send_json_success(
			[
				'token'     => $token,
				'delimiter' => $this->detect_delimiter( $dir . '/' . $token . '.csv' ),
			]
		);
	}

	/**
	 * Guess the delimiter from the header line.
	 *
	 * @param string $file File path.
	 * @return string
	 */
	private function detect_delimiter( $file ) {
		$handle = fopen( $file, 'r' ); // phpcs:ignore WordPress.WP.AlternativeFunctions.file_system_operations_fopen
		$line   = $handle ? (string) fgets( $handle ) : '';

		if ( $handle ) {
			fclose( $handle ); // phpcs:ignore WordPress.WP.AlternativeFunctions.file_system_operations_fclose
		}

		$counts = [
			','   => substr_count( $line, ',' ),
			';'   => substr_count( $line, ';' ),
			'tab' => substr_count( $line, "\t" ),
			'|'   => substr_count( $line, '|' ),
		];

		arsort( $counts );

		return (string) key( $counts );
	}

	/**
	 * Delimiter from request.
	 *
	 * @return string
	 */
	private function request_delimiter() {
		// phpcs:ignore WordPress.Security.NonceVerification.Missing -- verified in verify_ajax().
		$delimiter = isset( $_POST['delimiter'] ) ? sanitize_text_field( wp_unslash( $_POST['delimiter'] ) ) : ',';

		if ( 'tab' === $delimiter ) {
			return "\t";
		}

		return in_array( $delimiter, [ ',', ';', '|' ], true ) ? $delimiter : ',';
	}

	/**
	 * Read one CSV row, converted to UTF-8.
	 *
	 * @param resource $handle File handle.
	 * @param string   $delimiter Delimiter.
	 * @return array|false
	 */
	private function read_row( $handle, $delimiter ) {
		$row = fgetcsv( $handle, 0, $delimiter, '"', '\\' );

		if ( false === $row ) {
			return false;
		}

		foreach ( $row as $i => $cell ) {
			$cell = (string) $cell;

			// Excel on Windows saves CSV as Windows-1252, not UTF-8.
			if ( function_exists( 'mb_check_encoding' ) && ! mb_check_encoding( $cell, 'UTF-8' ) ) {
				$cell = mb_convert_encoding( $cell, 'UTF-8', 'Windows-1252' );
			}

			$row[ $i ] = trim( $cell );
		}

		return $row;
	}

	/**
	 * Open the file and read the header row.
	 *
	 * @param string $file File.
	 * @param string $delimiter Delimiter.
	 * @return array [handle, headers]
	 */
	private function open( $file, $delimiter ) {
		$handle = fopen( $file, 'r' ); // phpcs:ignore WordPress.WP.AlternativeFunctions.file_system_operations_fopen

		if ( ! $handle ) {
			return [ false, [] ];
		}

		// Skip UTF-8 BOM.
		if ( "\xEF\xBB\xBF" !== fread( $handle, 3 ) ) { // phpcs:ignore WordPress.WP.AlternativeFunctions.file_system_operations_fread
			rewind( $handle );
		}

		$headers = $this->read_row( $handle, $delimiter );

		return [ $handle, $headers ? $headers : [] ];
	}

	/**
	 * AJAX: headers, sample row and row count for the mapping step.
	 *
	 * @return void
	 */
	public function ajax_preview() {
		$this->verify_ajax();

		// phpcs:ignore WordPress.Security.NonceVerification.Missing -- verified in verify_ajax().
		$file = $this->file_from_token( isset( $_POST['token'] ) ? sanitize_text_field( wp_unslash( $_POST['token'] ) ) : '' );

		if ( ! $file ) {
			wp_send_json_error( [ 'message' => esc_html__( 'The uploaded file has expired. Please upload it again.', 'tlp-food-menu' ) ] );
		}

		list( $handle, $headers ) = $this->open( $file, $this->request_delimiter() );

		if ( ! $handle || empty( array_filter( $headers, 'strlen' ) ) ) {
			wp_send_json_error( [ 'message' => esc_html__( 'The file is empty or not a valid CSV.', 'tlp-food-menu' ) ] );
		}

		$sample = [];
		$rows   = 0;

		while ( false !== ( $row = $this->read_row( $handle, $this->request_delimiter() ) ) ) { // phpcs:ignore Generic.CodeAnalysis.AssignmentInCondition.FoundInWhileCondition
			if ( ! array_filter( $row, 'strlen' ) ) {
				continue;
			}

			if ( ! $sample ) {
				$sample = $row;
			}

			++$rows;
		}

		fclose( $handle ); // phpcs:ignore WordPress.WP.AlternativeFunctions.file_system_operations_fclose

		wp_send_json_success(
			[
				'headers' => $headers,
				'sample'  => array_map(
					function ( $value ) {
						return wp_html_excerpt( wp_strip_all_tags( $value ), 80, '…' );
					},
					$sample
				),
				'rows'    => $rows,
			]
		);
	}

	/* --------------------------------------------------------------------
	 * Import — rows
	 * ------------------------------------------------------------------ */

	/**
	 * AJAX: import one batch of rows.
	 *
	 * @return void
	 */
	public function ajax_run() {
		$this->verify_ajax();

		// phpcs:disable WordPress.Security.NonceVerification.Missing -- verified in verify_ajax().
		$token    = isset( $_POST['token'] ) ? sanitize_text_field( wp_unslash( $_POST['token'] ) ) : '';
		$position = isset( $_POST['position'] ) ? absint( $_POST['position'] ) : 0;
		$mapping  = isset( $_POST['mapping'] ) ? array_map( 'sanitize_key', (array) wp_unslash( $_POST['mapping'] ) ) : [];
		$options  = [
			'update'         => ! empty( $_POST['update'] ),
			'create_terms'   => ! empty( $_POST['create_terms'] ),
			'images'         => ! empty( $_POST['images'] ),
			'default_status' => isset( $_POST['default_status'] ) && 'draft' === $_POST['default_status'] ? 'draft' : 'publish',
		];
		// phpcs:enable

		$file = $this->file_from_token( $token );

		if ( ! $file ) {
			wp_send_json_error( [ 'message' => esc_html__( 'The uploaded file has expired. Please upload it again.', 'tlp-food-menu' ) ] );
		}

		$fields  = $this->get_fields();
		$mapping = array_filter(
			$mapping,
			function ( $key ) use ( $fields ) {
				return isset( $fields[ $key ] );
			}
		);

		$delimiter                = $this->request_delimiter();
		list( $handle, $headers ) = $this->open( $file, $delimiter );

		if ( ! $handle ) {
			wp_send_json_error( [ 'message' => esc_html__( 'The file could not be read.', 'tlp-food-menu' ) ] );
		}

		// Resume after the previous batch; the first batch starts after the header.
		if ( $position > ftell( $handle ) ) {
			fseek( $handle, $position );
		}

		$batch_size = max( 1, (int) apply_filters( 'tlp_fm_import_batch_size', 10 ) );
		$result     = [
			'created' => 0,
			'updated' => 0,
			'skipped' => 0,
			'failed'  => 0,
			'errors'  => [],
		];
		$processed  = 0;

		wp_defer_term_counting( true );

		// Row numbers in messages match the spreadsheet (header is row 1).
		// phpcs:ignore WordPress.Security.NonceVerification.Missing -- verified in verify_ajax().
		$line = isset( $_POST['line'] ) ? absint( $_POST['line'] ) : 1;

		while ( $processed < $batch_size && false !== ( $row = $this->read_row( $handle, $delimiter ) ) ) { // phpcs:ignore Generic.CodeAnalysis.AssignmentInCondition.FoundInWhileCondition
			++$line;

			if ( ! array_filter( $row, 'strlen' ) ) {
				continue;
			}

			++$processed;

			$data = [];
			foreach ( $mapping as $index => $key ) {
				if ( isset( $row[ $index ] ) ) {
					$data[ $key ] = $this->unescape_csv_cell( $row[ $index ] );
				}
			}

			$status = $this->import_row( $data, $options );

			if ( is_wp_error( $status ) ) {
				$label = isset( $data['title'] ) && '' !== $data['title'] ? ' (' . $data['title'] . ')' : '';
				// A partly applied row was still saved — count it as such.
				// Plain text: the page inserts these with jQuery .text().
				++$result[ 'partial' === $status->get_error_code() ? $status->get_error_data() : 'failed' ];
				/* translators: 1: row number, 2: item title, 3: error message. */
				$result['errors'][] = sprintf( __( 'Row %1$d%2$s: %3$s', 'tlp-food-menu' ), $line, $label, $status->get_error_message() );
			} else {
				++$result[ $status ];
			}
		}

		wp_defer_term_counting( false );

		$result['position'] = ftell( $handle );
		$result['line']     = $line;
		$result['done']     = feof( $handle ) || false === $this->peek( $handle );

		fclose( $handle ); // phpcs:ignore WordPress.WP.AlternativeFunctions.file_system_operations_fclose

		if ( $result['done'] ) {
			wp_delete_file( $file );
		}

		wp_send_json_success( $result );
	}

	/**
	 * Whether any bytes are left to read.
	 *
	 * @param resource $handle Handle.
	 * @return string|false
	 */
	private function peek( $handle ) {
		$pos  = ftell( $handle );
		$char = fgetc( $handle );
		fseek( $handle, $pos );

		return $char;
	}

	/**
	 * Undo the formula-injection prefix added on export.
	 *
	 * @param string $value Value.
	 * @return string
	 */
	private function unescape_csv_cell( $value ) {
		if ( strlen( $value ) > 1 && "'" === $value[0] && $this->is_formula_cell( substr( $value, 1 ) ) ) {
			return substr( $value, 1 );
		}

		return $value;
	}

	/**
	 * Find an existing food item by ID or slug.
	 *
	 * @param array $data Row data.
	 * @return int
	 */
	private function find_existing( $data ) {
		if ( ! empty( $data['id'] ) ) {
			$post = get_post( absint( $data['id'] ) );

			// IDs from another site can point at an unrelated item here — when
			// the row also has a slug, the ID only counts if the slug agrees.
			$slug_matches = $post && ( empty( $data['slug'] ) || sanitize_title( $data['slug'] ) === $post->post_name );

			if ( $post && TLPFoodMenu()->post_type === $post->post_type && $slug_matches ) {
				return $post->ID;
			}
		}

		if ( ! empty( $data['slug'] ) ) {
			$post = get_page_by_path( sanitize_title( $data['slug'] ), OBJECT, TLPFoodMenu()->post_type );

			if ( $post ) {
				return $post->ID;
			}
		}

		return 0;
	}

	/**
	 * Import one row.
	 *
	 * Empty cells never overwrite existing values when updating.
	 *
	 * @param array $data Field key => raw value.
	 * @param array $options Options.
	 * @return string|\WP_Error created|updated|skipped
	 */
	private function import_row( $data, $options ) {
		$fields      = $this->get_fields();
		$existing_id = $this->find_existing( $data );

		if ( $existing_id && ! $options['update'] ) {
			return 'skipped';
		}

		if ( ! $existing_id && ( ! isset( $data['title'] ) || '' === $data['title'] ) ) {
			return new \WP_Error( 'no_title', __( 'Title is empty.', 'tlp-food-menu' ) );
		}

		// A price range or size prices ("12.00-14.50", "Small 5.95 | Large 6.50")
		// is kept as the Price Label; its first number becomes the Regular Price.
		if ( isset( $data['price'] ) && '' !== $data['price'] && null === $this->format_value( $data['price'], $fields['price'] ) ) {
			if ( empty( $data['price_label'] ) && isset( $fields['price_label'] ) ) {
				$data['price_label'] = $data['price'];
			}

			$data['price'] = preg_match( '/\d+(?:[.,]\d+)?/', $data['price'], $match ) ? $match[0] : '';
		}

		$postarr = [];

		foreach ( $data as $key => $value ) {
			if ( '' === $value || 'post' !== $fields[ $key ]['type'] || 'ID' === $fields[ $key ]['key'] ) {
				continue;
			}

			$value = $this->format_value( $value, $fields[ $key ] );

			if ( null !== $value ) {
				$postarr[ $fields[ $key ]['key'] ] = $value;
			}
		}

		if ( isset( $postarr['post_date'] ) ) {
			$postarr['post_date_gmt'] = get_gmt_from_date( $postarr['post_date'] );
			// Without it WordPress ignores a new date on drafts when updating.
			$postarr['edit_date'] = true;
		}

		if ( $existing_id ) {
			$postarr['ID'] = $existing_id;
			$post_id       = $postarr ? wp_update_post( wp_slash( $postarr ), true ) : $existing_id;
		} else {
			$postarr['post_type']      = TLPFoodMenu()->post_type;
			$postarr['post_status']    = $postarr['post_status'] ?? $options['default_status'];
			$postarr['comment_status'] = get_default_comment_status( TLPFoodMenu()->post_type );
			$post_id                   = wp_insert_post( wp_slash( $postarr ), true );
		}

		if ( is_wp_error( $post_id ) ) {
			return $post_id;
		}

		$errors = [];

		foreach ( $data as $key => $value ) {
			$field = $fields[ $key ];

			if ( 'post' === $field['type'] ) {
				continue;
			}

			// Empty cell: leave untouched.
			if ( '' === $value ) {
				continue;
			}

			$error = $this->import_field( $post_id, $value, $field, $options );

			if ( is_wp_error( $error ) ) {
				$errors[] = $error->get_error_message();
			}
		}

		// Keep `_price` (used for price sorting) in sync, as the edit screen does.
		if ( isset( $data['price'] ) || isset( $data['sale_price'] ) ) {
			$sale = get_post_meta( $post_id, '_sale_price', true );
			update_post_meta( $post_id, '_price', '' !== $sale ? $sale : get_post_meta( $post_id, '_regular_price', true ) );
		}

		do_action( 'tlp_fm_after_import_row', $post_id, $data, $options, (bool) $existing_id );

		if ( $errors ) {
			// The item itself was saved — report what could not be applied.
			return new \WP_Error( 'partial', implode( ' ', $errors ), $existing_id ? 'updated' : 'created' );
		}

		return $existing_id ? 'updated' : 'created';
	}

	/**
	 * Format a scalar value according to the field format.
	 *
	 * @param string $value Value.
	 * @param array  $field Field.
	 * @return mixed|null Null when invalid.
	 */
	private function format_value( $value, $field ) {
		switch ( $field['format'] ?? 'text' ) {
			case 'html':
				return $value;

			case 'int':
				return is_numeric( $value ) ? (int) $value : null;

			case 'price':
				// Strip currency symbols / spaces, accept "12,50".
				$value = preg_replace( '/[^0-9.,\-]/', '', $value );

				if ( false !== strpos( $value, ',' ) && false === strpos( $value, '.' ) ) {
					$value = str_replace( ',', '.', $value );
				}

				$value = str_replace( ',', '', $value );

				// Two decimals, as the edit screen saves it ("13" → "13.00").
				return is_numeric( $value ) ? sprintf( '%.2f', (float) $value ) : null;

			case 'date':
				// Any format strtotime() reads, in the site's timezone. Bare
				// numbers are refused: "2025" would parse as 20:25 today.
				if ( ctype_digit( $value ) ) {
					return null;
				}

				try {
					$date = new \DateTimeImmutable( $value, wp_timezone() );
				} catch ( \Exception $e ) {
					return null;
				}

				return $date->format( 'Y-m-d H:i:s' );

			case 'status':
				$value = strtolower( $value );
				$map   = [
					'published' => 'publish',
					'1'         => 'publish',
					'yes'       => 'publish',
					'0'         => 'draft',
					'no'        => 'draft',
				];
				$value = $map[ $value ] ?? $value;

				return in_array( $value, [ 'publish', 'draft', 'pending', 'private' ], true ) ? $value : null;

			case 'options':
				// "In stock" → "instock".
				$value = preg_replace( '/[^a-z0-9_\-]/', '', strtolower( $value ) );

				return isset( $field['options'][ $value ] ) ? $value : null;
		}

		return sanitize_text_field( $value );
	}

	/**
	 * Apply a non-post field.
	 *
	 * @param int    $post_id Post ID.
	 * @param string $value Raw value.
	 * @param array  $field Field.
	 * @param array  $options Options.
	 * @return true|\WP_Error
	 */
	private function import_field( $post_id, $value, $field, $options ) {
		switch ( $field['type'] ) {
			case 'meta':
				$formatted = $this->format_value( $value, $field );

				if ( null === $formatted ) {
					/* translators: 1: field label, 2: value. */
					return new \WP_Error( 'invalid', sprintf( __( '%1$s "%2$s" is not valid.', 'tlp-food-menu' ), $field['label'], $value ) );
				}

				update_post_meta( $post_id, $field['key'], $formatted );

				return true;

			case 'taxonomy':
				return $this->import_terms( $post_id, $value, $field['key'], $options['create_terms'] );

			case 'image':
				$ids = $this->import_images( $value, $post_id, $options['images'] );

				if ( is_wp_error( $ids ) ) {
					return $ids;
				}

				set_post_thumbnail( $post_id, $ids[0] );

				return true;

			case 'images':
				$ids = $this->import_images( $value, $post_id, $options['images'] );

				if ( is_wp_error( $ids ) ) {
					return $ids;
				}

				update_post_meta( $post_id, $field['key'], $ids );

				return true;

			case 'custom':
				if ( isset( $field['import_cb'] ) && is_callable( $field['import_cb'] ) ) {
					$return = call_user_func( $field['import_cb'], $post_id, $value, $field, $options );

					return is_wp_error( $return ) ? $return : true;
				}
		}

		return true;
	}

	/**
	 * Split a comma list, honouring "\," escapes.
	 *
	 * @param string $value Value.
	 * @return array
	 */
	public function split_list( $value ) {
		$parts = preg_split( '/(?<!\\\\),/', (string) $value );
		$parts = array_map(
			function ( $part ) {
				return trim( str_replace( '\\,', ',', $part ) );
			},
			$parts
		);

		return array_values( array_filter( $parts, 'strlen' ) );
	}

	/**
	 * Find (or create) a term by name, optionally under a parent.
	 *
	 * @param string $name Name.
	 * @param string $taxonomy Taxonomy.
	 * @param bool   $create Create when missing.
	 * @param int    $parent Parent term ID.
	 * @return int|\WP_Error Term ID.
	 */
	public function get_term_id( $name, $taxonomy, $create = true, $parent = 0 ) {
		$args = [
			'taxonomy'   => $taxonomy,
			'name'       => $name,
			'hide_empty' => false,
			'number'     => 1,
			'fields'     => 'ids',
		];

		if ( is_taxonomy_hierarchical( $taxonomy ) ) {
			$args['parent'] = $parent;
		}

		$found = get_terms( $args );

		if ( ! empty( $found ) && ! is_wp_error( $found ) ) {
			return (int) $found[0];
		}

		// Allow a slug in place of the name.
		$term = get_term_by( 'slug', sanitize_title( $name ), $taxonomy );

		if ( $term && ( ! is_taxonomy_hierarchical( $taxonomy ) || (int) $term->parent === (int) $parent ) ) {
			return (int) $term->term_id;
		}

		if ( ! $create ) {
			/* translators: %s: term name. */
			return new \WP_Error( 'missing_term', sprintf( __( '"%s" does not exist.', 'tlp-food-menu' ), $name ) );
		}

		$new = wp_insert_term( $name, $taxonomy, [ 'parent' => $parent ] );

		if ( is_wp_error( $new ) ) {
			// Created by a parallel row in the meantime.
			if ( 'term_exists' === $new->get_error_code() ) {
				return (int) $new->get_error_data();
			}

			return $new;
		}

		return (int) $new['term_id'];
	}

	/**
	 * Assign terms from "Parent > Child, Other".
	 *
	 * @param int    $post_id Post ID.
	 * @param string $value Value.
	 * @param string $taxonomy Taxonomy.
	 * @param bool   $create Create missing terms.
	 * @return true|\WP_Error
	 */
	private function import_terms( $post_id, $value, $taxonomy, $create ) {
		if ( ! taxonomy_exists( $taxonomy ) ) {
			return true;
		}

		$term_ids = [];
		$errors   = [];

		foreach ( $this->split_list( $value ) as $path ) {
			$parent = 0;
			$names  = is_taxonomy_hierarchical( $taxonomy ) ? array_filter( array_map( 'trim', explode( '>', $path ) ), 'strlen' ) : [ $path ];

			foreach ( $names as $name ) {
				$parent = $this->get_term_id( $name, $taxonomy, $create, $parent );

				if ( is_wp_error( $parent ) ) {
					$errors[] = $parent->get_error_message();
					$parent   = 0;
					break;
				}
			}

			if ( $parent ) {
				$term_ids[] = $parent;
			}
		}

		if ( $term_ids ) {
			wp_set_object_terms( $post_id, $term_ids, $taxonomy );
		}

		return $errors ? new \WP_Error( 'terms', implode( ' ', $errors ) ) : true;
	}

	/**
	 * Resolve images (URLs or attachment IDs) to attachment IDs.
	 *
	 * Images imported before are reused instead of downloaded again.
	 *
	 * @param string $value Comma separated URLs / IDs.
	 * @param int    $post_id Post the images belong to.
	 * @param bool   $download Download remote URLs.
	 * @return int[]|\WP_Error
	 */
	public function import_images( $value, $post_id, $download = true ) {
		$ids    = [];
		$errors = [];

		foreach ( $this->split_list( $value ) as $item ) {
			$id = $this->import_image( $item, $post_id, $download );

			if ( is_wp_error( $id ) ) {
				$errors[] = $id->get_error_message();
			} else {
				$ids[] = $id;
			}
		}

		if ( ! $ids ) {
			return new \WP_Error( 'image', $errors ? implode( ' ', $errors ) : __( 'No valid image.', 'tlp-food-menu' ) );
		}

		return $ids;
	}

	/**
	 * Resolve one image.
	 *
	 * @param string $item URL or attachment ID.
	 * @param int    $post_id Post ID.
	 * @param bool   $download Download remote URLs.
	 * @return int|\WP_Error
	 */
	private function import_image( $item, $post_id, $download ) {
		if ( ctype_digit( $item ) ) {
			return wp_attachment_is_image( (int) $item ) ? (int) $item : new \WP_Error( 'image', sprintf( /* translators: %s: ID. */ __( 'Image ID %s not found.', 'tlp-food-menu' ), $item ) );
		}

		$url = esc_url_raw( $item );

		if ( ! $url || ! wp_parse_url( $url, PHP_URL_HOST ) ) {
			/* translators: %s: value. */
			return new \WP_Error( 'image', sprintf( __( 'Image "%s" is not a valid URL.', 'tlp-food-menu' ), $item ) );
		}

		// A well-formed URL WordPress still refuses: its host resolves to a local/private IP (e.g. *.local dev sites).
		if ( ! wp_http_validate_url( $url ) ) {
			/* translators: %s: URL. */
			return new \WP_Error( 'image', sprintf( __( 'Image "%s" is on a local or private network address, so WordPress blocks downloading it. Upload the images to the Media Library or use a public URL.', 'tlp-food-menu' ), $url ) );
		}

		// Already in this site's Media Library.
		$id = attachment_url_to_postid( $url );

		if ( $id ) {
			return $id;
		}

		$existing = get_posts(
			[
				'post_type'      => 'attachment',
				'post_status'    => 'any',
				'posts_per_page' => 1,
				'fields'         => 'ids',
				'meta_key'       => self::SOURCE_URL_META, // phpcs:ignore WordPress.DB.SlowDBQuery.slow_db_query_meta_key
				'meta_value'     => $url, // phpcs:ignore WordPress.DB.SlowDBQuery.slow_db_query_meta_value
			]
		);

		if ( $existing ) {
			return (int) $existing[0];
		}

		if ( ! $download ) {
			/* translators: %s: URL. */
			return new \WP_Error( 'image', sprintf( __( 'Image %s is not in the Media Library.', 'tlp-food-menu' ), $url ) );
		}

		if ( ! current_user_can( 'upload_files' ) ) {
			return new \WP_Error( 'image', __( 'You are not allowed to upload images.', 'tlp-food-menu' ) );
		}

		require_once ABSPATH . 'wp-admin/includes/file.php';
		require_once ABSPATH . 'wp-admin/includes/media.php';
		require_once ABSPATH . 'wp-admin/includes/image.php';

		$tmp = download_url( $url, 30 );

		if ( is_wp_error( $tmp ) ) {
			/* translators: 1: URL, 2: error. */
			return new \WP_Error( 'image', sprintf( __( 'Image %1$s could not be downloaded: %2$s', 'tlp-food-menu' ), $url, $tmp->get_error_message() ) );
		}

		$name = sanitize_file_name( wp_basename( wp_parse_url( $url, PHP_URL_PATH ) ) );

		// URLs without an image extension (CDNs) — name it from the real type.
		if ( ! wp_check_filetype( $name )['ext'] ) {
			$mime = wp_get_image_mime( $tmp );
			$exts = [
				'image/jpeg' => 'jpg',
				'image/png'  => 'png',
				'image/gif'  => 'gif',
				'image/webp' => 'webp',
			];
			$name = ( $name ? $name : 'image' ) . '.' . ( $exts[ $mime ] ?? 'jpg' );
		}

		$id = media_handle_sideload(
			[
				'name'     => $name,
				'tmp_name' => $tmp,
			],
			$post_id
		);

		if ( is_wp_error( $id ) ) {
			wp_delete_file( $tmp );

			/* translators: 1: URL, 2: error. */
			return new \WP_Error( 'image', sprintf( __( 'Image %1$s could not be saved: %2$s', 'tlp-food-menu' ), $url, $id->get_error_message() ) );
		}

		update_post_meta( $id, self::SOURCE_URL_META, $url );

		return (int) $id;
	}
}
