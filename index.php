<?php
require_once __DIR__ . '/config/database.php';
$db  = getDB();
$row = $db->query("SELECT `value` FROM settings WHERE `key`='jadwal_html'")->fetch();

// Kalau sudah ada HTML tersimpan, tampilkan langsung
if ($row && strlen($row['value']) > 500) {
    // Pastikan link upload.php tetap benar
    echo $row['value'];
    exit;
}

// Belum ada data → tampilkan halaman default (sem4)
?>
<!DOCTYPE html>
<html lang="id">
<head>
<meta charset="UTF-8">
<meta name="viewport" content="width=device-width, initial-scale=1.0">
<title>Jadwal Kuliah – UMK</title>
<link href="https://fonts.googleapis.com/css2?family=Plus+Jakarta+Sans:wght@400;500;600;700;800&display=swap" rel="stylesheet">
<style>
<?php echo file_get_contents(__DIR__ . '/jadwal_template.html') ? '' : ''; // just include css inline ?>
* { margin: 0; padding: 0; box-sizing: border-box; }
:root {
  --navy:#0d1f3c; --gold:#d4a030; --bg:#f0f2f7; --white:#fff;
  --border:#e2e6f0; --text:#1a2540; --muted:#7a84a0;
}
body { font-family:'Plus Jakarta Sans',sans-serif; background:var(--bg); color:var(--text); min-height:100vh; display:flex; align-items:center; justify-content:center; }
.empty-state { text-align:center; padding:60px 24px; }
.empty-icon { width:72px; height:72px; background:var(--navy); border-radius:18px; display:flex; align-items:center; justify-content:center; margin:0 auto 20px; }
h2 { font-size:20px; font-weight:800; color:var(--text); margin-bottom:10px; }
p { color:var(--muted); font-size:14px; line-height:1.6; margin-bottom:24px; }
.btn { display:inline-flex; align-items:center; gap:8px; background:var(--navy); color:#fff; text-decoration:none; font-family:'Plus Jakarta Sans',sans-serif; font-size:14px; font-weight:700; padding:14px 28px; border-radius:12px; }
.btn:hover { background:#1a3260; }
</style>
</head>
<body>
<div class="empty-state">
  <div class="empty-icon">
    <svg width="32" height="32" viewBox="0 0 24 24" fill="none" stroke="#d4a030" stroke-width="2"><path d="M14 2H6a2 2 0 00-2 2v16a2 2 0 002 2h12a2 2 0 002-2V8z"/><polyline points="14 2 14 8 20 8"/><line x1="12" y1="18" x2="12" y2="12"/><line x1="9" y1="15" x2="15" y2="15"/></svg>
  </div>
  <h2>Belum ada jadwal</h2>
  <p>Upload PDF jadwal dari Kanal UMK<br>untuk menampilkan jadwal kuliah.</p>
  <a href="upload.php" class="btn">
    <svg width="15" height="15" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.5"><path d="M21 15v4a2 2 0 01-2 2H5a2 2 0 01-2-2v-4"/><polyline points="17 8 12 3 7 8"/><line x1="12" y1="3" x2="12" y2="15"/></svg>
    Upload Jadwal
  </a>
</div>
</body>
</html>
