# 3. Boeking aanmaken – Max spots validatie

**Bestand:** `public/js/public.js`

---

## Test 3.1 – Meer spots dan maximum selecteren

**Actie:**
- Open de frontend boekingspagina.
- Probeer meer dan het ingestelde maximum te selecteren.

**Verwachting:**
- Na ingestelde maximum spots wordt selectie geblokkeerd.
- Er verschijnt een melding dat het maximum is bereikt.

**Resultaat:** ☐ Geslaagd 

**Opmerking:** Het formulier werd niet verstuurd. De browser toonde de melding " Je kunt maximaal {igestelde max} spots selecteren" 

---

## Test 3.2 – Exact het maximum selecteren

**Actie:**
- Open de frontend boekingspagina.
- Selecteer het ingestelde maximum.
- Vul geldige gegevens in en klik op "Confirm Booking".

**Verwachting:**
- Alle spots worden succesvol geboekt.
- De bevestigingsmail bevat alle spots met de juiste totaalprijs.

**Resultaat:** ☐ Geslaagd 

**Opmerking:** Het formulier werd verstuurd. In het formulier verscheen de melding "🎉 Boeking bevestigd! Je ontvangt een bevestigingsmail, en de bevestigingmail bevat alle geselecterde spots.
