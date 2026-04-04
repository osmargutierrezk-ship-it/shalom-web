<?php
/**
 * dashboard.php — Panel Estudiantil CBS
 * Requiere sesión activa con: codigo, nombre, grado, seccion
 */
session_start();

// ── Protección de ruta ──────────────────────────────────────────
if (empty($_SESSION['logged_in'])) {
    header('Location: registro.html');
    exit;
}

$nombre   = htmlspecialchars($_SESSION['nombre']  ?? 'Estudiante',    ENT_QUOTES);
$codigo   = htmlspecialchars($_SESSION['codigo']  ?? '',              ENT_QUOTES);
$grado    = htmlspecialchars($_SESSION['grado']   ?? '4to Básico',    ENT_QUOTES);
$seccion  = htmlspecialchars($_SESSION['seccion'] ?? 'A',             ENT_QUOTES);

// Iniciales del avatar (2 primeras letras de nombre y apellido)
$partes    = explode(' ', trim($nombre));
$iniciales = strtoupper(
    substr($partes[0] ?? '', 0, 1) .
    substr($partes[1] ?? $partes[0] ?? '', 0, 1)
);

// Datos del estudiante inyectados como JS para los fetch
$cbsJson = json_encode([
    'nombre'  => $_SESSION['nombre']  ?? '',
    'codigo'  => $_SESSION['codigo']  ?? '',
    'grado'   => $_SESSION['grado']   ?? '4to Básico',
    'seccion' => $_SESSION['seccion'] ?? 'A',
], JSON_HEX_TAG | JSON_HEX_QUOT);
?>
<!DOCTYPE html>
<html lang="es">
<head>
<meta charset="UTF-8"/>
<meta name="viewport" content="width=device-width,initial-scale=1"/>
<title>Panel Estudiantil – CBS</title>
<link rel="icon" type="image/png" href="logo_cbs.png">
<link href="https://fonts.googleapis.com/css2?family=Montserrat:wght@400;600;700;800;900&family=Lato:ital,wght@0,300;0,400;0,700;1,400&display=swap" rel="stylesheet"/>
<style>
:root {
  --navy:#0d2260;--navy-mid:#142d7a;--navy-dark:#091a4d;
  --gold:#f5a800;--gold-dark:#e09500;--gold-light:rgba(245,168,0,.10);
  --white:#fff;--surface:#f5f7fc;--surface2:#edf0f8;--border:#dde3f0;
  --text:#1a1e38;--text-mid:#4a5280;--text-soft:#8a90b0;
  --success:#18a05a;--warn:#e08c00;--danger:#d93b2b;--info:#1e5fc2;
  --sidebar-w:260px;--sidebar-w-c:72px;--top-h:64px;--radius:14px;
  --shadow:0 4px 24px rgba(13,34,96,.10);--shadow-lg:0 12px 48px rgba(13,34,96,.18);
  --trans:220ms cubic-bezier(.4,0,.2,1);
}
*,*::before,*::after{box-sizing:border-box;margin:0;padding:0}
html{scroll-behavior:smooth}
body{font-family:'Lato',sans-serif;background:var(--surface);color:var(--text);display:flex;min-height:100vh;overflow-x:hidden}
::-webkit-scrollbar{width:5px;height:5px}
::-webkit-scrollbar-track{background:transparent}
::-webkit-scrollbar-thumb{background:var(--border);border-radius:99px}
::-webkit-scrollbar-thumb:hover{background:var(--text-soft)}

/* ── SIDEBAR ── */
.sidebar{position:fixed;left:0;top:0;bottom:0;width:var(--sidebar-w);background:linear-gradient(175deg,var(--navy-dark) 0%,var(--navy-mid) 60%,#152f82 100%);display:flex;flex-direction:column;z-index:200;transition:width var(--trans);overflow:hidden;box-shadow:4px 0 32px rgba(9,26,77,.30)}
.sidebar::before{content:'';position:absolute;bottom:-80px;right:-80px;width:280px;height:280px;border-radius:50%;background:radial-gradient(circle,rgba(245,168,0,.10),transparent 70%);pointer-events:none}
body.sb-collapsed .sidebar{width:var(--sidebar-w-c)}
body.sb-collapsed .sb-label,body.sb-collapsed .sb-section-title,body.sb-collapsed .brand-text{opacity:0;pointer-events:none;width:0;overflow:hidden}
body.sb-collapsed .nav-item{justify-content:center}
body.sb-collapsed .nav-item .sb-label{display:none}
.sidebar-brand{display:flex;align-items:center;gap:12px;padding:0 18px;height:var(--top-h);border-bottom:1px solid rgba(255,255,255,.07);flex-shrink:0}
.brand-logo{width:38px;height:38px;flex-shrink:0;background:var(--gold);border-radius:10px;display:flex;align-items:center;justify-content:center;font-size:1.2rem;box-shadow:0 4px 14px rgba(245,168,0,.35)}
.brand-logo img{width:30px;height:30px;object-fit:contain}
.brand-text{font-family:'Montserrat',sans-serif;font-weight:900;font-size:.82rem;color:#fff;line-height:1.25;white-space:nowrap;transition:opacity var(--trans),width var(--trans)}
.brand-text span{color:var(--gold);display:block;font-size:.7rem;font-weight:600}
.sidebar-nav{flex:1;overflow-y:auto;overflow-x:hidden;padding:16px 0}
.sb-section-title{font-family:'Montserrat',sans-serif;font-size:.64rem;font-weight:700;letter-spacing:2px;text-transform:uppercase;color:rgba(255,255,255,.30);padding:12px 20px 6px;white-space:nowrap;transition:opacity var(--trans)}
.nav-item{display:flex;align-items:center;gap:13px;padding:11px 18px;cursor:pointer;border-radius:10px;margin:2px 10px;position:relative;transition:background var(--trans),transform var(--trans);text-decoration:none;white-space:nowrap}
.nav-item:hover{background:rgba(255,255,255,.07)}
.nav-item.active{background:linear-gradient(90deg,rgba(245,168,0,.20),rgba(245,168,0,.06));border-left:3px solid var(--gold)}
.nav-item.active .nav-icon{color:var(--gold)}
.nav-item.active .sb-label{color:#fff;font-weight:700}
.nav-icon{font-size:1.15rem;width:22px;text-align:center;color:rgba(255,255,255,.55);flex-shrink:0;transition:color var(--trans)}
.sb-label{font-family:'Montserrat',sans-serif;font-size:.83rem;font-weight:600;color:rgba(255,255,255,.70);transition:opacity var(--trans),color var(--trans)}
.sb-badge{margin-left:auto;background:var(--gold);color:var(--navy);font-family:'Montserrat',sans-serif;font-size:.62rem;font-weight:800;padding:2px 7px;border-radius:99px}
body.sb-collapsed .sb-badge{display:none}
.sidebar-footer{padding:14px 10px;border-top:1px solid rgba(255,255,255,.07);flex-shrink:0}
.sidebar-user{display:flex;align-items:center;gap:11px;padding:10px;border-radius:10px;background:rgba(255,255,255,.05);cursor:pointer;transition:background var(--trans)}
.sidebar-user:hover{background:rgba(255,255,255,.10)}
.user-avatar{width:36px;height:36px;flex-shrink:0;border-radius:50%;background:linear-gradient(135deg,var(--gold),var(--gold-dark));display:flex;align-items:center;justify-content:center;font-size:.95rem;font-weight:800;color:var(--navy);font-family:'Montserrat',sans-serif}
.user-info{overflow:hidden}
.user-name{font-family:'Montserrat',sans-serif;font-size:.82rem;font-weight:700;color:#fff;white-space:nowrap;overflow:hidden;text-overflow:ellipsis}
.user-role{font-size:.72rem;color:rgba(255,255,255,.45);white-space:nowrap}

/* ── TOPBAR ── */
.topbar{position:fixed;top:0;left:var(--sidebar-w);right:0;height:var(--top-h);background:rgba(255,255,255,.92);backdrop-filter:blur(12px);border-bottom:1px solid var(--border);display:flex;align-items:center;padding:0 28px;gap:16px;z-index:100;transition:left var(--trans);box-shadow:0 2px 16px rgba(13,34,96,.06)}
body.sb-collapsed .topbar{left:var(--sidebar-w-c)}
.toggle-btn{background:none;border:none;cursor:pointer;width:36px;height:36px;display:flex;align-items:center;justify-content:center;border-radius:8px;color:var(--text-mid);font-size:1.1rem;transition:background var(--trans);flex-shrink:0}
.toggle-btn:hover{background:var(--surface2)}
.topbar-title{font-family:'Montserrat',sans-serif;font-weight:800;font-size:1.05rem;color:var(--navy)}
.topbar-spacer{flex:1}
.search-box{display:flex;align-items:center;gap:8px;background:var(--surface);border:1px solid var(--border);border-radius:10px;padding:7px 14px;width:220px;transition:border-color var(--trans),box-shadow var(--trans)}
.search-box:focus-within{border-color:var(--gold);box-shadow:0 0 0 3px var(--gold-light)}
.search-box input{background:none;border:none;outline:none;font-size:.88rem;color:var(--text);width:100%;font-family:'Lato',sans-serif}
.search-box input::placeholder{color:var(--text-soft)}
.topbar-icon-btn{width:36px;height:36px;background:var(--surface);border:1px solid var(--border);border-radius:10px;display:flex;align-items:center;justify-content:center;cursor:pointer;font-size:1rem;position:relative;transition:background var(--trans)}
.topbar-icon-btn:hover{background:var(--surface2)}
.notif-dot{position:absolute;top:6px;right:6px;width:7px;height:7px;background:var(--gold);border-radius:50%;border:2px solid #fff}

/* ── MAIN ── */
.main{margin-left:var(--sidebar-w);margin-top:var(--top-h);padding:32px 28px;flex:1;min-height:calc(100vh - var(--top-h));transition:margin-left var(--trans)}
body.sb-collapsed .main{margin-left:var(--sidebar-w-c)}
.panel{display:none;animation:panelIn .35s ease both}
.panel.active{display:block}
@keyframes panelIn{from{opacity:0;transform:translateY(16px)}to{opacity:1;transform:translateY(0)}}
@keyframes fadeUp{from{opacity:0;transform:translateY(20px)}to{opacity:1;transform:translateY(0)}}

/* ── PAGE HEADER ── */
.page-header{display:flex;align-items:flex-start;justify-content:space-between;margin-bottom:28px;gap:16px}
.page-label{font-family:'Montserrat',sans-serif;font-size:.7rem;font-weight:700;letter-spacing:2.5px;text-transform:uppercase;color:var(--gold);margin-bottom:4px}
.page-title{font-family:'Montserrat',sans-serif;font-weight:900;font-size:1.65rem;color:var(--navy);line-height:1.1}
.page-desc{font-size:.9rem;color:var(--text-soft);margin-top:4px;font-style:italic}
.cards-row{display:grid;gap:18px}
.cols-4{grid-template-columns:repeat(4,1fr)}
.cols-3{grid-template-columns:repeat(3,1fr)}
.cols-2{grid-template-columns:1fr 1fr}
.cols-2-1{grid-template-columns:2fr 1fr}
.card{background:#fff;border-radius:var(--radius);border:1px solid var(--border);box-shadow:var(--shadow);overflow:hidden}
.card-header{padding:18px 22px 12px;display:flex;align-items:center;justify-content:space-between;border-bottom:1px solid var(--border)}
.card-header h3{font-family:'Montserrat',sans-serif;font-weight:800;font-size:.95rem;color:var(--navy)}
.card-body{padding:20px 22px}

/* ── STAT CARDS ── */
.stat-card{background:#fff;border-radius:var(--radius);border:1px solid var(--border);box-shadow:var(--shadow);padding:22px;display:flex;gap:16px;align-items:center;transition:transform var(--trans),box-shadow var(--trans);animation:fadeUp .5s ease both}
.stat-card:hover{transform:translateY(-4px);box-shadow:var(--shadow-lg)}
.stat-card:nth-child(1){animation-delay:.05s}.stat-card:nth-child(2){animation-delay:.10s}.stat-card:nth-child(3){animation-delay:.15s}.stat-card:nth-child(4){animation-delay:.20s}
.stat-icon{width:50px;height:50px;flex-shrink:0;border-radius:12px;display:flex;align-items:center;justify-content:center;font-size:1.4rem}
.stat-icon.navy{background:rgba(13,34,96,.08)}.stat-icon.gold{background:rgba(245,168,0,.13)}.stat-icon.green{background:rgba(24,160,90,.10)}.stat-icon.blue{background:rgba(30,95,194,.10)}
.stat-num{font-family:'Montserrat',sans-serif;font-weight:900;font-size:1.8rem;color:var(--navy);line-height:1}
.stat-num span{color:var(--gold)}
.stat-label{font-size:.8rem;color:var(--text-soft);font-family:'Montserrat',sans-serif;font-weight:600;text-transform:uppercase;letter-spacing:1px;margin-top:3px}

/* ── WELCOME BANNER ── */
.welcome-banner{background:linear-gradient(120deg,var(--navy) 0%,var(--navy-mid) 60%,#1a3a9c 100%);border-radius:var(--radius);padding:28px 32px;margin-bottom:22px;display:flex;align-items:center;justify-content:space-between;overflow:hidden;position:relative;animation:fadeUp .4s ease both}
.welcome-banner::before{content:'';position:absolute;top:-60px;right:100px;width:220px;height:220px;border-radius:50%;background:rgba(245,168,0,.08);pointer-events:none}
.welcome-banner::after{content:'🎓';position:absolute;right:32px;bottom:-8px;font-size:5.5rem;opacity:.12}
.welcome-text h2{font-family:'Montserrat',sans-serif;font-weight:900;font-size:1.4rem;color:#fff;margin-bottom:6px}
.welcome-text h2 span{color:var(--gold)}
.welcome-text p{color:rgba(255,255,255,.65);font-size:.9rem;font-style:italic}
.welcome-chips{display:flex;gap:10px;margin-top:14px;flex-wrap:wrap}
.w-chip{background:rgba(245,168,0,.14);border:1px solid rgba(245,168,0,.25);color:var(--gold);font-family:'Montserrat',sans-serif;font-size:.75rem;font-weight:700;padding:5px 12px;border-radius:99px}

/* ── NOTAS ── */
.grade-table{width:100%;border-collapse:collapse}
.grade-table thead tr{background:var(--surface)}
.grade-table th{font-family:'Montserrat',sans-serif;font-size:.72rem;font-weight:700;text-transform:uppercase;letter-spacing:1px;color:var(--text-soft);padding:11px 16px;text-align:left;border-bottom:2px solid var(--border)}
.grade-table td{padding:13px 16px;font-size:.9rem;border-bottom:1px solid var(--border);vertical-align:middle}
.grade-table tbody tr{transition:background var(--trans)}
.grade-table tbody tr:hover{background:var(--surface)}
.subject-dot{display:inline-block;width:8px;height:8px;border-radius:50%;margin-right:8px}
.grade-pill{display:inline-flex;align-items:center;justify-content:center;width:38px;height:26px;border-radius:8px;font-family:'Montserrat',sans-serif;font-size:.82rem;font-weight:800}
.grade-a{background:rgba(24,160,90,.12);color:#18a05a}.grade-b{background:rgba(30,95,194,.10);color:#1e5fc2}.grade-c{background:rgba(245,168,0,.12);color:#b07800}.grade-d{background:rgba(217,59,43,.10);color:#d93b2b}
.progress-bar{height:5px;background:var(--surface2);border-radius:99px;overflow:hidden;width:80px}
.progress-fill{height:100%;border-radius:99px;background:linear-gradient(90deg,var(--gold),var(--navy));transition:width .8s ease}
.bim-tabs{display:flex;gap:6px;margin-bottom:18px}
.bim-tab{padding:7px 16px;border-radius:8px;cursor:pointer;font-family:'Montserrat',sans-serif;font-size:.8rem;font-weight:700;background:var(--surface);border:1px solid var(--border);color:var(--text-mid);transition:all var(--trans)}
.bim-tab.active{background:var(--navy);color:#fff;border-color:var(--navy);box-shadow:0 4px 14px rgba(13,34,96,.20)}

/* ── HORARIO ── */
.schedule-grid{display:grid;grid-template-columns:70px repeat(5,1fr);gap:4px;font-size:.8rem}
.sched-header{background:var(--navy);color:rgba(255,255,255,.9);font-family:'Montserrat',sans-serif;font-weight:700;font-size:.72rem;padding:10px 6px;text-align:center;border-radius:8px;letter-spacing:.5px}
.sched-time{font-family:'Montserrat',sans-serif;font-size:.7rem;font-weight:700;color:var(--text-soft);display:flex;align-items:center;justify-content:center;text-align:center;padding:4px 2px}
.sched-cell{border-radius:8px;padding:9px 8px;font-size:.76rem;line-height:1.3;min-height:60px;display:flex;flex-direction:column;justify-content:center;transition:transform var(--trans),box-shadow var(--trans);cursor:default}
.sched-cell:hover{transform:scale(1.03);box-shadow:0 4px 16px rgba(0,0,0,.10)}
.sched-cell.empty{background:var(--surface)}
.sched-subject{font-family:'Montserrat',sans-serif;font-weight:800;font-size:.74rem}
.sched-teacher{font-size:.68rem;opacity:.75;margin-top:2px}.sched-room{font-size:.65rem;opacity:.55}
.s-mat{background:rgba(13,34,96,.08);border-left:3px solid #0d2260}
.s-len{background:rgba(30,95,194,.09);border-left:3px solid #1e5fc2}
.s-bio{background:rgba(24,160,90,.09);border-left:3px solid #18a05a}
.s-his{background:rgba(245,168,0,.12);border-left:3px solid #f5a800}
.s-fis{background:rgba(217,59,43,.08);border-left:3px solid #d93b2b}
.s-ing{background:rgba(147,51,234,.08);border-left:3px solid #9333ea}
.s-edu{background:rgba(234,88,12,.08);border-left:3px solid #ea580c}
.s-tec{background:rgba(2,132,199,.08);border-left:3px solid #0284c7}
.s-rel{background:rgba(245,168,0,.07);border-left:3px solid #d97706}

/* ── NOTICIAS ── */
.news-list{display:flex;flex-direction:column;gap:14px}
.news-item{display:flex;gap:16px;align-items:flex-start;padding:16px;background:#fff;border-radius:var(--radius);border:1px solid var(--border);box-shadow:var(--shadow);transition:transform var(--trans),box-shadow var(--trans);animation:fadeUp .4s ease both;cursor:pointer}
.news-item:hover{transform:translateY(-3px);box-shadow:var(--shadow-lg)}
.news-date-box{flex-shrink:0;width:50px;background:var(--navy);border-radius:10px;display:flex;flex-direction:column;align-items:center;justify-content:center;padding:8px 4px}
.news-day{font-family:'Montserrat',sans-serif;font-weight:900;font-size:1.3rem;color:var(--gold);line-height:1}
.news-month{font-family:'Montserrat',sans-serif;font-size:.6rem;font-weight:700;color:rgba(255,255,255,.6);text-transform:uppercase;letter-spacing:1px}
.news-content{flex:1}
.news-tag{display:inline-block;font-family:'Montserrat',sans-serif;font-size:.62rem;font-weight:700;letter-spacing:1px;text-transform:uppercase;padding:2px 9px;border-radius:99px;margin-bottom:6px}
.tag-deporte{background:rgba(234,88,12,.12);color:#ea580c}.tag-examen{background:rgba(217,59,43,.12);color:#d93b2b}
.tag-cultural{background:rgba(147,51,234,.10);color:#9333ea}.tag-general{background:rgba(13,34,96,.10);color:var(--navy)}.tag-entrega{background:rgba(24,160,90,.10);color:#18a05a}
.news-title{font-family:'Montserrat',sans-serif;font-weight:800;font-size:.92rem;color:var(--navy);margin-bottom:4px}
.news-desc{font-size:.84rem;color:var(--text-soft);line-height:1.5}
.news-meta{font-size:.75rem;color:var(--text-soft);margin-top:6px;display:flex;gap:12px;align-items:center}

/* ── INASISTENCIAS ── */
.form-grid{display:grid;gap:18px}
.form-row{display:grid;grid-template-columns:1fr 1fr;gap:16px}
.form-group{display:flex;flex-direction:column;gap:6px}
.form-label{font-family:'Montserrat',sans-serif;font-size:.72rem;font-weight:700;text-transform:uppercase;letter-spacing:1px;color:var(--text-mid)}
.form-input,.form-select,.form-textarea{width:100%;padding:11px 14px;border:1.5px solid var(--border);border-radius:10px;font-family:'Lato',sans-serif;font-size:.92rem;color:var(--text);background:#fff;outline:none;transition:border-color var(--trans),box-shadow var(--trans)}
.form-input:focus,.form-select:focus,.form-textarea:focus{border-color:var(--gold);box-shadow:0 0 0 3px var(--gold-light)}
.form-textarea{resize:vertical;min-height:90px}
.file-drop{border:2px dashed var(--border);border-radius:12px;padding:28px;text-align:center;cursor:pointer;transition:border-color var(--trans),background var(--trans)}
.file-drop:hover{border-color:var(--gold);background:var(--gold-light)}
.file-drop .drop-icon{font-size:2.2rem;margin-bottom:8px}
.file-drop p{font-size:.85rem;color:var(--text-soft)}.file-drop span{color:var(--gold);font-weight:700}
.btn-submit{background:linear-gradient(90deg,var(--gold),var(--gold-dark));color:var(--navy);font-family:'Montserrat',sans-serif;font-weight:800;font-size:.9rem;padding:12px 28px;border:none;border-radius:10px;cursor:pointer;box-shadow:0 6px 22px rgba(245,168,0,.30);transition:transform var(--trans),box-shadow var(--trans)}
.btn-submit:hover{transform:translateY(-2px);box-shadow:0 10px 30px rgba(245,168,0,.42)}
.btn-submit:disabled{opacity:.65;cursor:wait;transform:none}
.inas-item{display:flex;align-items:center;gap:14px;padding:13px 0;border-bottom:1px solid var(--border)}
.inas-item:last-child{border-bottom:none}
.inas-status{width:8px;height:8px;border-radius:50%;flex-shrink:0}
.inas-pending{background:#f5a800}.inas-approved{background:#18a05a}.inas-rejected{background:#d93b2b}
.inas-text{flex:1}
.inas-date{font-family:'Montserrat',sans-serif;font-size:.8rem;font-weight:700;color:var(--navy)}
.inas-reason{font-size:.8rem;color:var(--text-soft)}
.inas-badge{font-family:'Montserrat',sans-serif;font-size:.68rem;font-weight:700;padding:3px 10px;border-radius:99px}
.badge-pending{background:rgba(245,168,0,.12);color:#9a6d00}.badge-approved{background:rgba(24,160,90,.12);color:#18a05a}.badge-rejected{background:rgba(217,59,43,.10);color:#d93b2b}

/* ── BIBLIOTECA ── */
.book-grid{display:grid;grid-template-columns:repeat(auto-fill,minmax(180px,1fr));gap:18px}
.book-card{background:#fff;border:1px solid var(--border);border-radius:var(--radius);overflow:hidden;box-shadow:var(--shadow);transition:transform var(--trans),box-shadow var(--trans);animation:fadeUp .4s ease both;cursor:pointer}
.book-card:hover{transform:translateY(-6px);box-shadow:var(--shadow-lg)}
.book-cover{height:130px;display:flex;align-items:center;justify-content:center;font-size:3.5rem;position:relative;overflow:hidden}
.book-info{padding:12px 14px 14px}
.book-title{font-family:'Montserrat',sans-serif;font-weight:800;font-size:.83rem;color:var(--navy);line-height:1.3;margin-bottom:4px}
.book-sub{font-size:.75rem;color:var(--text-soft)}
.book-actions{display:flex;gap:8px;margin-top:10px}
.btn-book{flex:1;padding:7px 8px;border-radius:8px;font-family:'Montserrat',sans-serif;font-size:.72rem;font-weight:700;border:none;cursor:pointer;transition:all var(--trans)}
.btn-book.view{background:rgba(13,34,96,.08);color:var(--navy)}.btn-book.dl{background:linear-gradient(90deg,var(--gold),var(--gold-dark));color:var(--navy)}
.btn-book:hover{opacity:.85;transform:translateY(-1px)}

/* ── CLASSROOM ── */
.classroom-grid{display:grid;grid-template-columns:repeat(auto-fill,minmax(260px,1fr));gap:18px}
.class-card{background:#fff;border:1px solid var(--border);border-radius:var(--radius);overflow:hidden;box-shadow:var(--shadow);transition:transform var(--trans),box-shadow var(--trans);animation:fadeUp .4s ease both}
.class-card:hover{transform:translateY(-5px);box-shadow:var(--shadow-lg)}
.class-banner{height:64px;display:flex;align-items:center;padding:0 20px;gap:12px}
.class-banner-icon{font-size:1.8rem}
.class-banner-title{font-family:'Montserrat',sans-serif;font-weight:900;font-size:.9rem;color:#fff}
.class-body{padding:14px 18px 18px}
.class-teacher{font-size:.8rem;color:var(--text-soft);margin-bottom:10px;display:flex;align-items:center;gap:6px}
.class-links{display:flex;flex-direction:column;gap:6px}
.class-link{display:flex;align-items:center;gap:10px;padding:8px 12px;border-radius:8px;background:var(--surface);text-decoration:none;transition:background var(--trans),transform var(--trans);color:var(--text)}
.class-link:hover{background:var(--surface2);transform:translateX(4px)}
.class-link-text{font-family:'Montserrat',sans-serif;font-size:.78rem;font-weight:700;color:var(--navy)}
.class-link-sub{font-size:.7rem;color:var(--text-soft);margin-top:1px}
.class-link-arrow{margin-left:auto;color:var(--text-soft);font-size:.8rem}
.class-link.primary{background:linear-gradient(90deg,var(--gold),var(--gold-dark))}
.class-link.primary .class-link-text,.class-link.primary .class-link-arrow{color:var(--navy)}

/* ── MINI CALENDAR ── */
.cal-grid{display:grid;grid-template-columns:repeat(7,1fr);gap:2px}
.cal-day-name{font-family:'Montserrat',sans-serif;font-size:.62rem;font-weight:700;color:var(--text-soft);text-align:center;padding:4px 0}
.cal-day{font-family:'Montserrat',sans-serif;font-size:.78rem;font-weight:600;text-align:center;padding:5px 0;border-radius:6px;cursor:pointer;color:var(--text-mid);transition:background var(--trans),color var(--trans)}
.cal-day:hover{background:var(--surface2)}.cal-day.today{background:var(--navy);color:#fff;font-weight:800}
.cal-day.has-event{position:relative}.cal-day.has-event::after{content:'';position:absolute;bottom:2px;left:50%;transform:translateX(-50%);width:4px;height:4px;background:var(--gold);border-radius:50%}
.cal-day.other-month{color:var(--border)}

/* ── UTILITY ── */
.divider{height:1px;background:var(--border);margin:20px 0}
.flex-between{display:flex;align-items:center;justify-content:space-between}.flex-center{display:flex;align-items:center}.gap-8{gap:8px}.gap-12{gap:12px}
.chip-filter{padding:6px 14px;border-radius:99px;cursor:pointer;font-family:'Montserrat',sans-serif;font-size:.76rem;font-weight:700;background:var(--surface);border:1px solid var(--border);color:var(--text-mid);transition:all var(--trans)}
.chip-filter.active{background:var(--navy);color:#fff;border-color:var(--navy)}
.chip-filter:hover:not(.active){border-color:var(--navy);color:var(--navy)}
.section-sep{display:flex;align-items:center;gap:12px;margin:28px 0 18px}
.section-sep h4{font-family:'Montserrat',sans-serif;font-weight:800;font-size:.85rem;color:var(--navy);white-space:nowrap}
.section-sep::after{content:'';flex:1;height:1px;background:var(--border)}

/* Loading skeleton */
.skeleton{background:linear-gradient(90deg,var(--surface) 25%,var(--surface2) 50%,var(--surface) 75%);background-size:200% 100%;animation:shimmer 1.4s infinite;border-radius:6px}
@keyframes shimmer{0%{background-position:200% 0}100%{background-position:-200% 0}}
.sk-row{height:44px;margin-bottom:8px}

/* Toast */
.toast{position:fixed;bottom:28px;right:28px;background:var(--navy);color:#fff;font-family:'Montserrat',sans-serif;font-weight:700;font-size:.85rem;padding:13px 20px;border-radius:12px;box-shadow:0 8px 32px rgba(9,26,77,.35);opacity:0;transform:translateY(12px);transition:all .3s ease;z-index:999;pointer-events:none;display:flex;align-items:center;gap:10px}
.toast.show{opacity:1;transform:translateY(0);pointer-events:auto}
.toast.success .t-dot{background:#18a05a}.toast.error .t-dot{background:#d93b2b}
.t-dot{width:8px;height:8px;border-radius:50%;background:var(--gold);flex-shrink:0}

/* Mobile */
.sb-overlay{display:none;position:fixed;inset:0;background:rgba(9,26,77,.45);z-index:150;backdrop-filter:blur(2px)}
@media(max-width:900px){
  .sidebar{left:calc(-1 * var(--sidebar-w));transition:left var(--trans)}
  body.sb-open .sidebar{left:0}
  body.sb-open .sb-overlay{display:block}
  .topbar{left:0 !important}.main{margin-left:0 !important;padding:20px 16px}
  .cols-4{grid-template-columns:1fr 1fr}.cols-3{grid-template-columns:1fr}.cols-2{grid-template-columns:1fr}.form-row{grid-template-columns:1fr}
  .schedule-grid{grid-template-columns:60px repeat(5,1fr);font-size:.68rem}.search-box{display:none}
}
@media(max-width:480px){.cols-4{grid-template-columns:1fr 1fr}.book-grid{grid-template-columns:repeat(2,1fr)}.classroom-grid{grid-template-columns:1fr}}
</style>
</head>
<body>

<!-- Datos del estudiante para JS -->
<script>const CBS = <?= $cbsJson ?>;</script>

<!-- ════ SIDEBAR ════ -->
<aside class="sidebar" id="sidebar">
  <div class="sidebar-brand">
    <div class="brand-logo">
      <img src="logo_shalom.png" alt="CBS" onerror="this.textContent='🎓';this.style.fontSize='1.2rem'">
    </div>
    <div class="brand-text">Colegio Bautista<span>SHALOM · Panel CBS</span></div>
  </div>
  <nav class="sidebar-nav">
    <div class="sb-section-title">Principal</div>
    <div class="nav-item active" data-panel="inicio"      onclick="goTo('inicio',this)"><span class="nav-icon">🏠</span><span class="sb-label">Inicio</span></div>

    <div class="sb-section-title">Académico</div>
    <div class="nav-item" data-panel="notas"      onclick="goTo('notas',this)">     <span class="nav-icon">📊</span><span class="sb-label">Mis Notas</span></div>
    <div class="nav-item" data-panel="horario"    onclick="goTo('horario',this)">   <span class="nav-icon">🗓️</span><span class="sb-label">Horario</span></div>
    <div class="nav-item" data-panel="classroom"  onclick="goTo('classroom',this)"> <span class="nav-icon">💻</span><span class="sb-label">Classroom</span></div>

    <div class="sb-section-title">Recursos</div>
    <div class="nav-item" data-panel="biblioteca" onclick="goTo('biblioteca',this)"><span class="nav-icon">📚</span><span class="sb-label">Biblioteca</span></div>
    <div class="nav-item" data-panel="noticias"   onclick="goTo('noticias',this)">  <span class="nav-icon">📢</span><span class="sb-label">Noticias &amp; Eventos</span><span class="sb-badge" id="badge-noticias">…</span></div>

    <div class="sb-section-title">Gestión</div>
    <div class="nav-item" data-panel="inasistencias" onclick="goTo('inasistencias',this)"><span class="nav-icon">📋</span><span class="sb-label">Inasistencias</span></div>
  </nav>
  <div class="sidebar-footer">
    <div class="sidebar-user">
      <div class="user-avatar"><?= $iniciales ?></div>
      <div class="user-info brand-text">
        <div class="user-name"><?= $nombre ?></div>
        <div class="user-role"><?= $grado ?> · Sección <?= $seccion ?></div>
      </div>
    </div>
  </div>
</aside>

<div class="sb-overlay" id="sbOverlay" onclick="closeSb()"></div>

<!-- ════ TOPBAR ════ -->
<header class="topbar">
  <button class="toggle-btn" onclick="toggleSb()" aria-label="Menú">
    <svg width="18" height="14" viewBox="0 0 18 14" fill="none">
      <rect width="18" height="2" rx="1" fill="currentColor"/>
      <rect y="6" width="12" height="2" rx="1" fill="currentColor"/>
      <rect y="12" width="18" height="2" rx="1" fill="currentColor"/>
    </svg>
  </button>
  <div class="topbar-title" id="topbarTitle">Inicio</div>
  <div class="topbar-spacer"></div>
  <div class="topbar-icon-btn" title="Cerrar sesión" onclick="cerrarSesion()">🚪</div>
</header>

<!-- ════ MAIN ════ -->
<main class="main">

<!-- ─── INICIO ─── -->
<div class="panel active" id="panel-inicio">
  <div class="page-header">
    <div>
      <div class="page-label">Panel Estudiantil</div>
      <h1 class="page-title">¡Buen día, <?= $nombre ?>! 👋</h1>
      <p class="page-desc" id="inicio-desc">Cargando información…</p>
    </div>
  </div>
  <div class="welcome-banner">
    <div class="welcome-text">
      <h2>Bienvenid@ a tu <span>Panel CBS</span></h2>
      <p><?= $grado ?> · Sección <?= $seccion ?> · Ciclo 2026</p>
      <div class="welcome-chips" id="welcome-chips">
        <span class="w-chip" style="background:rgba(255,255,255,.08);border-color:rgba(255,255,255,.15);color:rgba(255,255,255,.5)">Cargando eventos…</span>
      </div>
    </div>
  </div>
  
  <div class="cards-row cols-4" style="margin-bottom:22px" id="stat-cards">
    <div class="stat-card"><div class="stat-icon gold">📊</div><div><div class="stat-num" id="stat-promedio">—</div><div class="stat-label">Promedio General</div></div></div>
    <div class="stat-card"><div class="stat-icon navy">📋</div><div><div class="stat-num" id="stat-inas">—</div><div class="stat-label">Inasistencias</div></div></div>
    <div class="stat-card"><div class="stat-icon green">✅</div><div><div class="stat-num" id="stat-aprobadas">—</div><div class="stat-label">Inasistencias Aprobadas</div></div></div>
    <div class="stat-card"><div class="stat-icon blue">📅</div><div><div class="stat-num" id="stat-eventos">—</div><div class="stat-label">Eventos este mes</div></div></div>
  </div>
  <div class="cards-row cols-2-1">
    <div class="card">
      <div class="card-header"><h3>Próximos Eventos</h3><span style="font-size:.8rem;color:var(--text-soft);cursor:pointer" onclick="goTo('noticias',document.querySelector('[data-panel=noticias]'))">Ver todos →</span></div>
      <div class="card-body"><div class="news-list" id="inicio-eventos"><div class="sk-row skeleton"></div><div class="sk-row skeleton"></div><div class="sk-row skeleton"></div></div></div>
    </div>
    <div class="card">
      <div class="card-header"><h3>📅 Abril 2026</h3></div>
      <div class="card-body">
        <div class="cal-grid" id="cal-grid">
          <div class="cal-day-name">L</div><div class="cal-day-name">M</div><div class="cal-day-name">X</div><div class="cal-day-name">J</div><div class="cal-day-name">V</div><div class="cal-day-name">S</div><div class="cal-day-name">D</div>
          <div class="cal-day other-month">31</div>
          <div class="cal-day">1</div><div class="cal-day">2</div><div class="cal-day">3</div><div class="cal-day today">4</div><div class="cal-day">5</div><div class="cal-day">6</div>
          <div class="cal-day has-event">7</div><div class="cal-day">8</div><div class="cal-day has-event">9</div><div class="cal-day has-event">10</div><div class="cal-day">11</div><div class="cal-day">12</div><div class="cal-day">13</div>
          <div class="cal-day">14</div><div class="cal-day has-event">15</div><div class="cal-day">16</div><div class="cal-day">17</div><div class="cal-day">18</div><div class="cal-day">19</div><div class="cal-day">20</div>
          <div class="cal-day">21</div><div class="cal-day has-event">22</div><div class="cal-day">23</div><div class="cal-day">24</div><div class="cal-day">25</div><div class="cal-day">26</div><div class="cal-day">27</div>
          <div class="cal-day">28</div><div class="cal-day">29</div><div class="cal-day">30</div><div class="cal-day other-month">1</div><div class="cal-day other-month">2</div><div class="cal-day other-month">3</div><div class="cal-day other-month">4</div>
        </div>
        <div style="margin-top:14px;display:flex;align-items:center;gap:6px;font-size:.75rem;color:var(--text-soft)"><span style="width:8px;height:8px;border-radius:50%;background:var(--gold);display:inline-block"></span>Eventos programados</div>
      </div>
    </div>
  </div>
</div>

<!-- ─── NOTAS ─── -->
<div class="panel" id="panel-notas">
  <div class="page-header">
    <div>
      <div class="page-label">Académico</div>
      <h1 class="page-title">Mis Notas</h1>
      <p class="page-desc">Ciclo Escolar 2026 · <?= $grado ?> · Sección <?= $seccion ?></p>
    </div>
  </div>
  <div class="bim-tabs">
    <div class="bim-tab active" onclick="loadNotas(1,this)">1er Bimestre</div>
    <div class="bim-tab" onclick="loadNotas(2,this)">2do Bimestre</div>
    <div class="bim-tab" onclick="loadNotas(3,this)">3er Bimestre</div>
    <div class="bim-tab" onclick="loadNotas(4,this)">4to Bimestre</div>
  </div>
  <div class="card">
    <div class="card-header">
      <h3 id="notas-title">Calificaciones – 1er Bimestre</h3>
      <div style="font-family:'Montserrat',sans-serif;font-size:.8rem;color:var(--text-soft)">Promedio: <strong id="notas-promedio" style="color:var(--navy)">—</strong></div>
    </div>
    <div class="card-body" style="padding:0">
      <table class="grade-table">
        <thead>
          <tr>
            <th>Materia</th><th>Z1</th><th>Z2</th><th>Z3</th><th>Examen</th><th>Final</th><th>Progreso</th><th>Estado</th>
          </tr>
        </thead>
        <tbody id="notas-tbody">
          <tr><td colspan="8" style="padding:28px;text-align:center;color:var(--text-soft)">Cargando notas…</td></tr>
        </tbody>
      </table>
    </div>
  </div>
</div>

<!-- ─── HORARIO ─── -->
<div class="panel" id="panel-horario">
  <div class="page-header">
    <div>
      <div class="page-label">Académico</div>
      <h1 class="page-title">Horario de Clases</h1>
      <p class="page-desc"><?= $grado ?> · Sección <?= $seccion ?> · Ciclo 2026</p>
    </div>
  </div>
  <div class="card">
    <div class="card-header"><h3>Semana Actual</h3></div>
    <div class="card-body" style="overflow-x:auto;padding:16px">
      <div id="schedule-container">
        <div class="sk-row skeleton" style="height:60px;margin-bottom:6px"></div>
        <div class="sk-row skeleton" style="height:60px;margin-bottom:6px"></div>
        <div class="sk-row skeleton" style="height:60px"></div>
      </div>
    </div>
  </div>
</div>

<!-- ─── NOTICIAS ─── -->
<div class="panel" id="panel-noticias">
  <div class="page-header">
    <div>
      <div class="page-label">Recursos</div>
      <h1 class="page-title">Noticias &amp; Eventos</h1>
      <p class="page-desc">Mantente al día con todo lo que pasa en el CBS.</p>
    </div>
  </div>
  <div class="flex-center gap-8" style="margin-bottom:22px;flex-wrap:wrap;">
    <div class="chip-filter active" onclick="filterNews('all',this)">Todos</div>
    <div class="chip-filter" onclick="filterNews('examen',this)">📝 Exámenes</div>
    <div class="chip-filter" onclick="filterNews('deporte',this)">⚽ Deportes</div>
    <div class="chip-filter" onclick="filterNews('cultural',this)">🎭 Cultural</div>
    <div class="chip-filter" onclick="filterNews('general',this)">📌 General</div>
  </div>
  <div class="news-list" id="news-list">
    <div class="sk-row skeleton"></div>
    <div class="sk-row skeleton"></div>
    <div class="sk-row skeleton"></div>
  </div>
</div>

<!-- ─── INASISTENCIAS ─── -->
<div class="panel" id="panel-inasistencias">
  <div class="page-header">
    <div>
      <div class="page-label">Gestión</div>
      <h1 class="page-title">Registrar Inasistencia</h1>
      <p class="page-desc">Notifica tu ausencia con anticipación para tramitar justificante.</p>
    </div>
  </div>
  <div class="cards-row cols-2">
    <div class="card">
      <div class="card-header"><h3>📋 Nueva Solicitud</h3></div>
      <div class="card-body">
        <form id="form-inasistencia" onsubmit="return false;" enctype="multipart/form-data">
          <div class="form-grid">
            <div class="form-row">
              <div class="form-group">
                <label class="form-label">Nombre Completo</label>
                <input type="text" class="form-input" value="<?= $nombre ?>" readonly style="background:var(--surface)">
              </div>
              <div class="form-group">
                <label class="form-label">Código Estudiantil</label>
                <input type="text" class="form-input" value="<?= $codigo ?>" readonly style="background:var(--surface)">
              </div>
            </div>
            <div class="form-row">
              <div class="form-group">
                <label class="form-label">Fecha de Ausencia</label>
                <input type="date" class="form-input" id="inas-fecha" name="fecha" required>
              </div>
              <div class="form-group">
                <label class="form-label">Tipo de Inasistencia</label>
                <select class="form-select" id="inas-tipo" name="tipo" required>
                  <option>Enfermedad</option>
                  <option>Cita médica</option>
                  <option>Emergencia familiar</option>
                  <option>Evento deportivo</option>
                  <option>Otro</option>
                </select>
              </div>
            </div>
            <div class="form-group">
              <label class="form-label">Materias Afectadas</label>
              <select class="form-select" id="inas-materias" name="materias" required>
                <option value="">— Selecciona las materias —</option>
                <option>Matemática</option>
                <option>Comunicación y Lenguaje</option>
                <option>Ciencias Naturales</option>
                <option>Estudios Sociales</option>
                <option>Física</option>
                <option>Inglés</option>
                <option>Ed. Física</option>
                <option>Religión</option>
                <option>Todas las materias del día</option>
              </select>
            </div>
            <div class="form-group">
              <label class="form-label">Motivo Detallado</label>
              <textarea class="form-textarea" id="inas-motivo" name="motivo" placeholder="Describe el motivo de tu ausencia..." required></textarea>
            </div>
            <div class="file-drop" onclick="document.getElementById('justificante-input').click()">
              <div class="drop-icon">📎</div>
              <p><span>Sube un justificante</span> o haz clic aquí</p>
              <p style="font-size:.75rem;margin-top:4px;color:var(--text-soft)" id="file-name-label">PDF, JPG o PNG · Máx 5 MB</p>
              <input type="file" id="justificante-input" name="justificante" accept=".pdf,.jpg,.jpeg,.png" style="display:none" onchange="document.getElementById('file-name-label').textContent = this.files[0]?.name || 'PDF, JPG o PNG · Máx 5 MB'">
            </div>
            <button class="btn-submit" id="btn-inas-submit" onclick="submitInasistencia()">Enviar Solicitud →</button>
          </div>
        </form>
      </div>
    </div>
    <div class="card">
      <div class="card-header">
        <h3>📜 Historial 2026</h3>
        <div id="inas-conteo-label" style="font-size:.8rem;color:var(--text-soft)">Cargando…</div>
      </div>
      <div class="card-body">
        <div id="inas-historial"><div class="sk-row skeleton"></div><div class="sk-row skeleton"></div></div>
        <div class="divider"></div>
        <div style="display:grid;grid-template-columns:1fr 1fr 1fr;gap:12px;text-align:center">
          <div style="background:rgba(24,160,90,.07);border-radius:10px;padding:14px 8px"><div id="cnt-aprobadas" style="font-family:'Montserrat',sans-serif;font-weight:900;font-size:1.5rem;color:#18a05a">—</div><div style="font-size:.72rem;color:var(--text-soft);font-family:'Montserrat',sans-serif;font-weight:700;text-transform:uppercase">Aprobadas</div></div>
          <div style="background:rgba(245,168,0,.08);border-radius:10px;padding:14px 8px"><div id="cnt-pendientes" style="font-family:'Montserrat',sans-serif;font-weight:900;font-size:1.5rem;color:#9a6d00">—</div><div style="font-size:.72rem;color:var(--text-soft);font-family:'Montserrat',sans-serif;font-weight:700;text-transform:uppercase">Pendientes</div></div>
          <div style="background:rgba(217,59,43,.07);border-radius:10px;padding:14px 8px"><div id="cnt-rechazadas" style="font-family:'Montserrat',sans-serif;font-weight:900;font-size:1.5rem;color:#d93b2b">—</div><div style="font-size:.72rem;color:var(--text-soft);font-family:'Montserrat',sans-serif;font-weight:700;text-transform:uppercase">Rechazadas</div></div>
        </div>
      </div>
    </div>
  </div>
</div>

<!-- ─── BIBLIOTECA ─── -->
<div class="panel" id="panel-biblioteca">
  <div class="page-header"><div><div class="page-label">Recursos</div><h1 class="page-title">Biblioteca Digital</h1><p class="page-desc">Accede y descarga tus libros de texto del ciclo escolar.</p></div></div>
  <div class="book-grid">
    <div class="book-card"><div class="book-cover" style="background:rgba(13,34,96,.07)">📐</div><div class="book-info"><div class="book-title">Matemática 4to Básico</div><div class="book-sub">Ciclo 2026</div><div class="book-actions"><button class="btn-book view" onclick="showToast('Abriendo libro…','success')">👁 Ver</button><button class="btn-book dl" onclick="showToast('Descargando PDF…','success')">⬇ PDF</button></div></div></div>
    <div class="book-card"><div class="book-cover" style="background:rgba(30,95,194,.07)">📖</div><div class="book-info"><div class="book-title">Comunicación y Lenguaje L1</div><div class="book-sub">Ciclo 2026</div><div class="book-actions"><button class="btn-book view" onclick="showToast('Abriendo libro…','success')">👁 Ver</button><button class="btn-book dl" onclick="showToast('Descargando PDF…','success')">⬇ PDF</button></div></div></div>
    <div class="book-card"><div class="book-cover" style="background:rgba(24,160,90,.07)">📒</div><div class="book-info"><div class="book-title">Contabilidad</div><div class="book-sub">Ciclo 2026</div><div class="book-actions"><button class="btn-book view" onclick="showToast('Abriendo libro…','success');window.open('biblioteca/test.pdf', '_blank')">👁 Ver</button><button class="btn-book dl" onclick="showToast('Descargando PDF…','success'); window.location.href='biblioteca/test.pdf'">⬇ PDF</button></div></div></div>
    <div class="book-card"><div class="book-cover" style="background:rgba(245,168,0,.08)">🌎</div><div class="book-info"><div class="book-title">Estudios Sociales</div><div class="book-sub">Ciclo 2026</div><div class="book-actions"><button class="btn-book view" onclick="showToast('Abriendo libro…','success')">👁 Ver</button><button class="btn-book dl" onclick="showToast('Descargando PDF…','success')">⬇ PDF</button></div></div></div>
    <div class="book-card"><div class="book-cover" style="background:rgba(217,59,43,.07)">⚡</div><div class="book-info"><div class="book-title">Física Fundamental</div><div class="book-sub">Ciclo 2026</div><div class="book-actions"><button class="btn-book view" onclick="showToast('Abriendo libro…','success')">👁 Ver</button><button class="btn-book dl" onclick="showToast('Descargando PDF…','success')">⬇ PDF</button></div></div></div>
    <div class="book-card"><div class="book-cover" style="background:rgba(147,51,234,.07)">🇬🇧</div><div class="book-info"><div class="book-title">English Connections 4</div><div class="book-sub">Ciclo 2026</div><div class="book-actions"><button class="btn-book view" onclick="showToast('Abriendo libro…','success')">👁 Ver</button><button class="btn-book dl" onclick="showToast('Descargando PDF…','success')">⬇ PDF</button></div></div></div>
    <div class="book-card"><div class="book-cover" style="background:rgba(2,132,199,.07)">💻</div><div class="book-info"><div class="book-title">Tecnología e Informática</div><div class="book-sub">Ciclo 2026</div><div class="book-actions"><button class="btn-book view" onclick="showToast('Abriendo libro…','success')">👁 Ver</button><button class="btn-book dl" onclick="showToast('Descargando PDF…','success')">⬇ PDF</button></div></div></div>
    <div class="book-card"><div class="book-cover" style="background:rgba(245,168,0,.07)">✝️</div><div class="book-info"><div class="book-title">Educación Cristiana</div><div class="book-sub">Ciclo 2026</div><div class="book-actions"><button class="btn-book view" onclick="showToast('Abriendo libro…','success')">👁 Ver</button><button class="btn-book dl" onclick="showToast('Descargando PDF…','success')">⬇ PDF</button></div></div></div>
  </div>
</div>

<!-- ─── CLASSROOM ─── -->
<div class="panel" id="panel-classroom">
  <div class="page-header"><div><div class="page-label">Académico</div><h1 class="page-title">Google Classroom</h1><p class="page-desc">Accede directamente a tus clases y plataformas digitales.</p></div></div>
  <div class="classroom-grid">
    <div class="class-card"><div class="class-banner" style="background:linear-gradient(135deg,#0d2260,#1e3799)"><span class="class-banner-icon">📐</span><div class="class-banner-title">Matemática</div></div><div class="class-body"><div class="class-teacher">👨‍🏫 Prof. Roberto Ramírez</div><div class="class-links"><a href="#" class="class-link primary" onclick="showToast('Abriendo Classroom…','success');return false"><span>🎓</span><div><div class="class-link-text">Abrir en Classroom</div><div class="class-link-sub">Ver tareas y anuncios</div></div><span class="class-link-arrow">→</span></a><a href="#" class="class-link" onclick="showToast('Iniciando Meet…','success');return false"><span>📹</span><div><div class="class-link-text">Google Meet</div><div class="class-link-sub">Clase virtual</div></div><span class="class-link-arrow">→</span></a></div></div></div>
    <div class="class-card"><div class="class-banner" style="background:linear-gradient(135deg,#1e5fc2,#1043a0)"><span class="class-banner-icon">📖</span><div class="class-banner-title">Lenguaje</div></div><div class="class-body"><div class="class-teacher">👩‍🏫 Prof. Andrea López</div><div class="class-links"><a href="#" class="class-link primary" onclick="showToast('Abriendo Classroom…','success');return false"><span>🎓</span><div><div class="class-link-text">Abrir en Classroom</div><div class="class-link-sub">Ver tareas y anuncios</div></div><span class="class-link-arrow">→</span></a><a href="#" class="class-link" onclick="showToast('Iniciando Meet…','success');return false"><span>📹</span><div><div class="class-link-text">Google Meet</div><div class="class-link-sub">Clase virtual</div></div><span class="class-link-arrow">→</span></a></div></div></div>
    <div class="class-card"><div class="class-banner" style="background:linear-gradient(135deg,#18a05a,#0d6e3d)"><span class="class-banner-icon">🔬</span><div class="class-banner-title">Ciencias Naturales</div></div><div class="class-body"><div class="class-teacher">👨‍🏫 Prof. Carlos Torres</div><div class="class-links"><a href="#" class="class-link primary" onclick="showToast('Abriendo Classroom…','success');return false"><span>🎓</span><div><div class="class-link-text">Abrir en Classroom</div><div class="class-link-sub">Ver tareas y anuncios</div></div><span class="class-link-arrow">→</span></a><a href="#" class="class-link" onclick="showToast('Iniciando Meet…','success');return false"><span>📹</span><div><div class="class-link-text">Google Meet</div><div class="class-link-sub">Clase virtual</div></div><span class="class-link-arrow">→</span></a></div></div></div>
    <div class="class-card"><div class="class-banner" style="background:linear-gradient(135deg,#9333ea,#5b1f96)"><span class="class-banner-icon">🇬🇧</span><div class="class-banner-title">Inglés</div></div><div class="class-body"><div class="class-teacher">👩‍🏫 Miss Sarah Johnson</div><div class="class-links"><a href="#" class="class-link primary" onclick="showToast('Abriendo Classroom…','success');return false"><span>🎓</span><div><div class="class-link-text">Abrir en Classroom</div><div class="class-link-sub">Ver tareas y anuncios</div></div><span class="class-link-arrow">→</span></a><a href="#" class="class-link" onclick="showToast('Abriendo Duolingo…','success');return false"><span>🦜</span><div><div class="class-link-text">Duolingo for Schools</div><div class="class-link-sub">Práctica diaria</div></div><span class="class-link-arrow">→</span></a></div></div></div>
    <div class="class-card"><div class="class-banner" style="background:linear-gradient(135deg,#d93b2b,#8f1e14)"><span class="class-banner-icon">⚡</span><div class="class-banner-title">Física</div></div><div class="class-body"><div class="class-teacher">👨‍🏫 Prof. Luis Vasquez</div><div class="class-links"><a href="#" class="class-link primary" onclick="showToast('Abriendo Classroom…','success');return false"><span>🎓</span><div><div class="class-link-text">Abrir en Classroom</div><div class="class-link-sub">Ver tareas y anuncios</div></div><span class="class-link-arrow">→</span></a><a href="#" class="class-link" onclick="showToast('Abriendo PhET…','success');return false"><span>🧪</span><div><div class="class-link-text">PhET Simulaciones</div><div class="class-link-sub">Laboratorio virtual</div></div><span class="class-link-arrow">→</span></a></div></div></div>
    <div class="class-card"><div class="class-banner" style="background:linear-gradient(135deg,#f5a800,#c47e00)"><span class="class-banner-icon">🌎</span><div class="class-banner-title">Estudios Sociales</div></div><div class="class-body"><div class="class-teacher">👩‍🏫 Prof. Rosa Cifuentes</div><div class="class-links"><a href="#" class="class-link primary" onclick="showToast('Abriendo Classroom…','success');return false"><span>🎓</span><div><div class="class-link-text">Abrir en Classroom</div><div class="class-link-sub">Ver tareas y anuncios</div></div><span class="class-link-arrow">→</span></a><a href="#" class="class-link" onclick="showToast('Abriendo Drive…','success');return false"><span>📁</span><div><div class="class-link-text">Material del Curso</div><div class="class-link-sub">Drive compartido</div></div><span class="class-link-arrow">→</span></a></div></div></div>
  </div>
</div>

</main>
<div class="toast" id="toast"><div class="t-dot"></div><span id="toast-msg"></span></div>

<script>
// ══ CONSTANTES ══════════════════════════════════════════
const PANEL_TITLES = {
  inicio:'Inicio', notas:'Mis Notas', horario:'Horario de Clases',
  noticias:'Noticias & Eventos', inasistencias:'Inasistencias',
  biblioteca:'Biblioteca Digital', classroom:'Google Classroom'
};

// Colores de materias para la tabla de notas
const SUBJECT_COLORS = {
  'matemática':           { dot:'#0d2260', bar:'linear-gradient(90deg,#0d2260,#1e3799)' },
  'comunicación y lenguaje':{ dot:'#1e5fc2', bar:'linear-gradient(90deg,#1e5fc2,#0d3880)' },
  'ciencias naturales':   { dot:'#18a05a', bar:'linear-gradient(90deg,#18a05a,#0d5c38)' },
  'estudios sociales':    { dot:'#f5a800', bar:'linear-gradient(90deg,#f5a800,#c47e00)' },
  'física':               { dot:'#d93b2b', bar:'linear-gradient(90deg,#d93b2b,#8f1e14)' },
  'inglés':               { dot:'#9333ea', bar:'linear-gradient(90deg,#9333ea,#5b1f96)' },
  'ed. física':           { dot:'#ea580c', bar:'linear-gradient(90deg,#ea580c,#7c2b05)' },
  'tecnología':           { dot:'#0284c7', bar:'linear-gradient(90deg,#0284c7,#01578a)' },
  'religión':             { dot:'#d97706', bar:'linear-gradient(90deg,#f5a800,#0d2260)' },
};

// Clases CSS del horario por materia (normalizada)
const SCHED_CLASS = {
  'matemática':'s-mat','lenguaje':'s-len','comunicación y lenguaje':'s-len',
  'ciencias naturales':'s-bio','ciencias':'s-bio','estudios sociales':'s-his',
  'física':'s-fis','inglés':'s-ing','ed. física':'s-edu','educación física':'s-edu',
  'tecnología':'s-tec','informática':'s-tec','religión':'s-rel'
};

const DAYS  = ['Lunes','Martes','Miércoles','Jueves','Viernes'];
const SLOTS = [
  { s:'07:00', e:'08:00', lbl:'7:00\n8:00' },
  { s:'08:00', e:'09:00', lbl:'8:00\n9:00' },
  { s:'09:00', e:'09:20', lbl:'9:00\n9:20',  receso:true },
  { s:'09:20', e:'10:20', lbl:'9:20\n10:20' },
  { s:'10:20', e:'11:20', lbl:'10:20\n11:20' },
  { s:'11:20', e:'12:00', lbl:'11:20\n12:00', almuerzo:true },
  { s:'12:00', e:'12:45', lbl:'12:00\n12:45' },
];

// ══ CACHE DE DATOS ════════════════════════════════════════
let cachedEventos   = null;
let cachedHorario   = null;
let currentBimestre = 1;

// ══ NAVEGACIÓN ═══════════════════════════════════════════
function goTo(id, el) {
  document.querySelectorAll('.panel').forEach(p => p.classList.remove('active'));
  document.querySelectorAll('.nav-item').forEach(n => n.classList.remove('active'));
  const panel = document.getElementById('panel-' + id);
  if (panel) panel.classList.add('active');
  if (el) el.classList.add('active');
  document.getElementById('topbarTitle').textContent = PANEL_TITLES[id] || id;
  if (window.innerWidth < 900) closeSb();
  window.scrollTo({ top: 0, behavior: 'smooth' });

  // Carga bajo demanda
  if (id === 'noticias' || id === 'inicio')    loadEventos();
  if (id === 'horario')                         loadHorario();
  if (id === 'notas')                           loadNotas(currentBimestre);
  if (id === 'inasistencias')                   loadInasistencias();
}

// ══ SIDEBAR ══════════════════════════════════════════════
function toggleSb() {
  if (window.innerWidth < 900) { document.body.classList.toggle('sb-open'); }
  else { document.body.classList.toggle('sb-collapsed'); }
}
function closeSb() { document.body.classList.remove('sb-open'); }

// ══ TOAST ════════════════════════════════════════════════
let toastTimer;
function showToast(msg, type = 'success') {
  clearTimeout(toastTimer);
  const t = document.getElementById('toast');
  document.getElementById('toast-msg').textContent = msg;
  t.className = 'toast show ' + type;
  toastTimer = setTimeout(() => { t.className = 'toast'; }, 3200);
}

// ══ HELPERS ══════════════════════════════════════════════
function subjectColor(name) {
  return SUBJECT_COLORS[name.toLowerCase()] || { dot:'#8a90b0', bar:'linear-gradient(90deg,var(--gold),var(--navy))' };
}
function gradeClass(n) {
  if (n >= 90) return 'grade-a';
  if (n >= 80) return 'grade-b';
  if (n >= 70) return 'grade-c';
  return 'grade-d';
}
function gradeStatus(n) {
  if (n >= 90) return { label:'Excelente', bg:'rgba(24,160,90,.12)', color:'#18a05a' };
  if (n >= 70) return { label:'Aprobado',  bg:'rgba(24,160,90,.12)', color:'#18a05a' };
  return              { label:'Reprobado', bg:'rgba(217,59,43,.10)', color:'#d93b2b' };
}
function tagClass(cat) {
  const m = { examen:'tag-examen', deporte:'tag-deporte', cultural:'tag-cultural', general:'tag-general', entrega:'tag-entrega' };
  return m[cat] || 'tag-general';
}
function tagLabel(cat) {
  const m = { examen:'Examen', deporte:'Deportes', cultural:'Cultural', general:'General', entrega:'Entrega' };
  return m[cat] || cat;
}
function schedClass(materia) {
  return SCHED_CLASS[materia.toLowerCase()] || 's-his';
}

// ══ CARGAR EVENTOS ════════════════════════════════════════
async function loadEventos() {
  if (cachedEventos) { renderEventos(cachedEventos); return; }
  try {
    const res  = await fetch('get_eventos.php');
    const json = await res.json();
    if (!json.success) throw new Error(json.error);
    cachedEventos = json.data;
    document.getElementById('badge-noticias').textContent = json.data.length;
    document.getElementById('stat-eventos').textContent   = json.data.length;
    renderEventos(json.data);
  } catch (e) {
    console.error(e);
    showToast('Error al cargar eventos: ' + e.message, 'error');
  }
}

function renderEventos(data) {
  // Panel Noticias completo
  const list = document.getElementById('news-list');
  if (list) {
    list.innerHTML = data.length
      ? data.map(ev => `
          <div class="news-item" data-tag="${ev.categoria}">
            <div class="news-date-box">
              <div class="news-day">${ev.dia}</div>
              <div class="news-month">${ev.mes}</div>
            </div>
            <div class="news-content">
              <span class="news-tag ${tagClass(ev.categoria)}">${tagLabel(ev.categoria)}</span>
              <div class="news-title">${ev.titulo}</div>
              <div class="news-desc">${ev.descripcion || ''}</div>
              <div class="news-meta">
                ${ev.hora_fmt ? `<span>⏰ ${ev.hora_fmt}</span>` : ''}
                ${ev.lugar ? `<span>📍 ${ev.lugar}</span>` : ''}
              </div>
            </div>
          </div>`).join('')
      : '<p style="color:var(--text-soft);text-align:center;padding:24px">No hay eventos registrados.</p>';
  }

  // Marcar días en el calendario
  const grid = document.getElementById('cal-grid');
  if (grid) {
    // Limpiar marcas previas
    grid.querySelectorAll('.cal-day').forEach(day => day.classList.remove('has-event'));

    data.forEach(ev => {
      const dia = parseInt(ev.dia, 10);
      const dayCell = Array.from(grid.querySelectorAll('.cal-day'))
        .find(cell => parseInt(cell.textContent, 10) === dia && !cell.classList.contains('other-month'));
      if (dayCell) {
        dayCell.classList.add('has-event');
        // Opcional: tooltip con título
        dayCell.title = ev.titulo;
      }
    });
  }

  // Panel Inicio: 3 primeros
  const ini = document.getElementById('inicio-eventos');
  if (ini) {
    const proximos = data.slice(0, 3);
    ini.innerHTML = proximos.map(ev => `
        <div class="news-item" style="padding:12px 14px">
          <div class="news-date-box"><div class="news-day">${ev.dia}</div><div class="news-month">${ev.mes}</div></div>
          <div class="news-content">
            <span class="news-tag ${tagClass(ev.categoria)}">${tagLabel(ev.categoria)}</span>
            <div class="news-title">${ev.titulo}</div>
            <div class="news-desc">${(ev.descripcion || '').substring(0, 80)}${ev.descripcion?.length > 80 ? '…' : ''}</div>
          </div>
        </div>`).join('');

    // Chips del banner de bienvenida
    const chips = document.getElementById('welcome-chips');
    if (chips) {
      chips.innerHTML = proximos.slice(0, 2).map(ev =>
        `<span class="w-chip">📅 ${ev.titulo} – ${ev.dia} ${ev.mes}</span>`
      ).join('');
    }
  }

  // Fecha de hoy
  const now = new Date();
  document.getElementById('inicio-desc').textContent =
    now.toLocaleDateString('es-GT', { weekday:'long', year:'numeric', month:'long', day:'numeric' }) +
    ' · <?= $grado ?> · Sección <?= $seccion ?>';
}

// ══ FILTRO DE NOTICIAS ════════════════════════════════════
function filterNews(tag, el) {
  document.querySelectorAll('#panel-noticias .chip-filter').forEach(c => c.classList.remove('active'));
  el.classList.add('active');
  document.querySelectorAll('#news-list .news-item').forEach(item => {
    item.style.display = (tag === 'all' || item.dataset.tag === tag) ? 'flex' : 'none';
  });
}

// ══ CARGAR HORARIO ════════════════════════════════════════
async function loadHorario() {
  if (cachedHorario) { renderHorario(cachedHorario); return; }
  try {
    const res  = await fetch(`get_horario.php?grado=${encodeURIComponent(CBS.grado)}&seccion=${encodeURIComponent(CBS.seccion)}`);
    const json = await res.json();
    if (!json.success) throw new Error(json.error);
    cachedHorario = json.data;
    renderHorario(json.data);
  } catch (e) {
    console.error(e);
    document.getElementById('schedule-container').innerHTML =
      `<p style="color:var(--danger);padding:20px">Error al cargar horario: ${e.message}</p>`;
  }
}

function renderHorario(data) {
  // Indexar: { "Lunes_07:00": { materia, profesor, aula } }
  const idx = {};
  data.forEach(r => { idx[`${r.dia}_${r.hora_inicio}`] = r; });

  let html = '<div class="schedule-grid">';
  // Headers
  html += '<div></div>';
  DAYS.forEach(d => { html += `<div class="sched-header">${d}</div>`; });

  SLOTS.forEach(slot => {
    html += `<div class="sched-time">${slot.lbl.replace('\n','<br>')}</div>`;
    if (slot.receso) {
      DAYS.forEach(() => { html += `<div class="sched-cell empty" style="text-align:center;color:var(--text-soft);font-size:.75rem">☕ Receso</div>`; });
    } else if (slot.almuerzo) {
      DAYS.forEach(() => { html += `<div class="sched-cell empty" style="text-align:center;color:var(--text-soft);font-size:.75rem">🍽 Almuerzo</div>`; });
    } else {
      DAYS.forEach(dia => {
        const r = idx[`${dia}_${slot.s}`];
        if (r) {
          html += `<div class="sched-cell ${schedClass(r.materia)}">
            <div class="sched-subject">${r.materia}</div>
            <div class="sched-teacher">${r.profesor || ''}</div>
            <div class="sched-room">${r.aula || ''}</div>
          </div>`;
        } else {
          html += `<div class="sched-cell empty"></div>`;
        }
      });
    }
  });
  html += '</div>';
  document.getElementById('schedule-container').innerHTML = html;
}

// ══ CARGAR NOTAS ══════════════════════════════════════════
async function loadNotas(bim, tabEl) {
  currentBimestre = bim;
  if (tabEl) {
    document.querySelectorAll('.bim-tab').forEach(t => t.classList.remove('active'));
    tabEl.classList.add('active');
  }
  const titles = ['','1er Bimestre','2do Bimestre','3er Bimestre','4to Bimestre'];
  document.getElementById('notas-title').textContent = `Calificaciones – ${titles[bim]}`;
  document.getElementById('notas-tbody').innerHTML =
    '<tr><td colspan="8" style="padding:28px;text-align:center"><div class="skeleton sk-row" style="width:100%;height:20px"></div></td></tr>';

  try {
    const res  = await fetch(`get_notas.php?bimestre=${bim}&ciclo=2026`);
    const json = await res.json();
    if (!json.success) throw new Error(json.error);

    document.getElementById('notas-promedio').textContent = json.promedio;
    document.getElementById('stat-promedio').innerHTML = `${json.promedio}<span>%</span>`;

    if (!json.data.length) {
      document.getElementById('notas-tbody').innerHTML =
        '<tr><td colspan="8" style="padding:28px;text-align:center;color:var(--text-soft)">No hay notas registradas para este bimestre.</td></tr>';
      return;
    }

    const rows = json.data.map(r => {
      const col   = subjectColor(r.materia);
      const nota  = parseFloat(r.nota_final) || 0;
      const gc    = gradeClass(nota);
      const gs    = gradeStatus(nota);
      const examen = r.examen !== null ? r.examen : '—';
      return `<tr>
        <td><span class="subject-dot" style="background:${col.dot}"></span><strong>${r.materia}</strong></td>
        <td>${r.zona1_raw ?? '—'}</td>
        <td>${r.zona2_raw ?? '—'}</td>
        <td>${r.zona3_raw ?? '—'}</td>
        <td>${examen}</td>
        <td><span class="grade-pill ${gc}">${nota}</span></td>
        <td><div class="progress-bar"><div class="progress-fill" style="width:${nota}%;background:${col.bar}"></div></div></td>
        <td><span class="sb-badge" style="background:${gs.bg};color:${gs.color}">${gs.label}</span></td>
      </tr>`;
    }).join('');
    document.getElementById('notas-tbody').innerHTML = rows;

  } catch (e) {
    console.error(e);
    document.getElementById('notas-tbody').innerHTML =
      `<tr><td colspan="8" style="padding:20px;text-align:center;color:var(--danger)">Error: ${e.message}</td></tr>`;
  }
}

// ══ CARGAR INASISTENCIAS ══════════════════════════════════
async function loadInasistencias() {
  const hist = document.getElementById('inas-historial');
  hist.innerHTML = '<div class="sk-row skeleton"></div><div class="sk-row skeleton"></div>';
  try {
    const res  = await fetch('get_inasistencias.php');
    const json = await res.json();
    if (!json.success) throw new Error(json.error);

    // Contadores
    document.getElementById('cnt-aprobadas').textContent  = json.conteo.aprobada;
    document.getElementById('cnt-pendientes').textContent = json.conteo.pendiente;
    document.getElementById('cnt-rechazadas').textContent = json.conteo.rechazada;
    document.getElementById('stat-inas').textContent      = json.data.length;
    document.getElementById('stat-aprobadas').textContent = json.conteo.aprobada;
    const total = json.data.length;
    document.getElementById('inas-conteo-label').textContent = `${total} inasistencia${total !== 1 ? 's' : ''} registrada${total !== 1 ? 's' : ''}`;

    if (!json.data.length) {
      hist.innerHTML = '<p style="color:var(--text-soft);padding:14px 0;font-size:.88rem">No hay inasistencias registradas.</p>';
      return;
    }

    hist.innerHTML = json.data.map(r => {
      const estadoMap = {
        aprobada:  { cls:'inas-approved', badge:'badge-approved', label:'Aprobada' },
        pendiente: { cls:'inas-pending',  badge:'badge-pending',  label:'Pendiente' },
        rechazada: { cls:'inas-rejected', badge:'badge-rejected', label:'Rechazada' },
      };
      const em = estadoMap[r.estado] || estadoMap.pendiente;
      return `<div class="inas-item">
        <div class="inas-status ${em.cls}"></div>
        <div class="inas-text">
          <div class="inas-date">${r.dia_semana?.trim()}, ${r.fecha_fmt} · ${r.tipo}</div>
          <div class="inas-reason">${r.motivo?.substring(0,80) ?? ''}${(r.motivo?.length > 80) ? '…' : ''}</div>
        </div>
        <span class="inas-badge ${em.badge}">${em.label}</span>
      </div>`;
    }).join('');

  } catch (e) {
    hist.innerHTML = `<p style="color:var(--danger);font-size:.85rem">Error al cargar historial: ${e.message}</p>`;
  }
}

// ══ ENVIAR INASISTENCIA ═══════════════════════════════════
async function submitInasistencia() {
  const fecha    = document.getElementById('inas-fecha').value;
  const tipo     = document.getElementById('inas-tipo').value;
  const materias = document.getElementById('inas-materias').value;
  const motivo   = document.getElementById('inas-motivo').value.trim();

  if (!fecha)    { showToast('Selecciona la fecha de ausencia', 'error'); return; }
  if (!materias) { showToast('Selecciona la(s) materia(s) afectada(s)', 'error'); return; }
  if (!motivo)   { showToast('Escribe el motivo de la ausencia', 'error'); return; }

  const btn = document.getElementById('btn-inas-submit');
  btn.disabled    = true;
  btn.textContent = 'Enviando…';

  const formData = new FormData(document.getElementById('form-inasistencia'));
  // Asegurar que los campos se manden correctamente
  formData.set('fecha',    fecha);
  formData.set('tipo',     tipo);
  formData.set('materias', materias);
  formData.set('motivo',   motivo);

  try {
    const res  = await fetch('registrar_inasistencia.php', { method:'POST', credentials:'same-origin', body: formData });
    const json = await res.json();

    if (json.success) {
      showToast('✅ ' + json.mensaje, 'success');
      // Limpiar formulario
      document.getElementById('inas-fecha').value    = new Date().toISOString().split('T')[0];
      document.getElementById('inas-motivo').value   = '';
      document.getElementById('inas-materias').value = '';
      document.getElementById('file-name-label').textContent = 'PDF, JPG o PNG · Máx 5 MB';
      document.getElementById('justificante-input').value = '';
      // Recargar historial
      cachedInasistencias = null;
      loadInasistencias();
    } else {
      showToast(json.error || 'Error al enviar la solicitud', 'error');
    }
  } catch (e) {
    showToast('Error de red: ' + e.message, 'error');
  } finally {
    btn.disabled    = false;
    btn.textContent = 'Enviar Solicitud →';
  }
}

// ══ CERRAR SESIÓN ═════════════════════════════════════════
function cerrarSesion() {
  if (confirm('¿Deseas cerrar sesión?')) {
    window.location.href = 'logout.php';
  }
}

// ══ INIT ═════════════════════════════════════════════════
window.addEventListener('load', () => {
  // Fecha por defecto en el formulario
  const hoy = new Date().toISOString().split('T')[0];
  const fechaEl = document.getElementById('inas-fecha');
  if (fechaEl) fechaEl.value = hoy;

  // Cargar datos del panel inicial
  loadEventos();
  loadInasistencias();
  loadNotas(1);
});
</script>
</body>
</html>