# 5. Boeking status wijzigen – Admin

**Bestand:** `includes/class-vb-rest-api.php`
**Endpoint:** `PATCH /visual-booker/v1/booking/{id}/status`

---

## Test 5.1 – Boeking goedkeuren

**Actie:**
- Maak eerst een testboeking aan via de frontend.
- Ga naar de admin: Booking Layouts → bewerk de layout.
- Scroll naar de Bookings tabel.
- Klik op de "Approve" knop bij de nieuwe boeking.
- Ververs de pagina.

**Verwachting:**
- De status verandert van "Pending" (geel) naar "Approved" (groen).
- Na verversen blijft de status op "Approved" staan.
- De spot op de frontend blijft rood (geboekt).

**Resultaat:** ☐ Geslaagd ☐ Mislukt

**Opmerking:**

---

## Test 5.2 – Boeking annuleren

**Actie:**
- Zoek een boeking met status "Pending" of "Approved" in de admin.
- Klik op de "Cancel" knop.
- Ververs de pagina.

**Verwachting:**
- De status verandert naar "Cancelled" (rood).
- Na verversen blijft de status op "Cancelled" staan.
- De spot op de frontend wordt weer groen (beschikbaar).

**Resultaat:** ☐ Geslaagd ☐ Mislukt

**Opmerking:**
