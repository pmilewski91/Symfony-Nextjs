import useSWR from 'swr';
import { apiClient } from '@/lib/api-client';
import { Room, Reservation } from '@/types/api';

// Fetcher functions for SWR
const fetchers = {
  rooms: () => apiClient.getRooms(),
  room: (id: number) => apiClient.getRoom(id),
  reservations: () => apiClient.getReservations(),
  roomReservations: (roomId: number) => apiClient.getReservationsForRoom(roomId),
};

// Custom hooks using SWR
export function useRooms() {
  const { data, error, isLoading, mutate } = useSWR<Room[]>('rooms', fetchers.rooms, {
    revalidateOnFocus: false,
    dedupingInterval: 5000,
  });

  return {
    rooms: data,
    isLoading,
    isError: error,
    mutate,
  };
}

export function useRoom(id: number) {
  const { data, error, isLoading, mutate } = useSWR<Room>(
    id ? `room-${id}` : null,
    () => fetchers.room(id),
    {
      revalidateOnFocus: false,
    }
  );

  return {
    room: data,
    isLoading,
    isError: error,
    mutate,
  };
}

export function useReservations() {
  const { data, error, isLoading, mutate } = useSWR<Reservation[]>(
    'reservations',
    fetchers.reservations,
    {
      revalidateOnFocus: false,
      refreshInterval: 30000, // Auto refresh every 30 seconds
    }
  );

  return {
    reservations: data,
    isLoading,
    isError: error,
    mutate,
  };
}

export function useRoomReservations(roomId: number) {
  const { data, error, isLoading, mutate } = useSWR<Reservation[]>(
    roomId ? `room-reservations-${roomId}` : null,
    () => fetchers.roomReservations(roomId),
    {
      revalidateOnFocus: false,
      refreshInterval: 30000,
    }
  );

  return {
    reservations: data,
    isLoading,
    isError: error,
    mutate,
  };
}
