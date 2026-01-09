<!doctype html>
<html lang="fr">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <title>Nexora Admin</title>
    <style>
        body { font-family: ui-sans-serif, system-ui, -apple-system, Segoe UI, Roboto, Helvetica, Arial; margin: 0; background: #f7f7f8; color: #111; }
        header { background: #1F2937; color: #fff; padding: 16px; }
        .container { max-width: 1080px; margin: 24px auto; padding: 0 16px; }
        .grid { display: grid; grid-template-columns: repeat(4, 1fr); gap: 16px; }
        .card { background: #fff; border: 1px solid #e5e7eb; border-radius: 8px; padding: 16px; }
        .card h3 { margin: 0 0 8px 0; font-size: 16px; color: #374151; }
        .value { font-size: 24px; font-weight: 700; }
        table { width: 100%; border-collapse: collapse; }
        th, td { padding: 10px; border-bottom: 1px solid #e5e7eb; text-align: left; }
        .section-title { font-size: 18px; font-weight: 600; margin: 24px 0 12px; }
    </style>
</head>
<body>
    <header>
        <div class="container">
            <h1>Nexora — Panneau d'administration</h1>
        </div>
    </header>
    <div class="container">
        @yield('content')
    </div>
</body>
</html>

