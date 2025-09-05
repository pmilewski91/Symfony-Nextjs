"use client";

import { useEffect, useState } from 'react';
import { usePathname } from 'next/navigation';

export const useNavigationLoading = () => {
  const [isLoading, setIsLoading] = useState(false);
  const pathname = usePathname();

  useEffect(() => {
    const handleStart = () => setIsLoading(true);
    const handleComplete = () => setIsLoading(false);

    // Obsługa nawigacji dla Next.js App Router
    const originalPushState = window.history.pushState;
    const originalReplaceState = window.history.replaceState;

    window.history.pushState = function(...args) {
      handleStart();
      originalPushState.apply(window.history, args);
    };

    window.history.replaceState = function(...args) {
      handleStart();
      originalReplaceState.apply(window.history, args);
    };

    // Nasłuchiwanie na zmiany w pathname
    handleComplete();

    // Event listener dla popstate (back/forward browser buttons)
    const handlePopState = () => {
      handleStart();
      // Loading zostanie zakończony przez useEffect z pathname
    };

    window.addEventListener('popstate', handlePopState);

    // Cleanup
    return () => {
      window.history.pushState = originalPushState;
      window.history.replaceState = originalReplaceState;
      window.removeEventListener('popstate', handlePopState);
    };
  }, [pathname]); // Dependency na pathname aby loading kończyć po zmianie

  return isLoading;
};
