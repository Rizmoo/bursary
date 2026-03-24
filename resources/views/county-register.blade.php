<!DOCTYPE html>
<html lang="{{ str_replace('_', '-', app()->getLocale()) }}" data-theme="dark">
<head>
  <meta charset="UTF-8" />
  <meta name="viewport" content="width=device-width, initial-scale=1.0"/>
  <title>Create County Account — Kirinyaga Bursary Cloud</title>
  <link rel="preconnect" href="https://fonts.googleapis.com">
  <link href="https://fonts.googleapis.com/css2?family=Playfair+Display:ital,wght@0,700;0,900;1,700&family=Sora:wght@300;400;500;600&display=swap" rel="stylesheet">
  <style>
    *, *::before, *::after { box-sizing: border-box; margin: 0; padding: 0; }
    :root, [data-theme="dark"] {
      --bg:#06090f; --bg2:#0d1117; --bg3:#111827; --surface:rgba(13,17,23,.9); --surface2:rgba(17,24,39,.85);
      --border:rgba(255,255,255,.07); --border2:rgba(255,255,255,.13); --text:#e4eaf6; --heading:#f0f5ff; --muted:#7a8799;
      --soft:#b0bac9; --amber:#f59e0b; --amber-l:#fcd34d; --amber-d:#b45309; --error:#f87171;
      --nav-bg:rgba(6,9,15,.78); --input-bg:rgba(6,9,15,.7); --input-border:rgba(255,255,255,.1);
    }
    [data-theme="light"] {
      --bg:#f5f4f0; --bg2:#edecea; --bg3:#e4e2de; --surface:rgba(255,253,248,.96); --surface2:rgba(240,238,233,.9);
      --border:rgba(0,0,0,.08); --border2:rgba(0,0,0,.15); --text:#1a1c22; --heading:#0d0f14; --muted:#6b7280;
      --soft:#374151; --amber:#d97706; --amber-l:#f59e0b; --amber-d:#92400e; --error:#dc2626;
      --nav-bg:rgba(245,244,240,.82); --input-bg:rgba(255,255,255,.7); --input-border:rgba(0,0,0,.13);
    }
    body { font-family:'Sora',sans-serif; background:var(--bg); color:var(--text); min-height:100vh; overflow-x:hidden; transition:background .35s,color .35s; }
    body::before { content:''; position:fixed; inset:0; z-index:0; pointer-events:none; transition:background .35s; }
    [data-theme="dark"] body::before { background:radial-gradient(ellipse 80% 50% at 15% -10%, rgba(245,158,11,.1) 0%, transparent 55%),radial-gradient(ellipse 60% 45% at 90% 10%, rgba(45,212,191,.06) 0%, transparent 50%); }
    [data-theme="light"] body::before { background:radial-gradient(ellipse 80% 50% at 15% -10%, rgba(217,119,6,.07) 0%, transparent 55%),radial-gradient(ellipse 60% 45% at 90% 10%, rgba(13,148,136,.04) 0%, transparent 50%); }
    body::after { content:''; position:fixed; inset:0; z-index:0; pointer-events:none; background-image:linear-gradient(var(--border) 1px, transparent 1px),linear-gradient(90deg, var(--border) 1px, transparent 1px); background-size:64px 64px; mask-image:radial-gradient(ellipse 80% 80% at 50% 50%, black 20%, transparent 75%); opacity:.4; }
    nav { position:fixed; top:0; left:0; right:0; z-index:100; padding:0 clamp(1.5rem,5vw,4rem); height:70px; display:flex; align-items:center; justify-content:space-between; background:var(--nav-bg); backdrop-filter:blur(20px) saturate(1.5); border-bottom:1px solid var(--border); }
    .nav-logo { display:flex; align-items:center; gap:.65rem; text-decoration:none; }
    .nav-mark { width:2rem; height:2rem; border-radius:.5rem; background:linear-gradient(135deg, var(--amber), var(--amber-l)); box-shadow:0 0 16px rgba(245,158,11,.3); position:relative; }
    .nav-mark::after { content:''; position:absolute; inset:5px; border:1.5px solid rgba(255,255,255,.3); border-radius:.25rem; }
    .nav-name { font-size:.9rem; font-weight:600; color:var(--text); }
    .nav-right { display:flex; gap:.6rem; align-items:center; }
    .btn-ghost { text-decoration:none; font-size:.83rem; font-weight:500; color:var(--soft); border:1px solid var(--border2); padding:.48rem .9rem; border-radius:.6rem; }
    .btn-ghost:hover { background:var(--surface2); color:var(--text); }
    .theme-toggle { width:2.5rem; height:2.5rem; border-radius:.6rem; border:1px solid var(--border2); background:var(--surface2); cursor:pointer; display:flex; align-items:center; justify-content:center; position:relative; }
    .t-icon { position:absolute; font-size:1.05rem; top:50%; left:50%; transition:opacity .3s, transform .35s cubic-bezier(.34,1.56,.64,1); }
    .t-sun { transform:translate(-50%,-50%) rotate(0deg) scale(1); }
    .t-moon { transform:translate(-50%,-50%) rotate(-90deg) scale(.4); }
    [data-theme="dark"] .t-sun { opacity:1; } [data-theme="dark"] .t-moon { opacity:0; }
    [data-theme="light"] .t-sun { opacity:0; } [data-theme="light"] .t-moon { opacity:1; transform:translate(-50%,-50%) rotate(0deg) scale(1); }
    .page { position:relative; z-index:2; min-height:100vh; display:flex; align-items:center; justify-content:center; padding:90px 1.5rem 3rem; }
    .card { width:100%; max-width:500px; border:1px solid var(--border2); background:var(--surface); border-radius:1.2rem; padding:2.2rem 2rem; backdrop-filter:blur(16px); box-shadow:0 32px 64px rgba(0,0,0,.3); }
    .card::before { content:''; position:absolute; top:-1px; left:15%; right:15%; height:2px; background:linear-gradient(90deg, transparent, var(--amber), transparent); opacity:.7; }
    .card-header { margin-bottom:1.6rem; }
    .card-eyebrow { display:inline-flex; align-items:center; gap:.4rem; font-size:.7rem; font-weight:600; letter-spacing:.1em; text-transform:uppercase; color:var(--amber); margin-bottom:.75rem; }
    .eyebrow-dot { width:5px; height:5px; border-radius:50%; background:var(--amber); }
    .card-title { font-family:'Playfair Display',serif; font-size:1.65rem; font-weight:700; color:var(--heading); margin-bottom:.45rem; }
    .card-sub { font-size:.83rem; color:var(--muted); line-height:1.6; font-weight:300; }
    .divider { height:1px; background:var(--border); margin:1.4rem 0; }
    .field { margin-bottom:.9rem; }
    .field label { display:block; font-size:.78rem; font-weight:500; color:var(--soft); margin-bottom:.35rem; }
    .input-wrap { position:relative; }
    .field input { width:100%; background:var(--input-bg); border:1px solid var(--input-border); color:var(--text); border-radius:.6rem; padding:.72rem .88rem; font-family:'Sora',sans-serif; font-size:.88rem; outline:none; }
    .input-wrap input[type="password"], .input-wrap input[type="text"] { padding-right:2.8rem; }
    .field input:focus { border-color:rgba(245,158,11,.5); box-shadow:0 0 0 3px rgba(245,158,11,.09); }
    .reveal-btn { position:absolute; right:0; top:0; bottom:0; width:2.6rem; display:flex; align-items:center; justify-content:center; background:none; border:none; cursor:pointer; color:var(--muted); border-radius:0 .6rem .6rem 0; }
    .reveal-btn:hover { color:var(--amber); }
    .reveal-btn svg { width:16px; height:16px; stroke-width:1.8; }
    .field-error { color:var(--error); font-size:.75rem; margin-top:.28rem; }
    .actions { display:flex; align-items:center; justify-content:space-between; gap:1rem; margin-top:1.4rem; flex-wrap:wrap; }
    .back-link { font-size:.82rem; color:var(--muted); text-decoration:none; }
    .back-link:hover { color:var(--amber); }
    .submit-btn { border:none; border-radius:.65rem; padding:.74rem 1.5rem; font-family:'Sora',sans-serif; font-size:.88rem; font-weight:600; color:#fff; background:linear-gradient(135deg, var(--amber-d), var(--amber)); box-shadow:0 4px 18px rgba(245,158,11,.28); cursor:pointer; }
    [data-theme="dark"] .submit-btn { color:#0d0f14; }
    .submit-btn:hover { transform:translateY(-1px); box-shadow:0 6px 24px rgba(245,158,11,.38); }
    .card-foot { margin-top:1.2rem; text-align:center; font-size:.78rem; color:var(--muted); }
    .card-foot a { color:var(--amber); text-decoration:none; }
    .card-foot a:hover { text-decoration:underline; }
  </style>
</head>
<body>
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
    <a href="{{ url('/app/login') }}" class="btn-ghost">County Admin Login</a>
  </div>
</nav>

<div class="page">
  <div class="card">
    <div class="card-header">
      <div class="card-eyebrow"><span class="eyebrow-dot"></span> County onboarding</div>
      <h1 class="card-title">Create County Account</h1>
      <p class="card-sub">Register your county administrator account to set up wards and manage county-level bursary reporting.</p>
    </div>

    <div class="divider"></div>

    <form method="POST" action="{{ route('county-register.store') }}">
      @csrf
      <div class="field">
        <label for="county_name">County Name</label>
        <input id="county_name" name="county_name" type="text" value="{{ old('county_name') }}" placeholder="e.g. Kirinyaga County" required>
        @error('county_name')<div class="field-error">{{ $message }}</div>@enderror
      </div>

      <div class="field">
        <label for="name">Full Name</label>
        <input id="name" name="name" type="text" value="{{ old('name') }}" placeholder="Your full name" required autocomplete="name">
        @error('name')<div class="field-error">{{ $message }}</div>@enderror
      </div>

      <div class="field">
        <label for="email">Email Address</label>
        <input id="email" name="email" type="email" value="{{ old('email') }}" placeholder="you@example.com" required autocomplete="email">
        @error('email')<div class="field-error">{{ $message }}</div>@enderror
      </div>

      <div class="field">
        <label for="password">Password</label>
        <div class="input-wrap">
          <input id="password" name="password" type="password" placeholder="Min. 8 characters" required autocomplete="new-password">
          <button type="button" class="reveal-btn" data-target="password" aria-label="Toggle password visibility">
            <svg id="eye-password" xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke="currentColor">
              <path stroke-linecap="round" stroke-linejoin="round" d="M2.458 12C3.732 7.943 7.523 5 12 5c4.478 0 8.268 2.943 9.542 7-1.274 4.057-5.064 7-9.542 7-4.477 0-8.268-2.943-9.542-7z"/>
              <path stroke-linecap="round" stroke-linejoin="round" d="M15 12a3 3 0 11-6 0 3 3 0 016 0z"/>
            </svg>
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
          <input id="password_confirmation" name="password_confirmation" type="password" placeholder="Repeat password" required autocomplete="new-password">
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

      <div class="actions">
        <a href="{{ route('landing') }}" class="back-link">Back to landing</a>
        <button type="submit" class="submit-btn">Create County Account</button>
      </div>
    </form>

    <p class="card-foot">Already have an account? <a href="{{ url('/app/login') }}">Sign in here</a>.</p>
  </div>
</div>

<script>
  const html = document.documentElement;
  const btn = document.getElementById('themeToggle');
  const saved = localStorage.getItem('kbc-theme');
  const sysDark = window.matchMedia('(prefers-color-scheme: dark)').matches;
  html.setAttribute('data-theme', saved || (sysDark ? 'dark' : 'light'));
  btn.addEventListener('click', () => {
    const next = html.getAttribute('data-theme') === 'dark' ? 'light' : 'dark';
    html.setAttribute('data-theme', next);
    localStorage.setItem('kbc-theme', next);
  });

  document.querySelectorAll('.reveal-btn').forEach((toggle) => {
    toggle.addEventListener('click', () => {
      const targetId = toggle.dataset.target;
      const input = document.getElementById(targetId);
      const eyeOn = document.getElementById('eye-' + targetId);
      const eyeOff = document.getElementById('eye-off-' + targetId);

      if (input.type === 'password') {
        input.type = 'text';
        eyeOn.style.display = 'none';
        eyeOff.style.display = 'block';
      } else {
        input.type = 'password';
        eyeOn.style.display = 'block';
        eyeOff.style.display = 'none';
      }

      input.focus();
    });
  });
</script>
</body>
</html>
