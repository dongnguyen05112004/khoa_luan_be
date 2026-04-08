<!DOCTYPE html>
<html lang="vi">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>API Docs – Gym Equipment Management</title>
    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link href="https://fonts.googleapis.com/css2?family=Inter:wght@300;400;500;600;700&family=JetBrains+Mono:wght@400;500&display=swap" rel="stylesheet">
    <style>
        :root {
            --bg:       #0f1117;
            --surface:  #1a1d27;
            --border:   #2a2d3e;
            --accent:   #6c63ff;
            --accent2:  #00d4aa;
            --text:     #e2e8f0;
            --muted:    #8892a4;
            --get:      #22c55e;
            --post:     #3b82f6;
            --put:      #f59e0b;
            --patch:    #a855f7;
            --delete:   #ef4444;
            --radius:   12px;
        }

        * { margin: 0; padding: 0; box-sizing: border-box; }

        body {
            background: var(--bg);
            color: var(--text);
            font-family: 'Inter', sans-serif;
            font-size: 14px;
            line-height: 1.6;
            display: flex;
            min-height: 100vh;
        }

        /* ── Sidebar ── */
        .sidebar {
            width: 260px;
            min-width: 260px;
            background: var(--surface);
            border-right: 1px solid var(--border);
            position: sticky;
            top: 0;
            height: 100vh;
            overflow-y: auto;
            display: flex;
            flex-direction: column;
        }

        .sidebar-logo {
            padding: 24px 20px 20px;
            border-bottom: 1px solid var(--border);
        }
        .sidebar-logo h1 {
            font-size: 16px;
            font-weight: 700;
            background: linear-gradient(135deg, var(--accent), var(--accent2));
            -webkit-background-clip: text;
            -webkit-text-fill-color: transparent;
            margin-bottom: 4px;
        }
        .sidebar-logo p { font-size: 11px; color: var(--muted); }

        .sidebar-nav { padding: 16px 12px; flex: 1; }

        .nav-section {
            margin-bottom: 20px;
        }
        .nav-section-title {
            font-size: 10px;
            font-weight: 600;
            letter-spacing: 1px;
            color: var(--muted);
            text-transform: uppercase;
            padding: 0 8px;
            margin-bottom: 6px;
        }
        .nav-item {
            display: flex;
            align-items: center;
            gap: 8px;
            padding: 7px 8px;
            border-radius: 7px;
            cursor: pointer;
            text-decoration: none;
            color: var(--muted);
            font-size: 13px;
            transition: all 0.15s;
            margin-bottom: 2px;
        }
        .nav-item:hover, .nav-item.active {
            background: rgba(108,99,255,0.12);
            color: var(--text);
        }
        .method-badge {
            font-family: 'JetBrains Mono', monospace;
            font-size: 9px;
            font-weight: 600;
            padding: 2px 6px;
            border-radius: 4px;
            min-width: 46px;
            text-align: center;
        }
        .badge-get    { background: rgba(34,197,94,.15);  color: var(--get); }
        .badge-post   { background: rgba(59,130,246,.15); color: var(--post); }
        .badge-put    { background: rgba(245,158,11,.15); color: var(--put); }
        .badge-patch  { background: rgba(168,85,247,.15); color: var(--patch); }
        .badge-delete { background: rgba(239,68,68,.15);  color: var(--delete); }

        /* ── Main ── */
        .main {
            flex: 1;
            overflow-y: auto;
            padding: 40px 48px;
            max-width: 960px;
        }

        /* ── Hero ── */
        .hero {
            background: linear-gradient(135deg, rgba(108,99,255,.15), rgba(0,212,170,.08));
            border: 1px solid var(--border);
            border-radius: var(--radius);
            padding: 32px 36px;
            margin-bottom: 40px;
        }
        .hero h2 {
            font-size: 28px;
            font-weight: 700;
            background: linear-gradient(135deg, var(--accent), var(--accent2));
            -webkit-background-clip: text;
            -webkit-text-fill-color: transparent;
            margin-bottom: 8px;
        }
        .hero p { color: var(--muted); max-width: 600px; line-height: 1.7; }

        .info-grid {
            display: grid;
            grid-template-columns: repeat(auto-fit, minmax(180px, 1fr));
            gap: 12px;
            margin-top: 20px;
        }
        .info-card {
            background: rgba(255,255,255,.04);
            border: 1px solid var(--border);
            border-radius: 8px;
            padding: 12px 16px;
        }
        .info-card label { font-size: 11px; color: var(--muted); display: block; margin-bottom: 4px; }
        .info-card span  { font-family: 'JetBrains Mono', monospace; font-size: 13px; color: var(--accent2); }

        /* ── Section ── */
        .section {
            margin-bottom: 48px;
        }
        .section-title {
            font-size: 11px;
            font-weight: 600;
            text-transform: uppercase;
            letter-spacing: 1.5px;
            color: var(--accent);
            margin-bottom: 16px;
            display: flex;
            align-items: center;
            gap: 8px;
        }
        .section-title::after {
            content: '';
            flex: 1;
            height: 1px;
            background: var(--border);
        }

        /* ── Endpoint card ── */
        .endpoint {
            background: var(--surface);
            border: 1px solid var(--border);
            border-radius: var(--radius);
            margin-bottom: 16px;
            overflow: hidden;
            transition: border-color 0.2s;
        }
        .endpoint:hover { border-color: rgba(108,99,255,.4); }

        .endpoint-header {
            display: flex;
            align-items: center;
            gap: 14px;
            padding: 16px 20px;
            cursor: pointer;
            user-select: none;
        }
        .endpoint-method {
            font-family: 'JetBrains Mono', monospace;
            font-size: 11px;
            font-weight: 700;
            padding: 4px 10px;
            border-radius: 6px;
            min-width: 60px;
            text-align: center;
        }
        .method-GET    { background: rgba(34,197,94,.12);  color: var(--get);    border: 1px solid rgba(34,197,94,.3); }
        .method-POST   { background: rgba(59,130,246,.12); color: var(--post);   border: 1px solid rgba(59,130,246,.3); }
        .method-PUT    { background: rgba(245,158,11,.12); color: var(--put);    border: 1px solid rgba(245,158,11,.3); }
        .method-PATCH  { background: rgba(168,85,247,.12); color: var(--patch);  border: 1px solid rgba(168,85,247,.3); }
        .method-DELETE { background: rgba(239,68,68,.12);  color: var(--delete); border: 1px solid rgba(239,68,68,.3); }

        .endpoint-path {
            font-family: 'JetBrains Mono', monospace;
            font-size: 14px;
            color: var(--text);
            flex: 1;
        }
        .endpoint-summary {
            font-size: 13px;
            color: var(--muted);
        }
        .toggle-icon {
            color: var(--muted);
            transition: transform 0.2s;
            font-size: 18px;
        }
        .endpoint.open .toggle-icon { transform: rotate(180deg); }

        .endpoint-body {
            display: none;
            padding: 0 20px 20px;
            border-top: 1px solid var(--border);
        }
        .endpoint.open .endpoint-body { display: block; }

        /* ── Auth notice ── */
        .auth-notice {
            display: inline-flex;
            align-items: center;
            gap: 6px;
            background: rgba(245,158,11,.1);
            border: 1px solid rgba(245,158,11,.3);
            color: #f59e0b;
            font-size: 12px;
            padding: 6px 12px;
            border-radius: 6px;
            margin: 12px 0;
        }

        /* ── Tables & params ── */
        .params-title {
            font-size: 12px;
            font-weight: 600;
            color: var(--muted);
            text-transform: uppercase;
            letter-spacing: .8px;
            margin: 16px 0 8px;
        }
        table.params {
            width: 100%;
            border-collapse: collapse;
            font-size: 13px;
        }
        table.params thead tr {
            border-bottom: 1px solid var(--border);
        }
        table.params th {
            text-align: left;
            padding: 6px 10px;
            font-size: 11px;
            color: var(--muted);
            font-weight: 500;
        }
        table.params td {
            padding: 8px 10px;
            border-bottom: 1px solid rgba(255,255,255,.04);
            vertical-align: top;
        }
        .param-name  { font-family: 'JetBrains Mono', monospace; color: var(--accent2); }
        .param-type  { color: #a78bfa; font-size: 12px; }
        .param-req   { color: var(--delete); font-size: 11px; font-weight: 600; }
        .param-opt   { color: var(--muted);  font-size: 11px; }
        .param-desc  { color: var(--text); }

        /* ── Code blocks ── */
        .code-block {
            background: #0a0c12;
            border: 1px solid var(--border);
            border-radius: 8px;
            padding: 16px;
            font-family: 'JetBrains Mono', monospace;
            font-size: 12.5px;
            line-height: 1.7;
            overflow-x: auto;
            margin-top: 8px;
            position: relative;
        }
        .code-label {
            font-size: 11px;
            color: var(--muted);
            font-weight: 600;
            text-transform: uppercase;
            letter-spacing: .8px;
            margin: 14px 0 6px;
        }
        .copy-btn {
            position: absolute;
            top: 8px;
            right: 8px;
            background: rgba(255,255,255,.07);
            border: 1px solid var(--border);
            color: var(--muted);
            padding: 4px 10px;
            border-radius: 5px;
            font-size: 11px;
            cursor: pointer;
            font-family: 'Inter', sans-serif;
            transition: all .15s;
        }
        .copy-btn:hover { background: rgba(255,255,255,.12); color: var(--text); }

        .json-key    { color: #7dd3fc; }
        .json-str    { color: #86efac; }
        .json-num    { color: #fbbf24; }
        .json-bool   { color: #c084fc; }
        .json-null   { color: #94a3b8; }
        .json-note   { color: #6b7280; font-style: italic; }

        /* ── Status values ── */
        .status-grid {
            display: flex;
            gap: 10px;
            flex-wrap: wrap;
            margin: 10px 0;
        }
        .status-chip {
            padding: 5px 14px;
            border-radius: 20px;
            font-size: 12px;
            font-weight: 500;
        }
        .chip-active  { background: rgba(34,197,94,.12);  color: var(--get);    border: 1px solid rgba(34,197,94,.3); }
        .chip-main    { background: rgba(245,158,11,.12); color: var(--put);    border: 1px solid rgba(245,158,11,.3); }
        .chip-broken  { background: rgba(239,68,68,.12);  color: var(--delete); border: 1px solid rgba(239,68,68,.3); }

        /* ── Scrollbar ── */
        ::-webkit-scrollbar { width: 6px; height: 6px; }
        ::-webkit-scrollbar-track { background: transparent; }
        ::-webkit-scrollbar-thumb { background: var(--border); border-radius: 9px; }
    </style>
</head>
<body>

<!-- ══════════════════════════════════════════════════════
     SIDEBAR
═══════════════════════════════════════════════════════ -->
<aside class="sidebar">
    <div class="sidebar-logo">
        <h1>⚙️ Equipment API</h1>
        <p>Gym Management · v1.0</p>
    </div>
    <nav class="sidebar-nav">
        <div class="nav-section">
            <div class="nav-section-title">Xác thực</div>
            <a class="nav-item" href="#auth-login">
                <span class="method-badge badge-post">POST</span>
                Đăng nhập
            </a>
            <a class="nav-item" href="#auth-logout">
                <span class="method-badge badge-post">POST</span>
                Đăng xuất
            </a>
            <a class="nav-item" href="#auth-me">
                <span class="method-badge badge-get">GET</span>
                Tài khoản
            </a>
        </div>

        <div class="nav-section">
            <div class="nav-section-title">Thiết bị</div>
            <a class="nav-item" href="#eq-list">
                <span class="method-badge badge-get">GET</span>
                Danh sách
            </a>
            <a class="nav-item" href="#eq-create">
                <span class="method-badge badge-post">POST</span>
                Thêm mới
            </a>
            <a class="nav-item" href="#eq-detail">
                <span class="method-badge badge-get">GET</span>
                Chi tiết
            </a>
            <a class="nav-item" href="#eq-update">
                <span class="method-badge badge-put">PUT</span>
                Cập nhật
            </a>
            <a class="nav-item" href="#eq-status">
                <span class="method-badge badge-patch">PATCH</span>
                Trạng thái
            </a>
            <a class="nav-item" href="#eq-delete">
                <span class="method-badge badge-delete">DEL</span>
                Xóa thiết bị
            </a>
        </div>

        <div class="nav-section">
            <div class="nav-section-title">Bảo trì</div>
            <a class="nav-item" href="#mt-list">
                <span class="method-badge badge-get">GET</span>
                Danh sách
            </a>
            <a class="nav-item" href="#mt-create">
                <span class="method-badge badge-post">POST</span>
                Tạo lịch
            </a>
            <a class="nav-item" href="#mt-detail">
                <span class="method-badge badge-get">GET</span>
                Chi tiết
            </a>
            <a class="nav-item" href="#mt-update">
                <span class="method-badge badge-put">PUT</span>
                Cập nhật
            </a>
            <a class="nav-item" href="#mt-duesoon">
                <span class="method-badge badge-get">GET</span>
                Sắp đến hạn
            </a>
            <a class="nav-item" href="#mt-delete">
                <span class="method-badge badge-delete">DEL</span>
                Xóa lịch
            </a>
        </div>
    </nav>
</aside>

<!-- ══════════════════════════════════════════════════════
     MAIN CONTENT
═══════════════════════════════════════════════════════ -->
<main class="main">

    <!-- ── Hero ── -->
    <div class="hero">
        <h2>Equipment Management API</h2>
        <p>Tài liệu API quản lý thiết bị phòng gym — bao gồm thêm/cập nhật/xóa thiết bị và lập lịch bảo trì định kỳ. Tất cả protected route yêu cầu <strong>Bearer Token</strong> từ Sanctum.</p>
        <div class="info-grid">
            <div class="info-card">
                <label>Base URL</label>
                <span>http://localhost/api</span>
            </div>
            <div class="info-card">
                <label>Auth</label>
                <span>Bearer Token</span>
            </div>
            <div class="info-card">
                <label>Format</label>
                <span>application/json</span>
            </div>
            <div class="info-card">
                <label>Version</label>
                <span>v1.0 · Laravel 11</span>
            </div>
        </div>
    </div>

    <!-- ════════════════════════════════════════
         1. XÁC THỰC
    ════════════════════════════════════════ -->
    <div class="section">
        <div class="section-title">Xác thực</div>

        <!-- LOGIN -->
        <div class="endpoint" id="auth-login">
            <div class="endpoint-header" onclick="toggle(this)">
                <span class="endpoint-method method-POST">POST</span>
                <span class="endpoint-path">/api/login</span>
                <span class="endpoint-summary">Admin đăng nhập, nhận Bearer Token</span>
                <span class="toggle-icon">⌄</span>
            </div>
            <div class="endpoint-body">
                <p style="color:var(--muted);margin-top:12px;">Đăng nhập bằng email + password. Trả về user info và access token dùng cho mọi request tiếp theo.</p>

                <div class="params-title">Body (JSON)</div>
                <table class="params">
                    <thead><tr><th>Field</th><th>Type</th><th>Req</th><th>Mô tả</th></tr></thead>
                    <tbody>
                        <tr><td class="param-name">email</td><td class="param-type">string</td><td class="param-req">✱ bắt buộc</td><td class="param-desc">Email tài khoản admin</td></tr>
                        <tr><td class="param-name">password</td><td class="param-type">string</td><td class="param-req">✱ bắt buộc</td><td class="param-desc">Mật khẩu</td></tr>
                    </tbody>
                </table>

                <div class="code-label">Request</div>
                <div class="code-block">
                    <button class="copy-btn" onclick="copyCode(this)">Copy</button>
<span class="json-key">POST</span> /api/login
<span class="json-key">Content-Type</span>: application/json

{
  <span class="json-key">"email"</span>: <span class="json-str">"admin@gym.com"</span>,
  <span class="json-key">"password"</span>: <span class="json-str">"password123"</span>
}</div>

                <div class="code-label">Response 200</div>
                <div class="code-block">
<button class="copy-btn" onclick="copyCode(this)">Copy</button>
{
  <span class="json-key">"message"</span>: <span class="json-str">"Đăng nhập thành công"</span>,
  <span class="json-key">"token"</span>: <span class="json-str">"1|abc123xyz..."</span>,
  <span class="json-key">"user"</span>: {
    <span class="json-key">"id"</span>: <span class="json-num">1</span>,
    <span class="json-key">"name"</span>: <span class="json-str">"Admin"</span>,
    <span class="json-key">"email"</span>: <span class="json-str">"admin@gym.com"</span>,
    <span class="json-key">"role"</span>: { <span class="json-key">"id"</span>: <span class="json-num">1</span>, <span class="json-key">"name"</span>: <span class="json-str">"admin"</span> },
    <span class="json-key">"branch"</span>: { <span class="json-key">"id"</span>: <span class="json-num">1</span>, <span class="json-key">"branch_name"</span>: <span class="json-str">"Chi nhánh 1"</span> }
  }
}</div>

                <div class="code-label">💡 Dùng token thế nào</div>
                <div class="code-block">
<span class="json-key">Authorization</span>: Bearer <span class="json-str">1|abc123xyz...</span>  <span class="json-note">← thêm vào tất cả request tiếp theo</span></div>
            </div>
        </div>

        <!-- LOGOUT -->
        <div class="endpoint" id="auth-logout">
            <div class="endpoint-header" onclick="toggle(this)">
                <span class="endpoint-method method-POST">POST</span>
                <span class="endpoint-path">/api/logout</span>
                <span class="endpoint-summary">Đăng xuất, thu hồi token</span>
                <span class="toggle-icon">⌄</span>
            </div>
            <div class="endpoint-body">
                <div class="auth-notice">🔒 Yêu cầu Bearer Token</div>
                <div class="code-label">Response 200</div>
                <div class="code-block">{ <span class="json-key">"message"</span>: <span class="json-str">"Đăng xuất thành công"</span> }</div>
            </div>
        </div>

        <!-- ME -->
        <div class="endpoint" id="auth-me">
            <div class="endpoint-header" onclick="toggle(this)">
                <span class="endpoint-method method-GET">GET</span>
                <span class="endpoint-path">/api/me</span>
                <span class="endpoint-summary">Xem thông tin tài khoản đang đăng nhập</span>
                <span class="toggle-icon">⌄</span>
            </div>
            <div class="endpoint-body">
                <div class="auth-notice">🔒 Yêu cầu Bearer Token</div>
                <p style="color:var(--muted);margin-top:8px;">Trả về thông tin user hiện tại kèm role, branch, memberProfile, employeeProfile.</p>
            </div>
        </div>
    </div>

    <!-- ════════════════════════════════════════
         2. THIẾT BỊ
    ════════════════════════════════════════ -->
    <div class="section">
        <div class="section-title">Quản lý thiết bị</div>

        <p style="color:var(--muted);margin-bottom:16px;">Trạng thái thiết bị hợp lệ:</p>
        <div class="status-grid" style="margin-bottom:24px;">
            <span class="status-chip chip-active">active — Đang sử dụng</span>
            <span class="status-chip chip-main">maintenance — Đang bảo trì</span>
            <span class="status-chip chip-broken">broken — Hỏng / Ngừng dùng</span>
        </div>

        <!-- LIST -->
        <div class="endpoint" id="eq-list">
            <div class="endpoint-header" onclick="toggle(this)">
                <span class="endpoint-method method-GET">GET</span>
                <span class="endpoint-path">/api/equipment</span>
                <span class="endpoint-summary">Danh sách tất cả thiết bị</span>
                <span class="toggle-icon">⌄</span>
            </div>
            <div class="endpoint-body">
                <div class="auth-notice">🔒 Yêu cầu Bearer Token</div>

                <div class="params-title">Query Parameters</div>
                <table class="params">
                    <thead><tr><th>Param</th><th>Type</th><th></th><th>Mô tả</th></tr></thead>
                    <tbody>
                        <tr><td class="param-name">branch_id</td><td class="param-type">int</td><td class="param-opt">tùy chọn</td><td class="param-desc">Lọc theo chi nhánh</td></tr>
                        <tr><td class="param-name">status</td><td class="param-type">string</td><td class="param-opt">tùy chọn</td><td class="param-desc"><code>active</code> | <code>maintenance</code> | <code>broken</code></td></tr>
                        <tr><td class="param-name">search</td><td class="param-type">string</td><td class="param-opt">tùy chọn</td><td class="param-desc">Tìm theo tên hoặc serial number</td></tr>
                    </tbody>
                </table>

                <div class="code-label">Request</div>
                <div class="code-block">
<button class="copy-btn" onclick="copyCode(this)">Copy</button>
GET /api/equipment?branch_id=1&status=active
Authorization: Bearer {token}</div>

                <div class="code-label">Response 200</div>
                <div class="code-block">
{
  <span class="json-key">"success"</span>: <span class="json-bool">true</span>,
  <span class="json-key">"total"</span>: <span class="json-num">3</span>,
  <span class="json-key">"data"</span>: [
    {
      <span class="json-key">"id"</span>: <span class="json-num">1</span>,
      <span class="json-key">"equipment_name"</span>: <span class="json-str">"Máy chạy bộ TF-1"</span>,
      <span class="json-key">"serial_number"</span>: <span class="json-str">"TF-2024-001"</span>,
      <span class="json-key">"branch_id"</span>: <span class="json-num">1</span>,
      <span class="json-key">"purchase_date"</span>: <span class="json-str">"2024-01-15"</span>,
      <span class="json-key">"status"</span>: <span class="json-str">"active"</span>,
      <span class="json-key">"branch"</span>: { <span class="json-key">"id"</span>: <span class="json-num">1</span>, <span class="json-key">"branch_name"</span>: <span class="json-str">"Chi nhánh Q1"</span> },
      <span class="json-key">"latest_maintenance"</span>: { <span class="json-key">"maintenance_date"</span>: <span class="json-str">"2025-12-01"</span>, ... }
    }
  ]
}</div>
            </div>
        </div>

        <!-- CREATE -->
        <div class="endpoint" id="eq-create">
            <div class="endpoint-header" onclick="toggle(this)">
                <span class="endpoint-method method-POST">POST</span>
                <span class="endpoint-path">/api/equipment</span>
                <span class="endpoint-summary">Thêm thiết bị mới vào hệ thống</span>
                <span class="toggle-icon">⌄</span>
            </div>
            <div class="endpoint-body">
                <div class="auth-notice">🔒 Yêu cầu Bearer Token</div>

                <div class="params-title">Body (JSON)</div>
                <table class="params">
                    <thead><tr><th>Field</th><th>Type</th><th>Req</th><th>Mô tả</th></tr></thead>
                    <tbody>
                        <tr><td class="param-name">equipment_name</td><td class="param-type">string</td><td class="param-req">✱ bắt buộc</td><td class="param-desc">Tên thiết bị (max 150 ký tự)</td></tr>
                        <tr><td class="param-name">serial_number</td><td class="param-type">string</td><td class="param-req">✱ bắt buộc</td><td class="param-desc">Số serial duy nhất (max 100 ký tự)</td></tr>
                        <tr><td class="param-name">branch_id</td><td class="param-type">int</td><td class="param-req">✱ bắt buộc</td><td class="param-desc">ID chi nhánh (phải tồn tại)</td></tr>
                        <tr><td class="param-name">purchase_date</td><td class="param-type">date</td><td class="param-req">✱ bắt buộc</td><td class="param-desc">Ngày mua (YYYY-MM-DD)</td></tr>
                        <tr><td class="param-name">status</td><td class="param-type">string</td><td class="param-opt">tùy chọn</td><td class="param-desc"><code>active</code> | <code>maintenance</code> | <code>broken</code>. Mặc định: <code>active</code></td></tr>
                    </tbody>
                </table>

                <div class="code-label">Request</div>
                <div class="code-block">
<button class="copy-btn" onclick="copyCode(this)">Copy</button>
POST /api/equipment
Authorization: Bearer {token}
Content-Type: application/json

{
  <span class="json-key">"equipment_name"</span>: <span class="json-str">"Máy đạp xe BC-500"</span>,
  <span class="json-key">"serial_number"</span>: <span class="json-str">"BC-2024-007"</span>,
  <span class="json-key">"branch_id"</span>: <span class="json-num">1</span>,
  <span class="json-key">"purchase_date"</span>: <span class="json-str">"2024-03-20"</span>,
  <span class="json-key">"status"</span>: <span class="json-str">"active"</span>
}</div>

                <div class="code-label">Response 201</div>
                <div class="code-block">
{
  <span class="json-key">"success"</span>: <span class="json-bool">true</span>,
  <span class="json-key">"message"</span>: <span class="json-str">"Đã thêm thiết bị mới"</span>,
  <span class="json-key">"data"</span>: { <span class="json-note">/* object thiết bị vừa tạo */</span> }
}</div>
            </div>
        </div>

        <!-- DETAIL -->
        <div class="endpoint" id="eq-detail">
            <div class="endpoint-header" onclick="toggle(this)">
                <span class="endpoint-method method-GET">GET</span>
                <span class="endpoint-path">/api/equipment/{id}</span>
                <span class="endpoint-summary">Chi tiết thiết bị + lịch sử bảo trì</span>
                <span class="toggle-icon">⌄</span>
            </div>
            <div class="endpoint-body">
                <div class="auth-notice">🔒 Yêu cầu Bearer Token</div>
                <p style="color:var(--muted);margin-top:8px;">Trả về thông tin chi tiết thiết bị kèm: branch, toàn bộ lịch sử bảo trì, lần bảo trì gần nhất, lịch bảo trì sắp tới.</p>

                <div class="code-label">Request</div>
                <div class="code-block">
<button class="copy-btn" onclick="copyCode(this)">Copy</button>
GET /api/equipment/1
Authorization: Bearer {token}</div>
            </div>
        </div>

        <!-- UPDATE -->
        <div class="endpoint" id="eq-update">
            <div class="endpoint-header" onclick="toggle(this)">
                <span class="endpoint-method method-PUT">PUT</span>
                <span class="endpoint-path">/api/equipment/{id}</span>
                <span class="endpoint-summary">Cập nhật thông tin thiết bị</span>
                <span class="toggle-icon">⌄</span>
            </div>
            <div class="endpoint-body">
                <div class="auth-notice">🔒 Yêu cầu Bearer Token</div>
                <p style="color:var(--muted);margin-top:8px;">Cập nhật bất kỳ trường nào. Tất cả đều <strong>optional</strong> (chỉ gửi trường cần thay đổi).</p>

                <div class="params-title">Body (JSON) — tất cả optional</div>
                <table class="params">
                    <thead><tr><th>Field</th><th>Type</th><th>Mô tả</th></tr></thead>
                    <tbody>
                        <tr><td class="param-name">equipment_name</td><td class="param-type">string</td><td class="param-desc">Tên thiết bị</td></tr>
                        <tr><td class="param-name">serial_number</td><td class="param-type">string</td><td class="param-desc">Số serial (duy nhất)</td></tr>
                        <tr><td class="param-name">branch_id</td><td class="param-type">int</td><td class="param-desc">ID chi nhánh</td></tr>
                        <tr><td class="param-name">purchase_date</td><td class="param-type">date</td><td class="param-desc">Ngày mua</td></tr>
                        <tr><td class="param-name">status</td><td class="param-type">string</td><td class="param-desc"><code>active</code> | <code>maintenance</code> | <code>broken</code></td></tr>
                    </tbody>
                </table>

                <div class="code-label">Request</div>
                <div class="code-block">
<button class="copy-btn" onclick="copyCode(this)">Copy</button>
PUT /api/equipment/1
Authorization: Bearer {token}
Content-Type: application/json

{
  <span class="json-key">"status"</span>: <span class="json-str">"broken"</span>
}</div>
            </div>
        </div>

        <!-- STATUS -->
        <div class="endpoint" id="eq-status">
            <div class="endpoint-header" onclick="toggle(this)">
                <span class="endpoint-method method-PATCH">PATCH</span>
                <span class="endpoint-path">/api/equipment/{id}/status</span>
                <span class="endpoint-summary">⚡ Cập nhật nhanh trạng thái thiết bị</span>
                <span class="toggle-icon">⌄</span>
            </div>
            <div class="endpoint-body">
                <div class="auth-notice">🔒 Yêu cầu Bearer Token</div>
                <p style="color:var(--muted);margin-top:8px;">Endpoint chuyên biệt để thay đổi trạng thái. Frontend chỉ cần gửi 1 trường duy nhất.</p>

                <div class="params-title">Body (JSON)</div>
                <table class="params">
                    <thead><tr><th>Field</th><th>Type</th><th>Req</th><th>Mô tả</th></tr></thead>
                    <tbody>
                        <tr><td class="param-name">status</td><td class="param-type">string</td><td class="param-req">✱ bắt buộc</td><td class="param-desc"><code>active</code> | <code>maintenance</code> | <code>broken</code></td></tr>
                    </tbody>
                </table>

                <div class="code-label">Request — đánh dấu thiết bị hỏng</div>
                <div class="code-block">
<button class="copy-btn" onclick="copyCode(this)">Copy</button>
PATCH /api/equipment/1/status
Authorization: Bearer {token}
Content-Type: application/json

{ <span class="json-key">"status"</span>: <span class="json-str">"broken"</span> }</div>

                <div class="code-label">Response 200</div>
                <div class="code-block">
{
  <span class="json-key">"success"</span>: <span class="json-bool">true</span>,
  <span class="json-key">"message"</span>: <span class="json-str">"Đã cập nhật trạng thái: Hỏng / Ngừng sử dụng"</span>,
  <span class="json-key">"data"</span>: { <span class="json-note">/* object thiết bị đã cập nhật */</span> }
}</div>
            </div>
        </div>

        <!-- DELETE -->
        <div class="endpoint" id="eq-delete">
            <div class="endpoint-header" onclick="toggle(this)">
                <span class="endpoint-method method-DELETE">DELETE</span>
                <span class="endpoint-path">/api/equipment/{id}</span>
                <span class="endpoint-summary">Xóa thiết bị (soft delete)</span>
                <span class="toggle-icon">⌄</span>
            </div>
            <div class="endpoint-body">
                <div class="auth-notice">🔒 Yêu cầu Bearer Token</div>
                <p style="color:var(--muted);margin-top:8px;">Xóa mềm (soft delete). Dữ liệu vẫn còn trong DB nhưng không hiển thị trong danh sách.</p>

                <div class="code-label">Request</div>
                <div class="code-block">
<button class="copy-btn" onclick="copyCode(this)">Copy</button>
DELETE /api/equipment/1
Authorization: Bearer {token}</div>

                <div class="code-label">Response 200</div>
                <div class="code-block">{ <span class="json-key">"success"</span>: <span class="json-bool">true</span>, <span class="json-key">"message"</span>: <span class="json-str">"Đã xóa thiết bị khỏi hệ thống"</span> }</div>
            </div>
        </div>
    </div>

    <!-- ════════════════════════════════════════
         3. BẢO TRÌ
    ════════════════════════════════════════ -->
    <div class="section">
        <div class="section-title">Lịch bảo trì thiết bị</div>

        <!-- LIST -->
        <div class="endpoint" id="mt-list">
            <div class="endpoint-header" onclick="toggle(this)">
                <span class="endpoint-method method-GET">GET</span>
                <span class="endpoint-path">/api/equipment-maintenance</span>
                <span class="endpoint-summary">Danh sách lịch bảo trì</span>
                <span class="toggle-icon">⌄</span>
            </div>
            <div class="endpoint-body">
                <div class="auth-notice">🔒 Yêu cầu Bearer Token</div>

                <div class="params-title">Query Parameters</div>
                <table class="params">
                    <thead><tr><th>Param</th><th>Type</th><th></th><th>Mô tả</th></tr></thead>
                    <tbody>
                        <tr><td class="param-name">equipment_id</td><td class="param-type">int</td><td class="param-opt">tùy chọn</td><td class="param-desc">Lọc theo thiết bị</td></tr>
                        <tr><td class="param-name">periodic</td><td class="param-type">bool</td><td class="param-opt">tùy chọn</td><td class="param-desc"><code>1</code> = chỉ lấy lịch bảo trì định kỳ</td></tr>
                        <tr><td class="param-name">due_soon</td><td class="param-type">int</td><td class="param-opt">tùy chọn</td><td class="param-desc">Lịch sắp đến hạn trong N ngày (VD: <code>due_soon=7</code>)</td></tr>
                    </tbody>
                </table>

                <div class="code-label">Request</div>
                <div class="code-block">
<button class="copy-btn" onclick="copyCode(this)">Copy</button>
GET /api/equipment-maintenance?equipment_id=1&periodic=1
Authorization: Bearer {token}</div>
            </div>
        </div>

        <!-- CREATE -->
        <div class="endpoint" id="mt-create">
            <div class="endpoint-header" onclick="toggle(this)">
                <span class="endpoint-method method-POST">POST</span>
                <span class="endpoint-path">/api/equipment-maintenance</span>
                <span class="endpoint-summary">Tạo lịch bảo trì (1 lần hoặc định kỳ)</span>
                <span class="toggle-icon">⌄</span>
            </div>
            <div class="endpoint-body">
                <div class="auth-notice">🔒 Yêu cầu Bearer Token</div>
                <p style="color:var(--muted);margin-top:8px;">Khi <code>is_periodic=true</code>, hệ thống tự tính <code>next_maintenance_date = maintenance_date + interval_days</code> và tự cập nhật status thiết bị → <code>maintenance</code>.</p>

                <div class="params-title">Body (JSON)</div>
                <table class="params">
                    <thead><tr><th>Field</th><th>Type</th><th>Req</th><th>Mô tả</th></tr></thead>
                    <tbody>
                        <tr><td class="param-name">equipment_id</td><td class="param-type">int</td><td class="param-req">✱ bắt buộc</td><td class="param-desc">ID thiết bị cần bảo trì</td></tr>
                        <tr><td class="param-name">maintenance_date</td><td class="param-type">date</td><td class="param-req">✱ bắt buộc</td><td class="param-desc">Ngày bắt đầu bảo trì (YYYY-MM-DD)</td></tr>
                        <tr><td class="param-name">technician_id</td><td class="param-type">int</td><td class="param-opt">tùy chọn</td><td class="param-desc">ID kỹ thuật viên (users.id)</td></tr>
                        <tr><td class="param-name">description</td><td class="param-type">string</td><td class="param-opt">tùy chọn</td><td class="param-desc">Mô tả công việc bảo trì</td></tr>
                        <tr><td class="param-name">cost</td><td class="param-type">number</td><td class="param-opt">tùy chọn</td><td class="param-desc">Chi phí bảo trì (VNĐ)</td></tr>
                        <tr><td class="param-name">is_periodic</td><td class="param-type">bool</td><td class="param-opt">tùy chọn</td><td class="param-desc"><code>true</code> = lịch định kỳ. Mặc định: <code>false</code></td></tr>
                        <tr><td class="param-name">interval_days</td><td class="param-type">int</td><td class="param-opt">bắt buộc nếu is_periodic=true</td><td class="param-desc">Chu kỳ bảo trì (ngày). VD: 30, 90, 180</td></tr>
                    </tbody>
                </table>

                <div class="code-label">Request — Lịch bảo trì 1 lần</div>
                <div class="code-block">
<button class="copy-btn" onclick="copyCode(this)">Copy</button>
POST /api/equipment-maintenance
Authorization: Bearer {token}
Content-Type: application/json

{
  <span class="json-key">"equipment_id"</span>: <span class="json-num">1</span>,
  <span class="json-key">"maintenance_date"</span>: <span class="json-str">"2026-04-10"</span>,
  <span class="json-key">"technician_id"</span>: <span class="json-num">3</span>,
  <span class="json-key">"description"</span>: <span class="json-str">"Thay dây curoa, vệ sinh máy"</span>,
  <span class="json-key">"cost"</span>: <span class="json-num">500000</span>,
  <span class="json-key">"is_periodic"</span>: <span class="json-bool">false</span>
}</div>

                <div class="code-label">Request — Lịch bảo trì định kỳ 90 ngày</div>
                <div class="code-block">
<button class="copy-btn" onclick="copyCode(this)">Copy</button>
POST /api/equipment-maintenance
Authorization: Bearer {token}
Content-Type: application/json

{
  <span class="json-key">"equipment_id"</span>: <span class="json-num">1</span>,
  <span class="json-key">"maintenance_date"</span>: <span class="json-str">"2026-04-10"</span>,
  <span class="json-key">"description"</span>: <span class="json-str">"Bảo dưỡng định kỳ hàng quý"</span>,
  <span class="json-key">"cost"</span>: <span class="json-num">300000</span>,
  <span class="json-key">"is_periodic"</span>: <span class="json-bool">true</span>,
  <span class="json-key">"interval_days"</span>: <span class="json-num">90</span>  <span class="json-note">← next_maintenance_date = 2026-07-09</span>
}</div>

                <div class="code-label">Response 201</div>
                <div class="code-block">
{
  <span class="json-key">"success"</span>: <span class="json-bool">true</span>,
  <span class="json-key">"message"</span>: <span class="json-str">"Đã tạo lịch bảo trì định kỳ"</span>,
  <span class="json-key">"data"</span>: {
    <span class="json-key">"id"</span>: <span class="json-num">5</span>,
    <span class="json-key">"equipment_id"</span>: <span class="json-num">1</span>,
    <span class="json-key">"maintenance_date"</span>: <span class="json-str">"2026-04-10"</span>,
    <span class="json-key">"is_periodic"</span>: <span class="json-bool">true</span>,
    <span class="json-key">"interval_days"</span>: <span class="json-num">90</span>,
    <span class="json-key">"next_maintenance_date"</span>: <span class="json-str">"2026-07-09"</span>,
    <span class="json-key">"cost"</span>: <span class="json-str">"300000.00"</span>
  }
}</div>
            </div>
        </div>

        <!-- DETAIL -->
        <div class="endpoint" id="mt-detail">
            <div class="endpoint-header" onclick="toggle(this)">
                <span class="endpoint-method method-GET">GET</span>
                <span class="endpoint-path">/api/equipment-maintenance/{id}</span>
                <span class="endpoint-summary">Chi tiết một bản ghi bảo trì</span>
                <span class="toggle-icon">⌄</span>
            </div>
            <div class="endpoint-body">
                <div class="auth-notice">🔒 Yêu cầu Bearer Token</div>
                <div class="code-label">Request</div>
                <div class="code-block">
<button class="copy-btn" onclick="copyCode(this)">Copy</button>
GET /api/equipment-maintenance/5
Authorization: Bearer {token}</div>
            </div>
        </div>

        <!-- UPDATE -->
        <div class="endpoint" id="mt-update">
            <div class="endpoint-header" onclick="toggle(this)">
                <span class="endpoint-method method-PUT">PUT</span>
                <span class="endpoint-path">/api/equipment-maintenance/{id}</span>
                <span class="endpoint-summary">Cập nhật lịch bảo trì</span>
                <span class="toggle-icon">⌄</span>
            </div>
            <div class="endpoint-body">
                <div class="auth-notice">🔒 Yêu cầu Bearer Token</div>

                <div class="params-title">Body (JSON) — tất cả optional</div>
                <table class="params">
                    <thead><tr><th>Field</th><th>Type</th><th>Mô tả</th></tr></thead>
                    <tbody>
                        <tr><td class="param-name">maintenance_date</td><td class="param-type">date</td><td class="param-desc">Ngày bảo trì</td></tr>
                        <tr><td class="param-name">technician_id</td><td class="param-type">int</td><td class="param-desc">ID kỹ thuật viên</td></tr>
                        <tr><td class="param-name">description</td><td class="param-type">string</td><td class="param-desc">Ghi chú bảo trì</td></tr>
                        <tr><td class="param-name">cost</td><td class="param-type">number</td><td class="param-desc">Chi phí</td></tr>
                        <tr><td class="param-name">is_periodic</td><td class="param-type">bool</td><td class="param-desc">Bật/tắt định kỳ</td></tr>
                        <tr><td class="param-name">interval_days</td><td class="param-type">int</td><td class="param-desc">Chu kỳ (ngày)</td></tr>
                    </tbody>
                </table>
            </div>
        </div>

        <!-- DUE SOON -->
        <div class="endpoint" id="mt-duesoon">
            <div class="endpoint-header" onclick="toggle(this)">
                <span class="endpoint-method method-GET">GET</span>
                <span class="endpoint-path">/api/equipment-maintenance/due-soon</span>
                <span class="endpoint-summary">⚡ Lịch bảo trì sắp đến hạn</span>
                <span class="toggle-icon">⌄</span>
            </div>
            <div class="endpoint-body">
                <div class="auth-notice">🔒 Yêu cầu Bearer Token</div>
                <p style="color:var(--muted);margin-top:8px;">Trả về tất cả lịch bảo trì định kỳ có <code>next_maintenance_date</code> nằm trong N ngày tới. Dùng để hiển thị cảnh báo trên dashboard.</p>

                <div class="params-title">Query Parameters</div>
                <table class="params">
                    <thead><tr><th>Param</th><th>Type</th><th></th><th>Mô tả</th></tr></thead>
                    <tbody>
                        <tr><td class="param-name">days</td><td class="param-type">int</td><td class="param-opt">tùy chọn</td><td class="param-desc">Số ngày tới kiểm tra. Mặc định: <code>7</code></td></tr>
                    </tbody>
                </table>

                <div class="code-label">Request</div>
                <div class="code-block">
<button class="copy-btn" onclick="copyCode(this)">Copy</button>
GET /api/equipment-maintenance/due-soon?days=30
Authorization: Bearer {token}</div>

                <div class="code-label">Response 200</div>
                <div class="code-block">
{
  <span class="json-key">"success"</span>: <span class="json-bool">true</span>,
  <span class="json-key">"message"</span>: <span class="json-str">"Lịch bảo trì sắp đến hạn trong 30 ngày"</span>,
  <span class="json-key">"total"</span>: <span class="json-num">2</span>,
  <span class="json-key">"data"</span>: [ <span class="json-note">/* danh sách lịch */</span> ]
}</div>
            </div>
        </div>

        <!-- DELETE -->
        <div class="endpoint" id="mt-delete">
            <div class="endpoint-header" onclick="toggle(this)">
                <span class="endpoint-method method-DELETE">DELETE</span>
                <span class="endpoint-path">/api/equipment-maintenance/{id}</span>
                <span class="endpoint-summary">Xóa lịch bảo trì</span>
                <span class="toggle-icon">⌄</span>
            </div>
            <div class="endpoint-body">
                <div class="auth-notice">🔒 Yêu cầu Bearer Token</div>
                <div class="code-label">Request</div>
                <div class="code-block">
<button class="copy-btn" onclick="copyCode(this)">Copy</button>
DELETE /api/equipment-maintenance/5
Authorization: Bearer {token}</div>
                <div class="code-label">Response 200</div>
                <div class="code-block">{ <span class="json-key">"success"</span>: <span class="json-bool">true</span>, <span class="json-key">"message"</span>: <span class="json-str">"Đã xóa lịch bảo trì"</span> }</div>
            </div>
        </div>
    </div>

    <!-- ════════════════════════════════════════
         FOOTER
    ════════════════════════════════════════ -->
    <div style="border-top:1px solid var(--border);padding-top:24px;margin-top:40px;color:var(--muted);font-size:12px;text-align:center;">
        Gym Equipment Management API · Laravel 11 + Sanctum · Generated 2026
    </div>
</main>

<script>
    function toggle(header) {
        const card = header.closest('.endpoint');
        card.classList.toggle('open');
    }

    // Auto-open first endpoint of each section for preview
    document.querySelectorAll('.section').forEach(sec => {
        const first = sec.querySelector('.endpoint');
        if (first) first.classList.add('open');
    });

    // Sidebar active highlight on scroll
    const sections = document.querySelectorAll('.endpoint[id]');
    const navItems = document.querySelectorAll('.nav-item');
    const observer = new IntersectionObserver(entries => {
        entries.forEach(e => {
            if (e.isIntersecting) {
                navItems.forEach(n => n.classList.remove('active'));
                const active = document.querySelector(`.nav-item[href="#${e.target.id}"]`);
                if (active) active.classList.add('active');
            }
        });
    }, { threshold: 0.6 });
    sections.forEach(s => observer.observe(s));

    function copyCode(btn) {
        const block = btn.closest('.code-block');
        const text = block.innerText.replace('Copy', '').trim();
        navigator.clipboard.writeText(text).then(() => {
            btn.textContent = '✓ Copied';
            setTimeout(() => btn.textContent = 'Copy', 1800);
        });
    }
</script>
</body>
</html>
