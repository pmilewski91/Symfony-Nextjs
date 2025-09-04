<?php

namespace App\Tests\Entity;

use App\Entity\Reservation;
use App\Entity\Room;
use App\Tests\Helper\EntityTestHelper;
use PHPUnit\Framework\TestCase;

class ReservationTest extends TestCase
{
    public function testReservationCreation(): void
    {
        $room = EntityTestHelper::createRoom(1, 'Test Room');
        $startDateTime = new \DateTime('2025-09-05 10:00:00');
        $endDateTime = new \DateTime('2025-09-05 12:00:00');

        $reservation = new Reservation();
        $reservation->setRoom($room);
        $reservation->setReservedBy('John Doe');
        $reservation->setReservedByEmail('john@example.com');
        $reservation->setStartDateTime($startDateTime);
        $reservation->setEndDateTime($endDateTime);

        $this->assertEquals($room, $reservation->getRoom());
        $this->assertEquals('John Doe', $reservation->getReservedBy());
        $this->assertEquals('john@example.com', $reservation->getReservedByEmail());
        $this->assertEquals($startDateTime, $reservation->getStartDateTime());
        $this->assertEquals($endDateTime, $reservation->getEndDateTime());
        $this->assertInstanceOf(\DateTimeImmutable::class, $reservation->getCreatedAt());
    }

    public function testReservationInitialization(): void
    {
        $reservation = new Reservation();

        $this->assertNull($reservation->getId());
        $this->assertNull($reservation->getRoom());
        $this->assertNull($reservation->getReservedBy());
        $this->assertNull($reservation->getReservedByEmail());
        $this->assertNull($reservation->getStartDateTime());
        $this->assertNull($reservation->getEndDateTime());
        $this->assertInstanceOf(\DateTimeImmutable::class, $reservation->getCreatedAt());
    }

    public function testReservationGettersAndSetters(): void
    {
        $room = EntityTestHelper::createRoom(1, 'Conference Room');
        $startDateTime = new \DateTime('2025-09-10 14:00:00');
        $endDateTime = new \DateTime('2025-09-10 16:00:00');
        $createdAt = new \DateTimeImmutable('2025-09-01 12:00:00');

        $reservation = new Reservation();
        $reservation->setRoom($room);
        $reservation->setReservedBy('Jane Smith');
        $reservation->setReservedByEmail('jane@example.com');
        $reservation->setStartDateTime($startDateTime);
        $reservation->setEndDateTime($endDateTime);
        $reservation->setCreatedAt($createdAt);

        $this->assertEquals($room, $reservation->getRoom());
        $this->assertEquals('Jane Smith', $reservation->getReservedBy());
        $this->assertEquals('jane@example.com', $reservation->getReservedByEmail());
        $this->assertEquals($startDateTime, $reservation->getStartDateTime());
        $this->assertEquals($endDateTime, $reservation->getEndDateTime());
        $this->assertEquals($createdAt, $reservation->getCreatedAt());
    }

    public function testReservationRoomRelationship(): void
    {
        $room = EntityTestHelper::createRoom(1, 'Meeting Room');
        $reservation = EntityTestHelper::createReservation(1, $room);

        // Test that reservation is linked to room
        $this->assertEquals($room, $reservation->getRoom());

        // Test setting room to null
        $reservation->setRoom(null);
        $this->assertNull($reservation->getRoom());
    }

    public function testReservationValidateMethod(): void
    {
        // This test ensures the validate method exists and can be called
        // The actual validation logic would be tested in integration tests
        $reservation = new Reservation();
        
        $this->assertTrue(method_exists($reservation, 'validate'));
    }

    public function testReservationWithHelperMethod(): void
    {
        $room = EntityTestHelper::createRoom(1, 'Helper Room');
        $startDateTime = new \DateTime('2025-09-15 09:00:00');
        $endDateTime = new \DateTime('2025-09-15 10:30:00');

        $reservation = EntityTestHelper::createReservation(
            100,
            $room,
            'Test User',
            'test@example.com',
            $startDateTime,
            $endDateTime
        );

        $this->assertEquals(100, $reservation->getId());
        $this->assertEquals($room, $reservation->getRoom());
        $this->assertEquals('Test User', $reservation->getReservedBy());
        $this->assertEquals('test@example.com', $reservation->getReservedByEmail());
        $this->assertEquals($startDateTime, $reservation->getStartDateTime());
        $this->assertEquals($endDateTime, $reservation->getEndDateTime());
    }
}
