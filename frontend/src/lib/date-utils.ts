export function formatDate(dateString: string): string {
  const date = new Date(dateString);
  return date.toLocaleDateString('pl-PL', {
    year: 'numeric',
    month: 'long',
    day: 'numeric',
  });
}

export function formatDateTime(dateString: string): string {
  const date = new Date(dateString);
  return date.toLocaleString('pl-PL', {
    year: 'numeric',
    month: 'short',
    day: 'numeric',
    hour: '2-digit',
    minute: '2-digit',
  });
}

export function formatTime(dateString: string): string {
  const date = new Date(dateString);
  return date.toLocaleTimeString('pl-PL', {
    hour: '2-digit',
    minute: '2-digit',
  });
}

export function isDateInPast(dateString: string): boolean {
  return new Date(dateString) < new Date();
}

export function getDateRange(startDate: string, endDate: string): string {
  const start = new Date(startDate);
  const end = new Date(endDate);
  
  const startDateStr = start.toLocaleDateString('pl-PL');
  const endDateStr = end.toLocaleDateString('pl-PL');
  
  const startTimeStr = formatTime(startDate);
  const endTimeStr = formatTime(endDate);
  
  if (startDateStr === endDateStr) {
    return `${startDateStr}, ${startTimeStr} - ${endTimeStr}`;
  }
  
  return `${startDateStr} ${startTimeStr} - ${endDateStr} ${endTimeStr}`;
}

export function addHours(date: Date, hours: number): Date {
  const result = new Date(date);
  result.setHours(result.getHours() + hours);
  return result;
}

export function roundToNearestQuarter(date: Date): Date {
  const result = new Date(date);
  const minutes = result.getMinutes();
  const roundedMinutes = Math.round(minutes / 15) * 15;
  
  result.setMinutes(roundedMinutes);
  result.setSeconds(0);
  result.setMilliseconds(0);
  
  return result;
}
