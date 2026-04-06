<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1, viewport-fit=cover">
    <title>{{ config('app.name').' - '.($pageTitle ?? 'Verification') }}</title>
    <style>
        :root {
            color-scheme: light;
            --bg: #f5f5f2;
            --surface: #ffffff;
            --surface-soft: #f3f3ef;
            --text: #111111;
            --muted: #646464;
            --border: #ddddda;
            --border-strong: #cfcfca;
            --success: #146c43;
            --success-bg: #edf8f1;
            --warning: #925f00;
            --warning-bg: #fff7e4;
            --danger: #8d1d1d;
            --danger-bg: #fff1f1;
            --shadow: 0 20px 44px rgba(0, 0, 0, 0.07);
            --radius-lg: 28px;
            --radius-md: 18px;
        }

        * {
            box-sizing: border-box;
        }

        html, body {
            margin: 0;
            min-height: 100%;
            background:
                radial-gradient(circle at top left, rgba(17, 17, 17, 0.05), transparent 32%),
                linear-gradient(180deg, #fafaf8 0%, var(--bg) 100%);
            color: var(--text);
            font-family: -apple-system, BlinkMacSystemFont, "Segoe UI", sans-serif;
        }

        body {
            padding: 20px 14px 32px;
        }

        .shell {
            max-width: 560px;
            margin: 0 auto;
        }

        .hero {
            padding: 10px 4px 18px;
        }

        .brand {
            margin: 4px 0 22px;
            text-align: center;
            font-size: 15px;
            font-weight: 800;
            letter-spacing: 0.04em;
            text-transform: uppercase;
        }

        .eyebrow {
            margin: 0 0 10px;
            font-size: 12px;
            font-weight: 700;
            letter-spacing: 0.14em;
            text-transform: uppercase;
            color: var(--muted);
        }

        h1 {
            margin: 0;
            font-size: clamp(28px, 6vw, 38px);
            line-height: 1.05;
            letter-spacing: -0.04em;
        }

        .subtitle {
            margin: 12px 0 0;
            font-size: 15px;
            line-height: 1.65;
            color: var(--muted);
        }

        .card {
            background: var(--surface);
            border: 1px solid rgba(221, 221, 218, 0.9);
            border-radius: var(--radius-lg);
            box-shadow: var(--shadow);
            padding: 22px 18px;
        }

        .stack {
            display: grid;
            gap: 16px;
        }

        .meta-bar {
            display: flex;
            flex-wrap: wrap;
            gap: 10px;
            margin-top: 18px;
        }

        .pill {
            display: inline-flex;
            align-items: center;
            gap: 8px;
            padding: 10px 14px;
            border-radius: 999px;
            background: var(--surface-soft);
            color: var(--text);
            font-size: 13px;
            font-weight: 600;
        }

        .section {
            display: grid;
            gap: 12px;
        }

        .section h2 {
            margin: 0;
            font-size: 18px;
            letter-spacing: -0.03em;
        }

        .section p {
            margin: 0;
            color: var(--muted);
            line-height: 1.55;
            font-size: 14px;
        }

        .notice {
            padding: 14px 16px;
            border-radius: 18px;
            border: 1px solid transparent;
            font-size: 14px;
            line-height: 1.55;
        }

        .notice.success {
            background: var(--success-bg);
            border-color: rgba(20, 108, 67, 0.15);
            color: var(--success);
        }

        .notice.warning {
            background: var(--warning-bg);
            border-color: rgba(146, 95, 0, 0.15);
            color: var(--warning);
        }

        .notice.danger {
            background: var(--danger-bg);
            border-color: rgba(141, 29, 29, 0.12);
            color: var(--danger);
        }

        label {
            display: block;
            font-size: 14px;
            font-weight: 700;
            margin-bottom: 8px;
        }

        .field {
            display: grid;
            gap: 10px;
        }

        .field-card {
            padding: 16px;
            border: 1px solid var(--border);
            border-radius: 22px;
            background: linear-gradient(180deg, #fcfcfa 0%, #f6f6f2 100%);
        }

        .field-actions {
            display: grid;
            gap: 10px;
        }

        select,
        input[type="file"] {
            width: 100%;
            border-radius: 16px;
            border: 1px solid var(--border-strong);
            background: var(--surface);
            padding: 14px 15px;
            font: inherit;
            color: var(--text);
        }

        input[type="file"] {
            padding: 13px;
        }

        .visually-hidden {
            position: absolute;
            width: 1px;
            height: 1px;
            padding: 0;
            margin: -1px;
            overflow: hidden;
            clip: rect(0, 0, 0, 0);
            white-space: nowrap;
            border: 0;
        }

        .preview-grid {
            display: grid;
            grid-template-columns: repeat(2, minmax(0, 1fr));
            gap: 12px;
        }

        .preview-card {
            display: grid;
            gap: 8px;
        }

        .preview-frame {
            min-height: 128px;
            border-radius: 20px;
            border: 1px dashed var(--border-strong);
            background: linear-gradient(180deg, #fafaf8 0%, #f0f0eb 100%);
            display: grid;
            place-items: center;
            overflow: hidden;
        }

        .preview-frame img {
            width: 100%;
            height: 100%;
            object-fit: cover;
        }

        .preview-frame.has-image {
            border-style: solid;
            background: #ffffff;
        }

        .preview-placeholder {
            padding: 18px;
            text-align: center;
            color: var(--muted);
            font-size: 13px;
            line-height: 1.5;
        }

        .field-meta {
            display: flex;
            justify-content: space-between;
            gap: 12px;
            align-items: center;
        }

        .field-hint,
        .file-note {
            color: var(--muted);
            font-size: 13px;
            line-height: 1.55;
        }

        .file-note.error {
            color: var(--danger);
        }

        .action-row {
            display: grid;
            grid-template-columns: repeat(2, minmax(0, 1fr));
            gap: 10px;
        }

        .action-row.single {
            grid-template-columns: 1fr;
        }

        .button-row {
            display: grid;
            gap: 12px;
        }

        .btn,
        .btn-secondary {
            width: 100%;
            border: 0;
            border-radius: 18px;
            padding: 16px 18px;
            font: inherit;
            font-weight: 700;
            cursor: pointer;
            text-align: center;
            text-decoration: none;
            transition: transform 0.18s ease, opacity 0.18s ease;
        }

        .btn {
            background: #111111;
            color: #ffffff;
            box-shadow: 0 16px 26px rgba(17, 17, 17, 0.18);
        }

        .btn-secondary {
            background: var(--surface);
            color: var(--text);
            border: 1px solid var(--border-strong);
        }

        .btn[disabled] {
            cursor: wait;
            opacity: 0.72;
        }

        .btn-inline {
            width: 100%;
            border: 1px solid var(--border-strong);
            border-radius: 16px;
            background: #ffffff;
            color: var(--text);
            padding: 14px 16px;
            font: inherit;
            font-weight: 700;
            cursor: pointer;
        }

        .error-list {
            margin: 0;
            padding-left: 18px;
            display: grid;
            gap: 6px;
            color: var(--danger);
            font-size: 14px;
        }

        .footer-note {
            margin-top: 18px;
            text-align: center;
            font-size: 13px;
            color: var(--muted);
            line-height: 1.55;
        }

        .required-mark {
            color: #c67800;
        }

        @media (max-width: 520px) {
            body {
                padding-left: 10px;
                padding-right: 10px;
            }

            .card {
                padding: 20px 16px;
                border-radius: 24px;
            }

            .preview-grid {
                grid-template-columns: 1fr;
            }

            .action-row {
                grid-template-columns: 1fr;
            }
        }
    </style>
</head>
<body>
    <main class="shell">
        <div class="brand">{{ config('app.name') }}</div>
        @yield('content')
    </main>
</body>
</html>
