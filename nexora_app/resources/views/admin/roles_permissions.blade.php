@extends('layouts.admin')

@section('content')
    <div class="bg-white dark:bg-gray-800 border border-gray-200 dark:border-gray-700 rounded-lg p-6">
        <h3 class="text-lg font-semibold mb-4 text-gray-800 dark:text-white">Rôles & Permissions</h3>
        @foreach($roles as $role)
            <div class="border border-gray-200 dark:border-gray-700 rounded p-4 mb-4">
                <strong class="text-gray-900 dark:text-white">{{ $role->name }}</strong>
                <form method="post" action="{{ route('admin.roles.permissions.sync', $role) }}">
                    @csrf
                    <div class="flex flex-wrap gap-3 mt-3">
                        @foreach($permissions as $perm)
                            <label class="flex items-center gap-2 text-gray-700 dark:text-gray-300">
                                <input type="checkbox" name="permission_ids[]" value="{{ $perm->id }}" {{ $role->permissions->contains('id', $perm->id) ? 'checked' : '' }} class="rounded border-gray-300 dark:border-gray-600 text-blue-600 focus:ring-blue-500 bg-white dark:bg-gray-700">
                                <span>{{ $perm->name }}</span>
                            </label>
                        @endforeach
                    </div>
                    <button type="submit" class="mt-3 bg-gray-900 dark:bg-blue-600 text-white px-3 py-1 rounded hover:bg-gray-800 dark:hover:bg-blue-700">Enregistrer</button>
                </form>
            </div>
        @endforeach
    </div>
@endsection
