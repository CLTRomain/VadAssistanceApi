<!DOCTYPE html>
<html lang="fr">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Réinitialisation du mot de passe - VAD Assistance</title>
    <style>
        * { box-sizing: border-box; margin: 0; padding: 0; }
        body {
            font-family: -apple-system, BlinkMacSystemFont, 'Segoe UI', sans-serif;
            background: #f5f5f5;
            display: flex;
            align-items: center;
            justify-content: center;
            min-height: 100vh;
            padding: 20px;
        }
        .card {
            background: white;
            border-radius: 12px;
            padding: 40px;
            max-width: 420px;
            width: 100%;
            box-shadow: 0 4px 20px rgba(0,0,0,0.08);
        }
        .logo {
            text-align: center;
            margin-bottom: 30px;
            font-size: 22px;
            font-weight: 700;
            color: #f97316;
        }
        h1 { font-size: 20px; color: #1a1a1a; margin-bottom: 8px; }
        p { color: #666; font-size: 14px; margin-bottom: 24px; }
        label { display: block; font-size: 14px; font-weight: 500; color: #333; margin-bottom: 6px; }
        input[type="password"] {
            width: 100%;
            padding: 12px 16px;
            border: 1.5px solid #e0e0e0;
            border-radius: 8px;
            font-size: 15px;
            outline: none;
            transition: border-color 0.2s;
            margin-bottom: 16px;
        }
        input[type="password"]:focus { border-color: #f97316; }
        button {
            width: 100%;
            padding: 13px;
            background: #f97316;
            color: white;
            border: none;
            border-radius: 8px;
            font-size: 16px;
            font-weight: 600;
            cursor: pointer;
            transition: background 0.2s;
        }
        button:hover { background: #ea6c0a; }
        .message {
            padding: 12px 16px;
            border-radius: 8px;
            margin-bottom: 20px;
            font-size: 14px;
        }
        .message.success { background: #f0fdf4; color: #16a34a; border: 1px solid #bbf7d0; }
        .message.error { background: #fef2f2; color: #dc2626; border: 1px solid #fecaca; }
    </style>
</head>
<body>
    <div class="card">
        <div class="logo">VAD Assistance</div>

        <?php if (!empty($message)): ?>
            <div class="message <?= $messageType ?>">
                <?= htmlspecialchars($message) ?>
            </div>
        <?php endif; ?>

        <?php if ($showForm): ?>
            <h1>Nouveau mot de passe</h1>
            <p>Choisissez un mot de passe sécurisé d'au moins 8 caractères.</p>

            <form method="POST" action="/subscribers/reset-password?token=<?= htmlspecialchars($token) ?>">
                <label>Nouveau mot de passe</label>
                <input type="password" name="password" placeholder="••••••••" required minlength="8">

                <label>Confirmer le mot de passe</label>
                <input type="password" name="password_confirm" placeholder="••••••••" required minlength="8">

                <button type="submit">Réinitialiser le mot de passe</button>
            </form>
        <?php endif; ?>
    </div>
</body>
</html>
