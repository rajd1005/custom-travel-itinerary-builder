<?php
/**
 * Plugin Name: Custom Travel Itinerary & Quotation Builder
 * Version: 1.0.0
 * Text Domain: ctib
 */

if ( ! defined( 'ABSPATH' ) ) exit;

define( 'CTIB_PLUGIN_DIR', plugin_dir_path( __FILE__ ) );
define( 'CTIB_PLUGIN_URL', plugin_dir_url( __FILE__ ) );

// Includes
require_once CTIB_PLUGIN_DIR . 'includes/class-ctib-cpt.php';
require_once CTIB_PLUGIN_DIR . 'includes/class-ctib-master-data.php';
require_once CTIB_PLUGIN_DIR . 'includes/class-ctib-pricing-engine.php';
require_once CTIB_PLUGIN_DIR . 'includes/class-ctib-admin-ui.php';
require_once CTIB_PLUGIN_DIR . 'includes/class-ctib-frontend.php';
require_once CTIB_PLUGIN_DIR . 'includes/class-ctib-frontend-manager.php';
require_once CTIB_PLUGIN_DIR . 'includes/class-ctib-cloner.php';
require_once CTIB_PLUGIN_DIR . 'includes/class-ctib-versioning.php';
require_once CTIB_PLUGIN_DIR . 'includes/class-ctib-ajax.php';

// Unified Initialization
add_action( 'init', function() {
    CTIB_CPT::register();       // Registers the main "Quotes" menu
    CTIB_Master_Data::init();   // Registers Hotels, Activities, etc. under "Quotes"
    CTIB_Admin_UI::init();
    CTIB_Frontend::init();
    CTIB_Frontend_Manager::init();
    CTIB_Versioning::init();
    CTIB_Cloner::init();
});

// Admin Assets
add_action( 'admin_enqueue_scripts', function() {
    global $post_type;
    $allowed = array( 'itinerary', 'ctib_hotel', 'ctib_activity' );
    if ( in_array( $post_type, $allowed ) ) {
        wp_enqueue_style( 'ctib-admin-css', CTIB_PLUGIN_URL . 'admin/css/ctib-admin.css' );
        wp_enqueue_script( 'jquery-ui-sortable' );
        wp_enqueue_script( 'ctib-admin-js', CTIB_PLUGIN_URL . 'admin/js/ctib-admin-builder.js', array('jquery', 'jquery-ui-sortable'), '1.0', true );
        wp_localize_script( 'ctib-admin-js', 'ctib_ajax', array( 'url' => admin_url('admin-ajax.php') ) );
    }
});

// Public Template Loader
add_filter( 'template_include', function( $template ) {
    if ( is_singular( 'itinerary' ) ) {
        return CTIB_PLUGIN_DIR . 'public/partials/view-ctib-client-quote.php';
    }
    return $template;
});