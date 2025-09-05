"use client";

import { useState } from "react";
import { Reservation, Room } from "@/types/interferance";
import { ChevronLeftIcon, ChevronRightIcon } from "@heroicons/react/24/outline";
import { parseLocalDateTime, getDateString } from "@/utils/dateUtils";

interface CalendarViewProps {
  reservations: Reservation[];
  rooms: Room[];
  selectedRoom: number | null;
  onDateClick: (date: Date) => void;
  onReservationClick: (reservation: Reservation) => void;
}

export default function CalendarView({ reservations, rooms, selectedRoom, onDateClick, onReservationClick }: CalendarViewProps) {
  const [currentDate, setCurrentDate] = useState(new Date());

  const today = new Date();
  const currentMonth = currentDate.getMonth();
  const currentYear = currentDate.getFullYear();

  // Get first day of month
  const firstDayOfMonth = new Date(currentYear, currentMonth, 1);
  
  // Get first day of the calendar (including previous month days)
  const firstDayOfCalendar = new Date(firstDayOfMonth);
  firstDayOfCalendar.setDate(firstDayOfCalendar.getDate() - firstDayOfMonth.getDay());

  // Generate calendar days
  const calendarDays = [];
  const currentDay = new Date(firstDayOfCalendar);
  
  for (let week = 0; week < 6; week++) {
    const weekDays = [];
    for (let day = 0; day < 7; day++) {
      weekDays.push(new Date(currentDay));
      currentDay.setDate(currentDay.getDate() + 1);
    }
    calendarDays.push(weekDays);
  }

  const getReservationsForDate = (date: Date) => {
    // Ensure reservations is an array
    if (!Array.isArray(reservations)) {
      return [];
    }
    
    const dateStr = getDateString(date);
    return reservations.filter(reservation => {
      // Parse dates as local dates to avoid timezone issues
      const startDate = parseLocalDateTime(reservation.startDateTime);
      const endDate = parseLocalDateTime(reservation.endDateTime);
      
      const startDateStr = getDateString(startDate);
      const endDateStr = getDateString(endDate);
      
      return dateStr >= startDateStr && dateStr <= endDateStr;
    });
  };

  const navigateMonth = (direction: 'prev' | 'next') => {
    const newDate = new Date(currentDate);
    if (direction === 'prev') {
      newDate.setMonth(currentMonth - 1);
    } else {
      newDate.setMonth(currentMonth + 1);
    }
    setCurrentDate(newDate);
  };

  const formatMonthYear = (date: Date) => {
    return date.toLocaleDateString('pl-PL', { month: 'long', year: 'numeric' });
  };

  const isToday = (date: Date) => {
    return date.toDateString() === today.toDateString();
  };

  const isCurrentMonth = (date: Date) => {
    return date.getMonth() === currentMonth;
  };

  const isPastDate = (date: Date) => {
    const today = new Date();
    today.setHours(0, 0, 0, 0);
    const compareDate = new Date(date);
    compareDate.setHours(0, 0, 0, 0);
    return compareDate < today;
  };

  return (
    <div className="bg-white">
      {/* Calendar Header */}
      <div className="flex items-center justify-between mb-6">
        <button
          onClick={() => navigateMonth('prev')}
          className="p-2 hover:bg-gray-100 rounded-md"
        >
          <ChevronLeftIcon className="h-5 w-5" />
        </button>
        <h2 className="text-lg font-semibold text-gray-900 capitalize">
          {formatMonthYear(currentDate)}
        </h2>
        <button
          onClick={() => navigateMonth('next')}
          className="p-2 hover:bg-gray-100 rounded-md"
        >
          <ChevronRightIcon className="h-5 w-5" />
        </button>
      </div>

      {/* Calendar Grid */}
      <div className="border border-gray-200 rounded-lg overflow-hidden">
        {/* Days header */}
        <div className="grid grid-cols-7 bg-gray-50 min-w-[700px] sm:min-w-0">
          {['Nd', 'Pn', 'Wt', 'Śr', 'Cz', 'Pt', 'So'].map((day) => (
            <div key={day} className="p-3 text-center text-sm font-medium text-gray-500">
              {day}
            </div>
          ))}
        </div>

        {/* Calendar days - scrollable on mobile */}
        <div className="overflow-x-auto">
          <div className="grid grid-cols-7 divide-y divide-gray-200 min-w-[700px] sm:min-w-0">
            {calendarDays.map((week, weekIndex) => (
              week.map((date, dayIndex) => {
                const dayReservations = getReservationsForDate(date);
                const isClickable = !isPastDate(date) && isCurrentMonth(date);

                return (
                  <div
                    key={`${weekIndex}-${dayIndex}`}
                    className={`min-h-[100px] sm:min-h-[120px] p-2 border-r border-gray-200 last:border-r-0 ${
                      isClickable ? 'cursor-pointer hover:bg-gray-50' : 'cursor-default'
                    } ${!isCurrentMonth(date) ? 'bg-gray-50 text-gray-400' : ''} ${
                      isPastDate(date) ? 'bg-gray-100' : ''
                    }`}
                    onClick={() => isClickable && onDateClick(date)}
                  >
                    <div className={`text-sm ${isToday(date) ? 'font-bold text-blue-600' : ''}`}>
                      {date.getDate()}
                    </div>
                    
                    {/* Reservations for this day */}
                    <div className="mt-1 space-y-1">
                      {dayReservations.slice(0, 2).map((reservation, index) => {
                        const room = rooms.find(r => r.id === reservation.room.id);
                        const shouldShow = !selectedRoom || reservation.room.id === selectedRoom;
                        
                        if (!shouldShow) return null;

                        return (
                          <div
                            key={`${reservation.id}-${index}`}
                            className="text-xs bg-blue-100 text-blue-800 p-1 rounded truncate cursor-pointer hover:bg-blue-200 transition-colors"
                            title={`${reservation.reservedBy} - ${room?.name}`}
                            onClick={(e) => {
                              e.stopPropagation();
                              onReservationClick(reservation);
                            }}
                          >
                            {reservation.reservedBy}
                          </div>
                        );
                      })}
                      
                      {dayReservations.length > 2 && (
                        <div className="text-xs text-gray-500">
                          +{dayReservations.length - 2} więcej
                        </div>
                      )}
                    </div>
                  </div>
                );
              })
            ))}
          </div>
        </div>
      </div>

      {/* Legend */}
      <div className="mt-4 space-y-2">
        <div className="flex flex-wrap items-center gap-x-6 gap-y-2 text-sm text-gray-600">
          <div className="flex items-center space-x-2">
            <div className="w-3 h-3 bg-blue-100 rounded"></div>
            <span>Rezerwacja (kliknij aby zobaczyć szczegóły)</span>
          </div>
          <div className="flex items-center space-x-2">
            <div className="w-3 h-3 bg-gray-100 rounded"></div>
            <span>Dzień przeszły</span>
          </div>
          <div className="flex items-center space-x-2">
            <div className="w-3 h-3 bg-blue-600 rounded"></div>
            <span>Dzisiaj</span>
          </div>
        </div>
        <p className="text-xs text-gray-500">
          Kliknij na dzień aby zobaczyć rezerwacje i dodać nową, lub kliknij na istniejącą rezerwację aby zobaczyć szczegóły.
        </p>
        <p className="text-xs text-gray-400 sm:hidden">
          💡 Przeciągnij kalendarz w lewo/prawo aby zobaczyć wszystkie dni
        </p>
      </div>
    </div>
  );
}
