# Visual Booker – WordPress Boekingsplugin

Een WordPress-plugin waarmee je een afbeelding (een plattegrond, marktkaart, of locatie-layout) kunt uploaden en daarop boekbare spots kunt plaatsen. Bezoekers kunnen vervolgens op beschikbare spots klikken en via een simpel formulier een boeking plaatsen.

De plugin is begonnen als een starter kit met een basis boekingsflow. Vanaf daar heb ik (Daryi) zelf verder gebouwd: e-mailbevestigingen bij elke statuswijziging, beveiliging tegen XSS, een admin-canvas die de status van elke spot duidelijk toont, bulk-acties voor prijzen en max-spots, en diverse bugfixes. Het resultaat is een werkend geheel plugin.

Geïnspireerd door [SeatReg](https://wordpress.org/plugins/seatreg/), maar volledig opnieuw geschreven.

---

## Aan de slag

1. Zet de `visual-booker/` map in `wp-content/plugins/`.
2. Activeer de plugin via de Plugins-pagina in WP Admin.
3. Ga naar Booking Layouts en maak een nieuwe layout. Geef een titel en upload een achtergrondafbeelding.
4. Gebruik de "Add Spot" knop om spots op de afbeelding te plaatsen. Je kunt ze verslepen, verkleinen, en hun label, prijs en type bewerken.
5. Klik op "Save All Spots" zodra je tevreden bent met de layout.
6. Kopieer de shortcode uit de zijbalk (bijvoorbeeld `[visual_booker id="42"]`) en plak deze in een pagina of bericht.
7. Klaar — bezoekers kunnen nu spots selecteren en boeken.

---

## Structuur van de plugin

```
visual-booker/
├── visual-booker.php                      Hoofdbestand. Registreert hooks en laadt assets.
├── includes/
│   ├── class-vb-db.php                    Databasetabellen en alle CRUD-functies voor spots en boekingen.
│   ├── class-vb-post-type.php             Het "Booking Layout" custom post type, plus de admin meta boxes.
│   ├── class-vb-rest-api.php              Alle REST API endpoints waarmee de front-end en builder communiceren.
│   └── class-vb-shortcode.php             De [visual_booker] shortcode die de publieke boekingsweergave rendert.
├── templates/
│   ├── front-end.php                      De HTML-template die bezoekers zien wanneer de shortcode laadt.
│   ├── email-klant-bevestiging.php        E-mail naar de klant na het plaatsen van een boeking.
│   ├── email-klant-goedgekeurd.php        E-mail naar de klant zodra de admin de boeking goedkeurt.
│   ├── email-klant-geannuleerd.php        E-mail naar de klant zodra de admin de boeking annuleert.
│   └── email-admin-melding.php            E-mail naar de admin bij een nieuwe boeking.
├── admin/
│   ├── css/admin.css                      Stijlen voor de drag-and-drop builder.
│   └── js/admin.js                        Alle builder-logica: slepen, verkleinen, bewerken, spots opslaan.
├── public/
│   ├── css/public.css                     Stijlen voor de front-end boekingsinterface.
│   └── js/public.js                       Spot-selectie, modal, en formulierverzending.
├── tests/                                  Handmatige testcases per onderdeel, met resultaten en conclusies.
└── README.md
```

### Een paar ontwerpkeuzes

**Waarom eigen databasetabellen in plaats van post meta?** Spots en boekingen zijn gestructureerde, doorzoekbare data. Eigen tabellen maken filteren, joinen en tellen veel makkelijker dan met de meta-tabel.

**Waarom percentage-gebaseerde positionering?** Spot-posities worden opgeslagen als percentages van de afbeeldingsafmetingen, zodat layouts responsief blijven op elk schermformaat zonder extra werk.

**Waarom jQuery?** Dat zit al in WordPress, dus geen extra dependencies nodig. Je mag het gerust vervangen door vanilla JS, React of Vue als leeroefening.

**Hoe werkt de boekingsflow?** Een bezoeker selecteert één of meer spots, klikt op "Book Now", vult een kort formulier in en verstuurt het. Elke geselecteerde spot wordt een eigen boeking via de REST API. De admin krijgt een e-mailmelding en kan boekingen goedkeuren of annuleren vanuit de layout-editor — de klant ontvangt daarna ook automatisch een e-mail.

---

## Databasetabellen

De plugin maakt bij activatie vijf tabellen aan, allemaal in één keer via `dbDelta()`:

**wp_vb_spots** bevat elke boekbare spot: positie, grootte, label, type, prijs, en status.

**wp_vb_bookings** bevat elke boeking: welke spot, welke layout, naam/e-mail/telefoon van de klant, boekingsstatus, en eventuele notities.

**wp_vb_spot_types**, **wp_vb_spot_statuses** en **wp_vb_booking_statuses** zijn lookup-tabellen — vaste lijstjes (zoals "open", "locked", "maintenance" of "pending", "approved", "cancelled") waar spots en bookings naar verwijzen via een ID.

---

## REST API

Alle endpoints staan onder `/wp-json/visual-booker/v1/`. De publieke endpoints vereisen geen authenticatie. De admin-endpoints controleren of de gebruiker de `edit_posts` capability heeft.

| Methode | Endpoint | Wie mag het gebruiken | Wat het doet |
|---|---|---|---|
| GET | /spots/{layout_id} | Iedereen | Geeft alle spots van een layout terug, met een vlag die aangeeft welke al geboekt zijn |
| POST | /spot | Admin | Maakt of werkt één spot bij |
| POST | /spots/bulk | Admin | Slaat alle spots in één keer op |
| DELETE | /spot/{id} | Admin | Verwijdert een spot |
| POST | /booking | Iedereen | Verstuurt een nieuwe boeking |
| POST | /bookings/bulk | Iedereen | Verstuurt meerdere boekingen (één per geselecteerde spot) in één request |
| PATCH | /booking/{id}/status | Admin | Wijzigt de status van een boeking (approve, cancel) en mailt de klant hierover |
| GET | /bookings/{layout_id} | Admin | Toont alle boekingen van een layout |
| POST | /layout/{id}/settings | Admin | Slaat layout-instellingen op, zoals max spots per boeking |

---

## Codeerrichtlijnen

Een paar dingen om in gedachten te houden:

- Saniteer altijd input en escape altijd output. Gebruik `sanitize_text_field()`, `sanitize_email()`, `esc_html()`, `esc_attr()`, enzovoort. Vertrouw nooit wat uit de browser komt.
- Gebruik `$wpdb->prepare()` voor elke databasequery met door de gebruiker aangeleverde waarden. Geen uitzonderingen.
- Geef alles (functies, classes, CSS-klassen, JS-variabelen) een `vb_` of `vb-` prefix om botsingen met andere plugins te voorkomen.
- Wrap alle tekst die de gebruiker ziet in `__()` of `esc_html__()` met het `'visual-booker'` text domain, zodat de plugin vertaald kan worden.
- Houd JS en CSS in hun eigen bestanden. Geen inline scripts of stijlen.
- Comment je code, vooral alles dat niet vanzelf spreekt in de builder- of API-logica.

---

## Testen en debuggen

Zet een lokale WordPress-installatie op (LocalWP, XAMPP, of Docker werken allemaal goed). Activeer de plugin, maak een layout met een paar spots, plaats die op een pagina, en probeer te boeken vanuit een incognito-venster.

Zet `WP_DEBUG` en `WP_DEBUG_LOG` aan in je `wp-config.php` om fouten vroeg op te sporen. Gebruik het Network-tabblad van de browser DevTools om REST API requests te bekijken. Je kunt de API ook direct in de browser aanroepen (bijvoorbeeld `GET /wp-json/visual-booker/v1/spots/42`) om te zien hoe de data eruitziet.

Als er iets niet klopt met de data, check de `wp_vb_spots` en `wp_vb_bookings` tabellen in phpMyAdmin.

---

## Nog één ding

Het valutasymbool kan worden ingesteld onder Booking Layouts → Instellingen. De getalnotatie (decimaal/duizendtal-scheidingstekens) volgt automatisch de taal van de WordPress site via `get_locale()`, dus dat hoeft niet apart geconfigureerd te worden.
