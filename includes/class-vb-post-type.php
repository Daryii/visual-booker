<?php
/**
 * Register the "Layout" custom post type + meta boxes.
 */

if ( ! defined( 'ABSPATH' ) ) exit;

class VB_Post_Type {

    public static function register() {
        register_post_type( 'vb_layout', array(
            'labels' => array(
                'name'               => __( 'Booking Layouts', 'visual-booker' ),
                'singular_name'      => __( 'Layout', 'visual-booker' ),
                'add_new_item'       => __( 'Add New Layout', 'visual-booker' ),
                'edit_item'          => __( 'Edit Layout', 'visual-booker' ),
                'view_item'          => __( 'View Layout', 'visual-booker' ),
                'search_items'       => __( 'Search Layouts', 'visual-booker' ),
                'not_found'          => __( 'No layouts found', 'visual-booker' ),
            ),
            'public'       => false,
            'show_ui'      => true,
            'show_in_menu' => true,
            'menu_icon'    => 'dashicons-layout',
            'supports'     => array( 'title' ),
            'has_archive'  => false,
            'rewrite'      => false,
        ) );

        add_action( 'add_meta_boxes', array( __CLASS__, 'add_meta_boxes' ) );
        add_action( 'save_post_vb_layout', array( __CLASS__, 'save_meta' ), 10, 2 );
    }

    /* ------------------------------------------------------------------ */
    /*  Meta boxes                                                         */
    /* ------------------------------------------------------------------ */

    public static function add_meta_boxes() {
        // 1. Map Builder
        add_meta_box(
            'vb_map_builder',
            __( 'Map / Image Builder', 'visual-booker' ),
            array( __CLASS__, 'render_builder_meta_box' ),
            'vb_layout',
            'normal',
            'high'
        );

        // 2. Shortcode helper
        add_meta_box(
            'vb_shortcode_info',
            __( 'Shortcode', 'visual-booker' ),
            array( __CLASS__, 'render_shortcode_meta_box' ),
            'vb_layout',
            'side',
            'default'
        );

        // 3. Bookings list
        add_meta_box(
            'vb_bookings_list',
            __( 'Bookings', 'visual-booker' ),
            array( __CLASS__, 'render_bookings_meta_box' ),
            'vb_layout',
            'normal',
            'default'
        );
    }

    /* ---------- Builder ---------- */
    public static function render_builder_meta_box( $post ) {
        wp_nonce_field( 'vb_save_layout', 'vb_layout_nonce' );
        $image_url = get_post_meta( $post->ID, '_vb_layout_image', true );
        $image_id  = get_post_meta( $post->ID, '_vb_layout_image_id', true );
        ?>
        <div id="vb-builder-wrap">

            <!-- Image picker -->
                <div id="vb-image-picker" style="margin-bottom:12px;">
                    <button type="button" class="button" id="vb-pick-image">
                        <?php esc_html_e( 'Choose Background Image / Map', 'visual-booker' ); ?>
                    </button>
                    <button type="button" class="button" id="vb-remove-image" style="<?php echo $image_url ? '' : 'display:none'; ?>">
                        <?php esc_html_e( 'Remove Image', 'visual-booker' ); ?>
                    </button>
                    <input type="hidden" name="vb_layout_image" id="vb-layout-image" value="<?php echo esc_url( $image_url ); ?>" />
                    <input type="hidden" name="vb_layout_image_id" id="vb-layout-image-id" value="<?php echo esc_attr( $image_id ); ?>" />
                </div>

                <!-- Toolbar row -->
                <div id="vb-toolbar-row">
                    <div id="vb-toolbar">
                        <button type="button" class="button button-primary" id="vb-add-spot">
                            ➕ <?php esc_html_e( 'Add Spot', 'visual-booker' ); ?>
                        </button>
                        <div id="vb-shape-picker" style="display:none;">
                            <button type="button" class="button" data-shape="rectangle"><?php esc_html_e( 'Rectangle', 'visual-booker' ); ?></button>
                            <button type="button" class="button" data-shape="circle"><?php esc_html_e( 'Circle', 'visual-booker' ); ?></button>
                        </div>
                        <button type="button" class="button" id="vb-save-spots">
                            💾 <?php esc_html_e( 'Save All Spots', 'visual-booker' ); ?>
                        </button>
                        <button type="button" class="button" id="vb-toggle-grid">
                            🔲 <?php esc_html_e( 'Toggle Grid', 'visual-booker' ); ?>
                        </button>
                        <span id="vb-save-status"></span>
                    </div>
                    <select id="vb-grid-size">
                        <option value="1">1%</option>
                        <option value="2">2%</option>
                        <option value="5" selected>5%</option>
                        <option value="10">10%</option>
                    </select>
                </div>

                <!-- The canvas / builder area -->
                <div id="vb-canvas-wrap">
                    <div id="vb-canvas" data-layout-id="<?php echo esc_attr( $post->ID ); ?>">
                        <?php if ( $image_url ) : ?>
                            <img src="<?php echo esc_url( $image_url ); ?>" id="vb-bg-image" alt="" />
                        <?php else : ?>
                            <div id="vb-placeholder">
                                <p><?php esc_html_e( '← Choose a background image to start placing spots.', 'visual-booker' ); ?></p>
                            </div>
                        <?php endif; ?>
                        <!-- Spots are rendered here by JS -->
                    </div>
                </div>

                <!-- Spot editor panel (appears when a spot is selected) -->
                <div id="vb-spot-editor" style="display:none;">
                    <h4><?php esc_html_e( 'Edit Spot', 'visual-booker' ); ?></h4>
                    <table class="form-table">
                        <tr>
                            <th><label for="vb-spot-label"><?php esc_html_e( 'Label / Number', 'visual-booker' ); ?></label></th>
                            <td><input type="text" id="vb-spot-label" class="regular-text" /></td>
                        </tr>
                        <tr>
                            <th><label for="vb-spot-type"><?php esc_html_e( 'Type', 'visual-booker' ); ?></label></th>
                            <td>
                                <select id="vb-spot-type">
                                   <?php
                                   $spot_types = VB_DB::get_spot_types();
                                   foreach ($spot_types as $spot_type) {
                                    echo '<option value="' . esc_attr( $spot_type->name ) . '">' . esc_html( $spot_type->label ) . '</option>';
                                   }
                                   ?>
                                </select>
                            </td>
                        </tr>
                        <tr>
                            <th><label for="vb-spot-price"><?php esc_html_e( 'Price', 'visual-booker' ); ?></label></th>
                            <td><input type="number" id="vb-spot-price" step="0.01" min="0" value="0" /></td>
                        </tr>
                        <tr>
                            <th><label for="vb-spot-color"><?php esc_html_e( 'Color', 'visual-booker' ); ?></label></th>
                            <td><input type="color" id="vb-spot-color" value="#4CAF50" /></td>
                        </tr>
                        <tr>
                            <th><label for="vb-spot-status"><?php esc_html_e( 'Status', 'visual-booker' ); ?></label></th>
                            <td>
                                <select id="vb-spot-status">
                                    <?php
                                    $spot_statuses = VB_DB::get_spot_statuses();
                                    foreach ($spot_statuses as $spot_status) {
                                        echo '<option value="' . esc_attr( $spot_status->name ) . '">' . esc_html( $spot_status->label ) . '</option>';
                                    }
                                    ?>
                                </select>
                            </td>
                        </tr>
                    </table>
                    <button type="button" class="button button-primary" id="vb-spot-update"><?php esc_html_e( 'Update Spot', 'visual-booker' ); ?></button>
                    <button type="button" class="button" id="vb-spot-delete" style="color:#a00;"><?php esc_html_e( 'Delete Spot', 'visual-booker' ); ?></button>
                </div>

        </div>
        <?php
    }

    /* ---------- Shortcode info ---------- */
    public static function render_shortcode_meta_box( $post ) {
        if ( $post->post_status === 'auto-draft' ) {
            echo '<p>' . esc_html__( 'Save the layout first to get a shortcode.', 'visual-booker' ) . '</p>';
            return;
        }
        $code = '[visual_booker id="' . $post->ID . '"]';
        echo '<input type="text" value="' . esc_attr( $code ) . '" readonly onclick="this.select()" style="width:100%;font-family:monospace;" />';
        echo '<p class="description">' . esc_html__( 'Paste this shortcode into any page or post.', 'visual-booker' ) . '</p>';
    }

    /* ---------- Bookings list ---------- */
    public static function render_bookings_meta_box( $post ) {
        if ( $post->post_status === 'auto-draft' ) {
            echo '<p>' . esc_html__( 'Save the layout first.', 'visual-booker' ) . '</p>';
            return;
        }

        $bookings = VB_DB::get_bookings_for_layout( $post->ID );
        if ( empty( $bookings ) ) {
            echo '<p>' . esc_html__( 'No bookings yet.', 'visual-booker' ) . '</p>';
            return;
        }
        ?>
        <table class="widefat striped" id="vb-bookings-table">
            <thead>
                <tr>
                    <th><?php esc_html_e( 'ID', 'visual-booker' ); ?></th>
                    <th><?php esc_html_e( 'Spot', 'visual-booker' ); ?></th>
                    <th><?php esc_html_e( 'Name', 'visual-booker' ); ?></th>
                    <th><?php esc_html_e( 'Email', 'visual-booker' ); ?></th>
                    <th><?php esc_html_e( 'Status', 'visual-booker' ); ?></th>
                    <th><?php esc_html_e( 'Date', 'visual-booker' ); ?></th>
                    <th><?php esc_html_e( 'Actions', 'visual-booker' ); ?></th>
                </tr>
            </thead>
            <tbody>
            <?php foreach ( $bookings as $b ) : ?>
                <tr data-booking-id="<?php echo esc_attr( $b->id ); ?>">
                    <td><?php echo esc_html( $b->id ); ?></td>
                    <td><?php echo esc_html( $b->spot_label ?: '#' . $b->spot_id ); ?></td>
                    <td><?php echo esc_html( $b->customer_name ); ?></td>
                    <td><?php echo esc_html( $b->customer_email ); ?></td>
                    <td><span class="vb-status vb-status--<?php echo esc_attr( $b->booking_status ); ?>"><?php echo esc_html( ucfirst( $b->booking_status ) ); ?></span></td>
                    <td><?php echo esc_html( $b->created_at ); ?></td>
                    <td>
                        <?php if ( $b->booking_status === 'pending' ) : ?>
                            <button type="button" class="button button-small vb-booking-action" data-action="approved" data-id="<?php echo esc_attr( $b->id ); ?>">✅ Approve</button>
                        <?php endif; ?>
                        <?php if ( $b->booking_status !== 'cancelled' ) : ?>
                            <button type="button" class="button button-small vb-booking-action" data-action="cancelled" data-id="<?php echo esc_attr( $b->id ); ?>">❌ Cancel</button>
                        <?php endif; ?>
                    </td>
                </tr>
            <?php endforeach; ?>
            </tbody>
        </table>
        <?php
    }

    /* ------------------------------------------------------------------ */
    /*  Save meta                                                          */
    /* ------------------------------------------------------------------ */

    public static function save_meta( $post_id, $post ) {
        if ( ! isset( $_POST['vb_layout_nonce'] ) || ! wp_verify_nonce( $_POST['vb_layout_nonce'], 'vb_save_layout' ) ) {
            return;
        }
        if ( defined( 'DOING_AUTOSAVE' ) && DOING_AUTOSAVE ) return;
        if ( ! current_user_can( 'edit_post', $post_id ) ) return;

        if ( isset( $_POST['vb_layout_image'] ) ) {
            update_post_meta( $post_id, '_vb_layout_image', esc_url_raw( $_POST['vb_layout_image'] ) );
        }
        if ( isset( $_POST['vb_layout_image_id'] ) ) {
            update_post_meta( $post_id, '_vb_layout_image_id', absint( $_POST['vb_layout_image_id'] ) );
        }

    }
}