<!DOCTYPE html>
<html lang="{{ str_replace('_', '-', app()->getLocale()) }}" data-theme="dark">
<head>
  <meta charset="UTF-8" />
  <meta name="viewport" content="width=device-width, initial-scale=1.0"/>
  <title>Create Ward Account — Kirinyaga Bursary Cloud</title>
  <link rel="preconnect" href="https://fonts.googleapis.com">
  <link href="https://fonts.googleapis.com/css2?family=Playfair+Display:ital,wght@0,700;0,900;1,700&family=Sora:wght@300;400;500;600&display=swap" rel="stylesheet">
  <style>
    *, *::before, *::after { box-sizing: border-box; margin: 0; padding: 0; }

    /* ── Theme tokens (mirrors landing page exactly) ── */
    :root, [data-theme="dark"] {
      --bg:        #06090f;
      --bg2:       #0d1117;
      --bg3:       #111827;
      --surface:   rgba(13,17,23,.9);
      --surface2:  rgba(17,24,39,.85);
      --border:    rgba(255,255,255,.07);
      --border2:   rgba(255,255,255,.13);
      --text:      #e4eaf6;
      --heading:   #f0f5ff;
      --muted:     #7a8799;
      --soft:      #b0bac9;
      --amber:     #f59e0b;
      --amber-l:   #fcd34d;
      --amber-d:   #b45309;
      --teal:      #2dd4bf;
      --blue:      #60a5fa;
      --error:     #f87171;
      --nav-bg:    rgba(6,9,15,.78);
      --nav-bg-s:  rgba(6,9,15,.97);
      --input-bg:  rgba(6,9,15,.7);
      --input-border: rgba(255,255,255,.1);
    }

    [data-theme="light"] {
      --bg:        #f5f4f0;
      --bg2:       #edecea;
      --bg3:       #e4e2de;
      --surface:   rgba(255,253,248,.96);
      --surface2:  rgba(240,238,233,.9);
      --border:    rgba(0,0,0,.08);
      --border2:   rgba(0,0,0,.15);
      --text:      #1a1c22;
      --heading:   #0d0f14;
      --muted:     #6b7280;
      --soft:      #374151;
      --amber:     #d97706;
      --amber-l:   #f59e0b;
      --amber-d:   #92400e;
      --teal:      #0d9488;
      --blue:      #2563eb;
      --error:     #dc2626;
      --nav-bg:    rgba(245,244,240,.82);
      --nav-bg-s:  rgba(245,244,240,.97);
      --input-bg:  rgba(255,255,255,.7);
      --input-border: rgba(0,0,0,.13);
    }

    html { scroll-behavior: smooth; }

    body {
      font-family: 'Sora', sans-serif;
      background: var(--bg);
      color: var(--text);
      min-height: 100vh;
      overflow-x: hidden;
      transition: background .35s, color .35s;
    }

    /* Background glow */
    body::before {
      content: '';
      position: fixed; inset: 0; z-index: 0; pointer-events: none;
      transition: background .35s;
    }

    [data-theme="dark"] body::before {
      background:
        radial-gradient(ellipse 80% 50% at 15% -10%, rgba(245,158,11,.1) 0%, transparent 55%),
        radial-gradient(ellipse 60% 45% at 90% 10%,  rgba(45,212,191,.06) 0%, transparent 50%);
    }

    [data-theme="light"] body::before {
      background:
        radial-gradient(ellipse 80% 50% at 15% -10%, rgba(217,119,6,.07) 0%, transparent 55%),
        radial-gradient(ellipse 60% 45% at 90% 10%,  rgba(13,148,136,.04) 0%, transparent 50%);
    }

    /* Grid bg */
    body::after {
      content: '';
      position: fixed; inset: 0; z-index: 0; pointer-events: none;
      background-image:
        linear-gradient(var(--border) 1px, transparent 1px),
        linear-gradient(90deg, var(--border) 1px, transparent 1px);
      background-size: 64px 64px;
      mask-image: radial-gradient(ellipse 80% 80% at 50% 50%, black 20%, transparent 75%);
      opacity: .4;
    }

    /* ── NAV ── */
    nav {
      position: fixed; top: 0; left: 0; right: 0; z-index: 100;
      padding: 0 clamp(1.5rem, 5vw, 4rem);
      height: 70px;
      display: flex; align-items: center; justify-content: space-between;
      background: var(--nav-bg);
      backdrop-filter: blur(20px) saturate(1.5);
      border-bottom: 1px solid var(--border);
      transition: background .3s, border-color .3s;
    }

    .nav-logo { display: flex; align-items: center; gap: .65rem; text-decoration: none; }

    .nav-mark {
      width: 2rem; height: 2rem; border-radius: .5rem;
      background: linear-gradient(135deg, var(--amber), var(--amber-l));
      box-shadow: 0 0 16px rgba(245,158,11,.3);
      flex-shrink: 0; position: relative;
    }

    .nav-mark::after {
      content: ''; position: absolute; inset: 5px;
      border: 1.5px solid rgba(255,255,255,.3); border-radius: .25rem;
    }

    .nav-name { font-size: .9rem; font-weight: 600; color: var(--text); letter-spacing: -.01em; transition: color .3s; }

    .nav-right { display: flex; gap: .6rem; align-items: center; }

    /* Theme toggle */
    .theme-toggle {
      width: 2.5rem; height: 2.5rem; border-radius: .6rem;
      border: 1px solid var(--border2); background: var(--surface2);
      cursor: pointer; display: flex; align-items: center; justify-content: center;
      flex-shrink: 0; position: relative; overflow: hidden;
      transition: background .25s, border-color .25s, transform .15s, box-shadow .2s;
    }

    .theme-toggle:hover {
      transform: scale(1.1) rotate(12deg);
      box-shadow: 0 4px 14px rgba(245,158,11,.25);
      border-color: var(--amber);
    }

    .t-icon {
      position: absolute; font-size: 1.05rem; line-height: 1; top: 50%; left: 50%;
      transition: opacity .3s, transform .35s cubic-bezier(.34,1.56,.64,1);
    }

    .t-sun  { transform: translate(-50%,-50%) rotate(0deg) scale(1); }
    .t-moon { transform: translate(-50%,-50%) rotate(-90deg) scale(.4); }

    [data-theme="dark"]  .t-sun  { opacity: 1;  transform: translate(-50%,-50%) rotate(0deg) scale(1); }
    [data-theme="dark"]  .t-moon { opacity: 0;  transform: translate(-50%,-50%) rotate(90deg) scale(.4); }
    [data-theme="light"] .t-sun  { opacity: 0;  transform: translate(-50%,-50%) rotate(-90deg) scale(.4); }
    [data-theme="light"] .t-moon { opacity: 1;  transform: translate(-50%,-50%) rotate(0deg) scale(1); }

    /* Ghost btn */
    .btn-ghost {
      text-decoration: none; font-size: .83rem; font-weight: 500;
      color: var(--soft); border: 1px solid var(--border2); background: transparent;
      padding: .48rem .9rem; border-radius: .6rem;
      transition: background .18s, color .18s;
    }

    .btn-ghost:hover { background: var(--surface2); color: var(--text); }

    /* ── PAGE LAYOUT ── */
    .page {
      position: relative; z-index: 2;
      min-height: 100vh;
      display: flex; align-items: center; justify-content: center;
      padding: 90px 1.5rem 3rem;
    }

    /* ── CARD ── */
    .card {
      width: 100%; max-width: 500px;
      border: 1px solid var(--border2);
      background: var(--surface);
      border-radius: 1.2rem;
      padding: 2.2rem 2rem;
      backdrop-filter: blur(16px);
      position: relative;
      overflow: hidden;
      box-shadow: 0 32px 64px rgba(0,0,0,.3);
      animation: fadeUp .5s ease both;
    }

    /* Amber top line */
    .card::before {
      content: '';
      position: absolute; top: -1px; left: 15%; right: 15%;
      height: 2px;
      background: linear-gradient(90deg, transparent, var(--amber), transparent);
      opacity: .7;
    }

    @keyframes fadeUp {
      from { opacity: 0; transform: translateY(18px); }
      to   { opacity: 1; transform: none; }
    }

    /* ── CARD HEADER ── */
    .card-header { margin-bottom: 1.6rem; }

    .card-eyebrow {
      display: inline-flex; align-items: center; gap: .4rem;
      font-size: .7rem; font-weight: 600; letter-spacing: .1em; text-transform: uppercase;
      color: var(--amber); margin-bottom: .75rem;
    }

    .eyebrow-dot {
      width: 5px; height: 5px; border-radius: 50%;
      background: var(--amber); box-shadow: 0 0 5px var(--amber);
      animation: pulse 2s infinite;
    }

    @keyframes pulse { 0%,100%{opacity:1;transform:scale(1)} 50%{opacity:.4;transform:scale(.65)} }

    .card-title {
      font-family: 'Playfair Display', serif;
      font-size: 1.65rem; font-weight: 700; letter-spacing: -.02em;
      color: var(--heading); line-height: 1.1;
      margin-bottom: .45rem;
      transition: color .3s;
    }

    .card-sub { font-size: .83rem; color: var(--muted); line-height: 1.6; font-weight: 300; }

    /* ── DIVIDER ── */
    .divider { height: 1px; background: var(--border); margin: 1.4rem 0; }

    /* ── ERROR BANNER ── */
    .error-banner {
      border: 1px solid rgba(248,113,113,.25);
      background: rgba(127,29,29,.18);
      color: #fca5a5;
      padding: .7rem .9rem;
      border-radius: .6rem;
      font-size: .82rem;
      line-height: 1.5;
      margin-bottom: 1.1rem;
    }

    [data-theme="light"] .error-banner {
      background: rgba(220,38,38,.07);
      color: #dc2626;
      border-color: rgba(220,38,38,.2);
    }

    /* ── FORM FIELDS ── */
    .field { margin-bottom: .9rem; }

    .field label {
      display: block;
      font-size: .78rem; font-weight: 500;
      color: var(--soft); margin-bottom: .35rem; letter-spacing: .01em;
      transition: color .3s;
    }

    /* Input wrapper (for password reveal) */
    .input-wrap { position: relative; }

    .field input {
      width: 100%;
      background: var(--input-bg);
      border: 1px solid var(--input-border);
      color: var(--text);
      border-radius: .6rem;
      padding: .72rem .88rem;
      font-family: 'Sora', sans-serif;
      font-size: .88rem;
      outline: none;
      transition: border-color .2s, box-shadow .2s, background .3s, color .3s;
    }

    .field input:focus {
      border-color: rgba(245,158,11,.5);
      box-shadow: 0 0 0 3px rgba(245,158,11,.09);
    }

    .field input::placeholder { color: rgba(136,146,164,.38); }

    [data-theme="light"] .field input::placeholder { color: rgba(107,114,128,.4); }

    /* Password input has right padding for the toggle */
    .input-wrap input[type="password"],
    .input-wrap input[type="text"] {
      padding-right: 2.8rem;
    }

    /* Reveal button */
    .reveal-btn {
      position: absolute; right: 0; top: 0; bottom: 0;
      width: 2.6rem;
      display: flex; align-items: center; justify-content: center;
      background: none; border: none; cursor: pointer;
      color: var(--muted);
      transition: color .18s;
      border-radius: 0 .6rem .6rem 0;
    }

    .reveal-btn:hover { color: var(--amber); }

    .reveal-btn svg { width: 16px; height: 16px; stroke-width: 1.8; }

    /* Field-level error */
    .field-error {
      color: var(--error);
      font-size: .75rem; margin-top: .28rem; line-height: 1.4;
    }

    /* Two-column row */
    .field-row { display: grid; grid-template-columns: 1fr 1fr; gap: .75rem; margin-bottom: .9rem; }
    .field-row .field { margin-bottom: 0; }

    /* ── ACTIONS ── */
    .actions {
      display: flex; align-items: center; justify-content: space-between;
      gap: 1rem; margin-top: 1.4rem; flex-wrap: wrap;
    }

    .back-link {
      display: inline-flex; align-items: center; gap: .35rem;
      font-size: .82rem; color: var(--muted); text-decoration: none;
      transition: color .18s;
    }

    .back-link:hover { color: var(--amber); }

    .back-link svg { width: 14px; height: 14px; stroke-width: 2; flex-shrink: 0; }

    .submit-btn {
      display: inline-flex; align-items: center; gap: .4rem;
      border: none; border-radius: .65rem;
      padding: .74rem 1.5rem;
      font-family: 'Sora', sans-serif; font-size: .88rem; font-weight: 600;
      color: #fff;
      background: linear-gradient(135deg, var(--amber-d), var(--amber));
      box-shadow: 0 4px 18px rgba(245,158,11,.28);
      cursor: pointer;
      transition: transform .15s, box-shadow .15s;
    }

    [data-theme="dark"] .submit-btn { color: #0d0f14; }

    .submit-btn:hover { transform: translateY(-1px); box-shadow: 0 6px 24px rgba(245,158,11,.38); }
    .submit-btn:active { transform: none; }

    .submit-btn svg { width: 15px; height: 15px; stroke-width: 2.2; }

    /* ── FOOTER NOTE ── */
    .card-foot {
      margin-top: 1.2rem; text-align: center;
      font-size: .78rem; color: var(--muted); line-height: 1.6;
    }

    .card-foot a { color: var(--amber); text-decoration: none; }
    .card-foot a:hover { text-decoration: underline; }

    @media (max-width: 480px) {
      .field-row { grid-template-columns: 1fr; }
      .card { padding: 1.6rem 1.2rem; }
      .actions { flex-direction: column-reverse; align-items: stretch; }
      .submit-btn { justify-content: center; }
    }
  </style>
</head>

<body>

<!-- ── NAV ── -->
<nav id="main-nav">
  <a class="nav-logo" href="{{ route('landing') }}">
    <div class="nav-mark"></div>
    <span class="nav-name">Kirinyaga Bursary Cloud</span>
  </a>

  <div class="nav-right">
    <button class="theme-toggle" id="themeToggle" title="Toggle theme" aria-label="Toggle light/dark mode">
      <span class="t-icon t-sun">☀️</span>
      <span class="t-icon t-moon">🌙</span>
    </button>
    <a href="{{ url('/admin/login') }}" class="btn-ghost">Admin Login</a>
  </div>
</nav>

<!-- ── PAGE ── -->
<div class="page">
  <div class="card">

    <div class="card-header">
      <div class="card-eyebrow">
        <span class="eyebrow-dot"></span>
        New ward registration
      </div>
      <h1 class="card-title">Create Ward Account</h1>
      <p class="card-sub">Register your ward administrator account to get started with the bursary platform.</p>
    </div>

    <div class="divider"></div>

    {{-- Ward-name error banner --}}
    @if ($errors->has('ward_name') && !$errors->has('name') && !$errors->has('email') && !$errors->has('password'))
      <div class="error-banner">{{ $errors->first('ward_name') }}</div>
    @endif

    <form method="POST" action="{{ route('self-register.store') }}">
      @csrf

      {{-- Full Name --}}
      <div class="field">
        <label for="name">Full Name</label>
        <div class="input-wrap">
          <input id="name" name="name" type="text"
                 value="{{ old('name') }}"
                 placeholder="Your full name"
                 required autocomplete="name">
        </div>
        @error('name')<div class="field-error">{{ $message }}</div>@enderror
      </div>

    {{-- County Name --}}
      <div class="field">
        <label for="county_name">County Name</label>
        <div class="input-wrap">
          <input id="county_name" name="county_name" type="text"
                 value="{{ old('county_name') }}"
                 placeholder="e.g. Kirinyaga County"
                 required>
        </div>
        @error('county_name')<div class="field-error">{{ $message }}</div>@enderror
      </div>

      {{-- Ward Name --}}
      <div class="field">
        <label for="ward_name">Ward Name</label>
        <div class="input-wrap">
          <input id="ward_name" name="ward_name" type="text"
                 value="{{ old('ward_name') }}"
                 placeholder="e.g. Mwea Ward"
                 required>
        </div>
        @error('ward_name')<div class="field-error">{{ $message }}</div>@enderror
      </div>

      {{-- Email --}}
      <div class="field">
        <label for="email">Email Address</label>
        <div class="input-wrap">
          <input id="email" name="email" type="email"
                 value="{{ old('email') }}"
                 placeholder="you@example.com"
                 required autocomplete="email">
        </div>
        @error('email')<div class="field-error">{{ $message }}</div>@enderror
      </div>

      {{-- Passwords side by side --}}
      <div class="field-row">
        <div class="field">
          <label for="password">Password</label>
          <div class="input-wrap">
            <input id="password" name="password" type="password"
                   placeholder="Min. 8 characters"
                   required autocomplete="new-password">
            <button type="button" class="reveal-btn" data-target="password" aria-label="Toggle password visibility">
              <!-- Eye icon -->
              <svg id="eye-password" xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                <path stroke-linecap="round" stroke-linejoin="round" d="M2.458 12C3.732 7.943 7.523 5 12 5c4.478 0 8.268 2.943 9.542 7-1.274 4.057-5.064 7-9.542 7-4.477 0-8.268-2.943-9.542-7z"/>
                <path stroke-linecap="round" stroke-linejoin="round" d="M15 12a3 3 0 11-6 0 3 3 0 016 0z"/>
              </svg>
              <!-- Eye-off icon (hidden by default) -->
              <svg id="eye-off-password" xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke="currentColor" style="display:none">
                <path stroke-linecap="round" stroke-linejoin="round" d="M13.875 18.825A10.05 10.05 0 0112 19c-4.477 0-8.268-2.943-9.542-7a9.956 9.956 0 012.223-3.592M6.53 6.53A9.956 9.956 0 0112 5c4.478 0 8.268 2.943 9.542 7a9.956 9.956 0 01-4.043 5.197M15 12a3 3 0 00-3-3m0 0a3 3 0 00-2.121.879M3 3l18 18"/>
              </svg>
            </button>
          </div>
          @error('password')<div class="field-error">{{ $message }}</div>@enderror
        </div>

        <div class="field">
          <label for="password_confirmation">Confirm Password</label>
          <div class="input-wrap">
            <input id="password_confirmation" name="password_confirmation" type="password"
                   placeholder="Repeat password"
                   required autocomplete="new-password">
            <button type="button" class="reveal-btn" data-target="password_confirmation" aria-label="Toggle confirm password visibility">
              <svg id="eye-password_confirmation" xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                <path stroke-linecap="round" stroke-linejoin="round" d="M2.458 12C3.732 7.943 7.523 5 12 5c4.478 0 8.268 2.943 9.542 7-1.274 4.057-5.064 7-9.542 7-4.477 0-8.268-2.943-9.542-7z"/>
                <path stroke-linecap="round" stroke-linejoin="round" d="M15 12a3 3 0 11-6 0 3 3 0 016 0z"/>
              </svg>
              <svg id="eye-off-password_confirmation" xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke="currentColor" style="display:none">
                <path stroke-linecap="round" stroke-linejoin="round" d="M13.875 18.825A10.05 10.05 0 0112 19c-4.477 0-8.268-2.943-9.542-7a9.956 9.956 0 012.223-3.592M6.53 6.53A9.956 9.956 0 0112 5c4.478 0 8.268 2.943 9.542 7a9.956 9.956 0 01-4.043 5.197M15 12a3 3 0 00-3-3m0 0a3 3 0 00-2.121.879M3 3l18 18"/>
              </svg>
            </button>
          </div>
        </div>
      </div>

      <div class="actions">
        <a href="{{ route('landing') }}" class="back-link">
          <svg xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke="currentColor">
            <path stroke-linecap="round" stroke-linejoin="round" d="M15 19l-7-7 7-7"/>
          </svg>
          Back to landing
        </a>

        <button type="submit" class="submit-btn">
          Create Account
          <svg xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke="currentColor">
            <path stroke-linecap="round" stroke-linejoin="round" d="M9 5l7 7-7 7"/>
          </svg>
        </button>
      </div>
    </form>

    <p class="card-foot">
      Already have an account? <a href="{{ url('/admin/login') }}">Sign in here</a>.
    </p>

  </div>
</div>

<script>
  /* ── Theme toggle ── */
  const html = document.documentElement;
  const btn  = document.getElementById('themeToggle');
  const saved   = localStorage.getItem('kbc-theme');
  const sysDark = window.matchMedia('(prefers-color-scheme: dark)').matches;
  html.setAttribute('data-theme', saved || (sysDark ? 'dark' : 'light'));

  btn.addEventListener('click', () => {
    const next = html.getAttribute('data-theme') === 'dark' ? 'light' : 'dark';
    html.setAttribute('data-theme', next);
    localStorage.setItem('kbc-theme', next);
    syncNav();
  });

  function syncNav() {
    const dark = html.getAttribute('data-theme') === 'dark';
    document.getElementById('main-nav').style.background =
      dark ? 'rgba(6,9,15,.97)' : 'rgba(245,244,240,.97)';
  }

  window.addEventListener('scroll', syncNav);

  /* ── Password reveal ── */
  document.querySelectorAll('.reveal-btn').forEach(btn => {
    btn.addEventListener('click', () => {
      const targetId = btn.dataset.target;
      const input    = document.getElementById(targetId);
      const eyeOn    = document.getElementById('eye-' + targetId);
      const eyeOff   = document.getElementById('eye-off-' + targetId);

      if (input.type === 'password') {
        input.type  = 'text';
        eyeOn.style.display  = 'none';
        eyeOff.style.display = 'block';
      } else {
        input.type  = 'password';
        eyeOn.style.display  = 'block';
        eyeOff.style.display = 'none';
      }

      input.focus();
    });
  });
</script>

</body>
</html>