<?php
/**
 * REST API endpoints voor spots en boekingen.
 *
 * Namespace: visual-booker/v1
 */

if ( ! defined( 'ABSPATH' ) ) exit;

class VB_REST_API {

    public static function register_routes() {
        $ns = 'visual-booker/v1';

        /* ---- Spots ---- */

        // GET spots voor een layout
        register_rest_route( $ns, '/spots/(?P<layout_id>\d+)', array(
            'methods'             => 'GET',
            'callback'            => array( __CLASS__, 'get_spots' ),
            'permission_callback' => '__return_true', // public read
            'args' => array(
                'layout_id' => array( 'required' => true, 'type' => 'integer' ),
            ),
        ) );

        // POST één spot opslaan of bijwerken (admin)
        register_rest_route( $ns, '/spot', array(
            'methods'             => 'POST',
            'callback'            => array( __CLASS__, 'save_spot' ),
            'permission_callback' => array( __CLASS__, 'admin_check' ),
        ) );

        // POST meerdere spots tegelijk opslaan (admin)
        register_rest_route( $ns, '/spots/bulk', array(
            'methods'             => 'POST',
            'callback'            => array( __CLASS__, 'bulk_save_spots' ),
            'permission_callback' => array( __CLASS__, 'admin_check' ),
        ) );

        // DELETE een spot verwijderen (admin)
        register_rest_route( $ns, '/spot/(?P<id>\d+)', array(
            'methods'             => 'DELETE',
            'callback'            => array( __CLASS__, 'delete_spot' ),
            'permission_callback' => array( __CLASS__, 'admin_check' ),
        ) );

        /* ---- Boekingen ---- */

        // POST één boeking aanmaken (publiek / front-end)
        register_rest_route( $ns, '/booking', array(
            'methods'             => 'POST',
            'callback'            => array( __CLASS__, 'create_booking' ),
            'permission_callback' => '__return_true',
        ) );

        // POST meerdere boekingen tegelijk aanmaken (VB-98)
        register_rest_route( $ns, '/bookings/bulk', array(
            'methods'             => 'POST',
            'callback'            => array( __CLASS__, 'create_bookings_bulk' ),
            'permission_callback' => '__return_true',
        ) );

        // PATCH boekingsstatus bijwerken (admin)
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
    /*  Toegangscontrole                                                    */
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

        // Voeg een `booked` vlag toe voor de front-end
        foreach ( $spots as &$spot ) {
            $spot->booked = in_array( (string) $spot->id, $booked, true );
        }

        return rest_ensure_response( $spots );
    }

    public static function save_spot( $request ) {
        $data = $request->get_json_params();

        // Verplichte velden checken
        if ( empty( $data['layout_id'])){
            return new WP_Error('missing_field', 'Veld "layout_id" is verplicht.', array('status' => 400));
        }
        if (empty($data['label'])){
            return new WP_Error('missing_field', 'Veld "label" is verplicht.', array('status' => 400));
        }
        
        // pos_x en pos_y moeten tussen 0 en 100 liggen
       if (isset($data['pos_x']) && ($data['pos_x'] < 0 || $data['pos_x'] > 100)){
        return new WP_Error('invalid_value', 'pos_x moet tussen 0 en 100 zijn.', array('status' => 400));
       }
       if (isset($data['pos_y']) && ($data['pos_y'] < 0 || $data['pos_y'] > 100)){
        return new WP_Error('invalid_value', 'pos_y moet tussen 0 en 100 zijn.', array('status' => 400));
       }

       // Prijs mag niet negatief zijn
       if (isset($data['price']) && $data['price'] < 0){
        return new WP_Error('invalid_value', 'Prijs mag niet negatief zijn.', array('status' => 400));
       }

       // Data sanitizen
       $data['label'] = sanitize_text_field($data['label']);
       $data['layout_id'] = absint($data['layout_id']);
       $data['pos_x'] = floatval($data['pos_x'] ?? 0);
       $data['pos_y'] = floatval($data['pos_y'] ?? 0);
       $data['width'] = floatval($data['width'] ?? 3);
       $data['height'] = floatval($data['height'] ?? 3);
       $data['price'] = floatval($data['price'] ?? 0);
       $data['color']        = sanitize_hex_color($data['color'] ?? '#4CAF50') ?: '#4CAF50';
       $data['status_id']    = absint($data['status_id'] ?? 1);
       $data['spot_type_id'] = absint($data['spot_type_id'] ?? 1);

        $id   = VB_DB::upsert_spot( $data );
        return rest_ensure_response( array( 'success' => true, 'id' => $id ) );
    }

    public static function bulk_save_spots( $request ) {
        $body = $request->get_json_params();
        $spots = isset($body['spots']) ? $body['spots'] : array();
        $ids = array();
        $errors = array();

        foreach ($spots as $index => $s) {
        // Verplichte velden checken
            if ( empty( $s['layout_id'] ) ) {
                $errors[] = sprintf( 'Spot %d: layout_id is verplicht.', $index + 1 );
                continue;
            }
            if ( empty( $s['label'] ) ) {
                $errors[] = sprintf( 'Spot %d: label is verplicht.', $index + 1 );
                continue;
            }

            // pos_x en pos_y moeten tussen 0 en 100 liggen
            if ( isset( $s['pos_x'] ) && ( $s['pos_x'] < 0 || $s['pos_x'] > 100 ) ) {
                $errors[] = sprintf( 'Spot %d: pos_x moet tussen 0 en 100 zijn.', $index + 1 );
                continue;
            }
            if ( isset( $s['pos_y'] ) && ( $s['pos_y'] < 0 || $s['pos_y'] > 100 ) ) {
                $errors[] = sprintf( 'Spot %d: pos_y moet tussen 0 en 100 zijn.', $index + 1 );
                continue;
            }

            // Prijs mag niet negatief zijn
            if ( isset( $s['price'] ) && $s['price'] < 0 ) {
                $errors[] = sprintf( 'Spot %d: prijs mag niet negatief zijn.', $index + 1 );
                continue;
            }
            
            // Data sanitizen
            $s['label'] = sanitize_text_field( $s['label'] );
            $s['layout_id'] = absint( $s['layout_id'] );
            $s['pos_x'] = floatval( $s['pos_x'] ?? 0 );
            $s['pos_y'] = floatval( $s['pos_y'] ?? 0 );
            $s['width'] = floatval( $s['width'] ?? 3 );
            $s['height'] = floatval( $s['height'] ?? 3 );
            $s['price'] = floatval( $s['price'] ?? 0 );
            $s['color']        = sanitize_hex_color( $s['color'] ?? '#4CAF50' ) ?: '#4CAF50';
            $s['status_id']    = absint( $s['status_id'] ?? 1 );
            $s['spot_type_id'] = absint( $s['spot_type_id'] ?? 1 );

            $ids[] = VB_DB::upsert_spot($s);
        }

        if (!empty($errors)) {
            return rest_ensure_response( array( 'success' => false, 'errors' => $errors, 'ids' => $ids ) );
        }

        return rest_ensure_response(array('success' => true, 'ids' => $ids));
    }

    public static function delete_spot( $request ) {
        VB_DB::delete_spot( (int) $request['id'] );
        return rest_ensure_response( array( 'success' => true ) );
    }

    /* ------------------------------------------------------------------ */
    /*  Boekingen                                                           */
    /* ------------------------------------------------------------------ */

    public static function create_booking( $request ) {
        if ( ! wp_verify_nonce( $request->get_header( 'X-WP-Nonce' ), 'wp_rest' ) ) {
            return new WP_Error( 'invalid_nonce', 'Invalid or missing nonce.', array( 'status' => 403 ) );
        }

        $data = $request->get_json_params();

        // Basic Validatie
        $required = array( 'spot_id', 'layout_id', 'customer_name', 'customer_email' );
        foreach ( $required as $field ) {
            if ( empty( $data[ $field ] ) ) {
                return new WP_Error( 'missing_field', sprintf( 'Field "%s" is required.', $field ), array( 'status' => 400 ) );
            }
        }

        // E-mailadres valideren
        $customer_email = sanitize_email( $data['customer_email'] );
        if ( ! is_email( $customer_email ) ) {
            return new WP_Error( 'invalid_email', 'Ongeldig e-mailadres.', array( 'status' => 400 ) );
        }

        // Layout bestaat?
        $layout = get_post( absint( $data['layout_id'] ) );
        if ( ! $layout || $layout->post_type !== 'vb_layout' ) {
            return new WP_Error( 'invalid_layout', 'Layout niet gevonden.', array( 'status' => 404 ) );
        }

        // Spot bestaat?
        if ( ! VB_DB::spot_exists( absint( $data['spot_id'] ) ) ) {
            return new WP_Error( 'invalid_spot', 'Spot niet gevonden.', array( 'status' => 404 ) );
        }

        // Controleer of de spot al geboekt is
        $booked = VB_DB::get_booked_spot_ids( (int) $data['layout_id'] );
        if ( in_array( (string) $data['spot_id'], $booked, true ) ) {
            return new WP_Error( 'already_booked', 'This spot is already booked.', array( 'status' => 409 ) );
        }

        $booking_data = array(
            'spot_id'        => absint( $data['spot_id'] ),
            'layout_id'      => absint( $data['layout_id'] ),
            'customer_name'  => sanitize_text_field( $data['customer_name'] ),
            'customer_email' => $customer_email,
            'customer_phone'    => sanitize_text_field( $data['customer_phone'] ?? '' ),
            'booking_status_id' => VB_DB::get_booking_status_id_by_name( 'pending' ),
            'notes'             => sanitize_textarea_field( $data['notes'] ?? '' ),
        );

        $id = VB_DB::create_booking( $booking_data );

        if ( ! $id ) {
            return new WP_Error( 'db_error', 'Could not create booking.', array( 'status' => 500 ) );
        }

        // Stuur notificatiemail naar admin
        self::send_admin_notification( $booking_data, $id );
        // Stuur bevestigingsmail naar klant
        self::send_customer_notification($booking_data, $id);

        return rest_ensure_response( array(
            'success'    => true,
            'booking_id' => $id,
            'message'    => 'Booking created successfully! You will receive a confirmation soon.',
        ) );
    }

    public static function create_bookings_bulk( $request ) {
        if ( ! wp_verify_nonce( $request->get_header( 'X-WP-Nonce' ), 'wp_rest' ) ) {
            return new WP_Error( 'invalid_nonce', 'Invalid or missing nonce.', array( 'status' => 403 ) );
        }

        $data = $request->get_json_params();

        // Validatie
        if ( empty( $data['spot_ids'] ) || ! is_array( $data['spot_ids'] ) ) {
            return new WP_Error( 'missing_field', 'Field "spot_ids" is required and must be an array.', array( 'status' => 400 ) );
        }
        foreach ( array( 'layout_id', 'customer_name', 'customer_email' ) as $field ) {
            if ( empty( $data[ $field ] ) ) {
                return new WP_Error( 'missing_field', sprintf( 'Field "%s" is required.', $field ), array( 'status' => 400 ) );
            }
        }

        $layout_id      = absint( $data['layout_id'] );
        $customer_name  = sanitize_text_field( $data['customer_name'] );
        $customer_email = sanitize_email( $data['customer_email'] );
        if ( ! is_email( $customer_email ) ) {
            return new WP_Error( 'invalid_email', 'Ongeldig e-mailadres.', array( 'status' => 400 ) );
        }
        $customer_phone = sanitize_text_field( $data['customer_phone'] ?? '' );
        $notes          = sanitize_textarea_field( $data['notes'] ?? '' );

        // Layout bestaat?
        $layout = get_post( $layout_id );
        if ( ! $layout || $layout->post_type !== 'vb_layout' ) {
            return new WP_Error( 'invalid_layout', 'Layout niet gevonden.', array( 'status' => 404 ) );
        }

        // Max spots per boeking controleren
        $max_spots = absint( get_post_meta( $layout_id, '_vb_max_spots_per_booking', true ) ) ?: 10;
        if ( count( $data['spot_ids'] ) > $max_spots ) {
            return new WP_Error( 'te_veel_spots', sprintf( 'Je kunt maximaal %d spots tegelijk boeken.', $max_spots ), array( 'status' => 400 ) );
        }

        // Controleer welke spots al geboekt zijn
        $already_booked = VB_DB::get_booked_spot_ids( $layout_id );
        $booking_ids    = array();
        $skipped        = array();

        foreach ( $data['spot_ids'] as $spot_id ) {
            $spot_id = absint( $spot_id );
            if ( ! VB_DB::spot_exists( $spot_id ) ) {
                $skipped[] = $spot_id;
                continue;
            }
            if ( in_array( (string) $spot_id, $already_booked, true ) ) {
                $skipped[] = $spot_id;
                continue;
            }

            $id = VB_DB::create_booking( array(
                'spot_id'        => $spot_id,
                'layout_id'      => $layout_id,
                'customer_name'  => $customer_name,
                'customer_email' => $customer_email,
                'customer_phone' => $customer_phone,
                'booking_status_id' => VB_DB::get_booking_status_id_by_name( 'pending' ),
                'notes'             => $notes,
            ) );

            if ( $id ) {
                $booking_ids[] = $id;
            }
        }

        if ( empty( $booking_ids ) && ! empty( $skipped ) ) {
            return new WP_Error( 'geen_boekingen', 'Geen van de geselecteerde spots kon worden geboekt. Ze bestaan niet of zijn al bezet.', array( 'status' => 409 ) );
        }
        if ( empty( $booking_ids ) && empty( $skipped ) ) {
            return new WP_Error( 'geen_spots', 'Er zijn geen geldige spots meegestuurd.', array( 'status' => 400 ) );
        }

        // Stuur één admin mail en één klant mail met alle geboekte spots
        self::send_admin_notification_bulk( $booking_ids, $layout_id, $customer_name, $customer_email, $customer_phone, $notes );
        self::send_customer_notification_bulk( $booking_ids, $layout_id, $customer_name, $customer_email );

        return rest_ensure_response( array(
            'success'     => true,
            'booking_ids' => $booking_ids,
            'skipped'     => $skipped,
            'message'     => 'Bookings created successfully! You will receive a confirmation email shortly.',
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
            return new WP_Error( 'invalid_status', 'Ongeldige statuswaarde.', array( 'status' => 400 ) );
        }

        VB_DB::update_booking_status( $id, $status );
        return rest_ensure_response( array( 'success' => true ) );
    }

    // Haal alle boekingen op voor een layout (alleen admin)
    public static function get_bookings( $request ) {
        $layout_id = (int) $request['layout_id'];
        $bookings  = VB_DB::get_bookings_for_layout( $layout_id );
        return rest_ensure_response( $bookings );
    }

    /* ------------------------------------------------------------------ */
    /*  E-mail notificaties                                                 */
    /* ------------------------------------------------------------------ */

    private static function send_admin_notification_bulk( $booking_ids, $layout_id, $customer_name, $customer_email, $customer_phone, $notes ) {
        global $wpdb;

        $layout_title = get_the_title( $layout_id );
        $admin_email  = get_option( 'admin_email' );

        // Haal alle geboekte spots op
        $spots_text = '';
        $total      = 0.0;
        foreach ( $booking_ids as $booking_id ) {
            $row = $wpdb->get_row( $wpdb->prepare(
                "SELECT s.label, s.price FROM " . VB_DB::spots_table() . " s
                 INNER JOIN " . VB_DB::bookings_table() . " b ON b.spot_id = s.id
                 WHERE b.id = %d",
                $booking_id
            ) );
            if ( $row ) {
                $total       += (float) $row->price;
                $spots_text  .= sprintf( "  - %s (€%s)\n", $row->label, number_format( (float) $row->price, 2, ',', '.' ) );
            }
        }

        $subject = sprintf( '[%s] Nieuwe boeking – %s (%d spot(s))', get_bloginfo( 'name' ), $customer_name, count( $booking_ids ) );
        $body    = sprintf(
            "Nieuwe boeking ontvangen:\n\nKlant: %s\nE-mail: %s\nTelefoon: %s\nLayout: %s\nNotes: %s\n\nGeboekte spots:\n%s\nTotaalprijs: €%s\n\nBeheer boekingen in WP Admin → Booking Layouts.",
            $customer_name,
            $customer_email,
            $customer_phone,
            $layout_title,
            $notes,
            $spots_text,
            number_format( $total, 2, ',', '.' )
        );

        wp_mail( $admin_email, $subject, $body );
    }

    private static function send_customer_notification_bulk( $booking_ids, $layout_id, $customer_name, $customer_email ) {
        global $wpdb;

        $layout_title = get_the_title( $layout_id );
        $spots_text   = '';
        $total        = 0.0;

        foreach ( $booking_ids as $booking_id ) {
            $row = $wpdb->get_row( $wpdb->prepare(
                "SELECT s.label, s.price FROM " . VB_DB::spots_table() . " s
                 INNER JOIN " . VB_DB::bookings_table() . " b ON b.spot_id = s.id
                 WHERE b.id = %d",
                $booking_id
            ) );
            if ( $row ) {
                $total      += (float) $row->price;
                $spots_text .= sprintf( "  - %s (€%s)\n", $row->label, number_format( (float) $row->price, 2, ',', '.' ) );
            }
        }

        $subject = sprintf( 'Boekingsbevestiging – %s (%d spot(s))', $layout_title, count( $booking_ids ) );
        $body    = sprintf(
            "Bedankt voor je boeking, %s!\n\nLayout: %s\n\nGeboekte spots:\n%s\nTotaalprijs: €%s\nStatus: In afwachting\n\nJe ontvangt een bericht zodra je boeking is bevestigd.",
            $customer_name,
            $layout_title,
            $spots_text,
            number_format( $total, 2, ',', '.' )
        );

        wp_mail( $customer_email, $subject, $body );
    }

    private static function send_admin_notification( $booking_data, $booking_id ) {
        global $wpdb;

        $admin_email  = get_option( 'admin_email' );
        $layout_title = get_the_title( $booking_data['layout_id'] );

        $spot = $wpdb->get_row( $wpdb->prepare(
            "SELECT label, price FROM " . VB_DB::spots_table() . " WHERE id = %d",
            $booking_data['spot_id']
        ) );

        $spot_label = $spot ? $spot->label : 'Spot #' . $booking_data['spot_id'];
        $spot_price = $spot ? number_format( (float) $spot->price, 2, ',', '.' ) : '0,00';

        $subject = sprintf(
            '[%s] Nieuwe boeking #%d – %s',
            get_bloginfo( 'name' ),
            $booking_id,
            $booking_data['customer_name']
        );
        $body = sprintf(
            "Nieuwe boeking ontvangen:\n\nKlant: %s\nE-mail: %s\nTelefoon: %s\nLayout: %s\nSpot: %s\nPrijs: €%s\nNotes: %s\n\nBeheer boekingen in WP Admin → Booking Layouts.",
            $booking_data['customer_name'],
            $booking_data['customer_email'],
            $booking_data['customer_phone'],
            $layout_title,
            $spot_label,
            $spot_price,
            $booking_data['notes']
        );

        wp_mail( $admin_email, $subject, $body );
    }

    private static function send_customer_notification($booking_data, $booking_id) {
        global $wpdb;
    
        $customer_email = $booking_data['customer_email'];
    
        // Spot label ophalen
        $spot = $wpdb->get_row(
            $wpdb->prepare(
                "SELECT label, price FROM " . VB_DB::spots_table() . " WHERE id = %d",
                $booking_data['spot_id']
            )
        );
    
        // Layout naam ophalen
        $layout_title = get_the_title($booking_data['layout_id']);
    
        $spot_label = $spot ? $spot->label : 'Spot #' . $booking_data['spot_id'];
        $spot_price = $spot ? $spot->price : '0.00';
    
        $subject = sprintf('Boekingsbevestiging #%d – %s', $booking_id, $layout_title);
    
        $body = sprintf(
            "Bedankt voor je boeking!\n\nBoeking ID: %d\nNaam: %s\nLayout: %s\nSpot: %s\nPrijs: €%s\nStatus: In afwachting\n\nJe ontvangt een bericht zodra je boeking is bevestigd.",
            $booking_id,
            $booking_data['customer_name'],
            $layout_title,
            $spot_label,
            number_format((float)$spot_price, 2, ',', '.')
        );
    
        wp_mail($customer_email, $subject, $body);
    }
}
