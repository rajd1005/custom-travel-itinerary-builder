<?php
class CTIB_Admin_UI {

    public static function init() {
        add_action( 'add_meta_boxes', array( __CLASS__, 'register_meta_boxes' ) );
        add_action( 'save_post_itinerary', array( __CLASS__, 'save_itinerary_data' ) );
    }

    public static function register_meta_boxes() {
        // Step 1: Basic Details
        add_meta_box( 'ctib_step1', '1. Client & Trip Details', function($post) { 
            include CTIB_PLUGIN_DIR . 'admin/partials/view-ctib-wizard-step1.php'; 
        }, 'itinerary', 'normal', 'high' );

        // Step 2: Day Builder
        add_meta_box( 'ctib_step2', '2. Day-Wise Builder', function($post) { 
            include CTIB_PLUGIN_DIR . 'admin/partials/view-ctib-wizard-step2.php'; 
        }, 'itinerary', 'normal', 'default' );

        // Step 3: Pricing
        add_meta_box( 'ctib_step3', '3. Pricing & Taxes', function($post) { 
            include CTIB_PLUGIN_DIR . 'admin/partials/view-ctib-wizard-step3.php'; 
        }, 'itinerary', 'normal', 'default' );

        // Step 4: Terms & Exclusions
        add_meta_box( 'ctib_step4', '4. Inclusions & Exclusions', function($post) { 
            include CTIB_PLUGIN_DIR . 'admin/partials/view-ctib-wizard-step4.php'; 
        }, 'itinerary', 'normal', 'default' );

        // Sidebar: Status Tracking
        add_meta_box( 'ctib_status_box', 'Quotation Status', array( __CLASS__, 'render_status_sidebar' ), 'itinerary', 'side', 'high' );
    }

    public static function render_status_sidebar( $post ) {
        wp_nonce_field( 'ctib_save_itinerary', 'ctib_itinerary_nonce' );
        $status = get_post_meta( $post->ID, '_ctib_quote_status', true ) ?: 'Draft';
        $options = array( 'Draft', 'Estimate', 'Sent', 'Viewed by Client', 'Approved', 'Rejected' );
        ?>
        <select name="ctib_quote_status" style="width: 100%; padding: 5px;">
            <?php foreach ( $options as $opt ) : ?>
                <option value="<?php echo esc_attr( $opt ); ?>" <?php selected( $status, $opt ); ?>><?php echo esc_html( $opt ); ?></option>
            <?php endforeach; ?>
        </select>
        <p class="description" style="margin-top: 10px;">Update this to track the lead's lifecycle.</p>
        <?php
    }

    public static function save_itinerary_data( $post_id ) {
        // Security Checks
        if ( ! isset( $_POST['ctib_itinerary_nonce'] ) || ! wp_verify_nonce( $_POST['ctib_itinerary_nonce'], 'ctib_save_itinerary' ) ) {
            return;
        }
        if ( defined( 'DOING_AUTOSAVE' ) && DOING_AUTOSAVE ) return;
        if ( ! current_user_can( 'edit_post', $post_id ) ) return;

        // 1. Save Basic Details & Status
        $text_fields = array( 'ctib_client_name', 'ctib_quote_status' );
        foreach ( $text_fields as $field ) {
            if ( isset( $_POST[$field] ) ) update_post_meta( $post_id, '_' . $field, sanitize_text_field( $_POST[$field] ) );
        }

        // 2. Save Numerical Pricing Data
        $num_fields = array( 'ctib_pax_adults', 'ctib_pax_children', 'ctib_cost_adult', 'ctib_cost_child' );
        foreach ( $num_fields as $field ) {
            if ( isset( $_POST[$field] ) ) update_post_meta( $post_id, '_' . $field, floatval( $_POST[$field] ) );
        }

        // 3. Save Discounts (Reformatting to array for the math engine)
        if ( isset( $_POST['ctib_discount_flat'] ) || isset( $_POST['ctib_discount_percent'] ) ) {
            $discounts = array();
            $flat = floatval( $_POST['ctib_discount_flat'] ?? 0 );
            $perc = floatval( $_POST['ctib_discount_percent'] ?? 0 );
            if ( $flat > 0 ) $discounts[] = array( 'type' => 'flat', 'val' => $flat );
            if ( $perc > 0 ) $discounts[] = array( 'type' => 'percent', 'val' => $perc );
            update_post_meta( $post_id, '_ctib_discounts', $discounts );
        }

        // 4. Save Inclusions & Exclusions
        if ( isset( $_POST['ctib_selected_terms'] ) && is_array( $_POST['ctib_selected_terms'] ) ) {
            $terms = array_map( 'intval', $_POST['ctib_selected_terms'] );
            update_post_meta( $post_id, '_ctib_selected_terms', $terms );
        } else {
            delete_post_meta( $post_id, '_ctib_selected_terms' );
        }

        if ( isset( $_POST['ctib_custom_exclusions'] ) ) {
            update_post_meta( $post_id, '_ctib_custom_exclusions', sanitize_textarea_field( $_POST['ctib_custom_exclusions'] ) );
        }

        // 5. Save Day-Wise Data
        if ( isset( $_POST['ctib_day_title'] ) ) {
            $days = array();
            for ( $i = 0; $i < count( $_POST['ctib_day_title'] ); $i++ ) {
                $days[] = array(
                    'title'    => sanitize_text_field( $_POST['ctib_day_title'][$i] ),
                    'desc'     => sanitize_textarea_field( $_POST['ctib_day_desc'][$i] ),
                    'hotel_id' => intval( $_POST['ctib_day_hotel_id'][$i] ?? 0 ),
                    'meals'    => isset( $_POST['ctib_meals'][$i] ) ? array_map('sanitize_text_field', $_POST['ctib_meals'][$i]) : array()
                );
            }
            update_post_meta( $post_id, '_ctib_days', $days );
        } else {
            delete_post_meta( $post_id, '_ctib_days' );
        }
    }
}