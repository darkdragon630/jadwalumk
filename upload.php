<?php
set_time_limit(0);
require_once __DIR__ . '/config/database.php';
$db    = getDB();
$error = '';
$success = false;

if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_FILES['html_file'])) {
    do {
        if ($_FILES['html_file']['error'] !== UPLOAD_ERR_OK) {
            $error = 'Upload gagal. Error code: ' . $_FILES['html_file']['error']; break;
        }
        $ext = strtolower(pathinfo($_FILES['html_file']['name'], PATHINFO_EXTENSION));
        if ($ext !== 'html' && $ext !== 'htm') {
            $error = 'Harap upload file HTML (.html atau .htm).'; break;
        }
        if ($_FILES['html_file']['size'] > 5 * 1024 * 1024) {
            $error = 'File terlalu besar (maks 5MB).'; break;
        }

        $html = file_get_contents($_FILES['html_file']['tmp_name']);
        if (stripos($html, '<html') === false && stripos($html, '<!doctype') === false) {
            $error = 'File tidak terlihat seperti HTML valid.'; break;
        }

        $db->prepare("INSERT INTO settings (`key`,`value`) VALUES ('jadwal_html',?) ON DUPLICATE KEY UPDATE `value`=?")
           ->execute([$html, $html]);

        header('Location: index.php'); exit;
    } while (false);
}
?>
<!DOCTYPE html>
<html lang="id">
<head>
<meta charset="UTF-8">
<meta name="viewport" content="width=device-width,initial-scale=1.0">
<title>Upload Jadwal – UMK</title>
<link href="https://fonts.googleapis.com/css2?family=Plus+Jakarta+Sans:wght@400;500;600;700;800&display=swap" rel="stylesheet">
<style>
*{margin:0;padding:0;box-sizing:border-box}
:root{--navy:#0d1f3c;--gold:#d4a030;--bg:#f0f2f7;--white:#fff;--border:#e2e6f0;--text:#1a2540;--muted:#7a84a0;--red:#9c2c2c}
body{font-family:'Plus Jakarta Sans',sans-serif;background:var(--bg);color:var(--text);min-height:100vh;display:flex;align-items:center;justify-content:center;padding:24px 16px}
.card{background:var(--white);border:1px solid var(--border);border-radius:24px;padding:40px;max-width:460px;width:100%;box-shadow:0 8px 40px rgba(13,31,60,.10);animation:up .4s ease both}
@keyframes up{from{opacity:0;transform:translateY(14px)}to{opacity:1;transform:translateY(0)}}
.top{display:flex;align-items:center;gap:14px;margin-bottom:28px}
.logo{width:44px;height:44px;background:var(--navy);border-radius:11px;display:flex;align-items:center;justify-content:center;font-size:14px;font-weight:800;color:var(--gold);flex-shrink:0}
.top h1{font-size:16px;font-weight:800;color:var(--navy)}
.top p{font-size:11.5px;color:var(--muted);margin-top:2px}
.back{margin-left:auto;font-size:12px;font-weight:600;color:var(--muted);text-decoration:none;white-space:nowrap}
.err{background:#fff0f0;border:1px solid #ffb0b0;color:var(--red);border-radius:10px;padding:12px 14px;font-size:13px;margin-bottom:16px;line-height:1.5}
.dz{border:2px dashed var(--border);border-radius:16px;padding:48px 24px;text-align:center;cursor:pointer;background:var(--bg);transition:.2s;position:relative}
.dz:hover,.dz.drag{border-color:var(--gold);background:#fdf8ee}
.dz input{position:absolute;inset:0;opacity:0;cursor:pointer;width:100%;height:100%}
.dz-ic{width:52px;height:52px;background:var(--navy);border-radius:14px;display:flex;align-items:center;justify-content:center;margin:0 auto 14px}
.dz h3{font-size:14px;font-weight:700;margin-bottom:4px}
.dz p{font-size:12px;color:var(--muted)}
.dz p span{color:var(--navy);font-weight:600}
.sf{display:none;align-items:center;gap:10px;background:#eef3ff;border:1px solid #c0d0ff;border-radius:10px;padding:11px 13px;margin-top:12px}
.sf.show{display:flex}
.sf-ic{width:30px;height:30px;background:var(--navy);border-radius:7px;display:flex;align-items:center;justify-content:center;flex-shrink:0}
.sf-name{font-size:13px;font-weight:700}
.sf-size{font-size:11px;color:var(--muted);margin-top:1px}
.btn{width:100%;padding:15px;border-radius:12px;border:none;cursor:pointer;font-family:'Plus Jakarta Sans',sans-serif;font-size:14px;font-weight:700;background:var(--navy);color:#fff;margin-top:14px;display:flex;align-items:center;justify-content:center;gap:8px;transition:.2s}
.btn:hover:not(:disabled){background:#1a3260;transform:translateY(-1px)}
.btn:disabled{opacity:.45;cursor:not-allowed;transform:none}
</style>
</head>
<body>
<div class="card">
  <div class="top">
    <div class="logo">UMK</div>
    <div>
      <h1>Upload Jadwal</h1>
      <p>Universitas Muria Kudus</p>
    </div>
    <a href="index.php" class="back">← Kembali</a>
  </div>

  <?php if ($error): ?>
  <div class="err"><strong>✗</strong> <?= htmlspecialchars($error) ?></div>
  <?php endif; ?>

  <form method="POST" enctype="multipart/form-data" id="form">
    <div class="dz" id="dz" ondragover="ev(event,1)" ondragleave="ev(event,0)" ondrop="drop(event)">
      <input type="file" name="html_file" id="fi" accept=".html,.htm" onchange="pick(this)"/>
      <div class="dz-ic">
        <svg width="24" height="24" viewBox="0 0 24 24" fill="none" stroke="#d4a030" stroke-width="2"><path d="M14 2H6a2 2 0 00-2 2v16a2 2 0 002 2h12a2 2 0 002-2V8z"/><polyline points="14 2 14 8 20 8"/><line x1="12" y1="18" x2="12" y2="12"/><line x1="9" y1="15" x2="15" y2="15"/></svg>
      </div>
      <h3>Seret file HTML ke sini</h3>
      <p>atau <span>klik untuk pilih file</span> · .html / .htm</p>
    </div>

    <div class="sf" id="sf">
      <div class="sf-ic">
        <svg width="14" height="14" viewBox="0 0 24 24" fill="none" stroke="#d4a030" stroke-width="2"><path d="M14 2H6a2 2 0 00-2 2v16a2 2 0 002 2h12a2 2 0 002-2V8z"/><polyline points="14 2 14 8 20 8"/></svg>
      </div>
      <div>
        <div class="sf-name" id="sfN">–</div>
        <div class="sf-size" id="sfS">–</div>
      </div>
    </div>

    <button type="submit" class="btn" id="btn" disabled>
      <svg width="14" height="14" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.5"><path d="M21 15v4a2 2 0 01-2 2H5a2 2 0 01-2-2v-4"/><polyline points="17 8 12 3 7 8"/><line x1="12" y1="3" x2="12" y2="15"/></svg>
      Simpan Jadwal
    </button>
  </form>
</div>

<script>
function ev(e,on){e.preventDefault();document.getElementById('dz').classList.toggle('drag',!!on);}
function drop(e){
  e.preventDefault();document.getElementById('dz').classList.remove('drag');
  const f=e.dataTransfer.files[0];
  if(f&&(f.name.endsWith('.html')||f.name.endsWith('.htm'))){
    const dt=new DataTransfer();dt.items.add(f);
    document.getElementById('fi').files=dt.files;setF(f);
  } else alert('Harap file HTML (.html atau .htm).');
}
function pick(inp){if(inp.files[0])setF(inp.files[0]);}
function setF(f){
  document.getElementById('sfN').textContent=f.name;
  document.getElementById('sfS').textContent=(f.size/1024).toFixed(1)+' KB';
  document.getElementById('sf').classList.add('show');
  document.getElementById('btn').disabled=false;
}
</script>
</body>
</html>
