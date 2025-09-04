#!/bin/bash

# Test Runner Script for Symfony Application
# Autor: Generated for Conference Room Booking System

echo "==================================="
echo "   Symfony Backend Test Suite     "
echo "==================================="

# Change to backend directory
cd "$(dirname "$0")"

# Check if PHPUnit is available
if ! command -v php &> /dev/null; then
    echo "Error: PHP not found!"
    exit 1
fi

if [ ! -f "bin/phpunit" ]; then
    echo "Error: PHPUnit not found! Run 'composer install' first."
    exit 1
fi

echo ""
echo "Running all tests..."
echo "-----------------------------------"

# Run all tests with verbose output
php bin/phpunit --configuration phpunit.dist.xml tests/

# Check the exit code
TEST_RESULT=$?

echo ""
echo "-----------------------------------"

if [ $TEST_RESULT -eq 0 ]; then
    echo "✅ All tests passed successfully!"
    
    echo ""
    echo "Running specific test suites:"
    echo ""
    
    echo "🧪 Unit Tests (Entities & Services):"
    php bin/phpunit tests/Entity/ tests/Service/ --testdox
    
    echo ""
    echo "🌐 Integration Tests (Controllers):"
    php bin/phpunit tests/Controller/ --testdox
    
else
    echo "❌ Tests failed! Exit code: $TEST_RESULT"
    exit $TEST_RESULT
fi

echo ""
echo "==================================="
echo "         Test Summary              "
echo "==================================="
echo "Total test files created:"
echo "- Controller Tests: 3 files"
echo "- Service Tests: 1 file"
echo "- Entity Tests: 2 files"
echo "- Helper Classes: 1 file"
echo ""
echo "Test categories:"
echo "- HealthController: Basic API health checks"
echo "- RoomController: CRUD operations for rooms"
echo "- ReservationController: Reservation management"
echo "- RoomService: Business logic for room operations"
echo "- Room Entity: Room model unit tests"
echo "- Reservation Entity: Reservation model unit tests"
echo "==================================="
