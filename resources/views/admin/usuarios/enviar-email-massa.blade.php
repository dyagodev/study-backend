@extends('admin.modern-layout')

@section('title', 'Enviar E-mail em Massa')
@section('page-title', 'Enviar E-mail em Massa')

@section('content')
    <div class="mb-6">
        <a href="{{ route('admin.usuarios.index') }}" class="inline-flex items-center px-4 py-2 bg-gray-600 border border-transparent rounded-md font-semibold text-xs text-white uppercase tracking-widest hover:bg-gray-700">
            ← Voltar para Usuários
        </a>
    </div>

    <!-- Statistics Cards -->
    <div class="grid grid-cols-1 md:grid-cols-5 gap-4 mb-6">
        <div class="bg-white overflow-hidden shadow rounded-lg">
            <div class="px-4 py-5 sm:p-6">
                <dt class="text-sm font-medium text-gray-500 truncate">Total de Usuários</dt>
                <dd class="mt-1 text-3xl font-semibold text-gray-900">{{ number_format($stats['total_usuarios'], 0, ',', '.') }}</dd>
            </div>
        </div>

        <div class="bg-white overflow-hidden shadow rounded-lg">
            <div class="px-4 py-5 sm:p-6">
                <dt class="text-sm font-medium text-gray-500 truncate">Alunos</dt>
                <dd class="mt-1 text-3xl font-semibold text-green-600">{{ number_format($stats['total_alunos'], 0, ',', '.') }}</dd>
            </div>
        </div>

        <div class="bg-white overflow-hidden shadow rounded-lg">
            <div class="px-4 py-5 sm:p-6">
                <dt class="text-sm font-medium text-gray-500 truncate">Professores</dt>
                <dd class="mt-1 text-3xl font-semibold text-blue-600">{{ number_format($stats['total_professores'], 0, ',', '.') }}</dd>
            </div>
        </div>

        <div class="bg-white overflow-hidden shadow rounded-lg">
            <div class="px-4 py-5 sm:p-6">
                <dt class="text-sm font-medium text-gray-500 truncate">Admins</dt>
                <dd class="mt-1 text-3xl font-semibold text-purple-600">{{ number_format($stats['total_admins'], 0, ',', '.') }}</dd>
            </div>
        </div>

        <div class="bg-white overflow-hidden shadow rounded-lg">
            <div class="px-4 py-5 sm:p-6">
                <dt class="text-sm font-medium text-gray-500 truncate">Ativos (30d)</dt>
                <dd class="mt-1 text-3xl font-semibold text-indigo-600">{{ number_format($stats['usuarios_ativos'], 0, ',', '.') }}</dd>
            </div>
        </div>
    </div>

    <!-- Email Form -->
    <div class="bg-white shadow rounded-lg">
        <div class="px-6 py-4 border-b border-gray-200">
            <h3 class="text-lg font-medium text-gray-900">Compor E-mail em Massa</h3>
            <p class="mt-1 text-sm text-gray-500">Envie uma mensagem para múltiplos usuários de uma vez</p>
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

            <form method="POST" action="{{ route('admin.usuarios.enviar-email-massa') }}" class="space-y-6" id="emailForm">
                @csrf

                <div>
                    <label for="destinatarios" class="block text-sm font-medium text-gray-700">Destinatários *</label>
                    <select name="destinatarios" id="destinatarios" required
                            class="mt-1 block w-full rounded-md border-gray-300 shadow-sm focus:border-indigo-500 focus:ring-indigo-500 sm:text-sm @error('destinatarios') border-red-500 @enderror">
                        <option value="">Selecione o grupo de destinatários</option>
                        <option value="todos" {{ old('destinatarios') == 'todos' ? 'selected' : '' }}>
                            Todos os Usuários ({{ number_format($stats['total_usuarios'], 0, ',', '.') }})
                        </option>
                        <option value="alunos" {{ old('destinatarios') == 'alunos' ? 'selected' : '' }}>
                            Apenas Alunos ({{ number_format($stats['total_alunos'], 0, ',', '.') }})
                        </option>
                        <option value="professores" {{ old('destinatarios') == 'professores' ? 'selected' : '' }}>
                            Apenas Professores ({{ number_format($stats['total_professores'], 0, ',', '.') }})
                        </option>
                        <option value="admins" {{ old('destinatarios') == 'admins' ? 'selected' : '' }}>
                            Apenas Administradores ({{ number_format($stats['total_admins'], 0, ',', '.') }})
                        </option>
                        <option value="ativos" {{ old('destinatarios') == 'ativos' ? 'selected' : '' }}>
                            Usuários Ativos nos últimos 30 dias ({{ number_format($stats['usuarios_ativos'], 0, ',', '.') }})
                        </option>
                    </select>
                    @error('destinatarios')
                        <p class="mt-1 text-sm text-red-600">{{ $message }}</p>
                    @enderror
                    <p class="mt-2 text-sm text-gray-500">Selecione o grupo de usuários que receberá o e-mail.</p>
                </div>

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
                    <textarea name="mensagem" id="mensagem" rows="12" required
                              class="mt-1 block w-full rounded-md border-gray-300 shadow-sm focus:border-indigo-500 focus:ring-indigo-500 sm:text-sm @error('mensagem') border-red-500 @enderror"
                              placeholder="Digite a mensagem do e-mail...">{{ old('mensagem') }}</textarea>
                    @error('mensagem')
                        <p class="mt-1 text-sm text-red-600">{{ $message }}</p>
                    @enderror
                    <p class="mt-2 text-sm text-gray-500">A mensagem será personalizada com o nome de cada usuário e incluirá uma assinatura automática da plataforma.</p>
                </div>

                <div class="bg-red-50 border-l-4 border-red-400 p-4">
                    <div class="flex">
                        <div class="flex-shrink-0">
                            <svg class="h-5 w-5 text-red-400" viewBox="0 0 20 20" fill="currentColor">
                                <path fill-rule="evenodd" d="M10 18a8 8 0 100-16 8 8 0 000 16zM8.707 7.293a1 1 0 00-1.414 1.414L8.586 10l-1.293 1.293a1 1 0 101.414 1.414L10 11.414l1.293 1.293a1 1 0 001.414-1.414L11.414 10l1.293-1.293a1 1 0 00-1.414-1.414L10 8.586 8.707 7.293z" clip-rule="evenodd" />
                            </svg>
                        </div>
                        <div class="ml-3">
                            <p class="text-sm text-red-700">
                                <strong>⚠️ ATENÇÃO:</strong> Você está prestes a enviar um e-mail em massa. Esta ação não pode ser desfeita e pode levar alguns minutos dependendo da quantidade de destinatários. Certifique-se de que o conteúdo está correto!
                            </p>
                        </div>
                    </div>
                </div>

                <div class="flex justify-end space-x-3">
                    <a href="{{ route('admin.usuarios.index') }}"
                       class="inline-flex items-center px-4 py-2 bg-gray-300 border border-transparent rounded-md font-semibold text-xs text-gray-700 uppercase tracking-widest hover:bg-gray-400">
                        Cancelar
                    </a>
                    <button type="submit"
                            class="inline-flex items-center px-6 py-3 bg-indigo-600 border border-transparent rounded-md font-semibold text-xs text-white uppercase tracking-widest hover:bg-indigo-700"
                            onclick="return confirm('Tem certeza que deseja enviar este e-mail em massa? Esta ação não pode ser desfeita.')">
                        <svg class="w-4 h-4 mr-2" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M3 8l7.89 5.26a2 2 0 002.22 0L21 8M5 19h14a2 2 0 002-2V7a2 2 0 00-2-2H5a2 2 0 00-2 2v10a2 2 0 002 2z"></path>
                        </svg>
                        Enviar E-mails
                    </button>
                </div>
            </form>
        </div>
    </div>

    <!-- Preview Section -->
    <div class="bg-white shadow rounded-lg mt-6">
        <div class="px-6 py-4 border-b border-gray-200">
            <h3 class="text-lg font-medium text-gray-900">💡 Dicas para E-mails em Massa</h3>
        </div>
        <div class="px-6 py-4">
            <ul class="list-disc list-inside space-y-2 text-sm text-gray-600">
                <li>Seja claro e objetivo na mensagem</li>
                <li>Evite usar palavras que possam ser identificadas como spam</li>
                <li>Personalize a mensagem quando possível</li>
                <li>Inclua um motivo claro para o e-mail</li>
                <li>Considere o horário de envio (evite horários inadequados)</li>
                <li>Revise a ortografia e gramática antes de enviar</li>
                <li>O nome de cada usuário será inserido automaticamente na saudação</li>
            </ul>
        </div>
    </div>
@endsection
