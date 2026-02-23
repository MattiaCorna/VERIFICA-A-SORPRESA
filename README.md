# VERIFICA-A-SORPRESA

API REST con Slim Framework - Endpoints da 1 a 10

## 📋 Descrizione

Questo progetto contiene un'API REST costruita con **Slim Framework** che espone 10 endpoint per il database Fornitori/Pezzi/Catalogo.

## 🚀 Installazione

### 1. Installare le dipendenze PHP
```bash
composer install
```

### 2. Configurare il database
```bash
mysql -u root -p < database.sql
```

### 3. Configurare la connessione al database
Modificare in `api.php` le credenziali del database:
```php
$pdo = new PDO(
    'mysql:host=localhost;dbname=magazzino;charset=utf8mb4',
    'root',
    'password'  // ← Cambiare con la tua password
);
```

### 4. Avviare il server
```bash
php -S localhost:8000
```

## 📍 Endpoint Disponibili

### Endpoint 1 - `/api/1`
**Descrizione:** Pezzi con fornitori  
**Parametri:**
- `color` - Filtro per colore del pezzo (opzionale)
- `limit` - Numero massimo di risultati (default: 1000)

**Esempio:** `GET /api/1?color=rosso&limit=10`

### Endpoint 2 - `/api/2`
**Descrizione:** Fornitori che forniscono ogni pezzo

### Endpoint 3 - `/api/3`
**Descrizione:** Fornitori con pezzi di colore specificato  
**Parametri:**
- `color` - Colore pezzo (default: rosso)

**Esempio:** `GET /api/3?color=blu`

### Endpoint 4 - `/api/4`
**Descrizione:** Pezzi forniti da un fornitore in esclusiva  
**Parametri:**
- `supplier` - Nome del fornitore (default: Acme)

**Esempio:** `GET /api/4?supplier=WidgetCorp`

### Endpoint 5 - `/api/5`
**Descrizione:** Fornitori con costo sopra media  
**Parametri:**
- `min_percentage` - Percentuale minima di ricarico (default: 0)

**Esempio:** `GET /api/5?min_percentage=20`

### Endpoint 6 - `/api/6`
**Descrizione:** Fornitori che forniscono tutti i pezzi

### Endpoint 7 - `/api/7`
**Descrizione:** Costo medio per pezzo  
**Parametri:**
- `color` - Filtro per colore (opzionale)
- `sort` - Ordine: ASC o DESC (default: ASC)
- `limit` - Numero max risultati (default: 1000)

**Esempio:** `GET /api/7?color=rosso&sort=DESC&limit=10`

### Endpoint 8 - `/api/8`
**Descrizione:** Pezzi più costosi  
**Parametri:**
- `color` - Filtro per colore (opzionale)
- `limit` - Numero max risultati (default: 10)
- `min_price` - Prezzo minimo (default: 0)

**Esempio:** `GET /api/8?color=blu&limit=5&min_price=100`

### Endpoint 9 - `/api/9`
**Descrizione:** Numero di fornitori per pezzo  
**Parametri:**
- `color` - Filtro per colore (opzionale)
- `min_suppliers` - Minimo numero di fornitori (default: 0)

**Esempio:** `GET /api/9?min_suppliers=3`

### Endpoint 10 - `/api/10`
**Descrizione:** Fornitori con lista dei loro pezzi  
**Parametri:**
- `supplier` - Nome fornitore specifico (opzionale)
- `color` - Colore dei pezzi da filtrar (opzionale)

**Esempio:** `GET /api/10?supplier=Acme&color=rosso`

## 🧪 Test degli Endpoint

### Interfaccia Web
Accedere a `http://localhost:8000/` per un'interfaccia grafica di test.

### Linea di comando (curl)
```bash
curl http://localhost:8000/api/1
curl http://localhost:8000/api/2
# ... etc
```

## 📁 Struttura File

```
.
├── api.php           # API principale con 10 endpoint
├── index.html        # Interfaccia web di test
├── database.sql      # Schema e dati del database
├── composer.json     # Dipendenze PHP
├── .htaccess         # Configurazione routing
└── README.md         # Questo file
```

## 📦 Dipendenze

- PHP >= 7.4
- Slim Framework 4.0
- PDO (MySQL)

## 🔐 Note di Sicurezza

- Utilizzare variabili di ambiente per le credenziali del database
- Implementare autenticazione/autorizzazione in produzione
- Validare sempre gli input
- Utilizzare prepared statements (già implementati tramite PDO)

## 👤 Autore

VERIFICA-A-SORPRESA Project
