# 2. Boeking aanmaken – E-mail validatie

**Bestand:** `includes/class-vb-rest-api.php`
**Endpoint:** `POST /visual-booker/v1/bookings/bulk`

---

## Test 2.1 – Ongeldig e-mailadres

**Actie:**
- Open de frontend boekingspagina.
- Selecteer een beschikbare spot en klik op "Book Now".
- Vul `"Jan de Vries"` in als naam en `"geen-email"` als e-mailadres.
- Klik op "Confirm Booking".

**Verwachting:**
- De browser toont een validatiefout: "Voer een geldig e-mailadres in" (HTML5 validatie).
- De REST API wordt niet aangeroepen.
- Er wordt geen boeking aangemaakt in de database.
- Er wordt geen e-mail verstuurd.

**Resultaat:** ☐ Geslaagd 

**Opmerking:** Het formulier werd niet verstuurd. De browser toonde de melding "Vul dit veld in" direct onder het emailveld. Er werd geen boeking aangemaakt in de database.

---

## Test 2.2 – Geldig e-mailadres

**Actie:**
- Open de frontend boekingspagina.
- Selecteer een beschikbare spot en klik op "Book Now".
- Vul `"Jan de Vries"` in als naam en `"test@test.nl"` als e-mailadres.
- Klik op "Confirm Booking".

**Verwachting:**
- De boeking wordt succesvol aangemaakt.
- De klant ontvangt een bevestigingsmail op test@test.nl.
- De admin ontvangt een notificatiemail.

**Resultaat:** ☐ Geslaagd 

**Opmerking:** De boeking werd succesvol aangemaakt. In het formulier verscheen de melding "🎉 Boeking bevestigd! Je ontvangt een bevestigingsmail." De klant ontving een bevestigingsmail en de admin een notificatiemail.
