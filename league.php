<?php
// ══════════════════════════════════════════════════════════
//  CONFIG — change these to match your phpMyAdmin setup
// ══════════════════════════════════════════════════════════
$DB_HOST = 'localhost';
$DB_NAME = 'IM_db';   // ← your database name
$DB_USER = 'root';
$DB_PASS = '';             // ← your password if any

// ══════════════════════════════════════════════════════════
//  API MODE — runs when ?action=... is in the request
// ══════════════════════════════════════════════════════════
if (isset($_REQUEST['action'])) {
    header('Content-Type: application/json');
    $conn = new mysqli($DB_HOST, $DB_USER, $DB_PASS, $DB_NAME);
    if ($conn->connect_error) {
        http_response_code(500);
        echo json_encode(['error' => 'DB connection failed: ' . $conn->connect_error]);
        exit;
    }
    $conn->set_charset('utf8mb4');

    $action = $_REQUEST['action'];

    function respond($data) { echo json_encode($data); exit; }
    function fail($msg, $code = 400) {
        http_response_code($code);
        echo json_encode(['error' => $msg]);
        exit;
    }
    function body() {
        return json_decode(file_get_contents('php://input'), true) ?? [];
    }

    // ── People ───────────────────────────────────────────

    if ($action === 'get_people') {
        $r = $conn->query("SELECT P_ID, Name FROM people ORDER BY Name");
        $rows = []; while ($row = $r->fetch_assoc()) $rows[] = $row;
        respond($rows);
    }
    if ($action === 'add_person') {
        $d = body(); $name = trim($d['name'] ?? '');
        if (!$name) fail('Name is required');
        $mid = $conn->query("SELECT COALESCE(MAX(P_ID),0)+1 AS n FROM people")->fetch_assoc()['n'];
        $s = $conn->prepare("INSERT INTO people (P_ID,Name) VALUES (?,?)");
        $s->bind_param('is', $mid, $name);
        $s->execute() ? respond(['success'=>true,'id'=>$mid]) : fail($conn->error);
    }
    if ($action === 'update_person') {
        $d = body(); $id = (int)($d['id']??0); $name = trim($d['name']??'');
        if (!$id || !$name) fail('ID and Name required');
        $s = $conn->prepare("UPDATE people SET Name=? WHERE P_ID=?");
        $s->bind_param('si', $name, $id);
        $s->execute() ? respond(['success'=>true]) : fail($conn->error);
    }
    if ($action === 'delete_person') {
        $d = body(); $id = (int)($d['id']??0);
        if (!$id) fail('ID required');
        $s = $conn->prepare("DELETE FROM people WHERE P_ID=?");
        $s->bind_param('i', $id);
        $s->execute() ? respond(['success'=>true]) : fail($conn->error);
    }

    // ── Dropdowns ────────────────────────────────────────

    if ($action === 'get_sports') {
        $r = $conn->query("SELECT S_ID, Sport_Type FROM sport ORDER BY Sport_Type");
        $rows = []; while ($row = $r->fetch_assoc()) $rows[] = $row;
        respond($rows);
    }
    if ($action === 'get_locations') {
        $r = $conn->query("SELECT L_ID, Location FROM location ORDER BY Location");
        $rows = []; while ($row = $r->fetch_assoc()) $rows[] = $row;
        respond($rows);
    }

    // ── Teams ─────────────────────────────────────────────

    if ($action === 'get_teams') {
        $r = $conn->query(
            "SELECT t.T_ID, t.S_ID, t.C_ID, s.Sport_Type, p.Name AS Captain_Name
             FROM team t
             JOIN sport s ON t.S_ID=s.S_ID
             LEFT JOIN people p ON t.C_ID=p.P_ID
             ORDER BY t.T_ID"
        );
        $rows = []; while ($row = $r->fetch_assoc()) $rows[] = $row;
        respond($rows);
    }
    if ($action === 'add_team') {
        $d = body(); $sid = (int)($d['s_id']??0);
        $cid = isset($d['c_id']) && $d['c_id'] !== '' ? (int)$d['c_id'] : null;
        if (!$sid) fail('Sport is required');
        $mid = $conn->query("SELECT COALESCE(MAX(T_ID),0)+1 AS n FROM team")->fetch_assoc()['n'];
        $s = $conn->prepare("INSERT INTO team (T_ID,S_ID,C_ID) VALUES (?,?,?)");
        $s->bind_param('iii', $mid, $sid, $cid);
        $s->execute() ? respond(['success'=>true,'id'=>$mid]) : fail($conn->error);
    }
    if ($action === 'update_team') {
        $d = body(); $id = (int)($d['id']??0); $sid = (int)($d['s_id']??0);
        $cid = isset($d['c_id']) && $d['c_id'] !== '' ? (int)$d['c_id'] : null;
        if (!$id || !$sid) fail('ID and Sport required');
        $s = $conn->prepare("UPDATE team SET S_ID=?, C_ID=? WHERE T_ID=?");
        $s->bind_param('iii', $sid, $cid, $id);
        $s->execute() ? respond(['success'=>true]) : fail($conn->error);
    }
    if ($action === 'delete_team') {
        $d = body(); $id = (int)($d['id']??0);
        if (!$id) fail('ID required');
        $s = $conn->prepare("DELETE FROM team WHERE T_ID=?");
        $s->bind_param('i', $id);
        $s->execute() ? respond(['success'=>true]) : fail($conn->error);
    }

    // ── Roster ───────────────────────────────────────────

    if ($action === 'get_roster') {
        $tid = (int)($_GET['tid']??0);
        if (!$tid) fail('Team ID required');
        $s = $conn->prepare(
            "SELECT tr.P_ID, p.Name FROM team_roster tr
             JOIN people p ON tr.P_ID=p.P_ID
             WHERE tr.T_ID=? ORDER BY p.Name"
        );
        $s->bind_param('i', $tid); $s->execute();
        $rows = []; $res = $s->get_result();
        while ($row = $res->fetch_assoc()) $rows[] = $row;
        respond($rows);
    }
    if ($action === 'add_roster') {
        $d = body(); $tid = (int)($d['t_id']??0); $pid = (int)($d['p_id']??0);
        if (!$tid || !$pid) fail('Team and Person required');
        $s = $conn->prepare("INSERT IGNORE INTO team_roster (T_ID,P_ID) VALUES (?,?)");
        $s->bind_param('ii', $tid, $pid);
        $s->execute() ? respond(['success'=>true]) : fail($conn->error);
    }
    if ($action === 'remove_roster') {
        $d = body(); $tid = (int)($d['t_id']??0); $pid = (int)($d['p_id']??0);
        if (!$tid || !$pid) fail('Team and Person required');
        $s = $conn->prepare("DELETE FROM team_roster WHERE T_ID=? AND P_ID=?");
        $s->bind_param('ii', $tid, $pid);
        $s->execute() ? respond(['success'=>true]) : fail($conn->error);
    }

    // ── Schedule ─────────────────────────────────────────

    if ($action === 'get_schedule') {
        $r = $conn->query(
            "SELECT sc.G_ID, sc.S_ID, sp.Sport_Type,
                    sc.Location AS L_ID, lo.Location AS Location_Name,
                    sc.T1_ID, sc.T2_ID
             FROM scheduling sc
             JOIN sport sp ON sc.S_ID=sp.S_ID
             JOIN location lo ON sc.Location=lo.L_ID
             ORDER BY sc.G_ID DESC"
        );
        $rows = []; while ($row = $r->fetch_assoc()) $rows[] = $row;
        respond($rows);
    }
    if ($action === 'add_game') {
        $d = body();
        $sid = (int)($d['s_id']??0); $loc = (int)($d['loc']??0);
        $t1  = (int)($d['t1_id']??0); $t2  = (int)($d['t2_id']??0);
        if (!$sid||!$loc||!$t1||!$t2) fail('All fields required');
        if ($t1===$t2) fail('Teams must be different');
        $mid = $conn->query("SELECT COALESCE(MAX(G_ID),0)+1 AS n FROM scheduling")->fetch_assoc()['n'];
        $s = $conn->prepare("INSERT INTO scheduling (G_ID,S_ID,Location,T1_ID,T2_ID) VALUES (?,?,?,?,?)");
        $s->bind_param('iiiii', $mid, $sid, $loc, $t1, $t2);
        $s->execute() ? respond(['success'=>true,'id'=>$mid]) : fail($conn->error);
    }
    if ($action === 'update_game') {
        $d = body();
        $id = (int)($d['id']??0); $sid = (int)($d['s_id']??0);
        $loc = (int)($d['loc']??0); $t1 = (int)($d['t1_id']??0); $t2 = (int)($d['t2_id']??0);
        if (!$id||!$sid||!$loc||!$t1||!$t2) fail('All fields required');
        $s = $conn->prepare("UPDATE scheduling SET S_ID=?,Location=?,T1_ID=?,T2_ID=? WHERE G_ID=?");
        $s->bind_param('iiiii', $sid, $loc, $t1, $t2, $id);
        $s->execute() ? respond(['success'=>true]) : fail($conn->error);
    }
    if ($action === 'delete_game') {
        $d = body(); $id = (int)($d['id']??0);
        if (!$id) fail('ID required');
        $s = $conn->prepare("DELETE FROM scheduling WHERE G_ID=?");
        $s->bind_param('i', $id);
        $s->execute() ? respond(['success'=>true]) : fail($conn->error);
    }

    // ── Analytics ─────────────────────────────────────────

    if ($action === 'analytics_top_scorers') {
        $r = $conn->query(
            "SELECT p.Name, SUM(sc.Points) AS Total_Points, COUNT(DISTINCT sc.G_ID) AS Games_Played
             FROM score sc JOIN people p ON sc.P_ID=p.P_ID
             GROUP BY sc.P_ID, p.Name ORDER BY Total_Points DESC LIMIT 20"
        );
        $rows = []; while ($row = $r->fetch_assoc()) $rows[] = $row;
        respond($rows);
    }
    if ($action === 'analytics_team_records') {
        $r = $conn->query(
            "SELECT t.T_ID, sp.Sport_Type,
                    COUNT(CASE WHEN h.Winner=t.T_ID THEN 1 END) AS Wins,
                    COUNT(CASE WHEN h.Winner!=t.T_ID THEN 1 END) AS Losses
             FROM team t JOIN sport sp ON t.S_ID=sp.S_ID
             LEFT JOIN history h ON h.T1_ID=t.T_ID OR h.T2_ID=t.T_ID
             GROUP BY t.T_ID, sp.Sport_Type ORDER BY Wins DESC"
        );
        $rows = []; if($r) { while($row=$r->fetch_assoc()) $rows[]=$row; } else $rows=['error'=>$conn->error];
        respond($rows);
    }
    if ($action === 'analytics_games_per_sport') {
        $r = $conn->query(
            "SELECT sp.Sport_Type, COUNT(sc.G_ID) AS Games_Scheduled
             FROM sport sp LEFT JOIN scheduling sc ON sc.S_ID=sp.S_ID
             GROUP BY sp.S_ID, sp.Sport_Type ORDER BY Games_Scheduled DESC"
        );
        $rows = []; while ($row = $r->fetch_assoc()) $rows[] = $row;
        respond($rows);
    }
    if ($action === 'analytics_roster_sizes') {
        $r = $conn->query(
            "SELECT t.T_ID, sp.Sport_Type, COUNT(tr.P_ID) AS Roster_Size, p.Name AS Captain
             FROM team t JOIN sport sp ON t.S_ID=sp.S_ID
             LEFT JOIN team_roster tr ON t.T_ID=tr.T_ID
             LEFT JOIN people p ON t.C_ID=p.P_ID
             GROUP BY t.T_ID, sp.Sport_Type, p.Name ORDER BY Roster_Size DESC"
        );
        $rows = []; while ($row = $r->fetch_assoc()) $rows[] = $row;
        respond($rows);
    }
    if ($action === 'analytics_location_usage') {
        $r = $conn->query(
            "SELECT lo.Location, COUNT(sc.G_ID) AS Times_Used,
                    GROUP_CONCAT(DISTINCT sp.Sport_Type ORDER BY sp.Sport_Type SEPARATOR ', ') AS Sports_Played
             FROM location lo
             LEFT JOIN scheduling sc ON sc.Location=lo.L_ID
             LEFT JOIN sport sp ON sc.S_ID=sp.S_ID
             GROUP BY lo.L_ID, lo.Location ORDER BY Times_Used DESC"
        );
        $rows = []; while ($row = $r->fetch_assoc()) $rows[] = $row;
        respond($rows);
    }

    fail("Unknown action: $action", 404);
}
// ══════════════════════════════════════════════════════════
//  HTML MODE — serves the UI when no ?action is present
// ══════════════════════════════════════════════════════════
?><!DOCTYPE html>
<html lang="en">
<head>
<meta charset="UTF-8">
<meta name="viewport" content="width=device-width, initial-scale=1.0">
<title>League Manager</title>
<link rel="preconnect" href="https://fonts.googleapis.com">
<link href="https://fonts.googleapis.com/css2?family=Barlow+Condensed:wght@400;600;700&family=DM+Mono:wght@400;500&display=swap" rel="stylesheet">
<style>
:root {
  --bg:       #0e1117;
  --surface:  #161b25;
  --surface2: #1e2535;
  --border:   #2a3348;
  --accent:   #38bdf8;
  --accent2:  #818cf8;
  --green:    #4ade80;
  --red:      #f87171;
  --yellow:   #fbbf24;
  --text:     #e2e8f0;
  --muted:    #64748b;
  --font-ui:  'Barlow Condensed', sans-serif;
  --font-data:'DM Mono', monospace;
  --r: 6px;
}
* { box-sizing: border-box; margin: 0; padding: 0; }
body { background: var(--bg); color: var(--text); font-family: var(--font-ui); font-size: 15px; display: flex; height: 100vh; overflow: hidden; }

/* Sidebar */
#sidebar { width: 220px; min-width: 220px; background: var(--surface); border-right: 1px solid var(--border); display: flex; flex-direction: column; }
.sidebar-logo { padding: 24px 20px 20px; border-bottom: 1px solid var(--border); }
.sidebar-logo h1 { font-size: 22px; font-weight: 700; letter-spacing: 2px; text-transform: uppercase; color: var(--accent); }
.sidebar-logo p { font-family: var(--font-data); font-size: 11px; color: var(--muted); margin-top: 2px; }
.nav-section { padding: 16px 12px 8px; font-size: 10px; font-weight: 600; letter-spacing: 2px; text-transform: uppercase; color: var(--muted); }
.nav-item { display: flex; align-items: center; gap: 10px; padding: 10px 16px; cursor: pointer; border-radius: var(--r); margin: 2px 8px; color: var(--muted); font-size: 15px; font-weight: 600; letter-spacing: 0.5px; transition: all 0.15s; user-select: none; }
.nav-item:hover { background: var(--surface2); color: var(--text); }
.nav-item.active { background: rgba(56,189,248,0.12); color: var(--accent); border-left: 3px solid var(--accent); margin-left: 8px; padding-left: 13px; }
.nav-icon { font-size: 18px; width: 22px; text-align: center; }

/* Main */
#main { flex: 1; display: flex; flex-direction: column; overflow: hidden; }
.topbar { padding: 20px 28px; border-bottom: 1px solid var(--border); display: flex; align-items: center; justify-content: space-between; background: var(--surface); }
.topbar h2 { font-size: 26px; font-weight: 700; letter-spacing: 1px; text-transform: uppercase; }
.topbar-sub { font-family: var(--font-data); font-size: 11px; color: var(--muted); margin-top: 2px; }
.content-area { flex: 1; overflow-y: auto; padding: 24px 28px; }
.content-area::-webkit-scrollbar { width: 6px; }
.content-area::-webkit-scrollbar-track { background: var(--bg); }
.content-area::-webkit-scrollbar-thumb { background: var(--border); border-radius: 3px; }

/* Sections */
.section { display: none; }
.section.active { display: block; }

/* Buttons */
.btn { font-family: var(--font-ui); font-weight: 600; font-size: 13px; letter-spacing: 0.5px; padding: 8px 16px; border: none; border-radius: var(--r); cursor: pointer; transition: all 0.15s; display: inline-flex; align-items: center; gap: 6px; }
.btn-primary { background: var(--accent); color: #0e1117; }
.btn-primary:hover { background: #7dd3fc; }
.btn-danger { background: transparent; color: var(--red); border: 1px solid var(--red); }
.btn-danger:hover { background: rgba(248,113,113,0.1); }
.btn-edit { background: transparent; color: var(--yellow); border: 1px solid var(--yellow); }
.btn-edit:hover { background: rgba(251,191,36,0.1); }
.btn-sm { padding: 5px 10px; font-size: 12px; }
.btn-green { background: var(--green); color: #0e1117; }
.btn-green:hover { background: #86efac; }
.btn-muted { background: var(--surface2); color: var(--muted); border: 1px solid var(--border); }
.btn-muted:hover { color: var(--text); }
.btn-roster { background: rgba(129,140,248,0.12); color: var(--accent2); border: 1px solid var(--accent2); }
.btn-roster:hover { background: rgba(129,140,248,0.22); }

/* Tables */
.table-wrap { background: var(--surface); border: 1px solid var(--border); border-radius: var(--r); overflow: hidden; }
.table-header { padding: 16px 20px; display: flex; align-items: center; justify-content: space-between; border-bottom: 1px solid var(--border); background: var(--surface2); }
.table-header h3 { font-size: 16px; font-weight: 700; letter-spacing: 1px; text-transform: uppercase; }
table { width: 100%; border-collapse: collapse; }
thead th { padding: 10px 16px; text-align: left; font-family: var(--font-data); font-size: 11px; font-weight: 500; text-transform: uppercase; letter-spacing: 1px; color: var(--muted); border-bottom: 1px solid var(--border); background: var(--surface2); }
tbody tr { border-bottom: 1px solid var(--border); transition: background 0.1s; }
tbody tr:last-child { border-bottom: none; }
tbody tr:hover { background: var(--surface2); }
tbody td { padding: 11px 16px; font-family: var(--font-data); font-size: 13px; }
.td-actions { display: flex; gap: 6px; }
.id-badge { font-family: var(--font-data); font-size: 11px; background: var(--surface2); border: 1px solid var(--border); padding: 2px 7px; border-radius: 4px; color: var(--muted); }
.empty-state { padding: 48px; text-align: center; color: var(--muted); font-family: var(--font-data); font-size: 13px; }

/* Modal */
#modal-overlay { display: none; position: fixed; inset: 0; background: rgba(0,0,0,0.7); z-index: 100; align-items: center; justify-content: center; }
#modal-overlay.open { display: flex; }
.modal { background: var(--surface); border: 1px solid var(--border); border-radius: 10px; width: 440px; max-width: 95vw; animation: slideUp 0.2s ease; }
@keyframes slideUp { from { opacity:0; transform:translateY(20px); } to { opacity:1; transform:translateY(0); } }
.modal-head { padding: 20px 24px; border-bottom: 1px solid var(--border); display: flex; justify-content: space-between; align-items: center; }
.modal-head h3 { font-size: 18px; font-weight: 700; letter-spacing: 0.5px; text-transform: uppercase; }
.modal-close { background: none; border: none; color: var(--muted); font-size: 22px; cursor: pointer; padding: 0 4px; }
.modal-close:hover { color: var(--text); }
.modal-body { padding: 24px; }
.modal-footer { padding: 16px 24px; border-top: 1px solid var(--border); display: flex; justify-content: flex-end; gap: 10px; }

/* Form */
.form-group { margin-bottom: 18px; }
.form-group label { display: block; font-size: 11px; font-weight: 600; letter-spacing: 1px; text-transform: uppercase; color: var(--muted); margin-bottom: 6px; }
.form-control { width: 100%; background: var(--bg); border: 1px solid var(--border); border-radius: var(--r); padding: 10px 12px; color: var(--text); font-family: var(--font-data); font-size: 13px; outline: none; transition: border-color 0.15s; }
.form-control:focus { border-color: var(--accent); }
select.form-control option { background: var(--surface); }

/* Analytics */
.analytics-grid { display: grid; grid-template-columns: 1fr 1fr; gap: 20px; }
@media (max-width: 900px) { .analytics-grid { grid-template-columns: 1fr; } }
.stat-card { background: var(--surface); border: 1px solid var(--border); border-radius: var(--r); overflow: hidden; }
.stat-card-head { padding: 14px 18px; border-bottom: 1px solid var(--border); background: var(--surface2); display: flex; align-items: center; justify-content: space-between; }
.stat-card-head h4 { font-size: 13px; font-weight: 700; letter-spacing: 1px; text-transform: uppercase; }
.stat-row { display: flex; align-items: center; justify-content: space-between; padding: 10px 18px; border-bottom: 1px solid var(--border); font-family: var(--font-data); font-size: 12px; }
.stat-row:last-child { border-bottom: none; }
.stat-row:hover { background: var(--surface2); }
.stat-badge { padding: 2px 8px; border-radius: 4px; font-size: 11px; font-weight: 600; }
.badge-green  { background: rgba(74,222,128,0.12); color: var(--green); }
.badge-blue   { background: rgba(56,189,248,0.12); color: var(--accent); }
.badge-yellow { background: rgba(251,191,36,0.12); color: var(--yellow); }
.badge-purple { background: rgba(129,140,248,0.12); color: var(--accent2); }
.refresh-btn { font-size: 16px; background: none; border: none; color: var(--muted); cursor: pointer; transition: color 0.15s; }
.refresh-btn:hover { color: var(--accent); }

/* Roster panel */
.roster-panel { margin-top: 20px; background: var(--surface); border: 1px solid var(--accent2); border-radius: var(--r); overflow: hidden; }
.roster-panel .table-header { background: rgba(129,140,248,0.08); border-bottom-color: var(--accent2); }

/* Toast */
#toast { position: fixed; bottom: 24px; right: 24px; background: var(--surface2); border: 1px solid var(--border); border-radius: var(--r); padding: 12px 20px; font-family: var(--font-data); font-size: 13px; z-index: 200; transform: translateY(80px); opacity: 0; transition: all 0.25s ease; }
#toast.show { transform: translateY(0); opacity: 1; }
#toast.success { border-color: var(--green); color: var(--green); }
#toast.error { border-color: var(--red); color: var(--red); }
.spacer { margin-top: 20px; }
</style>
</head>
<body>

<nav id="sidebar">
  <div class="sidebar-logo">
    <h1>⚡ League</h1>
    <p>MANAGEMENT SYSTEM</p>
  </div>
  <div class="nav-section">Records</div>
  <div class="nav-item active" data-section="people"><span class="nav-icon">👤</span> People</div>
  <div class="nav-item" data-section="teams"><span class="nav-icon">🛡️</span> Teams</div>
  <div class="nav-item" data-section="schedule"><span class="nav-icon">📅</span> Schedule</div>
  <div class="nav-section">Insights</div>
  <div class="nav-item" data-section="analytics"><span class="nav-icon">📊</span> Analytics</div>
</nav>

<div id="main">
  <div class="topbar">
    <div>
      <h2 id="page-title">People</h2>
      <div class="topbar-sub" id="page-sub">Manage all registered players and staff</div>
    </div>
  </div>
  <div class="content-area">

    <!-- PEOPLE -->
    <div class="section active" id="section-people">
      <div class="table-wrap">
        <div class="table-header">
          <h3>All People</h3>
          <button class="btn btn-primary" onclick="openAddPerson()">+ Add Person</button>
        </div>
        <table>
          <thead><tr><th>P_ID</th><th>Name</th><th>Actions</th></tr></thead>
          <tbody id="people-tbody"><tr><td colspan="3" class="empty-state">Loading…</td></tr></tbody>
        </table>
      </div>
    </div>

    <!-- TEAMS -->
    <div class="section" id="section-teams">
      <div class="table-wrap">
        <div class="table-header">
          <h3>All Teams</h3>
          <button class="btn btn-primary" onclick="openAddTeam()">+ Add Team</button>
        </div>
        <table>
          <thead><tr><th>T_ID</th><th>Sport</th><th>Captain</th><th>Actions</th></tr></thead>
          <tbody id="teams-tbody"><tr><td colspan="4" class="empty-state">Loading…</td></tr></tbody>
        </table>
      </div>
      <div class="spacer"></div>
      <div class="roster-panel" id="roster-panel" style="display:none">
        <div class="table-header">
          <h3>🛡️ Roster — Team <span id="roster-team-id"></span></h3>
          <button class="btn btn-sm btn-green" onclick="openAddRoster()">+ Add Player</button>
        </div>
        <table>
          <thead><tr><th>P_ID</th><th>Name</th><th>Remove</th></tr></thead>
          <tbody id="roster-tbody"><tr><td colspan="3" class="empty-state">Select a team above to view its roster</td></tr></tbody>
        </table>
      </div>
    </div>

    <!-- SCHEDULE -->
    <div class="section" id="section-schedule">
      <div class="table-wrap">
        <div class="table-header">
          <h3>Game Schedule</h3>
          <button class="btn btn-primary" onclick="openAddGame()">+ Add Game</button>
        </div>
        <table>
          <thead><tr><th>G_ID</th><th>Sport</th><th>Location</th><th>Team 1</th><th>Team 2</th><th>Actions</th></tr></thead>
          <tbody id="schedule-tbody"><tr><td colspan="6" class="empty-state">Loading…</td></tr></tbody>
        </table>
      </div>
    </div>

    <!-- ANALYTICS -->
    <div class="section" id="section-analytics">
      <div class="analytics-grid">
        <div class="stat-card">
          <div class="stat-card-head"><h4>🏆 Top Scorers</h4><button class="refresh-btn" onclick="loadAnalytics('top_scorers')">↻</button></div>
          <div id="analytics-top_scorers"><div class="empty-state">Click ↻ to load</div></div>
        </div>
        <div class="stat-card">
          <div class="stat-card-head"><h4>📈 Team Win/Loss Records</h4><button class="refresh-btn" onclick="loadAnalytics('team_records')">↻</button></div>
          <div id="analytics-team_records"><div class="empty-state">Click ↻ to load</div></div>
        </div>
        <div class="stat-card">
          <div class="stat-card-head"><h4>⚽ Games Per Sport</h4><button class="refresh-btn" onclick="loadAnalytics('games_per_sport')">↻</button></div>
          <div id="analytics-games_per_sport"><div class="empty-state">Click ↻ to load</div></div>
        </div>
        <div class="stat-card">
          <div class="stat-card-head"><h4>👥 Roster Sizes</h4><button class="refresh-btn" onclick="loadAnalytics('roster_sizes')">↻</button></div>
          <div id="analytics-roster_sizes"><div class="empty-state">Click ↻ to load</div></div>
        </div>
        <div class="stat-card" style="grid-column:1/-1">
          <div class="stat-card-head"><h4>📍 Location Usage</h4><button class="refresh-btn" onclick="loadAnalytics('location_usage')">↻</button></div>
          <div id="analytics-location_usage"><div class="empty-state">Click ↻ to load</div></div>
        </div>
      </div>
    </div>

  </div>
</div>

<!-- MODAL -->
<div id="modal-overlay" onclick="closeModal(event)">
  <div class="modal" onclick="event.stopPropagation()">
    <div class="modal-head">
      <h3 id="modal-title"></h3>
      <button class="modal-close" onclick="closeModal()">✕</button>
    </div>
    <div class="modal-body" id="modal-body"></div>
    <div class="modal-footer">
      <button class="btn btn-muted" onclick="closeModal()">Cancel</button>
      <button class="btn btn-primary" id="modal-submit" onclick="submitModal()">Save</button>
    </div>
  </div>
</div>

<div id="toast"></div>

<script>
const API = '<?= basename(__FILE__) ?>';
let modalAction = null, editId = null, rosterTeamId = null;
let _sports = [], _people = [], _locations = [], _teams = [];

const pageMeta = {
  people:    { title: 'People',    sub: 'Manage all registered players and staff' },
  teams:     { title: 'Teams',     sub: 'Manage teams, sports, and rosters' },
  schedule:  { title: 'Schedule',  sub: 'View and manage game scheduling' },
  analytics: { title: 'Analytics', sub: 'Five key data analysis queries' }
};

document.querySelectorAll('.nav-item').forEach(el => {
  el.addEventListener('click', () => {
    document.querySelectorAll('.nav-item').forEach(n => n.classList.remove('active'));
    document.querySelectorAll('.section').forEach(s => s.classList.remove('active'));
    el.classList.add('active');
    const sec = el.dataset.section;
    document.getElementById('section-' + sec).classList.add('active');
    const m = pageMeta[sec];
    document.getElementById('page-title').textContent = m.title;
    document.getElementById('page-sub').textContent   = m.sub;
    if (sec === 'analytics') loadAllAnalytics();
  });
});

// ── API ──────────────────────────────────────────────────
async function api(action, method = 'GET', body = null) {
  const opts = { method, headers: { 'Content-Type': 'application/json' } };
  if (body) opts.body = JSON.stringify(body);
  const res = await fetch(`${API}?action=${action}`, opts);
  const data = await res.json();
  if (data && data.error) throw new Error(data.error);
  return data;
}

// ── Toast ────────────────────────────────────────────────
function toast(msg, type = 'success') {
  const t = document.getElementById('toast');
  t.textContent = msg; t.className = 'show ' + type;
  setTimeout(() => { t.className = ''; }, 2800);
}

// ── Utils ────────────────────────────────────────────────
function val(id) { const el = document.getElementById(id); return el ? el.value.trim() : ''; }
function esc(s) { return String(s ?? '').replace(/&/g,'&amp;').replace(/</g,'&lt;').replace(/>/g,'&gt;').replace(/"/g,'&quot;').replace(/'/g,'&#39;'); }

// ════════════════════════════════════════════════════════
//  PEOPLE
// ════════════════════════════════════════════════════════
async function loadPeople() {
  const tb = document.getElementById('people-tbody');
  tb.innerHTML = `<tr><td colspan="3" class="empty-state">Loading…</td></tr>`;
  try {
    const rows = await api('get_people');
    tb.innerHTML = rows.length ? rows.map(r => `
      <tr>
        <td><span class="id-badge">#${r.P_ID}</span></td>
        <td>${esc(r.Name)}</td>
        <td class="td-actions">
          <button class="btn btn-sm btn-edit" onclick="openEditPerson(${r.P_ID},'${esc(r.Name)}')">Edit</button>
          <button class="btn btn-sm btn-danger" onclick="deletePerson(${r.P_ID},'${esc(r.Name)}')">Delete</button>
        </td>
      </tr>`).join('') : `<tr><td colspan="3" class="empty-state">No people found</td></tr>`;
  } catch(e) { tb.innerHTML = `<tr><td colspan="3" class="empty-state" style="color:var(--red)">${e.message}</td></tr>`; }
}

function openAddPerson() {
  modalAction = 'add_person'; editId = null;
  setModal('Add Person', `<div class="form-group"><label>Full Name</label><input class="form-control" id="f-name" placeholder="e.g. Alex Johnson" autofocus></div>`);
}
function openEditPerson(id, name) {
  modalAction = 'update_person'; editId = id;
  setModal('Edit Person', `<div class="form-group"><label>Full Name</label><input class="form-control" id="f-name" value="${name}" autofocus></div>`);
}
async function deletePerson(id, name) {
  if (!confirm(`Delete "${name}"?`)) return;
  try { await api('delete_person','POST',{id}); toast(`Deleted: ${name}`); loadPeople(); }
  catch(e) { toast(e.message,'error'); }
}

// ════════════════════════════════════════════════════════
//  TEAMS
// ════════════════════════════════════════════════════════
async function loadRefData() {
  try { [_sports,_people,_locations] = await Promise.all([api('get_sports'),api('get_people'),api('get_locations')]); } catch(e) {}
}
async function loadTeamsRef() {
  try { _teams = await api('get_teams'); } catch(e) {}
}

async function loadTeams() {
  const tb = document.getElementById('teams-tbody');
  tb.innerHTML = `<tr><td colspan="4" class="empty-state">Loading…</td></tr>`;
  try {
    const rows = await api('get_teams');
    _teams = rows;
    tb.innerHTML = rows.length ? rows.map(r => `
      <tr>
        <td><span class="id-badge">#${r.T_ID}</span></td>
        <td>${esc(r.Sport_Type)}</td>
        <td>${r.Captain_Name ? esc(r.Captain_Name) : '<span style="color:var(--muted)">—</span>'}</td>
        <td class="td-actions">
          <button class="btn btn-sm btn-roster" onclick="viewRoster(${r.T_ID})">Roster</button>
          <button class="btn btn-sm btn-edit" onclick="openEditTeam(${r.T_ID},${r.S_ID},${r.C_ID||'null'})">Edit</button>
          <button class="btn btn-sm btn-danger" onclick="deleteTeam(${r.T_ID})">Delete</button>
        </td>
      </tr>`).join('') : `<tr><td colspan="4" class="empty-state">No teams found</td></tr>`;
  } catch(e) { tb.innerHTML = `<tr><td colspan="4" class="empty-state" style="color:var(--red)">${e.message}</td></tr>`; }
}

function teamFormFields(sid, cid) {
  return `
    <div class="form-group"><label>Sport</label>
      <select class="form-control" id="f-sid">
        <option value="">— select sport —</option>
        ${_sports.map(s=>`<option value="${s.S_ID}" ${s.S_ID==sid?'selected':''}>${esc(s.Sport_Type)}</option>`).join('')}
      </select></div>
    <div class="form-group"><label>Captain (optional)</label>
      <select class="form-control" id="f-cid">
        <option value="">— none —</option>
        ${_people.map(p=>`<option value="${p.P_ID}" ${p.P_ID==cid?'selected':''}>${esc(p.Name)}</option>`).join('')}
      </select></div>`;
}
function openAddTeam() { modalAction='add_team'; editId=null; setModal('Add Team', teamFormFields(null,null)); }
function openEditTeam(id,sid,cid) { modalAction='update_team'; editId=id; setModal('Edit Team', teamFormFields(sid,cid)); }
async function deleteTeam(id) {
  if (!confirm(`Delete Team #${id}?`)) return;
  try { await api('delete_team','POST',{id}); toast('Team deleted'); loadTeams(); document.getElementById('roster-panel').style.display='none'; }
  catch(e) { toast(e.message,'error'); }
}

// ── Roster ───────────────────────────────────────────────
async function viewRoster(tid) {
  rosterTeamId = tid;
  document.getElementById('roster-panel').style.display = 'block';
  document.getElementById('roster-team-id').textContent = tid;
  await loadRoster();
}
async function loadRoster() {
  const tb = document.getElementById('roster-tbody');
  tb.innerHTML = `<tr><td colspan="3" class="empty-state">Loading…</td></tr>`;
  try {
    const rows = await api(`get_roster&tid=${rosterTeamId}`);
    tb.innerHTML = rows.length ? rows.map(r=>`
      <tr>
        <td><span class="id-badge">#${r.P_ID}</span></td>
        <td>${esc(r.Name)}</td>
        <td><button class="btn btn-sm btn-danger" onclick="removeRoster(${r.P_ID},'${esc(r.Name)}')">Remove</button></td>
      </tr>`).join('') : `<tr><td colspan="3" class="empty-state">No players on this roster</td></tr>`;
  } catch(e) { tb.innerHTML = `<tr><td colspan="3" class="empty-state" style="color:var(--red)">${e.message}</td></tr>`; }
}
function openAddRoster() {
  modalAction='add_roster'; editId=null;
  setModal('Add Player to Roster', `
    <div class="form-group"><label>Player</label>
      <select class="form-control" id="f-pid">
        <option value="">— select player —</option>
        ${_people.map(p=>`<option value="${p.P_ID}">${esc(p.Name)}</option>`).join('')}
      </select></div>`);
}
async function removeRoster(pid, name) {
  if (!confirm(`Remove ${name} from roster?`)) return;
  try { await api('remove_roster','POST',{t_id:rosterTeamId,p_id:pid}); toast(`${name} removed`); loadRoster(); }
  catch(e) { toast(e.message,'error'); }
}

// ════════════════════════════════════════════════════════
//  SCHEDULE
// ════════════════════════════════════════════════════════
async function loadSchedule() {
  const tb = document.getElementById('schedule-tbody');
  tb.innerHTML = `<tr><td colspan="6" class="empty-state">Loading…</td></tr>`;
  try {
    const rows = await api('get_schedule');
    tb.innerHTML = rows.length ? rows.map(r=>`
      <tr>
        <td><span class="id-badge">#${r.G_ID}</span></td>
        <td>${esc(r.Sport_Type)}</td>
        <td>${esc(r.Location_Name)}</td>
        <td><span class="id-badge">#${r.T1_ID}</span></td>
        <td><span class="id-badge">#${r.T2_ID}</span></td>
        <td class="td-actions">
          <button class="btn btn-sm btn-edit" onclick="openEditGame(${r.G_ID},${r.S_ID},${r.L_ID},${r.T1_ID},${r.T2_ID})">Edit</button>
          <button class="btn btn-sm btn-danger" onclick="deleteGame(${r.G_ID})">Delete</button>
        </td>
      </tr>`).join('') : `<tr><td colspan="6" class="empty-state">No games scheduled</td></tr>`;
  } catch(e) { tb.innerHTML = `<tr><td colspan="6" class="empty-state" style="color:var(--red)">${e.message}</td></tr>`; }
}

function gameFormFields(g) {
  return `
    <div class="form-group"><label>Sport</label>
      <select class="form-control" id="f-sid">
        <option value="">— select —</option>
        ${_sports.map(s=>`<option value="${s.S_ID}" ${g&&g.sid==s.S_ID?'selected':''}>${esc(s.Sport_Type)}</option>`).join('')}
      </select></div>
    <div class="form-group"><label>Location</label>
      <select class="form-control" id="f-loc">
        <option value="">— select —</option>
        ${_locations.map(l=>`<option value="${l.L_ID}" ${g&&g.loc==l.L_ID?'selected':''}>${esc(l.Location)}</option>`).join('')}
      </select></div>
    <div class="form-group"><label>Team 1</label>
      <select class="form-control" id="f-t1">
        <option value="">— select —</option>
        ${_teams.map(t=>`<option value="${t.T_ID}" ${g&&g.t1==t.T_ID?'selected':''}>#${t.T_ID} — ${esc(t.Sport_Type)}</option>`).join('')}
      </select></div>
    <div class="form-group"><label>Team 2</label>
      <select class="form-control" id="f-t2">
        <option value="">— select —</option>
        ${_teams.map(t=>`<option value="${t.T_ID}" ${g&&g.t2==t.T_ID?'selected':''}>#${t.T_ID} — ${esc(t.Sport_Type)}</option>`).join('')}
      </select></div>`;
}
function openAddGame() { modalAction='add_game'; editId=null; setModal('Add Game', gameFormFields(null)); }
function openEditGame(id,sid,loc,t1,t2) { modalAction='update_game'; editId=id; setModal('Edit Game', gameFormFields({sid,loc,t1,t2})); }
async function deleteGame(id) {
  if (!confirm(`Delete Game #${id}?`)) return;
  try { await api('delete_game','POST',{id}); toast('Game deleted'); loadSchedule(); }
  catch(e) { toast(e.message,'error'); }
}

// ════════════════════════════════════════════════════════
//  MODAL SUBMIT
// ════════════════════════════════════════════════════════
async function submitModal() {
  const btn = document.getElementById('modal-submit');
  btn.disabled = true; btn.textContent = 'Saving…';
  try {
    switch(modalAction) {
      case 'add_person':    await api('add_person','POST',{name:val('f-name')}); toast('Person added ✓'); loadPeople(); break;
      case 'update_person': await api('update_person','POST',{id:editId,name:val('f-name')}); toast('Person updated ✓'); loadPeople(); break;
      case 'add_team':      await api('add_team','POST',{s_id:val('f-sid'),c_id:val('f-cid')}); toast('Team added ✓'); loadTeams(); break;
      case 'update_team':   await api('update_team','POST',{id:editId,s_id:val('f-sid'),c_id:val('f-cid')}); toast('Team updated ✓'); loadTeams(); break;
      case 'add_roster':    await api('add_roster','POST',{t_id:rosterTeamId,p_id:val('f-pid')}); toast('Player added ✓'); loadRoster(); break;
      case 'add_game':      await api('add_game','POST',{s_id:val('f-sid'),loc:val('f-loc'),t1_id:val('f-t1'),t2_id:val('f-t2')}); toast('Game added ✓'); loadSchedule(); break;
      case 'update_game':   await api('update_game','POST',{id:editId,s_id:val('f-sid'),loc:val('f-loc'),t1_id:val('f-t1'),t2_id:val('f-t2')}); toast('Game updated ✓'); loadSchedule(); break;
    }
    closeModal();
  } catch(e) { toast(e.message,'error'); }
  finally { btn.disabled=false; btn.textContent='Save'; }
}

// ════════════════════════════════════════════════════════
//  ANALYTICS
// ════════════════════════════════════════════════════════
function loadAllAnalytics() {
  ['top_scorers','team_records','games_per_sport','roster_sizes','location_usage'].forEach(loadAnalytics);
}

async function loadAnalytics(key) {
  const el = document.getElementById('analytics-' + key);
  el.innerHTML = '<div class="empty-state">Loading…</div>';
  try {
    const data = await api('analytics_' + key);
    if (!data.length) { el.innerHTML = '<div class="empty-state">No data available</div>'; return; }
    let html = '';
    if (key === 'top_scorers')
      html = data.map((r,i) => `<div class="stat-row"><span>${i+1}. ${esc(r.Name)}</span><span><span class="stat-badge badge-green">${r.Total_Points} pts</span> <span class="stat-badge badge-blue">${r.Games_Played} games</span></span></div>`).join('');
    else if (key === 'team_records')
      html = data.map(r => `<div class="stat-row"><span>Team #${r.T_ID} <span style="color:var(--muted)">(${esc(r.Sport_Type)})</span></span><span><span class="stat-badge badge-green">${r.Wins}W</span> <span class="stat-badge" style="background:rgba(248,113,113,0.1);color:var(--red)">${r.Losses}L</span></span></div>`).join('');
    else if (key === 'games_per_sport')
      html = data.map(r => `<div class="stat-row"><span>${esc(r.Sport_Type)}</span><span class="stat-badge badge-blue">${r.Games_Scheduled} games</span></div>`).join('');
    else if (key === 'roster_sizes')
      html = data.map(r => `<div class="stat-row"><span>Team #${r.T_ID} <span style="color:var(--muted)">(${esc(r.Sport_Type)})</span>${r.Captain?` <span style="font-size:11px;color:var(--muted)">C: ${esc(r.Captain)}</span>`:''}</span><span class="stat-badge badge-purple">${r.Roster_Size} players</span></div>`).join('');
    else if (key === 'location_usage')
      html = data.map(r => `<div class="stat-row"><span>${esc(r.Location)}${r.Sports_Played?`<br><span style="font-size:11px;color:var(--muted)">${esc(r.Sports_Played)}</span>`:''}</span><span class="stat-badge badge-yellow">${r.Times_Used} games</span></div>`).join('');
    el.innerHTML = html;
  } catch(e) { el.innerHTML = `<div class="empty-state" style="color:var(--red)">${e.message}</div>`; }
}

// ── Modal helpers ────────────────────────────────────────
function setModal(title, bodyHtml) {
  document.getElementById('modal-title').textContent = title;
  document.getElementById('modal-body').innerHTML    = bodyHtml;
  document.getElementById('modal-overlay').classList.add('open');
  setTimeout(() => { const el = document.querySelector('.modal-body input,.modal-body select'); if(el) el.focus(); }, 80);
}
function closeModal(e) {
  if (e && e.target !== document.getElementById('modal-overlay')) return;
  document.getElementById('modal-overlay').classList.remove('open');
}

// ── Boot ─────────────────────────────────────────────────
(async () => {
  await loadRefData();
  await loadTeamsRef();
  loadPeople();
  loadTeams();
  loadSchedule();
})();
</script>
</body>
</html>
