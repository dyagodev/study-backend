<?php

namespace App\Http\Controllers;

use App\Models\User;
use App\Models\Questao;
use App\Models\Simulado;
use App\Models\TransacaoCredito;
use App\Models\PagamentoPix;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Mail;
use App\Mail\AdminNotificationMail;

class AdminController extends Controller
{
    /**
     * Dashboard principal do admin
     */
    public function dashboard()
    {
        $stats = [
            'total_usuarios' => User::count(),
            'usuarios_ativos' => User::where('created_at', '>=', now()->subDays(30))->count(),
            'usuarios_bloqueados' => User::whereNotNull('blocked_at')->count(),
            'total_questoes' => Questao::count(),
            'questoes_hoje' => Questao::whereDate('created_at', today())->count(),
            'total_simulados' => Simulado::count(),
            'simulados_hoje' => Simulado::whereDate('created_at', today())->count(),
            'total_creditos_distribuidos' => User::sum('creditos'),
            'transacoes_pendentes' => PagamentoPix::where('status', 'PENDENTE')->count(),
            'receita_total' => PagamentoPix::where('status', 'CONCLUIDA')->sum('valor'),
        ];

        $usuarios_recentes = User::latest()->take(10)->get();
        $simulados_recentes = Simulado::with('user')->latest()->take(10)->get();

        return view('admin.modern-dashboard', compact('stats', 'usuarios_recentes', 'simulados_recentes'));
    }

    /**
     * Lista todos os usuários
     */
    public function usuarios(Request $request)
    {
        $query = User::query();

        // Filtro de busca
        if ($request->has('search')) {
            $search = $request->search;
            $query->where(function($q) use ($search) {
                $q->where('name', 'like', "%{$search}%")
                  ->orWhere('email', 'like', "%{$search}%");
            });
        }

        // Filtro por role
        if ($request->has('role') && $request->role !== '') {
            $query->where('role', $request->role);
        }

        // Filtro por status
        if ($request->has('status')) {
            if ($request->status === 'blocked') {
                $query->whereNotNull('blocked_at');
            } elseif ($request->status === 'active') {
                $query->whereNull('blocked_at');
            }
        }

        $usuarios = $query->withCount(['questoes', 'simulados', 'transacoesCreditos'])
                          ->paginate(20);

        return view('admin.usuarios.modern-index', compact('usuarios'));
    }

    /**
     * Exibe detalhes de um usuário específico
     */
    public function usuarioShow($id)
    {
        $usuario = User::with(['questoes', 'simulados', 'transacoesCreditos', 'colecoes'])
                       ->withCount(['questoes', 'simulados', 'transacoesCreditos'])
                       ->findOrFail($id);

        $transacoes_recentes = $usuario->transacoesCreditos()->latest()->take(20)->get();

        return view('admin.usuarios.modern-show', compact('usuario', 'transacoes_recentes'));
    }

    /**
     * Exibe formulário de edição do usuário
     */
    public function usuarioEdit($id)
    {
        $usuario = User::findOrFail($id);
        return view('admin.usuarios.modern-edit', compact('usuario'));
    }

    /**
     * Atualiza os dados do usuário
     */
    public function usuarioUpdate(Request $request, $id)
    {
        $usuario = User::findOrFail($id);

        $request->validate([
            'name' => 'required|string|max:255',
            'email' => 'required|email|unique:users,email,' . $id,
            'role' => 'required|in:aluno,professor,admin',
            'creditos' => 'required|integer|min:0',
            'creditos_semanais' => 'required|integer|min:0',
        ]);

        $usuario->update([
            'name' => $request->name,
            'email' => $request->email,
            'role' => $request->role,
            'creditos' => $request->creditos,
            'creditos_semanais' => $request->creditos_semanais,
        ]);

        return redirect()->route('admin.usuarios.show', $usuario->id)
                         ->with('success', 'Usuário atualizado com sucesso!');
    }

    /**
     * Adiciona créditos a um usuário
     */
    public function adicionarCreditos(Request $request, $id)
    {
        $request->validate([
            'quantidade' => 'required|integer|min:1',
            'descricao' => 'nullable|string|max:255',
        ]);

        $usuario = User::findOrFail($id);
        $quantidade = $request->quantidade;

        DB::transaction(function() use ($usuario, $quantidade, $request) {
            $usuario->creditos += $quantidade;
            $usuario->save();

            TransacaoCredito::create([
                'user_id' => $usuario->id,
                'tipo' => 'adicao_manual',
                'quantidade' => $quantidade,
                'saldo_anterior' => $usuario->creditos - $quantidade,
                'saldo_atual' => $usuario->creditos,
                'descricao' => $request->descricao ?? 'Créditos adicionados manualmente pelo admin',
            ]);
        });

        return redirect()->back()->with('success', "Adicionados {$quantidade} créditos ao usuário!");
    }

    /**
     * Remove créditos de um usuário
     */
    public function removerCreditos(Request $request, $id)
    {
        $request->validate([
            'quantidade' => 'required|integer|min:1',
            'descricao' => 'nullable|string|max:255',
        ]);

        $usuario = User::findOrFail($id);
        $quantidade = $request->quantidade;

        DB::transaction(function() use ($usuario, $quantidade, $request) {
            $saldoAnterior = $usuario->creditos;
            $usuario->creditos = max(0, $usuario->creditos - $quantidade);
            $usuario->save();

            TransacaoCredito::create([
                'user_id' => $usuario->id,
                'tipo' => 'remocao_manual',
                'quantidade' => -$quantidade,
                'saldo_anterior' => $saldoAnterior,
                'saldo_atual' => $usuario->creditos,
                'descricao' => $request->descricao ?? 'Créditos removidos manualmente pelo admin',
            ]);
        });

        return redirect()->back()->with('success', "Removidos {$quantidade} créditos do usuário!");
    }

    /**
     * Bloqueia um usuário
     */
    public function bloquearUsuario($id)
    {
        $usuario = User::findOrFail($id);

        if ($usuario->isAdmin()) {
            return redirect()->back()->with('error', 'Não é possível bloquear um administrador!');
        }

        $usuario->blocked_at = now();
        $usuario->save();

        // Cancela todos os tokens do usuário
        $usuario->tokens()->delete();

        return redirect()->back()->with('success', 'Usuário bloqueado com sucesso!');
    }

    /**
     * Desbloqueia um usuário
     */
    public function desbloquearUsuario($id)
    {
        $usuario = User::findOrFail($id);
        $usuario->blocked_at = null;
        $usuario->save();

        return redirect()->back()->with('success', 'Usuário desbloqueado com sucesso!');
    }

    /**
     * Deleta um usuário
     */
    public function usuarioDelete($id)
    {
        $usuario = User::findOrFail($id);

        if ($usuario->isAdmin()) {
            return redirect()->back()->with('error', 'Não é possível deletar um administrador!');
        }

        $usuario->delete();

        return redirect()->route('admin.usuarios.index')
                         ->with('success', 'Usuário deletado com sucesso!');
    }

    /**
     * Estatísticas gerais
     */
    public function estatisticas()
    {
        // Usuários por mês (últimos 12 meses)
        $usuariosPorMes = User::select(
            DB::raw('DATE_FORMAT(created_at, "%Y-%m") as mes'),
            DB::raw('COUNT(*) as total')
        )
        ->where('created_at', '>=', now()->subMonths(12))
        ->groupBy('mes')
        ->orderBy('mes')
        ->get();

        // Questões por dia (últimos 30 dias)
        $questoesPorDia = Questao::select(
            DB::raw('DATE(created_at) as dia'),
            DB::raw('COUNT(*) as total')
        )
        ->where('created_at', '>=', now()->subDays(30))
        ->groupBy('dia')
        ->orderBy('dia')
        ->get();

        // Simulados por dia (últimos 30 dias)
        $simuladosPorDia = Simulado::select(
            DB::raw('DATE(created_at) as dia'),
            DB::raw('COUNT(*) as total')
        )
        ->where('created_at', '>=', now()->subDays(30))
        ->groupBy('dia')
        ->orderBy('dia')
        ->get();

        // Distribuição por role
        $usuariosPorRole = User::select('role', DB::raw('COUNT(*) as total'))
                                ->groupBy('role')
                                ->get();

        // Top 10 usuários com mais questões
        $topUsuariosQuestoes = User::withCount('questoes')
                                    ->orderBy('questoes_count', 'desc')
                                    ->take(10)
                                    ->get();

        // Top 10 usuários com mais simulados
        $topUsuariosSimulados = User::withCount('simulados')
                                     ->orderBy('simulados_count', 'desc')
                                     ->take(10)
                                     ->get();

        return view('admin.modern-estatisticas', compact(
            'usuariosPorMes',
            'questoesPorDia',
            'simuladosPorDia',
            'usuariosPorRole',
            'topUsuariosQuestoes',
            'topUsuariosSimulados'
        ));
    }

    /**
     * Pagamentos e transações
     */
    public function pagamentos(Request $request)
    {
        $query = PagamentoPix::with('user');

        // Filtro por status
        if ($request->has('status') && $request->status !== '') {
            $query->where('status', $request->status);
        }

        // Filtro por período
        if ($request->has('periodo')) {
            switch ($request->periodo) {
                case 'hoje':
                    $query->whereDate('created_at', today());
                    break;
                case 'semana':
                    $query->where('created_at', '>=', now()->subWeek());
                    break;
                case 'mes':
                    $query->where('created_at', '>=', now()->subMonth());
                    break;
            }
        }

        $pagamentos = $query->latest()->paginate(20);

        $stats = [
            'total_recebido' => PagamentoPix::where('status', 'CONCLUIDA')->sum('valor'),
            'pendentes' => PagamentoPix::where('status', 'PENDENTE')->count(),
            'aprovados' => PagamentoPix::where('status', 'CONCLUIDA')->count(),
            'rejeitados' => PagamentoPix::whereIn('status', ['CANCELADA', 'EXPIRADA'])->count(),
        ];

        return view('admin.modern-pagamentos', compact('pagamentos', 'stats'));
    }

    /**
     * Exibe formulário para enviar e-mail para um usuário específico
     */
    public function enviarEmailForm($id)
    {
        $usuario = User::findOrFail($id);
        return view('admin.usuarios.enviar-email', compact('usuario'));
    }

    /**
     * Envia e-mail para um usuário específico
     */
    public function enviarEmail(Request $request, $id)
    {
        $usuario = User::findOrFail($id);

        $request->validate([
            'assunto' => 'required|string|max:255',
            'mensagem' => 'required|string|min:10',
        ]);

        try {
            Mail::to($usuario->email)->send(
                new AdminNotificationMail(
                    $request->assunto,
                    $request->mensagem,
                    $usuario->name
                )
            );

            return redirect()->back()->with('success', 'E-mail enviado com sucesso para ' . $usuario->email . '!');
        } catch (\Exception $e) {
            return redirect()->back()->with('error', 'Erro ao enviar e-mail: ' . $e->getMessage());
        }
    }

    /**
     * Exibe formulário para enviar e-mail em massa
     */
    public function enviarEmailMassaForm()
    {
        $stats = [
            'total_usuarios' => User::count(),
            'total_alunos' => User::where('role', 'aluno')->count(),
            'total_professores' => User::where('role', 'professor')->count(),
            'total_admins' => User::where('role', 'admin')->count(),
            'usuarios_ativos' => User::where('created_at', '>=', now()->subDays(30))->count(),
        ];

        return view('admin.usuarios.enviar-email-massa', compact('stats'));
    }

    /**
     * Envia e-mail em massa para usuários
     */
    public function enviarEmailMassa(Request $request)
    {
        $request->validate([
            'destinatarios' => 'required|in:todos,alunos,professores,admins,ativos',
            'assunto' => 'required|string|max:255',
            'mensagem' => 'required|string|min:10',
        ]);

        // Seleciona os usuários baseado no filtro
        $query = User::query();

        switch ($request->destinatarios) {
            case 'alunos':
                $query->where('role', 'aluno');
                break;
            case 'professores':
                $query->where('role', 'professor');
                break;
            case 'admins':
                $query->where('role', 'admin');
                break;
            case 'ativos':
                $query->where('created_at', '>=', now()->subDays(30));
                break;
            case 'todos':
            default:
                // Nenhum filtro adicional
                break;
        }

        $usuarios = $query->get();

        if ($usuarios->isEmpty()) {
            return redirect()->back()->with('error', 'Nenhum usuário encontrado com os critérios selecionados.');
        }

        $enviados = 0;
        $erros = 0;

        foreach ($usuarios as $usuario) {
            try {
                Mail::to($usuario->email)->send(
                    new AdminNotificationMail(
                        $request->assunto,
                        $request->mensagem,
                        $usuario->name
                    )
                );
                $enviados++;
            } catch (\Exception $e) {
                $erros++;
                \Log::error('Erro ao enviar e-mail para ' . $usuario->email . ': ' . $e->getMessage());
            }
        }

        $mensagem = "E-mails enviados: {$enviados} de " . $usuarios->count() . " usuários.";
        if ($erros > 0) {
            $mensagem .= " {$erros} e-mails falharam.";
        }

        return redirect()->back()->with('success', $mensagem);
    }
}
