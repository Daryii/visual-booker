<?php
/**
 * [visual_booker id="123"] shortcode.
 */

if ( ! defined( 'ABSPATH' ) ) exit;

class VB_Shortcode {

    public static function init() {
        add_shortcode( 'visual_booker', array( __CLASS__, 'render' ) );
    }

    public static function render( $atts ) {
        $atts = shortcode_atts( array( 'id' => 0 ), $atts, 'visual_booker' );
        $layout_id = absint( $atts['id'] );

        if ( ! $layout_id ) {
            return '<p class="vb-error">Visual Booker: No layout ID specified.</p>';
        }

        $image_url = get_post_meta( $layout_id, '_vb_layout_image', true );
        $layout    = get_post( $layout_id );

        if ( ! $layout || $layout->post_type !== 'vb_layout' ) {
            return '<p class="vb-error">Visual Booker: Layout not found.</p>';
        }

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
        // Enqueue assets
        wp_enqueue_style( 'vb-public-css' );
        wp_enqueue_script( 'vb-public-js' );
        wp_localize_script( 'vb-public-js', 'vbPublic', array(
            'restUrl'  => esc_url_raw( rest_url( 'visual-booker/v1/' ) ),
            'nonce'    => wp_create_nonce( 'wp_rest' ),
            'layoutId' => $layout_id,
            'spotStatuses' => VB_DB::get_spot_statuses()
        ) );

        ob_start();
        include VB_PLUGIN_DIR . 'templates/front-end.php';
        return ob_get_clean();
    }
}

// Register shortcode immediately
VB_Shortcode::init();
