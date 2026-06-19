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
- De klant krijgt een bevestiging bericht.


**Resultaat:** ☐ Mislukt

**Opmerking:** De test is mislukt. De status verandert wel naar "Approved" en blijft ook na het verversen op "Approved" staan, maar de klant ontvangt geen bevestigingsbericht.

---

## Test 5.2 – Boeking annuleren

**Actie:**
- Zoek een boeking met status "Pending" of "Approved" in de admin.
- Klik op de "Cancel" knop.
- Ververs de pagina.

**Verwachting:**
- De status verandert naar "Cancelled" (rood).
- Na het auto-verversen verdwijnt het boeking van de tabel.
- Na het verversen van de pagina moet de boeking uit de tabel verwijderd zijn.
- De spot op de frontend wordt weer groen (beschikbaar).

**Resultaat:** ☐ Mislukt

**Opmerking:** De test is mislukt. De status verandert wel naar "Cancelled" en na het auto-verversen verdwijnt de boeking uit de tabel, maar na het verversen van de pagina wordt de cancelled boeking weer getoond in plaats van dat het verdwijnt.
