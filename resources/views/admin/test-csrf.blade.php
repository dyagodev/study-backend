<!DOCTYPE html>
<html lang="pt-BR">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <meta name="csrf-token" content="{{ csrf_token() }}">
    <title>Teste CSRF</title>
</head>
<body>
    <h1>Teste de CSRF Token</h1>

    <p><strong>CSRF Token:</strong> {{ csrf_token() }}</p>
    <p><strong>Session ID:</strong> {{ session()->getId() }}</p>
    <p><strong>Session Driver:</strong> {{ config('session.driver') }}</p>

    <hr>

    <form method="POST" action="{{ route('admin.login') }}">
        @csrf
        <p>Email: <input type="email" name="email" value="admin@study.com"></p>
        <p>Senha: <input type="password" name="password" value="admin123"></p>
        <p><button type="submit">Testar Login</button></p>
    </form>

    <hr>

    <p>Se você ver um token acima e conseguir enviar o formulário sem erro 419, então o CSRF está funcionando.</p>
</body>
</html>
