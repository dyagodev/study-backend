@extends('admin.modern-layout')

@section('title', 'Detalhes do Usuário')
@section('page-title', 'Detalhes do Usuário')

@section('content')
    <div style="margin-bottom: 20px;">
        <a href="{{ route('admin.usuarios.index') }}" class="btn btn-primary">← Voltar</a>
        <a href="{{ route('admin.usuarios.edit', $usuario->id) }}" class="btn btn-warning">✏️ Editar</a>
    </div>

    <!-- User Info -->
    <div class="content-card">
        <h2>📋 Informações do Usuário</h2>
        <div style="display: grid; grid-template-columns: repeat(2, 1fr); gap: 20px; margin-top: 20px;">
            <div>
                <strong>Nome:</strong> {{ $usuario->name }}
            </div>
            <div>
                <strong>Email:</strong> {{ $usuario->email }}
            </div>
            <div>
                <strong>Role:</strong>
                @if($usuario->role === 'admin')
                    <span class="badge badge-danger">Admin</span>
                @elseif($usuario->role === 'professor')
                    <span class="badge badge-info">Professor</span>
                @else
                    <span class="badge badge-success">Aluno</span>
                @endif
            </div>
            <div>
                <strong>Status:</strong>
                @if($usuario->blocked_at)
                    <span class="badge badge-danger">🔒 Bloqueado desde {{ $usuario->blocked_at->format('d/m/Y H:i') }}</span>
                @else
                    <span class="badge badge-success">✓ Ativo</span>
                @endif
            </div>
            <div>
                <strong>Créditos Atuais:</strong> {{ $usuario->creditos }}
            </div>
            <div>
                <strong>Créditos Semanais:</strong> {{ $usuario->creditos_semanais }}
            </div>
            <div>
                <strong>Última Renovação:</strong>
                {{ $usuario->ultima_renovacao ? $usuario->ultima_renovacao->format('d/m/Y H:i') : 'Nunca' }}
            </div>
            <div>
                <strong>Próxima Renovação:</strong>
                {{ $usuario->proximaRenovacao() ? $usuario->proximaRenovacao()->format('d/m/Y H:i') : 'N/A' }}
            </div>
            <div>
                <strong>Cadastrado em:</strong> {{ $usuario->created_at->format('d/m/Y H:i') }}
            </div>
            <div>
                <strong>Última atualização:</strong> {{ $usuario->updated_at->format('d/m/Y H:i') }}
            </div>
        </div>
    </div>

    <!-- Statistics -->
    <div class="stats-grid" style="margin-top: 20px;">
        <div class="stat-card blue">
            <h3>Questões Criadas</h3>
            <div class="value">{{ $usuario->questoes_count }}</div>
        </div>
        <div class="stat-card green">
            <h3>Simulados Criados</h3>
            <div class="value">{{ $usuario->simulados_count }}</div>
        </div>
        <div class="stat-card purple">
            <h3>Transações</h3>
            <div class="value">{{ $usuario->transacoes_creditos_count }}</div>
        </div>
    </div>

    <!-- Actions -->
    <div class="content-card" style="margin-top: 20px;">
        <h2>⚡ Ações Rápidas</h2>
        <div style="display: flex; gap: 15px; margin-top: 20px; flex-wrap: wrap;">
            <!-- Add Credits -->
            <form method="POST" action="{{ route('admin.usuarios.adicionar-creditos', $usuario->id) }}" style="display: inline;">
                @csrf
                <input type="number" name="quantidade" placeholder="Quantidade" required style="padding: 8px; margin-right: 5px; border: 1px solid #ddd; border-radius: 5px;">
                <input type="text" name="descricao" placeholder="Descrição (opcional)" style="padding: 8px; margin-right: 5px; border: 1px solid #ddd; border-radius: 5px;">
                <button type="submit" class="btn btn-success">➕ Adicionar Créditos</button>
            </form>

            <!-- Remove Credits -->
            <form method="POST" action="{{ route('admin.usuarios.remover-creditos', $usuario->id) }}" style="display: inline;">
                @csrf
                <input type="number" name="quantidade" placeholder="Quantidade" required style="padding: 8px; margin-right: 5px; border: 1px solid #ddd; border-radius: 5px;">
                <input type="text" name="descricao" placeholder="Descrição (opcional)" style="padding: 8px; margin-right: 5px; border: 1px solid #ddd; border-radius: 5px;">
                <button type="submit" class="btn btn-warning">➖ Remover Créditos</button>
            </form>

            @if(!$usuario->isAdmin())
                <!-- Block/Unblock -->
                @if($usuario->blocked_at)
                    <form method="POST" action="{{ route('admin.usuarios.desbloquear', $usuario->id) }}" style="display: inline;">
                        @csrf
                        <button type="submit" class="btn btn-success" onclick="return confirm('Desbloquear este usuário?')">🔓 Desbloquear</button>
                    </form>
                @else
                    <form method="POST" action="{{ route('admin.usuarios.bloquear', $usuario->id) }}" style="display: inline;">
                        @csrf
                        <button type="submit" class="btn btn-danger" onclick="return confirm('Bloquear este usuário?')">🔒 Bloquear</button>
                    </form>
                @endif

                <!-- Delete -->
                <form method="POST" action="{{ route('admin.usuarios.delete', $usuario->id) }}" style="display: inline;">
                    @csrf
                    @method('DELETE')
                    <button type="submit" class="btn btn-danger" onclick="return confirm('ATENÇÃO: Deletar este usuário permanentemente?')">🗑️ Deletar</button>
                </form>
            @endif
        </div>
    </div>

    <!-- Recent Transactions -->
    <div class="content-card" style="margin-top: 20px;">
        <h2>💳 Transações Recentes</h2>
        <table class="table">
            <thead>
                <tr>
                    <th>Data</th>
                    <th>Tipo</th>
                    <th>Quantidade</th>
                    <th>Saldo Anterior</th>
                    <th>Saldo Atual</th>
                    <th>Descrição</th>
                </tr>
            </thead>
            <tbody>
                @forelse($transacoes_recentes as $transacao)
                    <tr>
                        <td>{{ $transacao->created_at->format('d/m/Y H:i') }}</td>
                        <td>
                            @if(str_contains($transacao->tipo, 'adicao'))
                                <span class="badge badge-success">➕ {{ $transacao->tipo }}</span>
                            @elseif(str_contains($transacao->tipo, 'remocao') || str_contains($transacao->tipo, 'uso'))
                                <span class="badge badge-danger">➖ {{ $transacao->tipo }}</span>
                            @else
                                <span class="badge badge-info">{{ $transacao->tipo }}</span>
                            @endif
                        </td>
                        <td style="font-weight: bold; color: {{ $transacao->quantidade >= 0 ? '#2ecc71' : '#e74c3c' }};">
                            {{ $transacao->quantidade >= 0 ? '+' : '' }}{{ $transacao->quantidade }}
                        </td>
                        <td>{{ $transacao->saldo_anterior }}</td>
                        <td>{{ $transacao->saldo_atual }}</td>
                        <td>{{ $transacao->descricao ?? '-' }}</td>
                    </tr>
                @empty
                    <tr>
                        <td colspan="6" style="text-align: center; padding: 20px; color: #999;">
                            Nenhuma transação encontrada
                        </td>
                    </tr>
                @endforelse
            </tbody>
        </table>
    </div>
@endsection
