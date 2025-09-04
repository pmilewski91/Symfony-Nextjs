import axios, { AxiosInstance, AxiosError } from 'axios';
import { 
  Room, 
  Reservation, 
  CreateRoomRequest, 
  UpdateRoomRequest, 
  CreateReservationRequest,
  ApiError 
} from '@/types/api';

class ApiClient {
  private client: AxiosInstance;

  constructor() {
    this.client = axios.create({
      baseURL: process.env.NEXT_PUBLIC_API_URL || 'http://localhost:8000/api/v1',
      headers: {
        'Content-Type': 'application/json',
      },
      timeout: 10000,
    });

    // Response interceptor for error handling
    this.client.interceptors.response.use(
      (response) => response,
      (error: AxiosError) => {
        const apiError: ApiError = {
          message: 'An error occurred',
          code: 'UNKNOWN_ERROR',
        };

        if (error.response) {
          // Server responded with error status
          const data = error.response.data as any;
          apiError.message = data?.message || error.message;
          apiError.code = data?.code || `HTTP_${error.response.status}`;
          apiError.details = error.response.data;
        } else if (error.request) {
          // Network error
          apiError.message = 'Network error - please check your connection';
          apiError.code = 'NETWORK_ERROR';
        } else {
          // Other error
          apiError.message = error.message;
        }

        return Promise.reject(apiError);
      }
    );
  }

  // Room methods
  async getRooms(): Promise<Room[]> {
    const response = await this.client.get<Room[]>('/rooms');
    return response.data;
  }

  async getRoom(id: number): Promise<Room> {
    const response = await this.client.get<Room>(`/rooms/${id}`);
    return response.data;
  }

  async createRoom(room: CreateRoomRequest): Promise<Room> {
    const response = await this.client.post<Room>('/rooms', room);
    return response.data;
  }

  async updateRoom(id: number, room: UpdateRoomRequest): Promise<Room> {
    const response = await this.client.put<Room>(`/rooms/${id}`, room);
    return response.data;
  }

  async deleteRoom(id: number): Promise<void> {
    await this.client.delete(`/rooms/${id}`);
  }

  // Reservation methods
  async getReservations(): Promise<Reservation[]> {
    const response = await this.client.get<Reservation[]>('/reservations');
    return response.data;
  }

  async getReservationsForRoom(roomId: number): Promise<Reservation[]> {
    const response = await this.client.get<Reservation[]>(`/reservations/room/${roomId}`);
    return response.data;
  }

  async createReservation(reservation: CreateReservationRequest): Promise<Reservation> {
    const response = await this.client.post<Reservation>('/reservations', reservation);
    return response.data;
  }

  // Health check
  async healthCheck(): Promise<{ status: string }> {
    const response = await this.client.get('/health');
    return response.data;
  }
}

export const apiClient = new ApiClient();
export default ApiClient;
