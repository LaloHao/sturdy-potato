import { useState } from 'react';
import AuthenticatedLayout from '@/Layouts/AuthenticatedLayout';
import { Head, useForm, router } from '@inertiajs/react';
import InputLabel from '@/Components/InputLabel';
import TextInput from '@/Components/TextInput';
import PrimaryButton from '@/Components/PrimaryButton';
import { PlusIcon, TrashIcon } from '@heroicons/react/24/outline';
import axios from 'axios';

export default function Create() {
    const { data, setData, processing, errors, reset } = useForm({
        title: '',
        context: '',
        type: 'life',
        is_anonymous: false,
        expires_at: '',
        options: ['', '']
    });

    const handleSubmit = async (e) => {
        e.preventDefault();

        try {
            const response = await axios.post('/api/decisions', data);
            router.visit(`/decisions/${response.data.decision.id}`);
        } catch (error) {
            console.error('Error creating decision:', error);
            if (error.response && error.response.data) {
                console.error('Error:', error.response.data);
            }
        }
    };

    const addOption = () => {
        if (data.options.length < 4) {
            setData('options', [...data.options, '']);
        }
    };

    const removeOption = (index) => {
        if (data.options.length > 2) {
            const newOptions = data.options.filter((_, i) => i !== index);
            setData('options', newOptions);
        }
    };

    const updateOption = (index, value) => {
        const newOptions = [...data.options];
        newOptions[index] = value;
        setData('options', newOptions);
    };

    return (
        <AuthenticatedLayout
            header={
                <h2 className="text-xl font-semibold leading-tight text-gray-800">
                    Crear Nueva Decisión
                </h2>
            }
        >
            <Head title="Crear Decisión" />

            <div className="py-12">
                <div className="mx-auto max-w-3xl sm:px-6 lg:px-8">
                    <div className="bg-white overflow-hidden shadow-sm sm:rounded-lg">
                        <div className="p-6">
                            <form onSubmit={handleSubmit} className="space-y-6">
                                <div>
                                    <InputLabel htmlFor="title" value="Título de la decisión" />
                                    <TextInput
                                        id="title"
                                        type="text"
                                        className="mt-1 block w-full"
                                        value={data.title}
                                        onChange={(e) => setData('title', e.target.value)}
                                        placeholder="¿Debería cambiar de trabajo?"
                                        required
                                    />
                                </div>

                                <div>
                                    <InputLabel htmlFor="context" value="Contexto" />
                                    <textarea
                                        id="context"
                                        className="mt-1 block w-full rounded-md border-gray-300 shadow-sm focus:border-indigo-500 focus:ring-indigo-500"
                                        rows="4"
                                        value={data.context}
                                        onChange={(e) => setData('context', e.target.value)}
                                        placeholder="Explica tu situación y por qué necesitas ayuda para decidir..."
                                        required
                                    />
                                </div>

                                <div className="grid grid-cols-1 md:grid-cols-2 gap-4">
                                    <div>
                                        <InputLabel htmlFor="type" value="Tipo de decisión" />
                                        <select
                                            id="type"
                                            className="mt-1 block w-full rounded-md border-gray-300 shadow-sm focus:border-indigo-500 focus:ring-indigo-500"
                                            value={data.type}
                                            onChange={(e) => setData('type', e.target.value)}
                                        >
                                            <option value="career">Carrera</option>
                                            <option value="technical">Técnica</option>
                                            <option value="life">Vida</option>
                                            <option value="financial">Financiera</option>
                                            <option value="startup">Startup</option>
                                        </select>
                                    </div>

                                    <div>
                                        <InputLabel htmlFor="expires_at" value="Fecha de expiración" />
                                        <TextInput
                                            id="expires_at"
                                            type="datetime-local"
                                            className="mt-1 block w-full"
                                            value={data.expires_at}
                                            onChange={(e) => setData('expires_at', e.target.value)}
                                            min={new Date().toISOString().slice(0, 16)}
                                            required
                                        />
                                    </div>
                                </div>

                                <div>
                                    <div className="flex justify-between items-center mb-2">
                                        <InputLabel value="Opciones" />
                                        {data.options.length < 4 && (
                                            <button
                                                type="button"
                                                onClick={addOption}
                                                className="text-sm text-indigo-600 hover:text-indigo-500 flex items-center"
                                            >
                                                <PlusIcon className="w-4 h-4 mr-1" />
                                                Agregar opción
                                            </button>
                                        )}
                                    </div>
                                    <div className="space-y-2">
                                        {data.options.map((option, index) => (
                                            <div key={index} className="flex gap-2">
                                                <TextInput
                                                    type="text"
                                                    className="flex-1"
                                                    value={option}
                                                    onChange={(e) => updateOption(index, e.target.value)}
                                                    placeholder={`Opción ${index + 1}`}
                                                    required
                                                />
                                                {data.options.length > 2 && (
                                                    <button
                                                        type="button"
                                                        onClick={() => removeOption(index)}
                                                        className="text-red-600 hover:text-red-500 p-2"
                                                    >
                                                        <TrashIcon className="w-5 h-5" />
                                                    </button>
                                                )}
                                            </div>
                                        ))}
                                    </div>
                                </div>

                                <div className="flex items-center">
                                    <input
                                        id="is_anonymous"
                                        type="checkbox"
                                        className="rounded border-gray-300 text-indigo-600 shadow-sm focus:ring-indigo-500"
                                        checked={data.is_anonymous}
                                        onChange={(e) => setData('is_anonymous', e.target.checked)}
                                    />
                                    <label htmlFor="is_anonymous" className="ml-2 text-sm text-gray-600">
                                        Publicar de forma anónima
                                    </label>
                                </div>

                                <div className="flex justify-end space-x-2">
                                    <button
                                        type="button"
                                        onClick={() => router.visit('/decisions')}
                                        className="px-4 py-2 bg-gray-200 text-gray-800 rounded-md hover:bg-gray-300"
                                    >
                                        Cancelar
                                    </button>
                                    <PrimaryButton disabled={processing}>
                                        Crear Decisión
                                    </PrimaryButton>
                                </div>
                            </form>
                        </div>
                    </div>
                </div>
            </div>
        </AuthenticatedLayout>
    );
}