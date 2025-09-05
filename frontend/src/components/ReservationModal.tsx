"use client";

import { useState, useEffect } from "react";
import { Button } from "@/components/ui";
import { Room, ReservationFormData, ValidationErrors } from "@/types/interferance";
import axios from "axios";
import DatePicker from "react-datepicker";
import { XMarkIcon } from "@heroicons/react/24/outline";
import { toISOStringLocal } from "@/utils/dateUtils";

interface ReservationModalProps {
  isOpen: boolean;
  onClose: () => void;
  rooms: Room[];
  selectedDate?: Date | null;
  selectedRoomId?: number | null;
  onReservationCreated: () => void;
}

export default function ReservationModal({ 
  isOpen, 
  onClose, 
  rooms, 
  selectedDate,
  selectedRoomId,
  onReservationCreated 
}: ReservationModalProps) {
  const [formData, setFormData] = useState<ReservationFormData>({
    roomId: selectedRoomId || 0,
    reservedBy: '',
    reservedByEmail: '',
    startDateTime: '',
    endDateTime: ''
  });
  
  const [startDate, setStartDate] = useState<Date | null>(null);
  const [endDate, setEndDate] = useState<Date | null>(null);
  const [isSubmitting, setIsSubmitting] = useState(false);
  const [errors, setErrors] = useState<ValidationErrors>({});
  const [apiError, setApiError] = useState<string | null>(null);

  useEffect(() => {
    if (selectedDate) {
      const start = new Date(selectedDate);
      start.setHours(9, 0, 0, 0); // Default to 9:00 AM
      const end = new Date(selectedDate);
      end.setHours(10, 0, 0, 0); // Default to 10:00 AM
      
      setStartDate(start);
      setEndDate(end);
    }
  }, [selectedDate]);

  useEffect(() => {
    if (startDate) {
      setFormData(prev => ({
        ...prev,
        startDateTime: toISOStringLocal(startDate)
      }));
    }
  }, [startDate]);

  useEffect(() => {
    if (endDate) {
      setFormData(prev => ({
        ...prev,
        endDateTime: toISOStringLocal(endDate)
      }));
    }
  }, [endDate]);

  const handleInputChange = (e: React.ChangeEvent<HTMLInputElement | HTMLSelectElement>) => {
    const { name, value } = e.target;
    setFormData(prev => ({
      ...prev,
      [name]: name === 'roomId' ? Number(value) : value
    }));
    
    // Clear specific field error when user starts typing
    if (errors[name]) {
      setErrors(prev => ({
        ...prev,
        [name]: []
      }));
    }
  };

  const validateForm = (): boolean => {
    const newErrors: ValidationErrors = {};

    if (!formData.roomId) {
      newErrors.roomId = ['Proszę wybrać salę'];
    }

    if (!formData.reservedBy.trim()) {
      newErrors.reservedBy = ['Proszę podać imię i nazwisko'];
    }

    if (formData.reservedByEmail && !isValidEmail(formData.reservedByEmail)) {
      newErrors.reservedByEmail = ['Proszę podać prawidłowy adres email'];
    }

    if (!startDate) {
      newErrors.startDateTime = ['Proszę wybrać datę i godzinę rozpoczęcia'];
    }

    if (!endDate) {
      newErrors.endDateTime = ['Proszę wybrać datę i godzinę zakończenia'];
    }

    if (startDate && endDate && startDate >= endDate) {
      newErrors.endDateTime = ['Godzina zakończenia musi być późniejsza niż rozpoczęcia'];
    }

    if (startDate && startDate < new Date()) {
      newErrors.startDateTime = ['Nie można zarezerwować w przeszłości'];
    }

    setErrors(newErrors);
    return Object.keys(newErrors).length === 0;
  };

  const isValidEmail = (email: string): boolean => {
    const emailRegex = /^[^\s@]+@[^\s@]+\.[^\s@]+$/;
    return emailRegex.test(email);
  };

  const handleSubmit = async (e: React.FormEvent) => {
    e.preventDefault();
    
    if (!validateForm()) {
      return;
    }

    setIsSubmitting(true);
    setApiError(null);

    try {
      // Convert camelCase to snake_case for API
      const apiData = {
        room_id: formData.roomId,
        reserved_by: formData.reservedBy,
        reserved_by_email: formData.reservedByEmail || undefined,
        start_date_time: formData.startDateTime,
        end_date_time: formData.endDateTime
      };

      await axios.post('http://localhost:8000/api/v1/reservations', apiData);
      onReservationCreated();
      handleClose();
    } catch (error) {
      if (axios.isAxiosError(error)) {
        if (error.response?.status === 400 && error.response?.data?.details) {
          // Convert API errors from snake_case to camelCase
          const convertedErrors: ValidationErrors = {};
          Object.entries(error.response.data.details).forEach(([key, value]) => {
            const camelKey = key.replace(/_([a-z])/g, (_, letter) => letter.toUpperCase());
            convertedErrors[camelKey] = value as string[];
          });
          setErrors(convertedErrors);
        } else if (error.response?.status === 409) {
          setApiError(error.response?.data?.message || 'Konflikt rezerwacji - sala jest już zarezerwowana w tym czasie');
        } else {
          setApiError(error.response?.data?.message || 'Wystąpił błąd podczas tworzenia rezerwacji');
        }
      } else {
        setApiError('Wystąpił błąd podczas tworzenia rezerwacji');
      }
    } finally {
      setIsSubmitting(false);
    }
  };

  const handleClose = () => {
    setFormData({
      roomId: selectedRoomId || 0,
      reservedBy: '',
      reservedByEmail: '',
      startDateTime: '',
      endDateTime: ''
    });
    setStartDate(null);
    setEndDate(null);
    setErrors({});
    setApiError(null);
    onClose();
  };

  if (!isOpen) return null;

  return (
    <div className="fixed inset-0 bg-black bg-opacity-50 flex items-center justify-center p-4 z-50">
      <div className="bg-white rounded-lg shadow-xl max-w-md w-full max-h-[90vh] overflow-y-auto">
        <div className="flex items-center justify-between p-6 border-b border-gray-200">
          <h2 className="text-xl font-semibold text-gray-900">Nowa rezerwacja</h2>
          <button
            onClick={handleClose}
            className="p-2 hover:bg-gray-100 rounded-md"
          >
            <XMarkIcon className="h-5 w-5" />
          </button>
        </div>

        <form onSubmit={handleSubmit} className="p-6 space-y-4">
          {apiError && (
            <div className="bg-red-50 border border-red-200 text-red-700 px-4 py-3 rounded">
              {apiError}
            </div>
          )}

          {/* Room Selection */}
          <div>
            <label htmlFor="roomId" className="block text-sm font-medium text-gray-700 mb-1">
              Sala konferencyjna *
            </label>
            <select
              id="roomId"
              name="roomId"
              value={formData.roomId}
              onChange={handleInputChange}
              className={`w-full px-3 py-2 border rounded-md shadow-sm focus:outline-none focus:ring-blue-500 focus:border-blue-500 ${
                errors.roomId ? 'border-red-300' : 'border-gray-300'
              }`}
            >
              <option value="">Wybierz salę</option>
              {rooms.map((room) => (
                <option key={room.id} value={room.id}>
                  {room.name}
                </option>
              ))}
            </select>
            {errors.roomId && (
              <p className="mt-1 text-sm text-red-600">{errors.roomId[0]}</p>
            )}
          </div>

          {/* Reserved By */}
          <div>
            <label htmlFor="reservedBy" className="block text-sm font-medium text-gray-700 mb-1">
              Imię i nazwisko *
            </label>
            <input
              type="text"
              id="reservedBy"
              name="reservedBy"
              value={formData.reservedBy}
              onChange={handleInputChange}
              className={`w-full px-3 py-2 border rounded-md shadow-sm focus:outline-none focus:ring-blue-500 focus:border-blue-500 ${
                errors.reservedBy ? 'border-red-300' : 'border-gray-300'
              }`}
              placeholder="Jan Kowalski"
            />
            {errors.reservedBy && (
              <p className="mt-1 text-sm text-red-600">{errors.reservedBy[0]}</p>
            )}
          </div>

          {/* Email */}
          <div>
            <label htmlFor="reservedByEmail" className="block text-sm font-medium text-gray-700 mb-1">
              Email (opcjonalnie)
            </label>
            <input
              type="email"
              id="reservedByEmail"
              name="reservedByEmail"
              value={formData.reservedByEmail}
              onChange={handleInputChange}
              className={`w-full px-3 py-2 border rounded-md shadow-sm focus:outline-none focus:ring-blue-500 focus:border-blue-500 ${
                errors.reservedByEmail ? 'border-red-300' : 'border-gray-300'
              }`}
              placeholder="jan.kowalski@example.com"
            />
            {errors.reservedByEmail && (
              <p className="mt-1 text-sm text-red-600">{errors.reservedByEmail[0]}</p>
            )}
          </div>

          {/* Start DateTime */}
          <div>
            <label className="block text-sm font-medium text-gray-700 mb-1">
              Data i godzina rozpoczęcia *
            </label>
            <DatePicker
              selected={startDate}
              onChange={(date) => setStartDate(date)}
              showTimeSelect
              timeFormat="HH:mm"
              timeIntervals={15}
              dateFormat="dd/MM/yyyy HH:mm"
              minDate={new Date()}
              className={`w-full px-3 py-2 border rounded-md shadow-sm focus:outline-none focus:ring-blue-500 focus:border-blue-500 ${
                errors.startDateTime ? 'border-red-300' : 'border-gray-300'
              }`}
              placeholderText="Wybierz datę i godzinę"
            />
            {errors.startDateTime && (
              <p className="mt-1 text-sm text-red-600">{errors.startDateTime[0]}</p>
            )}
          </div>

          {/* End DateTime */}
          <div>
            <label className="block text-sm font-medium text-gray-700 mb-1">
              Data i godzina zakończenia *
            </label>
            <DatePicker
              selected={endDate}
              onChange={(date) => setEndDate(date)}
              showTimeSelect
              timeFormat="HH:mm"
              timeIntervals={15}
              dateFormat="dd/MM/yyyy HH:mm"
              minDate={startDate || new Date()}
              className={`w-full px-3 py-2 border rounded-md shadow-sm focus:outline-none focus:ring-blue-500 focus:border-blue-500 ${
                errors.endDateTime ? 'border-red-300' : 'border-gray-300'
              }`}
              placeholderText="Wybierz datę i godzinę"
            />
            {errors.endDateTime && (
              <p className="mt-1 text-sm text-red-600">{errors.endDateTime[0]}</p>
            )}
          </div>

          {/* Submit Buttons */}
          <div className="flex space-x-3 pt-4">
            <Button
              type="button"
              onClick={handleClose}
              className="flex-1 bg-gray-300 hover:bg-gray-400 text-gray-800 px-4 py-2 rounded-md"
            >
              Anuluj
            </Button>
            <Button
              type="submit"
              disabled={isSubmitting}
              className="flex-1 bg-blue-700 hover:bg-blue-800 text-white px-4 py-2 rounded-md disabled:opacity-50"
            >
              {isSubmitting ? 'Zapisywanie...' : 'Zapisz'}
            </Button>
          </div>
        </form>
      </div>
    </div>
  );
}
