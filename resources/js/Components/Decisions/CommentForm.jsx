import { useState } from "react";
import PrimaryButton from "@/Components/PrimaryButton";
import InputError from "@/Components/InputError";
import axios from "axios";

export default function CommentForm({ decisionId, onCommentAdded }) {
  const [content, setContent] = useState("");
  const [submitting, setSubmitting] = useState(false);
  const [error, setError] = useState("");

  const handleSubmit = async (e) => {
    e.preventDefault();

    if (content.trim().length < 10) {
      setError("El comentario debe tener al menos 10 caracteres.");
      return;
    }

    setSubmitting(true);
    setError("");

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
          rows="3"
          value={content}
          onChange={(e) => setContent(e.target.value)}
          className="w-full rounded-md border-gray-300 shadow-sm focus:border-indigo-500 focus:ring-indigo-500"
          placeholder="¿Qué opinas sobre esta decisión?"
          disabled={submitting}
          minLength={10}
          maxLength={1000}
        />
        <p className="mt-1 text-xs text-gray-500">
          {content.length}/1000 caracteres (mínimo 10)
        </p>
        {error && <InputError message={error} className="mt-1" />}
      </div>
      <div className="flex justify-end">
        <PrimaryButton
          type="submit"
          className="px-4 py-2"
          disabled={submitting || content.trim().length < 10}
        >
          {submitting ? "Enviando..." : "Enviar comentario"}
        </PrimaryButton>
      </div>
    </form>
  );
}
