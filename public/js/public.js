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
    const currencySymbol = vbPublic.currencySymbol;

    let spots    = [];
    let selected = [];  // array of spot objects

    let zoomLevel   = 1;
    const ZOOM_MIN  = 0.5;
    const ZOOM_MAX  = 3;
    const ZOOM_STEP = 0.25;

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
            const openStatus = vbPublic.spotStatuses[0].name;
            const isLocked = spot.status !== openStatus;
            

            let stateClass = 'vb-spot--open';
            if (isBooked) stateClass = 'vb-spot--booked';
            else if (isLocked) stateClass = 'vb-spot--locked';

            const priceText = parseFloat(spot.price) > 0
                ? ' - ' + currencySymbol + parseFloat(spot.price).toLocaleString('nl-NL')
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

            $spot.on('mouseenter', function () {
                const spotTop = this.getBoundingClientRect().top;
                const containerTop = $wrapper.find('.vb-canvas-container')[0].getBoundingClientRect().top;
                $(this).toggleClass('vb-spot--tooltip-below', spotTop - containerTop < 50);
            });

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
        $selectionBar.find('.vb-selected-total').text( currencySymbol + total.toLocaleString('nl-NL'));
        $selectionBar.slideDown(200);
    }

    /* ================================================================== */
    /*  4. Zoom (VB-72)                                                    */
    /* ================================================================== */
    function applyZoom( newLevel ) {
        zoomLevel = Math.max( ZOOM_MIN, Math.min( ZOOM_MAX, newLevel ) );
        $canvas.css( 'width', ( zoomLevel * 100 ) + '%' );
        $wrapper.find( '.vb-zoom-level' ).text( Math.round( zoomLevel * 100 ) + '%' );
    }

    $wrapper.find( '[id^="vb-zoom-in-"]' ).on( 'click', function () { applyZoom( zoomLevel + ZOOM_STEP ); } );
    $wrapper.find( '[id^="vb-zoom-out-"]' ).on( 'click', function () { applyZoom( zoomLevel - ZOOM_STEP ); } );
    $wrapper.find( '[id^="vb-zoom-reset-"]' ).on( 'click', function () { applyZoom( 1 ); } );

    /* ================================================================== */
    /*  5. Pan — click and drag to scroll the map (VB-72)                  */
    /* ================================================================== */
    (function setupPan() {
        const $container = $wrapper.find( '.vb-canvas-container' );
        let isPanning = false, panStartX = 0, panStartY = 0, scrollStartX = 0, scrollStartY = 0;

        $container.on( 'mousedown', function ( e ) {
            if ( $( e.target ).closest( '.vb-spot-public' ).length ) return;
            isPanning    = true;
            panStartX    = e.clientX;
            panStartY    = e.clientY;
            scrollStartX = $container[0].scrollLeft;
            scrollStartY = $container[0].scrollTop;
            $canvas.addClass( 'vb-is-panning' );
            e.preventDefault();
        } );

        $( document ).on( 'mousemove.vbPan', function ( e ) {
            if ( ! isPanning ) return;
            $container[0].scrollLeft = scrollStartX - ( e.clientX - panStartX );
            $container[0].scrollTop  = scrollStartY - ( e.clientY - panStartY );
        } );

        $( document ).on( 'mouseup.vbPan', function () {
            isPanning = false;
            $canvas.removeClass( 'vb-is-panning' );
        } );

        // Touch support (mobile)
        $container.on( 'touchstart', function ( e ) {
            if ( e.touches.length !== 1 ) return;
            const t    = e.touches[0];
            isPanning    = true;
            panStartX    = t.clientX;
            panStartY    = t.clientY;
            scrollStartX = $container[0].scrollLeft;
            scrollStartY = $container[0].scrollTop;
        } );

        $container.on( 'touchmove', function ( e ) {
            if ( ! isPanning || e.touches.length !== 1 ) return;
            const t = e.touches[0];
            $container[0].scrollLeft = scrollStartX - ( t.clientX - panStartX );
            $container[0].scrollTop  = scrollStartY - ( t.clientY - panStartY );
        } );

        $container.on( 'touchend', function () { isPanning = false; } );
    }());

    /* ================================================================== */
    /*  6. Booking modal                                                   */
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
                    $('<span>').text(currencySymbol + price.toLocaleString('nl-NL'))
                )
            );
        });

        $summary.append(
            $('<div>', { class: 'vb-summary-row' }).append(
                $('<span>').text('Total'),
                $('<span>').text(currencySymbol + total.toLocaleString('nl-NL'))
            )
        );

        $modal.show();
    });

    // Close modal
    $wrapper.on('click', '.vb-modal-close, .vb-modal-close-btn, .vb-modal-overlay', function () {
        $modal.hide();
    });

    /* ================================================================== */
    /*  7. Form submission                                                 */
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

        // Stuur alle spots in één API call (VB-99)
        $.ajax({
            url: API + 'bookings/bulk',
            method: 'POST',
            contentType: 'application/json',
            data: JSON.stringify({
                spot_ids: selected.map(function (s) { return s.id; }),
                layout_id: layoutId,
                customer_name: customerName,
                customer_email: customerEmail,
                customer_phone: customerPhone,
                notes: notes,
            }),
            beforeSend: function (xhr) {
                xhr.setRequestHeader('X-WP-Nonce', NONCE);
            },
            success: function (res) {
                $btn.prop('disabled', false).text('Confirm Booking');

                showFormMessage(
                    '🎉 Boeking bevestigd! Je ontvangt een bevestigingsmail.',
                    'success'
                );
                $form[0].reset();
                selected = [];
                updateSelectionBar();

                setTimeout(function () {
                    $modal.hide();
                    loadSpots();
                }, 2000);
            },
            error: function (xhr) {
                $btn.prop('disabled', false).text('Confirm Booking');
                const res = xhr.responseJSON;
                showFormMessage(res?.message || 'Er is iets misgegaan. Probeer het opnieuw.', 'error');
                loadSpots();
            },
        });
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
