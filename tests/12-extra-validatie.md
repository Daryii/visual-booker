# 12. Boeking aanmaken – Extra validatie

**Bestand:** `includes/class-vb-rest-api.php`

---

## Test 12.1 – Al geboekte spot opnieuw boeken

**Endpoint:** `POST /visual-booker/v1/bookings/bulk`

**Actie:**
- Zoek een spot ID dat al geboekt is (rode spot op de frontend).
- Stuur via de browser console een request:

```js
fetch('/wp-json/visual-booker/v1/bookings/bulk', {
    method: 'POST',
    headers: {
        'Content-Type': 'application/json',
        'X-WP-Nonce': vbPublic.nonce
    },
    body: JSON.stringify({
        spot_ids: [GEBOEKTE_SPOT_ID],
        layout_id: 7,
        customer_name: 'Jan de Vries',
        customer_email: 'test@test.nl'
    })
}).then(r => r.json()).then(d => console.log(d));
```

**Verwachting:**
- De API geeft een foutmelding terug: "Geen van de geselecteerde spots kon worden geboekt. Ze bestaan niet of zijn al bezet."
- Er wordt geen nieuwe boeking aangemaakt.

**Resultaat:** ☐ Geslaagd ☐ Mislukt

**Opmerking:**

---

## Test 12.2 – Ongeldige status bij boeking bijwerken

**Endpoint:** `PATCH /visual-booker/v1/booking/{id}/status`

**Actie:**
- Open de browser console op de **admin builder pagina** (niet de frontend).
- Voer het volgende uit:

```js
fetch('/wp-json/visual-booker/v1/booking/1/status', {
    method: 'PATCH',
    headers: {
        'Content-Type': 'application/json',
        'X-WP-Nonce': vbAdmin.nonce
    },
    body: JSON.stringify({ status: 'ongeldig' })
}).then(r => r.json()).then(d => console.log(d));
```

**Verwachting:**
- De API geeft een foutmelding terug: ongeldige statuswaarde.
- De boeking status verandert niet.

**Resultaat:** ☐ Geslaagd ☐ Mislukt

**Opmerking:**

---

## Test 12.3 – Alle spots opslaan via bulk endpoint

**Endpoint:** `POST /visual-booker/v1/spots/bulk`

**Actie:**
- Ga naar de admin builder.
- Bewerk de positie of label van meerdere spots.
- Klik op "Save All Spots".
- Ververs de pagina.

**Verwachting:**
- Alle spots worden opgeslagen.
- De statusbalk toont "All spots saved ✓".
- Na verversen zijn alle wijzigingen zichtbaar.

**Resultaat:** ☐ Geslaagd ☐ Mislukt

**Opmerking:**
