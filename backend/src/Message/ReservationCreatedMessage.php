<?php

namespace App\Message;

/**
 * Message sent to RabbitMQ when a new reservation is created
 */
class ReservationCreatedMessage
{
    private int $reservationId;
    private string $roomName;
    private string $reservedBy;
    private ?string $reservedByEmail;
    private \DateTimeInterface $startDateTime;
    private \DateTimeInterface $endDateTime;
    private \DateTimeInterface $createdAt;

    public function __construct(
        int $reservationId,
        string $roomName,
        string $reservedBy,
        ?string $reservedByEmail,
        \DateTimeInterface $startDateTime,
        \DateTimeInterface $endDateTime,
        \DateTimeInterface $createdAt
    ) {
        $this->reservationId = $reservationId;
        $this->roomName = $roomName;
        $this->reservedBy = $reservedBy;
        $this->reservedByEmail = $reservedByEmail;
        $this->startDateTime = $startDateTime;
        $this->endDateTime = $endDateTime;
        $this->createdAt = $createdAt;
    }

    public function getReservationId(): int
    {
        return $this->reservationId;
    }

    public function getRoomName(): string
    {
        return $this->roomName;
    }

    public function getReservedBy(): string
    {
        return $this->reservedBy;
    }

    public function getReservedByEmail(): ?string
    {
        return $this->reservedByEmail;
    }

    public function getStartDateTime(): \DateTimeInterface
    {
        return $this->startDateTime;
    }

    public function getEndDateTime(): \DateTimeInterface
    {
        return $this->endDateTime;
    }

    public function getCreatedAt(): \DateTimeInterface
    {
        return $this->createdAt;
    }
}
