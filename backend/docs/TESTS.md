# Dokumentacja Testów Jednostkowych - Backend

## Przegląd

Ten projekt zawiera kompleksowy zestaw testów jednostkowych dla aplikacji Conference Room Booking System opartej na Symfony. Testy obejmują wszystkie główne komponenty aplikacji: kontrolery, serwisy i encje.

## Struktura Testów

```
tests/
├── Controller/
│   ├── HealthControllerTest.php      # Testy kontrolera zdrowia API
│   ├── RoomControllerTest.php        # Testy CRUD operacji pokoi
│   └── ReservationControllerTest.php # Testy zarządzania rezerwacjami
├── Service/
│   └── RoomServiceTest.php           # Testy logiki biznesowej pokoi
├── Entity/
│   ├── RoomTest.php                  # Testy jednostkowe encji Room
│   └── ReservationTest.php           # Testy jednostkowe encji Reservation
├── Helper/
│   └── EntityTestHelper.php          # Klasa pomocnicza do tworzenia encji testowych
└── bootstrap.php                     # Bootstrap dla testów
```

## Rodzaje Testów

### 1. Testy Kontrolerów (Integration Tests)
Testują funkcjonalność HTTP endpoints z mockami serwisów:

#### HealthController
- ✅ `testHealthCheck()` - Test podstawowego endpoint'u zdrowia
- ✅ `testApiHealthCheck()` - Test API health check endpoint'u

#### RoomController  
- ✅ `testListRoomsSuccess()` - Pobieranie listy pokoi
- ✅ `testListRoomsServerError()` - Obsługa błędów serwera
- ✅ `testCreateRoomSuccess()` - Tworzenie nowego pokoju
- ✅ `testCreateRoomValidationError()` - Walidacja błędów przy tworzeniu
- ✅ `testUpdateRoomSuccess()` - Aktualizacja pokoju
- ✅ `testUpdateRoomNotFound()` - Aktualizacja nieistniejącego pokoju
- ✅ `testDeleteRoomSuccess()` - Usuwanie pokoju
- ✅ `testDeleteRoomNotFound()` - Usuwanie nieistniejącego pokoju
- ✅ `testDeleteRoomWithReservations()` - Obsługa konfliktu przy usuwaniu

#### ReservationController
- ✅ `testListReservationsSuccess()` - Pobieranie listy rezerwacji
- ✅ `testListReservationsValidationError()` - Błędy walidacji przy listowaniu
- ✅ `testCreateReservationSuccess()` - Tworzenie rezerwacji
- ✅ `testCreateReservationValidationError()` - Błędy walidacji przy tworzeniu
- ✅ `testCreateReservationRoomNotFound()` - Rezerwacja nieistniejącego pokoju
- ✅ `testCreateReservationTimeConflict()` - Konflikt czasowy rezerwacji
- ✅ `testListByRoomSuccess()` - Lista rezerwacji dla pokoju
- ✅ `testListByRoomNotFound()` - Lista dla nieistniejącego pokoju
- ✅ `testListByRoomValidationError()` - Błędy walidacji przy listowaniu dla pokoju
- ✅ `testListReservationsServerError()` - Obsługa błędów serwera

### 2. Testy Serwisów (Unit Tests)
Testują logikę biznesową z mockami repozytoriów i zależności:

#### RoomService
- ✅ `testListAllRooms()` - Listowanie wszystkich pokoi
- ✅ `testListActiveRoomsOnly()` - Listowanie tylko aktywnych pokoi
- ✅ `testCreateRoomSuccess()` - Pomyślne tworzenie pokoju
- ✅ `testCreateRoomInvalidJson()` - Błąd nieprawidłowego JSON
- ✅ `testCreateRoomDuplicateName()` - Błąd duplikatu nazwy
- ✅ `testCreateRoomValidationError()` - Błędy walidacji
- ✅ `testUpdateRoomSuccess()` - Pomyślna aktualizacja
- ✅ `testUpdateRoomNotFound()` - Aktualizacja nieistniejącego pokoju
- ✅ `testUpdateRoomInvalidJson()` - Błąd JSON przy aktualizacji
- ✅ `testUpdateRoomDuplicateName()` - Duplikacja nazwy przy aktualizacji
- ✅ `testDeleteRoomSuccess()` - Pomyślne usunięcie
- ✅ `testDeleteRoomNotFound()` - Usuwanie nieistniejącego pokoju
- ✅ `testDeleteRoomWithReservations()` - Usuwanie pokoju z rezerwacjami

### 3. Testy Encji (Unit Tests)
Testują modele danych i ich zachowania:

#### Room Entity
- ✅ `testRoomCreation()` - Tworzenie pokoju
- ✅ `testRoomReservationsCollection()` - Kolekcja rezerwacji
- ✅ `testRoomRemoveReservation()` - Usuwanie rezerwacji
- ✅ `testRoomDoesNotAddDuplicateReservation()` - Unikanie duplikatów
- ✅ `testSetUpdatedAtValue()` - Aktualizacja czasu modyfikacji
- ✅ `testRoomGettersAndSetters()` - Gettery i settery
- ✅ `testRoomInitialization()` - Inicjalizacja pokoju

#### Reservation Entity
- ✅ `testReservationCreation()` - Tworzenie rezerwacji
- ✅ `testReservationInitialization()` - Inicjalizacja rezerwacji
- ✅ `testReservationGettersAndSetters()` - Gettery i settery
- ✅ `testReservationRoomRelationship()` - Relacja z pokojem
- ✅ `testReservationValidateMethod()` - Istnienie metody walidacji
- ✅ `testReservationWithHelperMethod()` - Użycie klasy pomocniczej

## Użyte Wzorce Testowe

### 1. Mocking
- Używanie `PHPUnit\Framework\MockObject\MockObject` do mockowania zależności
- Mockowanie serwisów w testach kontrolerów
- Mockowanie repozytoriów i EntityManager w testach serwisów

### 2. Test Doubles
- Mock objects dla zewnętrznych zależności
- Stub methods dla zwracania określonych wartości

### 3. Data Providers (możliwe rozszerzenie)
- Przygotowane do użycia w przyszłych rozszerzeniach testów

### 4. Fixture Builders
- `EntityTestHelper` do tworzenia obiektów testowych
- Refleksja do ustawiania ID encji

## Uruchamianie Testów

### Wszystkie testy
```bash
./run-tests.sh
```

### Konkretne grupy testów
```bash
# Testy kontrolerów
php bin/phpunit tests/Controller/

# Testy serwisów  
php bin/phpunit tests/Service/

# Testy encji
php bin/phpunit tests/Entity/

# Konkretny test
php bin/phpunit tests/Controller/RoomControllerTest.php
```

### Z szczegółowym wyjściem
```bash
php bin/phpunit tests/ --verbose --testdox
```

## Statystyki Testów

- **Łączna liczba testów**: 47
- **Liczba asercji**: 237+  
- **Pokrycie kodu**: Kontrolery, Serwisy, Encje
- **Typy testów**: Unit (26), Integration (21)

## Testowane Scenariusze

### Pozytywne
- ✅ Pomyślne operacje CRUD
- ✅ Prawidłowa walidacja danych
- ✅ Poprawne relacje między encjami
- ✅ Serializacja/deserializacja JSON

### Negatywne  
- ✅ Obsługa błędów walidacji
- ✅ Nieistniejące rekordy (404)
- ✅ Konflikty danych (409)
- ✅ Błędy serwera (500)
- ✅ Nieprawidłowy JSON (400)

### Graniczne
- ✅ Puste kolekcje
- ✅ Null values
- ✅ Duplikaty danych
- ✅ Konflikty czasowe

## Wyjątki Testowane

- `ValidationException` - Błędy walidacji z szczegółami
- `RoomNotFoundException` - Nieistniejące pokoje
- `ReservationConflictException` - Konflikty rezerwacji
- `\Exception` - Ogólne błędy systemowe

## Konfiguracja Testów

### phpunit.dist.xml
```xml
- Bootstrap: tests/bootstrap.php
- Test suites: Unit Tests, Integration Tests, All Tests
- Coverage: HTML + Text output
- Strict mode: Deprecations, Notices, Warnings = failures
```

### Środowisko testowe
```
APP_ENV=test
Database: SQLite in-memory (w przyszłości)
HTTP Client: Symfony Test Client
```

## Przyszłe Rozszerzenia

### Planowane Testy
- [ ] Repository tests z testową bazą danych
- [ ] Integration tests z prawdziwą bazą danych
- [ ] Performance tests dla dużych zbiorów danych
- [ ] Security tests dla autoryzacji
- [ ] API contract tests

### Możliwe Ulepszenia
- [ ] Dodanie testów z fixtures
- [ ] Mutation testing z Infection
- [ ] Parallel test execution
- [ ] Custom assertions
- [ ] Test data builders

## Maintenance

### Aktualizacja testów przy zmianach
1. Dodanie nowych endpoint'ów → nowe testy kontrolerów
2. Zmiana logiki biznesowej → aktualizacja testów serwisów
3. Modyfikacja encji → przegląd testów encji
4. Nowe wyjątki → testy obsługi błędów

### Code Coverage
Uruchom z coverage'em:
```bash
php bin/phpunit --coverage-html var/coverage tests/
```

---

**Status**: ✅ Wszystkie testy przechodzą  
**Ostatnia aktualizacja**: 2025-09-04  
**Kompatybilność**: Symfony 7.0, PHP 8.3, PHPUnit 12.3
