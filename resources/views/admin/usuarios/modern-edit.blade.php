@extends('admin.modern-layout')

@section('title', 'Editar Usuário')
@section('page-title', 'Editar Usuário')

@section('content')
    <!-- Back Button -->
    <div class="mb-6">
        <a href="{{ route('admin.usuarios.show', $usuario->id) }}" class="inline-flex items-center px-4 py-2 border border-gray-300 shadow-sm text-sm font-medium rounded-md text-gray-700 bg-white hover:bg-gray-50 focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-indigo-500">
            <svg class="mr-2 h-5 w-5" fill="none" viewBox="0 0 24 24" stroke-width="1.5" stroke="currentColor">
                <path stroke-linecap="round" stroke-linejoin="round" d="M10.5 19.5L3 12m0 0l7.5-7.5M3 12h18" />
            </svg>
            Voltar
        </a>
    </div>

    <!-- Edit Form -->
    <div class="bg-white shadow rounded-lg">
        <div class="px-4 py-5 sm:px-6 border-b border-gray-200">
            <h3 class="text-lg leading-6 font-medium text-gray-900">Editar Dados do Usuário</h3>
            <p class="mt-1 text-sm text-gray-500">Atualize as informações do usuário {{ $usuario->name }}</p>
        </div>
        
        <form method="POST" action="{{ route('admin.usuarios.update', $usuario->id) }}" class="px-4 py-5 sm:p-6">
            @csrf
            @method('PUT')

            <div class="space-y-6 max-w-3xl">
                <!-- Name -->
                <div>
                    <label for="name" class="block text-sm font-medium text-gray-700">Nome *</label>
                    <input type="text" name="name" id="name" value="{{ old('name', $usuario->name) }}" required class="mt-1 block w-full rounded-md border-gray-300 shadow-sm focus:border-indigo-500 focus:ring-indigo-500 sm:text-sm @error('name') border-red-300 @enderror">
                    @error('name')
                        <p class="mt-2 text-sm text-red-600">{{ $message }}</p>
                    @enderror
                </div>

                <!-- Email -->
                <div>
                    <label for="email" class="block text-sm font-medium text-gray-700">Email *</label>
                    <input type="email" name="email" id="email" value="{{ old('email', $usuario->email) }}" required class="mt-1 block w-full rounded-md border-gray-300 shadow-sm focus:border-indigo-500 focus:ring-indigo-500 sm:text-sm @error('email') border-red-300 @enderror">
                    @error('email')
                        <p class="mt-2 text-sm text-red-600">{{ $message }}</p>
                    @enderror
                </div>

                <!-- Role -->
                <div>
                    <label for="role" class="block text-sm font-medium text-gray-700">Role *</label>
                    <select name="role" id="role" required class="mt-1 block w-full rounded-md border-gray-300 shadow-sm focus:border-indigo-500 focus:ring-indigo-500 sm:text-sm @error('role') border-red-300 @enderror">
                        <option value="aluno" {{ old('role', $usuario->role) === 'aluno' ? 'selected' : '' }}>Aluno</option>
                        <option value="professor" {{ old('role', $usuario->role) === 'professor' ? 'selected' : '' }}>Professor</option>
                        <option value="admin" {{ old('role', $usuario->role) === 'admin' ? 'selected' : '' }}>Admin</option>
                    </select>
                    @error('role')
                        <p class="mt-2 text-sm text-red-600">{{ $message }}</p>
                    @enderror
                </div>

                <!-- Créditos Atuais -->
                <div>
                    <label for="creditos" class="block text-sm font-medium text-gray-700">Créditos Atuais *</label>
                    <input type="number" name="creditos" id="creditos" value="{{ old('creditos', $usuario->creditos) }}" min="0" required class="mt-1 block w-full rounded-md border-gray-300 shadow-sm focus:border-indigo-500 focus:ring-indigo-500 sm:text-sm @error('creditos') border-red-300 @enderror">
                    @error('creditos')
                        <p class="mt-2 text-sm text-red-600">{{ $message }}</p>
                    @enderror
                </div>

                <!-- Créditos Semanais -->
                <div>
                    <label for="creditos_semanais" class="block text-sm font-medium text-gray-700">Créditos Semanais *</label>
                    <input type="number" name="creditos_semanais" id="creditos_semanais" value="{{ old('creditos_semanais', $usuario->creditos_semanais) }}" min="0" required class="mt-1 block w-full rounded-md border-gray-300 shadow-sm focus:border-indigo-500 focus:ring-indigo-500 sm:text-sm @error('creditos_semanais') border-red-300 @enderror">
                    <p class="mt-2 text-sm text-gray-500">Quantidade de créditos que o usuário recebe a cada 7 dias</p>
                    @error('creditos_semanais')
                        <p class="mt-2 text-sm text-red-600">{{ $message }}</p>
                    @enderror
                </div>

                <!-- Buttons -->
                <div class="flex gap-3 pt-5">
                    <button type="submit" class="inline-flex justify-center items-center px-6 py-3 border border-transparent shadow-sm text-base font-medium rounded-md text-white bg-indigo-600 hover:bg-indigo-700 focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-indigo-500">
                        <svg class="mr-2 h-5 w-5" fill="none" viewBox="0 0 24 24" stroke-width="1.5" stroke="currentColor">
                            <path stroke-linecap="round" stroke-linejoin="round" d="M16.5 3.75V16.5L12 14.25 7.5 16.5V3.75m9 0H18A2.25 2.25 0 0120.25 6v12A2.25 2.25 0 0118 20.25H6A2.25 2.25 0 013.75 18V6A2.25 2.25 0 016 3.75h1.5m9 0h-9" />
                        </svg>
                        Salvar Alterações
                    </button>
                    <a href="{{ route('admin.usuarios.show', $usuario->id) }}" class="inline-flex justify-center items-center px-6 py-3 border border-gray-300 shadow-sm text-base font-medium rounded-md text-gray-700 bg-white hover:bg-gray-50 focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-indigo-500">
                        Cancelar
                    </a>
                </div>
            </div>
        </form>
    </div>
@endsection
