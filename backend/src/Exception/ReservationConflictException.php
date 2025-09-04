<?php

namespace App\Exception;

class ReservationConflictException extends \Exception
{
    private int $roomId;
    private int $reservationsCount;

    public function __construct(int $roomId, int $reservationsCount, \Throwable $previous = null)
    {
        $this->roomId = $roomId;
        $this->reservationsCount = $reservationsCount;
        
        $message = "Cannot delete room with ID {$roomId}. It has {$reservationsCount} existing reservation(s)";
        parent::__construct($message, 409, $previous);
    }

    public function getRoomId(): int
    {
        return $this->roomId;
    }

    public function getReservationsCount(): int
    {
        return $this->reservationsCount;
    }

    public function toArray(): array
    {
        return [
            'error' => $this->getMessage(),
            'room_id' => $this->roomId,
            'reservations_count' => $this->reservationsCount
        ];
    }
}
