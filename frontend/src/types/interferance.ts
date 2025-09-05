export interface Room {
  id: number;
  name: string;
  description?: string | null;
  isActive: boolean;
}

export interface Reservation {
  id: number;
  room: Room;
  reservedBy: string;
  reservedByEmail?: string | null;
  startDateTime: string;
  endDateTime: string;
  createdAt: string;
}

export interface ReservationFormData {
  roomId: number;
  reservedBy: string;
  reservedByEmail?: string;
  startDateTime: string;
  endDateTime: string;
}

export interface ValidationErrors {
  [key: string]: string[];
}

export interface FormData {
  name: string;
  description: string;
  isActive: boolean;
}
