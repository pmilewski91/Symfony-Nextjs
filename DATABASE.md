# 🗄️ Database Schema

Dokumentacja struktury bazy danych dla Conference Room Booking System.

## 🔗 Diagram ERD

```
┌─────────────────┐         ┌──────────────────┐
│      Room       │ 1     ∞ │   Reservation    │
├─────────────────┤◄────────┤──────────────────┤
│ id (PK)         │         │ id (PK)          │
│ name            │         │ room_id (FK)     │
│ description     │         │ reserved_by      │
│ is_active       │         │ reserved_by_email│
│ created_at      │         │ start_date_time  │
│ updated_at      │         │ end_date_time    │
└─────────────────┘         │ created_at       │
                            └──────────────────┘
```

## 📋 Tabele

### `room` - Sale konferencyjne

| Kolumna | Typ | Ograniczenia | Opis |
|---------|-----|-------------|------|
| `id` | INTEGER | PRIMARY KEY, AUTO_INCREMENT | Unikalny identyfikator sali |
| `name` | VARCHAR(255) | NOT NULL, UNIQUE | Nazwa sali |
| `description` | TEXT | NULLABLE | Opis sali i wyposażenia |
| `is_active` | BOOLEAN | NOT NULL, DEFAULT TRUE | Czy sala jest aktywna |
| `created_at` | TIMESTAMP | NOT NULL | Data utworzenia rekordu |
| `updated_at` | TIMESTAMP | NOT NULL | Data ostatniej modyfikacji |

#### Indeksy:
- `PRIMARY KEY (id)`
- `UNIQUE INDEX idx_room_name (name)`
- `INDEX idx_room_active (is_active)`

#### Walidacja:
- `name`: długość 2-255 znaków, wymagane
- `description`: opcjonalne, tekst dowolnej długości
- `is_active`: domyślnie TRUE

### `reservation` - Rezerwacje sal

| Kolumna | Typ | Ograniczenia | Opis |
|---------|-----|-------------|------|
| `id` | INTEGER | PRIMARY KEY, AUTO_INCREMENT | Unikalny identyfikator rezerwacji |
| `room_id` | INTEGER | NOT NULL, FOREIGN KEY | Odniesienie do tabeli `room` |
| `reserved_by` | VARCHAR(255) | NOT NULL | Imię i nazwisko rezerwującego |
| `reserved_by_email` | VARCHAR(255) | NULLABLE | Email rezerwującego |
| `start_date_time` | TIMESTAMP | NOT NULL | Data i czas rozpoczęcia rezerwacji |
| `end_date_time` | TIMESTAMP | NOT NULL | Data i czas zakończenia rezerwacji |
| `created_at` | TIMESTAMP | NOT NULL | Data utworzenia rezerwacji |

#### Klucze obce:
- `FOREIGN KEY (room_id) REFERENCES room(id) ON DELETE CASCADE`

#### Indeksy:
- `PRIMARY KEY (id)`
- `INDEX idx_reservation_room_id (room_id)`
- `INDEX idx_reservation_dates (start_date_time, end_date_time)`
- `INDEX idx_reservation_room_dates (room_id, start_date_time, end_date_time)`

#### Walidacja:
- `reserved_by`: długość 2-255 znaków, wymagane
- `reserved_by_email`: opcjonalne, format email
- `start_date_time`: wymagane, musi być wcześniejsze niż `end_date_time`
- `end_date_time`: wymagane, musi być późniejsze niż `start_date_time`

#### Constraints:
- `CHECK (start_date_time < end_date_time)`

## 📊 Przykładowe dane

### Tabela `room`
```sql
INSERT INTO room (id, name, description, is_active, created_at, updated_at) VALUES
(1, 'Sala Konferencyjna A', 'Duża sala z projektorem i klimatyzacją', true, NOW(), NOW()),
(2, 'Sala Konferencyjna B', 'Średnia sala dla 15 osób', true, NOW(), NOW()),
(3, 'Sala Warsztatowa', 'Sala z flipchartami i sprzętem do warsztatów', true, NOW(), NOW()),
(4, 'Sala VIP', 'Ekskluzywna sala dla ważnych spotkań', false, NOW(), NOW());
```

### Tabela `reservation`
```sql
INSERT INTO reservation (id, room_id, reserved_by, reserved_by_email, start_date_time, end_date_time, created_at) VALUES
(1, 1, 'Jan Kowalski', 'jan.kowalski@example.com', '2025-09-06 09:00:00', '2025-09-06 10:30:00', NOW()),
(2, 2, 'Anna Nowak', 'anna.nowak@example.com', '2025-09-06 14:00:00', '2025-09-06 16:00:00', NOW()),
(3, 1, 'Piotr Wiśniewski', 'piotr.wisniewski@example.com', '2025-09-07 10:00:00', '2025-09-07 12:00:00', NOW());
```

## 🔍 Zapytania SQL

### Podstawowe zapytania

#### Wszystkie aktywne sale
```sql
SELECT id, name, description 
FROM room 
WHERE is_active = true 
ORDER BY name;
```

#### Rezerwacje na dzisiaj
```sql
SELECT r.*, rm.name as room_name 
FROM reservation r
JOIN room rm ON r.room_id = rm.id
WHERE DATE(r.start_date_time) = CURRENT_DATE
ORDER BY r.start_date_time;
```

#### Sprawdzenie dostępności sali
```sql
SELECT COUNT(*) as conflicts
FROM reservation 
WHERE room_id = :room_id 
  AND start_date_time < :end_time 
  AND end_date_time > :start_time;
```

### Zaawansowane zapytania

#### Sale z liczbą rezerwacji
```sql
SELECT 
    r.id,
    r.name,
    COUNT(res.id) as reservation_count
FROM room r
LEFT JOIN reservation res ON r.id = res.room_id
WHERE r.is_active = true
GROUP BY r.id, r.name
ORDER BY reservation_count DESC;
```

#### Top użytkownicy (najwięcej rezerwacji)
```sql
SELECT 
    reserved_by,
    COUNT(*) as reservation_count,
    SUM(EXTRACT(EPOCH FROM (end_date_time - start_date_time))/3600) as total_hours
FROM reservation
WHERE created_at >= CURRENT_DATE - INTERVAL '30 days'
GROUP BY reserved_by
ORDER BY reservation_count DESC
LIMIT 10;
```

#### Wykorzystanie sal w czasie
```sql
SELECT 
    rm.name as room_name,
    DATE(res.start_date_time) as reservation_date,
    SUM(EXTRACT(EPOCH FROM (res.end_date_time - res.start_date_time))/3600) as hours_booked
FROM reservation res
JOIN room rm ON res.room_id = rm.id
WHERE res.start_date_time >= CURRENT_DATE - INTERVAL '7 days'
GROUP BY rm.name, DATE(res.start_date_time)
ORDER BY reservation_date DESC, hours_booked DESC;
```

## 🔧 Migracje

### Tworzenie nowej migracji
```bash
docker-compose exec backend bin/console make:migration
```

### Wykonanie migracji
```bash
docker-compose exec backend bin/console doctrine:migrations:migrate
```

### Historia migracji
```bash
docker-compose exec backend bin/console doctrine:migrations:status
```

## 📈 Optymalizacja

### Indeksy wydajnościowe

```sql
-- Dla częstych zapytań o rezerwacje w zakresie dat
CREATE INDEX idx_reservation_room_date_range 
ON reservation (room_id, start_date_time, end_date_time);

-- Dla zapytań o aktywne sale
CREATE INDEX idx_room_active_name 
ON room (is_active, name);

-- Dla wyszukiwania po emailu
CREATE INDEX idx_reservation_email 
ON reservation (reserved_by_email);
```

### Statystyki wykorzystania

```sql
-- Sprawdzenie rozmiaru tabel
SELECT 
    schemaname,
    tablename,
    attname,
    n_distinct,
    correlation
FROM pg_stats 
WHERE schemaname = 'public';

-- Analiza wydajności indeksów
SELECT 
    schemaname,
    tablename,
    indexname,
    idx_scan,
    idx_tup_read,
    idx_tup_fetch
FROM pg_stat_user_indexes;
```

## 🛠️ Backup i przywracanie

### Backup bazy danych
```bash
docker-compose exec postgres pg_dump -U postgres conference_booking > backup.sql
```

### Przywracanie z backup
```bash
docker-compose exec -i postgres psql -U postgres conference_booking < backup.sql
```

### Backup z kompresją
```bash
docker-compose exec postgres pg_dump -U postgres -Fc conference_booking > backup.dump
docker-compose exec -i postgres pg_restore -U postgres -d conference_booking backup.dump
```

## 🔒 Bezpieczeństwo

### Uprawnienia użytkowników
```sql
-- Tworzenie użytkownika tylko do odczytu
CREATE USER conference_readonly WITH ENCRYPTED PASSWORD 'readonly_password';
GRANT CONNECT ON DATABASE conference_booking TO conference_readonly;
GRANT USAGE ON SCHEMA public TO conference_readonly;
GRANT SELECT ON ALL TABLES IN SCHEMA public TO conference_readonly;

-- Tworzenie użytkownika aplikacji (ograniczone uprawnienia)
CREATE USER conference_app WITH ENCRYPTED PASSWORD 'app_password';
GRANT CONNECT ON DATABASE conference_booking TO conference_app;
GRANT USAGE ON SCHEMA public TO conference_app;
GRANT SELECT, INSERT, UPDATE, DELETE ON ALL TABLES IN SCHEMA public TO conference_app;
GRANT USAGE ON ALL SEQUENCES IN SCHEMA public TO conference_app;
```

### Audit log (przyszła funkcjonalność)
```sql
-- Tabela audit log
CREATE TABLE audit_log (
    id SERIAL PRIMARY KEY,
    table_name VARCHAR(255) NOT NULL,
    operation VARCHAR(50) NOT NULL, -- INSERT, UPDATE, DELETE
    old_values JSONB,
    new_values JSONB,
    user_id VARCHAR(255),
    changed_at TIMESTAMP DEFAULT NOW()
);
```

## 📊 Monitoring

### Sprawdzenie stanu bazy
```sql
-- Rozmiar bazy danych
SELECT pg_size_pretty(pg_database_size('conference_booking'));

-- Rozmiar tabel
SELECT 
    relname AS table_name,
    pg_size_pretty(pg_total_relation_size(relid)) AS size
FROM pg_catalog.pg_statio_user_tables 
ORDER BY pg_total_relation_size(relid) DESC;

-- Aktywne połączenia
SELECT 
    pid,
    usename,
    application_name,
    client_addr,
    state,
    query_start
FROM pg_stat_activity 
WHERE datname = 'conference_booking';
```

---

**Więcej informacji**: [README.md](./README.md) | [API Examples](./API_EXAMPLES.md)
