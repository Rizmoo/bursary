<!DOCTYPE html>
<html lang="en" data-theme="dark">
<head>
  <meta charset="UTF-8" />
  <meta name="viewport" content="width=device-width, initial-scale=1.0"/>
  <title>  Bursary Cloud — Ward Education Funding Platform</title>
  <link rel="preconnect" href="https://fonts.googleapis.com">
  <link href="https://fonts.googleapis.com/css2?family=Playfair+Display:ital,wght@0,700;0,900;1,700&family=Sora:wght@300;400;500;600&display=swap" rel="stylesheet">
  <style>
    *, *::before, *::after { box-sizing: border-box; margin: 0; padding: 0; }

    /* ═══════════════════════════════════════
       THEME TOKENS
    ═══════════════════════════════════════ */
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
      --nav-bg:    rgba(6,9,15,.78);
      --nav-bg-s:  rgba(6,9,15,.97);
      --hero-grid: rgba(255,255,255,.032);
      --glow-op:   .09;
      --mob-menu:  rgba(6,9,15,.98);
      --stats-bg:  rgba(13,17,23,.72);
      --mock-bg:   #111827;
      --mock-row:  rgba(6,9,15,.55);
      --about-bg:  linear-gradient(145deg,#111827,#0d1117);
      --footer-bg: rgba(6,9,15,.82);
      --cta-bg:    linear-gradient(135deg,rgba(180,83,9,.16) 0%,rgba(13,17,23,.95) 50%,rgba(45,212,191,.08) 100%);
      --shadow-card: 0 40px 80px rgba(0,0,0,.5);
      --ic-amber:  rgba(245,158,11,.12);
      --ic-teal:   rgba(45,212,191,.1);
      --ic-blue:   rgba(96,165,250,.1);
      --bar-track: rgba(255,255,255,.06);
      --mb-green-bg: rgba(52,211,153,.12);
      --mb-green-c:  #34d399;
    }

    [data-theme="light"] {
      --bg:        #f5f4f0;
      --bg2:       #edecea;
      --bg3:       #e4e2de;
      --surface:   rgba(255,253,248,.93);
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
      --nav-bg:    rgba(245,244,240,.82);
      --nav-bg-s:  rgba(245,244,240,.97);
      --hero-grid: rgba(0,0,0,.055);
      --glow-op:   .07;
      --mob-menu:  rgba(245,244,240,.99);
      --stats-bg:  rgba(237,235,230,.8);
      --mock-bg:   #fff;
      --mock-row:  rgba(245,244,240,.85);
      --about-bg:  linear-gradient(145deg,#fff,#f0eeea);
      --footer-bg: rgba(228,226,222,.9);
      --cta-bg:    linear-gradient(135deg,rgba(217,119,6,.1) 0%,rgba(245,244,240,.97) 50%,rgba(13,148,136,.07) 100%);
      --shadow-card: 0 20px 60px rgba(0,0,0,.1);
      --ic-amber:  rgba(217,119,6,.1);
      --ic-teal:   rgba(13,148,136,.1);
      --ic-blue:   rgba(37,99,235,.09);
      --bar-track: rgba(0,0,0,.07);
      --mb-green-bg: rgba(5,150,105,.1);
      --mb-green-c:  #059669;
    }

    html { scroll-behavior: smooth; }

    body {
      font-family: 'Sora', sans-serif;
      background: var(--bg);
      color: var(--text);
      overflow-x: hidden;
      transition: background .35s, color .35s;
    }

    body::before {
      content: '';
      position: fixed;
      inset: 0;
      z-index: 0;
      pointer-events: none;
      transition: background .35s;
    }

    [data-theme="dark"] body::before {
      background:
        radial-gradient(ellipse 90% 55% at 0% 0%,   rgba(245,158,11,.11) 0%, transparent 60%),
        radial-gradient(ellipse 70% 50% at 100% 5%,  rgba(45,212,191,.07) 0%, transparent 55%),
        radial-gradient(ellipse 60% 60% at 50% 100%, rgba(96,165,250,.06) 0%, transparent 60%);
    }

    [data-theme="light"] body::before {
      background:
        radial-gradient(ellipse 90% 55% at 0% 0%,   rgba(217,119,6,.07) 0%, transparent 60%),
        radial-gradient(ellipse 70% 50% at 100% 5%,  rgba(13,148,136,.05) 0%, transparent 55%),
        radial-gradient(ellipse 60% 60% at 50% 100%, rgba(37,99,235,.04) 0%, transparent 60%);
    }

    /* ═══════════════════════════════════════
       THEME TOGGLE
    ═══════════════════════════════════════ */
    .theme-toggle {
      width: 2.5rem;
      height: 2.5rem;
      border-radius: .6rem;
      border: 1px solid var(--border2);
      background: var(--surface2);
      cursor: pointer;
      display: flex;
      align-items: center;
      justify-content: center;
      flex-shrink: 0;
      transition: background .25s, border-color .25s, transform .15s, box-shadow .2s;
      position: relative;
      overflow: hidden;
    }

    .theme-toggle:hover {
      transform: scale(1.1) rotate(12deg);
      box-shadow: 0 4px 14px rgba(245,158,11,.25);
      border-color: var(--amber);
    }

    .theme-toggle .t-icon {
      position: absolute;
      font-size: 1.05rem;
      line-height: 1;
      transition: opacity .3s, transform .35s cubic-bezier(.34,1.56,.64,1);
    }

    .t-sun  { top: 50%; left: 50%; transform: translate(-50%,-50%) rotate(0deg) scale(1); }
    .t-moon { top: 50%; left: 50%; transform: translate(-50%,-50%) rotate(-90deg) scale(.4); }

    [data-theme="dark"]  .t-sun  { opacity: 1; transform: translate(-50%,-50%) rotate(0deg) scale(1); }
    [data-theme="dark"]  .t-moon { opacity: 0; transform: translate(-50%,-50%) rotate(90deg) scale(.4); }
    [data-theme="light"] .t-sun  { opacity: 0; transform: translate(-50%,-50%) rotate(-90deg) scale(.4); }
    [data-theme="light"] .t-moon { opacity: 1; transform: translate(-50%,-50%) rotate(0deg) scale(1); }

    /* ═══════════════════════════════════════
       NAV
    ═══════════════════════════════════════ */
    nav {
      position: fixed;
      top: 0; left: 0; right: 0;
      z-index: 100;
      padding: 0 clamp(1.5rem, 5vw, 4rem);
      height: 70px;
      display: flex;
      align-items: center;
      justify-content: space-between;
      background: var(--nav-bg);
      backdrop-filter: blur(20px) saturate(1.5);
      border-bottom: 1px solid var(--border);
      transition: background .3s, border-color .3s;
    }

    .nav-logo { display: flex; align-items: center; gap: .65rem; text-decoration: none; }

    .nav-mark {
      width: 2rem; height: 2rem;
      border-radius: .5rem;
      background: linear-gradient(135deg, var(--amber), var(--amber-l));
      box-shadow: 0 0 16px rgba(245,158,11,.3);
      flex-shrink: 0;
      position: relative;
      transition: box-shadow .3s;
    }

    .nav-mark::after {
      content: '';
      position: absolute;
      inset: 5px;
      border: 1.5px solid rgba(255,255,255,.3);
      border-radius: .25rem;
    }

    .nav-name { font-size: .9rem; font-weight: 600; color: var(--text); letter-spacing: -.01em; transition: color .3s; }

    .nav-links { display: flex; align-items: center; gap: 2rem; list-style: none; }
    .nav-links a { text-decoration: none; font-size: .83rem; font-weight: 500; color: var(--muted); transition: color .18s; }
    .nav-links a:hover { color: var(--text); }

    .nav-cta { display: flex; gap: .6rem; align-items: center; }

    .btn { display: inline-flex; align-items: center; gap: .4rem; text-decoration: none; border-radius: .6rem; font-family: 'Sora', sans-serif; font-size: .83rem; font-weight: 600; cursor: pointer; transition: all .18s; border: none; }
    .btn-outline { padding: .48rem .9rem; color: var(--soft); border: 1px solid var(--border2); background: transparent; }
    .btn-outline:hover { background: var(--surface2); color: var(--text); }
    .btn-primary { padding: .52rem 1.1rem; color: #fff; background: linear-gradient(135deg, var(--amber-d), var(--amber)); box-shadow: 0 4px 18px rgba(245,158,11,.25); }
    [data-theme="dark"] .btn-primary { color: #0d0f14; }
    .btn-primary:hover { transform: translateY(-1px); box-shadow: 0 6px 24px rgba(245,158,11,.38); }
    .btn-lg { padding: .8rem 1.8rem; font-size: .95rem; border-radius: .75rem; }

    .hamburger { display: none; flex-direction: column; gap: 5px; cursor: pointer; padding: .4rem; }
    .hamburger span { display: block; width: 22px; height: 2px; background: var(--soft); border-radius: 2px; transition: all .25s; }

    /* ═══════════════════════════════════════
       HERO
    ═══════════════════════════════════════ */
    #hero {
      position: relative; z-index: 1;
      min-height: 100vh;
      display: flex; flex-direction: column; align-items: center; justify-content: center;
      text-align: center;
      padding: 8rem clamp(1.5rem, 5vw, 4rem) 5rem;
      overflow: hidden;
    }

    .hero-grid-bg {
      position: absolute; inset: 0;
      background-image:
        linear-gradient(var(--hero-grid) 1px, transparent 1px),
        linear-gradient(90deg, var(--hero-grid) 1px, transparent 1px);
      background-size: 64px 64px;
      mask-image: radial-gradient(ellipse 75% 75% at 50% 50%, black 30%, transparent 75%);
      transition: background-image .3s;
    }

    .hero-glow {
      position: absolute; width: 700px; height: 700px; border-radius: 50%;
      background: radial-gradient(circle, rgba(245,158,11,var(--glow-op)) 0%, transparent 65%);
      top: 50%; left: 50%; transform: translate(-50%,-55%);
      pointer-events: none; animation: breathe 6s ease-in-out infinite;
    }

    @keyframes breathe {
      0%,100% { transform: translate(-50%,-55%) scale(1); opacity: 1; }
      50%      { transform: translate(-50%,-55%) scale(1.1); opacity: .7; }
    }

    .hero-pill {
      display: inline-flex; align-items: center; gap: .5rem;
      border: 1px solid rgba(245,158,11,.3); background: rgba(245,158,11,.08);
      border-radius: 99px; padding: .35rem .9rem;
      font-size: .75rem; font-weight: 600; letter-spacing: .08em; text-transform: uppercase;
      color: var(--amber); margin-bottom: 1.6rem; animation: fadeUp .6s ease both;
    }

    .hero-pill-dot { width: 6px; height: 6px; border-radius: 50%; background: var(--amber); box-shadow: 0 0 6px var(--amber); animation: pulse 2s infinite; }

    @keyframes pulse { 0%,100%{opacity:1;transform:scale(1)} 50%{opacity:.4;transform:scale(.65)} }

    h1.hero-title {
      font-family: 'Playfair Display', serif;
      font-size: clamp(2.6rem, 6vw, 5rem); font-weight: 900; line-height: 1.05; letter-spacing: -.03em;
      color: var(--heading); max-width: 800px; margin: 0 auto 1.4rem;
      animation: fadeUp .6s .1s ease both; transition: color .3s;
    }

    h1.hero-title em { font-style: italic; color: var(--amber); }

    .hero-sub { font-size: clamp(.9rem, 1.5vw, 1.1rem); color: var(--muted); font-weight: 300; line-height: 1.75; max-width: 540px; margin: 0 auto 2.4rem; animation: fadeUp .6s .2s ease both; }

    .hero-actions { display: flex; gap: .85rem; justify-content: center; flex-wrap: wrap; animation: fadeUp .6s .3s ease both; }

    .hero-scroll {
      position: absolute; bottom: 2.2rem; left: 50%; transform: translateX(-50%);
      display: flex; flex-direction: column; align-items: center; gap: .4rem;
      color: var(--muted); font-size: .7rem; letter-spacing: .08em; text-transform: uppercase;
      animation: fadeUp .6s .6s ease both;
    }

    .scroll-line { width: 1px; height: 36px; background: linear-gradient(to bottom, var(--amber), transparent); animation: scrollPulse 2s ease-in-out infinite; }

    @keyframes scrollPulse { 0%,100%{opacity:.4} 50%{opacity:1} }

    /* ═══════════════════════════════════════
       STATS BAR
    ═══════════════════════════════════════ */
    .stats-bar {
      position: relative; z-index: 1;
      display: grid; grid-template-columns: repeat(4, 1fr);
      border-top: 1px solid var(--border); border-bottom: 1px solid var(--border);
      background: var(--stats-bg); backdrop-filter: blur(8px);
      transition: background .3s, border-color .3s;
    }

    .stat-item { padding: 2rem 1.5rem; text-align: center; border-right: 1px solid var(--border); transition: background .2s; }
    .stat-item:last-child { border-right: none; }
    .stat-item:hover { background: rgba(245,158,11,.04); }
    .stat-num { font-family: 'Playfair Display', serif; font-size: 2.2rem; font-weight: 700; color: var(--amber); display: block; line-height: 1; margin-bottom: .4rem; }
    .stat-desc { font-size: .78rem; color: var(--muted); font-weight: 400; }

    /* ═══════════════════════════════════════
       SHARED
    ═══════════════════════════════════════ */
    .section-label { font-size: .72rem; font-weight: 600; letter-spacing: .12em; text-transform: uppercase; color: var(--amber); margin-bottom: .9rem; }

    .section-title { font-family: 'Playfair Display', serif; font-size: clamp(1.8rem, 3.5vw, 2.8rem); font-weight: 700; line-height: 1.1; letter-spacing: -.02em; color: var(--heading); max-width: 500px; margin-bottom: 1rem; transition: color .3s; }

    .section-sub { font-size: .93rem; color: var(--muted); line-height: 1.75; max-width: 480px; margin-bottom: 3.5rem; font-weight: 300; }

    /* ═══════════════════════════════════════
       FEATURES
    ═══════════════════════════════════════ */
    #features { position: relative; z-index: 1; padding: 7rem clamp(1.5rem, 5vw, 4rem); max-width: 1200px; margin: 0 auto; }

    .features-grid { display: grid; grid-template-columns: repeat(3, 1fr); gap: 1.1rem; }

    .feat-card {
      border: 1px solid var(--border); background: var(--surface);
      border-radius: 1rem; padding: 1.7rem;
      transition: border-color .22s, transform .22s, background .3s;
      position: relative; overflow: hidden;
    }

    .feat-card::before { content: ''; position: absolute; top: 0; left: 0; right: 0; height: 1px; background: linear-gradient(90deg, transparent, var(--amber), transparent); opacity: 0; transition: opacity .25s; }
    .feat-card:hover { border-color: rgba(245,158,11,.28); transform: translateY(-3px); }
    .feat-card:hover::before { opacity: .7; }

    .feat-icon { width: 2.6rem; height: 2.6rem; border-radius: .65rem; display: flex; align-items: center; justify-content: center; font-size: 1.2rem; margin-bottom: 1.1rem; transition: background .3s; }
    .ic-amber { background: var(--ic-amber); }
    .ic-teal  { background: var(--ic-teal); }
    .ic-blue  { background: var(--ic-blue); }

    .feat-title { font-size: .97rem; font-weight: 600; color: var(--text); margin-bottom: .5rem; transition: color .3s; }
    .feat-desc  { font-size: .83rem; color: var(--muted); line-height: 1.65; font-weight: 300; }

    /* ═══════════════════════════════════════
       HOW IT WORKS
    ═══════════════════════════════════════ */
    #how {
      position: relative; z-index: 1;
      background: var(--surface2);
      border-top: 1px solid var(--border); border-bottom: 1px solid var(--border);
      padding: 7rem clamp(1.5rem, 5vw, 4rem);
      transition: background .3s;
    }

    .how-inner { max-width: 1200px; margin: 0 auto; display: grid; grid-template-columns: 1fr 1.4fr; gap: 5rem; align-items: center; }

    .steps { display: flex; flex-direction: column; gap: 1.4rem; margin-top: 2rem; }
    .step  { display: flex; gap: 1.1rem; align-items: flex-start; }

    .step-num { width: 2.2rem; height: 2.2rem; border-radius: 50%; background: linear-gradient(135deg, var(--amber-d), var(--amber)); color: #fff; font-size: .78rem; font-weight: 700; display: flex; align-items: center; justify-content: center; flex-shrink: 0; margin-top: .15rem; box-shadow: 0 4px 14px rgba(245,158,11,.28); }
    [data-theme="dark"] .step-num { color: #0d0f14; }

    .step-text strong { display: block; font-size: .93rem; color: var(--text); margin-bottom: .3rem; transition: color .3s; }
    .step-text p { font-size: .82rem; color: var(--muted); line-height: 1.6; font-weight: 300; }

    .mockup { border: 1px solid var(--border2); background: var(--mock-bg); border-radius: 1.2rem; padding: 1.5rem; box-shadow: var(--shadow-card); position: relative; transition: background .3s, box-shadow .3s; }
    .mockup::before { content: ''; position: absolute; top: -1px; left: 20%; right: 20%; height: 2px; background: linear-gradient(90deg, transparent, var(--amber), transparent); border-radius: 0 0 4px 4px; }

    .mock-bar { display: flex; gap: .45rem; margin-bottom: 1.2rem; }
    .mock-dot { width: 10px; height: 10px; border-radius: 50%; }
    .d-red { background: #f87171; } .d-yellow { background: #fcd34d; } .d-green { background: #34d399; }

    .mock-title { font-size: .72rem; font-weight: 600; color: var(--muted); letter-spacing: .07em; text-transform: uppercase; margin-bottom: 1rem; }

    .mock-stats { display: grid; grid-template-columns: repeat(3, 1fr); gap: .6rem; margin-bottom: 1.1rem; }
    .mock-stat { background: var(--surface2); border: 1px solid var(--border); border-radius: .6rem; padding: .7rem .8rem; transition: background .3s; }
    .mock-stat-val { font-family: 'Playfair Display', serif; font-size: 1.3rem; color: var(--amber); }
    .mock-stat-lbl { font-size: .65rem; color: var(--muted); }

    .mock-row { display: flex; align-items: center; justify-content: space-between; padding: .6rem .7rem; border-radius: .5rem; margin-bottom: .4rem; background: var(--mock-row); border: 1px solid var(--border); font-size: .76rem; transition: background .3s; }
    .mock-row-name { color: var(--soft); }
    .mock-row-amt  { color: var(--amber); font-weight: 600; }
    .mock-badge { font-size: .65rem; padding: .18rem .5rem; border-radius: 99px; font-weight: 600; }
    .mb-green { background: var(--mb-green-bg); color: var(--mb-green-c); }
    .mb-amber { background: rgba(245,158,11,.12); color: var(--amber); }

    /* ═══════════════════════════════════════
       ABOUT
    ═══════════════════════════════════════ */
    #about { position: relative; z-index: 1; padding: 7rem clamp(1.5rem, 5vw, 4rem); max-width: 1200px; margin: 0 auto; display: grid; grid-template-columns: 1fr 1fr; gap: 5rem; align-items: center; }

    .about-img-wrap { position: relative; }

    .about-img-block { border: 1px solid var(--border2); border-radius: 1.2rem; overflow: hidden; background: var(--about-bg); aspect-ratio: 4/3; display: flex; align-items: center; justify-content: center; transition: background .3s; }

    .about-graphic { width: 90%; display: flex; flex-direction: column; gap: .6rem; }
    .ab-row  { display: flex; align-items: center; gap: .7rem; }
    .ab-icon { width: 2.4rem; height: 2.4rem; border-radius: .55rem; display: flex; align-items: center; justify-content: center; font-size: 1rem; flex-shrink: 0; }
    .ab-bar-wrap { flex: 1; }
    .ab-label { font-size: .72rem; color: var(--muted); margin-bottom: .25rem; }
    .ab-bar   { height: 6px; border-radius: 99px; background: var(--bar-track); overflow: hidden; transition: background .3s; }
    .ab-fill  { height: 100%; border-radius: 99px; }
    .fill-amber { background: linear-gradient(90deg, var(--amber-d), var(--amber-l)); }
    .fill-teal  { background: linear-gradient(90deg, #0d9488, var(--teal)); }
    .fill-blue  { background: linear-gradient(90deg, #2563eb, var(--blue)); }

    .about-float { position: absolute; bottom: -1.2rem; right: -1.2rem; border: 1px solid var(--border2); background: var(--mock-bg); border-radius: .85rem; padding: .85rem 1.1rem; display: flex; align-items: center; gap: .75rem; box-shadow: 0 20px 40px rgba(0,0,0,.15); transition: background .3s; }
    .af-icon { font-size: 1.5rem; }
    .af-val  { font-family: 'Playfair Display', serif; font-size: 1.3rem; color: var(--text); display: block; line-height: 1; }
    .af-lbl  { font-size: .7rem; color: var(--muted); }

    .about-text .section-sub { max-width: 100%; }

    .about-list { list-style: none; display: flex; flex-direction: column; gap: .7rem; margin-top: 1.8rem; }
    .about-list li { display: flex; align-items: flex-start; gap: .75rem; font-size: .88rem; color: var(--soft); line-height: 1.55; transition: color .3s; }
    .about-list li::before { content: '→'; color: var(--amber); font-weight: 700; flex-shrink: 0; margin-top: .05rem; }

    /* ═══════════════════════════════════════
       CTA
    ═══════════════════════════════════════ */
    #cta { position: relative; z-index: 1; margin: 0 clamp(1rem, 3vw, 3rem) 5rem; border: 1px solid rgba(245,158,11,.2); background: var(--cta-bg); border-radius: 1.5rem; padding: 4.5rem clamp(2rem, 6vw, 5rem); text-align: center; overflow: hidden; transition: background .3s; }
    #cta::before { content: ''; position: absolute; top: -2px; left: 15%; right: 15%; height: 2px; background: linear-gradient(90deg, transparent, var(--amber), transparent); }
    #cta h2 { font-family: 'Playfair Display', serif; font-size: clamp(1.8rem, 3.5vw, 2.8rem); font-weight: 700; letter-spacing: -.02em; color: var(--heading); margin-bottom: 1rem; transition: color .3s; }
    #cta p  { font-size: .95rem; color: var(--muted); max-width: 480px; margin: 0 auto 2.2rem; line-height: 1.7; font-weight: 300; }
    .cta-actions { display: flex; gap: .85rem; justify-content: center; flex-wrap: wrap; }

    /* ═══════════════════════════════════════
       FOOTER
    ═══════════════════════════════════════ */
    footer { position: relative; z-index: 1; border-top: 1px solid var(--border); background: var(--footer-bg); padding: 3.5rem clamp(1.5rem, 5vw, 4rem) 2rem; transition: background .3s, border-color .3s; }
    .footer-inner { max-width: 1200px; margin: 0 auto; }
    .footer-top { display: grid; grid-template-columns: 1.8fr 1fr 1fr 1fr; gap: 3rem; padding-bottom: 3rem; border-bottom: 1px solid var(--border); }
    .footer-brand .nav-name { font-size: 1rem; margin-top: .5rem; display: block; }
    .footer-brand p { font-size: .82rem; color: var(--muted); line-height: 1.7; margin-top: .7rem; max-width: 260px; font-weight: 300; }
    .footer-col h4 { font-size: .75rem; font-weight: 600; letter-spacing: .1em; text-transform: uppercase; color: var(--soft); margin-bottom: 1.1rem; }
    .footer-col ul { list-style: none; display: flex; flex-direction: column; gap: .6rem; }
    .footer-col a { text-decoration: none; font-size: .83rem; color: var(--muted); font-weight: 300; transition: color .18s; }
    .footer-col a:hover { color: var(--amber); }
    .footer-bottom { display: flex; justify-content: space-between; align-items: center; padding-top: 2rem; font-size: .78rem; color: var(--muted); flex-wrap: wrap; gap: 1rem; }
    .footer-bottom a { color: var(--muted); text-decoration: none; }
    .footer-bottom a:hover { color: var(--soft); }
    .footer-links { display: flex; gap: 1.5rem; }

    /* ═══════════════════════════════════════
       ANIMATIONS
    ═══════════════════════════════════════ */
    @keyframes fadeUp { from { opacity: 0; transform: translateY(20px); } to { opacity: 1; transform: none; } }

    .reveal { opacity: 0; transform: translateY(24px); transition: opacity .65s ease, transform .65s ease; }
    .reveal.visible { opacity: 1; transform: none; }

    /* ═══════════════════════════════════════
       RESPONSIVE
    ═══════════════════════════════════════ */
    @media (max-width: 960px) {
      .features-grid { grid-template-columns: 1fr 1fr; }
      .how-inner { grid-template-columns: 1fr; gap: 3rem; }
      #about { grid-template-columns: 1fr; gap: 3rem; }
      .footer-top { grid-template-columns: 1fr 1fr; gap: 2rem; }
      .stats-bar { grid-template-columns: repeat(2, 1fr); }
      .stat-item:nth-child(2) { border-right: none; }
    }

    @media (max-width: 720px) {
      .nav-links { display: none; }
      .hamburger { display: flex; }
      .features-grid { grid-template-columns: 1fr; }
      .footer-top { grid-template-columns: 1fr; gap: 2rem; }
      .footer-brand p { max-width: 100%; }
    }

    @media (max-width: 480px) {
      .mock-stats { grid-template-columns: 1fr 1fr; }
    }
  </style>
</head>
<body>

<!-- ════════════ NAV ════════════ -->
<nav id="main-nav">
  <a class="nav-logo" href="#">
    <div class="nav-mark"></div>
    <span class="nav-name">  Bursary Cloud</span>
  </a>

  <ul class="nav-links">
    <li><a href="#features">Features</a></li>
    <li><a href="#how">How It Works</a></li>
    <li><a href="#about">About</a></li>
    <li><a href="#cta">Register</a></li>
  </ul>

  <div class="nav-cta">
    <button class="theme-toggle" id="themeToggle" title="Toggle theme" aria-label="Toggle light/dark mode">
      <span class="t-icon t-sun">☀️</span>
      <span class="t-icon t-moon">🌙</span>
    </button>
    <a href="/admin/login" class="btn btn-outline">Admin Login</a>
    <a href="#cta" class="btn btn-primary">Get Started</a>
  </div>

  <div class="hamburger" id="hbg">
    <span></span><span></span><span></span>
  </div>
</nav>


<!-- ════════════ HERO ════════════ -->
<section id="hero">
  <div class="hero-grid-bg"></div>
  <div class="hero-glow"></div>

  <div class="hero-pill">
    <span class="hero-pill-dot"></span>
      County &nbsp;·&nbsp; Ward Education Fund
  </div>

  <h1 class="hero-title">
    Empowering students<br>through <em>smarter bursaries</em>
  </h1>

  <p class="hero-sub">
    A modern, ward-scoped platform that digitises every step of the bursary lifecycle — from application intake to cheque clearance, reconciliation, and annual reporting.
  </p>

  <div class="hero-actions">
    <a href="#cta" class="btn btn-primary btn-lg">Register Your Ward</a>
    <a href="#features" class="btn btn-outline btn-lg">Explore Features</a>
  </div>

  <div class="hero-scroll">
    <span>Scroll</span>
    <div class="scroll-line"></div>
  </div>
</section>


<!-- ════════════ STATS ════════════ -->
<div class="stats-bar">
  <div class="stat-item reveal"><span class="stat-num">47</span><span class="stat-desc">Wards Supported</span></div>
  <div class="stat-item reveal"><span class="stat-num">12k+</span><span class="stat-desc">Students Funded</span></div>
  <div class="stat-item reveal"><span class="stat-num">100%</span><span class="stat-desc">Audit-Ready Records</span></div>
  <div class="stat-item reveal"><span class="stat-num">99.9%</span><span class="stat-desc">Platform Uptime</span></div>
</div>


<!-- ════════════ FEATURES ════════════ -->
<section id="features">
  <div class="reveal">
    <p class="section-label">Platform Features</p>
    <h2 class="section-title">Everything your ward needs, in one place</h2>
    <p class="section-sub">Built specifically for Kenyan ward bursary administration — not a generic system retrofitted to your workflow.</p>
  </div>
  <div class="features-grid">
    <div class="feat-card reveal"><div class="feat-icon ic-amber">🎓</div><div class="feat-title">Applicant Management</div><p class="feat-desc">Capture, track, and manage every student application with institution records, eligibility checks, and approval workflows.</p></div>
    <div class="feat-card reveal"><div class="feat-icon ic-teal">🏦</div><div class="feat-title">Cheque Lifecycle</div><p class="feat-desc">Full tracking from issuance through clearance — handle stale cheques, returns, re-issues, and cancellations with a complete audit trail.</p></div>
    <div class="feat-card reveal"><div class="feat-icon ic-blue">📊</div><div class="feat-title">Bank Reconciliation</div><p class="feat-desc">Smart matching of cleared cheques against bank statements, with automated penalty flags and discrepancy reporting.</p></div>
    <div class="feat-card reveal"><div class="feat-icon ic-amber">📁</div><div class="feat-title">Excel &amp; PDF Exports</div><p class="feat-desc">Generate payment schedules, reconciliation reports, and quarterly summaries at the click of a button — ready for the county treasury.</p></div>
    <div class="feat-card reveal"><div class="feat-icon ic-teal">🔒</div><div class="feat-title">Multi-Tenant Security</div><p class="feat-desc">Every ward operates in a fully isolated workspace. No data leakage between tenants, with role-based access controls throughout.</p></div>
    <div class="feat-card reveal"><div class="feat-icon ic-blue">📅</div><div class="feat-title">Financial-Year Analytics</div><p class="feat-desc">Year-scoped dashboards with disbursement trends, institution breakdowns, and executive summaries for leadership review.</p></div>
  </div>
</section>


<!-- ════════════ HOW IT WORKS ════════════ -->
<section id="how">
  <div class="how-inner">
    <div>
      <p class="section-label reveal">How It Works</p>
      <h2 class="section-title reveal">From application to clearance in four steps</h2>
      <p class="section-sub reveal">No paper. No spreadsheets over WhatsApp. A single source of truth for every bursary transaction.</p>
      <div class="steps">
        <div class="step reveal"><div class="step-num">1</div><div class="step-text"><strong>Register your ward</strong><p>Create a secure ward account. Each ward gets its own isolated tenant with full administrator control.</p></div></div>
        <div class="step reveal"><div class="step-num">2</div><div class="step-text"><strong>Capture applications &amp; institutions</strong><p>Add student applicants and link them to schools, colleges, and universities from your institution library.</p></div></div>
        <div class="step reveal"><div class="step-num">3</div><div class="step-text"><strong>Assign and track cheques</strong><p>Issue cheques against approved amounts, then track every status change — cleared, stale, returned, or re-issued.</p></div></div>
        <div class="step reveal"><div class="step-num">4</div><div class="step-text"><strong>Reconcile and report</strong><p>Run bank reconciliation, export quarterly reports, and present audit-ready financials to county leadership.</p></div></div>
      </div>
    </div>

    <div class="mockup reveal">
      <div class="mock-bar"><div class="mock-dot d-red"></div><div class="mock-dot d-yellow"></div><div class="mock-dot d-green"></div></div>
      <p class="mock-title">Bursary Dashboard · FY 2024/25</p>
      <div class="mock-stats">
        <div class="mock-stat"><div class="mock-stat-val">847</div><div class="mock-stat-lbl">Applicants</div></div>
        <div class="mock-stat"><div class="mock-stat-val">KES 4.2M</div><div class="mock-stat-lbl">Disbursed</div></div>
        <div class="mock-stat"><div class="mock-stat-val">93%</div><div class="mock-stat-lbl">Cleared</div></div>
      </div>
      <div class="mock-row"><span class="mock-row-name">  University</span><span class="mock-row-amt">KES 480,000</span><span class="mock-badge mb-green">Cleared</span></div>
      <div class="mock-row"><span class="mock-row-name">Thika Technical</span><span class="mock-row-amt">KES 210,000</span><span class="mock-badge mb-green">Cleared</span></div>
      <div class="mock-row"><span class="mock-row-name">Kerugoya High School</span><span class="mock-row-amt">KES 96,000</span><span class="mock-badge mb-amber">Pending</span></div>
      <div class="mock-row"><span class="mock-row-name">St. Joseph's Secondary</span><span class="mock-row-amt">KES 72,000</span><span class="mock-badge mb-green">Cleared</span></div>
    </div>
  </div>
</section>


<!-- ════════════ ABOUT ════════════ -->
<section id="about">
  <div class="about-img-wrap reveal">
    <div class="about-img-block">
      <div class="about-graphic">
        <div class="ab-row"><div class="ab-icon ic-amber">📋</div><div class="ab-bar-wrap"><div class="ab-label">Application Processing</div><div class="ab-bar"><div class="ab-fill fill-amber" style="width:88%"></div></div></div></div>
        <div class="ab-row"><div class="ab-icon ic-teal">✅</div><div class="ab-bar-wrap"><div class="ab-label">Cheque Clearance Rate</div><div class="ab-bar"><div class="ab-fill fill-teal" style="width:93%"></div></div></div></div>
        <div class="ab-row"><div class="ab-icon ic-blue">🏫</div><div class="ab-bar-wrap"><div class="ab-label">Institutions Covered</div><div class="ab-bar"><div class="ab-fill fill-blue" style="width:76%"></div></div></div></div>
        <div class="ab-row"><div class="ab-icon ic-amber">📊</div><div class="ab-bar-wrap"><div class="ab-label">Report Accuracy</div><div class="ab-bar"><div class="ab-fill fill-amber" style="width:100%"></div></div></div></div>
      </div>
    </div>
    <div class="about-float">
      <div class="af-icon">🎓</div>
      <div><span class="af-val">12,400+</span><span class="af-lbl">Students supported</span></div>
    </div>
  </div>

  <div class="about-text reveal">
    <p class="section-label">About the Platform</p>
    <h2 class="section-title">Built for  . Designed for impact.</h2>
    <p class="section-sub">  Bursary Cloud was built to solve the real challenges ward administrators face every financial year — lost cheques, manual spreadsheets, duplicated records, and audit pressure with no paper trail.</p>
    <ul class="about-list">
      <li>Fully ward-scoped multi-tenancy — your data, your ward, no cross-contamination</li>
      <li>Designed around the actual county bursary workflow, not generic finance logic</li>
      <li>Exports that match county treasury formats and quarterly reporting requirements</li>
      <li>Built to be accessible for non-technical ward office staff from day one</li>
    </ul>
  </div>
</section>


<!-- ════════════ CTA ════════════ -->
<section id="cta">
  <h2 class="reveal">Ready to modernise your ward's bursary?</h2>
  <p class="reveal">Register in minutes. No contracts, no complicated setup — your ward workspace is ready the moment you sign up.</p>
  <div class="cta-actions reveal">
    <a href="/self-register" class="btn btn-primary btn-lg">Create Ward Account</a>
    <a href="/admin/login" class="btn btn-outline btn-lg">Existing Ward? Login</a>
  </div>
</section>


<!-- ════════════ FOOTER ════════════ -->
<footer>
  <div class="footer-inner">
    <div class="footer-top">
      <div class="footer-brand">
        <a class="nav-logo" href="#"><div class="nav-mark"></div></a>
        <span class="nav-name">Bursary Cloud</span>
        <p>A modern, multi-tenant platform for managing ward-based education bursaries across  County.</p>
      </div>
      <div class="footer-col">
        <h4>Platform</h4>
        <ul><li><a href="#features">Features</a></li><li><a href="#how">How It Works</a></li><li><a href="#about">About</a></li><li><a href="#cta">Register</a></li></ul>
      </div>
      <div class="footer-col">
        <h4>Account</h4>
        <ul><li><a href="/self-register">Self Registration</a></li><li><a href="/admin/login">Admin Login</a></li><li><a href="#">Forgot Password</a></li></ul>
      </div>
      <div class="footer-col">
        <h4>Support</h4>
        <ul><li><a href="#">Documentation</a></li><li><a href="#">Contact Us</a></li><li><a href="#">County Portal</a></li></ul>
      </div>
    </div>
    <div class="footer-bottom">
      <span>© 2025   Bursary Cloud. All rights reserved.</span>
      <div class="footer-links"><a href="#">Privacy Policy</a><a href="#">Terms of Use</a></div>
    </div>
  </div>
</footer>


<script>
  const html = document.documentElement;
  const btn  = document.getElementById('themeToggle');

  // Init: respect saved or system preference
  const saved    = localStorage.getItem('kbc-theme');
  const sysDark  = window.matchMedia('(prefers-color-scheme: dark)').matches;
  html.setAttribute('data-theme', saved || (sysDark ? 'dark' : 'light'));

  btn.addEventListener('click', () => {
    const next = html.getAttribute('data-theme') === 'dark' ? 'light' : 'dark';
    html.setAttribute('data-theme', next);
    localStorage.setItem('kbc-theme', next);
    syncNav();
  });

  // Nav tint on scroll
  function syncNav() {
    const dark    = html.getAttribute('data-theme') === 'dark';
    const scrolled = window.scrollY > 40;
    document.getElementById('main-nav').style.background =
      dark
        ? (scrolled ? 'rgba(6,9,15,.97)'         : 'rgba(6,9,15,.78)')
        : (scrolled ? 'rgba(245,244,240,.97)'     : 'rgba(245,244,240,.82)');
  }

  window.addEventListener('scroll', syncNav);
  syncNav();

  // Hamburger
  document.getElementById('hbg').addEventListener('click', function() {
    const links = document.querySelector('.nav-links');
    const dark  = html.getAttribute('data-theme') === 'dark';
    const open  = links.style.display === 'flex';
    links.style.cssText = open ? '' :
      `display:flex;flex-direction:column;gap:1rem;position:absolute;
       top:70px;left:0;right:0;z-index:99;
       background:${dark ? 'rgba(6,9,15,.98)' : 'rgba(245,244,240,.99)'};
       padding:1.5rem 2rem;border-bottom:1px solid var(--border)`;
  });

  // Scroll reveal
  const io = new IntersectionObserver((entries) => {
    entries.forEach((e, i) => {
      if (e.isIntersecting) {
        e.target.style.transitionDelay = (i * 0.06) + 's';
        e.target.classList.add('visible');
        io.unobserve(e.target);
      }
    });
  }, { threshold: 0.12 });
  document.querySelectorAll('.reveal').forEach(el => io.observe(el));

  // Animated progress bars
  const barsObs = new IntersectionObserver((entries) => {
    entries.forEach(e => {
      if (e.isIntersecting) {
        e.target.style.transition = 'width 1.2s cubic-bezier(.4,0,.2,1)';
        barsObs.unobserve(e.target);
      }
    });
  }, { threshold: 0.5 });

  document.querySelectorAll('.ab-fill').forEach(b => {
    const w = b.style.width;
    b.style.width = '0';
    barsObs.observe(b);
    setTimeout(() => { b.style.width = w; }, 100);
  });
</script>

</body>
</html>