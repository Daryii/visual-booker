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

**Resultaat:** ☐ Mislukt

**Opmerking:** De spot verschijnt wel op de canvas en de statusbalk toont "Spot added ✓", maar de spot wordt niet automatisch opgeslagen in de database. 
Je moet handmatig op "Save All Spots" klikken om de spot op te slaan.

---

## Test 11.2 – Spot verwijderen via admin

**Endpoint:** `DELETE /visual-booker/v1/spot/{id}`

**Actie:**
- Selecteer een spot op de canvas.
- Klik op "Delete Spot" en bevestig.

**Verwachting:**
- De spot verdwijnt van de canvas.
- De spot is verwijderd uit de database.

**Resultaat:** ☐ Geslaagd 

**Opmerking:** De spot verdwijnt direct van de canvas en wordt ook uit de database verwijderd.

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

**Resultaat:** ☐ Geslaagd 

**Opmerking:** De nieuwe positie wordt opgeslagen, de statusbalk toont "All spots saved ✓" en na het verversen staat de spot op de nieuwe positie.
