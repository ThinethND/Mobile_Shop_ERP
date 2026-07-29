<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>503 - Service Unavailable</title>

    <!-- Load DezeStore font (same path as app.css) -->
    <style>
        @font-face {
            font-family: 'DezeStoreCustomFont';
            src: url('{{ asset("assets/fonts/DezeStoreCustomFontRegular.otf") }}') format('opentype');
            font-weight: normal;
            font-style: normal;
            font-display: swap;
        }

        :root {
            --background: #ffffff;
            --foreground: #111827;
            --muted-foreground: #6b7280;
            --primary: #38bdf8;
        }

        body {
            margin: 0;
            padding: 0;
            height: 100vh;
            background: var(--background);
            color: var(--foreground);
            font-family:
                DezeStoreCustomFont,
                ui-sans-serif,
                system-ui,
                -apple-system,
                BlinkMacSystemFont,
                "Segoe UI",
                Roboto,
                "Helvetica Neue",
                Arial,
                sans-serif;
            display: flex;
            align-items: center;
            justify-content: center;
            text-align: center;
            overflow: hidden;
        }

        .container {
            z-index: 10;
            padding: 1.5rem;
            position: relative;
        }

        h1 {
            font-size: 2rem;
            font-weight: 700;
            margin-bottom: 0.5rem;
            letter-spacing: -0.01em;
        }

        p.subtitle {
            font-size: 1rem;
            color: var(--muted-foreground);
            margin-bottom: 1.75rem;
        }

        .refresh-btn {
            background: #000;
            color: #fff;
            padding: 0.75rem 2rem;
            border-radius: 9999px;
            font-size: 0.95rem;
            font-weight: 600;
            text-decoration: none;
            display: inline-block;
            transition: background 0.2s ease;
        }

        .refresh-btn:hover {
            background: #1b1b1b;
        }

        /* Huge faint 503 background */
        .big-503 {
            position: absolute;
            bottom:50px;
            left: 50%;
            transform: translateX(-50%);
            font-size: 33vw;
            font-weight: 800;
            color: rgba(56, 189, 248, 0.04);
            user-select: none;
            pointer-events: none;
            line-height: 1;
        }

        @media (max-width: 640px) {
            h1 { font-size: 1.6rem; }
            p.subtitle { font-size: 0.9rem; }
            .big-503 { font-size: 50vw; }
        }
    </style>
</head>

<body>

    <div class="container">
        <h1>We’ll be right back</h1>
        <p class="subtitle">The site is undergoing scheduled maintenance.</p>

        <a href="{{ url('/') }}" class="refresh-btn">Refresh</a>
    </div>

    <div class="big-503">503</div>

</body>
</html>
