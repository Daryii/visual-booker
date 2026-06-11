# 3. Boeking aanmaken – Max spots validatie

**Bestand:** `public/js/public.js`

---

## Test 3.1 – Meer spots dan maximum selecteren

**Actie:**
- Open de frontend boekingspagina.
- Probeer meer dan 10 spots te selecteren (het ingestelde maximum).

**Verwachting:**
- Na 10 spots wordt verdere selectie geblokkeerd.
- Er verschijnt een melding dat het maximum is bereikt.

**Resultaat:** ☐ Geslaagd ☐ Mislukt

**Opmerking:**

---

## Test 3.2 – Exact het maximum selecteren

**Actie:**
- Open de frontend boekingspagina.
- Selecteer precies 10 spots.
- Vul geldige gegevens in en klik op "Confirm Booking".

**Verwachting:**
- Alle 10 spots worden succesvol geboekt.
- De bevestigingsmail bevat alle 10 spots met de juiste totaalprijs.

**Resultaat:** ☐ Geslaagd ☐ Mislukt

**Opmerking:**
