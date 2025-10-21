import { useState, useRef, useEffect } from "react";
import PrimaryButton from "@/Components/PrimaryButton";
import InputError from "@/Components/InputError";
import axios from "axios";
import { ExclamationTriangleIcon } from "@heroicons/react/24/outline";

export default function CommentForm({ decisionId, onCommentAdded }) {
  const [content, setContent] = useState("");
  const [submitting, setSubmitting] = useState(false);
  const [error, setError] = useState("");
  const [showConfirmation, setShowConfirmation] = useState(false);
  const textareaRef = useRef(null);

  // Auto-adjust textarea height as content changes
  useEffect(() => {
    if (textareaRef.current) {
      textareaRef.current.style.height = "auto";
      textareaRef.current.style.height =
        Math.max(80, textareaRef.current.scrollHeight) + "px";
    }
  }, [content]);

  const handleSubmit = async (e) => {
    e.preventDefault();

    if (content.trim().length < 10) {
      setError("El comentario debe tener al menos 10 caracteres.");
      return;
    }

    // Instead of submitting directly, show confirmation first
    if (!showConfirmation) {
      setShowConfirmation(true);
      return;
    }

    setSubmitting(true);
    setError("");
    setShowConfirmation(false);

    try {
      const response = await axios.post(
        `/api/decisions/${decisionId}/comments`,
        { content }
      );

      setContent("");
      onCommentAdded && onCommentAdded(response.data);
    } catch (error) {
      console.error("Error submitting comment:", error);
      if (error.response && error.response.data) {
        setError(
          error.response.data.message ||
          "Error al enviar el comentario."
        );
      } else {
        setError("Ha ocurrido un error al enviar el comentario.");
      }
    } finally {
      setSubmitting(false);
    }
  };

  return (
    <form onSubmit={handleSubmit} className="mt-4">
      <div className="mb-3">
        <label
          htmlFor="comment"
          className="block text-sm font-medium text-gray-700 mb-1"
        >
          Añadir un comentario
        </label>
        <textarea
          id="comment"
          ref={textareaRef}
          value={content}
          onChange={(e) => setContent(e.target.value)}
          className="w-full rounded-md border-gray-300 shadow-sm focus:border-indigo-500 focus:ring-indigo-500 resize-none overflow-hidden min-h-[80px]"
          placeholder="¿Qué opinas sobre esta decisión?"
          disabled={submitting}
          minLength={10}
          maxLength={1000}
          style={{ height: "80px" }}
        />
        <p className="mt-1 text-xs text-gray-500">
          {content.length}/1000 caracteres (mínimo 10)
        </p>
        {error && <InputError message={error} className="mt-1" />}
      </div>

      {showConfirmation && (
        <div className="mb-4 bg-amber-50 border border-amber-200 rounded-lg p-4 flex items-start">
          <ExclamationTriangleIcon className="h-5 w-5 text-amber-600 mr-3 flex-shrink-0 mt-0.5" />
          <div>
            <h3 className="text-sm font-medium text-amber-800">
              ¿Estás seguro de publicar este comentario?
            </h3>
            <p className="text-xs text-amber-700 mt-1">
              Tu comentario será visible para todos los usuarios y
              no podrás editarlo posteriormente.
            </p>
            <div className="mt-3 flex space-x-3">
              <button
                type="button"
                className="text-sm px-3 py-1.5 bg-gray-100 hover:bg-gray-200 text-gray-700 rounded-md"
                onClick={() => setShowConfirmation(false)}
              >
                Cancelar
              </button>
              <button
                type="submit"
                className="text-sm px-3 py-1.5 bg-amber-100 hover:bg-amber-200 text-amber-800 rounded-md"
              >
                Sí, publicar comentario
              </button>
            </div>
          </div>
        </div>
      )}

      {!showConfirmation && (
        <div className="flex justify-end">
          <PrimaryButton
            type="submit"
            className="px-4 py-2"
            disabled={submitting || content.trim().length < 10}
          >
            {submitting ? "Enviando..." : "Enviar comentario"}
          </PrimaryButton>
        </div>
      )}
    </form>
  );
}
