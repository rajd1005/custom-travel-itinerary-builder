<?php
class CTIB_Versioning {

    public static function init() {
        add_action( 'add_meta_boxes', array( __CLASS__, 'add_version_meta_box' ), 10, 2 );
        add_action( 'admin_action_ctib_create_version', array( __CLASS__, 'handle_new_version' ) );
    }

    // 1. Add Version History Box to the Sidebar
    public static function add_version_meta_box() {
        add_meta_box( 
            'ctib_version_box', 
            'Version Control', 
            array( __CLASS__, 'render_version_box' ), 
            'itinerary', 
            'side', 
            'low' 
        );
    }

    public static function render_version_box( $post ) {
        $parent_id = get_post_meta( $post->ID, '_ctib_parent_quote', true ) ?: $post->ID;
        $current_version = get_post_meta( $post->ID, '_ctib_version_number', true ) ?: 1;
        
        // Find all versions belonging to this parent quote
        $args = array(
            'post_type'      => 'itinerary',
            'posts_per_page' => -1,
            'post_status'    => 'any',
            'meta_query'     => array(
                'relation' => 'OR',
                array(
                    'key'   => '_ctib_parent_quote',
                    'value' => $parent_id,
                ),
                array(
                    'key'     => '_ctib_parent_quote',
                    'compare' => 'NOT EXISTS' // The original V1 won't have a parent ID initially
                )
            )
        );
        $versions = new WP_Query( $args );

        echo '<p><strong>Current:</strong> Version ' . esc_html( $current_version ) . '</p>';
        echo '<ul style="margin-left: 15px; list-style-type: disc;">';
        
        if ( $versions->have_posts() ) {
            while ( $versions->have_posts() ) {
                $versions->the_post();
                $v_num = get_post_meta( get_the_ID(), '_ctib_version_number', true ) ?: 1;
                $is_current = ( get_the_ID() == $post->ID ) ? ' <strong>(Active)</strong>' : '';
                echo '<li><a href="' . admin_url( 'post.php?post=' . get_the_ID() . '&action=edit' ) . '">Version ' . esc_html( $v_num ) . '</a>' . $is_current . '</li>';
            }
            wp_reset_postdata();
        }
        echo '</ul>';

        // Action Button to Create Next Version
        $url = wp_nonce_url( admin_url( 'admin-action.php?action=ctib_create_version&post=' . $post->ID ), 'ctib_version_nonce' );
        echo '<hr><a href="' . esc_url( $url ) . '" class="button button-secondary" style="width:100%; text-align:center;">Create New Version</a>';
        echo '<p class="description" style="margin-top:10px;">Creates a duplicate for negotiation without losing the original data.</p>';
    }

    // 2. Logic to Duplicate and Increment Version
    public static function handle_new_version() {
        if ( ! isset( $_GET['post'] ) || ! wp_verify_nonce( $_GET['_wpnonce'], 'ctib_version_nonce' ) ) {
            wp_die('Security check failed.');
        }

        $post_id = (int) $_GET['post'];
        $post = get_post( $post_id );
        if ( ! $post ) wp_die('Post not found.');

        $parent_id = get_post_meta( $post_id, '_ctib_parent_quote', true ) ?: $post_id;
        $current_version = get_post_meta( $post_id, '_ctib_version_number', true ) ?: 1;
        $new_version = $current_version + 1;

        // Clean up title (Remove old V2, V3 tags if they exist)
        $base_title = preg_replace('/ - V[0-9]+$/', '', $post->post_title);
        $new_title = $base_title . ' - V' . $new_version;

        $new_post_args = array(
            'post_title'  => $new_title,
            'post_status' => 'publish',
            'post_type'   => 'itinerary',
            'post_author' => get_current_user_id(),
        );

        $new_post_id = wp_insert_post( $new_post_args );

        if ( $new_post_id ) {
            // Copy Meta Data
            $post_meta = get_post_custom( $post_id );
            foreach ( $post_meta as $key => $values ) {
                foreach ( $values as $value ) {
                    add_post_meta( $new_post_id, $key, maybe_unserialize( $value ) );
                }
            }
            
            // Update Version Tracking Meta
            update_post_meta( $new_post_id, '_ctib_parent_quote', $parent_id );
            update_post_meta( $new_post_id, '_ctib_version_number', $new_version );
            update_post_meta( $new_post_id, '_ctib_quote_status', 'Draft' ); // Reset status for the new version
            
            // Set parent meta on the original post if it didn't have one
            if ( ! get_post_meta( $post_id, '_ctib_parent_quote', true ) ) {
                update_post_meta( $post_id, '_ctib_parent_quote', $parent_id );
                update_post_meta( $post_id, '_ctib_version_number', 1 );
            }

            wp_redirect( admin_url( 'post.php?post=' . $new_post_id . '&action=edit' ) );
            exit;
        }
    }
}