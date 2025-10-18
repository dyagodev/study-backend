<!DOCTYPE html>
<html lang="pt-BR">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Bem-vindo</title>
    <style>
        body {
            font-family: -apple-system, BlinkMacSystemFont, 'Segoe UI', Roboto, Helvetica, Arial, sans-serif;
            line-height: 1.6;
            color: #333;
            max-width: 600px;
            margin: 0 auto;
            padding: 20px;
        }
        .container {
            background-color: #ffffff;
            border-radius: 8px;
            padding: 40px;
            box-shadow: 0 2px 4px rgba(0, 0, 0, 0.1);
        }
        .header {
            text-align: center;
            margin-bottom: 30px;
        }
        .header h1 {
            color: #4F46E5;
            margin: 0;
            font-size: 28px;
        }
        .content {
            margin: 30px 0;
        }
        .highlight {
            background-color: #F3F4F6;
            padding: 20px;
            border-radius: 6px;
            margin: 20px 0;
        }
        .button {
            display: inline-block;
            padding: 12px 30px;
            background-color: #4F46E5;
            color: #ffffff;
            text-decoration: none;
            border-radius: 6px;
            margin: 20px 0;
            font-weight: 600;
        }
        .footer {
            margin-top: 40px;
            padding-top: 20px;
            border-top: 1px solid #E5E7EB;
            font-size: 14px;
            color: #6B7280;
            text-align: center;
        }
        .features {
            margin: 30px 0;
        }
        .feature-item {
            padding: 15px 0;
            border-bottom: 1px solid #E5E7EB;
        }
        .feature-item:last-child {
            border-bottom: none;
        }
        .feature-icon {
            font-size: 20px;
            margin-right: 10px;
        }
    </style>
</head>
<body>
    <div class="container">
        <div class="header">
            <h1>🎓 Bem-vindo ao {{ config('app.name', 'Seu Estudo') }}!</h1>
        </div>

        <div class="content">
            <p>Olá <strong>{{ $user->name }}</strong>,</p>

            <p>Estamos muito felizes em ter você conosco! Sua conta foi criada com sucesso e agora você tem acesso a uma plataforma completa de estudos.</p>

            <div class="highlight">
                <p><strong>📧 Email:</strong> {{ $user->email }}</p>
                <p><strong>📅 Data de cadastro:</strong> {{ $user->created_at->format('d/m/Y H:i') }}</p>
                @if($user->creditos > 0)
                <p><strong>💳 Créditos disponíveis:</strong> {{ $user->creditos }}</p>
                @endif
            </div>

            <div class="features">
                <h3>O que você pode fazer agora:</h3>

                <div class="feature-item">
                    <span class="feature-icon">📝</span>
                    <strong>Criar simulados personalizados</strong> - Monte seus próprios testes com questões de diversos temas
                </div>

                <div class="feature-item">
                    <span class="feature-icon">🎯</span>
                    <strong>Acompanhar seu desempenho</strong> - Veja suas estatísticas e identifique pontos de melhoria
                </div>

                <div class="feature-item">
                    <span class="feature-icon">💎</span>
                    <strong>Gerar questões com IA</strong> - Use créditos para criar questões personalizadas
                </div>

                <div class="feature-item">
                    <span class="feature-icon">🎨</span>
                    <strong>Personalizar temas</strong> - Customize a aparência da plataforma ao seu gosto
                </div>
            </div>

            <div style="text-align: center;">
                <a href="{{ config('app.url') }}" class="button">Começar a Estudar</a>
            </div>

            <p style="margin-top: 30px;">Se você tiver alguma dúvida ou precisar de ajuda, nossa equipe de suporte está sempre disponível para ajudá-lo.</p>

            <p>Bons estudos! 🚀</p>
        </div>

        <div class="footer">
            <p>© {{ date('Y') }} {{ config('app.name', 'Study') }}. Todos os direitos reservados.</p>
            <p style="font-size: 12px; margin-top: 10px;">Você está recebendo este email porque se cadastrou em nossa plataforma.</p>
        </div>
    </div>
</body>
</html>
