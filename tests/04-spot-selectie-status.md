# 4. Spot selectie – Status controle

**Bestand:** `public/js/public.js`

---

## Test 4.1 – Geboekte spot selecteren

**Actie:**
- Open de frontend boekingspagina.
- Klik op een spot die al geboekt is (rode kleur).

**Verwachting:**
- Er gebeurt niets — de spot reageert niet op de klik.
- De tooltip toont "(Booked)" achter de spot naam.

**Resultaat:** ☐ Geslaagd

**Opmerking:** De spot reageert niet op de klik en toont "Booked" achter de naam.

---

## Test 4.2 – Locked spot selecteren

**Actie:**
- Zet een spot op status `locked` via de admin builder.
- Open de frontend boekingspagina.
- Klik op de locked spot (grijze kleur).

**Verwachting:**
- Er gebeurt niets — de spot reageert niet op de klik.
- De tooltip toont "(Unavailable)" achter de spot naam.

**Resultaat:** ☐ Geslaagd 

**Opmerking:** De spot reageert niet op de klik en toont "Unavailable" achter de naam.

---

## Test 4.3 – Beschikbare spot selecteren en deselecteren

**Actie:**
- Open de frontend boekingspagina.
- Klik op een beschikbare spot (groene kleur).
- Controleer of de spot oranje wordt (geselecteerd).
- Klik nogmaals op dezelfde spot.

**Verwachting:**
- Eerste klik: spot wordt oranje, selection bar verschijnt onderaan met (spot/spots) en de prijs.
- Tweede klik: spot wordt weer groen, selection bar verdwijnt.

**Resultaat:** ☐ Geslaagd 

**Opmerking:** De spot wordt oranje bij de eerste klik en de selectiebalk verschijnt onderaan met de naam en prijs. Bij de tweede klik wordt de spot weer groen en verdwijnt de selectiebalk.
