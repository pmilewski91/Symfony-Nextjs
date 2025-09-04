export interface Room {
  id: number;
  name: string;
  description?: string;
  isActive: boolean;
  createdAt: string;
  updatedAt: string;
}

export interface CreateRoomRequest {
  name: string;
  description?: string;
  isActive?: boolean;
}

export interface UpdateRoomRequest extends Partial<CreateRoomRequest> {}

export interface Reservation {
  id: number;
  room: Room;
  reservedBy: string;
  reservedByEmail?: string;
  startDateTime: string;
  endDateTime: string;
  createdAt: string;
}

export interface CreateReservationRequest {
  roomId: number;
  reservedBy: string;
  reservedByEmail?: string;
  startDateTime: string;
  endDateTime: string;
}

export interface ApiError {
  message: string;
  code?: string;
  details?: any;
}

export interface ApiResponse<T> {
  data: T;
  success: boolean;
  message?: string;
}
