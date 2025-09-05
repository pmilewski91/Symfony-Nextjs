import Loading from './ui/Loading';

interface LoadingOverlayProps {
  isVisible: boolean;
}

const LoadingOverlay = ({ isVisible }: LoadingOverlayProps) => {
  if (!isVisible) return null;

  return (
    <div className="fixed inset-0 bg-white bg-opacity-80 backdrop-blur-sm z-50 flex items-center justify-center">
      <div className="bg-white rounded-lg shadow-lg p-8 flex flex-col items-center">
        <Loading size="lg" />
        <p className="mt-4 text-lg font-medium text-gray-700">
          Ładowanie...
        </p>
        <p className="mt-1 text-sm text-gray-500">
          Proszę czekać
        </p>
      </div>
    </div>
  );
};

export default LoadingOverlay;
