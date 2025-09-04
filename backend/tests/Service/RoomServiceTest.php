<?php

namespace App\Tests\Service;

use App\Entity\Room;
use App\Exception\ReservationConflictException;
use App\Exception\RoomNotFoundException;
use App\Exception\ValidationException;
use App\Repository\RoomRepository;
use App\Service\RoomService;
use Doctrine\Common\Collections\ArrayCollection;
use Doctrine\ORM\EntityManagerInterface;
use PHPUnit\Framework\MockObject\MockObject;
use PHPUnit\Framework\TestCase;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\Serializer\SerializerInterface;
use Symfony\Component\Validator\ConstraintViolation;
use Symfony\Component\Validator\ConstraintViolationList;
use Symfony\Component\Validator\Validator\ValidatorInterface;

class RoomServiceTest extends TestCase
{
    private RoomService $roomService;
    private RoomRepository&MockObject $roomRepository;
    private SerializerInterface&MockObject $serializer;
    private ValidatorInterface&MockObject $validator;
    private EntityManagerInterface&MockObject $entityManager;

    protected function setUp(): void
    {
        $this->roomRepository = $this->createMock(RoomRepository::class);
        $this->serializer = $this->createMock(SerializerInterface::class);
        $this->validator = $this->createMock(ValidatorInterface::class);
        $this->entityManager = $this->createMock(EntityManagerInterface::class);

        $this->roomService = new RoomService(
            $this->roomRepository,
            $this->serializer,
            $this->validator,
            $this->entityManager
        );
    }

    public function testListAllRooms(): void
    {
        $rooms = [
            $this->createRoom(1, 'Room 1', true),
            $this->createRoom(2, 'Room 2', false)
        ];

        $request = new Request();

        $this->roomRepository
            ->expects($this->once())
            ->method('findAll')
            ->willReturn($rooms);

        $this->serializer
            ->expects($this->once())
            ->method('serialize')
            ->with($rooms, 'json', ['groups' => ['room:read']])
            ->willReturn('{"data": "serialized_rooms"}');

        $result = $this->roomService->list($request);

        $this->assertEquals('{"data": "serialized_rooms"}', $result);
    }

    public function testListActiveRoomsOnly(): void
    {
        $activeRooms = [
            $this->createRoom(1, 'Room 1', true)
        ];

        $request = new Request(['active_only' => 'true']);

        $this->roomRepository
            ->expects($this->once())
            ->method('findActiveRooms')
            ->willReturn($activeRooms);

        $this->serializer
            ->expects($this->once())
            ->method('serialize')
            ->with($activeRooms, 'json', ['groups' => ['room:read']])
            ->willReturn('{"data": "active_rooms"}');

        $result = $this->roomService->list($request);

        $this->assertEquals('{"data": "active_rooms"}', $result);
    }

    public function testCreateRoomSuccess(): void
    {
        $requestData = [
            'name' => 'New Room',
            'description' => 'A new conference room',
            'isActive' => true
        ];

        $request = new Request([], [], [], [], [], [], json_encode($requestData));

        $this->roomRepository
            ->expects($this->once())
            ->method('findByName')
            ->with('New Room')
            ->willReturn(null);

        $this->validator
            ->expects($this->once())
            ->method('validate')
            ->willReturn(new ConstraintViolationList());

        $this->entityManager
            ->expects($this->once())
            ->method('persist')
            ->with($this->isInstanceOf(Room::class));

        $this->entityManager
            ->expects($this->once())
            ->method('flush');

        $this->serializer
            ->expects($this->once())
            ->method('serialize')
            ->with($this->isInstanceOf(Room::class), 'json', ['groups' => ['room:read']])
            ->willReturn('{"id": 1, "name": "New Room"}');

        $result = $this->roomService->create($request);

        $this->assertEquals('{"id": 1, "name": "New Room"}', $result);
    }

    public function testCreateRoomInvalidJson(): void
    {
        $request = new Request([], [], [], [], [], [], 'invalid json');

        $this->expectException(ValidationException::class);
        $this->expectExceptionMessage('Invalid JSON data provided');

        $this->roomService->create($request);
    }

    public function testCreateRoomDuplicateName(): void
    {
        $requestData = ['name' => 'Existing Room'];
        $request = new Request([], [], [], [], [], [], json_encode($requestData));

        $existingRoom = $this->createRoom(1, 'Existing Room', true);

        $this->roomRepository
            ->expects($this->once())
            ->method('findByName')
            ->with('Existing Room')
            ->willReturn($existingRoom);

        $this->expectException(ValidationException::class);
        $this->expectExceptionMessage('Room with this name already exists');

        $this->roomService->create($request);
    }

    public function testCreateRoomValidationError(): void
    {
        $requestData = ['name' => ''];
        $request = new Request([], [], [], [], [], [], json_encode($requestData));

        $this->roomRepository
            ->expects($this->once())
            ->method('findByName')
            ->willReturn(null);

        $violation = new ConstraintViolation(
            'Room name cannot be empty',
            null,
            [],
            null,
            'name',
            ''
        );
        $violationList = new ConstraintViolationList([$violation]);

        $this->validator
            ->expects($this->once())
            ->method('validate')
            ->willReturn($violationList);

        $this->expectException(ValidationException::class);
        $this->expectExceptionMessage('Validation failed');

        $this->roomService->create($request);
    }

    public function testUpdateRoomSuccess(): void
    {
        $requestData = [
            'name' => 'Updated Room',
            'description' => 'Updated description',
            'isActive' => false
        ];

        $request = new Request([], [], [], [], [], [], json_encode($requestData));
        $existingRoom = $this->createRoom(1, 'Old Room', true);

        $this->roomRepository
            ->expects($this->once())
            ->method('find')
            ->with(1)
            ->willReturn($existingRoom);

        $this->roomRepository
            ->expects($this->once())
            ->method('findByName')
            ->with('Updated Room')
            ->willReturn(null);

        $this->validator
            ->expects($this->once())
            ->method('validate')
            ->willReturn(new ConstraintViolationList());

        $this->entityManager
            ->expects($this->once())
            ->method('flush');

        $this->serializer
            ->expects($this->once())
            ->method('serialize')
            ->with($existingRoom, 'json', ['groups' => ['room:read']])
            ->willReturn('{"id": 1, "name": "Updated Room"}');

        $result = $this->roomService->update(1, $request);

        $this->assertEquals('{"id": 1, "name": "Updated Room"}', $result);
    }

    public function testUpdateRoomNotFound(): void
    {
        $request = new Request([], [], [], [], [], [], '{"name": "Updated Room"}');

        $this->roomRepository
            ->expects($this->once())
            ->method('find')
            ->with(999)
            ->willReturn(null);

        $this->expectException(RoomNotFoundException::class);

        $this->roomService->update(999, $request);
    }

    public function testUpdateRoomInvalidJson(): void
    {
        $existingRoom = $this->createRoom(1, 'Room', true);
        $request = new Request([], [], [], [], [], [], 'invalid json');

        $this->roomRepository
            ->expects($this->once())
            ->method('find')
            ->with(1)
            ->willReturn($existingRoom);

        $this->expectException(ValidationException::class);
        $this->expectExceptionMessage('Invalid JSON data provided');

        $this->roomService->update(1, $request);
    }

    public function testUpdateRoomDuplicateName(): void
    {
        $requestData = ['name' => 'Another Room'];
        $request = new Request([], [], [], [], [], [], json_encode($requestData));

        $existingRoom = $this->createRoom(1, 'Room 1', true);
        $anotherRoom = $this->createRoom(2, 'Another Room', true);

        $this->roomRepository
            ->expects($this->once())
            ->method('find')
            ->with(1)
            ->willReturn($existingRoom);

        $this->roomRepository
            ->expects($this->once())
            ->method('findByName')
            ->with('Another Room')
            ->willReturn($anotherRoom);

        $this->expectException(ValidationException::class);
        $this->expectExceptionMessage('Room with this name already exists');

        $this->roomService->update(1, $request);
    }

    public function testDeleteRoomSuccess(): void
    {
        $room = $this->createRoom(1, 'Room to Delete', true);
        $room->expects($this->once())
            ->method('getReservations')
            ->willReturn(new ArrayCollection());

        $this->roomRepository
            ->expects($this->once())
            ->method('find')
            ->with(1)
            ->willReturn($room);

        $this->entityManager
            ->expects($this->once())
            ->method('remove')
            ->with($room);

        $this->entityManager
            ->expects($this->once())
            ->method('flush');

        $result = $this->roomService->delete(1);

        $this->assertSame($room, $result);
    }

    public function testDeleteRoomNotFound(): void
    {
        $this->roomRepository
            ->expects($this->once())
            ->method('find')
            ->with(999)
            ->willReturn(null);

        $this->expectException(RoomNotFoundException::class);

        $this->roomService->delete(999);
    }

    public function testDeleteRoomWithReservations(): void
    {
        $room = $this->createRoom(1, 'Room with Reservations', true);
        
        // Create a mock reservation to simulate existing reservations
        $reservations = new ArrayCollection(['reservation1', 'reservation2']);
        
        $room->expects($this->once())
            ->method('getReservations')
            ->willReturn($reservations);

        $this->roomRepository
            ->expects($this->once())
            ->method('find')
            ->with(1)
            ->willReturn($room);

        $this->expectException(ReservationConflictException::class);

        $this->roomService->delete(1);
    }

    private function createRoom(int $id, string $name, bool $isActive): Room&MockObject
    {
        $room = $this->createMock(Room::class);
        $room->method('getId')->willReturn($id);
        $room->method('getName')->willReturn($name);
        $room->method('isActive')->willReturn($isActive);
        
        return $room;
    }
}
