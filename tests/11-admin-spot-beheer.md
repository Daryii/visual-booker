# 11. Admin builder – Spot beheer

**Bestand:** `admin/js/admin.js`

---

## Test 11.1 – Spot toevoegen via admin

**Endpoint:** `POST /visual-booker/v1/spot`

**Actie:**
- Ga naar de admin builder van een layout.
- Klik op "Add Spot".

**Verwachting:**
- De spot verschijnt op de canvas.
- De spot wordt opgeslagen in de database.
- De statusbalk toont "Spot added ✓".

**Resultaat:** ☐ Geslaagd ☐ Mislukt

**Opmerking:**

---

## Test 11.2 – Spot verwijderen via admin

**Endpoint:** `DELETE /visual-booker/v1/spot/{id}`

**Actie:**
- Selecteer een spot op de canvas.
- Klik op "Delete Spot" en bevestig.

**Verwachting:**
- De spot verdwijnt van de canvas.
- De spot is verwijderd uit de database.

**Resultaat:** ☐ Geslaagd ☐ Mislukt

**Opmerking:**

---

## Test 11.3 – Spot verplaatsen en opslaan

**Endpoint:** `POST /visual-booker/v1/spots/bulk`

**Actie:**
- Sleep een spot naar een andere positie op de canvas.
- Klik op "Save All Spots".
- Ververs de pagina.

**Verwachting:**
- De nieuwe positie wordt opgeslagen.
- De statusbalk toont "All spots saved ✓".
- Na verversen staat de spot op de nieuwe positie.

**Resultaat:** ☐ Geslaagd ☐ Mislukt

**Opmerking:**
