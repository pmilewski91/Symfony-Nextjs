"use client";

import { Reservation, Room } from "@/types/interferance";
import { Button } from "@/components/ui";
import { XMarkIcon, PlusIcon, ClockIcon, UserIcon } from "@heroicons/react/24/outline";
import { parseLocalDateTime } from "@/utils/dateUtils";

interface DayReservationsModalProps {
  isOpen: boolean;
  onClose: () => void;
  selectedDate: Date;
  reservations: Reservation[];
  rooms: Room[];
  onAddReservation: () => void;
  onReservationClick: (reservation: Reservation) => void;
}

export default function DayReservationsModal({
  isOpen,
  onClose,
  selectedDate,
  reservations,
  rooms,
  onAddReservation,
  onReservationClick
}: DayReservationsModalProps) {
  const formatDateOnly = (date: Date) => {
    return date.toLocaleDateString('pl-PL', {
      weekday: 'long',
      year: 'numeric',
      month: 'long',
      day: 'numeric'
    });
  };

  const formatTimeRange = (startDateTime: string, endDateTime: string) => {
    const start = parseLocalDateTime(startDateTime);
    const end = parseLocalDateTime(endDateTime);
    
    return `${start.toLocaleTimeString('pl-PL', { hour: '2-digit', minute: '2-digit' })} - ${end.toLocaleTimeString('pl-PL', { hour: '2-digit', minute: '2-digit' })}`;
  };

  const sortedReservations = [...reservations].sort((a, b) => {
    const timeA = parseLocalDateTime(a.startDateTime).getTime();
    const timeB = parseLocalDateTime(b.startDateTime).getTime();
    return timeA - timeB;
  });

  if (!isOpen) return null;

  return (
    <div className="fixed inset-0 bg-black bg-opacity-50 flex items-center justify-center p-4 z-50">
      <div className="bg-white rounded-lg shadow-xl max-w-md w-full max-h-[80vh] overflow-hidden">
        <div className="flex items-center justify-between p-6 border-b border-gray-200">
          <div>
            <h2 className="text-xl font-semibold text-gray-900">Rezerwacje</h2>
            <p className="text-sm text-gray-600 capitalize">{formatDateOnly(selectedDate)}</p>
          </div>
          <button
            onClick={onClose}
            className="p-2 hover:bg-gray-100 rounded-md"
          >
            <XMarkIcon className="h-5 w-5" />
          </button>
        </div>

        <div className="overflow-y-auto max-h-96">
          {sortedReservations.length > 0 ? (
            <div className="p-4 space-y-3">
              {sortedReservations.map((reservation) => (
                <div
                  key={reservation.id}
                  className="border border-gray-200 rounded-lg p-4 hover:bg-gray-50 cursor-pointer transition-colors"
                  onClick={() => onReservationClick(reservation)}
                >
                  <div className="flex items-start justify-between">
                    <div className="flex-1">
                      <div className="flex items-center space-x-2 mb-2">
                        <UserIcon className="h-4 w-4 text-gray-500" />
                        <span className="font-medium text-gray-900">{reservation.reservedBy}</span>
                      </div>
                      
                      <div className="flex items-center space-x-2 mb-2">
                        <ClockIcon className="h-4 w-4 text-gray-500" />
                        <span className="text-sm text-gray-600">
                          {formatTimeRange(reservation.startDateTime, reservation.endDateTime)}
                        </span>
                      </div>
                      
                      <div className="text-sm text-gray-600">
                        <span className="font-medium">{reservation.room.name}</span>
                        {reservation.room.description && (
                          <span className="block text-xs text-gray-500 mt-1">
                            {reservation.room.description}
                          </span>
                        )}
                      </div>
                      
                      {reservation.reservedByEmail && (
                        <div className="text-xs text-gray-500 mt-2">
                          {reservation.reservedByEmail}
                        </div>
                      )}
                    </div>
                  </div>
                </div>
              ))}
            </div>
          ) : (
            <div className="p-8 text-center text-gray-500">
              <ClockIcon className="h-12 w-12 mx-auto mb-4 text-gray-300" />
              <p className="text-lg font-medium mb-2">Brak rezerwacji</p>
              <p className="text-sm">Nie ma żadnych rezerwacji na ten dzień</p>
            </div>
          )}
        </div>

        {/* Action Buttons */}
        <div className="flex space-x-3 p-6 border-t border-gray-200">
          <Button
            onClick={onClose}
            className="flex-1 bg-gray-300 hover:bg-gray-400 text-gray-800 px-4 py-2 rounded-md"
          >
            Zamknij
          </Button>
          <Button
            onClick={() => {
              onClose();
              onAddReservation();
            }}
            className="flex-1 bg-blue-700 hover:bg-blue-800 text-white px-4 py-2 rounded-md flex items-center justify-center space-x-2"
          >
            <PlusIcon className="h-4 w-4" />
            <span>Dodaj rezerwację</span>
          </Button>
        </div>
      </div>
    </div>
  );
}
