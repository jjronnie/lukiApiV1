<!DOCTYPE html>
<html lang="en">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
        <meta name="robots" content="noindex, nofollow">

    <title>Get In there</title>
    <style>
        :root {
            --bg: #efefef;
            --card: #ffffff;
            --text: #111111;
            --muted: #5f5f5f;
            --line: #1a1a1a;
            --black: #000000;
            --white: #ffffff;
            --error-bg: #fef2f2;
            --error-border: #fecaca;
            --error-text: #b91c1c;
            --link: #1d4ed8;
            --shadow: 0 18px 45px rgba(0, 0, 0, 0.18);
            --radius: 18px;
        }

        * {
            box-sizing: border-box;
        }

        html,
        body {
            margin: 0;
            padding: 0;
            min-height: 100%;
            font-family: Inter, ui-sans-serif, system-ui, -apple-system, BlinkMacSystemFont, "Segoe UI", sans-serif;
            color: var(--text);
            background: var(--bg);
        }

        body {
            min-height: 100vh;
            display: grid;
            place-items: center;
            padding: 24px;
        }

        .login-shell {
            width: 100%;
            max-width: 860px;
        }

        .login-card {
            position: relative;
            overflow: hidden;
            display: grid;
            grid-template-columns: 1.05fr 0.95fr;
            min-height: 480px;
            background: var(--card);
            border-radius: var(--radius);
            box-shadow: var(--shadow);
            border: 1px solid rgba(0, 0, 0, 0.15);
            isolation: isolate;
        }

        .login-card::before {
            content: "";
            position: absolute;
            inset: 0;
            background: linear-gradient(90deg, var(--white) 50%, var(--black) 50%);
            z-index: 0;
        }

        .panel {
            position: relative;
            z-index: 1;
            padding: 48px 40px;
        }

        .panel-form {
            display: flex;
            align-items: center;
            justify-content: center;
        }

        .form-wrap {
            width: 100%;
            max-width: 320px;
        }

        .title {
            margin: 0;
            text-align: center;
            font-size: 2.1rem;
            font-weight: 800;
            letter-spacing: -0.02em;
        }

        .title-line {
            width: 42px;
            height: 4px;
            background: var(--line);
            border-radius: 999px;
            margin: 10px auto 34px;
        }

        .alert {
            margin-bottom: 18px;
            padding: 12px 14px;
            border-radius: 10px;
            border: 1px solid var(--error-border);
            background: var(--error-bg);
            color: var(--error-text);
            font-size: 0.94rem;
        }

        .field {
            margin-bottom: 22px;
        }

        .field-label {
            display: block;
            font-size: 0.98rem;
            margin-bottom: 8px;
            color: var(--text);
        }

        .input-wrap {
            position: relative;
        }

        .input {
            width: 100%;
            border: none;
            border-bottom: 2px solid #222;
            background: transparent;
            padding: 10px 44px 10px 0;
            font-size: 1rem;
            color: var(--text);
            border-radius: 0;
            outline: none;
            appearance: none;
        }

        .input:focus {
            border-bottom-color: #000;
        }

        .input::placeholder {
            color: #6b7280;
        }

        .field-icon,
        .toggle-password {
            position: absolute;
            top: 50%;
            transform: translateY(-50%);
            right: 0;
            display: inline-flex;
            align-items: center;
            justify-content: center;
            color: #111;
        }

        .field-icon {
            width: 24px;
            height: 24px;
            pointer-events: none;
        }

        .toggle-password {
            border: none;
            background: transparent;
            padding: 4px 0 4px 8px;
            cursor: pointer;
            font-size: 0.84rem;
            font-weight: 700;
            line-height: 1;
            color: #111;
        }

        .toggle-password:focus-visible {
            outline: 2px solid #111;
            outline-offset: 3px;
            border-radius: 6px;
        }

        .remember-row {
            display: flex;
            align-items: center;
            gap: 10px;
            margin: -4px 0 22px;
            font-size: 0.95rem;
            color: var(--muted);
        }

        .remember-row input[type="checkbox"] {
            width: 16px;
            height: 16px;
            accent-color: #000;
            margin: 0;
        }

        .submit-btn {
            width: 100%;
            border: none;
            background: #000;
            color: #fff;
            border-radius: 999px;
            padding: 14px 18px;
            font-size: 1rem;
            font-weight: 700;
            cursor: pointer;
            transition: transform 0.15s ease, opacity 0.15s ease;
        }

        .submit-btn:hover {
            opacity: 0.94;
        }

        .submit-btn:active {
            transform: translateY(1px);
        }

        .submit-btn:focus-visible {
            outline: 2px solid #111;
            outline-offset: 3px;
        }

        .welcome-panel {
            display: flex;
            align-items: center;
            justify-content: center;
            text-align: right;
            color: #fff;
        }

        .welcome-content {
            max-width: 260px;
            margin-left: auto;
        }

        .welcome-title {
            margin: 0 0 14px;
            font-size: clamp(2rem, 4vw, 3rem);
            line-height: 0.95;
            font-weight: 900;
            letter-spacing: -0.03em;
            text-transform: uppercase;
        }

        .welcome-text {
            margin: 0;
            font-size: 1rem;
            line-height: 1.8;
            color: rgba(255, 255, 255, 0.92);
        }

        .brand-note {
            margin-top: 18px;
            font-size: 0.95rem;
            color: rgba(255, 255, 255, 0.78);
        }

        .sr-only {
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

        @media (max-width: 768px) {
            .login-card {
                grid-template-columns: 1fr;
                min-height: auto;
            }

            .login-card::before {
                background:
                    linear-gradient(180deg,
                        var(--black) 0%,
                        var(--black) 32%,
                        var(--white) 32.2%,
                        var(--white) 100%);
            }

            .welcome-panel {
                order: -1;
                min-height: 180px;
                text-align: center;
                justify-content: center;
            }

            .welcome-content {
                margin: 0 auto;
                max-width: 320px;
            }

            .panel {
                padding: 32px 24px;
            }

            .form-wrap {
                max-width: 100%;
            }
        }

        @media (max-width: 420px) {
            body {
                padding: 14px;
            }

            .panel {
                padding: 26px 18px;
            }

            .title {
                font-size: 1.75rem;
            }

            .welcome-title {
                font-size: 1.8rem;
            }

            .welcome-text {
                font-size: 0.95rem;
                line-height: 1.6;
            }
        }
    </style>
</head>

<body>
    <main class="login-shell">
        <section class="login-card" aria-labelledby="login-title">
            <div class="panel panel-form">
                <div class="form-wrap">
                    <h1 class="title" id="login-title">Login</h1>
                    <div class="title-line" aria-hidden="true"></div>

                    @if ($errors->any())
                        <div class="alert" role="alert" aria-live="polite">
                            {{ $errors->first() }}
                        </div>
                    @endif

                    <form method="POST" action="{{ route('login.store') }}" novalidate>
                        @csrf

                        <div class="field">
                            <label class="field-label" for="email">Email</label>
                            <div class="input-wrap">
                                <input
                                    class="input"
                                    id="email"
                                    type="email"
                                    name="email"
                                    required
                                    autocomplete="username"
                                    inputmode="email"
                                    value="{{ old('email') }}"
                                    placeholder="Enter your email">
                                <span class="field-icon" aria-hidden="true">
                                    <svg width="18" height="18" viewBox="0 0 24 24" fill="currentColor">
                                        <path d="M12 12a4 4 0 1 0-4-4 4 4 0 0 0 4 4Zm0 2c-4.418 0-8 2.239-8 5v1h16v-1c0-2.761-3.582-5-8-5Z"/>
                                    </svg>
                                </span>
                            </div>
                        </div>

                        <div class="field">
                            <label class="field-label" for="password">Password</label>
                            <div class="input-wrap">
                                <input
                                    class="input"
                                    id="password"
                                    type="password"
                                    name="password"
                                    required
                                    autocomplete="current-password"
                                    placeholder="Enter your password">
                                <button
                                    type="button"
                                    class="toggle-password"
                                    id="togglePassword"
                                    aria-controls="password"
                                    aria-label="Show password"
                                    aria-pressed="false">
                                    Show
                                </button>
                            </div>
                        </div>

                        <label class="remember-row" for="remember">
                            <input id="remember" type="checkbox" name="remember" value="1">
                            <span>Remember me</span>
                        </label>

                        <button class="submit-btn" type="submit">Login</button>
                    </form>
                </div>
            </div>

            <aside class="panel welcome-panel" aria-hidden="true">
                <div class="welcome-content">
                    <h2 class="welcome-title">Welcome</h2>
                    <p class="welcome-text">
                        Hope you are not here by mistake.
                    </p>
                </div>
            </aside>
        </section>
    </main>

    <script>
        (function () {
            const passwordInput = document.getElementById('password');
            const toggleBtn = document.getElementById('togglePassword');

            if (!passwordInput || !toggleBtn) {
                return;
            }

            toggleBtn.addEventListener('click', function () {
                const isHidden = passwordInput.type === 'password';
                passwordInput.type = isHidden ? 'text' : 'password';
                toggleBtn.textContent = isHidden ? 'Hide' : 'Show';
                toggleBtn.setAttribute('aria-label', isHidden ? 'Hide password' : 'Show password');
                toggleBtn.setAttribute('aria-pressed', isHidden ? 'true' : 'false');
            });
        })();
    </script>
</body>

</html>
