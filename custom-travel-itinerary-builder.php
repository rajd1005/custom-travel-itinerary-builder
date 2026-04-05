<?php
/**
 * Plugin Name: Custom Travel Itinerary & Quotation Builder
 * Description: A modular, enterprise-grade itinerary builder with manual pricing, Master Data, status tracking, cloning, version control, and an employee frontend portal.
 * Version: 1.0.0
 * Author: Your Travel Agency
 * Text Domain: ctib
 */

// If this file is called directly, abort.
if ( ! defined( 'ABSPATH' ) ) {
    exit;
}

// Define Plugin Constants
define( 'CTIB_VERSION', '1.0.0' );
define( 'CTIB_PLUGIN_DIR', plugin_dir_path( __FILE__ ) );
define( 'CTIB_PLUGIN_URL', plugin_dir_url( __FILE__ ) );

// 1. Include Core Architecture & Database Files
require_once CTIB_PLUGIN_DIR . 'includes/class-ctib-frontend.php';
require_once CTIB_PLUGIN_DIR . 'includes/class-ctib-activator.php';
require_once CTIB_PLUGIN_DIR . 'includes/class-ctib-deactivator.php';
require_once CTIB_PLUGIN_DIR . 'includes/class-ctib-cpt.php';
require_once CTIB_PLUGIN_DIR . 'includes/class-ctib-master-data.php';

// 2. Include Logic & Engines
require_once CTIB_PLUGIN_DIR . 'includes/class-ctib-pricing-engine.php';
require_once CTIB_PLUGIN_DIR . 'includes/class-ctib-ajax.php';
require_once CTIB_PLUGIN_DIR . 'includes/class-ctib-cloner.php';
require_once CTIB_PLUGIN_DIR . 'includes/class-ctib-versioning.php';

// 3. Include Interfaces (Backend & Frontend)
require_once CTIB_PLUGIN_DIR . 'includes/class-ctib-admin-ui.php';
require_once CTIB_PLUGIN_DIR . 'includes/class-ctib-frontend-manager.php';

// Register Activation & Deactivation Hooks
register_activation_hook( __FILE__, array( 'CTIB_Activator', 'activate' ) );
register_deactivation_hook( __FILE__, array( 'CTIB_Deactivator', 'deactivate' ) );

// Initialize Systems
add_action( 'init', array( 'CTIB_CPT', 'register' ) );
add_action( 'init', array( 'CTIB_Master_Data', 'init' ) );
add_action( 'init', array( 'CTIB_Admin_UI', 'init' ) );
add_action( 'init', array( 'CTIB_Frontend_Manager', 'init' ) );
add_action( 'init', array( 'CTIB_Cloner', 'init' ) );
add_action( 'init', array( 'CTIB_Versioning', 'init' ) );
add_action( 'init', array( 'CTIB_Frontend', 'init' ) );

// Enqueue Admin Scripts & Styles
function ctib_enqueue_admin_assets( $hook ) {
    global $post_type;
    if ( 'itinerary' === $post_type ) {
        wp_enqueue_style( 'ctib-admin-css', CTIB_PLUGIN_URL . 'admin/css/ctib-admin.css', array(), CTIB_VERSION );
        wp_enqueue_script( 'jquery-ui-sortable' );
        wp_enqueue_script( 'ctib-admin-js', CTIB_PLUGIN_URL . 'admin/js/ctib-admin-builder.js', array( 'jquery', 'jquery-ui-sortable' ), CTIB_VERSION, true );
        wp_localize_script( 'ctib-admin-js', 'ctib_ajax', array( 'url' => admin_url( 'admin-ajax.php' ) ) );
    }
}
add_action( 'admin_enqueue_scripts', 'ctib_enqueue_admin_assets' );

// Enqueue Public Scripts & Load Custom Template
function ctib_enqueue_public_assets() {
    if ( is_singular( 'itinerary' ) ) {
        wp_enqueue_style( 'ctib-public-css', CTIB_PLUGIN_URL . 'public/css/ctib-public.css', array(), CTIB_VERSION );
        wp_enqueue_script( 'ctib-public-js', CTIB_PLUGIN_URL . 'public/js/ctib-public.js', array( 'jquery' ), CTIB_VERSION, true );
    }
}
add_action( 'wp_enqueue_scripts', 'ctib_enqueue_public_assets' );

// Filter to load the specific web view for the client
function ctib_load_client_template( $template ) {
    if ( is_singular( 'itinerary' ) ) {
        return CTIB_PLUGIN_DIR . 'public/partials/view-ctib-client-quote.php';
    }
    return $template;
}
add_filter( 'template_include', 'ctib_load_client_template' );