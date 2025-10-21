import React from "react";

export default function Avatar({ user, size = "md" }) {
    // Definimos tamaños disponibles
    const sizes = {
        sm: "w-8 h-8 text-xs",
        md: "w-10 h-10 text-sm",
        lg: "w-12 h-12 text-base",
    };

    const sizeClass = sizes[size] || sizes.md;

    // Si el usuario tiene un avatar, lo mostramos
    if (user?.avatar) {
        return (
            <img
                src={user.avatar}
                alt={user?.name || "Usuario"}
                className={`${sizeClass} rounded-full object-cover border-2 border-white shadow`}
            />
        );
    }

    // Si no tiene avatar, generamos uno con sus iniciales
    const getInitials = () => {
        if (!user?.name) return "U";

        // Obtener iniciales del nombre
        const nameParts = user.name.split(" ");
        if (nameParts.length === 1) {
            return nameParts[0].charAt(0).toUpperCase();
        }

        return `${nameParts[0].charAt(0)}${nameParts[1].charAt(
            0
        )}`.toUpperCase();
    };

    // Función para generar un color consistente basado en el nombre del usuario
    const getBackgroundColor = () => {
        if (!user?.name) return "bg-indigo-600";

        const colors = [
            "bg-indigo-600",
            "bg-purple-600",
            "bg-pink-600",
            "bg-red-600",
            "bg-orange-600",
            "bg-amber-600",
            "bg-yellow-600",
            "bg-lime-600",
            "bg-green-600",
            "bg-emerald-600",
            "bg-teal-600",
            "bg-cyan-600",
            "bg-blue-600",
        ];

        // Usamos la suma de los códigos ASCII de las letras del nombre para elegir un color
        const sum = user.name
            .split("")
            .reduce((acc, char) => acc + char.charCodeAt(0), 0);
        return colors[sum % colors.length];
    };

    return (
        <div
            className={`${sizeClass} rounded-full ${getBackgroundColor()} flex items-center justify-center text-white font-medium shadow`}
        >
            {getInitials()}
        </div>
    );
}
