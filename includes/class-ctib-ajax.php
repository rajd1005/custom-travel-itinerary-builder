<?php
class CTIB_Ajax {
    public static function init() {
        add_action( 'wp_ajax_ctib_get_master_data', array( __CLASS__, 'get_master_data' ) );
        add_action( 'wp_ajax_ctib_send_email', array( __CLASS__, 'send_email' ) );
    }

    /**
     * Fetches Hotels or Activities based on Destination Filter
     */
    public static function get_master_data() {
        check_ajax_referer( 'ctib_admin_nonce', 'nonce' );

        $post_type = sanitize_text_field( $_POST['master_type'] ); // ctib_hotel or ctib_activity
        $dest_id   = intval( $_POST['dest_id'] );

        $args = array(
            'post_type'      => $post_type,
            'posts_per_page' => -1,
            'tax_query'      => array(
                array(
                    'taxonomy' => 'ctib_destination',
                    'field'    => 'term_id',
                    'terms'    => $dest_id,
                ),
            ),
        );

        $query = new WP_Query( $args );
        $results = array();

        if ( $query->have_posts() ) {
            while ( $query->have_posts() ) {
                $query->the_post();
                $results[] = array(
                    'id'    => get_the_ID(),
                    'title' => get_the_title(),
                    'image' => get_the_post_thumbnail_url( get_the_ID(), 'thumbnail' ) ?: '',
                    'meal'  => get_post_meta( get_the_ID(), '_ctib_hotel_meal_plan', true ) ?: 'CP'
                );
            }
        }
        wp_reset_postdata();
        wp_send_json_success( $results );
    }

    public static function send_email() {
        // ... (Existing email logic)
    }
}
CTIB_Ajax::init();