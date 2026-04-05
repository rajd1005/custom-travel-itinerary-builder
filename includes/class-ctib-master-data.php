<?php
class CTIB_Master_Data {

    public static function init() {
        add_action( 'init', array( __CLASS__, 'register_taxonomies' ) );
        add_action( 'init', array( __CLASS__, 'register_master_cpts' ) );
        add_action( 'add_meta_boxes', array( __CLASS__, 'add_master_meta_boxes' ) );
        add_action( 'save_post', array( __CLASS__, 'save_master_meta_data' ) );
    }

    public static function register_taxonomies() {
        $labels = array(
            'name'              => 'Destinations',
            'singular_name'     => 'Destination',
            'menu_name'         => 'Destinations',
        );

        $args = array(
            'hierarchical'      => true,
            'labels'            => $labels,
            'show_ui'           => true,
            'show_admin_column' => true,
            'query_var'         => true,
            'rewrite'           => array( 'slug' => 'destination' ),
        );

        // This ensures Destinations appears under the Quotes menu
        register_taxonomy( 'ctib_destination', array( 'itinerary', 'ctib_hotel', 'ctib_activity' ), $args );
    }

    public static function register_master_cpts() {
        $parent_slug = 'edit.php?post_type=itinerary'; // Slug for the Quotes menu

        // Hotels
        register_post_type( 'ctib_hotel', array(
            'labels'      => array( 'name' => 'Hotels', 'singular_name' => 'Hotel' ),
            'public'      => false,
            'show_ui'     => true,
            'show_in_menu'=> $parent_slug,
            'supports'    => array( 'title', 'thumbnail' ),
        ));

        // Activities
        register_post_type( 'ctib_activity', array(
            'labels'      => array( 'name' => 'Activities', 'singular_name' => 'Activity' ),
            'public'      => false,
            'show_ui'     => true,
            'show_in_menu'=> $parent_slug,
            'supports'    => array( 'title', 'editor', 'thumbnail' ),
        ));

        // Inclusions & Terms
        register_post_type( 'ctib_term', array(
            'labels'      => array( 'name' => 'Inclusions & Terms', 'singular_name' => 'Term' ),
            'public'      => false,
            'show_ui'     => true,
            'show_in_menu'=> $parent_slug,
            'supports'    => array( 'title', 'editor' ),
        ));
    }

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
                <label>Star Rating:</label><br>
                <select name="ctib_hotel_stars" style="width: 100%;">
                    <option value="3" <?php selected($star_rating, '3'); ?>>3 Star</option>
                    <option value="4" <?php selected($star_rating, '4'); ?>>4 Star</option>
                    <option value="5" <?php selected($star_rating, '5'); ?>>5 Star</option>
                </select>
            </div>
            <div>
                <label>Meal Plan:</label><br>
                <select name="ctib_hotel_meal_plan" style="width: 100%;">
                    <option value="CP" <?php selected($meal_plan, 'CP'); ?>>CP</option>
                    <option value="MAP" <?php selected($meal_plan, 'MAP'); ?>>MAP</option>
                    <option value="AP" <?php selected($meal_plan, 'AP'); ?>>AP</option>
                </select>
            </div>
        </div>
        <?php
    }

    public static function save_master_meta_data( $post_id ) {
        if ( !isset($_POST['ctib_hotel_nonce']) || !wp_verify_nonce($_POST['ctib_hotel_nonce'], 'ctib_hotel_save') ) return;
        if ( defined('DOING_AUTOSAVE') && DOING_AUTOSAVE ) return;
        update_post_meta( $post_id, '_ctib_hotel_stars', sanitize_text_field($_POST['ctib_hotel_stars']) );
        update_post_meta( $post_id, '_ctib_hotel_meal_plan', sanitize_text_field($_POST['ctib_hotel_meal_plan']) );
    }
}