## Instalacja

`docker compose build`

`docker compose -f compose.yml -f compose.dev.yml up -d`

## Użycie

### Monitoring

`docker compose exec php sh`

`php spark app:coasters-monitoring`

Komenda zacznie wyświetlać status po następnym zdarzeniu (wykonaniu requestu).

### Przykładowe requesty:

Tworzy kolejkę z podanymi ustawieniami, zwraca coasterId UUIDv4
POST https://bluebinary.localhost/api/coasters
```json
{
    "liczba_personelu": 16,
    "liczba_klientow": 60000,
    "dl_trasy": 1800,
    "godziny_od": "8:00",
    "godziny_do": "16:00"
}
```

PUT https://bluebinary.localhost/api/coasters/:coasterId
```json
{
    "liczba_personelu": 16,
    "liczba_klientow": 60000,
    "dl_trasy": 1700,
    "godziny_od": "8:00",
    "godziny_do": "16:00"
}
```

Tworzy wagonik z podanymi ustawieniami, zwraca wagonId UUIDv4
POST https://bluebinary.localhost/api/coasters/:coasterId/wagons
```json
{
    "ilosc_miejsc": 32,
    "predkosc_wagonu": 1.2
}
```

DELETE https://bluebinary.localhost/api/coasters/:coasterId/wagons/:wagonId

## Dodatkowe notki

Na potrzeby tego zadania rekrutacyjnego nie tworzyłem i nie skupiałem się na walidacji danych wejściowych. 

Nie do końca zrozumiałem o co dokładnie chodziło poprzez "Niedostępna dla osób z zewnątrz" dla wersji deweloperskiej.
Wierzę, że połączenie poprzez bluebinary.localhost rozwiązuje sprawę.

Produkcyjna konfiguracja oczywiście jest symboliczna.