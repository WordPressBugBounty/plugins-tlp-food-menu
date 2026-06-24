<?php
/**
 * Get help page.
 *
 * @package RT_FoodMenu
 */

// Do not allow directly accessing this file.
if ( ! defined( 'ABSPATH' ) ) {
    exit( 'This script cannot be accessed directly.' );
}
//phpcs:disable PluginCheck.CodeAnalysis.ImageFunctions.NonEnqueuedImage

$iframe      = 'https://www.youtube.com/embed/4jyoaEtwCKE';
$pro         = 'https://www.radiustheme.com/downloads/food-menu-pro-wordpress/';
$doc         = 'https://www.radiustheme.com/docs/food-menu/getting-started/installations/';
$contact     = 'https://www.radiustheme.com/contact/';
$fb          = 'https://www.facebook.com/groups/234799147426640/';
$rt          = 'https://www.radiustheme.com/';
$review      = 'https://wordpress.org/support/plugin/tlp-food-menu/reviews/?filter=5#new-post';
$has_pro     = TLPFoodMenu()->has_pro();
$primary_hex = \RT\FoodMenu\Helpers\Fns::get_setting( 'fm_primary_color', '#dc2626' );

if ( ! $primary_hex || ! preg_match( '/^#[0-9a-fA-F]{6}$/', $primary_hex ) ) {
    $primary_hex = '#dc2626';
}

$hex_raw = ltrim( $primary_hex, '#' );
$r       = hexdec( substr( $hex_raw, 0, 2 ) );
$g       = hexdec( substr( $hex_raw, 2, 2 ) );
$b       = hexdec( substr( $hex_raw, 4, 2 ) );

// Darker shade for gradient end.
$dark_hex = sprintf( '#%02x%02x%02x', max( 0, (int) round( $r * 0.6 ) ), max( 0, (int) round( $g * 0.6 ) ), max( 0, (int) round( $b * 0.6 ) ) );

// Hover shade.
$hover_hex = sprintf( '#%02x%02x%02x', max( 0, (int) round( $r * 0.75 ) ), max( 0, (int) round( $g * 0.75 ) ), max( 0, (int) round( $b * 0.75 ) ) );

// Light shade for testimonial border.
$light_hex   = sprintf( '#%02x%02x%02x', min( 255, (int) round( $r + ( 255 - $r ) * 0.6 ) ), min( 255, (int) round( $g + ( 255 - $g ) * 0.6 ) ), min( 255, (int) round( $b + ( 255 - $b ) * 0.6 ) ) );
$primary_rgb = "$r, $g, $b";
?>
<style>
    /* Reset & Base */
    .fmp-help * {
        box-sizing: border-box;
    }

    .fmp-help {
        max-width: 1100px;
        margin: 20px auto 40px;
        font-family: -apple-system, BlinkMacSystemFont, "Segoe UI", Roboto, Oxygen, Ubuntu, sans-serif;
        color: #1e293b;
    }

    .fmp-help a {
        text-decoration: none;
    }

    /* Hero */
    .fmp-help-hero {
        background: linear-gradient(135deg, <?php echo esc_attr( $primary_hex ); ?> 0%, <?php echo esc_attr( $dark_hex ); ?> 100%);
        border-radius: 16px;
        padding: 48px;
        color: #fff;
        display: flex;
        align-items: center;
        gap: 48px;
        margin-bottom: 32px;
    }

    .fmp-help-hero-text {
        flex: 1;
        min-width: 0;
    }

    .fmp-help-hero-text h1 {
        color: #FFFFFF;
        font-size: 28px;
        font-weight: 700;
        margin: 0 0 12px;
        line-height: 1.3;
    }

    .fmp-help-hero-text p {
        font-size: 15px;
        margin: 0 0 24px;
        opacity: 0.9;
        line-height: 1.6;
    }

    .fmp-help-hero-actions {
        display: flex;
        gap: 12px;
        flex-wrap: wrap;
    }

    .fmp-help-btn {
        display: inline-flex;
        align-items: center;
        gap: 8px;
        padding: 10px 20px;
        border-radius: 8px;
        font-size: 14px;
        font-weight: 600;
        transition: all 0.2s;
        border: none;
        cursor: pointer;
    }

    .fmp-help-btn svg {
        flex-shrink: 0;
    }

    .fmp-help-btn-white {
        background: #fff;
        color: <?php echo esc_attr( $primary_hex ); ?> !important;
    }

    .fmp-help-btn-white:hover {
        background: rgba(255, 255, 255, 0.9);
    }

    .fmp-help-btn-outline {
        background: rgba(255, 255, 255, 0.15);
        color: #fff;
        border: 1px solid rgba(255, 255, 255, 0.3);
    }

    .fmp-help-btn-outline:hover {
        background: rgba(255, 255, 255, 0.25);
        color: #fff;
    }

    .fmp-help-hero-video {
        flex: 0 0 420px;
        border-radius: 12px;
        overflow: hidden;
        box-shadow: 0 20px 40px rgba(0, 0, 0, 0.3);
    }

    .fmp-help-hero-video .fmp-help-embed {
        position: relative;
        padding-top: 56.25%;
    }

    .fmp-help-hero-video iframe {
        position: absolute;
        top: 0;
        left: 0;
        width: 100%;
        height: 100%;
        border: 0;
    }

    /* Section heading */
    .fmp-help-section-title {
        font-size: 20px;
        font-weight: 700;
        margin: 0 0 6px;
        color: #0f172a;
    }

    .fmp-help-section-desc {
        font-size: 14px;
        color: #64748b;
        margin: 0 0 24px;
    }

    /* Cards grid */
    .fmp-help-cards {
        display: grid;
        grid-template-columns: repeat(3, 1fr);
        gap: 20px;
        margin-bottom: 32px;
    }

    .fmp-help-card {
        background: #fff;
        border: 1px solid #e2e8f0;
        border-radius: 12px;
        padding: 28px;
        transition: box-shadow 0.2s, border-color 0.2s;
    }

    .fmp-help-card:hover {
        box-shadow: 0 4px 12px rgba(0, 0, 0, 0.08);
        border-color: #cbd5e1;
    }

    .fmp-help-card-icon {
        width: 44px;
        height: 44px;
        border-radius: 10px;
        display: flex;
        align-items: center;
        justify-content: center;
        margin-bottom: 16px;
    }

    .fmp-help-card h3 {
        font-size: 16px;
        font-weight: 600;
        margin: 0 0 8px;
        color: #0f172a;
    }

    .fmp-help-card p {
        font-size: 13px;
        color: #64748b;
        line-height: 1.6;
        margin: 0 0 16px;
    }

    .fmp-help-card-link {
        display: inline-flex;
        align-items: center;
        gap: 6px;
        font-size: 13px;
        font-weight: 600;
        color: <?php echo esc_attr( $primary_hex ); ?>;
        transition: gap 0.2s;
    }

    .fmp-help-card-link:hover {
        gap: 10px;
        color: <?php echo esc_attr( $hover_hex ); ?>;
    }

    /* Features grid */
    .fmp-help-features {
        background: #fff;
        border: 1px solid #e2e8f0;
        border-radius: 12px;
        padding: 32px;
        margin-bottom: 32px;
    }

    .fmp-help-features-header {
        display: flex;
        align-items: center;
        justify-content: space-between;
        margin-bottom: 24px;
    }

    .fmp-help-features-header div {
        flex: 1;
    }

    .fmp-help-features-grid {
        display: grid;
        grid-template-columns: repeat(2, 1fr);
        gap: 12px 32px;
    }

    .fmp-help-feature-item {
        display: flex;
        align-items: flex-start;
        gap: 10px;
        font-size: 13.5px;
        color: #334155;
        line-height: 1.5;
        padding: 8px 0;
    }

    .fmp-help-feature-check {
        flex-shrink: 0;
        width: 20px;
        height: 20px;
        background: #fef2f2;
        background: rgba(<?php echo esc_attr($primary_rgb)  ?>, 0.1);
        border-radius: 50%;
        display: flex;
        align-items: center;
        justify-content: center;
        margin-top: 1px;
    }

    .fmp-help-feature-check svg {
        color: <?php echo esc_attr( $primary_hex ); ?>
    }

    .fmp-help-btn-red {
        background: <?php echo esc_attr( $primary_hex ); ?>;
        color: #fff !important;
        flex-shrink: 0;
    }

    .fmp-help-btn-red:hover {
        background: <?php echo esc_attr($dark_hex);  ?>
    }

    /* Testimonials */
    .fmp-help-testimonials {
        display: grid;
        grid-template-columns: repeat(2, 1fr);
        gap: 20px;
        margin-bottom: 32px;
    }

    .fmp-help-testimonial {
        background: #fff;
        border: 1px solid #e2e8f0;
        border-radius: 12px;
        padding: 28px;
    }

    .fmp-help-testimonial-quote {
        font-size: 13.5px;
        color: #475569;
        line-height: 1.7;
        margin: 0 0 20px;
        position: relative;
        padding-left: 16px;
        border-left: 3px solid<?php echo esc_attr( $light_hex ); ?>;
    }

    .fmp-help-testimonial-author {
        display: flex;
        align-items: center;
        gap: 12px;
    }

    .fmp-help-testimonial-author img {
        width: 44px;
        height: 44px;
        border-radius: 50%;
        object-fit: cover;
        border: 2px solid #f1f5f9;
    }

    .fmp-help-testimonial-name {
        font-size: 14px;
        font-weight: 600;
        color: #0f172a;
        display: block;
    }

    .fmp-help-testimonial-stars {
        display: flex;
        gap: 2px;
        margin-top: 4px;
    }

    .fmp-help-testimonial-stars svg {
        color: #f59e0b;
    }

    /* CTA */
    .fmp-help-cta {
        background: linear-gradient(135deg, #0f172a 0%, #1e293b 100%);
        border-radius: 12px;
        padding: 36px 40px;
        display: flex;
        align-items: center;
        justify-content: space-between;
        gap: 24px;
        margin-bottom: 32px;
    }

    .fmp-help-cta h3 {
        font-size: 20px;
        font-weight: 700;
        color: #fff;
        margin: 0 0 6px;
    }

    .fmp-help-cta p {
        font-size: 14px;
        color: #94a3b8;
        margin: 0;
    }

    /* Responsive */
    @media (max-width: 1200px) {
        .fmp-help-hero {
            flex-direction: column;
            padding: 36px;
            gap: 32px;
        }

        .fmp-help-hero-video {
            flex: none;
            width: 100%;
            max-width: 560px;
        }
    }

    @media (max-width: 900px) {
        .fmp-help-cards {
            grid-template-columns: 1fr;
        }

        .fmp-help-testimonials {
            grid-template-columns: 1fr;
        }

        .fmp-help-features-grid {
            grid-template-columns: 1fr;
        }

        .fmp-help-features-header {
            flex-direction: column;
            align-items: flex-start;
            gap: 16px;
        }

        .fmp-help-cta {
            flex-direction: column;
            text-align: center;
        }
    }

    @media (max-width: 600px) {
        .fmp-help-hero {
            padding: 24px;
        }

        .fmp-help-hero-text h1 {
            font-size: 22px;
        }

        .fmp-help-features {
            padding: 24px;
        }
    }
</style>

<div class="fmp-help">

    <!-- Hero -->
    <div class="fmp-help-hero">
        <div class="fmp-help-hero-text">
            <h1><?php esc_html_e( 'Welcome to Food Menu', 'tlp-food-menu' ); ?></h1>
            <p><?php esc_html_e( 'The most powerful restaurant menu and online ordering plugin for WordPress. Display beautiful food menus, manage orders, handle inventory, and grow your restaurant business.', 'tlp-food-menu' ); ?></p>
            <div class="fmp-help-hero-actions">
                <a href="<?php echo esc_url( $doc ); ?>" target="_blank" class="fmp-help-btn fmp-help-btn-white">
                    <svg width="16" height="16" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24">
                        <path d="M2 3h6a4 4 0 0 1 4 4v14a3 3 0 0 0-3-3H2z"/>
                        <path d="M22 3h-6a4 4 0 0 0-4 4v14a3 3 0 0 1 3-3h7z"/>
                    </svg>
                    <?php esc_html_e( 'Read Documentation', 'tlp-food-menu' ); ?>
                </a>
                <a href="<?php echo esc_url( $contact ); ?>" target="_blank" class="fmp-help-btn fmp-help-btn-outline">
                    <svg width="16" height="16" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24">
                        <path d="M21 15a2 2 0 0 1-2 2H7l-4 4V5a2 2 0 0 1 2-2h14a2 2 0 0 1 2 2z"/>
                    </svg>
                    <?php esc_html_e( 'Get Support', 'tlp-food-menu' ); ?>
                </a>
            </div>
        </div>
        <div class="fmp-help-hero-video">
            <div class="fmp-help-embed">
                <iframe src="<?php echo esc_url( $iframe ); ?>" title="Food Menu" allow="accelerometer; autoplay; clipboard-write; encrypted-media; gyroscope; picture-in-picture" allowfullscreen></iframe>
            </div>
        </div>
    </div>

    <!-- Quick links -->
    <div class="fmp-help-cards">
        <div class="fmp-help-card">
            <div class="fmp-help-card-icon" style="background: #eff6ff;">
                <svg width="22" height="22" fill="none" stroke="#2563eb" stroke-width="2" viewBox="0 0 24 24">
                    <path d="M2 3h6a4 4 0 0 1 4 4v14a3 3 0 0 0-3-3H2z"/>
                    <path d="M22 3h-6a4 4 0 0 0-4 4v14a3 3 0 0 1 3-3h7z"/>
                </svg>
            </div>
            <h3><?php esc_html_e( 'Documentation', 'tlp-food-menu' ); ?></h3>
            <p><?php esc_html_e( 'Step-by-step guides with screenshots and videos to help you get started quickly and make the most of every feature.', 'tlp-food-menu' ); ?></p>
            <a href="<?php echo esc_url( $doc ); ?>" target="_blank" class="fmp-help-card-link">
                <?php esc_html_e( 'Browse docs', 'tlp-food-menu' ); ?>
                <svg width="14" height="14" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24">
                    <path d="M5 12h14"/>
                    <path d="m12 5 7 7-7 7"/>
                </svg>
            </a>
        </div>
        <div class="fmp-help-card">
            <div class="fmp-help-card-icon" style="background: #f0fdf4;">
                <svg width="22" height="22" fill="none" stroke="#16a34a" stroke-width="2" viewBox="0 0 24 24">
                    <circle cx="12" cy="12" r="10"/>
                    <path d="M9.09 9a3 3 0 0 1 5.83 1c0 2-3 3-3 3"/>
                    <path d="M12 17h.01"/>
                </svg>
            </div>
            <h3><?php esc_html_e( 'Need Help?', 'tlp-food-menu' ); ?></h3>
            <p><?php esc_html_e( 'Stuck with something? Open a support ticket, post in our Facebook group, or start a live chat for urgent issues.', 'tlp-food-menu' ); ?></p>
            <a href="<?php echo esc_url( $contact ); ?>" target="_blank" class="fmp-help-card-link">
                <?php esc_html_e( 'Contact support', 'tlp-food-menu' ); ?>
                <svg width="14" height="14" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24">
                    <path d="M5 12h14"/>
                    <path d="m12 5 7 7-7 7"/>
                </svg>
            </a>
        </div>
        <div class="fmp-help-card">
            <div class="fmp-help-card-icon" style="background: #fefce8;">
                <svg width="22" height="22" fill="none" stroke="#ca8a04" stroke-width="2" viewBox="0 0 24 24">
                    <polygon points="12 2 15.09 8.26 22 9.27 17 14.14 18.18 21.02 12 17.77 5.82 21.02 7 14.14 2 9.27 8.91 8.26 12 2"/>
                </svg>
            </div>
            <h3><?php esc_html_e( 'Leave a Review', 'tlp-food-menu' ); ?></h3>
            <p><?php esc_html_e( 'Enjoying Food Menu? Your 5-star review helps us reach more restaurant owners and motivates us to keep improving.', 'tlp-food-menu' ); ?></p>
            <a href="<?php echo esc_url( $review ); ?>" target="_blank" class="fmp-help-card-link">
                <?php esc_html_e( 'Write a review', 'tlp-food-menu' ); ?>
                <svg width="14" height="14" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24">
                    <path d="M5 12h14"/>
                    <path d="m12 5 7 7-7 7"/>
                </svg>
            </a>
        </div>
    </div>

    <?php if ( ! $has_pro ) : ?>
        <!-- Pro Features -->
        <div class="fmp-help-features">
            <div class="fmp-help-features-header">
                <div>
                    <h2 class="fmp-help-section-title"><?php esc_html_e( 'Unlock Pro Features', 'tlp-food-menu' ); ?></h2>
                    <p class="fmp-help-section-desc" style="margin-bottom:0"><?php esc_html_e( 'Take your restaurant website to the next level with powerful tools built for real-world food businesses.', 'tlp-food-menu' ); ?></p>
                </div>
                <a href="<?php echo esc_url( $pro ); ?>" target="_blank" class="fmp-help-btn fmp-help-btn-red">
                    <svg width="16" height="16" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24">
                        <path d="M13 2L3 14h9l-1 8 10-12h-9l1-8z"/>
                    </svg>
                    <?php esc_html_e( 'Upgrade to Pro', 'tlp-food-menu' ); ?>
                </a>
            </div>
            <div class="fmp-help-features-grid">
                <?php
                $features = [
                        __( '11 layouts with Grid, Masonry, Slider & Isotope', 'tlp-food-menu' ),
                        __( 'Online ordering system via WooCommerce', 'tlp-food-menu' ),
                        __( 'Pickup & delivery with weekly schedules', 'tlp-food-menu' ),
                        __( 'Product addons (global & per-product)', 'tlp-food-menu' ),
                        __( 'Visual table reservation system', 'tlp-food-menu' ),
                        __( 'Inventory management with reports', 'tlp-food-menu' ),
                        __( 'Kitchen monitor for live order tracking', 'tlp-food-menu' ),
                        __( 'QR code table ordering for dine-in', 'tlp-food-menu' ),
                        __( 'POS printing (order, kitchen & delivery slips)', 'tlp-food-menu' ),
                        __( 'Custom order statuses with email notifications', 'tlp-food-menu' ),
                        __( 'Ajax tipping on cart & checkout', 'tlp-food-menu' ),
                        __( 'Discount rules per product & category', 'tlp-food-menu' ),
                        __( 'Special menu with time-based variations', 'tlp-food-menu' ),
                        __( 'Front-end staff dashboard', 'tlp-food-menu' ),
                        __( 'Menu item popup with full details', 'tlp-food-menu' ),
                        __( 'Food location-based filtering', 'tlp-food-menu' ),
                        __( 'AJAX pagination (load more & infinite scroll)', 'tlp-food-menu' ),
                        __( 'Full text & color customization controls', 'tlp-food-menu' ),
                ];
                foreach ( $features as $feature ) :
                    ?>
                    <div class="fmp-help-feature-item">
					<span class="fmp-help-feature-check">
						<svg width="12" height="12" fill="none" stroke="currentColor" stroke-width="2.5" viewBox="0 0 24 24"><polyline points="20 6 9 17 4 12"/></svg>
					</span>
                        <?php echo esc_html( $feature ); ?>
                    </div>
                <?php endforeach; ?>
            </div>
        </div>
    <?php endif; ?>

    <!-- Testimonials -->
    <div>
        <h2 class="fmp-help-section-title"><?php esc_html_e( 'Loved by Restaurant Owners', 'tlp-food-menu' ); ?></h2>
        <p class="fmp-help-section-desc"><?php esc_html_e( 'See what our users have to say about Food Menu.', 'tlp-food-menu' ); ?></p>
    </div>
    <div class="fmp-help-testimonials">
        <div class="fmp-help-testimonial">
            <p class="fmp-help-testimonial-quote"><?php esc_html_e( 'I love this plugin. After trying few other menu plugins I must say this is so far the best one. I bought the Pro version and I can enjoy a great variety of layouts and an infinite combination of styles and settings. Technical support is fast and reliable, and replied me during weekend hours. I feel 5 stars aren\'t enough to express how much I am satisfied with this plugin.', 'tlp-food-menu' ); ?></p>
            <div class="fmp-help-testimonial-author">
                <img src="<?php echo esc_url( TLPFoodMenu()->assets_url() ); ?>images/admin/client1.jpeg" alt="arenablue">
                <div>
                    <span class="fmp-help-testimonial-name">arenablue</span>
                    <div class="fmp-help-testimonial-stars">
                        <?php for ( $i = 0; $i < 5; $i ++ ) : ?>
                            <svg width="14" height="14" fill="currentColor" viewBox="0 0 24 24">
                                <polygon points="12 2 15.09 8.26 22 9.27 17 14.14 18.18 21.02 12 17.77 5.82 21.02 7 14.14 2 9.27 8.91 8.26 12 2"/>
                            </svg>
                        <?php endfor; ?>
                    </div>
                </div>
            </div>
        </div>
        <div class="fmp-help-testimonial">
            <p class="fmp-help-testimonial-quote"><?php esc_html_e( 'This plugin works like a charm, fully responsive without any js clash. Plugin functionality was clashing at one or two places with my theme but the author provided quick support and resolved all issues within a few minutes and updated the newer version. I am very thankful and highly obliged to the author for the help.', 'tlp-food-menu' ); ?></p>
            <div class="fmp-help-testimonial-author">
                <img src="<?php echo esc_url( TLPFoodMenu()->assets_url() ); ?>images/admin/client2.png" alt="pavitwalia">
                <div>
                    <span class="fmp-help-testimonial-name">pavitwalia</span>
                    <div class="fmp-help-testimonial-stars">
                        <?php for ( $i = 0; $i < 5; $i ++ ) : ?>
                            <svg width="14" height="14" fill="currentColor" viewBox="0 0 24 24">
                                <polygon points="12 2 15.09 8.26 22 9.27 17 14.14 18.18 21.02 12 17.77 5.82 21.02 7 14.14 2 9.27 8.91 8.26 12 2"/>
                            </svg>
                        <?php endfor; ?>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <?php if ( ! $has_pro ) : ?>
        <!-- CTA -->
        <div class="fmp-help-cta">
            <div>
                <h3><?php esc_html_e( 'Ready to grow your restaurant business?', 'tlp-food-menu' ); ?></h3>
                <p><?php esc_html_e( 'Join thousands of restaurant owners using Food Menu Pro to manage orders, menus, and more.', 'tlp-food-menu' ); ?></p>
            </div>
            <a href="<?php echo esc_url( $pro ); ?>" target="_blank" class="fmp-help-btn fmp-help-btn-white">
                <svg width="16" height="16" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24">
                    <path d="M13 2L3 14h9l-1 8 10-12h-9l1-8z"/>
                </svg>
                <?php esc_html_e( 'Upgrade to Pro', 'tlp-food-menu' ); ?>
            </a>
        </div>
    <?php endif; ?>

    <!-- Community -->
    <div class="fmp-help-cards" style="grid-template-columns: repeat(2, 1fr);">
        <div class="fmp-help-card">
            <div class="fmp-help-card-icon" style="background: #eff6ff;">
                <svg width="22" height="22" fill="none" stroke="#2563eb" stroke-width="2" viewBox="0 0 24 24">
                    <path d="M17 21v-2a4 4 0 0 0-4-4H5a4 4 0 0 0-4 4v2"/>
                    <circle cx="9" cy="7" r="4"/>
                    <path d="M23 21v-2a4 4 0 0 0-3-3.87"/>
                    <path d="M16 3.13a4 4 0 0 1 0 7.75"/>
                </svg>
            </div>
            <h3><?php esc_html_e( 'Join Our Community', 'tlp-food-menu' ); ?></h3>
            <p><?php esc_html_e( 'Connect with other Food Menu users, share tips, ask questions, and get the latest plugin news in our Facebook group.', 'tlp-food-menu' ); ?></p>
            <a href="<?php echo esc_url( $fb ); ?>" target="_blank" class="fmp-help-card-link">
                <?php esc_html_e( 'Join Facebook group', 'tlp-food-menu' ); ?>
                <svg width="14" height="14" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24">
                    <path d="M5 12h14"/>
                    <path d="m12 5 7 7-7 7"/>
                </svg>
            </a>
        </div>
        <div class="fmp-help-card">
            <div class="fmp-help-card-icon" style="background: #faf5ff;">
                <svg width="22" height="22" fill="none" stroke="#7c3aed" stroke-width="2" viewBox="0 0 24 24">
                    <path d="M21 11.5a8.38 8.38 0 0 1-.9 3.8 8.5 8.5 0 0 1-7.6 4.7 8.38 8.38 0 0 1-3.8-.9L3 21l1.9-5.7a8.38 8.38 0 0 1-.9-3.8 8.5 8.5 0 0 1 4.7-7.6 8.38 8.38 0 0 1 3.8-.9h.5a8.48 8.48 0 0 1 8 8v.5z"/>
                </svg>
            </div>
            <h3><?php esc_html_e( 'Live Chat Support', 'tlp-food-menu' ); ?></h3>
            <p><?php esc_html_e( 'For urgent issues, reach us directly via live chat on our website. Our team is ready to help you resolve any problem quickly.', 'tlp-food-menu' ); ?></p>
            <a href="<?php echo esc_url( $rt ); ?>" target="_blank" class="fmp-help-card-link">
                <?php esc_html_e( 'Start live chat', 'tlp-food-menu' ); ?>
                <svg width="14" height="14" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24">
                    <path d="M5 12h14"/>
                    <path d="m12 5 7 7-7 7"/>
                </svg>
            </a>
        </div>
    </div>

</div>
