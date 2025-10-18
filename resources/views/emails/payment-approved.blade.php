<!DOCTYPE html>
<html lang="pt-BR">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Pagamento Aprovado</title>
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
            color: #10B981;
            margin: 0;
            font-size: 28px;
        }
        .success-icon {
            font-size: 60px;
            text-align: center;
            margin: 20px 0;
        }
        .content {
            margin: 30px 0;
        }
        .payment-details {
            background-color: #F0FDF4;
            border: 2px solid #10B981;
            padding: 25px;
            border-radius: 8px;
            margin: 25px 0;
        }
        .payment-row {
            display: flex;
            justify-content: space-between;
            padding: 10px 0;
            border-bottom: 1px solid #D1FAE5;
        }
        .payment-row:last-child {
            border-bottom: none;
            font-weight: bold;
            font-size: 18px;
            color: #10B981;
            padding-top: 15px;
        }
        .payment-label {
            color: #6B7280;
        }
        .payment-value {
            font-weight: 600;
            color: #111827;
        }
        .button {
            display: inline-block;
            padding: 12px 30px;
            background-color: #10B981;
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
        .info-box {
            background-color: #EFF6FF;
            border-left: 4px solid #3B82F6;
            padding: 15px;
            margin: 20px 0;
            border-radius: 4px;
        }
        .info-box p {
            margin: 5px 0;
        }
    </style>
</head>
<body>
    <div class="container">
        <div class="success-icon">
            ✅
        </div>

        <div class="header">
            <h1>Pagamento Aprovado!</h1>
        </div>

        <div class="content">
            <p>Olá <strong>{{ $pagamento->user->name }}</strong>,</p>

            <p>Ótimas notícias! Seu pagamento foi aprovado com sucesso e seus créditos já foram adicionados à sua conta.</p>

            <div class="payment-details">
                <div class="payment-row">
                    <span class="payment-label">ID da Transação:</span>
                    <span class="payment-value">#{{ $pagamento->id }}</span>
                </div>
                <div class="payment-row">
                    <span class="payment-label">Data do Pagamento:</span>
                    <span class="payment-value">{{ $pagamento->updated_at->format('d/m/Y H:i') }}</span>
                </div>
                <div class="payment-row">
                    <span class="payment-label">Valor Pago:</span>
                    <span class="payment-value">R$ {{ number_format($pagamento->valor, 2, ',', '.') }}</span>
                </div>
                <div class="payment-row">
                    <span class="payment-label">Método de Pagamento:</span>
                    <span class="payment-value">PIX</span>
                </div>
                <div class="payment-row">
                    <span class="payment-label">Créditos Adicionados:</span>
                    <span class="payment-value">{{ $pagamento->creditos }} créditos</span>
                </div>
            </div>

            <div class="info-box">
                <p><strong>💳 Saldo atual de créditos:</strong> {{ $pagamento->user->creditos }} créditos</p>
                <p style="margin-top: 10px; font-size: 14px; color: #6B7280;">
                    Você pode usar seus créditos para gerar questões personalizadas com IA e aproveitar outros recursos premium da plataforma.
                </p>
            </div>

            <div style="text-align: center;">
                <a href="https://seuestudo.com" class="button">Acessar Plataforma</a>
            </div>

            <p style="margin-top: 30px;">Obrigado por confiar em nossos serviços. Desejamos ótimos estudos! 📚</p>

            <p style="font-size: 14px; color: #6B7280; margin-top: 20px;">
                <strong>Dúvidas sobre seu pagamento?</strong><br>
                Entre em contato com nosso suporte. Estamos aqui para ajudar!
            </p>
        </div>

        <div class="footer">
            <p>© {{ date('Y') }} {{ config('app.name', 'Study') }}. Todos os direitos reservados.</p>
            <p style="font-size: 12px; margin-top: 10px;">Este é um recibo automático de confirmação de pagamento.</p>
        </div>
    </div>
</body>
</html>
