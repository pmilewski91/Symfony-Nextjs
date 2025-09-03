<?php

namespace App\MessageHandler;

use App\Message\ReservationCreatedMessage;
use Psr\Log\LoggerInterface;
use Symfony\Component\Messenger\Attribute\AsMessageHandler;

/**
 * Handler for processing new reservation messages from RabbitMQ
 * 
 * This handler is automatically called when a ReservationCreatedMessage
 * is received from the RabbitMQ queue
 */
#[AsMessageHandler]
class ReservationCreatedMessageHandler
{
    private LoggerInterface $logger;

    public function __construct(LoggerInterface $logger)
    {
        $this->logger = $logger;
    }

    /**
     * Main message processing method
     * 
     * Symfony automatically calls this method when it receives a message
     * from the RabbitMQ queue
     */
    public function __invoke(ReservationCreatedMessage $message): void
    {
        $this->logger->info('Processing reservation created notification', [
            'reservation_id' => $message->getReservationId(),
            'room_name' => $message->getRoomName(),
            'reserved_by' => $message->getReservedBy(),
            'start_time' => $message->getStartDateTime()->format('Y-m-d H:i:s'),
            'end_time' => $message->getEndDateTime()->format('Y-m-d H:i:s')
        ]);

        try {
            // 1. Send email notification (if email is provided)
            if ($message->getReservedByEmail()) {
                $this->sendEmailNotification($message);
            }

            // 2. Send in-app notification (e.g. to administrators)
            $this->sendInAppNotification($message);

            // 3. Update reservation statistics
            $this->updateReservationStats($message);

            $this->logger->info('Reservation notification processed successfully', [
                'reservation_id' => $message->getReservationId()
            ]);

        } catch (\Exception $e) {
            $this->logger->error('Failed to process reservation notification', [
                'reservation_id' => $message->getReservationId(),
                'error' => $e->getMessage(),
                'trace' => $e->getTraceAsString()
            ]);

            // Throw exception again, so Symfony Messenger
            // knows that processing failed
            // and can try again later
            throw $e;
        }
    }

    /**
     * Sends email notification to the person who made the reservation
     */
    private function sendEmailNotification(ReservationCreatedMessage $message): void
    {
        // For now just log - we'll implement real email later
        $this->logger->info('Sending email notification', [
            'to' => $message->getReservedByEmail(),
            'reservation_id' => $message->getReservationId(),
            'room_name' => $message->getRoomName()
        ]);
    }

    /**
     * Sends in-app notification (e.g. to admin panel)
     */
    private function sendInAppNotification(ReservationCreatedMessage $message): void
    {
        $this->logger->info('Sending in-app notification', [
            'type' => 'reservation_created',
            'reservation_id' => $message->getReservationId(),
            'room_name' => $message->getRoomName(),
            'reserved_by' => $message->getReservedBy()
        ]);
    }

    /**
     * Updates reservation statistics
     */
    private function updateReservationStats(ReservationCreatedMessage $message): void
    {
        $this->logger->info('Updating reservation statistics', [
            'reservation_id' => $message->getReservationId(),
            'room_name' => $message->getRoomName(),
            'created_at' => $message->getCreatedAt()->format('Y-m-d H:i:s')
        ]);

    }
}
