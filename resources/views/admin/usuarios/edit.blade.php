@extends('admin.modern-layout')

@section('title', 'Editar Usuário')
@section('page-title', 'Editar Usuário')

@section('content')
    <div style="margin-bottom: 20px;">
        <a href="{{ route('admin.usuarios.show', $usuario->id) }}" class="btn btn-primary">← Voltar</a>
    </div>

    <div class="content-card">
        <h2>✏️ Editar Dados do Usuário</h2>

        <form method="POST" action="{{ route('admin.usuarios.update', $usuario->id) }}" style="max-width: 600px; margin-top: 20px;">
            @csrf
            @method('PUT')

            <div class="form-group">
                <label for="name">Nome *</label>
                <input type="text" id="name" name="name" class="form-control" value="{{ old('name', $usuario->name) }}" required>
                @error('name')
                    <span style="color: red; font-size: 12px;">{{ $message }}</span>
                @enderror
            </div>

            <div class="form-group">
                <label for="email">Email *</label>
                <input type="email" id="email" name="email" class="form-control" value="{{ old('email', $usuario->email) }}" required>
                @error('email')
                    <span style="color: red; font-size: 12px;">{{ $message }}</span>
                @enderror
            </div>

            <div class="form-group">
                <label for="role">Role *</label>
                <select id="role" name="role" class="form-control" required>
                    <option value="aluno" {{ old('role', $usuario->role) === 'aluno' ? 'selected' : '' }}>Aluno</option>
                    <option value="professor" {{ old('role', $usuario->role) === 'professor' ? 'selected' : '' }}>Professor</option>
                    <option value="admin" {{ old('role', $usuario->role) === 'admin' ? 'selected' : '' }}>Admin</option>
                </select>
                @error('role')
                    <span style="color: red; font-size: 12px;">{{ $message }}</span>
                @enderror
            </div>

            <div class="form-group">
                <label for="creditos">Créditos Atuais *</label>
                <input type="number" id="creditos" name="creditos" class="form-control" value="{{ old('creditos', $usuario->creditos) }}" min="0" required>
                @error('creditos')
                    <span style="color: red; font-size: 12px;">{{ $message }}</span>
                @enderror
            </div>

            <div class="form-group">
                <label for="creditos_semanais">Créditos Semanais *</label>
                <input type="number" id="creditos_semanais" name="creditos_semanais" class="form-control" value="{{ old('creditos_semanais', $usuario->creditos_semanais) }}" min="0" required>
                <small style="color: #666;">Quantidade de créditos que o usuário recebe a cada 7 dias</small>
                @error('creditos_semanais')
                    <span style="color: red; font-size: 12px;">{{ $message }}</span>
                @enderror
            </div>

            <div style="display: flex; gap: 10px; margin-top: 30px;">
                <button type="submit" class="btn btn-success">💾 Salvar Alterações</button>
                <a href="{{ route('admin.usuarios.show', $usuario->id) }}" class="btn btn-danger">Cancelar</a>
            </div>
        </form>
    </div>
@endsection
