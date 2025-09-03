<?php

namespace App\Service;

use App\Entity\Reservation;
use App\Message\ReservationCreatedMessage;
use Psr\Log\LoggerInterface;
use Symfony\Component\Messenger\MessageBusInterface;

/**
 * Service responsible for sending reservation notifications
 */
class NotificationService
{
    private MessageBusInterface $messageBus;
    private LoggerInterface $logger;

    public function __construct(
        MessageBusInterface $messageBus,
        LoggerInterface $logger
    ) {
        $this->messageBus = $messageBus;
        $this->logger = $logger;
    }

    /**
     * Sends notification about creating a new reservation
     *
     * @param Reservation $reservation Newly created reservation
     */
    public function sendReservationCreatedNotification(Reservation $reservation): void
    {
        try {
            // Create message with reservation data
            $message = new ReservationCreatedMessage(
                $reservation->getId(),
                $reservation->getRoom()->getName(),
                $reservation->getReservedBy(),
                $reservation->getReservedByEmail(),
                $reservation->getStartDateTime(),
                $reservation->getEndDateTime(),
                $reservation->getCreatedAt()
            );

            // Send message to RabbitMQ
            $this->messageBus->dispatch($message);

            $this->logger->info('Reservation notification sent successfully', [
                'reservation_id' => $reservation->getId(),
                'room_name' => $reservation->getRoom()->getName(),
                'reserved_by' => $reservation->getReservedBy()
            ]);

        } catch (\Exception $e) {
            // Log error, but don't interrupt the reservation creation process
            $this->logger->error('Failed to send reservation notification', [
                'reservation_id' => $reservation->getId(),
                'error' => $e->getMessage(),
                'trace' => $e->getTraceAsString()
            ]);
        }
    }

    /**
     * Sends notification about cancelling a reservation
     * 
     * @param Reservation $reservation Cancelled reservation
     */
    public function sendReservationCancelledNotification(Reservation $reservation): void
    {
        
        $this->logger->info('Reservation cancelled notification would be sent', [
            'reservation_id' => $reservation->getId(),
            'room_name' => $reservation->getRoom()->getName()
        ]);
    }
}
