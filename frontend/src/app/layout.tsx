import type { Metadata } from "next";
import { Inter } from "next/font/google";
import "./globals.css";
import Navigation from "@/components/Navigation";
import { NavigationProvider } from "@/contexts/NavigationContext";
import NavigationLoadingWrapper from "@/components/NavigationLoadingWrapper";

const inter = Inter({
  subsets: ["latin"],
  display: "swap",
});

export const metadata: Metadata = {
  title: "System Rezerwacji Sal",
  description: "Aplikacja do zarządzania rezerwacjami sal konferencyjnych",
  keywords: ["rezerwacje", "sale", "zarządzanie", "aplikacja"],
  authors: [{ name: "Your Company" }],
  viewport: "width=device-width, initial-scale=1",
};

export default function RootLayout({
  children,
}: Readonly<{
  children: React.ReactNode;
}>) {
  return (
    <html lang="pl" className="h-full">
      <body className={`${inter.className} h-full bg-gray-50 antialiased`}>
        <NavigationProvider>
          <NavigationLoadingWrapper>
            <div className="min-h-full">
              <Navigation />
              {children}
            </div>
          </NavigationLoadingWrapper>
        </NavigationProvider>
      </body>
    </html>
  );
}
