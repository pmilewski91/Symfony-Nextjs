<?php

namespace App\Exception;

class RoomNotFoundException extends \Exception
{
    private int $roomId;

    public function __construct(int $roomId, \Throwable $previous = null)
    {
        $this->roomId = $roomId;
        parent::__construct("Room with ID {$roomId} was not found", 404, $previous);
    }

    public function getRoomId(): int
    {
        return $this->roomId;
    }
}
