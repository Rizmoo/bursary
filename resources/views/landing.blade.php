<!DOCTYPE html>
<html lang="{{ str_replace('_', '-', app()->getLocale()) }}">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <title>{{ config('app.name', 'Kirinyaga Bursary Cloud') }}</title>
    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link href="https://fonts.googleapis.com/css2?family=DM+Serif+Display:ital@0;1&family=DM+Sans:ital,opsz,wght@0,9..40,300;0,9..40,400;0,9..40,500;0,9..40,600;1,9..40,300&display=swap" rel="stylesheet">

    @if (file_exists(public_path('build/manifest.json')) || file_exists(public_path('hot')))
        @vite(['resources/css/app.css', 'resources/js/app.js'])
    @endif

    <style>
        *, *::before, *::after { box-sizing: border-box; margin: 0; padding: 0; }

        :root {
            --ink:     #0d0f14;
            --ink-2:   #1a1e2b;
            --ink-3:   #252a3a;
            --border:  rgba(255,255,255,.08);
            --border-h:rgba(255,255,255,.15);
            --text:    #e8ecf4;
            --muted:   #8892a4;
            --soft:    #c4cad8;
            --gold:    #f5a623;
            --gold-lt: #fbbf50;
            --gold-dk: #c47e10;
            --green:   #34d399;
            --blue:    #60a5fa;
            --error:   #f87171;
            --r:       1rem;
        }

        html { scroll-behavior: smooth; }

        body {
            font-family: 'DM Sans', sans-serif;
            background-color: var(--ink);
            color: var(--text);
            min-height: 100vh;
            overflow-x: hidden;
        }

        /* ── Background ── */
        .bg-layer {
            position: fixed;
            inset: 0;
            z-index: 0;
            pointer-events: none;
            background:
                radial-gradient(ellipse 80% 50% at 15% -10%, rgba(245,166,35,.13) 0%, transparent 55%),
                radial-gradient(ellipse 60% 45% at 90% 10%,  rgba(59,130,246,.10) 0%, transparent 50%),
                radial-gradient(ellipse 50% 60% at 50% 110%, rgba(52,211,153,.07) 0%, transparent 55%),
                var(--ink);
        }

        .noise {
            position: fixed;
            inset: 0;
            z-index: 1;
            pointer-events: none;
            opacity: .022;
            background-image: url("data:image/svg+xml,%3Csvg viewBox='0 0 512 512' xmlns='http://www.w3.org/2000/svg'%3E%3Cfilter id='n'%3E%3CfeTurbulence type='fractalNoise' baseFrequency='.9' numOctaves='4' stitchTiles='stitch'/%3E%3C/filter%3E%3Crect width='100%25' height='100%25' filter='url(%23n)'/%3E%3C/svg%3E");
            background-size: 200px 200px;
        }

        /* ── Grid lines ── */
        .grid-lines {
            position: fixed;
            inset: 0;
            z-index: 0;
            pointer-events: none;
            background-image:
                linear-gradient(var(--border) 1px, transparent 1px),
                linear-gradient(90deg, var(--border) 1px, transparent 1px);
            background-size: 72px 72px;
            mask-image: radial-gradient(ellipse 80% 80% at 50% 50%, black 20%, transparent 75%);
            opacity: .35;
        }

        /* ── Layout ── */
        .page {
            position: relative;
            z-index: 2;
            max-width: 1160px;
            margin: 0 auto;
            padding: 0 1.5rem 4rem;
        }

        /* ── Nav ── */
        nav {
            display: flex;
            justify-content: space-between;
            align-items: center;
            padding: 1.5rem 0 2.5rem;
        }

        .logo {
            display: flex;
            align-items: center;
            gap: .7rem;
            text-decoration: none;
        }

        .logo-mark {
            width: 2.2rem;
            height: 2.2rem;
            border-radius: .55rem;
            background: linear-gradient(135deg, var(--gold), var(--gold-lt));
            box-shadow: 0 0 20px rgba(245,166,35,.4), 0 4px 12px rgba(0,0,0,.4);
            position: relative;
            flex-shrink: 0;
        }

        .logo-mark::after {
            content: '';
            position: absolute;
            inset: 5px;
            border-radius: .3rem;
            border: 1.5px solid rgba(255,255,255,.35);
        }

        .logo-text {
            font-size: .95rem;
            font-weight: 600;
            color: var(--text);
            letter-spacing: -.01em;
        }

        .logo-text span {
            display: block;
            font-size: .72rem;
            font-weight: 400;
            color: var(--muted);
            letter-spacing: .02em;
        }

        .nav-right { display: flex; gap: .6rem; align-items: center; }

        .btn-ghost {
            text-decoration: none;
            color: var(--soft);
            font-size: .85rem;
            font-weight: 500;
            padding: .5rem .9rem;
            border-radius: .6rem;
            border: 1px solid var(--border);
            transition: all .18s;
        }
        .btn-ghost:hover {
            color: var(--text);
            border-color: var(--border-h);
            background: rgba(255,255,255,.04);
        }

        /* ── Main grid ── */
        .main-grid {
            display: grid;
            grid-template-columns: 1.15fr .85fr;
            gap: 1.5rem;
            align-items: start;
        }

        /* ── Left column ── */
        .left { display: flex; flex-direction: column; gap: 1.25rem; }

        /* ── Hero block ── */
        .hero-block {
            border: 1px solid var(--border);
            background: linear-gradient(160deg, rgba(26,30,43,.9) 0%, rgba(13,15,20,.95) 100%);
            border-radius: 1.25rem;
            padding: 2.4rem 2.2rem;
            backdrop-filter: blur(12px);
            position: relative;
            overflow: hidden;
        }

        .hero-block::before {
            content: '';
            position: absolute;
            top: -1px; left: -1px; right: -1px;
            height: 2px;
            background: linear-gradient(90deg, transparent, var(--gold), transparent);
            opacity: .6;
        }

        .eyebrow {
            display: inline-flex;
            align-items: center;
            gap: .45rem;
            font-size: .72rem;
            font-weight: 600;
            letter-spacing: .1em;
            text-transform: uppercase;
            color: var(--gold);
            margin-bottom: 1.1rem;
        }

        .eyebrow-dot {
            width: 6px; height: 6px;
            border-radius: 50%;
            background: var(--gold);
            box-shadow: 0 0 6px var(--gold);
            animation: pulse 2s ease-in-out infinite;
        }

        @keyframes pulse {
            0%, 100% { opacity: 1; transform: scale(1); }
            50%       { opacity: .5; transform: scale(.7); }
        }

        .hero-title {
            font-family: 'DM Serif Display', Georgia, serif;
            font-size: clamp(2rem, 3.8vw, 3rem);
            line-height: 1.08;
            letter-spacing: -.02em;
            color: #f0f4ff;
            margin-bottom: 1rem;
        }

        .hero-title em {
            font-style: italic;
            color: var(--gold-lt);
        }

        .hero-sub {
            color: var(--muted);
            font-size: .95rem;
            line-height: 1.7;
            font-weight: 300;
            max-width: 42ch;
        }

        /* ── Stats row ── */
        .stats-row {
            display: grid;
            grid-template-columns: repeat(3, 1fr);
            gap: .75rem;
            margin-top: 1.6rem;
        }

        .stat-card {
            border: 1px solid var(--border);
            background: rgba(13,15,20,.6);
            border-radius: .85rem;
            padding: .9rem 1rem;
            transition: border-color .2s;
        }

        .stat-card:hover { border-color: var(--border-h); }

        .stat-value {
            font-family: 'DM Serif Display', serif;
            font-size: 1.35rem;
            color: var(--text);
            display: block;
            margin-bottom: .18rem;
        }

        .stat-label {
            font-size: .75rem;
            color: var(--muted);
            font-weight: 400;
        }

        /* ── Feature list ── */
        .features-block {
            border: 1px solid var(--border);
            background: linear-gradient(160deg, rgba(26,30,43,.85) 0%, rgba(13,15,20,.9) 100%);
            border-radius: 1.25rem;
            padding: 1.5rem 1.6rem;
            backdrop-filter: blur(12px);
        }

        .features-title {
            font-size: .72rem;
            font-weight: 600;
            letter-spacing: .1em;
            text-transform: uppercase;
            color: var(--muted);
            margin-bottom: 1rem;
        }

        .feature-list {
            list-style: none;
            display: flex;
            flex-direction: column;
            gap: .6rem;
        }

        .feature-item {
            display: flex;
            align-items: flex-start;
            gap: .7rem;
            font-size: .88rem;
            color: var(--soft);
            line-height: 1.5;
        }

        .feature-icon {
            width: 1.4rem;
            height: 1.4rem;
            border-radius: .4rem;
            display: flex;
            align-items: center;
            justify-content: center;
            font-size: .7rem;
            flex-shrink: 0;
            margin-top: .05rem;
        }

        .fi-gold  { background: rgba(245,166,35,.12);  color: var(--gold); }
        .fi-green { background: rgba(52,211,153,.1);   color: var(--green); }
        .fi-blue  { background: rgba(96,165,250,.1);   color: var(--blue); }

        /* ── Right column — Form ── */
        .form-card {
            border: 1px solid var(--border);
            background: linear-gradient(175deg, rgba(26,30,43,.92) 0%, rgba(13,15,20,.97) 100%);
            border-radius: 1.25rem;
            padding: 2rem 1.75rem;
            backdrop-filter: blur(16px);
            position: sticky;
            top: 1.5rem;
        }

        .form-card::before {
            content: '';
            position: absolute;
            top: -1px; left: -1px; right: -1px;
            height: 2px;
            border-radius: 1.25rem 1.25rem 0 0;
            background: linear-gradient(90deg, transparent 10%, var(--gold) 50%, transparent 90%);
            opacity: .5;
        }

        .form-header { margin-bottom: 1.4rem; }

        .form-title {
            font-family: 'DM Serif Display', serif;
            font-size: 1.45rem;
            letter-spacing: -.01em;
            color: var(--text);
            margin-bottom: .35rem;
        }

        .form-sub {
            font-size: .82rem;
            color: var(--muted);
            line-height: 1.5;
        }

        .divider {
            height: 1px;
            background: var(--border);
            margin: 1.2rem 0;
        }

        .field { margin-bottom: .85rem; }

        .field label {
            display: block;
            font-size: .78rem;
            font-weight: 500;
            color: var(--soft);
            margin-bottom: .35rem;
            letter-spacing: .01em;
        }

        .field input {
            width: 100%;
            background: rgba(13,15,20,.7);
            border: 1px solid rgba(255,255,255,.1);
            color: var(--text);
            border-radius: .6rem;
            padding: .7rem .85rem;
            font-family: inherit;
            font-size: .88rem;
            transition: border-color .18s, box-shadow .18s;
            outline: none;
        }

        .field input:focus {
            border-color: rgba(245,166,35,.5);
            box-shadow: 0 0 0 3px rgba(245,166,35,.08);
        }

        .field input::placeholder { color: rgba(136,146,164,.4); }

        .field-row {
            display: grid;
            grid-template-columns: 1fr 1fr;
            gap: .7rem;
        }

        .error-banner {
            margin-bottom: 1rem;
            border: 1px solid rgba(248,113,113,.25);
            background: rgba(127,29,29,.2);
            color: #fca5a5;
            padding: .7rem .85rem;
            border-radius: .6rem;
            font-size: .82rem;
            line-height: 1.5;
        }

        .field-error {
            color: var(--error);
            font-size: .75rem;
            margin-top: .25rem;
        }

        .submit-btn {
            width: 100%;
            border: none;
            border-radius: .7rem;
            padding: .82rem 1rem;
            font-family: inherit;
            font-size: .9rem;
            font-weight: 600;
            color: #0d0f14;
            background: linear-gradient(135deg, var(--gold), var(--gold-lt));
            cursor: pointer;
            letter-spacing: .01em;
            box-shadow: 0 4px 20px rgba(245,166,35,.3);
            transition: transform .15s, box-shadow .15s;
            margin-top: .3rem;
        }

        .submit-btn:hover {
            transform: translateY(-1px);
            box-shadow: 0 6px 28px rgba(245,166,35,.4);
        }

        .submit-btn:active { transform: translateY(0); }

        .form-footer {
            margin-top: 1.1rem;
            font-size: .78rem;
            color: var(--muted);
            text-align: center;
            line-height: 1.6;
        }

        .form-footer a {
            color: var(--gold-lt);
            text-decoration: none;
        }

        .form-footer a:hover { text-decoration: underline; }

        /* ── Trust badges ── */
        .trust-row {
            display: flex;
            gap: 1rem;
            margin-top: 1rem;
            flex-wrap: wrap;
        }

        .trust-badge {
            display: flex;
            align-items: center;
            gap: .4rem;
            font-size: .72rem;
            color: var(--muted);
            font-weight: 500;
        }

        .trust-badge svg {
            opacity: .55;
        }

        /* ── Floating accent ── */
        .accent-orb {
            position: absolute;
            width: 180px; height: 180px;
            border-radius: 50%;
            filter: blur(60px);
            pointer-events: none;
            z-index: 0;
        }

        .orb-gold {
            right: -40px; top: 30px;
            background: rgba(245,166,35,.12);
        }

        /* ── Animations ── */
        .fade-up {
            opacity: 0;
            transform: translateY(18px);
            animation: fadeUp .55s ease forwards;
        }

        @keyframes fadeUp {
            to { opacity: 1; transform: none; }
        }

        .delay-1 { animation-delay: .08s; }
        .delay-2 { animation-delay: .16s; }
        .delay-3 { animation-delay: .24s; }
        .delay-4 { animation-delay: .32s; }

        /* ── Responsive ── */
        @media (max-width: 900px) {
            .main-grid { grid-template-columns: 1fr; }
            .form-card { position: static; }
            .stats-row { grid-template-columns: repeat(3, 1fr); }
        }

        @media (max-width: 560px) {
            .stats-row { grid-template-columns: 1fr 1fr; }
            .field-row { grid-template-columns: 1fr; }
            .hero-block { padding: 1.6rem 1.3rem; }
            .hero-title { font-size: 1.9rem; }
        }
    </style>
</head>
<body>

<div class="bg-layer"></div>
<div class="noise"></div>
<div class="grid-lines"></div>

<div class="page">

    <!-- Nav -->
    <nav class="fade-up">
        <a class="logo" href="#">
            <div class="logo-mark"></div>
            <div class="logo-text">
                Kirinyaga Bursary Cloud
                <span>Ward Education Funding Platform</span>
            </div>
        </a>
        <div class="nav-right">
            <a class="btn-ghost" href="{{ url('/admin/login') }}">Admin Login</a>
        </div>
    </nav>

    <!-- Main grid -->
    <div class="main-grid">

        <!-- Left -->
        <div class="left">

            <!-- Hero -->
            <div class="hero-block fade-up delay-1">
                <div class="accent-orb orb-gold"></div>

                <div class="eyebrow">
                    <span class="eyebrow-dot"></span>
                    Kirinyaga County · Bursary Management
                </div>

                <h1 class="hero-title">
                    Modern funding for<br>
                    <em>ward-based education</em>
                </h1>

                <p class="hero-sub">
                    A secure, multi-tenant platform for managing applicants, cheque workflows, bank reconciliation, and quarterly reports — built for transparency and speed.
                </p>

                <div class="stats-row">
                    <div class="stat-card">
                        <span class="stat-value">Ward<br>Scoped</span>
                        <span class="stat-label">Isolated data per ward</span>
                    </div>
                    <div class="stat-card">
                        <span class="stat-value">Excel<br>&amp; PDF</span>
                        <span class="stat-label">Fast export workflows</span>
                    </div>
                    <div class="stat-card">
                        <span class="stat-value">Audit<br>Ready</span>
                        <span class="stat-label">Traceable cheque lifecycle</span>
                    </div>
                </div>

                <div class="trust-row">
                    <div class="trust-badge">
                        <svg width="12" height="12" viewBox="0 0 12 12" fill="none"><path d="M6 1L7.5 4.5H11L8.25 6.75L9.25 10.5L6 8.25L2.75 10.5L3.75 6.75L1 4.5H4.5L6 1Z" fill="#34d399"/></svg>
                        Multi-tenant security
                    </div>
                    <div class="trust-badge">
                        <svg width="12" height="12" viewBox="0 0 12 12" fill="none"><rect x="1" y="4" width="10" height="7" rx="1.5" stroke="#60a5fa" stroke-width="1.2"/><path d="M4 4V3a2 2 0 0 1 4 0v1" stroke="#60a5fa" stroke-width="1.2"/></svg>
                        Role-based access control
                    </div>
                    <div class="trust-badge">
                        <svg width="12" height="12" viewBox="0 0 12 12" fill="none"><path d="M2 6l3 3 5-5" stroke="#f5a623" stroke-width="1.5" stroke-linecap="round" stroke-linejoin="round"/></svg>
                        Financial-year scoped
                    </div>
                </div>
            </div>

            <!-- Features -->
            <div class="features-block fade-up delay-2">
                <p class="features-title">Platform capabilities</p>
                <ul class="feature-list">
                    <li class="feature-item">
                        <span class="feature-icon fi-gold">👤</span>
                        Applicant and institution management with full record tracking
                    </li>
                    <li class="feature-item">
                        <span class="feature-icon fi-blue">🏦</span>
                        Cheque assignment, clearance, stale and return handling workflows
                    </li>
                    <li class="feature-item">
                        <span class="feature-icon fi-green">📊</span>
                        Bank reconciliation with smart matching and penalty management
                    </li>
                    <li class="feature-item">
                        <span class="feature-icon fi-gold">📅</span>
                        Financial-year scoped analytics, reports, and data exports
                    </li>
                </ul>
            </div>

        </div>

        <!-- Right — Registration form -->
        <div class="form-card fade-up delay-3" style="position:relative; overflow:hidden;">

            <div class="form-header">
                <h2 class="form-title">Create a Ward Account</h2>
                <p class="form-sub">Register your ward to get started. Each ward operates in an isolated, secure workspace.</p>
            </div>

            @if ($errors->has('ward_name'))
                <div class="error-banner">{{ $errors->first('ward_name') }}</div>
            @endif

            <form method="POST" action="{{ route('self-register.store') }}">
                @csrf

                <div class="field">
                    <label>Full Name</label>
                    <input name="name" value="{{ old('name') }}" placeholder="Your full name" required>
                    @error('name')<div class="field-error">{{ $message }}</div>@enderror
                </div>

                <div class="field">
                    <label>Email Address</label>
                    <input type="email" name="email" value="{{ old('email') }}" placeholder="you@example.com" required>
                    @error('email')<div class="field-error">{{ $message }}</div>@enderror
                </div>

                <div class="field">
                    <label>Ward Name</label>
                    <input name="ward_name" value="{{ old('ward_name') }}" placeholder="e.g. Mwea Ward" required>
                </div>

                <div class="field-row">
                    <div class="field" style="margin-bottom:0">
                        <label>Password</label>
                        <input type="password" name="password" placeholder="Min. 8 characters" required>
                        @error('password')<div class="field-error">{{ $message }}</div>@enderror
                    </div>
                    <div class="field" style="margin-bottom:0">
                        <label>Confirm Password</label>
                        <input type="password" name="password_confirmation" placeholder="Repeat password" required>
                    </div>
                </div>

                <div class="divider" style="margin-top:1rem;"></div>

                <button class="submit-btn" type="submit">
                    Create Ward Account →
                </button>
            </form>

            <p class="form-footer">
                Already registered? Contact your ward admin, or<br>
                <a href="{{ url('/admin/login') }}">sign in to your account</a>.
            </p>

        </div>
    </div>

</div>

</body>
</html>