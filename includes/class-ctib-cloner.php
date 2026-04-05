<?php
class CTIB_Cloner {

    public static function init() {
        add_filter( 'post_row_actions', array( __CLASS__, 'add_clone_link' ), 10, 2 );
        add_action( 'admin_action_ctib_clone_itinerary', array( __CLASS__, 'handle_clone_request' ) );
    }

    // 1. Add "Clone" link to the WordPress Admin List
    public static function add_clone_link( $actions, $post ) {
        if ( $post->post_type !== 'itinerary' || !current_user_can('edit_posts') ) {
            return $actions;
        }

        $url = wp_nonce_url( 
            admin_url( 'admin-action.php?action=ctib_clone_itinerary&post=' . $post->ID ), 
            'ctib_clone_nonce' 
        );

        $actions['clone'] = '<a href="' . $url . '" title="Clone this as a new draft" rel="permalink">Clone/Copy</a>';
        return $actions;
    }

    // 2. Handle the Cloning Logic
    public static function handle_clone_request() {
        if ( ! isset( $_GET['post'] ) || ! wp_verify_nonce( $_GET['_wpnonce'], 'ctib_clone_nonce' ) ) {
            wp_die('Security check failed.');
        }

        $post_id = (int) $_GET['post'];
        $post = get_post( $post_id );

        if ( ! $post ) wp_die('Post not found.');

        $new_post_args = array(
            'post_title'  => $post->post_title . ' (Copy)',
            'post_status' => 'draft',
            'post_type'   => 'itinerary',
            'post_author' => get_current_user_id(),
        );

        $new_post_id = wp_insert_post( $new_post_args );

        if ( $new_post_id ) {
            // Copy all Meta Data (The "Secret Sauce" for cloning)
            $post_meta = get_post_custom( $post_id );
            foreach ( $post_meta as $key => $values ) {
                foreach ( $values as $value ) {
                    add_post_meta( $new_post_id, $key, maybe_unserialize($value) );
                }
            }
            // Ensure status is reset to Draft
            update_post_meta( $new_post_id, '_ctib_quote_status', 'Draft' );

            wp_redirect( admin_url( 'post.php?post=' . $new_post_id . '&action=edit' ) );
            exit;
        }
    }
}
CTIB_Cloner::init();