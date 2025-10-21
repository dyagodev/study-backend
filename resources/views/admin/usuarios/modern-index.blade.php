@extends('admin.modern-layout')

@section('title', 'Usuários')
@section('page-title', 'Gerenciar Usuários')

@section('content')
    <!-- Filters -->
    <div class="bg-white shadow rounded-lg mb-6">
        <div class="px-4 py-5 sm:p-6">
            <form method="GET" action="{{ route('admin.usuarios.index') }}" class="space-y-4">
                <div class="grid grid-cols-1 gap-4 sm:grid-cols-4">
                    <div>
                        <label for="search" class="block text-sm font-medium text-gray-700">Buscar</label>
                        <input type="text" name="search" id="search" class="mt-1 block w-full rounded-md border-gray-300 shadow-sm focus:border-indigo-500 focus:ring-indigo-500 sm:text-sm" placeholder="Nome ou email..." value="{{ request('search') }}">
                    </div>

                    <div>
                        <label for="role" class="block text-sm font-medium text-gray-700">Role</label>
                        <select name="role" id="role" class="mt-1 block w-full rounded-md border-gray-300 shadow-sm focus:border-indigo-500 focus:ring-indigo-500 sm:text-sm">
                            <option value="">Todos</option>
                            <option value="aluno" {{ request('role') === 'aluno' ? 'selected' : '' }}>Aluno</option>
                            <option value="professor" {{ request('role') === 'professor' ? 'selected' : '' }}>Professor</option>
                            <option value="admin" {{ request('role') === 'admin' ? 'selected' : '' }}>Admin</option>
                        </select>
                    </div>

                    <div>
                        <label for="status" class="block text-sm font-medium text-gray-700">Status</label>
                        <select name="status" id="status" class="mt-1 block w-full rounded-md border-gray-300 shadow-sm focus:border-indigo-500 focus:ring-indigo-500 sm:text-sm">
                            <option value="">Todos</option>
                            <option value="active" {{ request('status') === 'active' ? 'selected' : '' }}>Ativos</option>
                            <option value="blocked" {{ request('status') === 'blocked' ? 'selected' : '' }}>Bloqueados</option>
                        </select>
                    </div>

                    <div class="flex items-end">
                        <button type="submit" class="w-full inline-flex justify-center items-center px-4 py-2 border border-transparent shadow-sm text-sm font-medium rounded-md text-white bg-indigo-600 hover:bg-indigo-700 focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-indigo-500">
                            <svg class="h-5 w-5 mr-2" fill="none" viewBox="0 0 24 24" stroke-width="1.5" stroke="currentColor">
                                <path stroke-linecap="round" stroke-linejoin="round" d="M21 21l-5.197-5.197m0 0A7.5 7.5 0 105.196 5.196a7.5 7.5 0 0010.607 10.607z" />
                            </svg>
                            Filtrar
                        </button>
                    </div>
                </div>
            </form>
        </div>
    </div>

    <!-- Users Table -->
    <div class="bg-white shadow rounded-lg">
        <div class="px-4 py-5 sm:px-6 border-b border-gray-200">
            <div class="flex justify-between items-center">
                <div>
                    <h3 class="text-lg leading-6 font-medium text-gray-900">Lista de Usuários</h3>
                    <p class="mt-1 text-sm text-gray-500">Total: {{ $usuarios->total() }} usuário(s)</p>
                </div>
                <div id="selected-actions" class="hidden">
                    <button type="button" onclick="enviarEmailSelecionados()" class="inline-flex items-center px-4 py-2 border border-transparent shadow-sm text-sm font-medium rounded-md text-white bg-indigo-600 hover:bg-indigo-700 focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-indigo-500">
                        <svg class="h-5 w-5 mr-2" fill="none" viewBox="0 0 24 24" stroke-width="1.5" stroke="currentColor">
                            <path stroke-linecap="round" stroke-linejoin="round" d="M21.75 6.75v10.5a2.25 2.25 0 01-2.25 2.25h-15a2.25 2.25 0 01-2.25-2.25V6.75m19.5 0A2.25 2.25 0 0019.5 4.5h-15a2.25 2.25 0 00-2.25 2.25m19.5 0v.243a2.25 2.25 0 01-1.07 1.916l-7.5 4.615a2.25 2.25 0 01-2.36 0L3.32 8.91a2.25 2.25 0 01-1.07-1.916V6.75" />
                        </svg>
                        Enviar E-mail (<span id="selected-count">0</span>)
                    </button>
                </div>
            </div>
        </div>
        <div class="overflow-x-auto">
            <form id="email-form" method="POST" action="{{ route('admin.usuarios.enviar-email-selecionados') }}">
                @csrf
                <table class="min-w-full divide-y divide-gray-200">
                    <thead class="bg-gray-50">
                        <tr>
                            <th scope="col" class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">
                                <input type="checkbox" id="select-all" class="h-4 w-4 text-indigo-600 focus:ring-indigo-500 border-gray-300 rounded">
                            </th>
                            <th scope="col" class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">ID</th>
                            <th scope="col" class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Usuário</th>
                            <th scope="col" class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Email</th>
                            <th scope="col" class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Role</th>
                            <th scope="col" class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Créditos</th>
                            <th scope="col" class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Questões</th>
                            <th scope="col" class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Simulados</th>
                            <th scope="col" class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Status</th>
                            <th scope="col" class="px-6 py-3 text-right text-xs font-medium text-gray-500 uppercase tracking-wider">Ações</th>
                        </tr>
                    </thead>
                    <tbody class="bg-white divide-y divide-gray-200">
                        @forelse($usuarios as $usuario)
                            <tr class="hover:bg-gray-50">
                                <td class="px-6 py-4 whitespace-nowrap text-sm">
                                    <input type="checkbox" name="usuarios[]" value="{{ $usuario->id }}" class="user-checkbox h-4 w-4 text-indigo-600 focus:ring-indigo-500 border-gray-300 rounded">
                                </td>
                                <td class="px-6 py-4 whitespace-nowrap text-sm font-medium text-gray-900">
                                    #{{ $usuario->id }}
                                </td>
                            <td class="px-6 py-4 whitespace-nowrap">
                                <div class="flex items-center">
                                    <div class="h-10 w-10 flex-shrink-0">
                                        <div class="h-10 w-10 rounded-full bg-indigo-600 flex items-center justify-center text-white font-medium">
                                            {{ substr($usuario->name, 0, 1) }}
                                        </div>
                                    </div>
                                    <div class="ml-4">
                                        <div class="text-sm font-medium text-gray-900">{{ $usuario->name }}</div>
                                    </div>
                                </div>
                            </td>
                            <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-500">
                                {{ $usuario->email }}
                            </td>
                            <td class="px-6 py-4 whitespace-nowrap">
                                @if($usuario->role === 'admin')
                                    <span class="inline-flex rounded-full bg-red-100 px-2 text-xs font-semibold leading-5 text-red-800">Admin</span>
                                @elseif($usuario->role === 'professor')
                                    <span class="inline-flex rounded-full bg-blue-100 px-2 text-xs font-semibold leading-5 text-blue-800">Professor</span>
                                @else
                                    <span class="inline-flex rounded-full bg-green-100 px-2 text-xs font-semibold leading-5 text-green-800">Aluno</span>
                                @endif
                            </td>
                            <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-500">
                                {{ $usuario->creditos }}
                            </td>
                            <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-500">
                                {{ $usuario->questoes_count }}
                            </td>
                            <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-500">
                                {{ $usuario->simulados_count }}
                            </td>
                            <td class="px-6 py-4 whitespace-nowrap">
                                @if($usuario->blocked_at)
                                    <span class="inline-flex items-center rounded-full bg-red-100 px-2.5 py-0.5 text-xs font-medium text-red-800">
                                        <svg class="mr-1 h-3 w-3" fill="currentColor" viewBox="0 0 20 20">
                                            <path fill-rule="evenodd" d="M10 1.944A11.954 11.954 0 012.166 5C2.056 5.649 2 6.319 2 7c0 5.225 3.34 9.67 8 11.317C14.66 16.67 18 12.225 18 7c0-.682-.057-1.35-.166-2.001A11.954 11.954 0 0110 1.944zM11 14a1 1 0 11-2 0 1 1 0 012 0zm0-7a1 1 0 10-2 0v3a1 1 0 102 0V7z" clip-rule="evenodd" />
                                        </svg>
                                        Bloqueado
                                    </span>
                                @else
                                    <span class="inline-flex items-center rounded-full bg-green-100 px-2.5 py-0.5 text-xs font-medium text-green-800">
                                        <svg class="mr-1 h-3 w-3" fill="currentColor" viewBox="0 0 20 20">
                                            <path fill-rule="evenodd" d="M10 18a8 8 0 100-16 8 8 0 000 16zm3.707-9.293a1 1 0 00-1.414-1.414L9 10.586 7.707 9.293a1 1 0 00-1.414 1.414l2 2a1 1 0 001.414 0l4-4z" clip-rule="evenodd" />
                                        </svg>
                                        Ativo
                                    </span>
                                @endif
                            </td>
                            <td class="px-6 py-4 whitespace-nowrap text-right text-sm font-medium">
                                <a href="{{ route('admin.usuarios.show', $usuario->id) }}" class="text-indigo-600 hover:text-indigo-900 mr-3">Ver</a>
                                <a href="{{ route('admin.usuarios.edit', $usuario->id) }}" class="text-orange-600 hover:text-orange-900">Editar</a>
                            </td>
                        </tr>
                    @empty
                        <tr>
                            <td colspan="10" class="px-6 py-10 text-center text-sm text-gray-500">
                                <svg class="mx-auto h-12 w-12 text-gray-400" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 4.354a4 4 0 110 5.292M15 21H3v-1a6 6 0 0112 0v1zm0 0h6v-1a6 6 0 00-9-5.197M13 7a4 4 0 11-8 0 4 4 0 018 0z" />
                                </svg>
                                <p class="mt-2">Nenhum usuário encontrado</p>
                            </td>
                        </tr>
                    @endforelse
                </tbody>
            </table>
            </form>
        </div>

        <!-- Pagination -->
        @if($usuarios->hasPages())
            <div class="bg-white px-4 py-3 border-t border-gray-200 sm:px-6">
                {{ $usuarios->links() }}
            </div>
        @endif
    </div>

    <!-- Modal para enviar e-mail -->
    <div id="email-modal" class="hidden fixed inset-0 bg-gray-600 bg-opacity-50 overflow-y-auto h-full w-full z-50">
        <div class="relative top-20 mx-auto p-5 border w-full max-w-2xl shadow-lg rounded-md bg-white">
            <div class="mt-3">
                <div class="flex items-center justify-between mb-4">
                    <h3 class="text-lg font-medium text-gray-900">Enviar E-mail para Usuários Selecionados</h3>
                    <button type="button" onclick="fecharModal()" class="text-gray-400 hover:text-gray-500">
                        <svg class="h-6 w-6" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12" />
                        </svg>
                    </button>
                </div>

                <form id="modal-email-form" method="POST" action="{{ route('admin.usuarios.enviar-email-selecionados') }}">
                    @csrf
                    <input type="hidden" name="usuarios_selecionados" id="usuarios-selecionados">

                    <div class="mb-4">
                        <p class="text-sm text-gray-600">
                            Você está enviando e-mail para <strong><span id="modal-selected-count">0</span> usuário(s)</strong> selecionado(s).
                        </p>
                    </div>

                    <div class="mb-4">
                        <label for="modal-assunto" class="block text-sm font-medium text-gray-700">Assunto *</label>
                        <input type="text" name="assunto" id="modal-assunto" required
                               class="mt-1 block w-full rounded-md border-gray-300 shadow-sm focus:border-indigo-500 focus:ring-indigo-500 sm:text-sm"
                               placeholder="Digite o assunto do e-mail...">
                    </div>

                    <div class="mb-4">
                        <label for="modal-mensagem" class="block text-sm font-medium text-gray-700">Mensagem *</label>
                        <textarea name="mensagem" id="modal-mensagem" rows="8" required
                                  class="mt-1 block w-full rounded-md border-gray-300 shadow-sm focus:border-indigo-500 focus:ring-indigo-500 sm:text-sm"
                                  placeholder="Digite a mensagem do e-mail..."></textarea>
                        <p class="mt-2 text-sm text-gray-500">A mensagem será personalizada com o nome de cada usuário.</p>
                    </div>

                    <div class="bg-yellow-50 border-l-4 border-yellow-400 p-4 mb-4">
                        <div class="flex">
                            <div class="flex-shrink-0">
                                <svg class="h-5 w-5 text-yellow-400" viewBox="0 0 20 20" fill="currentColor">
                                    <path fill-rule="evenodd" d="M8.257 3.099c.765-1.36 2.722-1.36 3.486 0l5.58 9.92c.75 1.334-.213 2.98-1.742 2.98H4.42c-1.53 0-2.493-1.646-1.743-2.98l5.58-9.92zM11 13a1 1 0 11-2 0 1 1 0 012 0zm-1-8a1 1 0 00-1 1v3a1 1 0 002 0V6a1 1 0 00-1-1z" clip-rule="evenodd" />
                                </svg>
                            </div>
                            <div class="ml-3">
                                <p class="text-sm text-yellow-700">
                                    <strong>Atenção:</strong> Esta ação enviará e-mail para todos os usuários selecionados e não pode ser desfeita.
                                </p>
                            </div>
                        </div>
                    </div>

                    <div class="flex justify-end space-x-3">
                        <button type="button" onclick="fecharModal()"
                                class="inline-flex items-center px-4 py-2 bg-gray-300 border border-transparent rounded-md font-semibold text-xs text-gray-700 uppercase tracking-widest hover:bg-gray-400">
                            Cancelar
                        </button>
                        <button type="submit"
                                class="inline-flex items-center px-4 py-2 bg-indigo-600 border border-transparent rounded-md font-semibold text-xs text-white uppercase tracking-widest hover:bg-indigo-700">
                            <svg class="w-4 h-4 mr-2" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M3 8l7.89 5.26a2 2 0 002.22 0L21 8M5 19h14a2 2 0 002-2V7a2 2 0 00-2-2H5a2 2 0 00-2 2v10a2 2 0 002 2z"></path>
                            </svg>
                            Enviar E-mails
                        </button>
                    </div>
                </form>
            </div>
        </div>
    </div>

    <script>
        // Selecionar todos os checkboxes
        document.getElementById('select-all').addEventListener('change', function() {
            const checkboxes = document.querySelectorAll('.user-checkbox');
            checkboxes.forEach(checkbox => {
                checkbox.checked = this.checked;
            });
            atualizarContador();
        });

        // Atualizar contador quando checkboxes individuais mudam
        document.querySelectorAll('.user-checkbox').forEach(checkbox => {
            checkbox.addEventListener('change', atualizarContador);
        });

        function atualizarContador() {
            const checkboxes = document.querySelectorAll('.user-checkbox:checked');
            const count = checkboxes.length;
            document.getElementById('selected-count').textContent = count;
            
            const selectedActions = document.getElementById('selected-actions');
            if (count > 0) {
                selectedActions.classList.remove('hidden');
            } else {
                selectedActions.classList.add('hidden');
            }
        }

        function enviarEmailSelecionados() {
            const checkboxes = document.querySelectorAll('.user-checkbox:checked');
            const usuariosIds = Array.from(checkboxes).map(cb => cb.value);
            
            if (usuariosIds.length === 0) {
                alert('Selecione pelo menos um usuário');
                return;
            }

            // Atualizar modal
            document.getElementById('usuarios-selecionados').value = usuariosIds.join(',');
            document.getElementById('modal-selected-count').textContent = usuariosIds.length;
            
            // Mostrar modal
            document.getElementById('email-modal').classList.remove('hidden');
        }

        function fecharModal() {
            document.getElementById('email-modal').classList.add('hidden');
            document.getElementById('modal-email-form').reset();
        }

        // Fechar modal ao clicar fora
        document.getElementById('email-modal').addEventListener('click', function(e) {
            if (e.target === this) {
                fecharModal();
            }
        });
    </script>
@endsection
