"use client";

import { Reservation } from "@/types/interferance";
import { Button } from "@/components/ui";
import { XMarkIcon, TrashIcon } from "@heroicons/react/24/outline";
import axios from "axios";
import { useState } from "react";
import { parseLocalDateTime, formatDateTime } from "@/utils/dateUtils";

interface ReservationDetailsModalProps {
  isOpen: boolean;
  onClose: () => void;
  reservation: Reservation | null;
  onReservationDeleted: () => void;
}

export default function ReservationDetailsModal({ 
  isOpen, 
  onClose, 
  reservation,
  onReservationDeleted 
}: ReservationDetailsModalProps) {
  const [isDeleting, setIsDeleting] = useState(false);
  const [deleteError, setDeleteError] = useState<string | null>(null);

  const handleDelete = async () => {
    if (!reservation) return;
    
    if (!confirm(`Czy na pewno chcesz usunąć rezerwację ${reservation.reservedBy}?`)) {
      return;
    }

    setIsDeleting(true);
    setDeleteError(null);

    try {
      await axios.delete(`http://localhost:8000/api/v1/reservations/${reservation.id}`);
      onReservationDeleted();
      onClose();
    } catch (error) {
      let errorMessage = 'Błąd podczas usuwania rezerwacji';
      
      if (axios.isAxiosError(error)) {
        if (error.response?.status === 404) {
          errorMessage = 'Rezerwacja nie została znaleziona';
        } else if (error.response?.data?.message) {
          errorMessage = error.response.data.message;
        }
      }
      
      setDeleteError(errorMessage);
    } finally {
      setIsDeleting(false);
    }
  };

  const calculateDuration = (start: string, end: string) => {
    const startDate = parseLocalDateTime(start);
    const endDate = parseLocalDateTime(end);
    const durationMs = endDate.getTime() - startDate.getTime();
    const hours = Math.floor(durationMs / (1000 * 60 * 60));
    const minutes = Math.floor((durationMs % (1000 * 60 * 60)) / (1000 * 60));
    
    if (hours > 0 && minutes > 0) {
      return `${hours}h ${minutes}min`;
    } else if (hours > 0) {
      return `${hours}h`;
    } else {
      return `${minutes}min`;
    }
  };

  if (!isOpen || !reservation) return null;

  return (
    <div className="fixed inset-0 bg-black bg-opacity-50 flex items-center justify-center p-4 z-50">
      <div className="bg-white rounded-lg shadow-xl max-w-md w-full">
        <div className="flex items-center justify-between p-6 border-b border-gray-200">
          <h2 className="text-xl font-semibold text-gray-900">Szczegóły rezerwacji</h2>
          <button
            onClick={onClose}
            className="p-2 hover:bg-gray-100 rounded-md"
          >
            <XMarkIcon className="h-5 w-5" />
          </button>
        </div>

        <div className="p-6 space-y-4">
          {deleteError && (
            <div className="bg-red-50 border border-red-200 text-red-700 px-4 py-3 rounded">
              {deleteError}
            </div>
          )}

          {/* Room */}
          <div>
            <label className="block text-sm font-medium text-gray-700 mb-1">
              Sala
            </label>
            <p className="text-sm text-gray-900">{reservation.room.name}</p>
            {reservation.room.description && (
              <p className="text-xs text-gray-500">{reservation.room.description}</p>
            )}
          </div>

          {/* Reserved By */}
          <div>
            <label className="block text-sm font-medium text-gray-700 mb-1">
              Zarezerwowane przez
            </label>
            <p className="text-sm text-gray-900">{reservation.reservedBy}</p>
            {reservation.reservedByEmail && (
              <p className="text-xs text-gray-500">{reservation.reservedByEmail}</p>
            )}
          </div>

          {/* Start DateTime */}
          <div>
            <label className="block text-sm font-medium text-gray-700 mb-1">
              Rozpoczęcie
            </label>
            <p className="text-sm text-gray-900">{formatDateTime(reservation.startDateTime)}</p>
          </div>

          {/* End DateTime */}
          <div>
            <label className="block text-sm font-medium text-gray-700 mb-1">
              Zakończenie
            </label>
            <p className="text-sm text-gray-900">{formatDateTime(reservation.endDateTime)}</p>
          </div>

          {/* Duration */}
          <div>
            <label className="block text-sm font-medium text-gray-700 mb-1">
              Czas trwania
            </label>
            <p className="text-sm text-gray-900">
              {calculateDuration(reservation.startDateTime, reservation.endDateTime)}
            </p>
          </div>

          {/* Created At */}
          <div>
            <label className="block text-sm font-medium text-gray-700 mb-1">
              Data utworzenia
            </label>
            <p className="text-xs text-gray-500">{formatDateTime(reservation.createdAt)}</p>
          </div>
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
            onClick={handleDelete}
            disabled={isDeleting}
            className="flex-1 bg-red-700 hover:bg-red-800 text-white px-4 py-2 rounded-md disabled:opacity-50 flex items-center justify-center space-x-2"
          >
            <TrashIcon className="h-4 w-4" />
            <span>{isDeleting ? 'Usuwanie...' : 'Usuń'}</span>
          </Button>
        </div>
      </div>
    </div>
  );
}
