@extends('admin.modern-layout')

@section('title', 'Pagamentos')
@section('page-title', 'Gerenciar Pagamentos')

@section('content')
    <!-- Stats -->
    <div class="stats-grid">
        <div class="stat-card green">
            <h3>Total Recebido</h3>
            <div class="value">R$ {{ number_format($stats['total_recebido'], 2, ',', '.') }}</div>
        </div>

        <div class="stat-card orange">
            <h3>Pendentes</h3>
            <div class="value">{{ number_format($stats['pendentes'], 0, ',', '.') }}</div>
        </div>

        <div class="stat-card blue">
            <h3>Aprovados</h3>
            <div class="value">{{ number_format($stats['aprovados'], 0, ',', '.') }}</div>
        </div>

        <div class="stat-card red">
            <h3>Rejeitados</h3>
            <div class="value">{{ number_format($stats['rejeitados'], 0, ',', '.') }}</div>
        </div>
    </div>

    <!-- Filters -->
    <div class="content-card" style="margin-top: 20px;">
        <form method="GET" action="{{ route('admin.pagamentos') }}">
            <div class="filters">
                <div class="form-group">
                    <label>Status</label>
                    <select name="status" class="form-control">
                        <option value="">Todos</option>
                        <option value="pending" {{ request('status') === 'pending' ? 'selected' : '' }}>Pendente</option>
                        <option value="approved" {{ request('status') === 'approved' ? 'selected' : '' }}>Aprovado</option>
                        <option value="rejected" {{ request('status') === 'rejected' ? 'selected' : '' }}>Rejeitado</option>
                        <option value="cancelled" {{ request('status') === 'cancelled' ? 'selected' : '' }}>Cancelado</option>
                    </select>
                </div>

                <div class="form-group">
                    <label>Período</label>
                    <select name="periodo" class="form-control">
                        <option value="">Todos</option>
                        <option value="hoje" {{ request('periodo') === 'hoje' ? 'selected' : '' }}>Hoje</option>
                        <option value="semana" {{ request('periodo') === 'semana' ? 'selected' : '' }}>Última Semana</option>
                        <option value="mes" {{ request('periodo') === 'mes' ? 'selected' : '' }}>Último Mês</option>
                    </select>
                </div>

                <div class="form-group" style="display: flex; align-items: flex-end;">
                    <button type="submit" class="btn btn-primary">🔍 Filtrar</button>
                </div>
            </div>
        </form>
    </div>

    <!-- Payments Table -->
    <div class="content-card" style="margin-top: 20px;">
        <h2>💳 Lista de Pagamentos ({{ $pagamentos->total() }})</h2>
        <table class="table">
            <thead>
                <tr>
                    <th>ID</th>
                    <th>Usuário</th>
                    <th>Valor</th>
                    <th>Créditos</th>
                    <th>Status</th>
                    <th>Pagamento ID</th>
                    <th>Data</th>
                </tr>
            </thead>
            <tbody>
                @forelse($pagamentos as $pagamento)
                    <tr>
                        <td>{{ $pagamento->id }}</td>
                        <td>
                            <a href="{{ route('admin.usuarios.show', $pagamento->user_id) }}">
                                {{ $pagamento->user->name }}
                            </a>
                        </td>
                        <td>R$ {{ number_format($pagamento->valor, 2, ',', '.') }}</td>
                        <td>{{ $pagamento->creditos }}</td>
                        <td>
                            @if($pagamento->status === 'approved')
                                <span class="badge badge-success">✓ Aprovado</span>
                            @elseif($pagamento->status === 'pending')
                                <span class="badge badge-warning">⏳ Pendente</span>
                            @elseif($pagamento->status === 'rejected')
                                <span class="badge badge-danger">✗ Rejeitado</span>
                            @else
                                <span class="badge badge-info">{{ $pagamento->status }}</span>
                            @endif
                        </td>
                        <td>
                            <code style="font-size: 11px;">{{ substr($pagamento->pagamento_id, 0, 20) }}...</code>
                        </td>
                        <td>{{ $pagamento->created_at->format('d/m/Y H:i') }}</td>
                    </tr>
                @empty
                    <tr>
                        <td colspan="7" style="text-align: center; padding: 20px; color: #999;">
                            Nenhum pagamento encontrado
                        </td>
                    </tr>
                @endforelse
            </tbody>
        </table>

        <!-- Pagination -->
        <div class="pagination">
            {{ $pagamentos->appends(request()->query())->links() }}
        </div>
    </div>
@endsection
