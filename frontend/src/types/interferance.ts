export interface Room {
  id: number;
  name: string;
  description?: string | null;
  isActive: boolean;
}

export interface ValidationErrors {
  [key: string]: string[];
}

export interface FormData {
  name: string;
  description: string;
  isActive: boolean;
}