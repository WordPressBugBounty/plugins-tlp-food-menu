<?php
// phpcs:disable WordPress.NamingConventions.PrefixAllGlobals -- Established plugin: public namespace, hook names, functions and theme-overridable template variables must stay unchanged for backward compatibility.

namespace RT\FoodMenu\Controllers\Reservation;

defined( 'ABSPATH' ) || exit();

use RT\FoodMenu\Helpers\Fns;
use RT\FoodMenu\Helpers\Options;
use RT\FoodMenu\Traits\SingletonTrait;

/**
 * Reservation admin metabox: renders the "Reservation Data" form on the
 * fmp_reservation post-edit screen, handles save_post for that post type,
 * and wires up intl-tel-input on the admin phone field.
 *
 * Reservation moved from pro to free — this used to live in
 * food-menu-pro/app/Controllers/Admin/Metabox/PostMeta.php.
 */
class ReservationMeta {

	use SingletonTrait;

	/**
	 * Init hooks.
	 *
	 * @return void
	 */
	public function init() {
		add_action( 'admin_enqueue_scripts', [ $this, 'enqueue_reservation_phone_input' ], 20 );
		add_action( 'add_meta_boxes', [ $this, 'add_meta_boxes' ] );
		add_action( 'save_post', [ $this, 'save_meta_boxes' ], 10, 2 );
	}

	/**
	 * Register the reservation data metabox.
	 *
	 * @return void
	 */
	public function add_meta_boxes() {
		add_meta_box(
			'fmp-reservation-data',
			esc_html__( 'Reservation Data', 'tlp-food-menu' ),
			[ $this, 'render_reservation_metabox' ],
			'fmp_reservation',
			'normal',
			'low'
		);
	}

	/**
	 * Render the reservation form fields.
	 *
	 * @param \WP_Post $post Post object.
	 *
	 * @return void
	 */
	public function render_reservation_metabox( $post ) {
		wp_nonce_field( Fns::nonceText(), Fns::nonceId() );
		$booking_id = get_post_meta( $post->ID, 'fmp_resi_meta_invoice', true );
		?>
		<div class="fmp-reservation-meta-box-wrap">
			<?php if ( $booking_id ) : ?>
				<div class="rt-field-wrapper" id="fmp_resi_meta_invoice_holder">
					<div class="rt-label"><label for="fmp_resi_meta_invoice_input"><?php echo esc_html__( 'Booking ID', 'tlp-food-menu' ); ?></label></div>
					<div class="rt-field fmp-booking-id-field">
						<input type="text" id="fmp_resi_meta_invoice_input" class="rt-form-control" value="<?php echo esc_attr( $booking_id ); ?>" readonly="readonly">
						<button type="button" class="button fmp-copy-booking-id" data-target="#fmp_resi_meta_invoice_input" aria-label="<?php esc_attr_e( 'Copy Booking ID', 'tlp-food-menu' ); ?>">
							<span class="dashicons dashicons-clipboard" aria-hidden="true"></span>
							<span class="fmp-copy-text"><?php esc_html_e( 'Copy', 'tlp-food-menu' ); ?></span>
						</button>
					</div>
				</div>
				<style>
					.fmp-booking-id-field { display: flex; align-items: center; gap: 8px; }
					.fmp-booking-id-field input { flex: 0 0 auto; max-width: 280px; }
					.fmp-copy-booking-id { display: inline-flex; align-items: center; gap: 4px; }
					.fmp-copy-booking-id .dashicons { font-size: 16px; height: 16px; width: 16px; line-height: 1; }
					.fmp-copy-booking-id.is-copied { color: #15803d; border-color: #15803d; }
				</style>
				<script>
				(function () {
					if (window.fmpBookingIdCopyBound) return;
					window.fmpBookingIdCopyBound = true;

					var copiedLabel = <?php echo wp_json_encode( __( 'Copied!', 'tlp-food-menu' ) ); ?>;

					document.addEventListener('click', function (e) {
						var btn = e.target.closest('.fmp-copy-booking-id');
						if (!btn) return;

						var input = document.querySelector(btn.dataset.target);
						if (!input) return;

						var value = input.value;
						var done = function () { showFeedback(btn); };
						var fail = function () { fallbackCopy(input, done); };

						if (navigator.clipboard && window.isSecureContext) {
							navigator.clipboard.writeText(value).then(done).catch(fail);
						} else {
							fallbackCopy(input, done);
						}
					});

					function fallbackCopy(input, done) {
						input.removeAttribute('readonly');
						input.select();
						input.setSelectionRange(0, input.value.length);
						try { document.execCommand('copy'); } catch (e) {}
						input.setAttribute('readonly', 'readonly');
						input.blur();
						done();
					}

					function showFeedback(btn) {
						var textEl = btn.querySelector('.fmp-copy-text');
						if (!textEl) return;
						var original = textEl.dataset.original || textEl.textContent;
						textEl.dataset.original = original;
						textEl.textContent = copiedLabel;
						btn.classList.add('is-copied');
						clearTimeout(btn._fmpCopyTimer);
						btn._fmpCopyTimer = setTimeout(function () {
							textEl.textContent = original;
							btn.classList.remove('is-copied');
						}, 1500);
					}
				})();
				</script>
			<?php endif; ?>
			<?php Fns::print_html( Fns::rtFieldGenerator( Options::reservationMetaField() ), true ); ?>
		</div>
		<?php
	}

	/**
	 * Save reservation meta fields on post save.
	 *
	 * @param int      $post_id Post ID.
	 * @param \WP_Post $post    Post object.
	 *
	 * @return int
	 */
	public function save_meta_boxes( $post_id, $post ) {
		if ( defined( 'DOING_AUTOSAVE' ) && DOING_AUTOSAVE ) {
			return $post_id;
		}

		// Only process when the actual post-edit form is submitted, not during
		// bulk/row actions (trash, untrash, delete) that also fire save_post.
		if ( empty( $_POST['action'] ) || 'editpost' !== $_POST['action'] ) {
			return $post_id;
		}

		if ( 'fmp_reservation' !== $post->post_type ) {
			return $post_id;
		}

		if ( ! wp_verify_nonce( Fns::getNonce(), Fns::nonceText() ) ) {
			return $post_id;
		}

		$fields = Options::reservationMetaField();

		foreach ( $fields as $metaKey => $field ) {
			if ( 'fmp_resi_meta_status' === $metaKey ) {
				$reser_status = get_post_meta( $post_id, $metaKey, true );
				if ( isset( $_REQUEST[ $metaKey ] ) ) {
					$newReservation = sanitize_text_field( wp_unslash( $_REQUEST[ $metaKey ] ) );
					if ( $reser_status !== $newReservation ) {
						do_action( 'fmp_reservation_change_email_hook', $post_id, $newReservation );
					}
				}
			}

			//phpcs:ignore WordPress.Security.ValidatedSanitizedInput.InputNotSanitized, WordPress.Security.ValidatedSanitizedInput.MissingUnslash
			$rValue = $_REQUEST[ $metaKey ] ?? null;
			$value  = Fns::sanitize( $field, $rValue );

			if ( empty( $field['multiple'] ) ) {
				update_post_meta( $post_id, $metaKey, $value );
			} else {
				delete_post_meta( $post_id, $metaKey );
				if ( is_array( $value ) && ! empty( $value ) ) {
					foreach ( $value as $item ) {
						add_post_meta( $post_id, $metaKey, $item );
					}
				}
			}
		}

		// Convert date to timestamp.
		if ( ! empty( $_REQUEST['fmp_resi_meta_date'] ) ) {
			$date_input = sanitize_text_field( wp_unslash( $_REQUEST['fmp_resi_meta_date'] ) );
			$timestamp  = is_numeric( $date_input ) ? (int) $date_input : strtotime( $date_input );
			if ( $timestamp ) {
				update_post_meta( $post_id, 'fmp_resi_meta_date', $timestamp );
			}
		}

		// Generate booking ID for new reservations.
		$existing_invoice = get_post_meta( $post_id, 'fmp_resi_meta_invoice', true );
		if ( empty( $existing_invoice ) ) {
			$invoice_no = Fns::generate_invoice_number( $post_id );
			update_post_meta( $post_id, 'fmp_resi_meta_invoice', $invoice_no );
		}

		return $post_id;
	}

	/**
	 * Enqueue intl-tel-input + inline init script on the reservation post-edit
	 * screen so the admin phone field has the same country picker + validation
	 * as the frontend form.
	 *
	 * @return void
	 */
	public function enqueue_reservation_phone_input() {
		global $pagenow, $typenow;

		if ( ! in_array( $pagenow, [ 'post.php', 'post-new.php' ], true ) ) {
			return;
		}
		if ( 'fmp_reservation' !== $typenow ) {
			return;
		}

		wp_enqueue_script( 'fmp-intl-tel-input' );
		wp_enqueue_style( 'fmp-intl-tel-input' );

		$settings          = get_option( TLPFoodMenu()->options['settings'], [] );
		$default_country   = strtolower( sanitize_text_field( $settings['fmp_resi_default_country'] ?? 'us' ) );
		$allowed_countries = array_values(
			array_map(
				'strtolower',
				array_filter( (array) ( $settings['fmp_resi_allowed_countries'] ?? [] ), 'is_string' )
			)
		);

		wp_localize_script(
			'fmp-intl-tel-input',
			'fmpAdminResiPhone',
			[
				'default_country'   => $default_country,
				'allowed_countries' => $allowed_countries,
				'utils_script'      => TLPFoodMenu()->assets_url() . 'vendor/intl-tel-input/utils.js',
				'invalid_message'   => esc_html__( 'Please enter a valid phone number for the selected country.', 'tlp-food-menu' ),
				'required_message'  => esc_html__( 'Please enter your phone number.', 'tlp-food-menu' ),
			]
		);

		$init_script = <<<'JS'
(function () {
    function init() {
        if (typeof window.intlTelInput !== 'function') return;
        var el = document.getElementById('fmp_resi_meta_phone');
        if (!el || el.dataset.fmpItiBound) return;
        el.dataset.fmpItiBound = '1';

        var params = window.fmpAdminResiPhone || {};
        var initialCountry = params.default_country || 'us';
        var allowed = Array.isArray(params.allowed_countries) ? params.allowed_countries.filter(Boolean) : [];
        if (allowed.length && allowed.indexOf(initialCountry) === -1) {
            initialCountry = allowed[0];
        }

        var options = {
            initialCountry: initialCountry,
            separateDialCode: true,
            autoPlaceholder: 'aggressive',
            nationalMode: false,
            formatOnDisplay: true,
            utilsScript: params.utils_script || ''
        };
        if (allowed.length) options.onlyCountries = allowed;

        var iti = window.intlTelInput(el, options);
        window.fmpAdminResiPhoneIti = iti;

        var fieldHolder = document.getElementById('fmp_resi_meta_phone_holder') || el.parentNode;
        var errorEl = document.createElement('div');
        errorEl.className = 'fmp-resi-phone-error';
        errorEl.setAttribute('aria-live', 'polite');
        errorEl.style.cssText = 'display:none;margin-top:6px;font-size:13px;color:#dc2626;font-weight:500;';
        fieldHolder.appendChild(errorEl);

        function showError(msg) {
            errorEl.textContent = msg;
            errorEl.style.display = 'block';
            el.style.borderColor = '#dc2626';
        }
        function clearError() {
            errorEl.style.display = 'none';
            errorEl.textContent = '';
            el.style.borderColor = '';
        }

        function validate() {
            var raw = (el.value || '').trim();
            var isRequired = el.hasAttribute('required');

            if (!raw) {
                if (isRequired) {
                    showError(params.required_message || 'Phone number is required.');
                    return false;
                }
                clearError();
                return true;
            }

            var valid = null;
            try { valid = iti.isValidNumber(); } catch (e) {}

            if (valid === false) {
                showError(params.invalid_message || 'Invalid phone number.');
                return false;
            }

            clearError();
            return true;
        }
        window.fmpAdminResiPhoneValidate = validate;

        el.addEventListener('input', clearError);
        el.addEventListener('countrychange', function () {
            clearError();
            if (el.value.trim()) validate();
        });
        el.addEventListener('blur', validate);

        if (iti && iti.promise && typeof iti.promise.then === 'function') {
            iti.promise.then(function () {
                if (el.value.trim() && document.activeElement !== el) {
                    validate();
                }
            }).catch(function () {});
        }

        var form = el.closest('form') || document.getElementById('post');
        if (!form) return;

        form.addEventListener('submit', function (event) {
            if (!validate()) {
                event.preventDefault();
                event.stopImmediatePropagation();
                el.focus();
                return;
            }
            try {
                var full = iti.getNumber();
                if (full) el.value = full;
            } catch (e) {}
        }, true);
    }

    if (document.readyState === 'loading') {
        document.addEventListener('DOMContentLoaded', init);
    } else {
        init();
    }
})();
JS;

		wp_add_inline_script( 'fmp-intl-tel-input', $init_script );
	}
}
