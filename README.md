# 🏢 Conference Room Booking System

Aplikacja webowa do zarządzania rezerwacjami sal konferencyjnych zbudowana w oparciu o nowoczesny stack technologiczny: **Symfony 7** (backend), **Next.js 15** (frontend), **PostgreSQL** (baza danych) i **RabbitMQ** (message queue).

## 📋 Spis treści

- [Funkcjonalności](#-funkcjonalności)
- [Architektura](#-architektura)
- [Wymagania systemowe](#-wymagania-systemowe)
- [Instalacja](#-instalacja)
- [Konfiguracja](#-konfiguracja)
- [Użytkowanie](#-użytkowanie)
- [API Documentation](#-api-documentation)
- [Rozwój](#-rozwój)
- [Testy](#-testy)
- [Troubleshooting](#-troubleshooting)
- [Dokumentacja dodatkowa](#-dokumentacja-dodatkowa)

## ✨ Funkcjonalności

### 🏠 Zarządzanie salami
- ➕ Dodawanie nowych sal konferencyjnych
- ✏️ Edycja istniejących sal
- 🗑️ Usuwanie sal
- 📋 Przeglądanie listy wszystkich sal

### 📅 System rezerwacji
- 🔒 Rezerwacja sal na określony czas
- 🗓️ Widok kalendarza z rezerwacjami
- ⚠️ Automatyczne wykrywanie konfliktów rezerwacji
- 📧 Powiadomienia o nowych rezerwacjach (RabbitMQ)
- 🔍 Filtrowanie rezerwacji po salach

### 🎯 Dodatkowe funkcje
- 🌐 Responsywny design
- ✅ Walidacja po stronie klienta i serwera
- 🔄 Real-time updates
- 🛡️ Obsługa błędów i walidacji

## 🏗️ Architektura

### Backend (Symfony 7)
- **Framework**: Symfony 7.0 z PHP 8.3.6
- **ORM**: Doctrine ORM
- **API**: RESTful API z proper HTTP status codes
- **Message Queue**: RabbitMQ integration
- **Database**: PostgreSQL 15

### Frontend (Next.js 15)
- **Framework**: Next.js 15.5.2 z React 19
- **Styling**: Tailwind CSS
- **HTTP Client**: Axios z SWR dla cache'owania
- **UI Components**: Headless UI
- **Date Picker**: React DatePicker

### Infrastructure
- **Containerization**: Docker & Docker Compose
- **Database**: PostgreSQL 15 Alpine
- **Message Broker**: RabbitMQ 3 z Management UI

## 💻 Wymagania systemowe

- **Docker** 20.10+ 
- **Docker Compose** 2.0+
- **Git**
- **Minimum 4GB RAM**
- **Wolne porty**: 3000, 5432, 5672, 8000, 15672

### Sprawdzenie wymagań
```bash
# Sprawdź wersje Docker
docker --version
docker-compose --version

# Sprawdź dostępność portów
netstat -tuln | grep -E ':3000|:5432|:5672|:8000|:15672'
```

## 🚀 Instalacja

### 1. Klonowanie repozytorium
```bash
git clone https://github.com/pmilewski91/symfony-nextjs.git
cd symfony-nextjs
```

### 2. Uruchomienie aplikacji
```bash
# Uruchom wszystkie serwisy w tle
docker-compose up -d

# Lub uruchom z logami w foreground
docker-compose up
```

### 3. Sprawdzenie statusu
```bash
# Sprawdź status wszystkich kontenerów
docker-compose ps

# Sprawdź logi
docker-compose logs -f backend
docker-compose logs -f frontend
```

### 4. Dostęp do aplikacji

| Serwis | URL | Opis |
|--------|-----|------|
| 🎨 **Frontend** | http://localhost:3000 | Interfejs użytkownika |
| 🔧 **Backend API** | http://localhost:8000 | REST API |
| 🐘 **PostgreSQL** | localhost:5432 | Database (postgres/postgres) |
| 🐰 **RabbitMQ Management** | http://localhost:15672 | Queue Manager (admin/admin) |
| ❤️ **Health Check** | http://localhost:8000/api/health | Status aplikacji |

## ⚙️ Konfiguracja

### Zmienne środowiskowe

#### Backend (.env)
```bash
APP_ENV=dev
DATABASE_URL=postgresql://postgres:postgres@postgres:5432/conference_booking
MESSENGER_TRANSPORT_DSN=amqp://admin:admin@rabbitmq:5672/%2f/messages
RABBITMQ_URL=amqp://admin:admin@rabbitmq:5672/
CORS_ALLOW_ORIGIN=http://localhost:3000
```

#### Frontend
```bash
NEXT_PUBLIC_API_URL=http://localhost:8000
NODE_ENV=development
```

### Konfiguracja portów
Aby zmienić porty, edytuj `docker-compose.yml`:

```yaml
services:
  frontend:
    ports:
      - "3001:3000"  # Zmiana portu frontend
  backend:
    ports:
      - "8001:8000"  # Zmiana portu backend
```

## 🎯 Użytkowanie

### Pierwsze kroki

1. **Otwórz aplikację** pod adresem http://localhost:3000
2. **Dodaj sale konferencyjne** w sekcji "Sale"
3. **Utwórz rezerwacje** w kalendarzu
4. **Monitoruj powiadomienia** w RabbitMQ Management

### Przykładowe dane

Aplikacja automatycznie ładuje przykładowe dane:
- 🏢 Sala Konferencyjna A (20 miejsc)
- 🏢 Sala Konferencyjna B (15 miejsc)  
- 🏢 Sala Warsztatowa (30 miejsc)

## 📡 API Documentation

### Endpoints - Sale

```http
GET    /api/v1/rooms           # Lista sal
POST   /api/v1/rooms           # Dodaj salę
PUT    /api/v1/rooms/{id}      # Edytuj salę
DELETE /api/v1/rooms/{id}      # Usuń salę
```

### Endpoints - Rezerwacje

```http
GET    /api/v1/reservations              # Lista rezerwacji
POST   /api/v1/reservations              # Nowa rezerwacja
GET    /api/v1/reservations/room/{id}    # Rezerwacje dla sali
```

### Przykłady użycia API

#### Dodanie nowej sali
```bash
curl -X POST http://localhost:8000/api/v1/rooms \
  -H "Content-Type: application/json" \
  -d '{
    "name": "Sala Prezentacyjna",
    "description": "Sala z projektorem i nagłośnieniem",
    "isActive": true
  }'
```

#### Utworzenie rezerwacji
```bash
curl -X POST http://localhost:8000/api/v1/reservations \
  -H "Content-Type: application/json" \
  -d '{
    "roomId": 1,
    "reservedBy": "Jan Kowalski",
    "reservedByEmail": "jan.kowalski@example.com",
    "startDateTime": "2025-09-06T10:00:00",
    "endDateTime": "2025-09-06T12:00:00"
  }'
```

### Kody odpowiedzi

| Kod | Znaczenie |
|-----|-----------|
| 200 | ✅ Sukces |
| 201 | ✅ Utworzono |
| 400 | ❌ Błąd walidacji |
| 404 | ❌ Nie znaleziono |
| 409 | ⚠️ Konflikt rezerwacji |
| 500 | 💥 Błąd serwera |

## 🛠️ Rozwój

### Uruchomienie w trybie development

```bash
# Uruchomienie z hot reload
docker-compose up

# Restart konkretnego serwisu
docker-compose restart backend
docker-compose restart frontend
```

### Dostęp do kontenerów

```bash
# Shell w kontenerze backend
docker-compose exec backend sh

# Shell w kontenerze frontend  
docker-compose exec frontend sh

# Dostęp do bazy danych
docker-compose exec postgres psql -U postgres -d conference_booking
```

### Przydatne komendy Symfony

```bash
# Wykonanie migracji
docker-compose exec backend bin/console doctrine:migrations:migrate

# Załadowanie fixtures
docker-compose exec backend bin/console doctrine:fixtures:load

# Cache clear
docker-compose exec backend bin/console cache:clear

# Sprawdzenie routingu
docker-compose exec backend bin/console debug:router
```

### Przydatne komendy Next.js

```bash
# Instalacja nowych pakietów
docker-compose exec frontend npm install package-name

# Lint
docker-compose exec frontend npm run lint

# Build production
docker-compose exec frontend npm run build
```

## 🧪 Testy

### Backend (PHPUnit)

```bash
# Uruchom wszystkie testy
docker-compose exec backend ./run-tests.sh

# Testy z coverage
docker-compose exec backend vendor/bin/phpunit --coverage-html coverage

# Testy konkretnej klasy
docker-compose exec backend vendor/bin/phpunit tests/Controller/RoomControllerTest.php
```

### Struktura testów

```
tests/
├── Controller/          # Testy kontrolerów
├── Entity/             # Testy encji  
├── Repository/         # Testy repozytoriów
├── Service/           # Testy serwisów
└── Helper/            # Pomocnicze klasy testowe
```

## 🔧 Troubleshooting

### Częste problemy

#### 🐳 Kontenery nie startują

```bash
# Sprawdź logi
docker-compose logs

# Wyczyść stare kontenery
docker-compose down -v
docker system prune -a

# Przebuduj obrazy
docker-compose build --no-cache
docker-compose up
```

#### 🔌 Problemy z bazą danych

```bash
# Restart PostgreSQL
docker-compose restart postgres

# Sprawdź connectiv
docker-compose exec backend bin/console doctrine:schema:validate

# Resetuj bazę (OSTROŻNIE!)
docker-compose exec backend bin/console doctrine:database:drop --force
docker-compose exec backend bin/console doctrine:database:create
docker-compose exec backend bin/console doctrine:migrations:migrate
```

#### 🐰 Problemy z RabbitMQ

```bash
# Restart RabbitMQ
docker-compose restart rabbitmq

# Sprawdź kolejki w Management UI
# http://localhost:15672 (admin/admin)

# Sprawdź connection w backend
docker-compose exec backend bin/console messenger:stats
```

#### 🌐 CORS Errors

Sprawdź konfigurację w `backend/config/packages/nelmio_cors.yaml`:

```yaml
nelmio_cors:
    defaults:
        origin_regex: true
        allow_origin: ['%env(CORS_ALLOW_ORIGIN)%']
        allow_methods: ['GET', 'OPTIONS', 'POST', 'PUT', 'PATCH', 'DELETE']
        allow_headers: ['Content-Type', 'Authorization']
        expose_headers: ['Link']
        max_age: 3600
```

#### 📱 Frontend nie łączy się z API

```bash
# Sprawdź zmienne środowiskowe
docker-compose exec frontend env | grep NEXT_PUBLIC

# Sprawdź network connectivity
docker-compose exec frontend curl http://backend:8000/api/health
```

### Przydatne komendy debugowania

```bash
# Stan wszystkich kontenerów
docker-compose ps

# Zasoby systemowe
docker stats

# Sprawdź sieci Docker
docker network ls
docker network inspect symfony-nextjs_conference_network

# Sprawdź volumes
docker volume ls
```

## 📄 Licencja

Ten projekt jest udostępniony na licencji MIT.

**Więcej informacji**: [README.md](./README.md) | [API Examples](./API_EXAMPLES.md)

## 📚 Dokumentacja dodatkowa

### Przewodniki dla użytkowników
- 🚀 **[Quick Start Guide](./QUICK_START.md)** - Szybkie uruchomienie w 5 minut
- ✅ **[Installation Checklist](./INSTALLATION_CHECKLIST.md)** - Lista kontrolna instalacji
- 📖 **[README.md](./README.md)** - Główna dokumentacja projektu (ten plik)

### Dokumentacja techniczna
- 🔧 **[Developer Guide](./DEVELOPER_GUIDE.md)** - Przewodnik dla programistów
- 📡 **[API Examples](./API_EXAMPLES.md)** - Przykłady użycia REST API  
- 🗄️ **[Database Schema](./DATABASE.md)** - Struktura bazy danych
- 🧪 **[Tests Documentation](./backend/docs/TESTS.md)** - Dokumentacja testów

### Pliki konfiguracyjne
- 🐳 **[docker-compose.yml](./docker-compose.yml)** - Konfiguracja Docker
- ⚙️ **[.env.example](./.env.example)** - Przykładowe zmienne środowiskowe

---

**Autor**: [pmilewski91](https://github.com/pmilewski91)  
**Data utworzenia**: Wrzesień 2025  
**Wersja**: 1.0.0