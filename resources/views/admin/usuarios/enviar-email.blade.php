@extends('admin.modern-layout')

@section('title', 'Enviar E-mail')
@section('page-title', 'Enviar E-mail para ' . $usuario->name)

@section('content')
    <div class="mb-6">
        <a href="{{ route('admin.usuarios.show', $usuario->id) }}" class="inline-flex items-center px-4 py-2 bg-gray-600 border border-transparent rounded-md font-semibold text-xs text-white uppercase tracking-widest hover:bg-gray-700">
            ← Voltar para Usuário
        </a>
    </div>

    <!-- User Info Card -->
    <div class="bg-white shadow rounded-lg mb-6">
        <div class="px-6 py-4 border-b border-gray-200">
            <h3 class="text-lg font-medium text-gray-900">Informações do Destinatário</h3>
        </div>
        <div class="px-6 py-4">
            <div class="grid grid-cols-1 md:grid-cols-3 gap-4">
                <div>
                    <p class="text-sm font-medium text-gray-500">Nome</p>
                    <p class="text-sm text-gray-900">{{ $usuario->name }}</p>
                </div>
                <div>
                    <p class="text-sm font-medium text-gray-500">E-mail</p>
                    <p class="text-sm text-gray-900">{{ $usuario->email }}</p>
                </div>
                <div>
                    <p class="text-sm font-medium text-gray-500">Role</p>
                    <p class="text-sm text-gray-900">
                        @if($usuario->role === 'admin')
                            <span class="inline-flex items-center px-2.5 py-0.5 rounded-full text-xs font-medium bg-purple-100 text-purple-800">
                                Admin
                            </span>
                        @elseif($usuario->role === 'professor')
                            <span class="inline-flex items-center px-2.5 py-0.5 rounded-full text-xs font-medium bg-blue-100 text-blue-800">
                                Professor
                            </span>
                        @else
                            <span class="inline-flex items-center px-2.5 py-0.5 rounded-full text-xs font-medium bg-green-100 text-green-800">
                                Aluno
                            </span>
                        @endif
                    </p>
                </div>
            </div>
        </div>
    </div>

    <!-- Email Form -->
    <div class="bg-white shadow rounded-lg">
        <div class="px-6 py-4 border-b border-gray-200">
            <h3 class="text-lg font-medium text-gray-900">Compor E-mail</h3>
            <p class="mt-1 text-sm text-gray-500">Envie uma mensagem personalizada para este usuário</p>
        </div>
        <div class="px-6 py-4">
            @if(session('success'))
                <div class="mb-4 p-4 bg-green-100 border border-green-400 text-green-700 rounded-lg flex items-center">
                    <svg class="w-5 h-5 mr-2" fill="currentColor" viewBox="0 0 20 20">
                        <path fill-rule="evenodd" d="M10 18a8 8 0 100-16 8 8 0 000 16zm3.707-9.293a1 1 0 00-1.414-1.414L9 10.586 7.707 9.293a1 1 0 00-1.414 1.414l2 2a1 1 0 001.414 0l4-4z" clip-rule="evenodd"></path>
                    </svg>
                    {{ session('success') }}
                </div>
            @endif

            @if(session('error'))
                <div class="mb-4 p-4 bg-red-100 border border-red-400 text-red-700 rounded-lg flex items-center">
                    <svg class="w-5 h-5 mr-2" fill="currentColor" viewBox="0 0 20 20">
                        <path fill-rule="evenodd" d="M10 18a8 8 0 100-16 8 8 0 000 16zM8.707 7.293a1 1 0 00-1.414 1.414L8.586 10l-1.293 1.293a1 1 0 101.414 1.414L10 11.414l1.293 1.293a1 1 0 001.414-1.414L11.414 10l1.293-1.293a1 1 0 00-1.414-1.414L10 8.586 8.707 7.293z" clip-rule="evenodd"></path>
                    </svg>
                    {{ session('error') }}
                </div>
            @endif

            <form method="POST" action="{{ route('admin.usuarios.enviar-email', $usuario->id) }}" class="space-y-6">
                @csrf

                <div>
                    <label for="assunto" class="block text-sm font-medium text-gray-700">Assunto *</label>
                    <input type="text" name="assunto" id="assunto" required
                           class="mt-1 block w-full rounded-md border-gray-300 shadow-sm focus:border-indigo-500 focus:ring-indigo-500 sm:text-sm @error('assunto') border-red-500 @enderror"
                           placeholder="Digite o assunto do e-mail..."
                           value="{{ old('assunto') }}">
                    @error('assunto')
                        <p class="mt-1 text-sm text-red-600">{{ $message }}</p>
                    @enderror
                </div>

                <div>
                    <label for="mensagem" class="block text-sm font-medium text-gray-700">Mensagem *</label>
                    <textarea name="mensagem" id="mensagem" rows="10" required
                              class="mt-1 block w-full rounded-md border-gray-300 shadow-sm focus:border-indigo-500 focus:ring-indigo-500 sm:text-sm @error('mensagem') border-red-500 @enderror"
                              placeholder="Digite a mensagem do e-mail...">{{ old('mensagem') }}</textarea>
                    @error('mensagem')
                        <p class="mt-1 text-sm text-red-600">{{ $message }}</p>
                    @enderror
                    <p class="mt-2 text-sm text-gray-500">A mensagem será enviada com o nome do usuário e uma assinatura automática da plataforma.</p>
                </div>

                <div class="bg-yellow-50 border-l-4 border-yellow-400 p-4">
                    <div class="flex">
                        <div class="flex-shrink-0">
                            <svg class="h-5 w-5 text-yellow-400" viewBox="0 0 20 20" fill="currentColor">
                                <path fill-rule="evenodd" d="M8.257 3.099c.765-1.36 2.722-1.36 3.486 0l5.58 9.92c.75 1.334-.213 2.98-1.742 2.98H4.42c-1.53 0-2.493-1.646-1.743-2.98l5.58-9.92zM11 13a1 1 0 11-2 0 1 1 0 012 0zm-1-8a1 1 0 00-1 1v3a1 1 0 002 0V6a1 1 0 00-1-1z" clip-rule="evenodd" />
                            </svg>
                        </div>
                        <div class="ml-3">
                            <p class="text-sm text-yellow-700">
                                <strong>Atenção:</strong> Certifique-se de que o conteúdo do e-mail está correto antes de enviar. Esta ação não pode ser desfeita.
                            </p>
                        </div>
                    </div>
                </div>

                <div class="flex justify-end space-x-3">
                    <a href="{{ route('admin.usuarios.show', $usuario->id) }}"
                       class="inline-flex items-center px-4 py-2 bg-gray-300 border border-transparent rounded-md font-semibold text-xs text-gray-700 uppercase tracking-widest hover:bg-gray-400">
                        Cancelar
                    </a>
                    <button type="submit"
                            class="inline-flex items-center px-4 py-2 bg-indigo-600 border border-transparent rounded-md font-semibold text-xs text-white uppercase tracking-widest hover:bg-indigo-700">
                        <svg class="w-4 h-4 mr-2" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M3 8l7.89 5.26a2 2 0 002.22 0L21 8M5 19h14a2 2 0 002-2V7a2 2 0 00-2-2H5a2 2 0 00-2 2v10a2 2 0 002 2z"></path>
                        </svg>
                        Enviar E-mail
                    </button>
                </div>
            </form>
        </div>
    </div>
@endsection
