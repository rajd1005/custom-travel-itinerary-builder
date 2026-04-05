<?php
class CTIB_CPT {
    public static function register() {
        register_post_type( 'itinerary', array(
            'labels'      => array( 'name' => 'Quotes', 'singular_name' => 'Quote', 'add_new_item' => 'Build New Itinerary' ),
            'public'      => true,
            'has_archive' => false,
            'rewrite'     => array( 'slug' => 'quote' ),
            'menu_icon'   => 'dashicons-airplane',
            'supports'    => array( 'title' )
        ));
    }
}