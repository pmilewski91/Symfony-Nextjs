<?php

namespace App\Exception;

class ReservationConflictException extends \Exception
{
    private int $roomId;
    private ?int $reservationsCount = null;
    private ?array $conflictingReservations = null;
    private string $conflictType;

    public function __construct(int $roomId, string $message = null, string $conflictType = 'time_conflict', array $conflictingReservations = null, int $reservationsCount = null, \Throwable $previous = null)
    {
        $this->roomId = $roomId;
        $this->conflictType = $conflictType;
        $this->conflictingReservations = $conflictingReservations;
        $this->reservationsCount = $reservationsCount;
        
        if ($message === null) {
            if ($conflictType === 'delete_conflict') {
                $message = "Cannot delete room with ID {$roomId}. It has {$reservationsCount} existing reservation(s)";
            } else {
                $message = "Time slot conflicts with existing reservation for room {$roomId}";
            }
        }
        
        parent::__construct($message, 409, $previous);
    }

    public function getRoomId(): int
    {
        return $this->roomId;
    }

    public function getReservationsCount(): ?int
    {
        return $this->reservationsCount;
    }

    public function getConflictingReservations(): ?array
    {
        return $this->conflictingReservations;
    }

    public function getConflictType(): string
    {
        return $this->conflictType;
    }

    public function toArray(): array
    {
        $result = [
            'error' => $this->getMessage(),
            'room_id' => $this->roomId,
            'conflict_type' => $this->conflictType
        ];

        if ($this->conflictType === 'delete_conflict' && $this->reservationsCount !== null) {
            $result['reservations_count'] = $this->reservationsCount;
        }

        if ($this->conflictType === 'time_conflict' && $this->conflictingReservations !== null) {
            $result['conflicting_reservations'] = $this->conflictingReservations;
        }

        return $result;
    }
}
