<?php

namespace App\Entity;

use App\Repository\ReservationRepository;
use Doctrine\ORM\Mapping as ORM;
use Symfony\Component\Serializer\Annotation\Groups;
use Symfony\Component\Validator\Constraints as Assert;
use Symfony\Component\Validator\Context\ExecutionContextInterface;

#[ORM\Entity(repositoryClass: ReservationRepository::class)]
#[ORM\HasLifecycleCallbacks]
#[Assert\Callback('validate')]
class Reservation
{
    #[ORM\Id]
    #[ORM\GeneratedValue]
    #[ORM\Column]
    #[Groups(['reservation:read', 'reservation:write'])]
    private ?int $id = null;

    #[ORM\ManyToOne(inversedBy: 'reservations')]
    #[ORM\JoinColumn(nullable: false)]
    #[Groups(['reservation:read', 'reservation:write'])]
    private ?Room $room = null;

    #[ORM\Column(length: 255)]
    #[Assert\NotBlank(message: 'Reserved by field cannot be empty')]
    #[Assert\Length(min: 2, max: 255, minMessage: 'Name must be at least 2 characters', maxMessage: 'Name cannot exceed 255 characters')]
    #[Groups(['reservation:read', 'reservation:write'])]
    private ?string $reservedBy = null;

    #[ORM\Column(length: 255, nullable: true)]
    #[Assert\Email(message: 'Please provide a valid email address')]
    #[Groups(['reservation:read', 'reservation:write'])]
    private ?string $reservedByEmail = null;

    #[ORM\Column]
    #[Assert\NotNull(message: 'Start date and time must be specified')]
    #[Groups(['reservation:read', 'reservation:write'])]
    private ?\DateTime $startDateTime = null;

    #[ORM\Column]
    #[Assert\NotNull(message: 'End date and time must be specified')]
    #[Groups(['reservation:read', 'reservation:write'])]
    private ?\DateTime $endDateTime = null;

    #[ORM\Column]
    #[Groups(['reservation:read'])]
    private ?\DateTimeImmutable $createdAt = null;

    public function __construct()
    {
        $this->createdAt = new \DateTimeImmutable();
    }

    public function getId(): ?int
    {
        return $this->id;
    }

    public function getRoom(): ?Room
    {
        return $this->room;
    }

    public function setRoom(?Room $room): static
    {
        $this->room = $room;

        return $this;
    }

    public function getReservedBy(): ?string
    {
        return $this->reservedBy;
    }

    public function setReservedBy(string $reservedBy): static
    {
        $this->reservedBy = $reservedBy;

        return $this;
    }

    public function getReservedByEmail(): ?string
    {
        return $this->reservedByEmail;
    }

    public function setReservedByEmail(?string $reservedByEmail): static
    {
        $this->reservedByEmail = $reservedByEmail;

        return $this;
    }

    public function getStartDateTime(): ?\DateTime
    {
        return $this->startDateTime;
    }

    public function setStartDateTime(\DateTime $startDateTime): static
    {
        $this->startDateTime = $startDateTime;

        return $this;
    }

    public function getEndDateTime(): ?\DateTime
    {
        return $this->endDateTime;
    }

    public function setEndDateTime(\DateTime $endDateTime): static
    {
        $this->endDateTime = $endDateTime;

        return $this;
    }
    
    public function getCreatedAt(): ?\DateTimeImmutable
    {
        return $this->createdAt;
    }

    public function setCreatedAt(\DateTimeImmutable $createdAt): static
    {
        $this->createdAt = $createdAt;

        return $this;
    }

    public function validate(ExecutionContextInterface $context): void
    {
        if ($this->startDateTime && $this->endDateTime) {
            if ($this->startDateTime >= $this->endDateTime) {
                $context->buildViolation('End date and time must be after start date and time')
                    ->atPath('endDateTime')
                    ->addViolation();
            }

            // Check if reservation is not in the past
            $now = new \DateTime();
            if ($this->startDateTime < $now) {
                $context->buildViolation('Reservation cannot be made for past dates')
                    ->atPath('startDateTime')
                    ->addViolation();
            }
        }
    }
}
