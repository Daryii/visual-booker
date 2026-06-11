# Visual Booker – Testdocument

## Testvorm

**Gekozen testvorm:** Acceptatietest (handmatig)

De tests worden handmatig uitgevoerd in de browser. Er is gekozen voor een acceptatietest omdat de focus ligt op het controleren of de gerealiseerde functionaliteiten correct werken vanuit het perspectief van de eindgebruiker. De handmatige testlijst dekt alle kritische functies en laat zien dat er bewust is nagedacht over wat getest moet worden.

---

## Testmethodiek

De tests zijn opgebouwd uit twee soorten scenario's:

- **Happy path** — de correcte invoer werkt zoals verwacht
- **Foutscenario's** — ongeldige invoer wordt correct afgewezen

Elke test beschrijft de exacte stappen (Actie), wat er zou moeten gebeuren (Verwachting), en wat er daadwerkelijk gebeurde (Resultaat).

---

## Testomgeving

- **Omgeving:** Local by Flywheel (lokale WordPress installatie)
- **Browser:** Google Chrome
- **Plugin versie:** 1.0.2
- **WordPress versie:** 7.0
- **Test URL:** http://visual-booker.local/boeken/

---

## Testdata

- Geldige naam: `"Jan de Vries"`
- Naam te kort: `"A"` (1 teken)
- Naam te lang: 256 tekens (herhaald `"a"`)
- Grenswaarde kort: `"Ja"` (2 tekens)
- Grenswaarde lang: 255 tekens
- Geldig e-mailadres: `test@test.nl`
- Ongeldig e-mailadres: `geen-email`
- Bestaande layout ID: `7` (met spots in de database)
- Niet-bestaande layout ID: `9999`
- Niet-bestaande spot ID: `9999`

---

## Overzicht testbestanden

| [01-naam-validatie.md](01-naam-validatie.md) | Boeking aanmaken – Naam validatie |
| [02-email-validatie.md](02-email-validatie.md) | Boeking aanmaken – E-mail validatie |
| [03-max-spots-validatie.md](03-max-spots-validatie.md) | Boeking aanmaken – Max spots validatie |
| [04-spot-selectie-status.md](04-spot-selectie-status.md) | Spot selectie – Status controle |
| [05-boeking-status-admin.md](05-boeking-status-admin.md) | Boeking status wijzigen – Admin |
| [06-security-nonce.md](06-security-nonce.md) | Security – Nonce verificatie |
| [07-security-input-spots.md](07-security-input-spots.md) | Security – Input validatie spots |
| [08-security-xss.md](08-security-xss.md) | Security – XSS preventie |
| [09-zoom-pan.md](09-zoom-pan.md) | Zoom en pan – Frontend |
| [10-bevestigingsmail.md](10-bevestigingsmail.md) | Bevestigingsmail |
| [11-admin-spot-beheer.md](11-admin-spot-beheer.md) | Admin builder – Spot beheer |
| [12-extra-validatie.md](12-extra-validatie.md) | Boeking aanmaken – Extra validatie |
| [13-touch-pan-mobiel.md](13-touch-pan-mobiel.md) | Frontend – Touch pan (mobiel) |
| [conclusies.md](conclusies.md) | Conclusies – Overzicht alle resultaten |
