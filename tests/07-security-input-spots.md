# 7. Security – Input validatie spots

**Bestand:** `includes/class-vb-rest-api.php`
**Endpoint:** `POST /visual-booker/v1/spot`

---

## Test 7.1 – Spot met ontbrekend label

**Actie:**
- Open de browser console op de admin builder pagina.
- Voer het volgende uit:

```js
fetch(vbAdmin.restUrl + 'spot', {
    method: 'POST',
    headers: {
        'Content-Type': 'application/json',
        'X-WP-Nonce': vbAdmin.nonce
    },
    body: JSON.stringify({
        layout_id: 7,
        pos_x: 50,
        pos_y: 50
    })
}).then(r => r.json()).then(d => console.log(d));
```

**Verwachting:**
- De REST API geeft een foutmelding terug: veld "label" is verplicht.
- Er wordt geen spot aangemaakt in de database.

**Resultaat:** ☐ Geslaagd 

**Opmerking:** De REST API geeft een foutmelding terug met POST 400 (anonymous) Bad Request. De melding geeft aan dat het veld "label" verplicht is en er wordt geen spot aangemaakt in de database.

---

## Test 7.2 – Spot met pos_x boven 100

**Actie:**
- Voer in de browser console uit:

```js
fetch(vbAdmin.restUrl + 'spot', {
    method: 'POST',
    headers: {
        'Content-Type': 'application/json',
        'X-WP-Nonce': vbAdmin.nonce
    },
    body: JSON.stringify({
        layout_id: 7,
        label: 'Test',
        pos_x: 150,
        pos_y: 50
    })
}).then(r => r.json()).then(d => console.log(d));
```

**Verwachting:**
- De REST API geeft een foutmelding terug: pos_x moet tussen 0 en 100 zijn.
- Er wordt geen spot aangemaakt.

**Resultaat:** ☐ Geslaagd 

**Opmerking:** De REST API geeft een foutmelding terug met POST 400 (anonymous) Bad Request. De melding geeft aan dat pos_x tussen 0 en 100 moet zijn en er wordt geen spot aangemaakt in de database.

---

## Test 7.3 – Spot met negatieve prijs

**Actie:**
- Voer in de browser console uit:

```js
fetch(vbAdmin.restUrl + 'spot', {
    method: 'POST',
    headers: {
        'Content-Type': 'application/json',
        'X-WP-Nonce': vbAdmin.nonce
    },
    body: JSON.stringify({
        layout_id: 7,
        label: 'Test',
        pos_x: 50,
        pos_y: 50,
        price: -10
    })
}).then(r => r.json()).then(d => console.log(d));
```

**Verwachting:**
- De REST API geeft een foutmelding terug: prijs mag niet negatief zijn.
- Er wordt geen spot aangemaakt.

**Resultaat:** ☐ Geslaagd 

**Opmerking:** De REST API geeft een foutmelding terug met POST 400 (anonymous) Bad Request. De melding geeft aan dat 'Prijs mag niet negatief zijn' en er wordt geen spot aangemaakt in de database.