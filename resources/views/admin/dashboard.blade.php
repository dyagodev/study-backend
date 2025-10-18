@extends('admin.layout')

@section('title', 'Dashboard')
@section('page-title', 'Dashboard')

@section('content')
    <!-- Statistics Cards -->
    <div class="stats-grid">
        <div class="stat-card blue">
            <h3>Total de Usuários</h3>
            <div class="value">{{ number_format($stats['total_usuarios'], 0, ',', '.') }}</div>
        </div>

        <div class="stat-card green">
            <h3>Usuários Ativos (30d)</h3>
            <div class="value">{{ number_format($stats['usuarios_ativos'], 0, ',', '.') }}</div>
        </div>

        <div class="stat-card red">
            <h3>Usuários Bloqueados</h3>
            <div class="value">{{ number_format($stats['usuarios_bloqueados'], 0, ',', '.') }}</div>
        </div>

        <div class="stat-card purple">
            <h3>Total de Questões</h3>
            <div class="value">{{ number_format($stats['total_questoes'], 0, ',', '.') }}</div>
        </div>

        <div class="stat-card orange">
            <h3>Questões Hoje</h3>
            <div class="value">{{ number_format($stats['questoes_hoje'], 0, ',', '.') }}</div>
        </div>

        <div class="stat-card blue">
            <h3>Total de Simulados</h3>
            <div class="value">{{ number_format($stats['total_simulados'], 0, ',', '.') }}</div>
        </div>

        <div class="stat-card green">
            <h3>Simulados Hoje</h3>
            <div class="value">{{ number_format($stats['simulados_hoje'], 0, ',', '.') }}</div>
        </div>

        <div class="stat-card orange">
            <h3>Créditos Distribuídos</h3>
            <div class="value">{{ number_format($stats['total_creditos_distribuidos'], 0, ',', '.') }}</div>
        </div>

        <div class="stat-card red">
            <h3>Pagamentos Pendentes</h3>
            <div class="value">{{ number_format($stats['transacoes_pendentes'], 0, ',', '.') }}</div>
        </div>

        <div class="stat-card green">
            <h3>Receita Total</h3>
            <div class="value">R$ {{ number_format($stats['receita_total'], 2, ',', '.') }}</div>
        </div>
    </div>

    <!-- Recent Users -->
    <div class="content-card">
        <h2>👥 Usuários Recentes</h2>
        <table class="table">
            <thead>
                <tr>
                    <th>Nome</th>
                    <th>Email</th>
                    <th>Role</th>
                    <th>Créditos</th>
                    <th>Cadastrado em</th>
                    <th>Ações</th>
                </tr>
            </thead>
            <tbody>
                @forelse($usuarios_recentes as $usuario)
                    <tr>
                        <td>{{ $usuario->name }}</td>
                        <td>{{ $usuario->email }}</td>
                        <td>
                            @if($usuario->role === 'admin')
                                <span class="badge badge-danger">Admin</span>
                            @elseif($usuario->role === 'professor')
                                <span class="badge badge-info">Professor</span>
                            @else
                                <span class="badge badge-success">Aluno</span>
                            @endif
                        </td>
                        <td>{{ $usuario->creditos }}</td>
                        <td>{{ $usuario->created_at->format('d/m/Y H:i') }}</td>
                        <td>
                            <a href="{{ route('admin.usuarios.show', $usuario->id) }}" class="btn btn-primary btn-sm">Ver</a>
                        </td>
                    </tr>
                @empty
                    <tr>
                        <td colspan="6" style="text-align: center; padding: 20px; color: #999;">
                            Nenhum usuário encontrado
                        </td>
                    </tr>
                @endforelse
            </tbody>
        </table>
    </div>

    <!-- Recent Simulados -->
    <div class="content-card">
        <h2>📝 Simulados Recentes</h2>
        <table class="table">
            <thead>
                <tr>
                    <th>Título</th>
                    <th>Usuário</th>
                    <th>Questões</th>
                    <th>Status</th>
                    <th>Criado em</th>
                </tr>
            </thead>
            <tbody>
                @forelse($simulados_recentes as $simulado)
                    <tr>
                        <td>{{ $simulado->titulo }}</td>
                        <td>{{ $simulado->user->name }}</td>
                        <td>{{ $simulado->questoes->count() }}</td>
                        <td>
                            @if($simulado->status === 'concluido')
                                <span class="badge badge-success">Concluído</span>
                            @elseif($simulado->status === 'em_andamento')
                                <span class="badge badge-warning">Em Andamento</span>
                            @else
                                <span class="badge badge-info">Não Iniciado</span>
                            @endif
                        </td>
                        <td>{{ $simulado->created_at->format('d/m/Y H:i') }}</td>
                    </tr>
                @empty
                    <tr>
                        <td colspan="5" style="text-align: center; padding: 20px; color: #999;">
                            Nenhum simulado encontrado
                        </td>
                    </tr>
                @endforelse
            </tbody>
        </table>
    </div>
@endsection
