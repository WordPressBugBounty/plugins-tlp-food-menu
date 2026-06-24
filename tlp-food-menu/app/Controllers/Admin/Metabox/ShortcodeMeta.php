<?php
/**
 * Admin Shortcode Metabox Class.
 *
 * @package RT_FoodMenu
 */

namespace RT\FoodMenu\Controllers\Admin\Metabox;

use RT\FoodMenu\Helpers\Fns;
use RT\FoodMenu\Helpers\Options;

// Do not allow directly accessing this file.
if ( ! defined( 'ABSPATH' ) ) {
	exit( 'This script cannot be accessed directly.' );
}

/**
 * Admin Shortcode Metabox Class.
 */
class ShortcodeMeta {

	use \RT\FoodMenu\Traits\SingletonTrait;

	/**
	 * Class Init.
	 *
	 * @return void
	 */
	protected function init() {
		add_action( 'admin_enqueue_scripts', [ $this, 'admin_enqueue_scripts' ] );
		add_action( 'add_meta_boxes', [ $this, 'add_meta_boxes' ], 10 );
		add_action( 'save_post', [ $this, 'save_meta_boxes' ], 10, 2 );
		add_action( 'edit_form_after_title', [ $this, 'after_title_text' ] );
		add_action( 'admin_init', [ $this, 'fm_pro_remove_all_meta_box' ] );
		add_action( 'admin_footer', [ $this, 'pro_alert_html' ] );
	}

	/**
	 * Admin Enqueue Scripts.
	 *
	 * @return void
	 */
	public function admin_enqueue_scripts() {
		global $pagenow, $typenow;

		// validate page.
		if ( ! in_array( $pagenow, [ 'post.php', 'post-new.php', 'edit.php' ] ) ) {
			return;
		}

		if ( in_array( $typenow, [ TLPFoodMenu()->shortCodePT, 'fmp_reservation' ] ) ) {
			wp_enqueue_style( 'fm-admin-fmp-only' );
		}

		if ( $typenow != TLPFoodMenu()->shortCodePT ) {
			return;
		}

		wp_enqueue_media();

		$select2Id = 'fm-select2';

		if ( class_exists( 'Avada' ) ) {
			$select2Id = 'select2-avada-js';
		} elseif ( class_exists( 'wp_megamenu_base' ) ) {
			wp_dequeue_script( 'wpmm-select2' );
			wp_dequeue_script( 'wpmm_scripts_admin' );
		}


		wp_enqueue_style(
			[
				'wp-color-picker',
				'fm-select2',
				'fm-frontend',
				'fm-admin',
				'fm-admin-preview',
			]
		);

		wp_enqueue_script(
			[
				'jquery',
				'wp-color-picker',
				$select2Id,
				'fm-admin',
				'fm-admin-preview',
			]
		);

		wp_add_inline_style(
			'fm-admin',
			'#rt_plugin_sc_pro_information .handle-actions,
			#rt_plugin_sc_pro_information .handle-order-higher,
			#rt_plugin_sc_pro_information .handle-order-lower,
			#rt_plugin_sc_pro_information .handlediv { display: none !important; }
			#rt_plugin_sc_pro_information .hndle { cursor: default !important; }
			#rt_plugin_sc_pro_information { position: sticky; top: 56px; }'
		);

		$nonce = wp_create_nonce( Fns::nonceText() );

		wp_localize_script(
			'fm-admin',
			'fmp',
			[
				'nonceID' => esc_attr( Fns::nonceID() ),
				'nonce'   => esc_attr( $nonce ),
				'ajaxurl' => esc_url( admin_url( 'admin-ajax.php' ) ),
			]
		);
	}

	/**
	 * Add Meta Box.
	 *
	 * @return void
	 */
	public function add_meta_boxes() {
		add_meta_box(
			TLPFoodMenu()->shortCodePT . '_sc_settings_meta',
			esc_html__( 'Short Code Generator', 'tlp-food-menu' ),
			[ $this, 'fm_sc_settings_selection' ],
			TLPFoodMenu()->shortCodePT,
			'normal',
			'high'
		);

		add_meta_box(
			TLPFoodMenu()->shortCodePT . '_sc_preview_meta',
			esc_html__( 'Layout Preview', 'tlp-food-menu' ),
			[ $this, 'fm_sc_preview_selection' ],
			TLPFoodMenu()->shortCodePT,
			'normal',
			'high'
		);

		add_meta_box(
			'rt_plugin_sc_pro_information',
			esc_html__( 'Documentation', 'tlp-food-menu' ),
			[ $this, 'rt_plugin_sc_pro_information' ],
			TLPFoodMenu()->shortCodePT,
			'side',
			'default'
		);
	}

	/**
	 * Save Meta Box.
	 *
	 * @param int $post_id Post ID.
	 * @param object $post Post object.
	 *
	 * @return void
	 */
	public function save_meta_boxes( $post_id, $post ) {
		if ( defined( 'DOING_AUTOSAVE' ) && DOING_AUTOSAVE ) {
			return;
		}

		if ( ! wp_verify_nonce( Fns::getNonce(), Fns::nonceText() ) ) {
			return $post_id;
		}

		if ( TLPFoodMenu()->shortCodePT != $post->post_type ) {
			return $post_id;
		}

		$mates = Fns::fmpScMetaFields();
		$mates = apply_filters( 'rtfm_sc_meta_fields', $mates );

		foreach ( $mates as $metaKey => $field ) {
			/**
			 * Old code before sanitization. should remove later
			 * $rValue1 = ! empty( $_REQUEST[ $metaKey ] ) ? wp_unslash( $_REQUEST[ $metaKey ] ) : null;
			 */
			$rValue = '';
			if ( ! empty( $_REQUEST[ $metaKey ] ) ) {
				if ( is_array( $_REQUEST[ $metaKey ] ) ) {
					$rValue = array_map( 'sanitize_text_field', wp_unslash( $_REQUEST[ $metaKey ] ) );
				} else {
					$rValue = sanitize_text_field( wp_unslash( $_REQUEST[ $metaKey ] ) );
				}
			}

			$value = Fns::sanitize( $field, $rValue );

			if ( empty( $field['multiple'] ) ) {
				update_post_meta( $post_id, $metaKey, $value );
			} else {
				if ( apply_filters( 'tlp_fmp_has_multiple_meta_issue', false ) ) {
					update_post_meta( $post_id, $metaKey, $value );
				} else {
					delete_post_meta( $post_id, $metaKey );
					if ( is_array( $value ) && ! empty( $value ) ) {
						foreach ( $value as $item ) {
							add_post_meta( $post_id, $metaKey, $item );
						}
					} else {
						update_post_meta( $post_id, $metaKey, '' );
					}
				}
			}
		}

		if ( isset( $_POST['_rtfm_last_active_tab'] ) ) {
			update_post_meta( $post_id, '_rtfm_last_active_tab', sanitize_text_field( wp_unslash( $_POST['_rtfm_last_active_tab'] ) ) );
		}
	}

	/**
	 * Text after title.
	 *
	 * @param object $post Post Object.
	 *
	 * @return string
	 */
	public function after_title_text( $post ) {
		if ( TLPFoodMenu()->shortCodePT !== $post->post_type ) {
			return;
		}

		$primary      = Fns::get_setting( 'fm_primary_color', '#fc202e' );
		$shortcode    = sprintf( '[foodmenu id="%d" title="%s"]', (int) $post->ID, esc_attr( $post->post_title ) );
		$php_snippet  = sprintf( "<?php echo do_shortcode( '%s' ); ?>", $shortcode );
		$copy_label   = esc_html__( 'Copy', 'tlp-food-menu' );
		$copied_label = esc_html__( 'Copied!', 'tlp-food-menu' );
		$sc_label     = esc_html__( 'Shortcode', 'tlp-food-menu' );
		$php_label    = esc_html__( 'PHP Snippet', 'tlp-food-menu' );
		$sc_desc      = esc_html__( 'Copy this shortcode and paste it into any page, post, or widget to display this menu.', 'tlp-food-menu' );
		$php_desc     = esc_html__( 'Use this PHP snippet inside your theme or template files to render this menu programmatically.', 'tlp-food-menu' );

		$copy_icon = '<svg class="fmp-sc-copy-icon" width="14" height="14" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><rect x="9" y="9" width="13" height="13" rx="2" ry="2"/><path d="M5 15H4a2 2 0 0 1-2-2V4a2 2 0 0 1 2-2h9a2 2 0 0 1 2 2v1"/></svg>';
		$sc_icon   = '<svg class="fmp-sc-label-icon" width="14" height="14" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.4" stroke-linecap="round" stroke-linejoin="round"><polyline points="16 18 22 12 16 6"/><polyline points="8 6 2 12 8 18"/></svg>';
		$php_icon  = '<svg class="fmp-sc-label-icon" width="14" height="14" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.4" stroke-linecap="round" stroke-linejoin="round"><path d="M14 2H6a2 2 0 0 0-2 2v16a2 2 0 0 0 2 2h12a2 2 0 0 0 2-2V8z"/><polyline points="14 2 14 8 20 8"/></svg>';

		$html = '<div class="fmp-shortcode-input-wrapper" style="--fmp-sc-primary:' . esc_attr( $primary ) . ';">';
		$html .= '<div class="fmp-sc-fields">';

		$html .= '<div class="fmp-sc-field">';
		$html .= '<label class="fmp-sc-label">' . $sc_icon . esc_html( $sc_label ) . '</label>';
		$html .= '<p class="fmp-sc-desc">' . esc_html( $sc_desc ) . '</p>';
		$html .= '<div class="fmp-sc-field-wrap">';
		$html .= '<input type="text" readonly="readonly" onfocus="this.select();" value="' . esc_attr( $shortcode ) . '" class="fmp-sc-input">';
		$html .= '<button type="button" class="fmp-sc-copy" aria-label="' . esc_attr__( 'Copy shortcode', 'tlp-food-menu' ) . '">';
		$html .= $copy_icon . '<span class="fmp-sc-copy-text">' . esc_html( $copy_label ) . '</span>';
		$html .= '</button>';
		$html .= '</div></div>';

		$html .= '<div class="fmp-sc-field">';
		$html .= '<label class="fmp-sc-label">' . $php_icon . esc_html( $php_label ) . '</label>';
		$html .= '<p class="fmp-sc-desc">' . esc_html( $php_desc ) . '</p>';
		$html .= '<div class="fmp-sc-field-wrap">';
		$html .= '<input type="text" readonly="readonly" onfocus="this.select();" value="' . esc_attr( $php_snippet ) . '" class="fmp-sc-input">';
		$html .= '<button type="button" class="fmp-sc-copy" aria-label="' . esc_attr__( 'Copy PHP snippet', 'tlp-food-menu' ) . '">';
		$html .= $copy_icon . '<span class="fmp-sc-copy-text">' . esc_html( $copy_label ) . '</span>';
		$html .= '</button>';
		$html .= '</div></div>';

		$html .= '</div></div>';

		$html .= '<script>
		(function(){
			if (window.fmpShortcodeCopyInit) return;
			window.fmpShortcodeCopyInit = true;
			var COPIED = ' . wp_json_encode( $copied_label ) . ';
			var COPY = ' . wp_json_encode( $copy_label ) . ';
			document.addEventListener("click", function(e){
				var btn = e.target.closest && e.target.closest(".fmp-sc-copy");
				if (!btn) return;
				e.preventDefault();
				var input = btn.parentNode.querySelector(".fmp-sc-input");
				if (!input) return;
				var label = btn.querySelector(".fmp-sc-copy-text");
				var done = function(){
					btn.classList.add("is-copied");
					if (label) label.textContent = COPIED;
					clearTimeout(btn._fmpT);
					btn._fmpT = setTimeout(function(){
						btn.classList.remove("is-copied");
						if (label) label.textContent = COPY;
					}, 1600);
				};
				if (navigator.clipboard && window.isSecureContext) {
					navigator.clipboard.writeText(input.value).then(done).catch(function(){
						try { input.select(); document.execCommand("copy"); done(); } catch(_){}
					});
				} else {
					try { input.select(); document.execCommand("copy"); done(); } catch(_){}
				}
			});
		})();
		</script>';

		Fns::print_html( $html, true );
	}

	/**
	 * Remove all meta boxes.
	 *
	 * @return void
	 */
	public function fm_pro_remove_all_meta_box() {
		if ( is_admin() ) {
			add_filter(
				'get_user_option_meta-box-order_' . TLPFoodMenu()->shortCodePT,
				[ $this, 'remove_all_meta_boxes_fmp_sc' ]
			);
		}
	}

	/**
	 * Add only custom meta box.
	 *
	 * @return array
	 */
	public function remove_all_meta_boxes_fmp_sc() {
		global $wp_meta_boxes;

		$publishBox   = $wp_meta_boxes[ TLPFoodMenu()->shortCodePT ]['side']['core']['submitdiv'];
		$scBox        = $wp_meta_boxes[ TLPFoodMenu()->shortCodePT ]['normal']['high'][ TLPFoodMenu()->shortCodePT . '_sc_settings_meta' ];
		$scPreviewBox = $wp_meta_boxes[ TLPFoodMenu()->shortCodePT ]['normal']['high'][ TLPFoodMenu()->shortCodePT . '_sc_preview_meta' ];
		$docBox       = $wp_meta_boxes[ TLPFoodMenu()->shortCodePT ]['side']['default']['rt_plugin_sc_pro_information'];

		$wp_meta_boxes[ TLPFoodMenu()->shortCodePT ] = [
			'side'   => [
				'core'    => [ 'submitdiv' => $publishBox ],
				'default' => [
					'rt_plugin_sc_pro_information' => $docBox,
				],
			],
			'normal' => [
				'high' => [
					TLPFoodMenu()->shortCodePT . '_sc_settings_meta' => $scBox,
					TLPFoodMenu()->shortCodePT . '_sc_preview_meta'  => $scPreviewBox,
				],
			],
		];

		return [];
	}

	/**
	 * Setting Sections
	 *
	 * @param $post
	 */
	/**
	 * Setting Sections
	 *
	 * @param object $post Post object.
	 *
	 * @return void
	 */
	public function fm_sc_settings_selection( $post ) {
		$last_tab = trim( get_post_meta( $post->ID, '_rtfm_last_active_tab', true ) );
		$last_tab = ! empty( $last_tab ) ? $last_tab : 'sc-fmp-layout';

		wp_nonce_field( Fns::nonceText(), Fns::nonceID() );

		$html = null;
		$html .= '<div class="rt-shortcode-settings rt-tab-container">';
		$html .= sprintf(
			'<ul class="rt-tab-nav">
			<li %s><a href="#sc-fmp-layout"><svg width="18" height="18" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><rect x="3" y="3" width="7" height="7" rx="1"/><rect x="14" y="3" width="7" height="7" rx="1"/><rect x="3" y="14" width="7" height="7" rx="1"/><rect x="14" y="14" width="7" height="7" rx="1"/></svg>' . esc_html__( 'Layout', 'tlp-food-menu' ) . '</a></li>
			<li %s><a href="#sc-fmp-filter"><svg width="18" height="18" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><polygon points="22 3 2 3 10 12.46 10 19 14 21 14 12.46 22 3"/></svg>' . esc_html__( 'Filtering', 'tlp-food-menu' ) . '</a></li>
			<li %s><a href="#sc-fmp-field-selection"><svg width="18" height="18" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><path d="M12 3h7a2 2 0 0 1 2 2v14a2 2 0 0 1-2 2h-7m0-18H5a2 2 0 0 0-2 2v14a2 2 0 0 0 2 2h7m0-18v18"/></svg>' . esc_html__( 'Field Selection', 'tlp-food-menu' ) . '</a></li>
			<li %s><a href="#sc-fmp-style"><svg width="18" height="18" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><circle cx="13.5" cy="6.5" r="2.5"/><path d="M17.5 10.5 19 12l-5.5 5.5"/><circle cx="8.5" cy="14.5" r="2.5"/><path d="M2 21a8 8 0 0 1 10.434-7.62"/><path d="M22 3c-1.5 1-3.5 1.5-5 1.5S13 4 12 3"/></svg>' . esc_html__( 'Styling', 'tlp-food-menu' ) . '</a></li>
			</ul>',
			'sc-fmp-layout' === $last_tab ? 'class="active"' : '',
			'sc-fmp-filter' === $last_tab ? 'class="active"' : '',
			'sc-fmp-field-selection' === $last_tab ? 'class="active"' : '',
			'sc-fmp-style' === $last_tab ? 'class="active"' : ''
		);

		$html .= sprintf(
			'<div id="sc-fmp-layout" class="rt-tab-content" %s>',
			'sc-fmp-layout' === $last_tab ? 'style="display:block"' : ''
		);
		$html .= Fns::renderView( 'metabox.layout', $post, true );
		$html .= '</div>';

		$html .= sprintf( '<div id="sc-fmp-filter" class="rt-tab-content" %s>%s</div>', 'sc-fmp-filter' === $last_tab ? 'style="display:block"' : '', Fns::rtFieldGenerator( Options::scFilterMetaFields() ) );
		$html .= sprintf( '<div id="sc-fmp-field-selection" class="rt-tab-content" %s>%s</div>', 'sc-fmp-field-selection' === $last_tab ? 'style="display:block"' : '', Fns::rtFieldGenerator( Options::scItemFields() ) );

		$html .= sprintf(
			'<div id="sc-fmp-style" class="rt-tab-content" %s>',
			'sc-fmp-style' === $last_tab ? 'style="display:block"' : ''
		);
		$html .= Fns::renderView( 'metabox.styling', $post, true );
		$html .= '</div>';

		$html .= sprintf( '<input type="hidden" id="_rtfm_last_active_tab" name="_rtfm_last_active_tab" value="%s"/>', $last_tab );
		$html .= '</div>';

		Fns::print_html( $html, true );
	}

	/**
	 * Pro information.
	 *
	 * @return void
	 */
	public function rt_plugin_sc_pro_information() {
		global $pagenow;

		$html    = '';
		$doc     = 'https://www.radiustheme.com/docs/food-menu/getting-started/installations/';
		$contact = 'https://www.radiustheme.com/contact/';
		$fb      = 'https://www.facebook.com/groups/234799147426640/';
		$rt      = 'https://www.radiustheme.com/';

		if ( ! TLPFoodMenu()->has_pro() ) {
			$html .= Options::get_pro_feature_list();
		}

		$primary = Fns::get_setting( 'fm_primary_color', '#fc202e' );

		$html .= '<div style="display:flex;flex-direction:column;gap:12px;margin-top:16px;">';

		$html .= '<div style="flex:1;border-radius:12px;border:1px solid #E9EDF7;background:#fff;padding:20px;text-align:center;">
			<div style="width:44px;height:44px;border-radius:12px;background:' . esc_attr( $primary ) . '12;display:inline-flex;align-items:center;justify-content:center;margin-bottom:12px;">
				<svg width="22" height="22" viewBox="0 0 24 24" fill="none" stroke="' . esc_attr( $primary ) . '" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><path d="M4 19.5v-15A2.5 2.5 0 0 1 6.5 2H20v20H6.5a2.5 2.5 0 0 1 0-5H20"/></svg>
			</div>
			<h4 style="font-size:16px;font-weight:700;color:#1a1a2e;margin:0 0 6px;">' . esc_html__( 'Documentation', 'tlp-food-menu' ) . '</h4>
			<p style="font-size:14px;color:#676767;margin:0 5px 14px;line-height:1.5;">' . esc_html__( 'Step by step guide with screenshots & video.', 'tlp-food-menu' ) . '</p>
			<a href="' . esc_url( $doc ) . '" target="_blank" style="display:inline-block;padding:8px 16px;border-radius:6px;background:' . esc_attr( $primary ) . ';color:#fff;font-size:13px;font-weight:600;text-decoration:none;">' . esc_html__( 'View Docs', 'tlp-food-menu' ) . '</a>
		</div>';

		$html .= '<div style="flex:1;border-radius:12px;border:1px solid #E9EDF7;background:#fff;padding:20px;text-align:center;">
			<div style="width:44px;height:44px;border-radius:12px;background:#22C55E12;display:inline-flex;align-items:center;justify-content:center;margin-bottom:12px;">
				<svg width="22" height="22" viewBox="0 0 24 24" fill="none" stroke="#22C55E" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><path d="M21 15a2 2 0 0 1-2 2H7l-4 4V5a2 2 0 0 1 2-2h14a2 2 0 0 1 2 2z"/></svg>
			</div>
			<h4 style="font-size:16px;font-weight:700;color:#1a1a2e;margin:0 0 6px;">' . esc_html__( 'Need Help?', 'tlp-food-menu' ) . '</h4>
			<p style="font-size:14px;color:#676767;margin:0 0 14px;line-height:1.5;">' . esc_html__( 'Create a ticket or join our live chat.', 'tlp-food-menu' ) . '</p>
			<a href="' . esc_url( $contact ) . '" target="_blank" style="display:inline-block;padding:8px 16px;border-radius:6px;background:' . esc_attr( $primary ) . ';color:#fff;font-size:13px;font-weight:600;text-decoration:none;">' . esc_html__( 'Get Support', 'tlp-food-menu' ) . '</a>
		</div>';

		$html .= '</div>';

		Fns::print_html( $html, true );
	}

	/**
	 * Preview section
	 *
	 * @return void
	 */
	public function fm_sc_preview_selection() {
		echo "<div class='fmp-response'><span class='spinner'></span></div><div id='fmp-preview-container'></div>";
	}

	/**
	 * Pro Alert HTML.
	 *
	 * @return void
	 */
	public function pro_alert_html() {
		global $typenow;

		if ( TLPFoodMenu()->has_pro() ) {
			return;
		}

		if ( ( isset( $_GET['page'] ) && $_GET['page'] != 'food_menu_settings' ) || ! ( $typenow == TLPFoodMenu()->post_type || $typenow == TLPFoodMenu()->shortCodePT ) ) { //phpcs:ignore
			return;
		}

		$html = '';
		$pro  = 'https://www.radiustheme.com/downloads/food-menu-pro-wordpress/';
		$html .= '<div class="rtfm-document-box rtfm-alert rtfm-pro-alert">
				<div class="rtfm-box-icon"><i class="dashicons dashicons-lock"></i></div>
				<div class="rtfm-box-content">
					<h3 class="rtfm-box-title">' . esc_html__( 'Pro Field Alert!', 'tlp-food-menu' ) . '</h3>
					<p><span></span>' . esc_html__( 'Sorry! This is a Pro field. To activate this field, you need to upgrade to the Pro version.', 'tlp-food-menu' ) . '</p>
					<a href="' . esc_url( $pro ) . '" target="_blank" class="rt-admin-btn">' . esc_html__(
				'Get Pro Version',
				'tlp-food-menu'
			) . '</a>
					<a href="#" target="_blank" class="rtfm-alert-close rtfm-pro-alert-close"><span class="dashicons dashicons-no-alt"></span></a>
				</div>
			</div>';

		Fns::print_html( $html );
	}
}
