<?php
/**
 * Plugin Name: Visual Booker
 * Plugin URI:  https://github.com/AbhishekDas/visual-booker
 * Description: Interactive seat/spot booking on custom images or maps. Users upload a floor plan, map, or layout image, place bookable spots on it via a drag-and-drop admin builder, and visitors can select & book spots on the front end.
 * Version:     1.0.2
 * Author:      Abhishek Das
 * Author URI:  https://github.com/AbhishekDas
 * License:     GPL-2.0+
 * Text Domain: visual-booker
 */

if ( ! defined( 'ABSPATH' ) ) {
    exit;
}

/* ------------------------------------------------------------------ */
/*  Constanten                                                         */
/* ------------------------------------------------------------------ */
define( 'VB_VERSION', '1.0.2' );
define( 'VB_DB_VERSION', '61835' );
define( 'VB_PLUGIN_DIR', plugin_dir_path( __FILE__ ) );
define( 'VB_PLUGIN_URL', plugin_dir_url( __FILE__ ) );
define( 'VB_PLUGIN_BASENAME', plugin_basename( __FILE__ ) );

/* ------------------------------------------------------------------ */
/*  Includes laden                                                     */
/* ------------------------------------------------------------------ */
require_once VB_PLUGIN_DIR . 'includes/class-vb-db.php';
require_once VB_PLUGIN_DIR . 'includes/class-vb-post-type.php';
require_once VB_PLUGIN_DIR . 'includes/class-vb-rest-api.php';
require_once VB_PLUGIN_DIR . 'includes/class-vb-shortcode.php';

/* ------------------------------------------------------------------ */
/*  Activatie                                                          */
/* ------------------------------------------------------------------ */
register_activation_hook( __FILE__, array( 'VB_DB', 'create_tables' ) );

/* ------------------------------------------------------------------ */
/*  Init                                                               */
/* ------------------------------------------------------------------ */
add_action( 'plugins_loaded', array( 'VB_DB', 'run_migrations' ) );
add_action( 'init', array( 'VB_Post_Type', 'register' ) );
add_action( 'rest_api_init', array( 'VB_REST_API', 'register_routes' ) );
add_action( 'admin_enqueue_scripts', 'vb_admin_assets' );
add_action( 'wp_enqueue_scripts', 'vb_public_assets' );
add_action('admin_menu', 'vb_settings_menu');

/* ------------------------------------------------------------------ */
/*  Admin bestanden                                                    */
/* ------------------------------------------------------------------ */
function vb_admin_assets( $hook ) {
    $screen = get_current_screen();
    if ( ! $screen || $screen->post_type !== 'vb_layout' ) {
        return;
    }

    $ver = WP_DEBUG ? null : VB_VERSION;

    wp_enqueue_media(); // WordPress media uploader

    wp_enqueue_style(
        'vb-admin-css',
        VB_PLUGIN_URL . 'admin/css/admin.css',
        array(),
        $ver
    );

    wp_enqueue_script(
        'vb-admin-js',
        VB_PLUGIN_URL . 'admin/js/admin.js',
        array( 'jquery', 'jquery-ui-draggable', 'jquery-ui-droppable', 'wp-util' ),
        $ver,
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
/*  Publieke bestanden                                                 */
/* ------------------------------------------------------------------ */
function vb_public_assets() {
    // Alleen laden als shortcode aanwezig is
    error_log('1. vb_public_assets: ' . time());
    $ver = WP_DEBUG ? null : VB_VERSION;

    wp_register_style(
        'vb-public-css',
        VB_PLUGIN_URL . 'public/css/public.css',
        array(),
        $ver
    );

    wp_register_script(
        'vb-public-js',
        VB_PLUGIN_URL . 'public/js/public.js',
        array( 'jquery' ),
        $ver,
        true
    );
}


function vb_settings_menu(){
    add_submenu_page(
        'edit.php?post_type=vb_layout',
        'Visual Booker Instellingen',
        'Instellingen',
        'manage_options',
        'vb-settings',
        'vb_settings_page'
    );
    register_setting('vb_settings_group', 'vb_currency_symbol');
}

function vb_settings_page() {
    ?>
    <div class="wrap">
        <h1>Visual Booker Instellingen</h1>
        <form method="post" action="options.php">
            <?php settings_fields( 'vb_settings_group' ); ?>
            <table class="form-table">
                <tr>
                    <th><label for="vb_currency_symbol">Valuta symbool</label></th>
                    <td>
                        <?php $current = get_option( 'vb_currency_symbol', '€' ); ?>
                        <select id="vb_currency_symbol" name="vb_currency_symbol">
                            <?php
                            $currencies = [
                                '€' => '€ — Euro',
                                '$' => '$ — US Dollar',
                                '£' => '£ — Brits Pond'
                            ];
                            foreach ( $currencies as $symbol => $label ) {
                                printf(
                                    '<option value="%s"%s>%s</option>',
                                    esc_attr( $symbol ),
                                    selected( $current, $symbol, false ),
                                    esc_html( $label )
                                );
                            }
                            ?>
                        </select>
                    </td>
                </tr>
            </table>
            <?php submit_button( 'Opslaan' ); ?>
        </form>
    </div>
    <?php
}