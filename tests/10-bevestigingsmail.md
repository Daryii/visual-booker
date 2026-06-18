# 10. Bevestigingsmail

**Bestand:** `includes/class-vb-rest-api.php`

---

## Test 10.1 – Klant ontvangt bevestigingsmail

**Actie:**
- Maak een boeking aan met een echt e-mailadres.
- Check de inbox van dat e-mailadres.

**Verwachting:**
- Er is een bevestigingsmail ontvangen.
- De mail bevat: layout naam, spot label(s), prijs, totaalprijs, status.

**Resultaat:** ☐ Geslaagd

**Opmerking:** De klant ontvangt een bevestigingsmail met alle boekingsdetails: de layoutnaam, spot label(s), prijs per spot, totaalprijs en de status.

---

## Test 10.2 – Admin ontvangt notificatiemail

**Actie:**
- Maak een boeking aan via de frontend.
- Check de inbox van het admin e-mailadres.

**Verwachting:**
- Er is een notificatiemail ontvangen.
- De mail bevat: klantnaam, e-mail, telefoon, spot(s), layout, notes.

**Resultaat:** ☐ Geslaagd ☐ Mislukt

**Opmerking:** De Admin ontvangt een notificatiemail met alle boekingsdetails: klantnaam, e-mail, telefoon, spot(s), layout, notes.

---

## Test 10.3 – Bulk boeking: één mail per ontvanger

**Actie:**
- Selecteer 3 spots op de frontend en maak een boeking aan.
- Check de inbox van de klant en de admin.

**Verwachting:**
- De klant ontvangt precies 1 mail met alle 3 de spots erin.
- De admin ontvangt precies 1 mail met alle 3 de spots erin.
- De totaalprijs in de mail is de som van alle 3 de spots.

**Resultaat:** ☐ Geslaagd 

**Opmerking:** Zowel de klant als de admin ontvangen één mail met alle geboekte spots en de totaalprijs.
Bijvoorbeeld: 

Geboekte spots:
  - S22 (€10,00)
  - S26 (€10,00)
  - S24 (€10,00)

Totaalprijs: €30,00