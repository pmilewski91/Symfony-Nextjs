"use client";

import { useState } from "react";
import { useRouter } from "next/navigation";
import { Button, Input, Select, ErrorMessage } from "@/components/ui";
import axios from "axios";
import { ValidationErrors, FormData } from "@/types/interferance";

export default function CreateRoomPage() {
  const router = useRouter();
  
  const [formData, setFormData] = useState<FormData>({
    name: "",
    description: "",
    isActive: true,
  });
  
  const [isSubmitting, setIsSubmitting] = useState(false);
  const [errors, setErrors] = useState<ValidationErrors>({});
  const [generalError, setGeneralError] = useState<string | null>(null);

  const handleInputChange = (field: keyof FormData, value: string | boolean) => {
    setFormData(prev => ({
      ...prev,
      [field]: value
    }));
    
    // Clear field error when user starts typing
    if (errors[field]) {
      setErrors(prev => {
        const newErrors = { ...prev };
        delete newErrors[field];
        return newErrors;
      });
    }
  };

  const validateForm = (): boolean => {
    const newErrors: ValidationErrors = {};
    
    // Validate name
    if (!formData.name.trim()) {
      newErrors.name = ["Nazwa sali jest wymagana"];
    } else if (formData.name.trim().length < 2) {
      newErrors.name = ["Nazwa sali musi mieć co najmniej 2 znaki"];
    } else if (formData.name.trim().length > 255) {
      newErrors.name = ["Nazwa sali nie może przekraczać 255 znaków"];
    }

    setErrors(newErrors);
    return Object.keys(newErrors).length === 0;
  };

  const handleSubmit = async (e: React.FormEvent) => {
    e.preventDefault();
    
    if (!validateForm()) {
      return;
    }

    setIsSubmitting(true);
    setGeneralError(null);
    setErrors({});

    try {
      const payload = {
        name: formData.name.trim(),
        description: formData.description.trim() || null,
        isActive: formData.isActive,
      };

      await axios.post("http://localhost:8000/api/v1/rooms", payload, {
        headers: {
          "Content-Type": "application/json",
        },
      });

      // Success - redirect to rooms list
      router.push("/");
    } catch (err: any) {
      if (err.response?.status === 400 && err.response?.data?.details) {
        // Validation errors from backend
        setErrors(err.response.data.details);
      } else {
        // General error
        const errorMessage = err.response?.data?.message || err.message || "Wystąpił błąd podczas tworzenia sali";
        setGeneralError(errorMessage);
      }
    } finally {
      setIsSubmitting(false);
    }
  };

  const handleCancel = () => {
    router.push("/");
  };

  return (
    <div className="max-w-2xl mx-auto mt-10 p-6">
      <div className="bg-white shadow-md rounded-lg overflow-hidden">
        <div className="px-6 py-5 border-b border-gray-100">
          <h1 className="text-2xl font-semibold text-gray-800">Dodaj nową salę</h1>
        </div>

        <form onSubmit={handleSubmit} className="p-6 space-y-6">
          {generalError && (
            <ErrorMessage message={generalError} />
          )}

          {/* Name Field */}
          <div>
            <label htmlFor="name" className="block text-sm font-medium text-gray-700 mb-2">
              Nazwa sali <span className="text-red-500">*</span>
            </label>
            <Input
              id="name"
              type="text"
              value={formData.name}
              onChange={(e) => handleInputChange("name", e.target.value)}
              placeholder="Wprowadź nazwę sali"
              className={errors.name ? "border-red-500" : ""}
              disabled={isSubmitting}
            />
            {errors.name && (
              <div className="mt-1">
                {errors.name.map((error, index) => (
                  <p key={index} className="text-sm text-red-600">{error}</p>
                ))}
              </div>
            )}
          </div>

          {/* Description Field */}
          <div>
            <label htmlFor="description" className="block text-sm font-medium text-gray-700 mb-2">
              Opis sali
            </label>
            <textarea
              id="description"
              value={formData.description}
              onChange={(e) => handleInputChange("description", e.target.value)}
              placeholder="Wprowadź opis sali (opcjonalne)"
              rows={4}
              className={`w-full px-3 py-2 border border-gray-300 rounded-md shadow-sm focus:outline-none focus:ring-2 focus:ring-blue-500 focus:border-blue-500 ${
                errors.description ? "border-red-500" : ""
              }`}
              disabled={isSubmitting}
            />
            {errors.description && (
              <div className="mt-1">
                {errors.description.map((error, index) => (
                  <p key={index} className="text-sm text-red-600">{error}</p>
                ))}
              </div>
            )}
          </div>

          {/* Status Field */}
          <div>
            <label htmlFor="isActive" className="block text-sm font-medium text-gray-700 mb-2">
              Status sali <span className="text-red-500">*</span>
            </label>
            <Select
              id="isActive"
              value={formData.isActive ? "true" : "false"}
              onChange={(e) => handleInputChange("isActive", e.target.value === "true")}
              options={[
                { value: "true", label: "Aktywna" },
                { value: "false", label: "Nieaktywna" },
              ]}
              disabled={isSubmitting}
            />
            {errors.isActive && (
              <div className="mt-1">
                {errors.isActive.map((error, index) => (
                  <p key={index} className="text-sm text-red-600">{error}</p>
                ))}
              </div>
            )}
          </div>

          {/* Action Buttons */}
          <div className="flex justify-end space-x-4 pt-6 border-t border-gray-200">
            <Button
              type="button"
              onClick={handleCancel}
              className="bg-gray-500 hover:bg-gray-600 text-white px-6 py-2 rounded-md"
              disabled={isSubmitting}
            >
              Anuluj
            </Button>
            <Button
              type="submit"
              className="bg-blue-700 hover:bg-blue-800 text-white px-6 py-2 rounded-md disabled:opacity-50"
              disabled={isSubmitting}
            >
              {isSubmitting ? "Dodawanie..." : "Dodaj salę"}
            </Button>
          </div>
        </form>
      </div>
    </div>
  );
}
