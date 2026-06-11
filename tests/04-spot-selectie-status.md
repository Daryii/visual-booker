# 4. Spot selectie – Status controle

**Bestand:** `public/js/public.js`

---

## Test 4.1 – Geboekte spot selecteren

**Actie:**
- Open de frontend boekingspagina.
- Klik op een spot die al geboekt is (rode kleur).

**Verwachting:**
- Er gebeurt niets — de spot reageert niet op de klik.
- De cursor toont `not-allowed` bij hover.
- De tooltip toont "(Booked)" achter de spot naam.

**Resultaat:** ☐ Geslaagd ☐ Mislukt

**Opmerking:**

---

## Test 4.2 – Locked spot selecteren

**Actie:**
- Zet een spot op status `locked` via de admin builder.
- Open de frontend boekingspagina.
- Klik op de locked spot (grijze kleur).

**Verwachting:**
- Er gebeurt niets — de spot reageert niet op de klik.
- De cursor toont `not-allowed` bij hover.
- De tooltip toont "(Unavailable)" achter de spot naam.

**Resultaat:** ☐ Geslaagd ☐ Mislukt

**Opmerking:**

---

## Test 4.3 – Beschikbare spot selecteren en deselecteren

**Actie:**
- Open de frontend boekingspagina.
- Klik op een beschikbare spot (groene kleur).
- Controleer of de spot oranje wordt (geselecteerd).
- Klik nogmaals op dezelfde spot.

**Verwachting:**
- Eerste klik: spot wordt oranje, selection bar verschijnt onderaan met 1 spot en de prijs.
- Tweede klik: spot wordt weer groen, selection bar verdwijnt.

**Resultaat:** ☐ Geslaagd ☐ Mislukt

**Opmerking:**
