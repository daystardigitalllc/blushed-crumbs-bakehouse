<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>SaaS Brand Command Center | Doughmain.pro</title>
    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
    <link href="https://fonts.googleapis.com/css2?family=Inter:wght@400;500;600;700;800&family=Outfit:wght@600;700;800&display=swap" rel="stylesheet">
    <style>
        * { box-sizing: border-box; margin: 0; padding: 0; }
        body {
            font-family: 'Inter', sans-serif;
            background: #0b0f19;
            color: #f8fafc;
            min-height: 100vh;
            display: flex;
            flex-direction: column;
        }

        /* TOP HEADER */
        .brand-admin-header {
            background: #111827;
            border-bottom: 1px solid #1e293b;
            padding: 1rem 2rem;
            display: flex;
            justify-content: space-between;
            align-items: center;
            position: sticky;
            top: 0;
            z-index: 100;
        }
        .brand-logo {
            font-family: 'Outfit', sans-serif;
            font-size: 1.4rem;
            font-weight: 800;
            color: #ec4899;
            text-decoration: none;
            display: flex;
            align-items: center;
            gap: 12px;
        }
        .brand-badge {
            background: rgba(139, 92, 246, 0.15);
            color: #c084fc;
            font-size: 0.75rem;
            padding: 4px 10px;
            border-radius: 20px;
            border: 1px solid rgba(139, 92, 246, 0.3);
            font-weight: 700;
            letter-spacing: 0.03em;
        }
        .header-actions {
            display: flex;
            align-items: center;
            gap: 16px;
        }

        /* LAYOUT CONTAINERS */
        .brand-admin-wrapper {
            display: flex;
            flex: 1;
        }

        /* LEFT SIDEBAR */
        .brand-sidebar {
            width: 260px;
            background: #111827;
            border-right: 1px solid #1e293b;
            padding: 1.5rem 1rem;
            display: flex;
            flex-direction: column;
            gap: 8px;
        }
        .brand-sidebar-title {
            font-size: 0.75rem;
            font-weight: 800;
            color: #64748b;
            text-transform: uppercase;
            letter-spacing: 0.08em;
            padding: 0 12px 8px;
        }
        .sidebar-btn {
            display: flex;
            align-items: center;
            gap: 12px;
            padding: 12px 16px;
            border-radius: 12px;
            background: transparent;
            border: none;
            color: #94a3b8;
            font-size: 0.95rem;
            font-weight: 600;
            cursor: pointer;
            text-align: left;
            transition: all 0.2s ease;
            width: 100%;
        }
        .sidebar-btn:hover {
            background: rgba(255,255,255,0.04);
            color: #f8fafc;
        }
        .sidebar-btn.active {
            background: linear-gradient(135deg, rgba(236,72,153,0.2) 0%, rgba(139,92,246,0.2) 100%);
            color: #f472b6;
            border: 1px solid rgba(236,72,153,0.3);
            font-weight: 700;
        }

        /* MAIN CONTENT AREA */
        .brand-main-content {
            flex: 1;
            padding: 2rem;
            max-width: 1400px;
        }

        /* TAB PANELS */
        .brand-tab-panel {
            display: none;
        }
        .brand-tab-panel.active {
            display: block;
        }

        /* CARDS & STATS */
        .stats-grid {
            display: grid;
            grid-template-columns: repeat(auto-fit, minmax(220px, 1fr));
            gap: 1.25rem;
            margin-bottom: 2rem;
        }
        .stat-card {
            background: #111827;
            border: 1px solid #1e293b;
            border-radius: 16px;
            padding: 1.4rem;
        }
        .stat-card h4 {
            font-size: 0.8rem;
            text-transform: uppercase;
            letter-spacing: 0.06em;
            color: #64748b;
            margin-bottom: 8px;
        }
        .stat-card .val {
            font-size: 2.2rem;
            font-weight: 800;
            color: #ffffff;
            font-family: 'Outfit', sans-serif;
        }
        .card {
            background: #111827;
            border: 1px solid #1e293b;
            border-radius: 16px;
            padding: 1.8rem;
            margin-bottom: 2rem;
        }
        .card-header {
            display: flex;
            justify-content: space-between;
            align-items: center;
            margin-bottom: 1.2rem;
            padding-bottom: 1rem;
            border-bottom: 1px solid #1e293b;
        }
        .card-title {
            font-family: 'Outfit', sans-serif;
            font-size: 1.3rem;
            font-weight: 700;
            color: #ffffff;
        }

        /* TABLES */
        table {
            width: 100%;
            border-collapse: collapse;
            text-align: left;
        }
        th {
            background: #0b0f19;
            padding: 12px 16px;
            font-size: 0.8rem;
            text-transform: uppercase;
            color: #64748b;
            border-bottom: 1px solid #1e293b;
        }
        td {
            padding: 16px;
            border-bottom: 1px solid #1e293b;
            font-size: 0.95rem;
        }
        tr:hover td {
            background: rgba(255,255,255,0.02);
        }
        .badge {
            display: inline-block;
            padding: 4px 10px;
            border-radius: 12px;
            font-size: 0.78rem;
            font-weight: 600;
        }
        .badge-active { background: rgba(34,197,94,0.15); color: #4ade80; border: 1px solid rgba(34,197,94,0.3); }
        .badge-suspended { background: rgba(239,68,68,0.15); color: #f87171; border: 1px solid rgba(239,68,68,0.3); }
        .badge-pro { background: rgba(168,85,247,0.15); color: #c084fc; border: 1px solid rgba(168,85,247,0.3); }
        .badge-standard { background: rgba(59,130,246,0.15); color: #60a5fa; border: 1px solid rgba(59,130,246,0.3); }
        .btn-action {
            padding: 6px 12px;
            border-radius: 8px;
            font-size: 0.82rem;
            font-weight: 600;
            cursor: pointer;
            border: none;
            text-decoration: none;
            display: inline-block;
        }
        .btn-toggle { background: #1e293b; color: #f8fafc; transition: all 0.2s; }
        .btn-toggle:hover { background: #334155; }
        .btn-link { background: rgba(236,72,153,0.15); color: #f472b6; border: 1px solid rgba(236,72,153,0.3); }
        .btn-link:hover { background: rgba(236,72,153,0.3); }
    </style>
</head>
<body>

    <!-- TOP HEADER -->
    <header class="brand-admin-header">
        <a href="/admin" class="brand-logo">
            🧁 Doughmain.pro <span class="brand-badge">SaaS SuperAdmin Portal</span>
        </a>
        <div class="header-actions">
            <span style="font-size:0.9rem; color:#94a3b8;">Logged in as <strong>{{ auth()->user()->email }}</strong></span>
            <form method="POST" action="/logout" style="display:inline;">
                @csrf
                <button type="submit" class="btn-action btn-toggle" style="background:#dc2626; color:#fff;">Logout</button>
            </form>
        </div>
    </header>

    <div class="brand-admin-wrapper">
        <!-- LEFT SIDEBAR NAVIGATION -->
        <aside class="brand-sidebar">
            <div class="brand-sidebar-title">Brand Command Center</div>
            <button class="sidebar-btn active" data-tab="tab-brand-overview" onclick="switchBrandTab('tab-brand-overview', this)">
                <span>📊</span> Platform Overview
            </button>
            <button class="sidebar-btn" data-tab="tab-brand-tenants" onclick="switchBrandTab('tab-brand-tenants', this)">
                <span>🏬</span> Bakery Tenants
            </button>
            <button class="sidebar-btn" data-tab="tab-brand-users" onclick="switchBrandTab('tab-brand-users', this)">
                <span>👥</span> User Management
            </button>
            <button class="sidebar-btn" data-tab="tab-brand-tickets" onclick="switchBrandTab('tab-brand-tickets', this)">
                <span>🎧</span> Support Desk
            </button>
            <button class="sidebar-btn" data-tab="tab-brand-ai" onclick="switchBrandTab('tab-brand-ai', this)">
                <span>⚡</span> AI System Health
            </button>
        </aside>

        <!-- MAIN CONTENT AREA -->
        <main class="brand-main-content">

            @if(session('success'))
                <div style="background:rgba(34,197,94,0.15); border:1px solid rgba(34,197,94,0.3); color:#4ade80; padding:14px 20px; border-radius:12px; margin-bottom:20px; font-weight:600;">
                    ✓ {{ session('success') }}
                </div>
            @endif

            @if(session('error'))
                <div style="background:rgba(239,68,68,0.15); border:1px solid rgba(239,68,68,0.3); color:#f87171; padding:14px 20px; border-radius:12px; margin-bottom:20px; font-weight:600;">
                    ✕ {{ session('error') }}
                </div>
            @endif

            <!-- TAB 1: OVERVIEW -->
            <div id="tab-brand-overview" class="brand-tab-panel active">
                <div class="stats-grid">
                    <div class="stat-card">
                        <h4>Monthly Recurring Revenue (MRR)</h4>
                        <div class="val" style="color:#4ade80;">${{ number_format($mrr, 2) }}</div>
                    </div>
                    <div class="stat-card">
                        <h4>Active Bakeries</h4>
                        <div class="val">{{ $tenants->count() }}</div>
                    </div>
                    <div class="stat-card">
                        <h4>Platform Users</h4>
                        <div class="val">{{ $users->count() }}</div>
                    </div>
                    <div class="stat-card">
                        <h4>Total Orders Processed</h4>
                        <div class="val">{{ $ordersCount }}</div>
                    </div>
                </div>

                <div class="card">
                    <div class="card-header">
                        <h3 class="card-title">🚀 Quick Actions &amp; Diagnostics</h3>
                    </div>
                    <p style="color:#94a3b8; font-size:0.95rem; line-height:1.6;">
                        Welcome to the Doughmain.pro SaaS Command Center. From this dashboard, you have full control over all bakery tenants, subscription statuses, platform user roles, support ticket queues, and Gemini AI status.
                    </p>
                </div>
            </div>

            <!-- TAB 2: TENANTS -->
            <div id="tab-brand-tenants" class="brand-tab-panel">
                <div class="card">
                    <div class="card-header">
                        <h3 class="card-title">🏬 Bakery Tenants Directory</h3>
                        <span style="font-size:0.85rem; color:#64748b;">Manage active bakery subscriptions</span>
                    </div>

                    <table>
                        <thead>
                            <tr>
                                <th>Bakery Name &amp; Subdomain</th>
                                <th>Owner</th>
                                <th>Plan</th>
                                <th>Status</th>
                                <th>Created</th>
                                <th>Actions</th>
                            </tr>
                        </thead>
                        <tbody>
                            @forelse($tenants as $tenant)
                                <tr>
                                    <td>
                                        <strong style="color:#fff;">{{ $tenant->name }}</strong><br>
                                        <small style="color:#64748b;">{{ $tenant->subdomain ?? $tenant->slug }}.doughmain.pro.test</small>
                                    </td>
                                    <td>
                                        <div>{{ $tenant->owner_name }}</div>
                                        <small style="color:#64748b;">{{ $tenant->email }}</small>
                                    </td>
                                    <td>
                                        <span class="badge {{ $tenant->plan_tier === 'pro' ? 'badge-pro' : 'badge-standard' }}">
                                            {{ strtoupper($tenant->plan_tier ?? 'standard') }} (${{ $tenant->plan_tier === 'pro' ? '50' : '29' }}/mo)
                                        </span>
                                    </td>
                                    <td>
                                        <span class="badge {{ $tenant->is_active ? 'badge-active' : 'badge-suspended' }}">
                                            {{ $tenant->is_active ? 'Active' : 'Suspended' }}
                                        </span>
                                    </td>
                                    <td>{{ $tenant->created_at ? $tenant->created_at->format('M d, Y') : 'N/A' }}</td>
                                    <td>
                                        <div style="display:flex; gap:8px;">
                                            <a href="http://{{ $tenant->subdomain ?? $tenant->slug }}.doughmain.pro.test" target="_blank" class="btn-action btn-link">Visit Site ↗</a>
                                            <a href="/site/{{ $tenant->subdomain ?? $tenant->slug }}/dashboard" target="_blank" class="btn-action btn-toggle" style="background:#475569;">Baker CMS ↗</a>
                                            <form method="POST" action="{{ route('superadmin.tenant.toggle', $tenant->id) }}" style="display:inline;">
                                                @csrf
                                                <button type="submit" class="btn-action btn-toggle">
                                                    {{ $tenant->is_active ? 'Suspend' : 'Activate' }}
                                                </button>
                                            </form>
                                        </div>
                                    </td>
                                </tr>
                            @empty
                                <tr>
                                    <td colspan="6" style="text-align:center; padding:40px; color:#64748b;">No bakery accounts registered yet.</td>
                                </tr>
                            @endforelse
                        </tbody>
                    </table>
                </div>
            </div>

            <!-- TAB 3: USERS -->
            <div id="tab-brand-users" class="brand-tab-panel">
                <div class="card">
                    <div class="card-header">
                        <h3 class="card-title">👥 Platform User Management</h3>
                        <span style="font-size:0.85rem; color:#64748b;">All registered user accounts &amp; permissions</span>
                    </div>

                    <table>
                        <thead>
                            <tr>
                                <th>User Name &amp; Email</th>
                                <th>Associated Bakery</th>
                                <th>Current Role</th>
                                <th>Joined Date</th>
                                <th>Actions</th>
                            </tr>
                        </thead>
                        <tbody>
                            @forelse($users as $usr)
                                <tr>
                                    <td>
                                        <strong style="color:#fff;">{{ $usr->name }}</strong><br>
                                        <small style="color:#64748b;">{{ $usr->email }}</small>
                                    </td>
                                    <td>
                                        <div>{{ $usr->tenant->name ?? 'Platform System' }}</div>
                                        <small style="color:#64748b;">{{ $usr->tenant->subdomain ?? 'N/A' }}</small>
                                    </td>
                                    <td>
                                        <span class="badge {{ $usr->isSuperAdmin() ? 'badge-pro' : 'badge-standard' }}">
                                            {{ strtoupper($usr->role ?? 'owner') }}
                                        </span>
                                    </td>
                                    <td>{{ $usr->created_at ? $usr->created_at->format('M d, Y') : 'N/A' }}</td>
                                    <td>
                                        <div style="display:flex; gap:8px; align-items:center;">
                                            <form method="POST" action="{{ route('superadmin.user.role', $usr->id) }}" style="display:flex; gap:4px;">
                                                @csrf
                                                <select name="role" onchange="this.form.submit()" style="background:#0b0f19; color:#fff; border:1px solid #1e293b; padding:6px 10px; border-radius:8px; font-size:0.82rem;">
                                                    <option value="owner" {{ $usr->role === 'owner' ? 'selected' : '' }}>Owner</option>
                                                    <option value="admin" {{ $usr->role === 'admin' ? 'selected' : '' }}>Admin</option>
                                                    <option value="staff" {{ $usr->role === 'staff' ? 'selected' : '' }}>Staff</option>
                                                    <option value="superadmin" {{ $usr->role === 'superadmin' ? 'selected' : '' }}>SuperAdmin</option>
                                                </select>
                                            </form>
                                            @if($usr->id !== auth()->id())
                                                <form method="POST" action="{{ route('superadmin.user.delete', $usr->id) }}" onsubmit="return confirm('Delete this user account?')" style="display:inline;">
                                                    @csrf
                                                    @method('DELETE')
                                                    <button type="submit" class="btn-action btn-toggle" style="background:#dc2626; color:#fff;">Delete</button>
                                                </form>
                                            @endif
                                        </div>
                                    </td>
                                </tr>
                            @empty
                                <tr>
                                    <td colspan="5" style="text-align:center; padding:30px; color:#64748b;">No registered users found.</td>
                                </tr>
                            @endforelse
                        </tbody>
                    </table>
                </div>
            </div>

            <!-- TAB 4: TICKETS -->
            <div id="tab-brand-tickets" class="brand-tab-panel">
                <div class="card">
                    <div class="card-header">
                        <h3 class="card-title">🎧 Support Desk Inbox</h3>
                        <span style="font-size:0.85rem; color:#64748b;">Help requests submitted by bakery owners</span>
                    </div>

                    <table>
                        <thead>
                            <tr>
                                <th>Subject &amp; Bakery Details</th>
                                <th>Type</th>
                                <th>Status</th>
                                <th>Submitted</th>
                                <th>Action</th>
                            </tr>
                        </thead>
                        <tbody>
                            @forelse($tickets as $tkt)
                                <tr>
                                    <td>
                                        <strong style="color:#fff;">{{ $tkt->subject }}</strong><br>
                                        <small style="color:#64748b;">{{ $tkt->tenant->name ?? 'Bakery' }} ({{ $tkt->tenant->email ?? '' }})</small>
                                        <p style="font-size:0.88rem; color:#cbd5e1; margin-top:6px; background:#0b0f19; padding:10px; border-radius:8px;">{{ $tkt->message }}</p>
                                    </td>
                                    <td><span class="badge badge-standard">{{ strtoupper($tkt->type) }}</span></td>
                                    <td>
                                        <span class="badge {{ $tkt->status === 'resolved' ? 'badge-active' : 'badge-suspended' }}">
                                            {{ strtoupper($tkt->status) }}
                                        </span>
                                    </td>
                                    <td>{{ $tkt->created_at ? $tkt->created_at->diffForHumans() : 'N/A' }}</td>
                                    <td>
                                        <form method="POST" action="{{ route('superadmin.ticket.status', $tkt->id) }}">
                                            @csrf
                                            <select name="status" onchange="this.form.submit()" style="background:#0b0f19; color:#fff; border:1px solid #1e293b; padding:6px 10px; border-radius:8px; font-size:0.82rem;">
                                                <option value="open" {{ $tkt->status === 'open' ? 'selected' : '' }}>Open</option>
                                                <option value="in_progress" {{ $tkt->status === 'in_progress' ? 'selected' : '' }}>In Progress</option>
                                                <option value="resolved" {{ $tkt->status === 'resolved' ? 'selected' : '' }}>Resolved</option>
                                            </select>
                                        </form>
                                    </td>
                                </tr>
                            @empty
                                <tr>
                                    <td colspan="5" style="text-align:center; padding:30px; color:#64748b;">No support tickets submitted yet.</td>
                                </tr>
                            @endforelse
                        </tbody>
                    </table>
                </div>
            </div>

            <!-- TAB 5: AI SYSTEM -->
            <div id="tab-brand-ai" class="brand-tab-panel">
                <div class="card" style="background:#13113c; border-color:#3730a3;">
                    <div class="card-header" style="border-bottom-color:#3730a3;">
                        <h3 class="card-title" style="color:#c7d2fe;">⚡ Google Gemini AI Integration Diagnostic</h3>
                        <span class="badge badge-active">Live &amp; Healthy</span>
                    </div>
                    <div style="font-size:0.95rem; color:#a5b4fc; line-height:1.8;">
                        <p style="margin-bottom:8px;">• <strong>Model Endpoint:</strong> <code>v1beta/models/gemini-3.5-flash-lite:generateContent</code></p>
                        <p style="margin-bottom:8px;">• <strong>Authentication Mode:</strong> Auth Developer Key (AQ Key Format)</p>
                        <p style="margin-bottom:8px;">• <strong>HTTP Status Code:</strong> 200 OK</p>
                        <p>• <strong>Capabilities:</strong> Automatic bakery bio generation, custom product categories output, 3 custom reviews generation.</p>
                    </div>
                </div>
            </div>

        </main>
    </div>

    <script>
        function switchBrandTab(tabId, btnElement) {
            document.querySelectorAll('.brand-tab-panel').forEach(panel => {
                panel.classList.remove('active');
            });
            document.querySelectorAll('.sidebar-btn').forEach(btn => {
                btn.classList.remove('active');
            });
            const targetPanel = document.getElementById(tabId);
            if (targetPanel) {
                targetPanel.classList.add('active');
            }
            if (btnElement) {
                btnElement.classList.add('active');
            }
        }
    </script>

</body>
</html>
