# 🔧 Developer Guide

Przewodnik techniczny dla developerów pracujących z Conference Room Booking System.

## 🏗️ Architektura szczegółowa

### Backend Structure (Symfony)
```
backend/src/
├── Entity/                 # Encje Doctrine
│   ├── Room.php           # Sala konferencyjna
│   └── Reservation.php    # Rezerwacja
├── Repository/            # Repozytoria danych
│   ├── RoomRepository.php
│   └── ReservationRepository.php  
├── Controller/            # Kontrolery API
│   ├── RoomController.php
│   ├── ReservationController.php
│   └── HealthController.php
├── Service/              # Logika biznesowa
│   ├── RoomService.php
│   ├── ReservationService.php
│   └── NotificationService.php
├── Message/              # RabbitMQ Messages
│   └── ReservationCreatedMessage.php
└── MessageHandler/       # Message Handlers
    └── ReservationCreatedMessageHandler.php
```

### Frontend Structure (Next.js)
```
frontend/src/
├── app/                  # App Router (Next.js 13+)
│   ├── page.tsx         # Strona główna - lista sal
│   ├── calendar/        # Kalendarz rezerwacji
│   └── layout.tsx       # Layout aplikacji
├── components/          # Komponenty React
│   ├── ui/             # Podstawowe komponenty UI
│   ├── forms/          # Formularze
│   └── layout/         # Komponenty layoutu
├── contexts/           # React Contexts
├── hooks/              # Custom React Hooks
├── lib/               # Biblioteki pomocnicze
├── types/             # TypeScript typy
└── utils/             # Funkcje pomocnicze
```

## 🔄 Workflow rozwijania

### 1. Dodawanie nowych funkcji

#### Backend (Symfony)
```bash
# Nowa encja
docker-compose exec backend bin/console make:entity

# Nowy kontroler
docker-compose exec backend bin/console make:controller

# Migracja
docker-compose exec backend bin/console make:migration
docker-compose exec backend bin/console doctrine:migrations:migrate

# Test
docker-compose exec backend bin/console make:test
```

#### Frontend (Next.js)
```bash
# Nowy komponent
# Utwórz plik w src/components/

# Nowa strona  
# Utwórz plik w src/app/nazwa-strony/page.tsx

# Nowy hook
# Utwórz plik w src/hooks/

# Nowy typ
# Dodaj do src/types/
```

### 2. Testing Strategy

#### Backend Tests
```bash
# Unit tests - testują pojedyncze klasy
tests/Entity/              # Testy encji
tests/Repository/          # Testy repozytoriów  
tests/Service/             # Testy serwisów

# Integration tests - testują przepływ danych
tests/Controller/          # Testy API endpoints

# Uruchomienie
docker-compose exec backend ./run-tests.sh
```

#### Frontend Tests (Future)
```bash
# Component tests
__tests__/components/

# Page tests
__tests__/pages/

# Hook tests  
__tests__/hooks/

# Uruchomienie (gdy zostanie skonfigurowane)
docker-compose exec frontend npm test
```

## 🎯 Best Practices

### Backend

#### 1. Repository Pattern
```php
// ✅ Dobre - logika w repository
$conflictingReservations = $this->reservationRepository
    ->findConflictingReservations($room, $startDateTime, $endDateTime);

// ❌ Złe - logika w kontrolerze
$qb = $entityManager->createQueryBuilder();
// ... kompleksowe zapytanie w kontrolerze
```

#### 2. Service Layer
```php
// ✅ Dobre - logika biznesowa w serwisie
$this->reservationService->create($reservationData);

// ❌ Złe - logika w kontrolerze
if ($this->isConflicting($room, $start, $end)) {
    // logika walidacji w kontrolerze
}
```

#### 3. Exception Handling
```php
// ✅ Dobre - custom exceptions
throw new ReservationConflictException('Room is already booked');

// ❌ Złe - generic exceptions
throw new \Exception('Error');
```

### Frontend

#### 1. Component Structure
```tsx
// ✅ Dobre - mały, focused komponent
const RoomCard = ({ room }: { room: Room }) => {
  return <div>...</div>;
};

// ❌ Złe - duży komponent robi za dużo
const RoomPageEverything = () => {
  // 200+ linii kodu...
};
```

#### 2. State Management
```tsx
// ✅ Dobre - używaj SWR dla API calls
const { data: rooms, error } = useSWR('/api/v1/rooms', fetcher);

// ✅ Dobre - local state dla UI
const [isModalOpen, setIsModalOpen] = useState(false);
```

#### 3. Error Handling
```tsx
// ✅ Dobre - obsługa błędów
if (error) return <ErrorMessage error={error} />;
if (!data) return <LoadingSpinner />;

// ❌ Złe - brak obsługi błędów
const rooms = data; // może być undefined
```

## 🔍 Debugging

### Backend Debugging

#### 1. Logi Symfony
```bash
# Tail logs
docker-compose logs -f backend

# Debug konkretny request
docker-compose exec backend tail -f var/log/dev.log
```

#### 2. Database Debug
```bash
# SQL queries log
docker-compose exec backend bin/console doctrine:query:sql "SELECT * FROM room"

# Schema validation
docker-compose exec backend bin/console doctrine:schema:validate
```

#### 3. RabbitMQ Debug
```bash
# Message stats
docker-compose exec backend bin/console messenger:stats

# Consume messages manually
docker-compose exec backend bin/console messenger:consume async -vv
```

### Frontend Debugging

#### 1. Console Logs
```tsx
// Podczas developmentu
console.log('API Response:', data);
console.error('Error occurred:', error);
```

#### 2. Network Tab
- Sprawdź requests w DevTools
- Verify CORS headers
- Check response status codes

#### 3. React DevTools
- Install browser extension
- Monitor component state
- Performance profiling

## 🛠️ Konfiguracja IDE

### VS Code Extensions
```json
{
  "recommendations": [
    "bmewburn.vscode-intelephense-client",  // PHP
    "bradlc.vscode-tailwindcss",           // Tailwind CSS
    "ms-vscode.vscode-typescript-next",     // TypeScript
    "esbenp.prettier-vscode",               // Prettier
    "ms-vscode.vscode-docker"              // Docker
  ]
}
```

### PHPStorm Configuration
- Enable Symfony plugin
- Configure Docker integration
- Set up Xdebug (optional)

## 🔒 Security Considerations

### Backend
```php
// ✅ Input validation
#[Assert\NotBlank]
#[Assert\Length(min: 1, max: 255)]
private string $name;

// ✅ SQL injection prevention (Doctrine handles this)
$this->createQueryBuilder('r')
    ->where('r.room = :room')
    ->setParameter('room', $room);
```

### Frontend
```tsx
// ✅ XSS prevention (React handles this)
<div>{user.name}</div> // Automatically escaped

// ✅ CSRF (not needed for API-only backend)
// ✅ Validate on both client and server
```

## 📊 Performance Tips

### Backend
- Use Doctrine query optimization
- Implement proper indexing
- Cache frequently accessed data
- Use async messaging for notifications

### Frontend  
- Lazy load components
- Use SWR for efficient caching
- Optimize bundle size
- Implement proper loading states


## 📝 Code Style

### Backend (PHP)
- Follow PSR-12 standards
- Use type declarations
- Document public methods
- Use meaningful variable names

### Frontend (TypeScript)
- Use ESLint configuration
- Prefer interfaces over types
- Use proper typing
- Follow React best practices

---

**Więcej informacji**: [README.md](./README.md) | [Quick Start](./QUICK_START.md)
