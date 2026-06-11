# 8. Security – XSS preventie

**Bestand:** `includes/class-vb-rest-api.php`

---

## Test 8.1 – XSS in spot label

**Actie:**
- Ga naar de admin builder.
- Maak een spot aan met het label: `<script>alert('XSS')</script>`
- Sla de spot op.
- Open de frontend boekingspagina.

**Verwachting:**
- Er verschijnt GEEN alert popup.
- De API geeft een foutmelding terug: HTML tags zijn niet toegestaan.
- In de HTML broncode is het label ge-escaped.

**Resultaat:** ☐ Geslaagd ☐ Mislukt

**Opmerking:**

---

## Test 8.2 – XSS in boekingsformulier naam

**Actie:**
- Open de frontend boekingspagina.
- Selecteer een spot en klik op "Book Now".
- Vul als naam in: `<img src=x onerror=alert('XSS')>`
- Vul een geldig e-mailadres in en klik op "Confirm Booking".
- Ga naar de admin en bekijk de boeking.

**Verwachting:**
- Er verschijnt GEEN alert popup, niet op de frontend en niet in de admin.
- De API geeft een foutmelding terug: HTML tags zijn niet toegestaan.

**Resultaat:** ☐ Geslaagd ☐ Mislukt

**Opmerking:**
