import { useState, useEffect, useRef } from "react";
import { ChevronLeftIcon, ChevronRightIcon } from "@heroicons/react/24/solid";
import { PencilIcon, TrashIcon, XMarkIcon } from "@heroicons/react/24/outline";
import Avatar from "@/Components/Avatar";
import PrimaryButton from "@/Components/PrimaryButton";
import InputError from "@/Components/InputError";
import axios from "axios";
import { usePage } from '@inertiajs/react';

export default function CommentList({ decisionId }) {
    const { auth } = usePage().props;
    const [comments, setComments] = useState([]);
    const [loading, setLoading] = useState(true);
    const [loadingMore, setLoadingMore] = useState(false);
    const [pagination, setPagination] = useState({
        current_page: 1,
        last_page: 1,
        total: 0,
    });
    const [editingCommentId, setEditingCommentId] = useState(null);
    const [editContent, setEditContent] = useState('');
    const [editError, setEditError] = useState('');
    const [deleteConfirm, setDeleteConfirm] = useState(null);

    // Reference for infinite scroll
    const observerTarget = useRef(null);
    const listContainerRef = useRef(null);

    const fetchComments = async (page = 1, append = false) => {
        if (page === 1) {
            setLoading(true);
        } else {
            setLoadingMore(true);
        }

        try {
            const response = await axios.get(
                `/api/decisions/${decisionId}/comments?page=${page}`
            );
            const data = response.data;

            if (append) {
                setComments(prevComments => [...prevComments, ...data.data]);
            } else {
                setComments(data.data);
            }

            setPagination({
                current_page: data.current_page,
                last_page: data.last_page,
                total: data.total,
            });
        } catch (error) {
            console.error("Error fetching comments:", error);
        } finally {
            setLoading(false);
            setLoadingMore(false);
        }
    };

    // Setup intersection observer for infinite scroll
    useEffect(() => {
        const observer = new IntersectionObserver(
            entries => {
                if (entries[0].isIntersecting && pagination.current_page < pagination.last_page && !loadingMore) {
                    fetchComments(pagination.current_page + 1, true);
                }
            },
            { threshold: 1.0 }
        );

        if (observerTarget.current) {
            observer.observe(observerTarget.current);
        }

        return () => {
            if (observerTarget.current) {
                observer.unobserve(observerTarget.current);
            }
        };
    }, [pagination, loadingMore]);

    useEffect(() => {
        if (decisionId) {
            fetchComments();
        }
    }, [decisionId]);

    // Edit comment functionality
    const startEditing = (comment) => {
        setEditingCommentId(comment.id);
        setEditContent(comment.content);
        setEditError('');
    };

    const cancelEditing = () => {
        setEditingCommentId(null);
        setEditContent('');
        setEditError('');
    };

    const submitEdit = async (commentId) => {
        if (editContent.trim().length < 10) {
            setEditError('El comentario debe tener al menos 10 caracteres.');
            return;
        }

        try {
            const response = await axios.put(
                `/api/decisions/${decisionId}/comments/${commentId}`,
                { content: editContent }
            );

            // Update the comment in the list
            setComments(prevComments =>
                prevComments.map(comment =>
                    comment.id === commentId ? { ...comment, content: editContent } : comment
                )
            );

            cancelEditing();
        } catch (error) {
            console.error("Error updating comment:", error);
            setEditError(error.response?.data?.message || 'Error al actualizar el comentario');
        }
    };

    // Delete comment functionality
    const confirmDelete = (commentId) => {
        setDeleteConfirm(commentId);
    };

    const cancelDelete = () => {
        setDeleteConfirm(null);
    };

    const deleteComment = async (commentId) => {
        try {
            await axios.delete(`/api/decisions/${decisionId}/comments/${commentId}`);

            // Remove the comment from the list
            setComments(prevComments =>
                prevComments.filter(comment => comment.id !== commentId)
            );

            // Update the total count
            setPagination(prev => ({
                ...prev,
                total: prev.total - 1
            }));

            cancelDelete();
        } catch (error) {
            console.error("Error deleting comment:", error);
        }
    };

    const formatDate = (dateString) => {
        const date = new Date(dateString);
        const now = new Date();
        const diffInSeconds = Math.floor((now - date) / 1000);

        // Diferentes intervalos de tiempo en segundos
        const minute = 60;
        const hour = minute * 60;
        const day = hour * 24;
        const week = day * 7;
        const month = day * 30;
        const year = day * 365;

        // Determinar el formato relativo más adecuado
        if (diffInSeconds < minute) {
            return "ahora mismo";
        } else if (diffInSeconds < hour) {
            const minutes = Math.floor(diffInSeconds / minute);
            return `hace ${minutes} ${minutes === 1 ? "minuto" : "minutos"}`;
        } else if (diffInSeconds < day) {
            const hours = Math.floor(diffInSeconds / hour);
            return `hace ${hours} ${hours === 1 ? "hora" : "horas"}`;
        } else if (diffInSeconds < week) {
            const days = Math.floor(diffInSeconds / day);
            return `hace ${days} ${days === 1 ? "día" : "días"}`;
        } else if (diffInSeconds < month) {
            const weeks = Math.floor(diffInSeconds / week);
            return `hace ${weeks} ${weeks === 1 ? "semana" : "semanas"}`;
        } else if (diffInSeconds < year) {
            const months = Math.floor(diffInSeconds / month);
            return `hace ${months} ${months === 1 ? "mes" : "meses"}`;
        } else {
            const years = Math.floor(diffInSeconds / year);
            return `hace ${years} ${years === 1 ? "año" : "años"}`;
        }
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
        <div ref={listContainerRef}>
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
                                    <div className="flex items-center space-x-2">
                                        {/* Edit/Delete buttons only for comment author */}
                                        {auth.user && auth.user.id === comment.user_id && !editingCommentId && !deleteConfirm && (
                                            <div className="flex space-x-2">
                                                <button
                                                    onClick={() => startEditing(comment)}
                                                    className="text-gray-400 hover:text-indigo-600 transition-colors"
                                                    title="Editar comentario"
                                                >
                                                    <PencilIcon className="h-4 w-4" />
                                                </button>
                                                <button
                                                    onClick={() => confirmDelete(comment.id)}
                                                    className="text-gray-400 hover:text-red-600 transition-colors"
                                                    title="Eliminar comentario"
                                                >
                                                    <TrashIcon className="h-4 w-4" />
                                                </button>
                                            </div>
                                        )}

                                        <span
                                            className="text-xs text-gray-400"
                                            title={new Date(comment.created_at).toLocaleString("es-ES", {
                                                day: "2-digit",
                                                month: "2-digit",
                                                year: "numeric",
                                                hour: "2-digit",
                                                minute: "2-digit"
                                            })}
                                        >
                                            {formatDate(comment.created_at)}
                                        </span>
                                    </div>
                                </div>

                                {/* Edit mode */}
                                {editingCommentId === comment.id ? (
                                    <div>
                                        <textarea
                                            value={editContent}
                                            onChange={(e) => setEditContent(e.target.value)}
                                            className="w-full rounded-md border-gray-300 shadow-sm focus:border-indigo-500 focus:ring-indigo-500 text-gray-600 mb-2"
                                            rows={4}
                                            minLength={10}
                                            maxLength={1000}
                                        />
                                        {editError && <InputError message={editError} className="mt-1 mb-2" />}
                                        <div className="flex justify-end space-x-2 mt-2">
                                            <button
                                                onClick={cancelEditing}
                                                className="px-3 py-1 text-sm bg-gray-100 hover:bg-gray-200 text-gray-700 rounded-md"
                                            >
                                                Cancelar
                                            </button>
                                            <PrimaryButton
                                                onClick={() => submitEdit(comment.id)}
                                                className="px-3 py-1 text-sm"
                                            >
                                                Guardar cambios
                                            </PrimaryButton>
                                        </div>
                                    </div>
                                ) : deleteConfirm === comment.id ? (
                                    <div className="bg-red-50 border border-red-200 rounded-lg p-3 mt-2">
                                        <p className="text-sm text-red-700">
                                            ¿Estás seguro de que deseas eliminar este comentario? Esta acción no se puede deshacer.
                                        </p>
                                        <div className="flex justify-end space-x-2 mt-2">
                                            <button
                                                onClick={cancelDelete}
                                                className="px-3 py-1 text-sm bg-gray-100 hover:bg-gray-200 text-gray-700 rounded-md"
                                            >
                                                Cancelar
                                            </button>
                                            <button
                                                onClick={() => deleteComment(comment.id)}
                                                className="px-3 py-1 text-sm bg-red-600 hover:bg-red-700 text-white rounded-md"
                                            >
                                                Eliminar
                                            </button>
                                        </div>
                                    </div>
                                ) : (
                                    <p className="text-gray-600 whitespace-pre-wrap">
                                        {comment.content}
                                    </p>
                                )}
                            </div>
                        </div>
                    </div>
                ))}

                {/* Infinite scroll loading indicator */}
                {loadingMore && (
                    <div className="flex justify-center py-4">
                        <div className="animate-spin rounded-full h-6 w-6 border-2 border-gray-300 border-t-indigo-600"></div>
                    </div>
                )}

                {/* Intersection observer target */}
                <div ref={observerTarget} className="h-4 w-full" />
            </div>
        </div>
    );
}
