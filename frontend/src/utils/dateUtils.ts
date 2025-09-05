/**
 * Date utilities for handling timezone-safe operations
 */

/**
 * Converts a date string to a local date without timezone conversion
 * This is useful when the API returns dates that should be treated as local dates
 */
export function parseLocalDateTime(dateString: string): Date {
  // Remove timezone info and parse as local date
  const cleanDateString = dateString.replace(/[+-]\d{2}:\d{2}$/, '').replace('T', ' ');
  return new Date(cleanDateString);
}

/**
 * Get date string in YYYY-MM-DD format for a given date
 */
export function getDateString(date: Date): string {
  return date.getFullYear() + '-' + 
    String(date.getMonth() + 1).padStart(2, '0') + '-' + 
    String(date.getDate()).padStart(2, '0');
}

/**
 * Format date for display in Polish locale
 */
export function formatDateTime(dateTime: string): string {
  const date = parseLocalDateTime(dateTime);
  return date.toLocaleString('pl-PL', {
    year: 'numeric',
    month: 'long',
    day: 'numeric',
    hour: '2-digit',
    minute: '2-digit'
  });
}

/**
 * Format date to ISO string for API calls
 */
export function toISOStringLocal(date: Date): string {
  const year = date.getFullYear();
  const month = String(date.getMonth() + 1).padStart(2, '0');
  const day = String(date.getDate()).padStart(2, '0');
  const hours = String(date.getHours()).padStart(2, '0');
  const minutes = String(date.getMinutes()).padStart(2, '0');
  const seconds = String(date.getSeconds()).padStart(2, '0');
  
  return `${year}-${month}-${day} ${hours}:${minutes}:${seconds}`;
}
