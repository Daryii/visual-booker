# Visual Booker – WordPress Booking Plugin (Starter Kit)

This is a starter WordPress plugin that lets you upload any image (a floor plan, a market map, a venue layout) and place bookable spots on it. Visitors can then click on available spots and submit a booking through a simple form.

We built this as a foundation for you to learn from and extend. The core flow works end to end, but there's plenty of room to improve it and add features.

It's inspired by [SeatReg](https://wordpress.org/plugins/seatreg/), but written from scratch.

---

## Getting Started

1. Drop the `visual-booker/` folder into `wp-content/plugins/`.
2. Activate it from the Plugins page in WP Admin.
3. Head to Booking Layouts and create a new one. Give it a title and upload a background image.
4. Use the "Add Spot" button to start placing spots on the image. You can drag them around, resize them, and edit their label, price, color, and type.
5. Hit "Save All Spots" when you're happy with the layout.
6. Copy the shortcode from the sidebar (something like `[visual_booker id="42"]`) and paste it into any page or post.
7. That's it - visitors can now select spots and book them.

---

## How the Plugin is Structured

```
visual-booker/
├── visual-booker.php              Main plugin file. Registers hooks and loads assets.
├── includes/
│   ├── class-vb-db.php            Database tables and all the CRUD functions for spots and bookings.
│   ├── class-vb-post-type.php     The "Booking Layout" custom post type, plus the admin meta boxes.
│   ├── class-vb-rest-api.php      All the REST API endpoints that the front-end and builder talk to.
│   └── class-vb-shortcode.php     The [visual_booker] shortcode that renders the public booking view.
├── templates/
│   └── front-end.php              The HTML template visitors see when the shortcode loads.
├── admin/
│   ├── css/admin.css              Styles for the drag-and-drop builder.
│   └── js/admin.js                All the builder logic: dragging, resizing, editing, saving spots.
├── public/
│   ├── css/public.css             Styles for the front-end booking interface.
│   └── js/public.js               Spot selection, modal, and booking form submission.
└── README.md
```

### A Few Design Notes

**Why custom database tables instead of post meta?** Spots and bookings are structured, queryable data. Custom tables make it much easier to filter, join, and count records compared to the meta table.

**Why percentage-based positioning?** Spot positions are stored as percentages of the image dimensions, so layouts stay responsive across screen sizes without any extra work.

**Why jQuery?** It ships with WordPress, so there are no extra dependencies. You're welcome to swap it out for vanilla JS, React, or Vue as a learning exercise.

**How does the booking flow work?** A visitor selects one or more spots, clicks "Book Now", fills in a short form, and submits. Each selected spot creates a separate booking via the REST API. The admin gets an email notification and can approve or cancel bookings from the layout editor.

---

## Database Tables

The plugin creates two tables on activation:

**wp_vb_spots** stores each bookable spot: its position, size, label, type (seat, table, zone, etc.), price, color, and status (open, locked, or maintenance).

**wp_vb_bookings** stores each booking: which spot, which layout, the customer's name/email/phone, the booking status (pending, approved, or cancelled), and any notes.

---

## REST API

All endpoints live under `/wp-json/visual-booker/v1/`. The public ones don't require authentication. The admin ones check that the user has the `edit_posts` capability.

| Method | Endpoint | Who can use it | What it does |
|---|---|---|---|
| GET | /spots/{layout_id} | Anyone | Returns all spots for a layout, with a flag showing which ones are already booked |
| POST | /spot | Admin | Creates or updates a single spot |
| POST | /spots/bulk | Admin | Saves all spots at once |
| DELETE | /spot/{id} | Admin | Removes a spot |
| POST | /booking | Anyone | Submits a new booking |
| PATCH | /booking/{id}/status | Admin | Changes a booking's status (approve, cancel, etc.) |
| GET | /bookings/{layout_id} | Admin | Lists all bookings for a layout |

---

## Things You Can Build Next

These are roughly ordered from simpler to more involved. Pick whatever interests you.

**Straightforward improvements:**

- Add circle-shaped spots alongside the existing rectangles (hint: it's mostly CSS).
- Send a confirmation email to the customer, not just the admin.
- Add a button to export bookings as a CSV file.
- Let admins define custom legends (like "VIP" in gold, "Regular" in green, "Accessible" in blue) and display them on the front end.
- Auto-number spots when placing them in bulk.

**Medium-effort features:**

- Support multiple rooms or sections per layout, with tabs to switch between them (like SeatReg does).
- Add zoom and pan for large layouts, especially on mobile.
- Let admins define custom form fields per layout (e.g., "T-shirt size" or "Dietary preference"), stored in the booking's meta_json column.
- Set booking limits: max spots per booking, max bookings per email address, or time-based availability windows.
- Use the WordPress Heartbeat API to update spot availability in real time without refreshing the page.
- Add a "Duplicate Layout" button that clones a layout and all its spots.
- Add undo/redo support in the builder.

**Bigger projects:**

- Integrate a payment gateway (Razorpay, Stripe, or WooCommerce) so bookings are only confirmed after payment.
- Rebuild the front end in React or Vue instead of jQuery.
- Replace the DOM-based builder with an HTML5 Canvas or Fabric.js implementation; this matters when a layout has hundreds of spots.
- Create pre-built templates (theater, classroom, bus, restaurant) that users can start from instead of building from scratch.
- Generate QR codes on booking confirmation that can be scanned for check-in.
- Add a calendar/scheduling mode so spots can be booked for specific dates and times.
- Build a Gutenberg block as an alternative to the shortcode.
- Make the whole booking flow keyboard-navigable and screen-reader friendly.

---

## Coding Guidelines

A few things to keep in mind while working on this:

- Always sanitize input and escape output. Use `sanitize_text_field()`, `sanitize_email()`, `esc_html()`, `esc_attr()`, and so on. Never trust what comes from the browser.
- Use `$wpdb->prepare()` for every database query that includes user-supplied values. No exceptions.
- Prefix everything (functions, classes, CSS classes, JS variables) with `vb_` or `vb-` to avoid collisions with other plugins.
- Wrap all user-facing strings in `__()` or `esc_html__()` with the `'visual-booker'` text domain so the plugin can be translated.
- Keep JS and CSS in their own files. No inline scripts or styles.
- Comment your code, especially anything non-obvious in the builder or API logic.

---

## Testing and Debugging

Set up a local WordPress install (LocalWP, XAMPP, or Docker all work fine). Activate the plugin, create a layout with a handful of spots, embed it on a page, and try booking from an incognito window.

Turn on `WP_DEBUG` and `WP_DEBUG_LOG` in your `wp-config.php` so you can catch errors early. Use the browser's DevTools Network tab to watch REST API requests go back and forth. You can also hit the API directly in your browser (for example, `GET /wp-json/visual-booker/v1/spots/42`) to see what the data looks like.

If something looks wrong with the data, check the `wp_vb_spots` and `wp_vb_bookings` tables in phpMyAdmin.

---

## One More Thing

The starter uses Indian Rupees for price display. If you need a different currency, search for the rupee sign and `en-IN` in `public/js/public.js` and update them. Ideally, you'd build a settings page where the admin can pick their currency - that's another good task to take on.
