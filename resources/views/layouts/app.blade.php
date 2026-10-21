<!doctype html>
<html lang="en">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <title>{{ $title ?? 'Simple Banking System' }}</title>
    <style>
        :root { font-family: Inter, system-ui, sans-serif; color: #172033; background: #f4f7fb; }
        * { box-sizing: border-box; } body { margin: 0; } a { color: inherit; text-decoration: none; }
        .nav { background: #14213d; color: white; padding: 18px max(24px, calc((100% - 1100px)/2)); display:flex; justify-content:space-between; align-items:center; }
        .brand { font-weight: 800; letter-spacing: .02em; } .nav a { color:#dbe7ff; margin-left:20px; font-size:.95rem; }
        main { max-width:1100px; margin:38px auto; padding:0 20px; } .eyebrow { color:#5b6b88; text-transform:uppercase; letter-spacing:.12em; font-size:.75rem; font-weight:700; }
        h1 { margin:6px 0 24px; font-size:2.2rem; } h2 { margin:0 0 12px; } .grid { display:grid; gap:18px; grid-template-columns:repeat(3,1fr); }
        .card { background:white; border:1px solid #e0e7f0; border-radius:16px; padding:22px; box-shadow:0 8px 24px #23395d0a; } .balance { background:#14213d; color:white; }
        .balance .eyebrow { color:#a9c4fb; } .amount { font-size:2.4rem; font-weight:800; margin-top:8px; } .muted { color:#6b7890; }
        table { width:100%; border-collapse:collapse; } th,td { padding:14px 10px; border-bottom:1px solid #edf0f5; text-align:left; } th { color:#6b7890; font-size:.78rem; text-transform:uppercase; }
        .section { margin-top:24px; } .toolbar { display:flex; justify-content:space-between; align-items:center; gap:14px; margin-bottom:14px; }
        .btn { display:inline-block; border:0; border-radius:9px; background:#246bfd; color:white; padding:11px 16px; font-weight:700; cursor:pointer; } .btn.secondary { background:#eaf0ff; color:#2456bf; }
        .alert { padding:13px 16px; border-radius:10px; margin-bottom:18px; background:#e7f7ee; color:#16703d; } .warning { background:#fff4d6; color:#8a5a00; }
        label { display:block; font-weight:700; margin:14px 0 6px; } input,select,textarea { width:100%; padding:11px; border:1px solid #ccd5e3; border-radius:8px; font:inherit; } .form-card { max-width:620px; margin:auto; }
        .pagination { margin-top:18px; display:flex; gap:8px; } .pagination a,.pagination span { padding:7px 11px; border:1px solid #d9e0eb; border-radius:7px; }
        @media(max-width:720px){.grid{grid-template-columns:1fr}.toolbar{align-items:flex-start;flex-direction:column} table{font-size:.85rem}}
    </style>
</head>
<body>
<nav class="nav"><a class="brand" href="{{ Auth::check() ? route('dashboard') : route('login') }}">SB / Simple Banking</a><div>@auth<a href="{{ route('dashboard') }}">Dashboard</a><a href="{{ route('transactions.create') }}">New transaction</a><form method="post" action="{{ route('logout') }}" style="display:inline">@csrf<button type="submit" style="background:none;border:0;color:#dbe7ff;margin-left:20px;font:inherit;cursor:pointer">Logout</button></form>@else<a href="{{ route('login') }}">Sign in</a><a href="{{ route('register') }}">Register</a>@endauth</div></nav>
<main>@yield('content')</main>
@stack('scripts')
</body>
</html>
