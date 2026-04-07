<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Admin Dashboard - Presensi</title>
    <link href="https://fonts.googleapis.com/css2?family=Inter:wght@300;400;500;600;700&display=swap" rel="stylesheet">
    <script src="https://unpkg.com/@phosphor-icons/web"></script>
    <style>
        :root {
            --primary: #2563eb;
            --primary-hover: #1d4ed8;
            --danger: #ef4444;
            --danger-hover: #dc2626;
            --bg-body: #f8fafc;
            --bg-sidebar: #ffffff;
            --bg-card: #ffffff;
            --text-main: #0f172a;
            --text-muted: #64748b;
            --border-color: #e2e8f0;
        }
        * { box-sizing: border-box; margin: 0; padding: 0; font-family: 'Inter', sans-serif; }
        body { display: flex; min-height: 100vh; background-color: var(--bg-body); color: var(--text-main); }
        .sidebar { width: 260px; background: var(--bg-sidebar); border-right: 1px solid var(--border-color); padding: 24px; display: flex; flex-direction: column;}
        .sidebar-brand { display: flex; align-items: center; gap: 12px; margin-bottom: 40px; color: var(--primary); font-size: 1.25rem; font-weight: 700; }
        .sidebar-brand i { font-size: 1.8rem; }
        .nav-links { list-style: none; flex: 1;}
        .nav-links li { margin-bottom: 8px; }
        .nav-links a { text-decoration: none; color: var(--text-muted); display: flex; align-items: center; gap: 12px; padding: 12px 16px; border-radius: 8px; font-weight: 500; transition: all 0.2s ease;}
        .nav-links a i { font-size: 1.2rem; }
        .nav-links a:hover { background: #f1f5f9; color: var(--primary); }
        .nav-links a.active { background: #eff6ff; color: var(--primary); font-weight: 600; }

        .content { flex: 1; padding: 40px; max-width: calc(100vw - 260px); overflow-y: auto;}
        .header { margin-bottom: 32px; display: flex; justify-content: space-between; align-items: center; }
        .header h1 { font-size: 1.75rem; font-weight: 700; color: var(--text-main); tracking: -0.02em; }

        .card { background: var(--bg-card); padding: 24px; border-radius: 16px; border: 1px solid var(--border-color); box-shadow: 0 1px 3px rgba(0,0,0,0.02), 0 1px 2px rgba(0,0,0,0.01); }

        .table-responsive { width: 100%; overflow-x: auto; }
        table { width: 100%; border-collapse: separate; border-spacing: 0; margin-top: 8px;}
        table th, table td { padding: 16px; text-align: left; border-bottom: 1px solid var(--border-color); font-size: 0.95rem;}
        table th { background: #f8fafc; font-weight: 600; color: var(--text-muted); text-transform: uppercase; font-size: 0.75rem; letter-spacing: 0.05em;}
        table tr:last-child td { border-bottom: none; }
        table tbody tr { transition: background 0.15s; }
        table tbody tr:hover { background: #f8fafc; }

        .btn { padding: 10px 18px; border: none; border-radius: 8px; cursor: pointer; font-weight: 500; text-decoration: none; display: inline-flex; align-items: center; justify-content: center; gap: 8px; transition: all 0.2s ease; font-size: 0.9rem;}
        .btn-primary { background: var(--primary); color: white; }
        .btn-primary:hover { background: var(--primary-hover); box-shadow: 0 4px 6px -1px rgba(37, 99, 235, 0.2); }
        .btn-danger { background: var(--danger); color: white; }
        .btn-danger:hover { background: var(--danger-hover); }
        .btn-outline { background: white; border: 1px solid var(--border-color); color: var(--text-muted); }
        .btn-outline:hover { background: #f1f5f9; color: var(--text-main); }

        .form-group { margin-bottom: 20px; }
        .form-group label { display: block; margin-bottom: 8px; font-weight: 500; font-size: 0.9rem; color: var(--text-muted); }
        .form-group input, .form-group select { width: 100%; padding: 12px 16px; border: 1px solid var(--border-color); border-radius: 10px; font-size: 0.95rem; color: var(--text-main); outline: none; transition: border-color 0.2s; background: #fff;}
        .form-group input:focus, .form-group select:focus { border-color: var(--primary); box-shadow: 0 0 0 3px rgba(37, 99, 235, 0.1); }

        .badge { display: inline-block; padding: 6px 12px; border-radius: 9999px; font-size: 0.75rem; font-weight: 600; text-transform: uppercase; letter-spacing: 0.025em; }
    </style>
</head>
<body>
    <div class="sidebar">
        <div class="sidebar-brand">
            <i class="ph ph-fingerprint"></i>
            <span>Presensi Admin</span>
        </div>
        <ul class="nav-links">
            <li><a href="{{ route('admin.dashboard') }}" class="{{ request()->routeIs('admin.dashboard') ? 'active' : '' }}"><i class="ph ph-squares-four"></i> Dashboard</a></li>
            <li><a href="{{ route('admin.employees') }}" class="{{ request()->routeIs('admin.employees') ? 'active' : '' }}"><i class="ph ph-users"></i> Karyawan</a></li>
            <li><a href="{{ route('admin.attendances') }}" class="{{ request()->routeIs('admin.attendances') ? 'active' : '' }}"><i class="ph ph-list-checks"></i> Rekap Presensi</a></li>
            <li style="margin-top: 10px; border-top: 1px solid var(--border-color); padding-top: 10px;"><a href="/" target="_blank"><i class="ph ph-arrow-square-out"></i> App Karyawan</a></li>
        </ul>
        <form action="{{ route('admin.logout') }}" method="POST" style="margin-top: auto;">
            @csrf
            <button type="submit" class="btn btn-outline" style="width: 100%;">
                <i class="ph ph-sign-out"></i> Logout
            </button>
        </form>
    </div>
    <div class="content">
        <div class="header">
            <h1>@yield('title')</h1>
        </div>
        @if(session('success'))
            <div style="background: #28a745; color: white; padding: 12px; border-radius: 8px; margin-bottom: 20px;">
                {{ session('success') }}
            </div>
        @endif
        @yield('content')
    </div>
</body>
</html>
