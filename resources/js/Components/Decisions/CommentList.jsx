import { useState, useEffect } from "react";
import { ChevronLeftIcon, ChevronRightIcon } from "@heroicons/react/24/solid";
import Avatar from "@/Components/Avatar";

export default function CommentList({ decisionId }) {
    const [comments, setComments] = useState([]);
    const [loading, setLoading] = useState(true);
    const [pagination, setPagination] = useState({
        current_page: 1,
        last_page: 1,
        total: 0,
    });

    const fetchComments = async (page = 1) => {
        setLoading(true);
        try {
            const response = await fetch(
                `/api/decisions/${decisionId}/comments?page=${page}`
            );
            const data = await response.json();
            setComments(data.data);
            setPagination({
                current_page: data.current_page,
                last_page: data.last_page,
                total: data.total,
            });
        } catch (error) {
            console.error("Error fetching comments:", error);
        } finally {
            setLoading(false);
        }
    };

    useEffect(() => {
        if (decisionId) {
            fetchComments();
        }
    }, [decisionId]);

    const handlePageChange = (page) => {
        fetchComments(page);
    };

    const formatDate = (dateString) => {
        const date = new Date(dateString);
        return date.toLocaleDateString("es-ES", {
            day: "2-digit",
            month: "2-digit",
            year: "numeric",
            hour: "2-digit",
            minute: "2-digit",
        });
    };

    if (loading && comments.length === 0) {
        return (
            <div className="flex flex-col justify-center items-center py-12 text-center">
                <div className="animate-spin rounded-full h-10 w-10 border-4 border-gray-200 border-t-indigo-600 mb-4"></div>
                <p className="text-gray-500">Cargando comentarios...</p>
            </div>
        );
    }

    if (comments.length === 0) {
        return (
            <div className="bg-gray-50 border border-gray-200 rounded-lg p-8 text-center">
                <svg
                    className="w-12 h-12 mx-auto text-gray-400 mb-3"
                    fill="none"
                    stroke="currentColor"
                    viewBox="0 0 24 24"
                    xmlns="http://www.w3.org/2000/svg"
                >
                    <path
                        strokeLinecap="round"
                        strokeLinejoin="round"
                        strokeWidth={1.5}
                        d="M8 12h.01M12 12h.01M16 12h.01M21 12c0 4.418-4.03 8-9 8a9.863 9.863 0 01-4.255-.949L3 20l1.395-3.72C3.512 15.042 3 13.574 3 12c0-4.418 4.03-8 9-8s9 3.582 9 8z"
                    />
                </svg>
                <p className="text-gray-500 mb-2">No hay comentarios todavía</p>
                <p className="text-sm text-gray-400">
                    ¡Sé el primero en compartir tu opinión sobre esta decisión!
                </p>
            </div>
        );
    }

    return (
        <div>
            <h3 className="font-semibold text-gray-700 mb-4">
                Comentarios ({pagination.total})
            </h3>
            <div className="space-y-4">
                {comments.map((comment) => (
                    <div
                        key={comment.id}
                        className="bg-gray-50 rounded-lg p-6 shadow-sm"
                    >
                        <div className="flex items-start space-x-4">
                            <div className="flex-shrink-0">
                                <Avatar user={comment.user} />
                            </div>
                            <div className="flex-1">
                                <div className="flex justify-between items-center mb-2">
                                    <h4 className="text-sm font-semibold text-gray-800">
                                        {comment.user?.name || "Usuario"}
                                    </h4>
                                    <span className="text-xs text-gray-400">
                                        {formatDate(comment.created_at)}
                                    </span>
                                </div>
                                <p className="text-gray-600 whitespace-pre-wrap">
                                    {comment.content}
                                </p>
                            </div>
                        </div>
                    </div>
                ))}
            </div>

            {pagination.last_page > 1 && (
                <div className="flex justify-center items-center space-x-4 mt-6">
                    <button
                        onClick={() =>
                            handlePageChange(pagination.current_page - 1)
                        }
                        disabled={pagination.current_page === 1}
                        className="p-2 rounded-full border border-gray-300 disabled:opacity-50 disabled:cursor-not-allowed"
                    >
                        <ChevronLeftIcon className="h-4 w-4" />
                    </button>
                    <span className="text-sm text-gray-600">
                        Página {pagination.current_page} de{" "}
                        {pagination.last_page}
                    </span>
                    <button
                        onClick={() =>
                            handlePageChange(pagination.current_page + 1)
                        }
                        disabled={
                            pagination.current_page === pagination.last_page
                        }
                        className="p-2 rounded-full border border-gray-300 disabled:opacity-50 disabled:cursor-not-allowed"
                    >
                        <ChevronRightIcon className="h-4 w-4" />
                    </button>
                </div>
            )}
        </div>
    );
}
