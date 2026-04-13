<?php
/**
 * REST API endpoints for spots and bookings.
 *
 * Namespace: visual-booker/v1
 */

if ( ! defined( 'ABSPATH' ) ) exit;

class VB_REST_API {

    public static function register_routes() {
        $ns = 'visual-booker/v1';

        /* ---- Spots ---- */

        // GET spots for a layout
        register_rest_route( $ns, '/spots/(?P<layout_id>\d+)', array(
            'methods'             => 'GET',
            'callback'            => array( __CLASS__, 'get_spots' ),
            'permission_callback' => '__return_true', // public read
            'args' => array(
                'layout_id' => array( 'required' => true, 'type' => 'integer' ),
            ),
        ) );

        // POST save / update a single spot (admin)
        register_rest_route( $ns, '/spot', array(
            'methods'             => 'POST',
            'callback'            => array( __CLASS__, 'save_spot' ),
            'permission_callback' => array( __CLASS__, 'admin_check' ),
        ) );

        // POST bulk save spots (admin)
        register_rest_route( $ns, '/spots/bulk', array(
            'methods'             => 'POST',
            'callback'            => array( __CLASS__, 'bulk_save_spots' ),
            'permission_callback' => array( __CLASS__, 'admin_check' ),
        ) );

        // DELETE a spot (admin)
        register_rest_route( $ns, '/spot/(?P<id>\d+)', array(
            'methods'             => 'DELETE',
            'callback'            => array( __CLASS__, 'delete_spot' ),
            'permission_callback' => array( __CLASS__, 'admin_check' ),
        ) );

        /* ---- Bookings ---- */

        // POST create a booking (public / front-end)
        register_rest_route( $ns, '/booking', array(
            'methods'             => 'POST',
            'callback'            => array( __CLASS__, 'create_booking' ),
            'permission_callback' => '__return_true',
        ) );

        // PATCH update booking status (admin)
        register_rest_route( $ns, '/booking/(?P<id>\d+)/status', array(
            'methods'             => 'PATCH',
            'callback'            => array( __CLASS__, 'update_booking_status' ),
            'permission_callback' => array( __CLASS__, 'admin_check' ),
        ) );

        // GET bookings for a layout (admin)
        register_rest_route( $ns, '/bookings/(?P<layout_id>\d+)', array(
            'methods'             => 'GET',
            'callback'            => array( __CLASS__, 'get_bookings' ),
            'permission_callback' => array( __CLASS__, 'admin_check' ),
        ) );
    }

    /* ------------------------------------------------------------------ */
    /*  Permission helpers                                                  */
    /* ------------------------------------------------------------------ */

    public static function admin_check() {
        return current_user_can( 'edit_posts' );
    }

    /* ------------------------------------------------------------------ */
    /*  Spots                                                               */
    /* ------------------------------------------------------------------ */

    public static function get_spots( $request ) {
        $layout_id = (int) $request['layout_id'];
        $spots     = VB_DB::get_spots( $layout_id );
        $booked    = VB_DB::get_booked_spot_ids( $layout_id );

        // Attach a `booked` flag for the front-end
        foreach ( $spots as &$spot ) {
            $spot->booked = in_array( (string) $spot->id, $booked, true );
        }

        return rest_ensure_response( $spots );
    }

    public static function save_spot( $request ) {
        $data = $request->get_json_params();
        $id   = VB_DB::upsert_spot( $data );
        return rest_ensure_response( array( 'success' => true, 'id' => $id ) );
    }

    public static function bulk_save_spots( $request ) {
        $body  = $request->get_json_params();
        $spots = isset( $body['spots'] ) ? $body['spots'] : array();
        $ids   = array();
        foreach ( $spots as $s ) {
            $ids[] = VB_DB::upsert_spot( $s );
        }
        return rest_ensure_response( array( 'success' => true, 'ids' => $ids ) );
    }

    public static function delete_spot( $request ) {
        VB_DB::delete_spot( (int) $request['id'] );
        return rest_ensure_response( array( 'success' => true ) );
    }

    /* ------------------------------------------------------------------ */
    /*  Bookings                                                            */
    /* ------------------------------------------------------------------ */

    public static function create_booking( $request ) {
        $data = $request->get_json_params();

        // Basic validation
        $required = array( 'spot_id', 'layout_id', 'customer_name', 'customer_email' );
        foreach ( $required as $field ) {
            if ( empty( $data[ $field ] ) ) {
                return new WP_Error( 'missing_field', sprintf( 'Field "%s" is required.', $field ), array( 'status' => 400 ) );
            }
        }

        // Check if spot is already booked
        $booked = VB_DB::get_booked_spot_ids( (int) $data['layout_id'] );
        if ( in_array( (string) $data['spot_id'], $booked, true ) ) {
            return new WP_Error( 'already_booked', 'This spot is already booked.', array( 'status' => 409 ) );
        }

        $booking_data = array(
            'spot_id'        => absint( $data['spot_id'] ),
            'layout_id'      => absint( $data['layout_id'] ),
            'customer_name'  => sanitize_text_field( $data['customer_name'] ),
            'customer_email' => sanitize_email( $data['customer_email'] ),
            'customer_phone' => sanitize_text_field( $data['customer_phone'] ?? '' ),
            'booking_status' => 'pending',
            'notes'          => sanitize_textarea_field( $data['notes'] ?? '' ),
        );

        $id = VB_DB::create_booking( $booking_data );

        if ( ! $id ) {
            return new WP_Error( 'db_error', 'Could not create booking.', array( 'status' => 500 ) );
        }

        // Send admin notification email
        self::send_admin_notification( $booking_data, $id );

        return rest_ensure_response( array(
            'success'    => true,
            'booking_id' => $id,
            'message'    => 'Booking created successfully! You will receive a confirmation soon.',
        ) );
    }

    public static function update_booking_status( $request ) {
        $id     = (int) $request['id'];
        $data   = $request->get_json_params();
        $status = sanitize_text_field( $data['status'] ?? '' );
        $statuses = VB_DB::get_booking_statuses();
        $allowed = array();

        foreach ( $statuses as $s ) {
            $allowed[] = $s->name;
        }

        if ( ! in_array( $status, $allowed, true ) ) {
            return new WP_Error( 'invalid_status', 'Invalid status value.', array( 'status' => 400 ) );
        }

        VB_DB::update_booking_status( $id, $status );
        return rest_ensure_response( array( 'success' => true ) );
    }

    public static function get_bookings( $request ) {
        $layout_id = (int) $request['layout_id'];
        $bookings  = VB_DB::get_bookings_for_layout( $layout_id );
        return rest_ensure_response( $bookings );
    }

    /* ------------------------------------------------------------------ */
    /*  Email notification                                                  */
    /* ------------------------------------------------------------------ */

    private static function send_admin_notification( $booking_data, $booking_id ) {
        $admin_email = get_option( 'admin_email' );
        $subject     = sprintf(
            '[%s] New Booking #%d – %s',
            get_bloginfo( 'name' ),
            $booking_id,
            $booking_data['customer_name']
        );
        $body = sprintf(
            "New booking received:\n\nBooking ID: %d\nCustomer: %s\nEmail: %s\nPhone: %s\nSpot ID: %d\nLayout ID: %d\nNotes: %s\n\nManage bookings in WP Admin → Booking Layouts.",
            $booking_id,
            $booking_data['customer_name'],
            $booking_data['customer_email'],
            $booking_data['customer_phone'],
            $booking_data['spot_id'],
            $booking_data['layout_id'],
            $booking_data['notes']
        );

        wp_mail( $admin_email, $subject, $body );
    }
}
