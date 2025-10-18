@extends('admin.modern-layout')

@section('title', 'Usuários')
@section('page-title', 'Gerenciar Usuários')

@section('content')
    <!-- Filters -->
    <div class="content-card">
        <form method="GET" action="{{ route('admin.usuarios.index') }}">
            <div class="filters">
                <div class="form-group">
                    <label>Buscar</label>
                    <input type="text" name="search" class="form-control" placeholder="Nome ou email..." value="{{ request('search') }}">
                </div>

                <div class="form-group">
                    <label>Role</label>
                    <select name="role" class="form-control">
                        <option value="">Todos</option>
                        <option value="aluno" {{ request('role') === 'aluno' ? 'selected' : '' }}>Aluno</option>
                        <option value="professor" {{ request('role') === 'professor' ? 'selected' : '' }}>Professor</option>
                        <option value="admin" {{ request('role') === 'admin' ? 'selected' : '' }}>Admin</option>
                    </select>
                </div>

                <div class="form-group">
                    <label>Status</label>
                    <select name="status" class="form-control">
                        <option value="">Todos</option>
                        <option value="active" {{ request('status') === 'active' ? 'selected' : '' }}>Ativos</option>
                        <option value="blocked" {{ request('status') === 'blocked' ? 'selected' : '' }}>Bloqueados</option>
                    </select>
                </div>

                <div class="form-group" style="display: flex; align-items: flex-end;">
                    <button type="submit" class="btn btn-primary">🔍 Filtrar</button>
                </div>
            </div>
        </form>
    </div>

    <!-- Users Table -->
    <div class="content-card">
        <h2>👥 Lista de Usuários ({{ $usuarios->total() }})</h2>
        <table class="table">
            <thead>
                <tr>
                    <th>ID</th>
                    <th>Nome</th>
                    <th>Email</th>
                    <th>Role</th>
                    <th>Créditos</th>
                    <th>Questões</th>
                    <th>Simulados</th>
                    <th>Status</th>
                    <th>Ações</th>
                </tr>
            </thead>
            <tbody>
                @forelse($usuarios as $usuario)
                    <tr>
                        <td>{{ $usuario->id }}</td>
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
                        <td>{{ $usuario->questoes_count }}</td>
                        <td>{{ $usuario->simulados_count }}</td>
                        <td>
                            @if($usuario->blocked_at)
                                <span class="badge badge-danger">🔒 Bloqueado</span>
                            @else
                                <span class="badge badge-success">✓ Ativo</span>
                            @endif
                        </td>
                        <td>
                            <a href="{{ route('admin.usuarios.show', $usuario->id) }}" class="btn btn-primary btn-sm">Ver</a>
                            <a href="{{ route('admin.usuarios.edit', $usuario->id) }}" class="btn btn-warning btn-sm">Editar</a>
                        </td>
                    </tr>
                @empty
                    <tr>
                        <td colspan="9" style="text-align: center; padding: 20px; color: #999;">
                            Nenhum usuário encontrado
                        </td>
                    </tr>
                @endforelse
            </tbody>
        </table>

        <!-- Pagination -->
        <div class="pagination">
            {{ $usuarios->links() }}
        </div>
    </div>
@endsection
