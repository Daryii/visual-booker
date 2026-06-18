# 8. Security – XSS preventie

**Bestand:** `includes/class-vb-rest-api.php`

---

## Test 8.1 – XSS in spot label

**Actie:**
- Ga naar de admin builder.
- Maak een nieuwe spot aan.
- Bewerk het label van de spot naar: `<script>alert('XSS')</script>`
- Sla de spot op.
- Open de frontend boekingspagina.

**Verwachting:**
- De API geeft een foutmelding terug: "HTML tags zijn niet toegestaan in het label."
- De spot wordt niet opgeslagen in de database.
- Er verschijnt GEEN alert popup op de frontend.

**Resultaat:** ☐ Geslaagd 

**Opmerking:** De API geeft een foutmelding terug met:
    "code": "invalid_field",
    "message": "HTML tags zijn niet toegestaan in het label.",
    "data": {
        "status": 400
    }
De spot wordt niet opgeslagen en er verschijnt geen popup op de frontend.

---

## Test 8.2 – XSS in boekingsformulier naam

**Actie:**
- Open de frontend boekingspagina.
- Selecteer een spot en klik op "Book Now".
- Vul als naam in: `<img src=x onerror=alert('XSS')>`
- Vul een geldig e-mailadres in en klik op "Confirm Booking".

**Verwachting:**
- De API geeft een foutmelding terug (HTTP 400): "HTML tags zijn niet toegestaan in de naam."
- De foutmelding is zichtbaar in het formulier als rode melding.
- De boeking wordt niet opgeslagen in de database.
- Er verschijnt GEEN alert popup op de frontend.

**Resultaat:** ☐ Mislukt 

**Opmerking:** De test is gelukt maar met een andere foutmelding dan verwacht. In plaats van "HTML tags zijn niet toegestaan" werd de volgende melding teruggegeven:
{
    "code": "invalid_name",
    "message": "Naam moet tussen 2 en 255 tekens zijn.",
    "data": {
        "status": 400
    }
}


