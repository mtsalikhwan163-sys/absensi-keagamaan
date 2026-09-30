:root {
    --primary: #1f6f4a;
    --primary-dark: #154f39;
    --secondary: #f0c76e;
    --bg: #f5f7f5;
    --text: #1b1e21;
    --muted: #7a7f87;
    --success: #2bb673;
    --warning: #f4b942;
    --danger: #d9534f;
    --info: #3ab0ff;
    --card: #ffffff;
}

* { box-sizing: border-box; }
body {
    margin: 0;
    background: var(--bg);
    color: var(--text);
    font-family: 'Segoe UI', sans-serif;
}

.login-page {
    background: linear-gradient(135deg, #eaf7f0 0%, #f7f1dd 100%);
}

.login-card {
    border-radius: 24px;
    overflow: hidden;
}

.brand-icon {
    width: 70px;
    height: 70px;
    border-radius: 18px;
    margin: auto;
    display: grid;
    place-items: center;
    font-size: 2rem;
    background: linear-gradient(145deg, var(--primary), var(--primary-dark));
    color: #fff;
    box-shadow: 0 8px 25px rgba(31, 111, 74, 0.25);
}

.btn-login { background: linear-gradient(135deg, var(--primary), var(--primary-dark)); border: none; }
.btn-login:hover { background: linear-gradient(135deg, var(--primary-dark), var(--primary)); }

.app-shell {
    display: flex;
    min-height: 100vh;
}

.sidebar {
    width: 280px;
    background: linear-gradient(180deg, var(--primary) 0%, var(--primary-dark) 100%);
    color: #fff;
    padding: 20px 18px;
}

.brand-box {
    display: flex;
    align-items: center;
    gap: 12px;
    padding-bottom: 20px;
    border-bottom: 1px solid rgba(255,255,255,0.2);
}

.brand-box .brand-icon {
    width: 44px;
    height: 44px;
    font-size: 1.2rem;
    border-radius: 12px;
}

.sidebar .nav-link {
    color: rgba(255,255,255,0.85);
    padding: 10px 14px;
    border-radius: 10px;
    margin-top: 4px;
}

.sidebar .nav-link:hover {
    background: rgba(255,255,255,0.08);
    color: #fff;
}

.nav-section {
    color: rgba(255,255,255,0.6);
    font-size: 0.75rem;
    text-transform: uppercase;
    letter-spacing: 1px;
    margin: 16px 10px 8px;
}

.main-content {
    flex: 1;
    display: flex;
    flex-direction: column;
}

.topbar {
    display: flex;
    justify-content: space-between;
    align-items: center;
    background: rgba(255,255,255,0.9);
    backdrop-filter: blur(8px);
    padding: 18px 28px;
    border-bottom: 1px solid rgba(0,0,0,0.06);
}

.content-body {
    padding: 24px;
}

.custom-card {
    border: none;
    border-radius: 16px;
    box-shadow: 0 8px 30px rgba(0,0,0,0.04);
}

.card-header {
    background: #fff;
    font-weight: 600;
    border-bottom: 1px solid rgba(0,0,0,0.05);
    padding: 16px 20px;
}

.stat-card {
    background: #fff;
    border-radius: 16px;
    box-shadow: 0 8px 30px rgba(0,0,0,0.04);
    padding: 20px;
    display: flex;
    align-items: center;
    gap: 18px;
}

.stat-card .icon {
    width: 58px;
    height: 58px;
    border-radius: 16px;
    display: grid;
    place-items: center;
    font-size: 1.5rem;
    color: #fff;
}

.stat-card .label {
    color: var(--muted);
    font-size: 0.8rem;
}

.stat-card .value {
    font-size: 1.9rem;
    font-weight: 700;
}

.mini-card {
    background: #fff;
    border-radius: 16px;
    padding: 18px 20px;
    box-shadow: 0 8px 25px rgba(0,0,0,0.04);
}

.mini-card.success { border-left: 5px solid var(--success); }
.mini-card.warning { border-left: 5px solid var(--warning); }
.mini-card.danger { border-left: 5px solid var(--danger); }
.mini-card.secondary { border-left: 5px solid #6c757d; }

.avatar-sm {
    width: 40px;
    height: 40px;
    border-radius: 50%;
    object-fit: cover;
}

.avatar-xl {
    width: 120px;
    height: 120px;
    border-radius: 50%;
    object-fit: cover;
    border: 4px solid #eef5ef;
}

.table th {
    background: rgba(31, 111, 74, 0.04);
    color: var(--text);
}

@media (max-width: 991px) {
    .app-shell { display: block; }
    .sidebar {
        width: 100%;
        min-height: auto;
    }
    .topbar { padding: 16px 20px; }
    .content-body { padding: 16px; }
}

@media print {
    .sidebar, .topbar, .btn, .card-footer { display: none !important; }
    .main-content { display: block; }
}
