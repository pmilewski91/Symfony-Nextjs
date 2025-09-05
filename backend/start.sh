#!/bin/sh

# Czekamy na bazę danych
echo "Waiting for database..."
until bin/console doctrine:database:create --if-not-exists >/dev/null 2>&1; do
    sleep 1
done

echo "Database is ready!"

# Uruchamiamy migracje
echo "Running migrations..."
bin/console doctrine:migrations:migrate --no-interaction

# Sprawdzamy czy są już dane w bazie (sprawdzamy czy tabela room istnieje i ma dane)
echo "Checking if fixtures need to be loaded..."
TABLES_EXIST=$(bin/console doctrine:query:sql "SELECT COUNT(*) FROM information_schema.tables WHERE table_name = 'room'" 2>/dev/null | grep -o '[0-9]\+' | tail -1)

if [ "$TABLES_EXIST" = "1" ]; then
    ROOM_COUNT=$(bin/console doctrine:query:sql "SELECT COUNT(*) FROM room" 2>/dev/null | grep -o '[0-9]\+' | tail -1)
    if [ "$ROOM_COUNT" = "0" ]; then
        echo "Loading fixtures..."
        bin/console doctrine:fixtures:load --no-interaction
        echo "Fixtures loaded successfully!"
    else
        echo "Database already contains $ROOM_COUNT rooms, skipping fixtures."
    fi
else
    echo "Tables don't exist yet, fixtures will be loaded after migrations."
    bin/console doctrine:fixtures:load --no-interaction
    echo "Fixtures loaded successfully!"
fi

echo "Starting application..."
exec php -S 0.0.0.0:8000 -t public
