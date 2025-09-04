<?php

namespace App\Tests\Controller;

use App\Entity\Room;
use App\Exception\ReservationConflictException;
use App\Exception\RoomNotFoundException;
use App\Exception\ValidationException;
use App\Service\RoomService;
use Symfony\Bundle\FrameworkBundle\Test\WebTestCase;
use Symfony\Component\HttpFoundation\Request;

class RoomControllerTest extends WebTestCase
{
    private $mockRoomService;

    protected function setUp(): void
    {
        parent::setUp();
        $this->mockRoomService = $this->createMock(RoomService::class);
    }

    public function testListRoomsSuccess(): void
    {
        $client = static::createClient();
        
        // Mock service response
        $this->mockRoomService
            ->expects($this->once())
            ->method('list')
            ->willReturn('{"data": [{"id": 1, "name": "Room 1", "isActive": true}]}');

        // Replace service in container
        static::getContainer()->set(RoomService::class, $this->mockRoomService);

        $client->request('GET', '/api/v1/rooms');

        $this->assertResponseIsSuccessful();
        $this->assertResponseStatusCodeSame(200);
        
        $response = $client->getResponse();
        $data = json_decode($response->getContent(), true);
        
        $this->assertArrayHasKey('data', $data);
        $this->assertIsArray($data['data']);
    }

    public function testListRoomsServerError(): void
    {
        $client = static::createClient();
        
        // Mock service to throw exception
        $this->mockRoomService
            ->expects($this->once())
            ->method('list')
            ->willThrowException(new \Exception('Database connection failed'));

        static::getContainer()->set(RoomService::class, $this->mockRoomService);

        $client->request('GET', '/api/v1/rooms');

        $this->assertResponseStatusCodeSame(500);
        
        $response = $client->getResponse();
        $data = json_decode($response->getContent(), true);
        
        $this->assertArrayHasKey('error', $data);
        $this->assertEquals('Failed to fetch rooms', $data['error']);
    }

    public function testCreateRoomSuccess(): void
    {
        $client = static::createClient();
        
        $roomData = [
            'name' => 'Test Room',
            'description' => 'Test Description',
            'isActive' => true
        ];

        $this->mockRoomService
            ->expects($this->once())
            ->method('create')
            ->willReturn('{"id": 1, "name": "Test Room", "isActive": true}');

        static::getContainer()->set(RoomService::class, $this->mockRoomService);

        $client->request('POST', '/api/v1/rooms', [], [], 
            ['CONTENT_TYPE' => 'application/json'],
            json_encode($roomData)
        );

        $this->assertResponseStatusCodeSame(201);
        
        $response = $client->getResponse();
        $data = json_decode($response->getContent(), true);
        
        $this->assertArrayHasKey('id', $data);
        $this->assertEquals('Test Room', $data['name']);
    }

    public function testCreateRoomValidationError(): void
    {
        $client = static::createClient();
        
        $roomData = [
            'name' => '', // Invalid: empty name
            'isActive' => true
        ];

        $validationException = new ValidationException('Validation failed', ['name' => ['Room name cannot be empty']]);
        
        $this->mockRoomService
            ->expects($this->once())
            ->method('create')
            ->willThrowException($validationException);

        static::getContainer()->set(RoomService::class, $this->mockRoomService);

        $client->request('POST', '/api/v1/rooms', [], [], 
            ['CONTENT_TYPE' => 'application/json'],
            json_encode($roomData)
        );

        $this->assertResponseStatusCodeSame(400);
        
        $response = $client->getResponse();
        $data = json_decode($response->getContent(), true);
        
        $this->assertArrayHasKey('error', $data);
        $this->assertEquals('Validation failed', $data['error']);
        $this->assertArrayHasKey('details', $data);
    }

    public function testUpdateRoomSuccess(): void
    {
        $client = static::createClient();
        
        $roomData = [
            'name' => 'Updated Room',
            'description' => 'Updated Description',
            'isActive' => false
        ];

        $this->mockRoomService
            ->expects($this->once())
            ->method('update')
            ->with(1, $this->isInstanceOf(Request::class))
            ->willReturn('{"id": 1, "name": "Updated Room", "isActive": false}');

        static::getContainer()->set(RoomService::class, $this->mockRoomService);

        $client->request('PUT', '/api/v1/rooms/1', [], [], 
            ['CONTENT_TYPE' => 'application/json'],
            json_encode($roomData)
        );

        $this->assertResponseStatusCodeSame(200);
        
        $response = $client->getResponse();
        $data = json_decode($response->getContent(), true);
        
        $this->assertEquals(1, $data['id']);
        $this->assertEquals('Updated Room', $data['name']);
    }

    public function testUpdateRoomNotFound(): void
    {
        $client = static::createClient();
        
        $roomData = ['name' => 'Updated Room'];

        $this->mockRoomService
            ->expects($this->once())
            ->method('update')
            ->willThrowException(new RoomNotFoundException(999));

        static::getContainer()->set(RoomService::class, $this->mockRoomService);

        $client->request('PUT', '/api/v1/rooms/999', [], [], 
            ['CONTENT_TYPE' => 'application/json'],
            json_encode($roomData)
        );

        $this->assertResponseStatusCodeSame(404);
        
        $response = $client->getResponse();
        $data = json_decode($response->getContent(), true);
        
        $this->assertArrayHasKey('error', $data);
        $this->assertStringContainsString('Room with ID 999 was not found', $data['error']);
    }

    public function testDeleteRoomSuccess(): void
    {
        $client = static::createClient();
        
        $room = new Room();
        $room->setName('Test Room');
        
        // Use reflection to set the ID since it's normally set by Doctrine
        $reflection = new \ReflectionClass($room);
        $idProperty = $reflection->getProperty('id');
        $idProperty->setAccessible(true);
        $idProperty->setValue($room, 1);

        $this->mockRoomService
            ->expects($this->once())
            ->method('delete')
            ->with(1)
            ->willReturn($room);

        static::getContainer()->set(RoomService::class, $this->mockRoomService);

        $client->request('DELETE', '/api/v1/rooms/1');

        $this->assertResponseStatusCodeSame(200);
        
        $response = $client->getResponse();
        $data = json_decode($response->getContent(), true);
        
        $this->assertArrayHasKey('message', $data);
        $this->assertEquals('Room deleted successfully', $data['message']);
        $this->assertArrayHasKey('deleted_room', $data);
        $this->assertEquals(1, $data['deleted_room']['id']);
        $this->assertEquals('Test Room', $data['deleted_room']['name']);
    }

    public function testDeleteRoomNotFound(): void
    {
        $client = static::createClient();

        $this->mockRoomService
            ->expects($this->once())
            ->method('delete')
            ->willThrowException(new RoomNotFoundException(999));

        static::getContainer()->set(RoomService::class, $this->mockRoomService);

        $client->request('DELETE', '/api/v1/rooms/999');

        $this->assertResponseStatusCodeSame(404);
        
        $response = $client->getResponse();
        $data = json_decode($response->getContent(), true);
        
        $this->assertArrayHasKey('error', $data);
    }

    public function testDeleteRoomWithReservations(): void
    {
        $client = static::createClient();

        $conflictException = new ReservationConflictException(
            1,
            'Cannot delete room with active reservations',
            'delete_conflict',
            null,
            2
        );

        $this->mockRoomService
            ->expects($this->once())
            ->method('delete')
            ->willThrowException($conflictException);

        static::getContainer()->set(RoomService::class, $this->mockRoomService);

        $client->request('DELETE', '/api/v1/rooms/1');

        $this->assertResponseStatusCodeSame(409);
        
        $response = $client->getResponse();
        $data = json_decode($response->getContent(), true);
        
        $this->assertArrayHasKey('error', $data);
        $this->assertArrayHasKey('room_id', $data);
        $this->assertArrayHasKey('conflict_type', $data);
        $this->assertArrayHasKey('reservations_count', $data);
    }
}
