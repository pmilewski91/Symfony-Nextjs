"use client";

import { useState, useEffect, useCallback } from "react";
import { useSearchParams } from "next/navigation";
import { Button } from "@/components/ui";
import CalendarView from "@/components/CalendarView";
import ReservationModal from "@/components/ReservationModal";
import ReservationDetailsModal from "@/components/ReservationDetailsModal";
import DayReservationsModal from "@/components/DayReservationsModal";
import axios from "axios";
import { Room, Reservation } from "@/types/interferance";

export default function CalendarPage() {
  const searchParams = useSearchParams();
  const roomParam = searchParams.get('room');
  
  const [rooms, setRooms] = useState<Room[]>([]);
  const [reservations, setReservations] = useState<Reservation[]>([]);
  const [selectedRoom, setSelectedRoom] = useState<number | null>(
    roomParam ? Number(roomParam) : null
  );
  const [isLoading, setIsLoading] = useState(true);
  const [isError, setIsError] = useState<string | null>(null);
  const [isModalOpen, setIsModalOpen] = useState(false);
  const [selectedDate, setSelectedDate] = useState<Date | null>(null);
  const [isDetailsModalOpen, setIsDetailsModalOpen] = useState(false);
  const [selectedReservation, setSelectedReservation] = useState<Reservation | null>(null);
  const [isDayModalOpen, setIsDayModalOpen] = useState(false);

  const fetchRooms = async () => {
    try {
      const response = await axios.get('http://localhost:8000/api/v1/rooms');
      setRooms(response.data.filter((room: Room) => room.isActive));
    } catch {
      setIsError('Błąd pobierania sal');
    }
  };

  const fetchReservations = useCallback(async () => {
    try {
      let url = 'http://localhost:8000/api/v1/reservations';
      if (selectedRoom) {
        url = `http://localhost:8000/api/v1/reservations/room/${selectedRoom}`;
      }
      const response = await axios.get(url);
      
      // Handle different response structures
      if (selectedRoom && response.data.reservations) {
        // Room-specific endpoint returns { reservations: [...] }
        setReservations(response.data.reservations);
      } else {
        // General endpoint returns [...]
        setReservations(response.data);
      }
    } catch {
      setIsError('Błąd pobierania rezerwacji');
    }
  }, [selectedRoom]);

  useEffect(() => {
    const loadData = async () => {
      setIsLoading(true);
      await fetchRooms();
      await fetchReservations();
      setIsLoading(false);
    };
    loadData();
  }, [selectedRoom, fetchReservations]);

  const handleDateClick = (date: Date) => {
    setSelectedDate(date);
    setIsDayModalOpen(true);
  };

  const handleAddReservationFromDay = () => {
    setIsDayModalOpen(false);
    setIsModalOpen(true);
  };

  const handleReservationCreated = () => {
    fetchReservations();
    setIsModalOpen(false);
  };

  const handleReservationClick = (reservation: Reservation) => {
    setSelectedReservation(reservation);
    setIsDetailsModalOpen(true);
  };

  const handleReservationDeleted = () => {
    fetchReservations();
    setIsDetailsModalOpen(false);
    setSelectedReservation(null);
  };

  const getReservationsForDay = (date: Date) => {
    if (!Array.isArray(reservations)) return [];
    
    const dateStr = date.getFullYear() + '-' + 
      String(date.getMonth() + 1).padStart(2, '0') + '-' + 
      String(date.getDate()).padStart(2, '0');
    
    return reservations.filter(reservation => {
      const startDate = new Date(reservation.startDateTime.replace(/[+-]\d{2}:\d{2}$/, ''));
      const endDate = new Date(reservation.endDateTime.replace(/[+-]\d{2}:\d{2}$/, ''));
      
      const startDateStr = startDate.getFullYear() + '-' + 
        String(startDate.getMonth() + 1).padStart(2, '0') + '-' + 
        String(startDate.getDate()).padStart(2, '0');
      const endDateStr = endDate.getFullYear() + '-' + 
        String(endDate.getMonth() + 1).padStart(2, '0') + '-' + 
        String(endDate.getDate()).padStart(2, '0');
      
      return dateStr >= startDateStr && dateStr <= endDateStr;
    });
  };

  if (isLoading) {
    return (
      <div className="max-w-full mx-auto mt-4 sm:mt-10 p-2 sm:p-6">
        <div className="py-10 text-center text-blue-600">Ładowanie kalendarza...</div>
      </div>
    );
  }

  if (isError) {
    return (
      <div className="max-w-full mx-auto mt-4 sm:mt-10 p-2 sm:p-6">
        <div className="py-10 text-center">
          <div className="text-red-600 font-medium">Wystąpił błąd podczas ładowania kalendarza.</div>
          <div className="text-xs text-gray-400 mt-2">{isError}</div>
        </div>
      </div>
    );
  }

  return (
    <div className="max-w-full mx-auto mt-4 sm:mt-10 p-2 sm:p-6">
      <div className="bg-white shadow-md rounded-lg overflow-hidden">
        <div className="px-4 sm:px-6 py-5 border-b border-gray-100 flex items-center justify-between">
          <h1 className="text-xl sm:text-2xl font-semibold text-gray-800">Kalendarz rezerwacji</h1>
          <Button 
            className="bg-blue-700 hover:bg-blue-800 text-white px-3 sm:px-4 py-2 rounded-md text-sm sm:text-base"
            onClick={() => {
              setSelectedDate(null);
              setIsModalOpen(true);
            }}
          >
            <span className="hidden sm:inline">Nowa rezerwacja</span>
            <span className="sm:hidden">+ Nowa</span>
          </Button>
        </div>

        <div className="p-4 sm:p-6">
          {/* Filter by room */}
          <div className="mb-6">
            <label htmlFor="room-filter" className="block text-sm font-medium text-gray-700 mb-2">
              Filtruj po sali:
            </label>
            <select
              id="room-filter"
              value={selectedRoom || ''}
              onChange={(e) => setSelectedRoom(e.target.value ? Number(e.target.value) : null)}
              className="block w-full max-w-xs px-3 py-2 border border-gray-300 rounded-md shadow-sm focus:outline-none focus:ring-blue-500 focus:border-blue-500"
            >
              <option value="">Wszystkie sale</option>
              {rooms.map((room) => (
                <option key={room.id} value={room.id}>
                  {room.name}
                </option>
              ))}
            </select>
          </div>

          {/* Calendar */}
          <CalendarView 
            reservations={reservations}
            rooms={rooms}
            selectedRoom={selectedRoom}
            onDateClick={handleDateClick}
            onReservationClick={handleReservationClick}
          />
        </div>
      </div>

      {/* Day Reservations Modal */}
      {isDayModalOpen && selectedDate && (
        <DayReservationsModal
          isOpen={isDayModalOpen}
          onClose={() => setIsDayModalOpen(false)}
          selectedDate={selectedDate}
          reservations={getReservationsForDay(selectedDate)}
          rooms={rooms}
          onAddReservation={handleAddReservationFromDay}
          onReservationClick={handleReservationClick}
        />
      )}

      {/* Reservation Modal */}
      {isModalOpen && (
        <ReservationModal
          isOpen={isModalOpen}
          onClose={() => setIsModalOpen(false)}
          rooms={rooms}
          selectedDate={selectedDate}
          selectedRoomId={selectedRoom}
          onReservationCreated={handleReservationCreated}
        />
      )}

      {/* Reservation Details Modal */}
      {isDetailsModalOpen && (
        <ReservationDetailsModal
          isOpen={isDetailsModalOpen}
          onClose={() => setIsDetailsModalOpen(false)}
          reservation={selectedReservation}
          onReservationDeleted={handleReservationDeleted}
        />
      )}
    </div>
  );
}
