<?php

namespace App\Tests\Controller;

use App\Exception\ReservationConflictException;
use App\Exception\RoomNotFoundException;
use App\Exception\ValidationException;
use App\Service\ReservationService;
use Symfony\Bundle\FrameworkBundle\Test\WebTestCase;
use Symfony\Component\HttpFoundation\Request;

class ReservationControllerTest extends WebTestCase
{
    private $mockReservationService;

    protected function setUp(): void
    {
        parent::setUp();
        $this->mockReservationService = $this->createMock(ReservationService::class);
    }

    public function testListReservationsSuccess(): void
    {
        $client = static::createClient();
        
        $reservationData = [
            'data' => [
                [
                    'id' => 1,
                    'roomId' => 1,
                    'reservedBy' => 'John Doe',
                    'startDateTime' => '2025-09-05T10:00:00+00:00',
                    'endDateTime' => '2025-09-05T12:00:00+00:00'
                ]
            ],
            'total' => 1
        ];

        $this->mockReservationService
            ->expects($this->once())
            ->method('list')
            ->willReturn($reservationData);

        static::getContainer()->set(ReservationService::class, $this->mockReservationService);

        $client->request('GET', '/api/v1/reservations');

        $this->assertResponseIsSuccessful();
        $this->assertResponseStatusCodeSame(200);
        
        $response = $client->getResponse();
        $data = json_decode($response->getContent(), true);
        
        $this->assertArrayHasKey('data', $data);
        $this->assertIsArray($data['data']);
        $this->assertEquals(1, $data['total']);
    }

    public function testListReservationsValidationError(): void
    {
        $client = static::createClient();
        
        $validationException = new ValidationException('Invalid date format', ['date' => ['Invalid date format']]);
        
        $this->mockReservationService
            ->expects($this->once())
            ->method('list')
            ->willThrowException($validationException);

        static::getContainer()->set(ReservationService::class, $this->mockReservationService);

        $client->request('GET', '/api/v1/reservations?date=invalid');

        $this->assertResponseStatusCodeSame(400);
        
        $response = $client->getResponse();
        $data = json_decode($response->getContent(), true);
        
        $this->assertArrayHasKey('error', $data);
        $this->assertEquals('Validation failed', $data['error']);
    }

    public function testCreateReservationSuccess(): void
    {
        $client = static::createClient();
        
        $reservationData = [
            'roomId' => 1,
            'reservedBy' => 'John Doe',
            'reservedByEmail' => 'john@example.com',
            'startDateTime' => '2025-09-05T10:00:00+00:00',
            'endDateTime' => '2025-09-05T12:00:00+00:00'
        ];

        $responseData = [
            'id' => 1,
            'roomId' => 1,
            'reservedBy' => 'John Doe',
            'reservedByEmail' => 'john@example.com',
            'startDateTime' => '2025-09-05T10:00:00+00:00',
            'endDateTime' => '2025-09-05T12:00:00+00:00'
        ];

        $this->mockReservationService
            ->expects($this->once())
            ->method('create')
            ->willReturn($responseData);

        static::getContainer()->set(ReservationService::class, $this->mockReservationService);

        $client->request('POST', '/api/v1/reservations', [], [], 
            ['CONTENT_TYPE' => 'application/json'],
            json_encode($reservationData)
        );

        $this->assertResponseStatusCodeSame(201);
        
        $response = $client->getResponse();
        $data = json_decode($response->getContent(), true);
        
        $this->assertEquals(1, $data['id']);
        $this->assertEquals('John Doe', $data['reservedBy']);
        $this->assertEquals('john@example.com', $data['reservedByEmail']);
    }

    public function testCreateReservationValidationError(): void
    {
        $client = static::createClient();
        
        $reservationData = [
            'roomId' => 1,
            'reservedBy' => '', // Invalid: empty name
            'startDateTime' => '2025-09-05T10:00:00+00:00',
            'endDateTime' => '2025-09-05T12:00:00+00:00'
        ];

        $validationException = new ValidationException('Validation failed', [
            'reservedBy' => ['Reserved by cannot be empty']
        ]);
        
        $this->mockReservationService
            ->expects($this->once())
            ->method('create')
            ->willThrowException($validationException);

        static::getContainer()->set(ReservationService::class, $this->mockReservationService);

        $client->request('POST', '/api/v1/reservations', [], [], 
            ['CONTENT_TYPE' => 'application/json'],
            json_encode($reservationData)
        );

        $this->assertResponseStatusCodeSame(400);
        
        $response = $client->getResponse();
        $data = json_decode($response->getContent(), true);
        
        $this->assertArrayHasKey('error', $data);
        $this->assertEquals('Validation failed', $data['error']);
        $this->assertArrayHasKey('details', $data);
    }

    public function testCreateReservationRoomNotFound(): void
    {
        $client = static::createClient();
        
        $reservationData = [
            'roomId' => 999, // Non-existent room
            'reservedBy' => 'John Doe',
            'startDateTime' => '2025-09-05T10:00:00+00:00',
            'endDateTime' => '2025-09-05T12:00:00+00:00'
        ];

        $this->mockReservationService
            ->expects($this->once())
            ->method('create')
            ->willThrowException(new RoomNotFoundException(999));

        static::getContainer()->set(ReservationService::class, $this->mockReservationService);

        $client->request('POST', '/api/v1/reservations', [], [], 
            ['CONTENT_TYPE' => 'application/json'],
            json_encode($reservationData)
        );

        $this->assertResponseStatusCodeSame(404);
        
        $response = $client->getResponse();
        $data = json_decode($response->getContent(), true);
        
        $this->assertArrayHasKey('error', $data);
        $this->assertStringContainsString('Room with ID 999 was not found', $data['error']);
    }

    public function testCreateReservationTimeConflict(): void
    {
        $client = static::createClient();
        
        $reservationData = [
            'roomId' => 1,
            'reservedBy' => 'John Doe',
            'startDateTime' => '2025-09-05T10:00:00+00:00',
            'endDateTime' => '2025-09-05T12:00:00+00:00'
        ];

        $conflictException = new ReservationConflictException(
            1,
            'Time slot conflicts with existing reservation',
            'time_conflict',
            [
                [
                    'id' => 2,
                    'startDateTime' => '2025-09-05T09:00:00+00:00',
                    'endDateTime' => '2025-09-05T11:00:00+00:00'
                ]
            ]
        );

        $this->mockReservationService
            ->expects($this->once())
            ->method('create')
            ->willThrowException($conflictException);

        static::getContainer()->set(ReservationService::class, $this->mockReservationService);

        $client->request('POST', '/api/v1/reservations', [], [], 
            ['CONTENT_TYPE' => 'application/json'],
            json_encode($reservationData)
        );

        $this->assertResponseStatusCodeSame(409);
        
        $response = $client->getResponse();
        $data = json_decode($response->getContent(), true);
        
        $this->assertArrayHasKey('error', $data);
        $this->assertArrayHasKey('room_id', $data);
        $this->assertArrayHasKey('conflict_type', $data);
        $this->assertEquals(1, $data['room_id']);
        $this->assertEquals('time_conflict', $data['conflict_type']);
    }

    public function testListByRoomSuccess(): void
    {
        $client = static::createClient();
        
        $roomId = 1;
        $responseData = [
            'data' => [
                [
                    'id' => 1,
                    'roomId' => 1,
                    'reservedBy' => 'John Doe',
                    'startDateTime' => '2025-09-05T10:00:00+00:00',
                    'endDateTime' => '2025-09-05T12:00:00+00:00'
                ]
            ],
            'room' => [
                'id' => 1,
                'name' => 'Conference Room A'
            ],
            'total' => 1
        ];

        $this->mockReservationService
            ->expects($this->once())
            ->method('listByRoom')
            ->with($roomId, $this->isInstanceOf(Request::class))
            ->willReturn($responseData);

        static::getContainer()->set(ReservationService::class, $this->mockReservationService);

        $client->request('GET', "/api/v1/reservations/room/{$roomId}");

        $this->assertResponseStatusCodeSame(200);
        
        $response = $client->getResponse();
        $data = json_decode($response->getContent(), true);
        
        $this->assertArrayHasKey('data', $data);
        $this->assertArrayHasKey('room', $data);
        $this->assertArrayHasKey('total', $data);
        $this->assertEquals(1, $data['room']['id']);
        $this->assertEquals('Conference Room A', $data['room']['name']);
    }

    public function testListByRoomNotFound(): void
    {
        $client = static::createClient();
        
        $roomId = 999;

        $this->mockReservationService
            ->expects($this->once())
            ->method('listByRoom')
            ->willThrowException(new RoomNotFoundException(999));

        static::getContainer()->set(ReservationService::class, $this->mockReservationService);

        $client->request('GET', "/api/v1/reservations/room/{$roomId}");

        $this->assertResponseStatusCodeSame(404);
        
        $response = $client->getResponse();
        $data = json_decode($response->getContent(), true);
        
        $this->assertArrayHasKey('error', $data);
        $this->assertStringContainsString('Room with ID 999 was not found', $data['error']);
    }

    public function testListByRoomValidationError(): void
    {
        $client = static::createClient();
        
        $roomId = 1;

        $validationException = new ValidationException('Invalid date range', [
            'dateRange' => ['Start date must be before end date']
        ]);

        $this->mockReservationService
            ->expects($this->once())
            ->method('listByRoom')
            ->willThrowException($validationException);

        static::getContainer()->set(ReservationService::class, $this->mockReservationService);

        $client->request('GET', "/api/v1/reservations/room/{$roomId}?startDate=2025-09-10&endDate=2025-09-05");

        $this->assertResponseStatusCodeSame(400);
        
        $response = $client->getResponse();
        $data = json_decode($response->getContent(), true);
        
        $this->assertArrayHasKey('error', $data);
        $this->assertEquals('Validation failed', $data['error']);
        $this->assertArrayHasKey('details', $data);
    }

    public function testListReservationsServerError(): void
    {
        $client = static::createClient();
        
        $this->mockReservationService
            ->expects($this->once())
            ->method('list')
            ->willThrowException(new \Exception('Database connection failed'));

        static::getContainer()->set(ReservationService::class, $this->mockReservationService);

        $client->request('GET', '/api/v1/reservations');

        $this->assertResponseStatusCodeSame(500);
        
        $response = $client->getResponse();
        $data = json_decode($response->getContent(), true);
        
        $this->assertArrayHasKey('error', $data);
        $this->assertEquals('Failed to fetch reservations', $data['error']);
    }
}
