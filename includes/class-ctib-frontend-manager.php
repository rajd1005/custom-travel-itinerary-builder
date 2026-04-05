<?php
class CTIB_Frontend_Manager {

    public static function init() {
        add_shortcode( 'ctib_employee_portal', array( __CLASS__, 'render_portal' ) );
        add_action( 'admin_post_ctib_save_frontend_quote', array( __CLASS__, 'handle_form_submission' ) );
    }

    public static function render_portal() {
        if ( ! is_user_logged_in() || ! current_user_can( 'edit_posts' ) ) {
            return '<div style="padding: 20px; background: #fee; color: #c00; border: 1px solid #c00; border-radius: 5px;">Access Denied. You must be logged in as an authorized travel agent to view this portal.</div>';
        }

        ob_start();
        self::render_creation_form();
        return ob_get_clean();
    }

    private static function render_creation_form() {
        ?>
        <div class="ctib-frontend-container" style="max-width: 900px; margin: 0 auto; background: #fff; padding: 40px; border-radius: 8px; box-shadow: 0 10px 30px rgba(0,0,0,0.1); font-family: sans-serif;">
            <h2 style="border-bottom: 2px solid #0073aa; padding-bottom: 15px; margin-top: 0;">Create New Client Quotation</h2>
            
            <form action="<?php echo esc_url( admin_url('admin-post.php') ); ?>" method="POST">
                <input type="hidden" name="action" value="ctib_save_frontend_quote">
                <?php wp_nonce_field( 'ctib_frontend_quote_nonce', 'ctib_nonce' ); ?>

                <h3 style="margin-top: 25px; color: #222;">1. Client Details</h3>
                <div style="display: grid; grid-template-columns: 1fr 1fr; gap: 20px;">
                    <div>
                        <label style="font-weight: bold; display: block; margin-bottom: 5px;">Client Name *</label>
                        <input type="text" name="client_name" required style="width: 100%; padding: 10px; border: 1px solid #ccc; border-radius: 4px;">
                    </div>
                    <div>
                        <label style="font-weight: bold; display: block; margin-bottom: 5px;">Number of Adults *</label>
                        <input type="number" name="pax_adults" value="2" min="1" required style="width: 100%; padding: 10px; border: 1px solid #ccc; border-radius: 4px;">
                    </div>
                    <div>
                        <label style="font-weight: bold; display: block; margin-bottom: 5px;">Number of Children</label>
                        <input type="number" name="pax_children" value="0" min="0" style="width: 100%; padding: 10px; border: 1px solid #ccc; border-radius: 4px;">
                    </div>
                    <div>
                        <label style="font-weight: bold; display: block; margin-bottom: 5px;">Initial Status</label>
                        <select name="quote_status" style="width: 100%; padding: 10px; border: 1px solid #ccc; border-radius: 4px;">
                            <option value="Draft">Draft</option>
                            <option value="Estimate">Mini-Quote / Estimate</option>
                            <option value="Sent">Ready to Send</option>
                        </select>
                    </div>
                </div>

                <h3 style="margin-top: 35px; color: #222;">2. Pricing (Manual Setup)</h3>
                <div style="display: grid; grid-template-columns: 1fr 1fr; gap: 20px; background: #f9f9f9; padding: 20px; border-radius: 5px;">
                    <div>
                        <label style="font-weight: bold; display: block; margin-bottom: 5px;">Per Head Cost (Adult) ₹ *</label>
                        <input type="number" name="cost_adult" required style="width: 100%; padding: 10px; border: 1px solid #ccc; border-radius: 4px;">
                    </div>
                    <div>
                        <label style="font-weight: bold; display: block; margin-bottom: 5px;">Per Head Cost (Child) ₹</label>
                        <input type="number" name="cost_child" value="0" style="width: 100%; padding: 10px; border: 1px solid #ccc; border-radius: 4px;">
                    </div>
                    <div>
                        <label style="font-weight: bold; display: block; margin-bottom: 5px;">Flat Discount (₹)</label>
                        <input type="number" name="discount_flat" value="0" style="width: 100%; padding: 10px; border: 1px solid #ccc; border-radius: 4px;">
                    </div>
                </div>

                <h3 style="margin-top: 35px; color: #222;">3. Quick Inclusions</h3>
                <div style="background: #f1f1f1; padding: 20px; border-radius: 5px; border: 1px solid #ddd;">
                    <?php 
                    $terms = get_posts( array('post_type' => 'ctib_term', 'posts_per_page' => -1) );
                    if ( empty($terms) ) {
                        echo '<p style="color: #666; margin: 0;">No Standard Terms found. Please add them in the Master Data backend.</p>';
                    } else {
                        foreach ( $terms as $term ) : ?>
                            <label style="display: block; margin-bottom: 10px; cursor: pointer;">
                                <input type="checkbox" name="selected_terms[]" value="<?php echo esc_attr( $term->ID ); ?>">
                                <?php echo esc_html( $term->post_title ); ?>
                            </label>
                        <?php endforeach; 
                    } ?>
                </div>

                <div style="margin-top: 40px; text-align: right;">
                    <button type="submit" style="background: #0073aa; color: white; padding: 15px 30px; border: none; font-size: 16px; font-weight: bold; cursor: pointer; border-radius: 5px; transition: background 0.3s;">Create & Generate Client Link</button>
                </div>
            </form>
        </div>
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

        $post_data = array(
            'post_title'  => $post_title,
            'post_type'   => 'itinerary',
            'post_status' => 'publish', // Publish immediately so the Web View link works
            'post_author' => get_current_user_id()
        );

        $post_id = wp_insert_post( $post_data );

        if ( $post_id ) {
            // Save Core Data
            update_post_meta( $post_id, '_ctib_client_name', $client_name );
            update_post_meta( $post_id, '_ctib_pax_adults', absint( $_POST['pax_adults'] ) );
            update_post_meta( $post_id, '_ctib_pax_children', absint( $_POST['pax_children'] ) );
            update_post_meta( $post_id, '_ctib_cost_adult', floatval( $_POST['cost_adult'] ) );
            update_post_meta( $post_id, '_ctib_cost_child', floatval( $_POST['cost_child'] ) );
            update_post_meta( $post_id, '_ctib_quote_status', sanitize_text_field( $_POST['quote_status'] ) );
            
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