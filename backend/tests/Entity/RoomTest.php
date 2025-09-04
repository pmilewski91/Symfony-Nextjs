<?php

namespace App\Tests\Entity;

use App\Entity\Room;
use App\Entity\Reservation;
use App\Tests\Helper\EntityTestHelper;
use PHPUnit\Framework\TestCase;

class RoomTest extends TestCase
{
    public function testRoomCreation(): void
    {
        $room = new Room();
        $room->setName('Conference Room A');
        $room->setDescription('Main conference room');
        $room->setIsActive(true);

        $this->assertEquals('Conference Room A', $room->getName());
        $this->assertEquals('Main conference room', $room->getDescription());
        $this->assertTrue($room->isActive());
        $this->assertInstanceOf(\DateTimeImmutable::class, $room->getCreatedAt());
        $this->assertInstanceOf(\DateTimeImmutable::class, $room->getUpdatedAt());
    }

    public function testRoomReservationsCollection(): void
    {
        $room = EntityTestHelper::createRoom(1, 'Test Room');
        $reservation1 = EntityTestHelper::createReservation(1, $room);
        $reservation2 = EntityTestHelper::createReservation(2, $room);

        $room->addReservation($reservation1);
        $room->addReservation($reservation2);

        $this->assertCount(2, $room->getReservations());
        $this->assertTrue($room->getReservations()->contains($reservation1));
        $this->assertTrue($room->getReservations()->contains($reservation2));
    }

    public function testRoomRemoveReservation(): void
    {
        $room = EntityTestHelper::createRoom(1, 'Test Room');
        $reservation = EntityTestHelper::createReservation(1, $room);

        $room->addReservation($reservation);
        $this->assertCount(1, $room->getReservations());

        $room->removeReservation($reservation);
        $this->assertCount(0, $room->getReservations());
        $this->assertFalse($room->getReservations()->contains($reservation));
    }

    public function testRoomDoesNotAddDuplicateReservation(): void
    {
        $room = EntityTestHelper::createRoom(1, 'Test Room');
        $reservation = EntityTestHelper::createReservation(1, $room);

        $room->addReservation($reservation);
        $room->addReservation($reservation); // Add same reservation again

        $this->assertCount(1, $room->getReservations());
    }

    public function testSetUpdatedAtValue(): void
    {
        $room = new Room();
        $originalUpdatedAt = $room->getUpdatedAt();

        // Simulate the @PreUpdate lifecycle callback
        sleep(1); // Ensure time difference
        $room->setUpdatedAtValue();

        $this->assertNotEquals($originalUpdatedAt, $room->getUpdatedAt());
        $this->assertInstanceOf(\DateTimeImmutable::class, $room->getUpdatedAt());
    }

    public function testRoomGettersAndSetters(): void
    {
        $room = new Room();
        $createdAt = new \DateTimeImmutable('2025-09-01 10:00:00');
        $updatedAt = new \DateTimeImmutable('2025-09-02 11:00:00');

        $room->setName('Test Room');
        $room->setDescription('Test Description');
        $room->setIsActive(false);
        $room->setCreatedAt($createdAt);
        $room->setUpdatedAt($updatedAt);

        $this->assertEquals('Test Room', $room->getName());
        $this->assertEquals('Test Description', $room->getDescription());
        $this->assertFalse($room->isActive());
        $this->assertEquals($createdAt, $room->getCreatedAt());
        $this->assertEquals($updatedAt, $room->getUpdatedAt());
    }

    public function testRoomInitialization(): void
    {
        $room = new Room();

        $this->assertNull($room->getId());
        $this->assertNull($room->getName());
        $this->assertNull($room->getDescription());
        $this->assertNull($room->isActive());
        $this->assertInstanceOf(\DateTimeImmutable::class, $room->getCreatedAt());
        $this->assertInstanceOf(\DateTimeImmutable::class, $room->getUpdatedAt());
        $this->assertCount(0, $room->getReservations());
    }
}
