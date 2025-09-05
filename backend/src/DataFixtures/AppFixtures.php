<?php

namespace App\DataFixtures;

use App\Entity\Room;
use App\Entity\Reservation;
use Doctrine\Bundle\FixturesBundle\Fixture;
use Doctrine\Persistence\ObjectManager;
use DateTime;

class AppFixtures extends Fixture
{
    public function load(ObjectManager $manager): void
    {
        // Tworzenie przykładowych sal konferencyjnych
        $roomA = new Room();
        $roomA->setName('Sala A');
        $roomA->setDescription('Duża sala konferencyjna z projektorem i systemem nagłaśniającym');
        $roomA->setIsActive(true);
        $manager->persist($roomA);

        $roomB = new Room();
        $roomB->setName('Sala B');
        $roomB->setDescription('Mała sala spotkań dla 6-8 osób');
        $roomB->setIsActive(true);
        $manager->persist($roomB);

        $roomC = new Room();
        $roomC->setName('Sala C');
        $roomC->setDescription('Sala z wideokonferencją i tablicą interaktywną');
        $roomC->setIsActive(true);
        $manager->persist($roomC);

        // Zapisujemy sale przed utworzeniem rezerwacji
        $manager->flush();

        // Przykładowe rezerwacje
        $today = new DateTime();
        $tomorrow = new DateTime('+1 day');
        $dayAfterTomorrow = new DateTime('+2 days');

        // Rezerwacja na dzisiaj w Sali A
        $reservation1 = new Reservation();
        $reservation1->setRoom($roomA);
        $reservation1->setReservedBy('Jan Kowalski');
        $reservation1->setReservedByEmail('jan.kowalski@example.com');
        $reservation1->setStartDateTime(new DateTime($today->format('Y-m-d') . ' 09:00:00'));
        $reservation1->setEndDateTime(new DateTime($today->format('Y-m-d') . ' 11:00:00'));
        $manager->persist($reservation1);

        // Rezerwacja na dzisiaj w Sali B
        $reservation2 = new Reservation();
        $reservation2->setRoom($roomB);
        $reservation2->setReservedBy('Anna Nowak');
        $reservation2->setReservedByEmail('anna.nowak@example.com');
        $reservation2->setStartDateTime(new DateTime($today->format('Y-m-d') . ' 14:00:00'));
        $reservation2->setEndDateTime(new DateTime($today->format('Y-m-d') . ' 16:00:00'));
        $manager->persist($reservation2);

        // Rezerwacja na jutro w Sali A
        $reservation3 = new Reservation();
        $reservation3->setRoom($roomA);
        $reservation3->setReservedBy('Piotr Wiśniewski');
        $reservation3->setReservedByEmail('piotr.wisniewski@example.com');
        $reservation3->setStartDateTime(new DateTime($tomorrow->format('Y-m-d') . ' 10:00:00'));
        $reservation3->setEndDateTime(new DateTime($tomorrow->format('Y-m-d') . ' 12:00:00'));
        $manager->persist($reservation3);

        // Rezerwacja na jutro w Sali C
        $reservation4 = new Reservation();
        $reservation4->setRoom($roomC);
        $reservation4->setReservedBy('Maria Dąbrowska');
        $reservation4->setReservedByEmail('maria.dabrowska@example.com');
        $reservation4->setStartDateTime(new DateTime($tomorrow->format('Y-m-d') . ' 13:00:00'));
        $reservation4->setEndDateTime(new DateTime($tomorrow->format('Y-m-d') . ' 15:00:00'));
        $manager->persist($reservation4);

        // Rezerwacja na pojutrze w Sali B
        $reservation5 = new Reservation();
        $reservation5->setRoom($roomB);
        $reservation5->setReservedBy('Tomasz Lewandowski');
        $reservation5->setReservedByEmail('tomasz.lewandowski@example.com');
        $reservation5->setStartDateTime(new DateTime($dayAfterTomorrow->format('Y-m-d') . ' 08:00:00'));
        $reservation5->setEndDateTime(new DateTime($dayAfterTomorrow->format('Y-m-d') . ' 10:00:00'));
        $manager->persist($reservation5);

        $manager->flush();
    }
}
