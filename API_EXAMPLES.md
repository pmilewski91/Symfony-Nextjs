# 📡 API Examples

Praktyczne przykłady użycia REST API dla Conference Room Booking System.

## 🔧 Konfiguracja

### Base URL
```
http://localhost:8000
```

### Headers
```http
Content-Type: application/json
Accept: application/json
```

## 🏠 Zarządzanie salami

### Pobierz wszystkie sale
```bash
curl -X GET http://localhost:8000/api/v1/rooms \
  -H "Accept: application/json"
```

**Response:**
```json
[
  {
    "id": 1,
    "name": "Sala Konferencyjna A",
    "description": "Duża sala konferencyjna z projektorem",
    "isActive": true,
    "createdAt": "2025-09-05T10:00:00+00:00",
    "updatedAt": "2025-09-05T10:00:00+00:00"
  },
  {
    "id": 2,
    "name": "Sala Warsztatowa",
    "description": "Sala do warsztatów i szkoleń",
    "isActive": true,
    "createdAt": "2025-09-05T10:05:00+00:00",
    "updatedAt": "2025-09-05T10:05:00+00:00"
  }
]
```

### Dodaj nową salę
```bash
curl -X POST http://localhost:8000/api/v1/rooms \
  -H "Content-Type: application/json" \
  -H "Accept: application/json" \
  -d '{
    "name": "Sala VIP",
    "description": "Ekskluzywna sala dla ważnych spotkań",
    "isActive": true
  }'
```

**Response (201 Created):**
```json
{
  "id": 3,
  "name": "Sala VIP",
  "description": "Ekskluzywna sala dla ważnych spotkań",
  "isActive": true,
  "createdAt": "2025-09-05T14:30:00+00:00",
  "updatedAt": "2025-09-05T14:30:00+00:00"
}
```

### Edytuj salę
```bash
curl -X PUT http://localhost:8000/api/v1/rooms/3 \
  -H "Content-Type: application/json" \
  -H "Accept: application/json" \
  -d '{
    "name": "Sala VIP Premium",
    "description": "Najbardziej ekskluzywna sala w budynku",
    "isActive": true
  }'
```

### Usuń salę
```bash
curl -X DELETE http://localhost:8000/api/v1/rooms/3 \
  -H "Accept: application/json"
```

**Response (204 No Content)**

## 📅 Zarządzanie rezerwacjami

### Pobierz wszystkie rezerwacje
```bash
curl -X GET http://localhost:8000/api/v1/reservations \
  -H "Accept: application/json"
```

**Response:**
```json
[
  {
    "id": 1,
    "room": {
      "id": 1,
      "name": "Sala Konferencyjna A"
    },
    "reservedBy": "Jan Kowalski",
    "reservedByEmail": "jan.kowalski@example.com",
    "startDateTime": "2025-09-06T09:00:00+00:00",
    "endDateTime": "2025-09-06T10:30:00+00:00",
    "createdAt": "2025-09-05T15:00:00+00:00"
  },
  {
    "id": 2,
    "room": {
      "id": 2,
      "name": "Sala Warsztatowa"
    },
    "reservedBy": "Anna Nowak",
    "reservedByEmail": "anna.nowak@example.com",
    "startDateTime": "2025-09-06T14:00:00+00:00",
    "endDateTime": "2025-09-06T16:00:00+00:00",
    "createdAt": "2025-09-05T15:15:00+00:00"
  }
]
```

### Pobierz rezerwacje dla konkretnej sali
```bash
curl -X GET http://localhost:8000/api/v1/reservations/room/1 \
  -H "Accept: application/json"
```

### Utwórz nową rezerwację
```bash
curl -X POST http://localhost:8000/api/v1/reservations \
  -H "Content-Type: application/json" \
  -H "Accept: application/json" \
  -d '{
    "roomId": 1,
    "reservedBy": "Piotr Wiśniewski",
    "reservedByEmail": "piotr.wisniewski@example.com",
    "startDateTime": "2025-09-07T10:00:00",
    "endDateTime": "2025-09-07T12:00:00"
  }'
```

**Response (201 Created):**
```json
{
  "id": 3,
  "room": {
    "id": 1,
    "name": "Sala Konferencyjna A"
  },
  "reservedBy": "Piotr Wiśniewski",
  "reservedByEmail": "piotr.wisniewski@example.com",
  "startDateTime": "2025-09-07T10:00:00+00:00",
  "endDateTime": "2025-09-07T12:00:00+00:00",
  "createdAt": "2025-09-06T08:30:00+00:00"
}
```

## ❤️ Health Check

### Sprawdź status aplikacji
```bash
curl -X GET http://localhost:8000/api/health \
  -H "Accept: application/json"
```

**Response:**
```json
{
  "status": "OK",
  "timestamp": "2025-09-05T16:45:00+00:00",
  "database": "connected",
  "rabbitmq": "connected",
  "environment": "dev"
}
```

## ⚠️ Obsługa błędów

### Błąd walidacji (400 Bad Request)
```bash
curl -X POST http://localhost:8000/api/v1/rooms \
  -H "Content-Type: application/json" \
  -H "Accept: application/json" \
  -d '{
    "name": "",
    "description": "Opis bez nazwy"
  }'
```

**Response:**
```json
{
  "error": "Validation Failed",
  "message": "Dane wejściowe są nieprawidłowe",
  "details": {
    "name": ["Ta wartość nie powinna być pusta."]
  }
}
```

### Sala nie znaleziona (404 Not Found)
```bash
curl -X GET http://localhost:8000/api/v1/rooms/999 \
  -H "Accept: application/json"
```

**Response:**
```json
{
  "error": "Room Not Found",
  "message": "Sala o ID 999 nie została znaleziona"
}
```

### Konflikt rezerwacji (409 Conflict)
```bash
curl -X POST http://localhost:8000/api/v1/reservations \
  -H "Content-Type: application/json" \
  -H "Accept: application/json" \
  -d '{
    "roomId": 1,
    "reservedBy": "Test User",
    "startDateTime": "2025-09-06T09:30:00",
    "endDateTime": "2025-09-06T10:00:00"
  }'
```

**Response:**
```json
{
  "error": "Reservation Conflict",
  "message": "Sala jest już zarezerwowana w podanym czasie",
  "conflictingReservations": [
    {
      "id": 1,
      "reservedBy": "Jan Kowalski",
      "startDateTime": "2025-09-06T09:00:00+00:00",
      "endDateTime": "2025-09-06T10:30:00+00:00"
    }
  ]
}
```

## 📊 Zaawansowane przykłady

### Pobranie rezerwacji w określonym zakresie dat
```bash
curl -X GET "http://localhost:8000/api/v1/reservations?start=2025-09-06&end=2025-09-07" \
  -H "Accept: application/json"
```

### Filtrowanie aktywnych sal
```bash
curl -X GET "http://localhost:8000/api/v1/rooms?active=true" \
  -H "Accept: application/json"
```

### Masowe tworzenie rezerwacji (przykład skryptu)
```bash
#!/bin/bash

# Tablica rezerwacji
reservations=(
  '{"roomId":1,"reservedBy":"User 1","startDateTime":"2025-09-08T09:00:00","endDateTime":"2025-09-08T10:00:00"}'
  '{"roomId":2,"reservedBy":"User 2","startDateTime":"2025-09-08T11:00:00","endDateTime":"2025-09-08T12:00:00"}'
  '{"roomId":1,"reservedBy":"User 3","startDateTime":"2025-09-08T14:00:00","endDateTime":"2025-09-08T15:00:00"}'
)

# Tworzenie rezerwacji
for reservation in "${reservations[@]}"; do
  echo "Creating reservation: $reservation"
  curl -X POST http://localhost:8000/api/v1/reservations \
    -H "Content-Type: application/json" \
    -H "Accept: application/json" \
    -d "$reservation"
  echo -e "\n---"
done
```

## 🧪 Testowanie API

### Użycie z Postman

1. **Import Collection**: Utwórz kolekcję Postman z powyższymi przykładami
2. **Environment Variables**: 
   - `base_url`: `http://localhost:8000`
   - `api_prefix`: `/api/v1`

### Użycie z HTTPie
```bash
# Instalacja HTTPie
pip install httpie

# Przykłady użycia
http GET localhost:8000/api/v1/rooms
http POST localhost:8000/api/v1/rooms name="Nowa Sala" description="Opis"
```

### Automatyczne testy API
```bash
# Test wszystkich endpoints
#!/bin/bash
set -e

echo "🧪 Testing API endpoints..."

# Test health check
echo "Testing health check..."
curl -f http://localhost:8000/api/health

# Test rooms
echo "Testing rooms endpoint..."
curl -f http://localhost:8000/api/v1/rooms

# Test reservations  
echo "Testing reservations endpoint..."
curl -f http://localhost:8000/api/v1/reservations

echo "✅ All tests passed!"
```

## 📝 Notatki dla deweloperów

### Format daty
- Wszystkie daty w formacie ISO 8601: `YYYY-MM-DDTHH:MM:SS`
- Timezone: UTC lub z offsetem (`+00:00`)

### Paginacja (future feature)
```json
{
  "data": [...],
  "pagination": {
    "current_page": 1,
    "per_page": 20,
    "total": 100,
    "total_pages": 5
  }
}
```

### Rate Limiting (future feature)
```http
X-RateLimit-Limit: 1000
X-RateLimit-Remaining: 999
X-RateLimit-Reset: 1631234567
```

---

**Więcej informacji**: [README.md](./README.md) | [Developer Guide](./DEVELOPER_GUIDE.md)
