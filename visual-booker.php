<?php
/**
 * Plugin Name: Visual Booker
 * Plugin URI:  https://github.com/AbhishekDas/visual-booker
 * Description: Interactive seat/spot booking on custom images or maps. Users upload a floor plan, map, or layout image, place bookable spots on it via a drag-and-drop admin builder, and visitors can select & book spots on the front end.
 * Version:     1.0.0
 * Author:      Abhishek Das
 * Author URI:  https://github.com/AbhishekDas
 * License:     GPL-2.0+
 * Text Domain: visual-booker
 */

if ( ! defined( 'ABSPATH' ) ) {
    exit;
}

/* ------------------------------------------------------------------ */
/*  Constants                                                          */
/* ------------------------------------------------------------------ */
define( 'VB_VERSION', '1.0.0' );
define( 'VB_PLUGIN_DIR', plugin_dir_path( __FILE__ ) );
define( 'VB_PLUGIN_URL', plugin_dir_url( __FILE__ ) );
define( 'VB_PLUGIN_BASENAME', plugin_basename( __FILE__ ) );

/* ------------------------------------------------------------------ */
/*  Autoload includes                                                  */
/* ------------------------------------------------------------------ */
require_once VB_PLUGIN_DIR . 'includes/class-vb-db.php';
require_once VB_PLUGIN_DIR . 'includes/class-vb-post-type.php';
require_once VB_PLUGIN_DIR . 'includes/class-vb-rest-api.php';
require_once VB_PLUGIN_DIR . 'includes/class-vb-shortcode.php';

/* ------------------------------------------------------------------ */
/*  Activation / Deactivation                                          */
/* ------------------------------------------------------------------ */
register_activation_hook( __FILE__, array( 'VB_DB', 'create_tables' ) );

/* ------------------------------------------------------------------ */
/*  Init                                                               */
/* ------------------------------------------------------------------ */
add_action( 'init', array( 'VB_Post_Type', 'register' ) );
add_action( 'rest_api_init', array( 'VB_REST_API', 'register_routes' ) );
add_action( 'admin_enqueue_scripts', 'vb_admin_assets' );
add_action( 'wp_enqueue_scripts', 'vb_public_assets' );

/* ------------------------------------------------------------------ */
/*  Admin assets                                                       */
/* ------------------------------------------------------------------ */
function vb_admin_assets( $hook ) {
    $screen = get_current_screen();
    if ( ! $screen || $screen->post_type !== 'vb_layout' ) {
        return;
    }

    wp_enqueue_media(); // WP media uploader

    wp_enqueue_style(
        'vb-admin-css',
        VB_PLUGIN_URL . 'admin/css/admin.css',
        array(),
        VB_VERSION
    );

    wp_enqueue_script(
        'vb-admin-js',
        VB_PLUGIN_URL . 'admin/js/admin.js',
        array( 'jquery', 'jquery-ui-draggable', 'jquery-ui-droppable', 'wp-util' ),
        VB_VERSION,
        true
    );

    wp_localize_script( 'vb-admin-js', 'vbAdmin', array(
        'restUrl'  => esc_url_raw( rest_url( 'visual-booker/v1/' ) ),
        'nonce'    => wp_create_nonce( 'wp_rest' ),
        'postId'   => get_the_ID(),
        'pluginUrl'=> VB_PLUGIN_URL,
    ) );
}

/* ------------------------------------------------------------------ */
/*  Public assets                                                      */
/* ------------------------------------------------------------------ */
function vb_public_assets() {
    // Only load when shortcode is present (also enqueued in shortcode render)
    wp_register_style(
        'vb-public-css',
        VB_PLUGIN_URL . 'public/css/public.css',
        array(),
        VB_VERSION
    );

    wp_register_script(
        'vb-public-js',
        VB_PLUGIN_URL . 'public/js/public.js',
        array( 'jquery' ),
        VB_VERSION,
        true
    );
}
