# 1. Boeking aanmaken – Naam validatie

**Bestand:** `includes/class-vb-rest-api.php`
**Endpoint:** `POST /visual-booker/v1/bookings/bulk`

---

## Test 1.1 – Naam leeg

**Actie:**
- Open de frontend boekingspagina in de browser.
- Selecteer een beschikbare spot op de plattegrond.
- Klik op "Book Now" om het boekingsformulier te openen.
- Laat het naam veld leeg en vul een geldig e-mailadres in.
- Klik op "Confirm Booking".

**Verwachting:**
- Het formulier wordt niet verstuurd.
- Er verschijnt een foutmelding dat de naam verplicht is.
- Er wordt geen boeking aangemaakt in de database.

**Resultaat:** ☐ Geslaagd

**Opmerking:** Het formulier werd niet verstuurd. De browser toonde de melding "Vul dit veld in" direct onder het naamveld. Er werd geen boeking aangemaakt in de database.
---

## Test 1.2 – Naam te kort (1 teken)

**Actie:**
- Open de frontend boekingspagina.
- Selecteer een beschikbare spot en klik op "Book Now".
- Vul `"A"` in als naam en `test@test.nl` als e-mailadres.
- Klik op "Confirm Booking".

**Verwachting:**
- De REST API geeft een foutmelding terug: naam moet minimaal 2 en max 255 tekens zijn.
- De foutmelding wordt getoond in het formulier.
- Er wordt geen boeking aangemaakt in de database.

**Resultaat:** ☐ Geslaagd 

**Opmerking:** De REST API weigerde de boeking. In het formulier verscheen de melding "Naam moet tussen 2 en 255 tekens zijn." Er werd geen boeking aangemaakt in de database.
---

## Test 1.3 – Naam te lang (256 tekens)

**Actie:**
- Open de frontend boekingspagina.
- Selecteer een beschikbare spot en klik op "Book Now".
- Vul een naam van 256 tekens in (kopieer 256x de letter "a").
- Vul een geldig e-mailadres in en klik op "Confirm Booking".

**Verwachting:**
- De REST API geeft een foutmelding terug: naam mag maximaal 255 tekens zijn.
- De foutmelding wordt getoond in het formulier.
- Er wordt geen boeking aangemaakt in de database.

**Resultaat:** ☐ Geslaagd 

**Opmerking:** De REST API weigerde de boeking. In het formulier verscheen de melding "Naam moet tussen 2 en 255 tekens zijn." Er werd geen boeking aangemaakt in de database.

---

## Test 1.4 – Naam geldig (2 tekens, grenswaarde)

**Actie:**
- Open de frontend boekingspagina.
- Selecteer een beschikbare spot en klik op "Book Now".
- Vul `"Jo"` in als naam en `test@test.nl` als e-mailadres.
- Klik op "Confirm Booking".

**Verwachting:**
- De boeking wordt succesvol aangemaakt.
- De succesmelding verschijnt in het formulier.
- De klant ontvangt een bevestigingsmail.
- De spot wordt rood (geboekt) na het herladen.

**Resultaat:** ☐ Geslaagd 

**Opmerking:** De boeking werd succesvol aangemaakt. In het formulier verscheen de melding "🎉 Boeking bevestigd! Je ontvangt een bevestigingsmail." Na 2 seconden sloot het formulier automatisch en werd de spot rood weergegeven op de plattegrond. De klant ontving een bevestigingsmail.

---

## Test 1.5 – Naam geldig (255 tekens, grenswaarde)

**Actie:**
- Open de frontend boekingspagina.
- Selecteer een beschikbare spot en klik op "Book Now".
- Vul een naam van precies 255 tekens in.
- Vul een geldig e-mailadres in en klik op "Confirm Booking".

**Verwachting:**
- De boeking wordt succesvol aangemaakt.
- De naam wordt correct opgeslagen in de database (controleer via phpMyAdmin).

**Resultaat:** ☐ Geslaagd 

**Opmerking:** De boeking werd succesvol aangemaakt. In het formulier verscheen de melding "🎉 Boeking bevestigd! Je ontvangt een bevestigingsmail." Na 2 seconden sloot het formulier automatisch en werd de spot rood weergegeven op de plattegrond. De klant ontving een bevestigingsmail.

