"use client";

import { useNavigationContext } from "@/contexts/NavigationContext";
import LoadingOverlay from "./LoadingOverlay";

const NavigationLoadingWrapper = ({ children }: { children: React.ReactNode }) => {
  const { isLoading } = useNavigationContext();
  
  return (
    <>
      {children}
      <LoadingOverlay isVisible={isLoading} />
    </>
  );
};

export default NavigationLoadingWrapper;
