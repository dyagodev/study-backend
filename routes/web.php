<?php

use App\Http\Controllers\ProfileController;
use App\Http\Controllers\AdminController;
use Illuminate\Support\Facades\Route;

Route::get('/', function () {
    return view('welcome');
});

Route::get('/dashboard', function () {
    return view('dashboard');
})->middleware(['auth', 'verified'])->name('dashboard');

Route::middleware('auth')->group(function () {
    Route::get('/profile', [ProfileController::class, 'edit'])->name('profile.edit');
    Route::patch('/profile', [ProfileController::class, 'update'])->name('profile.update');
    Route::delete('/profile', [ProfileController::class, 'destroy'])->name('profile.destroy');
});

require __DIR__.'/auth.php';

// ============================================
// ROTAS DO PAINEL ADMINISTRATIVO
// ============================================

// Redirect /admin/login para login padrão do Breeze
Route::get('/admin/login', function() {
    return redirect()->route('login');
})->name('admin.login.form');

// Rotas Admin (protegidas por auth e admin middleware)
Route::middleware(['auth', 'admin'])->prefix('admin')->name('admin.')->group(function () {
    // Dashboard
    Route::get('/', [AdminController::class, 'dashboard'])->name('dashboard');
    
    // Usuários
    Route::get('/usuarios', [AdminController::class, 'usuarios'])->name('usuarios.index');
    Route::get('/usuarios/{id}', [AdminController::class, 'usuarioShow'])->name('usuarios.show');
    Route::get('/usuarios/{id}/edit', [AdminController::class, 'usuarioEdit'])->name('usuarios.edit');
    Route::put('/usuarios/{id}', [AdminController::class, 'usuarioUpdate'])->name('usuarios.update');
    Route::delete('/usuarios/{id}', [AdminController::class, 'usuarioDelete'])->name('usuarios.delete');
    
    // Ações de usuários
    Route::post('/usuarios/{id}/adicionar-creditos', [AdminController::class, 'adicionarCreditos'])->name('usuarios.adicionar-creditos');
    Route::post('/usuarios/{id}/remover-creditos', [AdminController::class, 'removerCreditos'])->name('usuarios.remover-creditos');
    Route::post('/usuarios/{id}/bloquear', [AdminController::class, 'bloquearUsuario'])->name('usuarios.bloquear');
    Route::post('/usuarios/{id}/desbloquear', [AdminController::class, 'desbloquearUsuario'])->name('usuarios.desbloquear');
    
    // Estatísticas
    Route::get('/estatisticas', [AdminController::class, 'estatisticas'])->name('estatisticas');
    
    // Pagamentos
    Route::get('/pagamentos', [AdminController::class, 'pagamentos'])->name('pagamentos');
});
