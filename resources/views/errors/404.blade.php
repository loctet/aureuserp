<!DOCTYPE html>
<html lang="{{ str_replace('_', '-', app()->getLocale()) }}">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <title>404 - Page Not Found</title>
    <link rel="icon" type="image/x-icon" href="{{ asset('favicon.ico') }}">
    <link rel="shortcut icon" href="{{ asset('favicon.ico') }}">
    <style>
        :root {
            --bg: #10223f;
            --bg-accent: #24356c;
            --card: rgba(255, 255, 255, 0.08);
            --card-border: rgba(255, 255, 255, 0.2);
            --text: #f8fafc;
            --muted: #cbd5e1;
            --primary: #3f94d3;
            --primary-hover: #247cbc;
            --secondary: rgba(255, 255, 255, 0.14);
            --secondary-hover: rgba(255, 255, 255, 0.2);
        }

        * { box-sizing: border-box; }

        body {
            margin: 0;
            min-height: 100vh;
            font-family: Inter, "Segoe UI", Roboto, Helvetica, Arial, sans-serif;
            color: var(--text);
            background:
                radial-gradient(900px 500px at 20% -10%, #3f94d3 0%, transparent 60%),
                radial-gradient(800px 400px at 100% 0%, #83afdf 0%, transparent 55%),
                linear-gradient(135deg, var(--bg) 0%, var(--bg-accent) 100%);
            display: grid;
            place-items: center;
            padding: 24px;
        }

        .card {
            width: 100%;
            max-width: 760px;
            border: 1px solid var(--card-border);
            background: var(--card);
            backdrop-filter: blur(8px);
            border-radius: 20px;
            padding: 40px 32px;
            box-shadow: 0 20px 60px rgba(0, 0, 0, 0.35);
            text-align: center;
        }

        .badge {
            display: inline-block;
            padding: 6px 12px;
            border-radius: 999px;
            font-size: 12px;
            letter-spacing: .08em;
            text-transform: uppercase;
            background: rgba(63, 148, 211, 0.18);
            border: 1px solid rgba(131, 175, 223, 0.55);
            color: #dbeafe;
            margin-bottom: 16px;
        }

        .logo {
            margin: 0 auto 14px;
            width: min(320px, 90%);
            height: auto;
            filter: drop-shadow(0 8px 20px rgba(0, 0, 0, 0.25));
        }

        .code {
            font-size: clamp(48px, 12vw, 110px);
            line-height: 1;
            margin: 4px 0 12px;
            font-weight: 800;
            letter-spacing: -0.03em;
        }

        h1 {
            font-size: clamp(24px, 4vw, 36px);
            margin: 0 0 12px;
            letter-spacing: -0.02em;
        }

        p {
            margin: 0 auto;
            max-width: 560px;
            color: var(--muted);
            font-size: 16px;
            line-height: 1.65;
        }

        .actions {
            margin-top: 30px;
            display: flex;
            justify-content: center;
            gap: 12px;
            flex-wrap: wrap;
        }

        .btn {
            appearance: none;
            border: 0;
            text-decoration: none;
            border-radius: 12px;
            padding: 12px 18px;
            font-weight: 600;
            font-size: 14px;
            transition: .2s ease;
            display: inline-flex;
            align-items: center;
            justify-content: center;
            min-width: 150px;
        }

        .btn-primary {
            background: var(--primary);
            color: white;
        }

        .btn-primary:hover {
            background: var(--primary-hover);
            transform: translateY(-1px);
        }

        .btn-secondary {
            background: var(--secondary);
            color: var(--text);
            border: 1px solid rgba(255, 255, 255, 0.25);
        }

        .btn-secondary:hover {
            background: var(--secondary-hover);
            transform: translateY(-1px);
        }

        .hint {
            margin-top: 18px;
            font-size: 13px;
            color: #94a3b8;
        }
    </style>
</head>
<body>
    <main class="card">
        <picture>
            <source srcset="{{ asset('images/logo-full-dark.svg') }}" media="(prefers-color-scheme: dark)">
            <img class="logo" src="{{ asset('images/logo-full-light.svg') }}" alt="AureusERP">
        </picture>
        <span class="badge">404 Error</span>
        <div class="code">404</div>
        <h1>Page Not Found</h1>
        <p>
            The page you are looking for does not exist, may have been moved, or the URL might be incorrect.
            Please go back to a known page and continue from there.
        </p>

        <div class="actions">
            <a class="btn btn-primary" href="{{ url('/') }}">Go to Dashboard</a>
            <a class="btn btn-secondary" href="javascript:history.back()">Go Back</a>
        </div>

        <div class="hint">If this keeps happening, contact your administrator.</div>
    </main>
</body>
</html>
