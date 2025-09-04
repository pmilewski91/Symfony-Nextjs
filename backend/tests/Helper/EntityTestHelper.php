<?php

namespace App\Tests\Helper;

use App\Entity\Room;
use App\Entity\Reservation;

/**
 * Helper class for creating test entities
 */
class EntityTestHelper
{
    public static function createRoom(
        int $id = null,
        string $name = 'Test Room',
        string $description = null,
        bool $isActive = true
    ): Room {
        $room = new Room();
        $room->setName($name);
        $room->setDescription($description);
        $room->setIsActive($isActive);
        
        if ($id !== null) {
            // Use reflection to set the ID since it's normally set by Doctrine
            $reflection = new \ReflectionClass($room);
            $idProperty = $reflection->getProperty('id');
            $idProperty->setAccessible(true);
            $idProperty->setValue($room, $id);
        }
        
        return $room;
    }

    public static function createReservation(
        int $id = null,
        Room $room = null,
        string $reservedBy = 'John Doe',
        string $reservedByEmail = 'john@example.com',
        \DateTime $startDateTime = null,
        \DateTime $endDateTime = null
    ): Reservation {
        $reservation = new Reservation();
        
        if ($room !== null) {
            $reservation->setRoom($room);
        }
        
        $reservation->setReservedBy($reservedBy);
        $reservation->setReservedByEmail($reservedByEmail);
        $reservation->setStartDateTime($startDateTime ?? new \DateTime('2025-09-05 10:00:00'));
        $reservation->setEndDateTime($endDateTime ?? new \DateTime('2025-09-05 12:00:00'));
        
        if ($id !== null) {
            // Use reflection to set the ID since it's normally set by Doctrine
            $reflection = new \ReflectionClass($reservation);
            $idProperty = $reflection->getProperty('id');
            $idProperty->setAccessible(true);
            $idProperty->setValue($reservation, $id);
        }
        
        return $reservation;
    }
}
