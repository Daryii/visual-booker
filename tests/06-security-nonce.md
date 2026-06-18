# 6. Security – Nonce verificatie

**Bestand:** `includes/class-vb-rest-api.php`
**Endpoint:** `POST /visual-booker/v1/bookings/bulk`

---

## Test 6.1 – Booking request zonder nonce

**Actie:**
- Open de browser console (F12 → Console).
- Voer het volgende uit:

```js
fetch('/wp-json/visual-booker/v1/bookings/bulk', {
    method: 'POST',
    headers: { 'Content-Type': 'application/json' },
    body: JSON.stringify({
        spot_ids: [1],
        layout_id: 7,
        customer_name: 'Test',
        customer_email: 'test@test.nl'
    })
}).then(r => r.json()).then(d => console.log(d));
```

**Verwachting:**
- De request wordt geweigerd met status 403.
- De foutmelding bevat "Invalid nonce" of missing nonce.
- Er wordt geen boeking aangemaakt.

**Resultaat:** ☐ Geslaagd

**Opmerking:** De request wordt geweigerd met statuscode 403. De foutmelding geeft aan dat de nonce ongeldig of ontbrekend is en er wordt geen boeking aangemaakt.
---

## Test 6.2 – Booking request met geldige nonce (via frontend)

**Actie:**
- Maak een normale boeking aan via het frontend formulier.
- Controleer in het Network tabje (F12) dat de `X-WP-Nonce` header wordt meegestuurd.

**Verwachting:**
- De boeking wordt succesvol aangemaakt.
- De `X-WP-Nonce` header is zichtbaar in het Network tabje.

**Resultaat:** ☐ Geslaagd 

**Opmerking:** De boeking is succesvol aangemaakt en de `X-WP-Nonce` header is zichtbaar in het Network tabje.
