import { ExclamationTriangleIcon } from '@heroicons/react/24/outline';
import { cn } from '@/lib/utils';

interface ErrorMessageProps {
  message: string;
  className?: string;
  onRetry?: () => void;
}

const ErrorMessage = ({ message, className, onRetry }: ErrorMessageProps) => {
  return (
    <div className={cn('rounded-md bg-red-50 border border-red-200 p-4', className)}>
      <div className="flex">
        <div className="flex-shrink-0">
          <ExclamationTriangleIcon className="h-5 w-5 text-red-400" aria-hidden="true" />
        </div>
        <div className="ml-3">
          <h3 className="text-sm font-medium text-red-800">Wystąpił błąd</h3>
          <div className="mt-2 text-sm text-red-700">
            <p>{message}</p>
          </div>
          {onRetry && (
            <div className="mt-3">
              <button
                type="button"
                className="text-sm font-medium text-red-800 hover:text-red-900 underline"
                onClick={onRetry}
              >
                Spróbuj ponownie
              </button>
            </div>
          )}
        </div>
      </div>
    </div>
  );
};

export default ErrorMessage;
