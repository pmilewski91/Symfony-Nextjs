"use client";

import { useState, useEffect } from "react";
import { useRouter } from "next/navigation";
import { Button } from "@/components/ui";
import axios from "axios";
import { Room } from "@/types/interferance";

export default function HomePage() {
  const router = useRouter();
  

  const [rooms, setRooms] = useState<Room[]>([]);
  const [isLoading, setIsLoading] = useState(true);
  const [isError, setIsError] = useState<string | null>(null);
  const [isDeleting, setIsDeleting] = useState<number | null>(null);

  const fetchRooms = () => {
    setIsLoading(true);
    setIsError(null);
    axios.get('http://localhost:8000/api/v1/rooms')
      .then(res => {
        setRooms(res.data);
        setIsLoading(false);
      })
      .catch(err => {
        setIsError(err?.message || 'Błąd pobierania sal');
        setIsLoading(false);
      });
  };

  useEffect(() => {
    fetchRooms();
  }, []);

  const deleteRoom = async (roomId: number, roomName: string) => {
    if (!confirm(`Czy na pewno chcesz usunąć salę "${roomName}"?`)) {
      return;
    }

    setIsDeleting(roomId);
    
    try {
      await axios.delete(`http://localhost:8000/api/v1/rooms/${roomId}`);
      // Odśwież listę sal po usunięciu
      fetchRooms();
    } catch (err: any) {
      let errorMessage = 'Błąd podczas usuwania sali';
      
      if (err.response?.status === 404) {
        errorMessage = 'Sala nie została znaleziona';
      } else if (err.response?.status === 409) {
        errorMessage = 'Nie można usunąć sali - ma aktywne rezerwacje';
      } else if (err.response?.data?.message) {
        errorMessage = err.response.data.message;
      }
      
      alert(errorMessage);
    } finally {
      setIsDeleting(null);
    }
  };

  
  return (
    <div className="max-w-full mx-auto mt-10 p-6">
      <div className="bg-white shadow-md rounded-lg overflow-hidden">
        <div className="px-6 py-5 border-b border-gray-100 flex items-center justify-between">
          <h1 className="text-2xl font-semibold text-gray-800">Sale konferencyjne</h1>
        </div>

        <div className="p-6">
          {isLoading ? (
            <div className="py-10 text-center text-blue-600">Ładowanie sal…</div>
          ) : isError ? (
            <div className="py-10 text-center">
              <div className="text-red-600 font-medium">Wystąpił błąd podczas pobierania sal.</div>
              <div className="text-xs text-gray-400 mt-2">{isError}</div>
            </div>
          ) : (
            <>
              {/* Desktop / Tablet: table view */}
              <div className="hidden sm:block">
                <div className="overflow-x-auto">
                  <table className="min-w-full divide-y divide-gray-200">
                    <thead className="bg-gray-50">
                      <tr>
                        <th className="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Nazwa</th>
                        <th className="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Opis</th>
                        <th className="px-6 py-3 text-center text-xs font-medium text-gray-500 uppercase tracking-wider">Status</th>
                        <th className="px-6 py-3 text-center text-xs font-medium text-gray-500 uppercase tracking-wider">Akcja</th>
                      </tr>
                    </thead>
                    <tbody className="bg-white divide-y divide-gray-100">
                      {rooms && rooms.length > 0 ? (
                        rooms.map((room: Room) => (
                          <tr key={room.id} className="hover:bg-gray-50">
                            <td className="px-6 py-4 whitespace-nowrap text-sm font-medium text-gray-900">{room.name}</td>
                            <td className="px-6 py-4 whitespace-normal text-sm text-gray-600 max-w-xl">{room.description || '-'}</td>
                            <td className="px-6 py-4 whitespace-nowrap text-center">
                              {room.isActive ? (
                                <span className="inline-flex items-center px-2.5 py-1 rounded-full text-xs font-semibold bg-green-100 text-green-800">
                                  <span className="w-2 h-2 mr-2 bg-green-600 rounded-full" />
                                  Aktywna
                                </span>
                              ) : (
                                <span className="inline-flex items-center px-2.5 py-1 rounded-full text-xs font-semibold bg-red-100 text-red-800">
                                  <span className="w-2 h-2 mr-2 bg-red-600 rounded-full" />
                                  Nieaktywna
                                </span>
                              )}
                            </td>
                            <td className="px-6 py-4 whitespace-nowrap text-center space-x-2">
                              {room.isActive ? (
                                <Button className="bg-green-600 hover:bg-green-700 text-white px-4 py-2 rounded-md" disabled={false}>
                                  Rezerwuj
                                </Button>
                              ) : (
                                <Button className="bg-gray-100 text-black px-4 py-2 rounded-md" disabled>
                                  Niedostępna
                                </Button>
                              )}
                              <Button 
                                className="bg-blue-700 hover:bg-blue-800 text-white px-4 py-2 rounded-md"
                                onClick={() => router.push(`/rooms/${room.id}/edit`)}
                              >
                                Edytuj
                              </Button>
                              <Button 
                                className="bg-red-700 hover:bg-red-800 text-white px-4 py-2 rounded-md disabled:opacity-50"
                                onClick={() => deleteRoom(room.id, room.name)}
                                disabled={isDeleting === room.id}
                              >
                                {isDeleting === room.id ? 'Usuwanie...' : 'Usuń'}
                              </Button>
                            </td>
                          </tr>
                        ))
                      ) : (
                        <tr>
                          <td colSpan={4} className="px-6 py-8 text-center text-gray-400">Brak sal do wyświetlenia</td>
                        </tr>
                      )}
                    </tbody>
                  </table>
                </div>
              </div>

              {/* Mobile: card list */}
              <div className="sm:hidden">
                {rooms && rooms.length > 0 ? (
                  <div className="space-y-4">
                    {rooms.map((room) => (
                      <div key={room.id} className="bg-gray-50 border border-gray-100 rounded-lg p-4">
                        <div className="flex items-start justify-between">
                          <div>
                            <div className="text-sm font-semibold text-gray-900">{room.name}</div>
                            <div className="text-xs text-gray-500 mt-1">{room.description || '-'}</div>
                          </div>
                          <div className="ml-4 text-right">
                            <div>
                              {room.isActive ? (
                                <span className="inline-flex items-center px-2 py-0.5 rounded-full text-xs font-medium bg-green-100 text-green-800">
                                  Aktywna
                                </span>
                              ) : (
                                <span className="inline-flex items-center px-2 py-0.5 rounded-full text-xs font-medium bg-red-100 text-red-800">
                                  Nieaktywna
                                </span>
                              )}
                            </div>
                            <div className="mt-3 space-y-2 space-x-2">
                              {room.isActive ? (
                                <Button className="bg-green-600 hover:bg-green-700 text-white px-3 py-1 rounded-md" disabled={false}>
                                  Rezerwuj
                                </Button>
                              ) : (
                                <Button className="bg-gray-100 text-black px-3 py-1 rounded-md" disabled>
                                  Niedostępna
                                </Button>
                              )}
                              <Button 
                                className="bg-blue-700 hover:bg-blue-800 text-white px-4 py-2 rounded-md"
                                onClick={() => router.push(`/rooms/${room.id}/edit`)}
                              >
                                Edytuj
                              </Button>
                              <Button 
                                className="bg-red-700 hover:bg-red-800 text-white px-4 py-2 rounded-md disabled:opacity-50"
                                onClick={() => deleteRoom(room.id, room.name)}
                                disabled={isDeleting === room.id}
                              >
                                {isDeleting === room.id ? 'Usuwanie...' : 'Usuń'}
                              </Button>
                            </div>
                          </div>
                        </div>
                      </div>
                    ))}
                  </div>
                ) : (
                  <div className="py-8 text-center text-gray-400">Brak sal do wyświetlenia</div>
                )}
              </div>
            </>
          )}
        </div>
        <Button 
          className="bg-blue-700 hover:bg-blue-800 text-white px-4 py-2 rounded-md mx-2 my-2"
          onClick={() => router.push("/rooms/create")}
        >
          Dodaj salę
        </Button>
      </div>
    </div>
  );
}
