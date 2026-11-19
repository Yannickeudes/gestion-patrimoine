<!DOCTYPE html>
<html lang="fr">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <title>GPE — {{ $title ?? 'Tableau de bord' }}</title>
    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link href="https://fonts.googleapis.com/css2?family=Inter:wght@400;500;600;700;800&display=swap" rel="stylesheet">
    <style>
        * { margin:0; padding:0; box-sizing:border-box; font-family:'Inter',sans-serif; }
        body { background:#f4f6fa; color:#1f2d3d; display:flex; min-height:100vh; }
        .sidebar {
            width:230px; background:#1e3a5f; color:#c8d8ec; display:flex;
            flex-direction:column; position:fixed; top:0; bottom:0; left:0; z-index:10;
        }
        .sidebar .brand { display:flex; align-items:center; gap:10px; padding:20px 18px; color:#fff; }
        .sidebar .brand .mark {
            width:34px; height:34px; border-radius:8px; background:#14b8a6;
            display:flex; align-items:center; justify-content:center; font-weight:800;
        }
        .sidebar nav { padding:10px 12px; flex:1; }
        .sidebar .nav-label { font-size:10.5px; text-transform:uppercase; letter-spacing:.08em; color:#6f86a8; padding:14px 10px 6px; }
        .sidebar a {
            display:flex; align-items:center; gap:11px; padding:10px 12px; margin:2px 0;
            border-radius:9px; color:#c8d8ec; text-decoration:none; font-size:13.5px; font-weight:500;
        }
        .sidebar a:hover { background:rgba(255,255,255,.07); color:#fff; }
        .sidebar a.active { background:#14b8a6; color:#fff; }
        .sidebar a .ico { width:18px; text-align:center; }
        .sidebar .logout { margin:12px; padding:10px; border-radius:9px; background:rgba(255,255,255,.06);
            color:#e8b4b4; border:none; cursor:pointer; font-size:13px; font-weight:600; width:calc(100% - 24px); }
        .sidebar .logout:hover { background:rgba(255,255,255,.12); color:#fff; }
        .main { margin-left:230px; flex:1; padding:28px 32px; min-width:0; }
        .topbar { display:flex; justify-content:space-between; align-items:center; margin-bottom:24px; }
        .topbar h1 { font-size:22px; font-weight:700; color:#1e3a5f; }
        .topbar .user { font-size:13px; color:#64748b; }
        .topbar .user b { color:#1e3a5f; }
        .kpis { display:grid; grid-template-columns:repeat(4,1fr); gap:16px; margin-bottom:24px; }
        .kpi { background:#fff; border-radius:14px; padding:18px 20px; box-shadow:0 1px 4px rgba(30,58,95,.07); }
        .kpi .lbl { font-size:12px; color:#7a8aa0; font-weight:600; text-transform:uppercase; letter-spacing:.04em; }
        .kpi .val { font-size:24px; font-weight:800; color:#1e3a5f; margin-top:6px; }
        .kpi .sub { font-size:11.5px; color:#94a3b8; margin-top:4px; }
        .kpi .icon { float:right; width:38px; height:38px; border-radius:10px; display:flex;
            align-items:center; justify-content:center; font-size:17px; }
        .ic-blue { background:#e8f0fe; } .ic-teal { background:#e0f7f4; }
        .ic-amber { background:#fef3e2; } .ic-slate { background:#eef1f6; }
        .panel { background:#fff; border-radius:14px; box-shadow:0 1px 4px rgba(30,58,95,.07); overflow:hidden; margin-bottom:24px; }
        .panel-head { display:flex; justify-content:space-between; align-items:center; padding:16px 20px; border-bottom:1px solid #eef1f6; flex-wrap:wrap; gap:10px; }
        .panel-head h2 { font-size:15px; font-weight:700; color:#1e3a5f; }
        .filters { display:flex; gap:10px; flex-wrap:wrap; }
        .filters input, .filters select, .form-grid input, .form-grid select, .form-grid textarea {
            padding:8px 12px; border:1.5px solid #e2e8f0; border-radius:8px; font-size:13px;
            font-family:'Inter',sans-serif; outline:none; background:#fff;
        }
        .filters input:focus, .filters select:focus, .form-grid input:focus, .form-grid select:focus, .form-grid textarea:focus { border-color:#14b8a6; }
        .filters input { width:220px; }
        table { width:100%; border-collapse:collapse; font-size:13px; }
        thead th {
            text-align:left; padding:11px 16px; font-size:11px; text-transform:uppercase;
            letter-spacing:.05em; color:#7a8aa0; background:#f8fafc; border-bottom:1px solid #eef1f6;
        }
        tbody td { padding:12px 16px; border-bottom:1px solid #f1f4f8; vertical-align:middle; }
        tbody tr:hover { background:#f8fafc; }
        .badge { display:inline-block; padding:4px 10px; border-radius:20px; font-size:11px; font-weight:600; }
        .b-en_service, .b-neuf, .b-bon { background:#dcfce7; color:#15803d; }
        .b-maintenance, .b-use, .b-ouvert, .b-en_cours { background:#fef3c7; color:#b45309; }
        .b-reforme { background:#e2e8f0; color:#475569; }
        .b-cession, .b-preventive { background:#dbeafe; color:#1d4ed8; }
        .b-sorti, .b-hors_service, .b-clos, .b-corrective { background:#fee2e2; color:#b91c1c; }
        .empty { text-align:center; color:#94a3b8; padding:40px !important; }
        .btn { padding:8px 16px; border:none; border-radius:8px; cursor:pointer; font-size:13px;
            font-weight:600; font-family:'Inter',sans-serif; }
        .btn-primary { background:#1e3a5f; color:#fff; } .btn-primary:hover { background:#14b8a6; }
        .btn-teal { background:#14b8a6; color:#fff; }
        .btn-sm { padding:5px 11px; font-size:12px; }
        .btn-danger { background:#fee2e2; color:#b91c1c; } .btn-danger:hover { background:#fecaca; }
        .btn-ghost { background:#eef1f6; color:#475569; }
        .form-grid { display:grid; grid-template-columns:repeat(auto-fit,minmax(200px,1fr)); gap:14px; padding:20px; }
        .form-grid label { display:block; font-size:12px; font-weight:600; color:#4a5a70; margin-bottom:5px; }
        .form-grid input, .form-grid select, .form-grid textarea { width:100%; }
        .form-grid textarea { resize:vertical; min-height:70px; }
        .form-actions { padding:0 20px 20px; display:flex; gap:10px; }
        .alert { padding:12px 16px; border-radius:10px; font-size:13px; margin-bottom:20px; }
        .alert-info { background:#e8f0fe; color:#1e40af; border:1px solid #c7d7f5; }
        .alert-warn { background:#fef3e2; color:#92400e; border:1px solid #f5d9a8; }
        .alert-err { background:#fef2f2; color:#b91c1c; border:1px solid #fecaca; }
        .two-col { display:grid; grid-template-columns:1fr 1fr; gap:24px; }
        @media (max-width:900px) {
            .sidebar { width:64px; } .sidebar .brand span, .sidebar nav a span, .sidebar .nav-label, .sidebar .logout span { display:none; }
            .main { margin-left:64px; padding:20px; }
            .kpis { grid-template-columns:repeat(2,1fr); } .two-col { grid-template-columns:1fr; }
        }
    </style>
</head>
<body>
<aside class="sidebar">
    <div class="brand"><div class="mark">G</div><span style="font-weight:700">GPE</span></div>
    <nav>
        <div class="nav-label">Principal</div>
        <a href="/" class="{{ ($active ?? '') === 'dashboard' ? 'active' : '' }}"><span class="ico">▦</span><span>Tableau de bord</span></a>
        <a href="/maintenances" class="{{ ($active ?? '') === 'maintenances' ? 'active' : '' }}"><span class="ico">⚒</span><span>Maintenances</span></a>
        <a href="/rapports" class="{{ ($active ?? '') === 'rapports' ? 'active' : '' }}"><span class="ico">▤</span><span>Rapports</span></a>
        <div class="nav-label">Administration</div>
        <a href="/utilisateurs" class="{{ ($active ?? '') === 'utilisateurs' ? 'active' : '' }}"><span class="ico">☰</span><span>Utilisateurs</span></a>
    </nav>
    <button class="logout" onclick="logout()"><span>⏻</span> <span>Déconnexion</span></button>
</aside>
<div class="main">
