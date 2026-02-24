<?php
/**
 * Front-end booking template.
 *
 * Variables available: $layout_id, $image_url, $layout (WP_Post)
 */
if ( ! defined( 'ABSPATH' ) ) exit;
?>
<div class="vb-wrapper" data-layout-id="<?php echo esc_attr( $layout_id ); ?>">

    <!-- Header -->
    <div class="vb-header">
        <h3 class="vb-title"><?php echo esc_html( $layout->post_title ); ?></h3>
        <div class="vb-legend">
            <span class="vb-legend-item"><span class="vb-dot vb-dot--open"></span> Available</span>
            <span class="vb-legend-item"><span class="vb-dot vb-dot--booked"></span> Booked</span>
            <span class="vb-legend-item"><span class="vb-dot vb-dot--selected"></span> Selected</span>
            <span class="vb-legend-item"><span class="vb-dot vb-dot--locked"></span> Unavailable</span>
        </div>
    </div>

    <!-- Map / Image canvas -->
    <div class="vb-canvas-container">
        <div class="vb-canvas" id="vb-public-canvas-<?php echo esc_attr( $layout_id ); ?>">
            <?php if ( $image_url ) : ?>
                <img src="<?php echo esc_url( $image_url ); ?>" class="vb-bg-image" draggable="false" alt="<?php echo esc_attr( $layout->post_title ); ?>" />
            <?php endif; ?>
            <!-- Spots rendered by JS -->
        </div>
    </div>

    <!-- Selection info bar -->
    <div class="vb-selection-bar" id="vb-selection-bar-<?php echo esc_attr( $layout_id ); ?>" style="display:none;">
        <div class="vb-selection-info">
            <strong>Selected:</strong>
            <span class="vb-selected-count">0</span> spot(s)
            &nbsp;|&nbsp;
            <strong>Total:</strong>
            <span class="vb-selected-total">₹0</span>
        </div>
        <button type="button" class="vb-btn vb-btn-primary vb-open-booking-form">
            Book Now →
        </button>
    </div>

    <!-- Booking form modal -->
    <div class="vb-modal" id="vb-modal-<?php echo esc_attr( $layout_id ); ?>" style="display:none;">
        <div class="vb-modal-overlay"></div>
        <div class="vb-modal-content">
            <button type="button" class="vb-modal-close">&times;</button>
            <h3>Complete Your Booking</h3>

            <div class="vb-selected-summary">
                <!-- Filled by JS -->
            </div>

            <form class="vb-booking-form" id="vb-form-<?php echo esc_attr( $layout_id ); ?>">
                <div class="vb-form-group">
                    <label for="vb-name-<?php echo esc_attr( $layout_id ); ?>">Full Name <span class="required">*</span></label>
                    <input type="text" id="vb-name-<?php echo esc_attr( $layout_id ); ?>" name="customer_name" required />
                </div>
                <div class="vb-form-group">
                    <label for="vb-email-<?php echo esc_attr( $layout_id ); ?>">Email <span class="required">*</span></label>
                    <input type="email" id="vb-email-<?php echo esc_attr( $layout_id ); ?>" name="customer_email" required />
                </div>
                <div class="vb-form-group">
                    <label for="vb-phone-<?php echo esc_attr( $layout_id ); ?>">Phone</label>
                    <input type="tel" id="vb-phone-<?php echo esc_attr( $layout_id ); ?>" name="customer_phone" />
                </div>
                <div class="vb-form-group">
                    <label for="vb-notes-<?php echo esc_attr( $layout_id ); ?>">Notes</label>
                    <textarea id="vb-notes-<?php echo esc_attr( $layout_id ); ?>" name="notes" rows="3"></textarea>
                </div>

                <div class="vb-form-actions">
                    <button type="submit" class="vb-btn vb-btn-primary">Confirm Booking</button>
                    <button type="button" class="vb-btn vb-modal-close-btn">Cancel</button>
                </div>

                <div class="vb-form-message" style="display:none;"></div>
            </form>
        </div>
    </div>
</div>
