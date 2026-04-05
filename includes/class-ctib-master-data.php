<?php
class CTIB_Master_Data {

    public static function init() {
        // Hook into WordPress init
        add_action( 'init', array( __CLASS__, 'register_taxonomies' ) );
        add_action( 'init', array( __CLASS__, 'register_master_cpts' ) );
        add_action( 'add_meta_boxes', array( __CLASS__, 'add_master_meta_boxes' ) );
        add_action( 'save_post', array( __CLASS__, 'save_master_meta_data' ) );
    }

    // 1. Register Destinations as a Taxonomy (Category Style)
    public static function register_taxonomies() {
        $labels = array(
            'name'              => 'Destinations',
            'singular_name'     => 'Destination',
            'search_items'      => 'Search Destinations',
            'all_items'         => 'All Destinations',
            'parent_item'       => 'Parent Destination (Country/State)',
            'parent_item_colon' => 'Parent Destination:',
            'edit_item'         => 'Edit Destination',
            'update_item'       => 'Update Destination',
            'add_new_item'      => 'Add New Destination',
            'new_item_name'     => 'New Destination Name',
            'menu_name'         => 'Destinations',
        );

        $args = array(
            'hierarchical'      => true, // Allows Countries -> Cities
            'labels'            => $labels,
            'show_ui'           => true,
            'show_admin_column' => true,
            'query_var'         => true,
            'rewrite'           => array( 'slug' => 'destination' ),
        );

        // We will attach this taxonomy to Hotels and Activities shortly
        register_taxonomy( 'ctib_destination', array( 'ctib_hotel', 'ctib_activity', 'itinerary' ), $args );
    }

    // 2. Register Custom Post Types for Hotels, Activities, and Terms
    public static function register_master_cpts() {
        
        // A. Accommodations (Hotels)
        register_post_type( 'ctib_hotel', array(
            'labels'      => array( 'name' => 'Hotels', 'singular_name' => 'Hotel', 'add_new_item' => 'Add New Hotel' ),
            'public'      => false, // Internal data only
            'show_ui'     => true,
            'show_in_menu'=> 'edit.php?post_type=itinerary', // Put it under the main Itinerary menu
            'supports'    => array( 'title', 'thumbnail' ), // Thumbnail for the Dynamic Image Library later
        ));

        // B. Activities & Sightseeing
        register_post_type( 'ctib_activity', array(
            'labels'      => array( 'name' => 'Activities', 'singular_name' => 'Activity', 'add_new_item' => 'Add New Activity' ),
            'public'      => false,
            'show_ui'     => true,
            'show_in_menu'=> 'edit.php?post_type=itinerary',
            'supports'    => array( 'title', 'editor', 'thumbnail' ),
        ));

        // C. Standard Terms (Inclusions, Exclusions, Policies)
        register_post_type( 'ctib_term', array(
            'labels'      => array( 'name' => 'Inclusions & Terms', 'singular_name' => 'Term', 'add_new_item' => 'Add New Term' ),
            'public'      => false,
            'show_ui'     => true,
            'show_in_menu'=> 'edit.php?post_type=itinerary',
            'supports'    => array( 'title', 'editor' ), // Editor for the bullet points
        ));
    }

    // 3. Add Custom Data Fields to Hotels
    public static function add_master_meta_boxes() {
        add_meta_box( 'ctib_hotel_details', 'Hotel Details', array( __CLASS__, 'render_hotel_meta_box' ), 'ctib_hotel', 'normal', 'high' );
    }

    public static function render_hotel_meta_box( $post ) {
        wp_nonce_field( 'ctib_hotel_save', 'ctib_hotel_nonce' );
        $star_rating = get_post_meta( $post->ID, '_ctib_hotel_stars', true );
        $meal_plan = get_post_meta( $post->ID, '_ctib_hotel_meal_plan', true );
        ?>
        <div style="display: grid; grid-template-columns: 1fr 1fr; gap: 15px;">
            <div>
                <label><strong>Star Rating:</strong></label><br>
                <select name="ctib_hotel_stars" style="width: 100%; margin-top: 5px;">
                    <option value="3" <?php selected($star_rating, '3'); ?>>3 Star</option>
                    <option value="4" <?php selected($star_rating, '4'); ?>>4 Star</option>
                    <option value="5" <?php selected($star_rating, '5'); ?>>5 Star</option>
                    <option value="Boutique" <?php selected($star_rating, 'Boutique'); ?>>Boutique / Villa</option>
                </select>
            </div>
            <div>
                <label><strong>Default Meal Plan:</strong></label><br>
                <select name="ctib_hotel_meal_plan" style="width: 100%; margin-top: 5px;">
                    <option value="CP" <?php selected($meal_plan, 'CP'); ?>>CP (Breakfast Only)</option>
                    <option value="MAP" <?php selected($meal_plan, 'MAP'); ?>>MAP (Breakfast + Dinner)</option>
                    <option value="AP" <?php selected($meal_plan, 'AP'); ?>>AP (All Meals)</option>
                    <option value="EP" <?php selected($meal_plan, 'EP'); ?>>EP (Room Only)</option>
                </select>
            </div>
        </div>
        <?php
    }

    // 4. Save Hotel Meta Data
    public static function save_master_meta_data( $post_id ) {
        if ( ! isset( $_POST['ctib_hotel_nonce'] ) || ! wp_verify_nonce( $_POST['ctib_hotel_nonce'], 'ctib_hotel_save' ) ) {
            return;
        }
        if ( defined( 'DOING_AUTOSAVE' ) && DOING_AUTOSAVE ) return;
        if ( ! current_user_can( 'edit_post', $post_id ) ) return;

        if ( isset( $_POST['ctib_hotel_stars'] ) ) {
            update_post_meta( $post_id, '_ctib_hotel_stars', sanitize_text_field( $_POST['ctib_hotel_stars'] ) );
        }
        if ( isset( $_POST['ctib_hotel_meal_plan'] ) ) {
            update_post_meta( $post_id, '_ctib_hotel_meal_plan', sanitize_text_field( $_POST['ctib_hotel_meal_plan'] ) );
        }
    }
}