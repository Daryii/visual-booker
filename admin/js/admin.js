/**
 * Visual Booker – Admin Map Builder
 *
 * Handles: image picker, spot placement, drag/resize, spot editing,
 *          bulk save via REST API, and booking status management.
 */
(function ($) {
    'use strict';

    const API     = vbAdmin.restUrl;
    const NONCE   = vbAdmin.nonce;
    const $canvas = $('#vb-canvas');
    const layoutId = $canvas.data('layout-id');

    let spots = [];          // local state
    let selectedSpotId = null;
    let isDragging = false;
    let gridSize = 5; // raster grootte in procenten

    /* ================================================================== */
    /*  1. Image Picker (WP Media)                                         */
    /* ================================================================== */
    $('#vb-pick-image').on('click', function (e) {
        e.preventDefault();
        const frame = wp.media({
            title: 'Choose Layout Image',
            button: { text: 'Use this image' },
            multiple: false,
        });

        frame.on('select', function () {
            const attachment = frame.state().get('selection').first().toJSON();
            $('#vb-layout-image').val(attachment.url);
            $('#vb-layout-image-id').val(attachment.id);

            // Replace canvas content
            $canvas.find('#vb-bg-image, #vb-placeholder').remove();
            $canvas.prepend(
                $('<img>', {
                    id: 'vb-bg-image',
                    src: attachment.url,
                    draggable: false,
                })
            );
            $('#vb-remove-image').show();
        });

        frame.open();
    });

    $('#vb-remove-image').on('click', function () {
        $('#vb-layout-image, #vb-layout-image-id').val('');
        $canvas.find('#vb-bg-image').remove();
        if (!$canvas.find('#vb-placeholder').length) {
            $canvas.append('<div id="vb-placeholder"><p>← Choose a background image to start.</p></div>');
        }
        $(this).hide();
    });

    /* ================================================================== */
    /*  2. Load existing spots                                             */
    /* ================================================================== */
    function loadSpots() {
        if (!layoutId) return;

        $.getJSON(API + 'spots/' + layoutId, function (data) {
            spots = data;
            renderAllSpots();
        });
    }

    /* ================================================================== */
    /*  3. Render spots on canvas                                          */
    /* ================================================================== */
    function renderAllSpots() {
        $canvas.find('.vb-spot').remove();
        spots.forEach(function (spot) {
            renderSpot(spot);
        });
    }

    function renderSpot(spot) {
        const shapeClass = spot.shape === 'circle' ? ' vb-spot--circle' : '';
        const $spot = $('<div>', {
            class: 'vb-spot' + shapeClass,
            'data-id': spot.id,
        })
            .css({
                left: spot.pos_x + '%',
                top: spot.pos_y + '%',
                width: spot.width + '%',
                height: spot.height + '%',
                backgroundColor: spot.color || '#4CAF50',
            })
            .append($('<span>', { class: 'vb-spot-label', text: spot.label || '' }))
            .append($('<div>', { class: 'vb-resize-handle' }));

        // Click to select
        $spot.on('click', function (e) {
            if (isDragging) return;
            e.stopPropagation();
            selectSpot(spot.id);
        });

        // Make draggable
        makeDraggable($spot, spot);

        // Make resizable via handle
        makeResizable($spot, spot);

        $canvas.append($spot);
    }

    /* ================================================================== */
    /*  4. Dragging                                                        */
    /* ================================================================== */
    function makeDraggable($el, spot) {
        let startX, startY, startLeft, startTop;

        $el.on('mousedown', function (e) {
            if ($(e.target).hasClass('vb-resize-handle')) return;
            e.preventDefault();
            isDragging = false;

            const canvasRect = $canvas[0].getBoundingClientRect();
            const elRect = $el[0].getBoundingClientRect();

            startX = e.clientX;
            startY = e.clientY;
            startLeft = ((elRect.left - canvasRect.left) / canvasRect.width) * 100;
            startTop  = ((elRect.top  - canvasRect.top)  / canvasRect.height) * 100;

            function onMove(ev) {
                isDragging = true;
                const dx = ((ev.clientX - startX) / canvasRect.width) * 100;
                const dy = ((ev.clientY - startY) / canvasRect.height) * 100;

                let newX = Math.max(0, Math.min(100 - parseFloat(spot.width), startLeft + dx));
                let newY = Math.max(0, Math.min(100 - parseFloat(spot.height), startTop + dy));

                // Snap naar grid als het raster actief is
                if ($canvas.hasClass('vb-grid-active')) {
                    newX = Math.round(newX / gridSize) * gridSize;
                    newY = Math.round(newY / gridSize) * gridSize;
                }

                $el.css({ left: newX + '%', top: newY + '%' });
                spot.pos_x = parseFloat(newX.toFixed(2));
                spot.pos_y = parseFloat(newY.toFixed(2));
            }

            function onUp() {
                $(document).off('mousemove', onMove).off('mouseup', onUp);
                // Delay resetting isDragging so the click handler can check it
                setTimeout(function () { isDragging = false; }, 50);
            }

            $(document).on('mousemove', onMove).on('mouseup', onUp);
        });
    }

    /* ================================================================== */
    /*  5. Resizing (via bottom-right handle)                              */
    /* ================================================================== */
    function makeResizable($el, spot) {
        $el.find('.vb-resize-handle').on('mousedown', function (e) {
            e.preventDefault();
            e.stopPropagation();

            const canvasRect = $canvas[0].getBoundingClientRect();
            const startX = e.clientX;
            const startY = e.clientY;
            const startW = parseFloat(spot.width);
            const startH = parseFloat(spot.height);

            function onMove(ev) {
                const dx = ((ev.clientX - startX) / canvasRect.width) * 100;
                const dy = ((ev.clientY - startY) / canvasRect.height) * 100;

                let newW = Math.max(1, startW + dx);
                let newH = Math.max(1, startH + dy);

                $el.css({ width: newW + '%', height: newH + '%' });
                spot.width  = parseFloat(newW.toFixed(2));
                spot.height = parseFloat(newH.toFixed(2));
            }

            function onUp() {
                $(document).off('mousemove', onMove).off('mouseup', onUp);
            }

            $(document).on('mousemove', onMove).on('mouseup', onUp);
        });
    }

    /* ================================================================== */
    /*  6. Select / edit spot                                              */
    /* ================================================================== */
    function selectSpot(id) {
        selectedSpotId = id;
        const spot = spots.find(s => s.id == id);
        if (!spot) return;

        // Highlight
        $canvas.find('.vb-spot').removeClass('vb-spot--selected');
        $canvas.find('.vb-spot[data-id="' + id + '"]').addClass('vb-spot--selected');

        // Populate editor
        $('#vb-spot-label').val(spot.label);
        $('#vb-spot-type').val(spot.spot_type_id || 1);
        $('#vb-spot-price').val(spot.price || 0);
        $('#vb-spot-color').val(spot.color || '#4CAF50');
        $('#vb-spot-status').val(spot.status_id || 1);
        $('#vb-spot-editor').slideDown(200);
    }

    // Deselect on canvas click
    $canvas.on('click', function () {
        selectedSpotId = null;
        $canvas.find('.vb-spot').removeClass('vb-spot--selected');
        $('#vb-spot-editor').slideUp(200);
    });

    // Update spot properties
    $('#vb-spot-update').on('click', function () {
        if (!selectedSpotId) return;
        const spot = spots.find(s => s.id == selectedSpotId);
        if (!spot) return;

        spot.label        = $('#vb-spot-label').val();
        spot.spot_type_id = parseInt($('#vb-spot-type').val());
        spot.price        = parseFloat($('#vb-spot-price').val()) || 0;
        spot.color        = $('#vb-spot-color').val();
        spot.status_id    = parseInt($('#vb-spot-status').val());

        // Re-render this spot
        const $el = $canvas.find('.vb-spot[data-id="' + spot.id + '"]');
        $el.css('backgroundColor', spot.color);
        $el.find('.vb-spot-label').text(spot.label);
        $el.toggleClass('vb-spot--circle', spot.shape === 'circle');

        // Auto-save this spot
        saveSpot(spot);
    });

    // Delete spot
    $('#vb-spot-delete').on('click', function () {
        if (!selectedSpotId) return;
        if (!confirm('Delete this spot?')) return;

        $.ajax({
            url: API + 'spot/' + selectedSpotId,
            method: 'DELETE',
            beforeSend: function (xhr) {
                xhr.setRequestHeader('X-WP-Nonce', NONCE);
            },
            success: function () {
                $canvas.find('.vb-spot[data-id="' + selectedSpotId + '"]').remove();
                spots = spots.filter(s => s.id != selectedSpotId);
                selectedSpotId = null;
                $('#vb-spot-editor').slideUp(200);
            },
        });
    });

    /* ================================================================== */
    /*  7. Add new spot                                                    */
    /* ================================================================== */
    $('#vb-add-spot').on('click', function () {
        if (!$canvas.find('#vb-bg-image').length) {
            alert('Please choose a background image first.');
            return;
        }
        $('#vb-shape-picker').toggle();
    });

    $('#vb-shape-picker').on('click', 'button[data-shape]', function () {
        const shape = $(this).data('shape');
        $('#vb-shape-picker').hide();

        const newSpot = {
            layout_id: layoutId,
            label: 'S' + (spots.length + 1),
            pos_x: 10 + Math.random() * 20,
            pos_y: 10 + Math.random() * 20,
            width: 3,
            height: 3,
            spot_type_id: 1,
            price: 0,
            status_id: 1,
            color: '#4CAF50',
            shape: shape,
        };

        // Save to DB first
        $.ajax({
            url: API + 'spot',
            method: 'POST',
            contentType: 'application/json',
            data: JSON.stringify(newSpot),
            beforeSend: function (xhr) {
                xhr.setRequestHeader('X-WP-Nonce', NONCE);
            },
            success: function (res) {
                newSpot.id = res.id;
                spots.push(newSpot);
                renderSpot(newSpot);
                selectSpot(newSpot.id);
                showStatus('Spot added ✓');
            },
        });
    });

    /* ================================================================== */
    /*  8. Save single / bulk                                              */
    /* ================================================================== */
    function saveSpot(spot) {
        $.ajax({
            url: API + 'spot',
            method: 'POST',
            contentType: 'application/json',
            data: JSON.stringify(spot),
            beforeSend: function (xhr) {
                xhr.setRequestHeader('X-WP-Nonce', NONCE);
            },
            success: function () {
                showStatus('Saved ✓');
            },
        });
    }

    $('#vb-save-spots').on('click', function () {
        showStatus('Saving…');
        $.ajax({
            url: API + 'spots/bulk',
            method: 'POST',
            contentType: 'application/json',
            data: JSON.stringify({ spots: spots }),
            beforeSend: function (xhr) {
                xhr.setRequestHeader('X-WP-Nonce', NONCE);
            },
            success: function () {
                showStatus('All spots saved ✓');
            },
            error: function () {
                showStatus('Error saving spots ✗');
            },
        });
    });

    function showStatus(msg) {
        const $s = $('#vb-save-status');
        $s.text(msg);
        clearTimeout($s.data('timer'));
        $s.data('timer', setTimeout(function () { $s.text(''); }, 3000));
    }

    /* ================================================================== */
    /*  9. Booking actions (approve / cancel)                              */
    /* ================================================================== */
    $(document).on('click', '.vb-booking-action', function () {
        const $btn      = $(this);
        const bookingId = $btn.data('id');
        const action    = $btn.data('action');

        $.ajax({
            url: API + 'booking/' + bookingId + '/status',
            method: 'PATCH',
            contentType: 'application/json',
            data: JSON.stringify({ status: action }),
            beforeSend: function (xhr) {
                xhr.setRequestHeader('X-WP-Nonce', NONCE);
            },
            success: function () {
                const $row = $btn.closest('tr');
                $row.find('.vb-status')
                    .removeClass('vb-status--pending vb-status--approved vb-status--cancelled')
                    .addClass('vb-status--' + action)
                    .text(action.charAt(0).toUpperCase() + action.slice(1));

                // Update action buttons
                const $td = $btn.closest('td');
                $td.empty();
                if (action === 'approved') {
                    $td.append(
                        $('<button>', {
                            class: 'button button-small vb-booking-action',
                            'data-action': 'cancelled',
                            'data-id': bookingId,
                            text: '❌ Cancel',
                        })
                    );
                }

                if (action === 'cancelled') {
                    setTimeout(function () {
                        $row.fadeOut(300, function () { $(this).remove(); });
                    }, 1500);
                }
            },
        });
    });

    $('#vb-toggle-grid').on('click', function () {
        $canvas.toggleClass('vb-grid-active');
        $(this).toggleClass('vb-grid-is-active');
    });
    
    $('#vb-grid-size').on('change', function () {
        gridSize = parseInt($(this).val());
        $canvas.css('--grid-size', gridSize + '%');
    });

    /* ================================================================== */
    /*  Init                                                               */
    /* ================================================================== */

    const $bgImage = $('#vb-bg-image');

    if ($bgImage.length && $bgImage[0].complete) {
        // afbeelding was al geladen (cache)
        loadSpots();
    } else if ($bgImage.length) {
        // afbeelding is nog aan het laden
        $bgImage.on('load', function () {
            loadSpots();
        });
    } else {
        // geen afbeelding aanwezig
        loadSpots();
    }


    
})(jQuery);
