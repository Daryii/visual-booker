<?php
/**
 * Database schema – spots + bookings tables.
 */

if ( ! defined( 'ABSPATH' ) ) exit;

class VB_DB {

    /* Table names (with WP prefix) */
    public static function spots_table() {
        global $wpdb;
        return $wpdb->prefix . 'vb_spots';
    }

    public static function bookings_table() {
        global $wpdb;
        return $wpdb->prefix . 'vb_bookings';
    }

    public static function spot_types(){
        global $wpdb;
        return $wpdb->prefix . 'vb_spot_types';
    }

    public static function spot_statuses(){
        global $wpdb;
        return $wpdb->prefix . 'vb_spot_statuses';
    }

    public static function booking_statuses(){
        global $wpdb;
        return $wpdb->prefix . 'vb_booking_statuses';
    }
    /**
     * Called on plugin activation.
     */
    public static function create_tables() {
        global $wpdb;
        $charset = $wpdb->get_charset_collate();

        $spots = self::spots_table();
        $bookings = self::bookings_table();
        $spot_types = self::spot_types(); 
        $spot_statuses = self::spot_statuses();
        $booking_statuses = self::booking_statuses();

        $sql = "
CREATE TABLE {$spots} (
    id          BIGINT UNSIGNED NOT NULL AUTO_INCREMENT,
    layout_id   BIGINT UNSIGNED NOT NULL COMMENT 'post ID of vb_layout CPT',
    label       VARCHAR(100)    NOT NULL DEFAULT '',
    pos_x       FLOAT           NOT NULL DEFAULT 0 COMMENT 'percentage X on image',
    pos_y       FLOAT           NOT NULL DEFAULT 0 COMMENT 'percentage Y on image',
    width       FLOAT           NOT NULL DEFAULT 3 COMMENT 'percentage width',
    height      FLOAT           NOT NULL DEFAULT 3 COMMENT 'percentage height',
    spot_type   VARCHAR(20)     NOT NULL DEFAULT 'seat' COMMENT 'seat|table|zone|custom',
    price       DECIMAL(10,2)   NOT NULL DEFAULT 0.00,
    status      VARCHAR(20)     NOT NULL DEFAULT 'open' COMMENT 'open|locked|maintenance',
    color       VARCHAR(7)      NOT NULL DEFAULT '#4CAF50',
    meta_json   LONGTEXT        NULL COMMENT 'extra data as JSON',
    sort_order  INT             NOT NULL DEFAULT 0,
    created_at  DATETIME        NOT NULL DEFAULT CURRENT_TIMESTAMP,
    PRIMARY KEY (id),
    KEY idx_layout (layout_id),
    KEY idx_status (status)
) {$charset};

CREATE TABLE {$bookings} (
    id          BIGINT UNSIGNED NOT NULL AUTO_INCREMENT,
    spot_id     BIGINT UNSIGNED NOT NULL,
    layout_id   BIGINT UNSIGNED NOT NULL,
    customer_name   VARCHAR(200) NOT NULL DEFAULT '',
    customer_email  VARCHAR(200) NOT NULL DEFAULT '',
    customer_phone  VARCHAR(50)  NOT NULL DEFAULT '',
    booking_status  VARCHAR(20)  NOT NULL DEFAULT 'pending' COMMENT 'pending|approved|cancelled',
    notes       TEXT            NULL,
    meta_json   LONGTEXT        NULL,
    created_at  DATETIME        NOT NULL DEFAULT CURRENT_TIMESTAMP,
    updated_at  DATETIME        NOT NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
    PRIMARY KEY (id),
    KEY idx_spot  (spot_id),
    KEY idx_layout(layout_id),
    KEY idx_status(booking_status)
) {$charset};

CREATE TABLE {$spot_types} (
    id          TINYINT UNSIGNED NOT NULL AUTO_INCREMENT,
    name        VARCHAR(20)    NOT NULL DEFAULT '',
    label       VARCHAR(50),
    created_at  DATETIME        NOT NULL DEFAULT CURRENT_TIMESTAMP,
    PRIMARY KEY (id)
) {$charset};

CREATE TABLE {$spot_statuses} (
id          TINYINT UNSIGNED NOT NULL AUTO_INCREMENT,
name        VARCHAR(20)    NOT NULL DEFAULT '',
label       VARCHAR(50),
created_at  DATETIME        NOT NULL DEFAULT CURRENT_TIMESTAMP,
PRIMARY KEY (id)
) {$charset};

CREATE TABLE {$booking_statuses} (
id          TINYINT UNSIGNED NOT NULL AUTO_INCREMENT,
name        VARCHAR(20)    NOT NULL DEFAULT '',
label       VARCHAR(50),
created_at  DATETIME        NOT NULL DEFAULT CURRENT_TIMESTAMP,
PRIMARY KEY (id)
) {$charset};
";


        require_once ABSPATH . 'wp-admin/includes/upgrade.php';
        dbDelta( $sql );

        // Spot types invoegen (alleen als tabel leeg is)

    if ( ! $wpdb->get_var( "SELECT COUNT(*) FROM " . self::spot_types() ) ) {

        $wpdb->insert( self::spot_types(), array( 'name' => 'seat', 'label' => 'Seat' ) );

        $wpdb->insert( self::spot_types(), array( 'name' => 'table', 'label' => 'Table' ) );

        $wpdb->insert( self::spot_types(), array( 'name' => 'zone', 'label' => 'Zone' ) );

        $wpdb->insert( self::spot_types(), array( 'name' => 'custom', 'label' => 'Custom' ) );

    }

    // Spot statuses invoegen (alleen als tabel leeg is)

    if ( ! $wpdb->get_var( "SELECT COUNT(*) FROM " . self::spot_statuses() ) ) {

        $wpdb->insert( self::spot_statuses(), array( 'name' => 'open', 'label' => 'Open' ) );

        $wpdb->insert( self::spot_statuses(), array( 'name' => 'locked', 'label' => 'Locked' ) );

        $wpdb->insert( self::spot_statuses(), array( 'name' => 'maintenance', 'label' => 'Maintenance' ) );

    }

    // Booking statuses invoegen (alleen als tabel leeg is)

    if ( ! $wpdb->get_var( "SELECT COUNT(*) FROM " . self::booking_statuses() ) ) {

        $wpdb->insert( self::booking_statuses(), array( 'name' => 'pending', 'label' => 'Pending' ) );

        $wpdb->insert( self::booking_statuses(), array( 'name' => 'approved', 'label' => 'Approved' ) );

        $wpdb->insert( self::booking_statuses(), array( 'name' => 'cancelled', 'label' => 'Cancelled' ) );

    }

        update_option( 'vb_db_version', VB_VERSION );
    }

    /* ------------------------------------------------------------------ */
    /*  Spot CRUD helpers                                                   */
    /* ------------------------------------------------------------------ */

    public static function get_spots( $layout_id ) {
        global $wpdb;
        return $wpdb->get_results(
            $wpdb->prepare(
                "SELECT * FROM " . self::spots_table() . " WHERE layout_id = %d ORDER BY sort_order ASC, id ASC",
                $layout_id
            )
        );
    }

    public static function upsert_spot( $data ) {
        global $wpdb;
        $table = self::spots_table();

        $defaults = array(
            'layout_id' => 0,
            'label'     => '',
            'pos_x'     => 0,
            'pos_y'     => 0,
            'width'     => 3,
            'height'    => 3,
            'spot_type' => 'seat',
            'price'     => 0,
            'status'    => 'open',
            'color'     => '#4CAF50',
            'meta_json' => null,
            'sort_order'=> 0,
        );
        $data = wp_parse_args( $data, $defaults );

        if ( ! empty( $data['id'] ) ) {
            $id = absint( $data['id'] );
            unset( $data['id'] );
            $wpdb->update( $table, $data, array( 'id' => $id ) );
            return $id;
        }

        $wpdb->insert( $table, $data );
        return $wpdb->insert_id;
    }

    public static function delete_spot( $id ) {
        global $wpdb;
        return $wpdb->delete( self::spots_table(), array( 'id' => absint( $id ) ) );
    }

    /* ------------------------------------------------------------------ */
    /*  Booking helpers                                                     */
    /* ------------------------------------------------------------------ */

    public static function create_booking( $data ) {
        global $wpdb;
        $wpdb->insert( self::bookings_table(), $data );
        return $wpdb->insert_id;
    }

    public static function get_bookings_for_layout( $layout_id, $status = null ) {
        global $wpdb;
        $sql = "SELECT b.*, s.label AS spot_label FROM " . self::bookings_table() . " b
                LEFT JOIN " . self::spots_table() . " s ON b.spot_id = s.id
                WHERE b.layout_id = %d";
        $params = array( $layout_id );

        if ( $status ) {
            $sql .= " AND b.booking_status = %s";
            $params[] = $status;
        }

        $sql .= " ORDER BY b.created_at DESC";

        return $wpdb->get_results( $wpdb->prepare( $sql, ...$params ) );
    }

    public static function update_booking_status( $booking_id, $status ) {
        global $wpdb;
        return $wpdb->update(
            self::bookings_table(),
            array( 'booking_status' => $status ),
            array( 'id' => absint( $booking_id ) )
        );
    }

    /**
     * Get all booked spot IDs for a layout (approved + pending).
     */
    public static function get_booked_spot_ids( $layout_id ) {
        global $wpdb;
        return $wpdb->get_col(
            $wpdb->prepare(
                "SELECT spot_id FROM " . self::bookings_table() . "
                 WHERE layout_id = %d AND booking_status IN ('pending','approved')",
                $layout_id
            )
        );
    }

    /* ------------------------------------------------------------------ */
    /*  Lookup table helpers                                               */
    /* ------------------------------------------------------------------ */

    public static function get_spot_types() {
        global $wpdb;
        return $wpdb->get_results( "SELECT * FROM " . self::spot_types() );
    }

    public static function get_spot_statuses() {
        global $wpdb;
        return $wpdb->get_results( "SELECT * FROM " . self::spot_statuses() );
    }

    public static function get_booking_statuses() {
        global $wpdb;
        return $wpdb->get_results( "SELECT * FROM " . self::booking_statuses() );
    }


}
