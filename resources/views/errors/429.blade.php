<!DOCTYPE html>
<html lang="pt-BR">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Muitas tentativas — Marthina Learning</title>
    <link href="https://fonts.googleapis.com/css2?family=Baloo+2:wght@600;700&family=Nunito:wght@400;600;700&display=swap" rel="stylesheet">
    <style>
        body {
            margin: 0;
            min-height: 100vh;
            display: flex;
            align-items: center;
            justify-content: center;
            font-family: 'Nunito', sans-serif;
            color: #213547;
            background: linear-gradient(180deg, #f2f8ff 0%, #fff8ef 52%, #fffdf8 100%);
            padding: 1.5rem;
        }
        .card {
            background: rgba(255, 255, 255, 0.92);
            border-radius: 28px;
            box-shadow: 0 20px 45px rgba(79, 141, 253, 0.14);
            max-width: 460px;
            width: 100%;
            padding: 2.5rem 2rem;
            text-align: center;
        }
        .emoji { font-size: 3rem; line-height: 1; margin-bottom: 0.75rem; }
        h1 { font-family: 'Baloo 2', cursive; font-size: 1.6rem; margin: 0 0 0.75rem; }
        p { color: #6b7280; font-size: 1.05rem; margin: 0 0 1.5rem; }
        a {
            display: inline-block;
            background: linear-gradient(135deg, #4f8dfd, #6da7ff);
            color: #fff;
            font-weight: 800;
            text-decoration: none;
            border-radius: 999px;
            padding: 0.75rem 1.75rem;
            box-shadow: 0 12px 24px rgba(79, 141, 253, 0.28);
        }
    </style>
</head>
<body>
    <div class="card">
        <div class="emoji">⏳</div>
        <h1>Calma, aventureiro!</h1>
        <p>Voce fez muitas tentativas em pouco tempo. Aguarde cerca de um minuto e tente de novo.</p>
        <a href="{{ url('/login') }}">Voltar ao login</a>
    </div>
</body>
</html>
