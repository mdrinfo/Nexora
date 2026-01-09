@extends('layouts.admin')

@section('content')
    <div class="max-w-2xl mx-auto">
        <div class="flex items-center gap-2 mb-6 text-sm text-gray-500">
            <a href="{{ route('admin.suppliers.index') }}" class="hover:underline">Fournisseurs</a>
            <span>/</span>
            <span class="text-gray-900">Modifier</span>
        </div>

        <div class="bg-white border border-gray-200 rounded-lg p-6 shadow-sm">
            <h2 class="text-xl font-semibold text-gray-800 mb-6">Modifier: {{ $supplier->name }}</h2>

            <form action="{{ url('/admin/suppliers/' . $supplier->id) }}" method="POST">
                @csrf
                @method('PUT')
                
                <div class="mb-4">
                    <label class="block text-sm font-medium text-gray-700 mb-1">Nom de l'entreprise</label>
                    <input type="text" name="name" value="{{ old('name', $supplier->name) }}" required
                           class="w-full rounded-md border-gray-300 shadow-sm focus:border-blue-500 focus:ring-blue-500 sm:text-sm border p-2 text-gray-900">
                </div>

                <div class="mb-4">
                    <label class="block text-sm font-medium text-gray-700 mb-1">Nom du Contact</label>
                    <input type="text" name="contact_name" value="{{ old('contact_name', $supplier->contact_name) }}"
                           class="w-full rounded-md border-gray-300 shadow-sm focus:border-blue-500 focus:ring-blue-500 sm:text-sm border p-2 text-gray-900">
                </div>

                <div class="grid grid-cols-1 md:grid-cols-2 gap-4 mb-6">
                    <div>
                        <label class="block text-sm font-medium text-gray-700 mb-1">Téléphone</label>
                        <input type="text" name="phone" value="{{ old('phone', $supplier->phone) }}"
                               class="w-full rounded-md border-gray-300 shadow-sm focus:border-blue-500 focus:ring-blue-500 sm:text-sm border p-2 text-gray-900">
                    </div>

                    <div>
                        <label class="block text-sm font-medium text-gray-700 mb-1">Email</label>
                        <input type="email" name="email" value="{{ old('email', $supplier->email) }}"
                               class="w-full rounded-md border-gray-300 shadow-sm focus:border-blue-500 focus:ring-blue-500 sm:text-sm border p-2 text-gray-900">
                    </div>
                </div>

                <div class="flex justify-between items-center pt-4 border-t border-gray-100">
                    <button type="button" onclick="if(confirm('Supprimer ce fournisseur ?')) document.getElementById('delete-form').submit();" class="text-red-600 hover:text-red-800 text-sm font-medium">
                        Supprimer
                    </button>

                    <div class="flex gap-3">
                        <a href="{{ route('admin.suppliers.index') }}" class="px-4 py-2 text-sm font-medium text-gray-700 bg-white border border-gray-300 rounded-md hover:bg-gray-50">
                            Annuler
                        </a>
                        <button type="submit" class="px-4 py-2 text-sm font-medium text-white bg-blue-600 rounded-md hover:bg-blue-700">
                            Enregistrer
                        </button>
                    </div>
                </div>
            </form>

            <form id="delete-form" action="{{ url('/admin/suppliers/' . $supplier->id) }}" method="POST" class="hidden">
                @csrf
                @method('DELETE')
            </form>
        </div>
    </div>
@endsection
