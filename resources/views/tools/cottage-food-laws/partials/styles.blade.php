<style>
    :root {
        --primary-pink: #e67399;
        --primary-pink-dark: #d25a80;
        --deep-purple: #6d28d9;
        --soft-pink: #fff7fa;
        --dark-section: #1a0a2e;
        --text-dark: #333333;
        --text-gray: #666666;
        --white: #ffffff;
        --card-bg: #ffffff;
        --border-color: #f0dde4;
        --input-bg: #fdfafb;
        --page-bg: var(--soft-pink);
        --green: #1f7a3f;
        --green-bg: #e2f5e8;
        --red: #a12727;
        --red-bg: #fde2e2;
    }

    * { margin: 0; padding: 0; box-sizing: border-box; }

    body {
        font-family: 'Inter', sans-serif;
        color: var(--text-dark);
        background-color: var(--page-bg);
        line-height: 1.6;
    }

    h1, h2, h3, h4 { font-family: 'Outfit', sans-serif; color: var(--dark-section); line-height: 1.2; }

    a { text-decoration: none; color: inherit; }

    .container { max-width: 1200px; margin: 0 auto; padding: 0 24px; }

    .visually-hidden {
        position: absolute; width: 1px; height: 1px; padding: 0; margin: -1px;
        overflow: hidden; clip: rect(0,0,0,0); white-space: nowrap; border: 0;
    }

    .btn {
        display: inline-block;
        padding: 14px 28px;
        border-radius: 8px;
        font-weight: 600;
        transition: all 0.3s ease;
        cursor: pointer;
        border: none;
        font-size: 1rem;
        text-align: center;
    }
    .btn-primary { background-color: var(--primary-pink); color: #fff; box-shadow: 0 4px 14px rgba(230, 115, 153, 0.4); }
    .btn-primary:hover { background-color: var(--primary-pink-dark); transform: translateY(-2px); }
    .btn-primary:disabled { opacity: 0.5; cursor: not-allowed; transform: none; box-shadow: none; }
    .btn-light { background-color: var(--white); color: var(--dark-section); border: 1px solid var(--border-color); }
    .btn-light:hover { background-color: var(--input-bg); transform: translateY(-2px); }
    .btn-outline { background: transparent; color: var(--primary-pink-dark); border: 2px solid var(--primary-pink); }
    .btn-outline:hover { background: var(--primary-pink); color: #fff; }
    .btn-small { padding: 8px 14px; font-size: 0.85rem; border-radius: 6px; }
    .btn:disabled { opacity: 0.55; cursor: not-allowed; transform: none !important; }

    /* Navigation */
    .navbar {
        position: sticky; top: 0; left: 0; width: 100%;
        background-color: rgba(255, 255, 255, 0.95);
        backdrop-filter: blur(10px);
        z-index: 1000;
        box-shadow: 0 2px 10px rgba(0,0,0,0.05);
    }
    .navbar .container { display: flex; justify-content: space-between; align-items: center; }
    .nav-logo img { height: 56px; width: auto; object-fit: contain; }
    .nav-links { display: flex; align-items: center; gap: 20px; }
    .nav-links a.nav-login { font-weight: 500; }
    .nav-links a.nav-login:hover { color: var(--primary-pink); }
    .nav-login.active { color: var(--primary-pink-dark); font-weight: 700; }

    .nav-dropdown { position: relative; }
    .nav-dropdown-toggle {
        display: inline-flex; align-items: center; gap: 5px;
        background: none; border: none; font-family: 'Inter', sans-serif;
        font-size: 1rem; cursor: pointer; padding: 0;
    }
    .nav-dropdown-toggle .caret { font-size: 0.6rem; transition: transform 0.2s ease; }
    .nav-dropdown.open .nav-dropdown-toggle .caret { transform: rotate(180deg); }
    .nav-dropdown-menu {
        display: none; position: absolute; top: calc(100% + 14px); left: 0;
        background: var(--card-bg); border: 1px solid var(--border-color);
        border-radius: 10px; box-shadow: 0 10px 30px rgba(0,0,0,0.12);
        padding: 8px; min-width: 220px; z-index: 1001;
    }
    .nav-dropdown.open .nav-dropdown-menu { display: block; }
    .nav-dropdown-menu a {
        display: block; padding: 10px 14px; border-radius: 6px;
        font-size: 0.92rem; font-weight: 500; color: var(--text-dark); white-space: nowrap;
    }
    .nav-dropdown-menu a:hover { background: var(--input-bg); color: var(--primary-pink-dark); }
    .nav-dropdown-menu a.active { color: var(--primary-pink-dark); font-weight: 700; }

    /* Mobile hamburger menu */
    .nav-hamburger {
        display: none;
        background: none;
        border: none;
        cursor: pointer;
        padding: 8px;
        font-size: 1.5rem;
        line-height: 1;
        color: var(--dark-section);
    }
    @media (max-width: 768px) {
        .nav-hamburger { display: block; }
        .nav-links {
            display: none;
            position: absolute;
            top: 100%;
            left: 0;
            right: 0;
            background: var(--card-bg);
            flex-direction: column;
            align-items: stretch;
            gap: 4px;
            padding: 16px 24px 24px;
            box-shadow: 0 10px 30px rgba(0,0,0,0.12);
            border-top: 1px solid var(--border-color);
        }
        .nav-links.open { display: flex; }
        .nav-links .nav-login { padding: 10px 0; }
        .nav-links a.btn { text-align: center; margin-top: 8px; }
        .nav-dropdown { width: 100%; }
        .nav-dropdown-toggle { width: 100%; justify-content: space-between; padding: 10px 0; }
        .nav-dropdown-menu {
            position: static;
            box-shadow: none;
            border: none;
            padding: 0 0 0 14px;
            margin-top: 0;
            min-width: 0;
        }
    }

    /* Hero */
    .tool-hero { padding: 48px 0 20px; text-align: center; }
    .tool-hero h1 { font-size: 2.3rem; margin-bottom: 14px; }
    .tool-hero p { color: var(--text-gray); font-size: 1.1rem; max-width: 680px; margin: 0 auto; }

    /* Breadcrumbs */
    .breadcrumbs { padding: 16px 0 0; font-size: 0.85rem; color: var(--text-gray); }
    .breadcrumbs a:hover { color: var(--primary-pink-dark); }
    .breadcrumbs .sep { margin: 0 6px; }

    /* Cards */
    .card {
        background: var(--card-bg);
        border: 1px solid var(--border-color);
        border-radius: 16px;
        padding: 24px;
        margin-bottom: 20px;
        box-shadow: 0 4px 20px rgba(0,0,0,0.04);
    }
    .card h2 { font-size: 1.3rem; margin-bottom: 12px; }
    .card h2 .icon { margin-right: 8px; }

    /* State picker (index page) */
    .state-picker-card {
        background: var(--card-bg);
        border: 1px solid var(--border-color);
        border-radius: 16px;
        padding: 28px;
        margin: 24px 0;
        box-shadow: 0 4px 20px rgba(0,0,0,0.04);
    }
    .state-picker-card label { display: block; font-weight: 600; margin-bottom: 8px; }
    .state-search-wrap { position: relative; }
    #state-search {
        width: 100%; padding: 14px 16px; border-radius: 10px;
        border: 1px solid var(--border-color); background: var(--input-bg);
        font-size: 1.05rem; font-family: 'Inter', sans-serif; color: var(--text-dark);
    }
    #state-search:focus { outline: 2px solid var(--primary-pink); outline-offset: 1px; }
    .state-search-results {
        display: none; position: absolute; top: calc(100% + 6px); left: 0; right: 0;
        background: var(--white); border: 1px solid var(--border-color); border-radius: 10px;
        box-shadow: 0 10px 30px rgba(0,0,0,0.12); z-index: 50; max-height: 320px; overflow-y: auto;
    }
    .state-search-item {
        display: flex; justify-content: space-between; align-items: center;
        padding: 12px 16px; font-weight: 500; border-bottom: 1px solid var(--border-color);
    }
    .state-search-item:last-child { border-bottom: none; }
    .state-search-item span { color: var(--text-gray); font-size: 0.8rem; font-weight: 400; }
    .state-search-item:hover { background: var(--soft-pink); color: var(--primary-pink-dark); }
    .state-search-empty { padding: 14px 16px; color: var(--text-gray); font-size: 0.9rem; }
    .state-picker-hint { color: var(--text-gray); font-size: 0.85rem; margin-top: 10px; }

    /* Recently viewed */
    .recent-states { margin-bottom: 20px; }
    .recent-states h2 { font-size: 1.05rem; margin-bottom: 10px; }
    .recent-chips { display: flex; gap: 8px; flex-wrap: wrap; }
    .recent-chip {
        background: var(--card-bg); border: 1px solid var(--border-color);
        border-radius: 999px; padding: 8px 16px; font-size: 0.88rem; font-weight: 600;
    }
    .recent-chip:hover { border-color: var(--primary-pink); color: var(--primary-pink-dark); }

    /* Compare */
    .compare-bar {
        position: sticky; top: 72px; z-index: 40;
        display: flex; justify-content: space-between; align-items: center;
        background: var(--dark-section); color: #fff; border-radius: 12px;
        padding: 14px 20px; margin-bottom: 20px; font-weight: 600; font-size: 0.9rem;
    }
    .compare-bar .btn-light { color: var(--dark-section); }
    .compare-panel {
        background: var(--card-bg); border: 1px solid var(--border-color);
        border-radius: 16px; padding: 24px; margin-bottom: 24px;
        box-shadow: 0 4px 20px rgba(0,0,0,0.04);
    }
    .compare-table-wrap { overflow-x: auto; }
    .compare-table { width: 100%; border-collapse: collapse; margin-top: 12px; }
    .compare-table th, .compare-table td {
        text-align: left; padding: 12px 16px; border-bottom: 1px solid var(--border-color);
        font-size: 0.92rem; white-space: nowrap;
    }
    .compare-table th { font-family: 'Outfit', sans-serif; color: var(--dark-section); }

    /* State grid */
    .state-grid {
        display: grid; grid-template-columns: repeat(auto-fill, minmax(220px, 1fr));
        gap: 14px; margin: 24px 0 40px;
    }
    .state-card {
        position: relative;
        background: var(--card-bg); border: 1px solid var(--border-color); border-radius: 12px;
        transition: all 0.2s ease;
    }
    .state-card:hover { border-color: var(--primary-pink); box-shadow: 0 6px 18px rgba(230,115,153,0.15); }
    .state-compare-check { position: absolute; top: 10px; right: 10px; z-index: 2; }
    .state-compare-check input { width: 18px; height: 18px; cursor: pointer; }
    .state-card-link { display: block; padding: 18px; }
    .state-abbr {
        display: inline-block; background: var(--soft-pink); color: var(--primary-pink-dark);
        font-weight: 700; font-size: 0.75rem; border-radius: 6px; padding: 3px 8px; margin-bottom: 8px;
    }
    .state-name { display: block; font-weight: 700; font-size: 1.05rem; margin-bottom: 4px; }
    .state-limit { display: block; color: var(--text-gray); font-size: 0.82rem; }

    /* Disclaimer */
    .disclaimer-box {
        background: #fff8ec; border: 1px solid #f3d9a0; border-radius: 12px;
        padding: 18px 22px; margin: 32px auto; font-size: 0.88rem; color: #7a5c1e; line-height: 1.6;
    }

    /* Overview */
    .overview-meta { display: flex; flex-wrap: wrap; gap: 16px; margin-top: 14px; font-size: 0.85rem; color: var(--text-gray); }
    .overview-meta strong { color: var(--text-dark); }

    /* Two-column food lists */
    .food-columns { display: grid; grid-template-columns: 1fr 1fr; gap: 20px; }
    @media (max-width: 640px) { .food-columns { grid-template-columns: 1fr; } }
    .food-list { list-style: none; }
    .food-list li { padding: 8px 0; font-size: 0.92rem; display: flex; gap: 8px; align-items: flex-start; }
    .food-list.allowed li::before { content: "✓"; color: var(--green); font-weight: 700; flex-shrink: 0; }
    .food-list.prohibited li::before { content: "✕"; color: var(--red); font-weight: 700; flex-shrink: 0; }
    .food-note {
        margin-top: 14px; background: var(--soft-pink); border-radius: 10px; padding: 12px 16px;
        font-size: 0.85rem; color: var(--text-dark);
    }

    /* Selling locations */
    .locations-grid { display: grid; grid-template-columns: repeat(auto-fill, minmax(160px, 1fr)); gap: 10px; }
    .location-pill {
        display: flex; align-items: center; gap: 8px; border: 1px solid var(--border-color);
        border-radius: 10px; padding: 10px 14px; font-size: 0.88rem; font-weight: 600;
    }
    .location-pill.yes { background: var(--green-bg); color: var(--green); border-color: transparent; }
    .location-pill.no { background: var(--red-bg); color: var(--red); border-color: transparent; }

    /* Revenue limit highlight */
    .sales-limit-highlight {
        text-align: center; background: linear-gradient(135deg, var(--primary-pink), var(--deep-purple));
        border-radius: 14px; padding: 26px 20px; color: #fff;
    }
    .sales-limit-highlight .label { font-size: 0.8rem; opacity: 0.9; font-weight: 600; text-transform: uppercase; letter-spacing: 0.03em; }
    .sales-limit-highlight .amount { font-family: 'Outfit', sans-serif; font-size: 1.9rem; font-weight: 800; margin-top: 6px; }

    /* Permits grid */
    .permit-grid { display: grid; grid-template-columns: 1fr 1fr; gap: 16px; }
    @media (max-width: 640px) { .permit-grid { grid-template-columns: 1fr; } }
    .permit-item { border: 1px solid var(--border-color); border-radius: 10px; padding: 14px 16px; }
    .permit-item h4 { font-size: 0.82rem; color: var(--text-gray); text-transform: uppercase; letter-spacing: 0.03em; margin-bottom: 6px; }
    .permit-item p { font-size: 0.92rem; }

    /* Labeling list */
    .labeling-list { list-style: none; counter-reset: label-counter; }
    .labeling-list li {
        counter-increment: label-counter; padding: 10px 0 10px 34px; position: relative;
        font-size: 0.92rem; border-bottom: 1px solid var(--border-color);
    }
    .labeling-list li:last-child { border-bottom: none; }
    .labeling-list li::before {
        content: counter(label-counter); position: absolute; left: 0; top: 8px;
        background: var(--primary-pink); color: #fff; width: 22px; height: 22px; border-radius: 50%;
        display: flex; align-items: center; justify-content: center; font-size: 0.75rem; font-weight: 700;
    }
    .label-disclaimer {
        margin-top: 14px; font-size: 0.78rem; color: var(--text-gray); font-style: italic;
        border-top: 1px solid var(--border-color); padding-top: 12px;
    }

    /* Checklist */
    .checklist { list-style: none; }
    .checklist li { padding: 4px 0; }
    .checklist label { display: flex; align-items: center; gap: 12px; font-size: 0.95rem; cursor: pointer; padding: 8px 4px; }
    .checklist input[type="checkbox"] { width: 20px; height: 20px; flex-shrink: 0; cursor: pointer; }
    .checklist label.checked span { text-decoration: line-through; color: var(--text-gray); }

    /* Actions (share/print) */
    .page-actions { display: flex; gap: 8px; flex-wrap: wrap; justify-content: center; margin-top: 18px; }

    /* CTA */
    .cta-section {
        background: var(--dark-section); color: #fff; padding: 60px 24px;
        text-align: center; border-radius: 24px; margin-bottom: 40px;
    }
    .cta-section h2 { color: #fff; font-size: 2rem; margin-bottom: 12px; }
    .cta-section p { color: #d9c9d3; max-width: 560px; margin: 0 auto 24px; }
    .cta-buttons { display: flex; gap: 14px; justify-content: center; flex-wrap: wrap; }

    /* FAQ */
    .faq-section { padding: 10px 0 40px; }
    .faq-section h2 { text-align: center; margin-bottom: 24px; }
    .faq-item { background: var(--card-bg); border: 1px solid var(--border-color); border-radius: 12px; padding: 4px 20px; margin-bottom: 12px; }
    .faq-item summary { padding: 16px 0; font-weight: 600; cursor: pointer; }
    .faq-item p { padding-bottom: 16px; color: var(--text-gray); }

    footer.site-footer { text-align: center; padding: 30px 24px; color: var(--text-gray); font-size: 0.85rem; }
    footer.site-footer a { text-decoration: underline; }

    .copy-toast {
        position: fixed; bottom: 24px; left: 50%; transform: translateX(-50%) translateY(20px);
        background: var(--dark-section); color: #fff; padding: 12px 20px; border-radius: 8px;
        font-size: 0.9rem; opacity: 0; pointer-events: none; transition: all 0.25s ease; z-index: 2000;
    }
    .copy-toast.show { opacity: 1; transform: translateX(-50%) translateY(0); }

    .other-states { padding: 8px 0 40px; }
    .other-states h2 { font-size: 1.2rem; margin-bottom: 14px; }
    .other-states-list { display: flex; flex-wrap: wrap; gap: 8px; }
    .other-states-list a {
        border: 1px solid var(--border-color); border-radius: 999px; padding: 7px 14px;
        font-size: 0.82rem; font-weight: 600; background: var(--card-bg);
    }
    .other-states-list a:hover { border-color: var(--primary-pink); color: var(--primary-pink-dark); }

    @media print {
        .navbar, .cta-section, .faq-section, .page-actions, .other-states, footer.site-footer, .breadcrumbs { display: none !important; }
        body { background: #fff; }
        .card { box-shadow: none; border: 1px solid #ccc; }
    }
</style>
