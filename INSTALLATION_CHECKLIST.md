# ✅ Installation Checklist

Checklist do weryfikacji poprawnej instalacji Conference Room Booking System.

## 🔧 Przed instalacją

- [ ] **Docker Desktop zainstalowany** (wersja 20.10+)
  ```bash
  docker --version
  ```

- [ ] **Docker Compose dostępny** (wersja 2.0+)
  ```bash
  docker-compose --version
  ```

- [ ] **Git zainstalowany**
  ```bash
  git --version
  ```

- [ ] **Wolne porty sprawdzone**
  ```bash
  netstat -tuln | grep -E ':3000|:5432|:5672|:8000|:15672'
  # Żadne porty nie powinny być zajęte
  ```

- [ ] **Minimum 4GB RAM dostępne**
  ```bash
  free -h
  ```

## 📥 Proces instalacji

- [ ] **Repozytorium sklonowane**
  ```bash
  git clone https://github.com/pmilewski91/symfony-nextjs.git
  cd symfony-nextjs
  ```

- [ ] **Kontenery uruchomione**
  ```bash
  docker-compose up -d
  ```

- [ ] **Status kontenerów sprawdzony**
  ```bash
  docker-compose ps
  # Wszystkie powinny pokazywać "Up"
  ```

## 🔍 Weryfikacja usług

### Frontend (Next.js)
- [ ] **Dostępny pod http://localhost:3000**
- [ ] **Strona główna ładuje się bez błędów**
- [ ] **Menu nawigacji działa**
- [ ] **Responsywny design na mobile**

### Backend (Symfony)
- [ ] **Health check działa**
  ```bash
  curl http://localhost:8000/api/health
  # Powinno zwrócić status "OK"
  ```

- [ ] **API endpoints odpowiadają**
  ```bash
  curl http://localhost:8000/api/v1/rooms
  # Powinno zwrócić listę sal (może być pusta)
  ```

### Baza danych (PostgreSQL)
- [ ] **Kontener działa**
  ```bash
  docker-compose exec postgres pg_isready -U postgres
  ```

- [ ] **Połączenie działa**
  ```bash
  docker-compose exec postgres psql -U postgres -d conference_booking -c "\dt"
  # Powinno pokazać tabele: room, reservation
  ```

- [ ] **Przykładowe dane załadowane**
  ```bash
  docker-compose exec postgres psql -U postgres -d conference_booking -c "SELECT COUNT(*) FROM room;"
  # Powinno pokazać liczbę > 0
  ```

### RabbitMQ
- [ ] **Management UI dostępny pod http://localhost:15672**
- [ ] **Login admin/admin działa**
- [ ] **Queues są widoczne w zakładce "Queues"**

## 🎯 Funkcjonalność

### Zarządzanie salami
- [ ] **Dodawanie nowej sali działa**
  - Przejdź do sekcji "Sale"
  - Kliknij "Dodaj salę"
  - Wypełnij formularz
  - Zapisz

- [ ] **Edycja sali działa**
  - Kliknij przycisk edycji przy sali
  - Zmień dane
  - Zapisz

- [ ] **Usuwanie sali działa**
  - Kliknij przycisk usuwania
  - Potwierdź akcję

### System rezerwacji  
- [ ] **Kalendarz ładuje się**
  - Przejdź do sekcji "Kalendarz"
  - Sprawdź czy wydarzenia są widoczne

- [ ] **Tworzenie rezerwacji działa**
  - Kliknij "Nowa rezerwacja"
  - Wypełnij formularz
  - Sprawdź czy pojawia się w kalendarzu

- [ ] **Walidacja konfliktów działa**
  - Spróbuj stworzyć nakładającą się rezerwację
  - Sprawdź czy pojawi się błąd

## 🧪 API Testing

- [ ] **GET /api/v1/rooms**
  ```bash
  curl -H "Accept: application/json" http://localhost:8000/api/v1/rooms
  ```

- [ ] **POST /api/v1/rooms**
  ```bash
  curl -X POST http://localhost:8000/api/v1/rooms \
    -H "Content-Type: application/json" \
    -d '{"name":"Test Room","description":"Test","isActive":true}'
  ```

- [ ] **GET /api/v1/reservations**
  ```bash
  curl -H "Accept: application/json" http://localhost:8000/api/v1/reservations
  ```

- [ ] **POST /api/v1/reservations**
  ```bash
  curl -X POST http://localhost:8000/api/v1/reservations \
    -H "Content-Type: application/json" \
    -d '{
      "roomId":1,
      "reservedBy":"Test User",
      "startDateTime":"2025-09-10T10:00:00",
      "endDateTime":"2025-09-10T11:00:00"
    }'
  ```

## 🔔 Powiadomienia (RabbitMQ)

- [ ] **Tworzenie rezerwacji generuje powiadomienie**
  - Stwórz nową rezerwację
  - Sprawdź w RabbitMQ Management czy pojawiła się wiadomość
  - Sprawdź logi backend: `docker-compose logs backend`

## 📊 Monitoring

### Logi
- [ ] **Backend logi bez błędów**
  ```bash
  docker-compose logs backend | grep -i error
  # Nie powinno być błędów
  ```

- [ ] **Frontend logi bez błędów**
  ```bash
  docker-compose logs frontend | grep -i error
  # Może być kilka warning, ale nie powinno być błędów
  ```

### Zasoby systemowe
- [ ] **CPU usage rozsądny**
  ```bash
  docker stats --no-stream
  # Nie powinno przekraczać 80% CPU
  ```

- [ ] **Memory usage w normie**
  ```bash
  docker stats --no-stream
  # Backend: ~200-300MB, Frontend: ~100-200MB
  ```

## 🚨 Troubleshooting

### Typowe problemy
- [ ] **Port 3000 zajęty?**
  ```bash
  # Zmień port w docker-compose.yml
  frontend:
    ports:
      - "3001:3000"
  ```

- [ ] **Baza danych nie łączy się?**
  ```bash
  docker-compose restart postgres
  docker-compose logs postgres
  ```

- [ ] **CORS errors w przeglądarce?**
  - Sprawdź czy backend jest dostępny
  - Sprawdź zmienną CORS_ALLOW_ORIGIN w docker-compose.yml

- [ ] **RabbitMQ nie działa?**
  ```bash
  docker-compose restart rabbitmq
  # Poczekaj 30 sekund na uruchomienie
  ```

### Emergency restart
- [ ] **Pełny restart systemu**
  ```bash
  docker-compose down
  docker-compose up -d
  # Poczekaj 2-3 minuty na pełne uruchomienie
  ```

## ✅ Końcowy test

- [ ] **Kompletny workflow działa**
  1. Otwórz http://localhost:3000
  2. Dodaj nową salę
  3. Przejdź do kalendarza
  4. Stwórz rezerwację
  5. Sprawdź czy pojawia się w kalendarzu
  6. Sprawdź powiadomienie w RabbitMQ
  7. Sprawdź API: `curl http://localhost:8000/api/v1/reservations`


**Powodzenia!** 🎉 Jeśli wszystkie punkty są zaznaczone, Twoja instalacja jest kompletna i gotowa do użycia.
