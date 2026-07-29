<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <meta name="robots" content="noindex, nofollow">
    <title>DezeStore | Maintenance</title>
    <style>
        :root {
            color-scheme: light;
            --ink: #0f172a;
            --muted: #64748b;
            --line: #d7e0ea;
            --blue: #075985;
            --green: #16a34a;
            --paper: #ffffff;
            --wash: #f8fafc;
        }

        * {
            box-sizing: border-box;
        }

        html,
        body {
            min-height: 100%;
            margin: 0;
        }

        body {
            min-height: 100vh;
            overflow-x: hidden;
            background: var(--wash);
            color: var(--ink);
            font-family: Inter, ui-sans-serif, system-ui, -apple-system, BlinkMacSystemFont, "Segoe UI", sans-serif;
        }

        .page {
            width: 100%;
            min-height: 100vh;
        }

        .shell {
            display: grid;
            grid-template-columns: minmax(0, 1fr) minmax(360px, 40vw);
            min-height: 100vh;
            background: var(--paper);
        }

        .content {
            display: flex;
            flex-direction: column;
            justify-content: center;
            padding: 72px min(8vw, 96px);
        }

        .logo {
            width: 216px;
            max-width: 74%;
            height: auto;
            margin-bottom: 56px;
            object-fit: contain;
        }

        .eyebrow {
            display: inline-flex;
            width: fit-content;
            align-items: center;
            gap: 10px;
            margin: 0 0 18px;
            padding: 8px 12px;
            border: 1px solid rgba(2, 132, 199, 0.18);
            border-radius: 8px;
            background: #eff6ff;
            color: var(--blue);
            font-size: 12px;
            font-weight: 800;
            letter-spacing: 0;
            text-transform: uppercase;
        }

        .pulse {
            width: 8px;
            height: 8px;
            border-radius: 999px;
            background: var(--green);
            box-shadow: 0 0 0 rgba(22, 163, 74, 0.34);
            animation: pulse 1.7s ease-out infinite;
        }

        h1 {
            max-width: 720px;
            margin: 0;
            color: var(--ink);
            font-size: 76px;
            font-weight: 900;
            line-height: 0.98;
            letter-spacing: 0;
        }

        .copy {
            max-width: 560px;
            margin: 22px 0 0;
            color: var(--muted);
            font-size: 18px;
            line-height: 1.7;
        }

        .timer {
            display: grid;
            grid-template-columns: repeat(4, minmax(72px, 1fr));
            gap: 12px;
            width: min(100%, 520px);
            margin-top: 34px;
        }

        .time-box {
            min-height: 94px;
            padding: 16px 12px;
            border: 1px solid var(--line);
            border-radius: 8px;
            background: #ffffff;
            text-align: center;
        }

        .time-value {
            display: block;
            color: var(--ink);
            font-size: 38px;
            font-weight: 900;
            line-height: 1;
            font-variant-numeric: tabular-nums;
        }

        .time-label {
            display: block;
            margin-top: 10px;
            color: #64748b;
            font-size: 11px;
            font-weight: 800;
            letter-spacing: 0;
            text-transform: uppercase;
        }

        .visual {
            position: relative;
            min-height: 420px;
            overflow: hidden;
            background: linear-gradient(145deg, #071f4f 0%, #075985 55%, #0f766e 100%);
        }

        .status-card {
            position: absolute;
            inset: auto 48px 48px;
            z-index: 2;
            padding: 24px;
            border: 1px solid rgba(255, 255, 255, 0.18);
            border-radius: 8px;
            background: rgba(255, 255, 255, 0.14);
            color: #ffffff;
            backdrop-filter: blur(14px);
        }

        .infinity {
            position: relative;
            z-index: 1;
            display: grid;
            height: 100%;
            min-height: inherit;
            place-items: center;
            color: rgba(255, 255, 255, 0.84);
            font-size: 220px;
            font-weight: 800;
            line-height: 1;
            animation: breathe 3.6s ease-in-out infinite;
        }

        .status-title {
            margin: 0;
            font-size: 18px;
            font-weight: 900;
        }

        .status-copy {
            margin: 10px 0 0;
            color: rgba(255, 255, 255, 0.78);
            font-size: 14px;
            line-height: 1.7;
        }

        @keyframes pulse {
            0% {
                box-shadow: 0 0 0 0 rgba(22, 163, 74, 0.34);
            }
            70% {
                box-shadow: 0 0 0 12px rgba(22, 163, 74, 0);
            }
            100% {
                box-shadow: 0 0 0 0 rgba(22, 163, 74, 0);
            }
        }

        @keyframes breathe {
            0%,
            100% {
                opacity: 0.76;
                transform: scale(0.98);
            }
            50% {
                opacity: 1;
                transform: scale(1.02);
            }
        }

        @media (max-width: 820px) {
            .shell {
                grid-template-columns: 1fr;
            }

            .content {
                padding: 36px 24px 32px;
            }

            .logo {
                width: 188px;
                margin-bottom: 34px;
            }

            h1 {
                font-size: 44px;
            }

            .copy {
                font-size: 16px;
            }

            .visual {
                min-height: 300px;
            }

            .infinity {
                font-size: 132px;
            }

            .status-card {
                inset: auto 22px 22px;
            }
        }

        @media (max-width: 520px) {
            .timer {
                grid-template-columns: repeat(2, minmax(0, 1fr));
            }

            .time-box {
                min-height: 86px;
            }

            .time-value {
                font-size: 32px;
            }
        }
    </style>
</head>
<body>
    <main class="page" aria-label="DezeStore maintenance notice">
        <section class="shell">
            <div class="content">
                <img class="logo" src="/images/dezestoreblack.png" alt="DezeStore">

                <p class="eyebrow"><span class="pulse" aria-hidden="true"></span> Maintenance in progress</p>
                <h1>We will be back soon.</h1>
                <p class="copy">
                    DezeStore is getting a quick update. Orders, products, and the shopping experience will return shortly.
                </p>

                <div class="timer" aria-label="Maintenance timer" aria-live="polite">
                    <div class="time-box">
                        <span id="days" class="time-value">00</span>
                        <span class="time-label">Days</span>
                    </div>
                    <div class="time-box">
                        <span id="hours" class="time-value">00</span>
                        <span class="time-label">Hours</span>
                    </div>
                    <div class="time-box">
                        <span id="minutes" class="time-value">00</span>
                        <span class="time-label">Minutes</span>
                    </div>
                    <div class="time-box">
                        <span id="seconds" class="time-value">00</span>
                        <span class="time-label">Seconds</span>
                    </div>
                </div>
            </div>

            <div class="visual" aria-hidden="true">
                <div class="infinity">&infin;</div>
                <div class="status-card">
                    <p class="status-title">System update active</p>
                    <p class="status-copy">The storefront is temporarily covered while the live build is refreshed.</p>
                </div>
            </div>
        </section>
    </main>

    <script>
        (function () {
            var startedAt = Date.now();
            var days = document.getElementById('days');
            var hours = document.getElementById('hours');
            var minutes = document.getElementById('minutes');
            var seconds = document.getElementById('seconds');

            function pad(value) {
                return String(value).padStart(2, '0');
            }

            function tick() {
                var elapsed = Math.floor((Date.now() - startedAt) / 1000);
                var dayCount = Math.floor(elapsed / 86400);
                var hourCount = Math.floor((elapsed % 86400) / 3600);
                var minuteCount = Math.floor((elapsed % 3600) / 60);
                var secondCount = elapsed % 60;

                days.textContent = pad(dayCount);
                hours.textContent = pad(hourCount);
                minutes.textContent = pad(minuteCount);
                seconds.textContent = pad(secondCount);
            }

            tick();
            window.setInterval(tick, 1000);
        })();
    </script>
</body>
</html>
