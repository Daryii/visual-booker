/**
 * Visual Booker – Front-end Booking Interface
 *
 * Handles: loading spots, selection toggle, booking form submission.
 * Each shortcode instance is scoped by layout ID.
 */
(function ($) {
    'use strict';

    const API      = vbPublic.restUrl;
    const NONCE    = vbPublic.nonce;
    const layoutId = vbPublic.layoutId;

    const $wrapper      = $('.vb-wrapper[data-layout-id="' + layoutId + '"]');
    const $canvas       = $wrapper.find('.vb-canvas');
    const $selectionBar = $wrapper.find('.vb-selection-bar');
    const $modal        = $wrapper.find('.vb-modal');
    const $form         = $wrapper.find('.vb-booking-form');

    let spots    = [];
    let selected = [];  // array of spot objects

    /* ================================================================== */
    /*  1. Load spots from REST API                                        */
    /* ================================================================== */
    function loadSpots() {
        $.getJSON(API + 'spots/' + layoutId, function (data) {
            spots = data;
            renderSpots();
        });
    }

    /* ================================================================== */
    /*  2. Render spots on canvas                                          */
    /* ================================================================== */
    function renderSpots() {
        $canvas.find('.vb-spot-public').remove();

        spots.forEach(function (spot) {
            const isBooked = spot.booked;
            const isLocked = spot.status === 'locked' || spot.status === 'maintenance';

            let stateClass = 'vb-spot--open';
            if (isBooked) stateClass = 'vb-spot--booked';
            else if (isLocked) stateClass = 'vb-spot--locked';

            const priceText = parseFloat(spot.price) > 0
                ? ' – ₹' + parseFloat(spot.price).toLocaleString('en-IN')
                : '';

            const $spot = $('<div>', {
                class: 'vb-spot-public ' + stateClass,
                'data-id': spot.id,
            })
                .css({
                    left: spot.pos_x + '%',
                    top: spot.pos_y + '%',
                    width: spot.width + '%',
                    height: spot.height + '%',
                    backgroundColor: isBooked ? undefined : (spot.color || '#4CAF50'),
                })
                .append($('<span>', { class: 'vb-spot-label', text: spot.label }))
                .append(
                    $('<div>', {
                        class: 'vb-tooltip',
                        text: (spot.label || 'Spot #' + spot.id) + priceText +
                              (isBooked ? ' (Booked)' : isLocked ? ' (Unavailable)' : ''),
                    })
                );

            // Click handler for selectable spots
            if (!isBooked && !isLocked) {
                $spot.on('click', function () {
                    toggleSelection(spot, $spot);
                });
            }

            $canvas.append($spot);
        });
    }

    /* ================================================================== */
    /*  3. Selection management                                            */
    /* ================================================================== */
    function toggleSelection(spot, $el) {
        const idx = selected.findIndex(s => s.id == spot.id);

        if (idx > -1) {
            // Deselect
            selected.splice(idx, 1);
            $el.removeClass('vb-spot--selected').addClass('vb-spot--open');
        } else {
            // Select
            selected.push(spot);
            $el.removeClass('vb-spot--open').addClass('vb-spot--selected');
        }

        updateSelectionBar();
    }

    function updateSelectionBar() {
        if (selected.length === 0) {
            $selectionBar.slideUp(200);
            return;
        }

        const total = selected.reduce(function (sum, s) {
            return sum + (parseFloat(s.price) || 0);
        }, 0);

        $selectionBar.find('.vb-selected-count').text(selected.length);
        $selectionBar.find('.vb-selected-total').text('₹' + total.toLocaleString('en-IN'));
        $selectionBar.slideDown(200);
    }

    /* ================================================================== */
    /*  4. Booking modal                                                   */
    /* ================================================================== */
    $wrapper.on('click', '.vb-open-booking-form', function () {
        if (selected.length === 0) return;

        // Build summary
        const $summary = $modal.find('.vb-selected-summary').empty();
        let total = 0;

        selected.forEach(function (s) {
            const price = parseFloat(s.price) || 0;
            total += price;
            $summary.append(
                $('<div>', { class: 'vb-summary-row' }).append(
                    $('<span>').text(s.label || 'Spot #' + s.id),
                    $('<span>').text('₹' + price.toLocaleString('en-IN'))
                )
            );
        });

        $summary.append(
            $('<div>', { class: 'vb-summary-row' }).append(
                $('<span>').text('Total'),
                $('<span>').text('₹' + total.toLocaleString('en-IN'))
            )
        );

        $modal.show();
    });

    // Close modal
    $wrapper.on('click', '.vb-modal-close, .vb-modal-close-btn, .vb-modal-overlay', function () {
        $modal.hide();
    });

    /* ================================================================== */
    /*  5. Form submission                                                 */
    /* ================================================================== */
    $form.on('submit', function (e) {
        e.preventDefault();

        const $msg = $form.find('.vb-form-message').hide();
        const $btn = $form.find('button[type="submit"]').prop('disabled', true).text('Booking…');

        const customerName  = $form.find('[name="customer_name"]').val().trim();
        const customerEmail = $form.find('[name="customer_email"]').val().trim();
        const customerPhone = $form.find('[name="customer_phone"]').val().trim();
        const notes         = $form.find('[name="notes"]').val().trim();

        if (!customerName || !customerEmail) {
            showFormMessage('Please fill in all required fields.', 'error');
            $btn.prop('disabled', false).text('Confirm Booking');
            return;
        }

        // Submit bookings sequentially for each selected spot
        let completed = 0;
        let errors    = [];

        selected.forEach(function (spot) {
            $.ajax({
                url: API + 'booking',
                method: 'POST',
                contentType: 'application/json',
                data: JSON.stringify({
                    spot_id: spot.id,
                    layout_id: layoutId,
                    customer_name: customerName,
                    customer_email: customerEmail,
                    customer_phone: customerPhone,
                    notes: notes,
                }),
                beforeSend: function (xhr) {
                    xhr.setRequestHeader('X-WP-Nonce', NONCE);
                },
                success: function () {
                    completed++;
                    checkComplete();
                },
                error: function (xhr) {
                    const res = xhr.responseJSON;
                    errors.push((spot.label || 'Spot #' + spot.id) + ': ' + (res?.message || 'Error'));
                    completed++;
                    checkComplete();
                },
            });
        });

        function checkComplete() {
            if (completed < selected.length) return;

            $btn.prop('disabled', false).text('Confirm Booking');

            if (errors.length === 0) {
                showFormMessage(
                    '🎉 Booking confirmed! You will receive a confirmation email shortly.',
                    'success'
                );
                $form[0].reset();
                selected = [];
                updateSelectionBar();

                // Reload spots to reflect new bookings
                setTimeout(function () {
                    $modal.hide();
                    loadSpots();
                }, 2000);
            } else {
                showFormMessage('Some bookings had issues: ' + errors.join('; '), 'error');
                // Reload anyway to update availability
                loadSpots();
            }
        }
    });

    function showFormMessage(text, type) {
        $form.find('.vb-form-message')
            .removeClass('vb-msg-success vb-msg-error')
            .addClass('vb-msg-' + type)
            .text(text)
            .show();
    }

    /* ================================================================== */
    /*  Init                                                               */
    /* ================================================================== */
    loadSpots();

})(jQuery);
