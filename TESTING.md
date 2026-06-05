# Visual Booker – Testdocument

---

## Testvorm
**Gekozen testvorm:** Acceptatietest (handmatig)

De tests worden handmatig uitgevoerd in de browser. Er is gekozen voor een acceptatietest omdat de focus ligt op het controleren of de gerealiseerde functionaliteiten correct werken vanuit het perspectief van de eindgebruiker.

---

## Testmethodiek
De tests zijn opgebouwd uit twee soorten scenario's:

- **Happy path** — de correcte invoer werkt zoals verwacht
- **Foutscenario's** — ongeldige invoer wordt correct afgewezen

---

## Testomgeving
- **Omgeving:** Local by Flywheel (lokale WordPress installatie)
- **Browser:** Google Chrome
- **Plugin versie:** 1.0.2
- **WordPress versie:** 6.x

---

## Testdata
- Geldige naam: `"Jan de Vries"`
- Naam te kort: `"A"` (1 teken)
- Naam te lang: 201 tekens (bijv. herhaald `"a"`)
- Grenswaarde kort: `"Jo"` (2 tekens)
- Grenswaarde lang: 200 tekens
- Geldig e-mailadres: `test@test.nl`
- Bestaande layout met spots aanwezig in de database

---

## Testscenario's

### 1. Boeking aanmaken – Naam validatie

**Bestand:** `includes/class-vb-rest-api.php`  
**Endpoint:** `POST /visual-booker/v1/bookings`

#### Test 1.1 – Naam leeg
- **Actie:** Formulier versturen zonder naam in te vullen
- **Verwacht:** Foutmelding — vul naam in
- **Resultaat:** ☐ Geslaagd &nbsp; ☐ Mislukt

#### Test 1.2 – Naam te kort (1 teken)
- **Actie:** Naam invullen als `"A"` en formulier versturen
- **Verwacht:** Foutmelding — naam moet minimaal 2 tekens zijn
- **Resultaat:** ☐ Geslaagd &nbsp; ☐ Mislukt

#### Test 1.3 – Naam te lang (201 tekens)
- **Actie:** Naam invullen met 201 tekens en formulier versturen
- **Verwacht:** Foutmelding — naam mag maximaal 200 tekens zijn
- **Resultaat:** ☐ Geslaagd &nbsp; ☐ Mislukt

#### Test 1.4 – Naam geldig (2 tekens)
- **Actie:** Naam invullen als `"Jo"` en formulier versturen
- **Verwacht:** Boeking succesvol aangemaakt
- **Resultaat:** ☐ Geslaagd &nbsp; ☐ Mislukt

#### Test 1.5 – Naam geldig (200 tekens)
- **Actie:** Naam invullen met precies 200 tekens en formulier versturen
- **Verwacht:** Boeking succesvol aangemaakt
- **Resultaat:** ☐ Geslaagd &nbsp; ☐ Mislukt

---

### 2. Boeking aanmaken – Max spots validatie

**Bestand:** `includes/class-vb-rest-api.php`  
**Endpoint:** `POST /visual-booker/v1/bookings/bulk`

#### Test 2.1 – Meer spots dan maximum selecteren
- **Actie:** Meer spots selecteren dan het ingestelde maximum (standaard 10)
- **Verwacht:** Selectie geblokkeerd — verdere selectie niet mogelijk
- **Resultaat:** ☐ Geslaagd &nbsp; ☐ Mislukt

#### Test 2.2 – Exact het maximum selecteren
- **Actie:** Precies het maximale aantal spots selecteren
- **Verwacht:** Selectie toegestaan, boeking succesvol
- **Resultaat:** ☐ Geslaagd &nbsp; ☐ Mislukt

---

### 3. Spot selectie – Status

**Bestand:** `public/js/public.js`

#### Test 3.1 – Geboekte spot selecteren
- **Actie:** Klikken op een spot die al geboekt is (rood)
- **Verwacht:** Geen reactie — spot is niet klikbaar
- **Resultaat:** ☐ Geslaagd &nbsp; ☐ Mislukt

#### Test 3.2 – Unavailable spot selecteren
- **Actie:** Klikken op een spot met status `locked` of `maintenance`
- **Verwacht:** Geen reactie — spot is niet klikbaar
- **Resultaat:** ☐ Geslaagd &nbsp; ☐ Mislukt

---

### 4. Boeking status wijzigen – Admin

**Bestand:** `includes/class-vb-rest-api.php`  
**Endpoint:** `POST /visual-booker/v1/booking/{id}/status`

#### Test 4.1 – Boeking goedkeuren
- **Actie:** In de admin een boeking goedkeuren via de knop
- **Verwacht:** Status verandert naar `approved`, label wordt groen
- **Resultaat:** ☐ Geslaagd &nbsp; ☐ Mislukt

#### Test 4.2 – Boeking annuleren
- **Actie:** In de admin een boeking annuleren via de knop
- **Verwacht:** Status verandert naar `cancelled`
- **Resultaat:** ☐ Geslaagd &nbsp; ☐ Mislukt

---

## Conclusies

| Testsectie | Geslaagd | Mislukt | Conclusie |
|-----------|----------|---------|-----------|
| 1. Naam validatie | | | |
| 2. Max spots | | | |
| 3. Spot selectie | | | |
| 4. Status wijzigen | | | |

**Algemene conclusie:**

