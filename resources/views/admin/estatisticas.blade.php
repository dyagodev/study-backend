@extends('admin.modern-layout')

@section('title', 'Estatísticas')
@section('page-title', 'Estatísticas do Sistema')

@section('content')
    <!-- Users by Role -->
    <div class="content-card">
        <h2>👥 Distribuição de Usuários por Role</h2>
        <table class="table">
            <thead>
                <tr>
                    <th>Role</th>
                    <th>Total</th>
                    <th>Percentual</th>
                </tr>
            </thead>
            <tbody>
                @php
                    $total = $usuariosPorRole->sum('total');
                @endphp
                @foreach($usuariosPorRole as $item)
                    <tr>
                        <td>
                            @if($item->role === 'admin')
                                <span class="badge badge-danger">Admin</span>
                            @elseif($item->role === 'professor')
                                <span class="badge badge-info">Professor</span>
                            @else
                                <span class="badge badge-success">Aluno</span>
                            @endif
                        </td>
                        <td>{{ $item->total }}</td>
                        <td>{{ $total > 0 ? number_format(($item->total / $total) * 100, 1) : 0 }}%</td>
                    </tr>
                @endforeach
            </tbody>
        </table>
    </div>

    <!-- Registrations by Month -->
    <div class="content-card" style="margin-top: 20px;">
        <h2>📊 Cadastros por Mês (Últimos 12 meses)</h2>
        <table class="table">
            <thead>
                <tr>
                    <th>Mês</th>
                    <th>Novos Usuários</th>
                </tr>
            </thead>
            <tbody>
                @forelse($usuariosPorMes as $item)
                    <tr>
                        <td>{{ \Carbon\Carbon::parse($item->mes . '-01')->format('F Y') }}</td>
                        <td>{{ $item->total }}</td>
                    </tr>
                @empty
                    <tr>
                        <td colspan="2" style="text-align: center; padding: 20px; color: #999;">
                            Sem dados disponíveis
                        </td>
                    </tr>
                @endforelse
            </tbody>
        </table>
    </div>

    <!-- Questions by Day -->
    <div class="content-card" style="margin-top: 20px;">
        <h2>📝 Questões Criadas por Dia (Últimos 30 dias)</h2>
        <table class="table">
            <thead>
                <tr>
                    <th>Data</th>
                    <th>Total</th>
                </tr>
            </thead>
            <tbody>
                @forelse($questoesPorDia as $item)
                    <tr>
                        <td>{{ \Carbon\Carbon::parse($item->dia)->format('d/m/Y') }}</td>
                        <td>{{ $item->total }}</td>
                    </tr>
                @empty
                    <tr>
                        <td colspan="2" style="text-align: center; padding: 20px; color: #999;">
                            Sem dados disponíveis
                        </td>
                    </tr>
                @endforelse
            </tbody>
        </table>
    </div>

    <!-- Simulados by Day -->
    <div class="content-card" style="margin-top: 20px;">
        <h2>📋 Simulados Criados por Dia (Últimos 30 dias)</h2>
        <table class="table">
            <thead>
                <tr>
                    <th>Data</th>
                    <th>Total</th>
                </tr>
            </thead>
            <tbody>
                @forelse($simuladosPorDia as $item)
                    <tr>
                        <td>{{ \Carbon\Carbon::parse($item->dia)->format('d/m/Y') }}</td>
                        <td>{{ $item->total }}</td>
                    </tr>
                @empty
                    <tr>
                        <td colspan="2" style="text-align: center; padding: 20px; color: #999;">
                            Sem dados disponíveis
                        </td>
                    </tr>
                @endforelse
            </tbody>
        </table>
    </div>

    <!-- Top Users - Questions -->
    <div class="content-card" style="margin-top: 20px;">
        <h2>🏆 Top 10 - Usuários com Mais Questões</h2>
        <table class="table">
            <thead>
                <tr>
                    <th>Posição</th>
                    <th>Usuário</th>
                    <th>Email</th>
                    <th>Total de Questões</th>
                </tr>
            </thead>
            <tbody>
                @forelse($topUsuariosQuestoes as $index => $usuario)
                    <tr>
                        <td>{{ $index + 1 }}º</td>
                        <td>
                            <a href="{{ route('admin.usuarios.show', $usuario->id) }}">{{ $usuario->name }}</a>
                        </td>
                        <td>{{ $usuario->email }}</td>
                        <td>{{ $usuario->questoes_count }}</td>
                    </tr>
                @empty
                    <tr>
                        <td colspan="4" style="text-align: center; padding: 20px; color: #999;">
                            Sem dados disponíveis
                        </td>
                    </tr>
                @endforelse
            </tbody>
        </table>
    </div>

    <!-- Top Users - Simulados -->
    <div class="content-card" style="margin-top: 20px;">
        <h2>🏆 Top 10 - Usuários com Mais Simulados</h2>
        <table class="table">
            <thead>
                <tr>
                    <th>Posição</th>
                    <th>Usuário</th>
                    <th>Email</th>
                    <th>Total de Simulados</th>
                </tr>
            </thead>
            <tbody>
                @forelse($topUsuariosSimulados as $index => $usuario)
                    <tr>
                        <td>{{ $index + 1 }}º</td>
                        <td>
                            <a href="{{ route('admin.usuarios.show', $usuario->id) }}">{{ $usuario->name }}</a>
                        </td>
                        <td>{{ $usuario->email }}</td>
                        <td>{{ $usuario->simulados_count }}</td>
                    </tr>
                @empty
                    <tr>
                        <td colspan="4" style="text-align: center; padding: 20px; color: #999;">
                            Sem dados disponíveis
                        </td>
                    </tr>
                @endforelse
            </tbody>
        </table>
    </div>
@endsection
