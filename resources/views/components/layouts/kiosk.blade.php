<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1, viewport-fit=cover">
    <meta name="csrf-token" content="{{ csrf_token() }}">
    <title>{{ $title ?? 'El Solar Uruguay — Registro' }}</title>
    <link rel="icon" type="image/svg+xml" href="{{ asset('images/sol-elsolar.svg') }}">
    @livewireStyles
    <style>
        :root {
            --brand: #a10a6b;
            --brand-dark: #7d0853;
            --brand-soft: #f7e6f1;
            --ink: #1f2430;
            --muted: #6b7280;
            --ok: #157347;
            --ok-soft: #d1e7dd;
            --err: #b02a37;
            --err-soft: #f8d7da;
            --line: #e6e6ef;
            --bg: #f2f2f6;
        }

        * { box-sizing: border-box; }

        html, body {
            margin: 0;
            padding: 0;
            background: var(--bg);
            color: var(--ink);
            font-family: ui-sans-serif, system-ui, -apple-system, "Segoe UI", Roboto, Arial, sans-serif;
            -webkit-text-size-adjust: 100%;
        }

        .kiosk-shell { min-height: 100vh; display: flex; flex-direction: column; }

        .kiosk-header {
            background: var(--brand);
            color: #fff;
            display: flex;
            align-items: center;
            gap: 16px;
            padding: 14px 22px;
            box-shadow: 0 2px 10px rgba(0, 0, 0, .12);
        }
        .sol-icon { display: block; width: 100%; height: 100%; }
        .kiosk-sun { display: inline-flex; width: 46px; height: 46px; color: #fff; flex: 0 0 auto; }
        .kiosk-header .brand-name { font-size: 1.35rem; font-weight: 800; letter-spacing: .3px; }
        .kiosk-header .spacer { flex: 1; }
        .kiosk-header .who {
            font-size: .85rem; opacity: .95; text-align: right; line-height: 1.25;
        }
        .kiosk-header .who a { color: #fff; text-decoration: underline; }

        .kiosk-main {
            flex: 1;
            width: 100%;
            max-width: 1100px;
            margin: 0 auto;
            padding: 22px 18px 40px;
        }

        .panel {
            background: #fff;
            border: 1px solid var(--line);
            border-radius: 16px;
            box-shadow: 0 6px 24px rgba(31, 36, 48, .06);
            padding: 20px;
        }

        .grid-top { display: grid; grid-template-columns: 1fr; gap: 22px; }
        @media (min-width: 820px) {
            .grid-top { grid-template-columns: 360px 1fr; align-items: start; }
        }

        .dni-display {
            width: 100%;
            font-size: 2.6rem;
            font-weight: 800;
            text-align: center;
            letter-spacing: .18em;
            padding: 16px 12px;
            border: 3px solid var(--brand-soft);
            border-radius: 14px;
            background: #fafafa;
            color: var(--ink);
            min-height: 74px;
        }
        .dni-label { font-size: .8rem; color: var(--muted); text-transform: uppercase; letter-spacing: .1em; margin: 0 0 6px; }

        .keypad {
            display: grid;
            grid-template-columns: repeat(3, 1fr);
            gap: 12px;
            margin-top: 16px;
        }
        .key {
            font-size: 1.8rem;
            font-weight: 700;
            padding: 20px 0;
            border: 1px solid var(--line);
            border-radius: 14px;
            background: #fff;
            color: var(--ink);
            cursor: pointer;
            user-select: none;
            transition: transform .05s ease, background .12s ease;
            min-height: 68px;
        }
        .key:active { transform: translateY(1px); background: var(--brand-soft); }
        .key.wide { grid-column: span 2; }
        .key.clear { background: var(--err-soft); color: var(--err); border-color: #f1b0b7; }

        .actions { display: grid; grid-template-columns: 1fr 1fr; gap: 16px; margin-top: 16px; }
        .btn-big {
            font-size: 1.5rem;
            font-weight: 800;
            letter-spacing: .04em;
            padding: 26px 0;
            border: none;
            border-radius: 16px;
            color: #fff;
            cursor: pointer;
            box-shadow: 0 6px 16px rgba(0, 0, 0, .14);
            transition: transform .05s ease, filter .12s ease;
        }
        .btn-big:active { transform: translateY(1px); filter: brightness(.96); }
        .btn-entrada { background: var(--ok); }
        .btn-salida { background: var(--brand); }

        .flash { border-radius: 12px; padding: 14px 16px; font-size: 1.15rem; font-weight: 700; text-align: center; margin-bottom: 16px; }
        .flash.ok { background: var(--ok-soft); color: var(--ok); border: 1px solid #a3cfbb; }
        .flash.err { background: var(--err-soft); color: var(--err); border: 1px solid #f1b0b7; }

        .list-title { font-size: 1.05rem; font-weight: 800; margin: 4px 0 12px; color: var(--brand-dark); }

        .table-wrap { overflow-x: auto; }
        table.fichajes { width: 100%; border-collapse: collapse; font-size: .95rem; min-width: 640px; }
        table.fichajes th, table.fichajes td { padding: 10px 12px; text-align: left; border-bottom: 1px solid var(--line); vertical-align: middle; }
        table.fichajes th { font-size: .72rem; text-transform: uppercase; letter-spacing: .08em; color: var(--muted); background: #faf7fb; }
        table.fichajes tr:nth-child(even) td { background: #fcfbfd; }

        .badge { display: inline-block; padding: 4px 12px; border-radius: 999px; font-size: .75rem; font-weight: 800; letter-spacing: .05em; }
        .badge.entrada { background: var(--ok-soft); color: var(--ok); }
        .badge.salida { background: var(--brand-soft); color: var(--brand-dark); }

        .obs-text { color: var(--muted); font-size: .85rem; }
        .btn-obs {
            font-size: .8rem; font-weight: 700; padding: 8px 14px; border-radius: 10px;
            border: 1px solid var(--brand-soft); background: #fff; color: var(--brand-dark); cursor: pointer;
        }
        .btn-obs:active { background: var(--brand-soft); }
        .obs-editor textarea { width: 100%; min-height: 70px; border: 1px solid var(--line); border-radius: 10px; padding: 8px; font: inherit; }
        .obs-editor .row { display: flex; gap: 8px; margin-top: 8px; }
        .btn-save { background: var(--brand); color: #fff; border: none; border-radius: 10px; padding: 8px 16px; font-weight: 700; cursor: pointer; }
        .btn-cancel { background: #fff; color: var(--muted); border: 1px solid var(--line); border-radius: 10px; padding: 8px 16px; cursor: pointer; }

        /* Standalone login */
        .login-wrap { min-height: 100vh; display: flex; align-items: center; justify-content: center; padding: 20px; }
        .login-card {
            width: 100%; max-width: 400px; background: #fff; border-radius: 18px; overflow: hidden;
            box-shadow: 0 12px 34px rgba(31, 36, 48, .14);
        }
        .login-brand {
            background: linear-gradient(135deg, var(--brand), var(--brand-dark));
            color: #fff; text-align: center; padding: 30px 24px 24px;
        }
        .login-sun { display: inline-flex; width: 60px; height: 60px; color: #fff; }
        .login-brand .name { font-weight: 800; font-size: 1.4rem; margin-top: 12px; letter-spacing: .3px; }
        .login-brand .tag { font-size: .72rem; opacity: .92; margin-top: 6px; line-height: 1.35; }
        .login-body { padding: 26px 24px 28px; }
        .field { margin-bottom: 14px; }
        .field label { display: block; font-size: .8rem; font-weight: 700; color: var(--muted); margin-bottom: 6px; }
        .field input { width: 100%; padding: 12px 14px; border: 1px solid var(--line); border-radius: 10px; font-size: 1rem; }
        .field .error { color: var(--err); font-size: .82rem; margin-top: 6px; }
        .btn-login { width: 100%; background: var(--brand); color: #fff; border: none; border-radius: 10px; padding: 14px; font-size: 1.05rem; font-weight: 800; cursor: pointer; }
    </style>
</head>
<body>
    {{ $slot }}
    @livewireScripts
</body>
</html>
