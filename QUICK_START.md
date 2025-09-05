# 🚀 Quick Start Guide

Ten przewodnik pozwoli Ci uruchomić aplikację Conference Room Booking System w **mniej niż 5 minut**.

## ⚡ Szybkie uruchomienie

### 1️⃣ Wymagania
Upewnij się, że masz zainstalowane:
- Docker
- Git

### 2️⃣ Klonowanie i uruchomienie
```bash
# Sklonuj repozytorium
git clone https://github.com/pmilewski91/symfony-nextjs.git
cd symfony-nextjs

# Uruchom wszystkie serwisy
docker-compose up -d

# Sprawdź status (wszystkie powinny być "Up")
docker-compose ps
```

### 3️⃣ Dostęp do aplikacji
Po uruchomieniu kontenerów (może potrwać 2-3 minuty):

| 🎯 Aplikacja | 🔗 URL | 👤 Login |
|-------------|--------|----------|
| **Frontend** | http://localhost:3000 | - |
| **API Backend** | http://localhost:8000/api/health | - |
| **RabbitMQ UI** | http://localhost:15672 | admin / admin |

### 4️⃣ Pierwsze kroki
1. Otwórz http://localhost:3000
2. Przejdź do sekcji "Sale" i dodaj nową salę
3. Przejdź do "Kalendarza" i utwórz rezerwację
4. Sprawdź powiadomienia w RabbitMQ Management UI

## 🔍 Sprawdzenie czy wszystko działa

### Szybki test API
```bash
# Test health check
curl http://localhost:8000/api/health

# Pobranie listy sal
curl http://localhost:8000/api/v1/rooms
```

### Sprawdzenie logów
```bash
# Logi backend
docker-compose logs backend

# Logi frontend  
docker-compose logs frontend
```

## 🛑 Zatrzymanie aplikacji
```bash
# Zatrzymaj wszystkie kontenery
docker-compose down

# Zatrzymaj i usuń dane (OSTROŻNIE!)
docker-compose down -v
```

## ❗ Rozwiązywanie problemów

### Port już zajęty
```bash
# Sprawdź co używa portu 3000
sudo lsof -i :3000

# Lub zmień port w docker-compose.yml
ports:
  - "3001:3000"  # Zamiast 3000:3000
```

### Kontenery nie startują
```bash
# Wyczyść cache Docker
docker system prune -a

# Przebuduj obrazy
docker-compose build --no-cache
docker-compose up
```

### Baza danych nie łączy się
```bash
# Restart PostgreSQL
docker-compose restart postgres

# Sprawdź logi
docker-compose logs postgres
```

## 📚 Więcej informacji

Szczegółową dokumentację znajdziesz w [README.md](./README.md).

---
💡 **Tip**: Dodaj ten projekt do zakładek - po pierwszym uruchomieniu wystarczy `docker-compose up -d`!
