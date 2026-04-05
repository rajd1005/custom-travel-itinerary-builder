<?php
class CTIB_Frontend_Manager {

    public static function init() {
        add_shortcode( 'ctib_employee_portal', array( __CLASS__, 'render_portal' ) );
        add_action( 'admin_post_ctib_save_frontend_quote', array( __CLASS__, 'handle_form_submission' ) );
    }

    public static function render_portal() {
        if ( ! is_user_logged_in() || ! current_user_can( 'edit_posts' ) ) {
            return '<div style="padding: 20px; background: #fee; color: #c00; border: 1px solid #c00; border-radius: 5px;">Access Denied. Agent login required.</div>';
        }

        ob_start();
        self::render_creation_form();
        return ob_get_clean();
    }

    private static function render_creation_form() {
        // Fetch Master Data for dropdowns
        $destinations = get_terms( array( 'taxonomy' => 'ctib_destination', 'hide_empty' => false ) );
        $hotels       = get_posts( array( 'post_type' => 'ctib_hotel', 'posts_per_page' => -1 ) );
        $terms        = get_posts( array( 'post_type' => 'ctib_term', 'posts_per_page' => -1 ) );
        ?>
        <div class="ctib-frontend-container" style="max-width: 900px; margin: 0 auto; background: #fff; padding: 40px; border-radius: 8px; box-shadow: 0 10px 30px rgba(0,0,0,0.1); font-family: sans-serif;">
            
            <div style="display: flex; justify-content: space-between; align-items: center; border-bottom: 2px solid #0073aa; padding-bottom: 15px; margin-bottom: 20px;">
                <h2 style="margin: 0;">Create New Client Quotation</h2>
                <a href="<?php echo esc_url( remove_query_arg('action') ); ?>" style="color: #666; text-decoration: none;">&larr; Back to Dashboard</a>
            </div>
            
            <form action="<?php echo esc_url( admin_url('admin-post.php') ); ?>" method="POST" id="ctib-frontend-form">
                <input type="hidden" name="action" value="ctib_save_frontend_quote">
                <?php wp_nonce_field( 'ctib_frontend_quote_nonce', 'ctib_nonce' ); ?>

                <h3 style="color: #222;">1. Trip & Client Details</h3>
                <div style="display: grid; grid-template-columns: 1fr 1fr; gap: 20px; background: #f8f9fa; padding: 20px; border-radius: 5px; border: 1px solid #eee;">
                    <div>
                        <label style="font-weight: bold; display: block; margin-bottom: 5px;">Client Name *</label>
                        <input type="text" name="client_name" required style="width: 100%; padding: 10px; border: 1px solid #ccc; border-radius: 4px;">
                    </div>
                    <div>
                        <label style="font-weight: bold; display: block; margin-bottom: 5px;">Travel Date</label>
                        <input type="date" name="travel_date" style="width: 100%; padding: 10px; border: 1px solid #ccc; border-radius: 4px;">
                    </div>
                    <div>
                        <label style="font-weight: bold; display: block; margin-bottom: 5px;">Destination</label>
                        <select name="destination_id" style="width: 100%; padding: 10px; border: 1px solid #ccc; border-radius: 4px;">
                            <option value="">-- Select Destination --</option>
                            <?php foreach($destinations as $dest) echo '<option value="'.$dest->term_id.'">'.$dest->name.'</option>'; ?>
                        </select>
                    </div>
                    <div>
                        <label style="font-weight: bold; display: block; margin-bottom: 5px;">Primary Hotel</label>
                        <select name="hotel_id" style="width: 100%; padding: 10px; border: 1px solid #ccc; border-radius: 4px;">
                            <option value="">-- Select Hotel --</option>
                            <?php foreach($hotels as $hotel) echo '<option value="'.$hotel->ID.'">'.esc_html($hotel->post_title).'</option>'; ?>
                        </select>
                    </div>
                    <div>
                        <label style="font-weight: bold; display: block; margin-bottom: 5px;">Adults *</label>
                        <input type="number" id="fe_pax_adults" name="pax_adults" value="2" min="1" required style="width: 100%; padding: 10px; border: 1px solid #ccc; border-radius: 4px;">
                    </div>
                    <div>
                        <label style="font-weight: bold; display: block; margin-bottom: 5px;">Children</label>
                        <input type="number" id="fe_pax_children" name="pax_children" value="0" min="0" style="width: 100%; padding: 10px; border: 1px solid #ccc; border-radius: 4px;">
                    </div>
                </div>

                <h3 style="margin-top: 35px; color: #222;">2. Manual Pricing & Discounts</h3>
                <div style="display: grid; grid-template-columns: 1fr 1fr; gap: 20px;">
                    
                    <div style="background: #f9f9f9; padding: 20px; border-radius: 5px; border: 1px solid #eee;">
                        <label style="font-weight: bold; display: block; margin-bottom: 5px;">Per Head Cost (Adult) ₹ *</label>
                        <input type="number" id="fe_cost_adult" name="cost_adult" value="0" required style="width: 100%; padding: 10px; border: 1px solid #ccc; border-radius: 4px; margin-bottom: 15px;">
                        
                        <label style="font-weight: bold; display: block; margin-bottom: 5px;">Per Head Cost (Child) ₹</label>
                        <input type="number" id="fe_cost_child" name="cost_child" value="0" style="width: 100%; padding: 10px; border: 1px solid #ccc; border-radius: 4px; margin-bottom: 15px;">
                        
                        <label style="font-weight: bold; display: block; margin-bottom: 5px;">Flat Discount (₹)</label>
                        <input type="number" id="fe_discount" name="discount_flat" value="0" style="width: 100%; padding: 10px; border: 1px solid #ccc; border-radius: 4px;">
                    </div>

                    <div style="background: #e3f2fd; padding: 20px; border-radius: 5px; border: 1px solid #bbdefb;">
                        <h4 style="margin-top: 0; color: #0277bd;">Live Total Preview</h4>
                        <table style="width: 100%; font-size: 15px; border-collapse: collapse;">
                            <tr><td style="padding: 5px 0;">Base Total:</td><td style="text-align: right; font-weight: bold;" id="fe_prev_base">₹ 0.00</td></tr>
                            <tr><td style="padding: 5px 0;">Discount:</td><td style="text-align: right; color: red;" id="fe_prev_disc">- ₹ 0.00</td></tr>
                            <tr style="border-bottom: 1px solid #90caf9;"><td style="padding: 5px 0;">GST (5%):</td><td style="text-align: right;" id="fe_prev_tax">+ ₹ 0.00</td></tr>
                            <tr><td style="padding: 15px 0 0 0; font-size: 20px; font-weight: bold;">GRAND TOTAL:</td><td style="padding: 15px 0 0 0; text-align: right; font-size: 20px; font-weight: bold; color: #2e7d32;" id="fe_prev_grand">₹ 0.00</td></tr>
                        </table>
                    </div>
                </div>

                <h3 style="margin-top: 35px; color: #222;">3. Inclusions & Standard Terms</h3>
                <div style="background: #f1f1f1; padding: 20px; border-radius: 5px; border: 1px solid #ddd; max-height: 200px; overflow-y: auto;">
                    <?php 
                    if ( empty($terms) ) { echo '<p style="color: #666; margin: 0;">No Standard Terms found. Add them in the backend Master Data.</p>'; } 
                    else {
                        foreach ( $terms as $term ) : ?>
                            <label style="display: block; margin-bottom: 10px; cursor: pointer;">
                                <input type="checkbox" name="selected_terms[]" value="<?php echo esc_attr( $term->ID ); ?>">
                                <?php echo esc_html( $term->post_title ); ?>
                            </label>
                        <?php endforeach; 
                    } ?>
                </div>

                <div style="display: flex; justify-content: space-between; align-items: center; margin-top: 40px;">
                    <select name="quote_status" style="padding: 12px; border: 1px solid #ccc; border-radius: 4px; font-weight: bold;">
                        <option value="Draft">Save as Draft</option>
                        <option value="Estimate">Save as Quick Estimate</option>
                        <option value="Sent" selected>Save & Ready to Send</option>
                    </select>
                    <button type="submit" style="background: #0073aa; color: white; padding: 15px 30px; border: none; font-size: 16px; font-weight: bold; cursor: pointer; border-radius: 5px; transition: background 0.3s;">Generate Client Link &rarr;</button>
                </div>
            </form>
        </div>

        <script>
            document.addEventListener('DOMContentLoaded', function() {
                const inputs = ['fe_pax_adults', 'fe_pax_children', 'fe_cost_adult', 'fe_cost_child', 'fe_discount'];
                
                function calculateFrontendTotal() {
                    let a = parseFloat(document.getElementById('fe_pax_adults').value) || 0;
                    let c = parseFloat(document.getElementById('fe_pax_children').value) || 0;
                    let costA = parseFloat(document.getElementById('fe_cost_adult').value) || 0;
                    let costC = parseFloat(document.getElementById('fe_cost_child').value) || 0;
                    let disc = parseFloat(document.getElementById('fe_discount').value) || 0;

                    let base = (a * costA) + (c * costC);
                    let taxable = Math.max(0, base - disc);
                    let tax = taxable * 0.05; // 5% GST
                    let grand = taxable + tax;

                    document.getElementById('fe_prev_base').innerText = '₹ ' + base.toFixed(2);
                    document.getElementById('fe_prev_disc').innerText = '- ₹ ' + disc.toFixed(2);
                    document.getElementById('fe_prev_tax').innerText = '+ ₹ ' + tax.toFixed(2);
                    document.getElementById('fe_prev_grand').innerText = '₹ ' + grand.toFixed(2);
                }

                inputs.forEach(id => {
                    document.getElementById(id).addEventListener('input', calculateFrontendTotal);
                });
            });
        </script>
        <?php
    }

    public static function handle_form_submission() {
        if ( ! isset( $_POST['ctib_nonce'] ) || ! wp_verify_nonce( $_POST['ctib_nonce'], 'ctib_frontend_quote_nonce' ) ) {
            wp_die('Security check failed.');
        }

        if ( ! current_user_can('edit_posts') ) {
            wp_die('Permission denied.');
        }

        $client_name = sanitize_text_field( $_POST['client_name'] );
        $post_title  = $client_name . ' - Custom Itinerary';

        $post_id = wp_insert_post( array(
            'post_title'  => $post_title,
            'post_type'   => 'itinerary',
            'post_status' => 'publish', // Publish immediately so the Web View link works
            'post_author' => get_current_user_id()
        ));

        if ( $post_id ) {
            // Save Client & Pricing Data
            update_post_meta( $post_id, '_ctib_client_name', $client_name );
            update_post_meta( $post_id, '_ctib_pax_adults', absint( $_POST['pax_adults'] ) );
            update_post_meta( $post_id, '_ctib_pax_children', absint( $_POST['pax_children'] ) );
            update_post_meta( $post_id, '_ctib_cost_adult', floatval( $_POST['cost_adult'] ) );
            update_post_meta( $post_id, '_ctib_cost_child', floatval( $_POST['cost_child'] ) );
            update_post_meta( $post_id, '_ctib_quote_status', sanitize_text_field( $_POST['quote_status'] ) );
            
            // Save Date
            if( !empty($_POST['travel_date']) ) {
                update_post_meta( $post_id, '_ctib_travel_date', sanitize_text_field($_POST['travel_date']) );
            }

            // Auto-Generate Day 1 based on selected Hotel
            if( !empty($_POST['hotel_id']) ) {
                $days = array(
                    array(
                        'title' => 'Arrival & Check-in',
                        'desc' => 'Welcome! Check into your hotel and enjoy the rest of the day at leisure.',
                        'hotel_id' => intval($_POST['hotel_id']),
                        'meals' => array('D') // Default to Dinner on arrival
                    )
                );
                update_post_meta( $post_id, '_ctib_days', $days );
            }
            
            // Save Discounts
            $flat_discount = floatval( $_POST['discount_flat'] );
            if ( $flat_discount > 0 ) {
                update_post_meta( $post_id, '_ctib_discounts', array( array( 'type' => 'flat', 'val' => $flat_discount ) ) );
            }

            // Save Inclusions
            if ( isset( $_POST['selected_terms'] ) && is_array( $_POST['selected_terms'] ) ) {
                $terms = array_map( 'intval', $_POST['selected_terms'] );
                update_post_meta( $post_id, '_ctib_selected_terms', $terms );
            }

            // Redirect employee directly to the generated client-facing Web View
            wp_redirect( get_permalink( $post_id ) );
            exit;
        }
    }
}