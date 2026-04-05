<?php
// Ensure this is loaded within WordPress
if ( ! defined( 'ABSPATH' ) ) {
    exit;
}

get_header();

global $post;
$post_id = $post->ID;

// Fetch Client & Trip Data
$client_name = get_post_meta( $post_id, '_ctib_client_name', true );
$adults      = get_post_meta( $post_id, '_ctib_pax_adults', true ) ?: 2;
$children    = get_post_meta( $post_id, '_ctib_pax_children', true ) ?: 0;
$days        = get_post_meta( $post_id, '_ctib_days', true ) ?: array();
$terms       = get_post_meta( $post_id, '_ctib_selected_terms', true ) ?: array();
$custom_ex   = get_post_meta( $post_id, '_ctib_custom_exclusions', true );
$status      = get_post_meta( $post_id, '_ctib_quote_status', true ) ?: 'Draft';

// Fetch Pricing Data
$cost_adult = get_post_meta( $post_id, '_ctib_cost_adult', true ) ?: 0;
$cost_child = get_post_meta( $post_id, '_ctib_cost_child', true ) ?: 0;
$discounts  = get_post_meta( $post_id, '_ctib_discounts', true ) ?: array();

// Run the Math Engine
$pricing = CTIB_Pricing_Engine::calculate_advanced( $adults, $cost_adult, $children, $cost_child, $discounts );

// Communication Logic Setup
$agency_whatsapp = '919000000000'; // Default WhatsApp String
$agency_email    = get_option('admin_email');
$current_url     = get_permalink( $post_id );

$whatsapp_msg    = urlencode("Hello! I am viewing the quotation for {$client_name} ({$current_url}). I would like to proceed with the booking for ₹{$pricing['grand_total']}.");
$whatsapp_link   = "https://wa.me/{$agency_whatsapp}?text={$whatsapp_msg}";

$mailto_subject  = urlencode("Changes Requested: Itinerary for {$client_name}");
$mailto_body     = urlencode("Hello,\n\nI am reviewing my itinerary: {$current_url}\n\nI would like to request the following changes:\n\n");
?>

<div class="ctib-web-view-container">
    
    <div class="ctib-quote-header">
        <div class="ctib-badge status-<?php echo esc_attr( strtolower( str_replace( ' ', '-', $status ) ) ); ?>">Status: <?php echo esc_html( $status ); ?></div>
        <h1><?php echo esc_html( get_the_title() ); ?></h1>
        <p class="ctib-meta">Prepared for: <strong><?php echo esc_html( $client_name ); ?></strong> | Passengers: <?php echo esc_html( $adults + $children ); ?></p>
    </div>

    <?php if ( $status === 'Estimate' ) : ?>
        
        <div class="ctib-section" style="background: #f0f6fb; text-align: center; border-radius: 8px; margin-bottom: 30px;">
            <h2 style="border-bottom: none; color: #0073aa; margin-bottom: 10px;">Quick Trip Estimate</h2>
            <p style="font-size: 18px; color: #555;">Based on your requirements, here is the estimated investment for your upcoming journey. If this ballpark works for your budget, let us know and we will design a fully customized day-by-day itinerary for you.</p>
            <div style="font-size: 32px; font-weight: bold; color: #222; margin-top: 20px;">
                Estimated Total: ₹ <?php echo number_format( $pricing['grand_total'], 2 ); ?>
            </div>
            <p style="font-size: 14px; color: #777; margin-top: 10px;">* Includes taxes. Final price is subject to specific hotel selections and flight availability.</p>
        </div>

    <?php else : ?>
        
        <div class="ctib-section">
            <h2>Your Itinerary</h2>
            <div class="ctib-timeline">
                <?php 
                if ( ! empty( $days ) ) : 
                    foreach ( $days as $index => $day ) : 
                        $hotel_name = 'No Hotel Selected';
                        $hotel_img = '';
                        if ( ! empty( $day['hotel_id'] ) ) {
                            $hotel_name = get_the_title( $day['hotel_id'] );
                            $hotel_img  = get_the_post_thumbnail_url( $day['hotel_id'], 'large' );
                        }
                        $meals_str = ! empty( $day['meals'] ) ? implode( ', ', $day['meals'] ) : 'None';
                ?>
                    <div class="ctib-day-card">
                        <div class="ctib-day-number">Day <?php echo esc_html( $index + 1 ); ?></div>
                        <div class="ctib-day-content">
                            <h3><?php echo esc_html( $day['title'] ); ?></h3>
                            <p><?php echo nl2br( esc_html( $day['desc'] ) ); ?></p>
                            
                            <div class="ctib-day-meta">
                                <span><strong>Hotel:</strong> <?php echo esc_html( $hotel_name ); ?></span>
                                <span><strong>Meals Included:</strong> <?php echo esc_html( $meals_str ); ?></span>
                            </div>
                            
                            <?php if ( $hotel_img ) : ?>
                                <img src="<?php echo esc_url( $hotel_img ); ?>" class="ctib-dynamic-img" alt="Hotel Image">
                            <?php endif; ?>
                        </div>
                    </div>
                <?php 
                    endforeach; 
                else: 
                    echo '<p>Itinerary details are currently being updated by your agent.</p>';
                endif; 
                ?>
            </div>
        </div>

    <?php endif; ?>

    <div class="ctib-section">
        <h2>Inclusions & Terms</h2>
        <div class="ctib-terms-grid">
            <div class="ctib-inclusions-box">
                <ul class="ctib-check-list">
                    <?php 
                    if ( ! empty( $terms ) ) {
                        foreach ( $terms as $term_id ) {
                            echo '<li>' . esc_html( get_the_title( $term_id ) ) . '</li>';
                        }
                    } else {
                        echo '<li>Standard inclusions apply.</li>';
                    }
                    ?>
                </ul>
            </div>
            <?php if ( $custom_ex ) : ?>
                <div class="ctib-exclusions-box">
                    <strong>Special Notes / Exclusions:</strong>
                    <p><?php echo nl2br( esc_html( $custom_ex ) ); ?></p>
                </div>
            <?php endif; ?>
        </div>
    </div>

    <div class="ctib-section ctib-pricing-section">
        <h2>Investment Details</h2>
        <table class="ctib-price-table">
            <tbody>
                <tr>
                    <td>Adult Cost (<?php echo esc_html( $adults ); ?>)</td>
                    <td class="ctib-text-right">₹ <?php echo number_format( $pricing['adult_total'], 2 ); ?></td>
                </tr>
                <?php if ( $children > 0 ) : ?>
                <tr>
                    <td>Child Cost (<?php echo esc_html( $children ); ?>)</td>
                    <td class="ctib-text-right">₹ <?php echo number_format( $pricing['child_total'], 2 ); ?></td>
                </tr>
                <?php endif; ?>
                <tr class="ctib-subtotal-row">
                    <td><strong>Gross Base Total</strong></td>
                    <td class="ctib-text-right"><strong>₹ <?php echo number_format( $pricing['gross_base'], 2 ); ?></strong></td>
                </tr>
                
                <?php if ( $pricing['total_discount'] > 0 ) : ?>
                <tr class="ctib-discount-row">
                    <td>Discounts Applied</td>
                    <td class="ctib-text-right">- ₹ <?php echo number_format( $pricing['total_discount'], 2 ); ?></td>
                </tr>
                <?php endif; ?>

                <tr>
                    <td>Taxable Value</td>
                    <td class="ctib-text-right">₹ <?php echo number_format( $pricing['taxable_value'], 2 ); ?></td>
                </tr>
                <tr>
                    <td>GST (5%)</td>
                    <td class="ctib-text-right">+ ₹ <?php echo number_format( $pricing['total_tax'], 2 ); ?></td>
                </tr>
            </tbody>
            <tfoot>
                <tr>
                    <th>GRAND TOTAL</th>
                    <th class="ctib-text-right ctib-grand-total">₹ <?php echo number_format( $pricing['grand_total'], 2 ); ?></th>
                </tr>
            </tfoot>
        </table>
    </div>

    <div class="ctib-action-bar no-print">
        <a href="<?php echo esc_url( $whatsapp_link ); ?>" target="_blank" class="ctib-btn ctib-btn-whatsapp">
            <span class="dashicons dashicons-whatsapp"></span> Accept & Chat on WhatsApp
        </a>
        <a href="mailto:<?php echo esc_attr( $agency_email ); ?>?subject=<?php echo $mailto_subject; ?>&body=<?php echo $mailto_body; ?>" class="ctib-btn ctib-btn-email">
            <span class="dashicons dashicons-email"></span> Request Changes
        </a>
        <button id="ctib-download-pdf" class="ctib-btn ctib-btn-pdf">
            <span class="dashicons dashicons-download"></span> Download PDF
        </button>
    </div>

</div>

<?php get_footer(); ?>