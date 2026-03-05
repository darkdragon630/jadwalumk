<?php
set_time_limit(0);
require_once __DIR__ . '/config/database.php';
$db     = getDB();
$keyRow = $db->query("SELECT `value` FROM settings WHERE `key`='anthropic_api_key'")->fetch();
$hasKey = (bool)$keyRow;
$error  = '';

// Simpan API key
if ($_SERVER['REQUEST_METHOD']==='POST' && isset($_POST['save_key'])) {
    $key = trim($_POST['api_key']??'');
    if (!str_starts_with($key,'sk-ant-')) {
        $error = 'Format tidak valid (harus sk-ant-...)';
    } else {
        $db->prepare("INSERT INTO settings (`key`,`value`) VALUES ('anthropic_api_key',?) ON DUPLICATE KEY UPDATE `value`=?")->execute([$key,$key]);
        header('Location: upload.php'); exit;
    }
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
:root{--navy:#0d1f3c;--gold:#d4a030;--bg:#f0f2f7;--white:#fff;--border:#e2e6f0;--text:#1a2540;--muted:#7a84a0;--green:#1e7a45;--red:#9c2c2c}
body{font-family:'Plus Jakarta Sans',sans-serif;background:var(--bg);color:var(--text);min-height:100vh;display:flex;align-items:center;justify-content:center;padding:24px 16px}
.card{background:var(--white);border:1px solid var(--border);border-radius:24px;padding:40px;max-width:480px;width:100%;box-shadow:0 8px 40px rgba(13,31,60,.10);animation:up .4s ease both}
@keyframes up{from{opacity:0;transform:translateY(14px)}to{opacity:1;transform:translateY(0)}}
.top{display:flex;align-items:center;gap:14px;margin-bottom:22px}
.logo{width:44px;height:44px;background:var(--navy);border-radius:11px;display:flex;align-items:center;justify-content:center;font-size:14px;font-weight:800;color:var(--gold);flex-shrink:0}
.top h1{font-size:16px;font-weight:800;color:var(--navy)}
.top p{font-size:11.5px;color:var(--muted);margin-top:2px}
.back{margin-left:auto;font-size:12px;font-weight:600;color:var(--muted);text-decoration:none}
.badge{display:inline-flex;align-items:center;gap:6px;font-size:11px;font-weight:700;padding:5px 12px;border-radius:20px;margin-bottom:16px}
.badge.ok{background:#edfaf3;border:1px solid #a0e0c0;color:#0d5c32}
.badge.warn{background:#fff8e0;border:1px solid #f5c842;color:#7a5800}
.field{margin-bottom:12px}
.lbl{font-size:11px;font-weight:700;color:var(--muted);text-transform:uppercase;letter-spacing:.08em;margin-bottom:6px;display:flex;justify-content:space-between;align-items:center}
.lbl a{font-size:11px;font-weight:700;color:#1a73e8;text-decoration:none;text-transform:none;letter-spacing:0}
.row{display:flex;gap:8px}
.inp{flex:1;padding:11px 14px;border:1.5px solid var(--border);border-radius:10px;font-family:'Plus Jakarta Sans',sans-serif;font-size:13px;color:var(--text);background:var(--bg);outline:none}
.inp:focus{border-color:var(--gold);background:var(--white)}
.savebtn{padding:11px 16px;background:var(--navy);color:#fff;border:none;border-radius:10px;font-family:'Plus Jakarta Sans',sans-serif;font-size:12px;font-weight:700;cursor:pointer}
.hint{background:#f0f5ff;border:1px solid #c8d8ff;border-radius:10px;padding:10px 13px;margin-top:8px;font-size:11.5px;color:#1a3a8f;line-height:1.7}
.hint ol{padding-left:16px;margin-top:4px}
hr{border:none;border-top:1px solid var(--border);margin:18px 0}
.dz{border:2px dashed var(--border);border-radius:14px;padding:32px 20px;text-align:center;cursor:pointer;background:var(--bg);transition:.2s;position:relative}
.dz:hover,.dz.drag{border-color:var(--gold);background:#fdf8ee}
.dz input{position:absolute;inset:0;opacity:0;cursor:pointer;width:100%;height:100%}
.dz-ic{width:46px;height:46px;background:var(--navy);border-radius:12px;display:flex;align-items:center;justify-content:center;margin:0 auto 10px}
.dz h3{font-size:13px;font-weight:700;margin-bottom:3px}
.dz p{font-size:12px;color:var(--muted)}
.dz p span{color:var(--navy);font-weight:600}
.sf{display:none;align-items:center;gap:10px;background:#eef3ff;border:1px solid #c0d0ff;border-radius:10px;padding:11px 13px;margin-top:10px}
.sf.show{display:flex}
.sf-ic{width:30px;height:30px;background:var(--navy);border-radius:7px;display:flex;align-items:center;justify-content:center;flex-shrink:0}
.sf-name{font-size:13px;font-weight:700}
.sf-size{font-size:11px;color:var(--muted);margin-top:1px}
.btn{width:100%;padding:14px;border-radius:12px;border:none;cursor:pointer;font-family:'Plus Jakarta Sans',sans-serif;font-size:14px;font-weight:700;background:var(--navy);color:#fff;margin-top:14px;display:flex;align-items:center;justify-content:center;gap:8px;transition:.2s}
.btn:hover:not(:disabled){background:#1a3260;transform:translateY(-1px)}
.btn:disabled{opacity:.45;cursor:not-allowed;transform:none}
.err{background:#fff0f0;border:1px solid #ffb0b0;color:#8b0000;border-radius:10px;padding:13px 15px;font-size:13px;margin-bottom:14px;line-height:1.6}

/* Loading overlay */
#overlay{display:none;position:fixed;inset:0;background:rgba(13,31,60,.75);z-index:999;align-items:center;justify-content:center;flex-direction:column;gap:18px}
#overlay.show{display:flex}
.spin{width:50px;height:50px;border:4px solid rgba(212,160,48,.25);border-top-color:#d4a030;border-radius:50%;animation:sp 1s linear infinite}
@keyframes sp{to{transform:rotate(360deg)}}
.ov-title{color:#fff;font-family:'Plus Jakarta Sans',sans-serif;font-size:15px;font-weight:700}
.ov-sub{color:rgba(255,255,255,.55);font-family:'Plus Jakarta Sans',sans-serif;font-size:12px;margin-top:-8px;text-align:center}
.ov-steps{display:flex;flex-direction:column;gap:6px;margin-top:4px}
.ov-step{font-family:'Plus Jakarta Sans',sans-serif;font-size:12px;color:rgba(255,255,255,.45);display:flex;align-items:center;gap:8px}
.ov-step.active{color:#d4a030;font-weight:600}
.ov-step.done{color:rgba(255,255,255,.7)}
.ov-step::before{content:'○';font-size:10px}
.ov-step.active::before{content:'●';color:#d4a030}
.ov-step.done::before{content:'✓'}
</style>
</head>
<body>

<div id="overlay">
  <div class="spin"></div>
  <div class="ov-title">Memproses jadwal...</div>
  <div class="ov-sub">Harap tunggu, jangan tutup tab ini</div>
  <div class="ov-steps">
    <div class="ov-step active" id="st1">Mengirim PDF ke Claude AI</div>
    <div class="ov-step" id="st2">Membaca & menganalisis data</div>
    <div class="ov-step" id="st3">Membuat tampilan HTML</div>
    <div class="ov-step" id="st4">Menyimpan ke server</div>
  </div>
</div>

<div class="card">
  <div class="top">
    <div class="logo">UMK</div>
    <div>
      <h1>Upload Jadwal</h1>
      <p>Universitas Muria Kudus</p>
    </div>
    <a href="index.php" class="back">← Kembali</a>
  </div>

  <?php if ($hasKey): ?>
  <div class="badge ok">✓ Claude API key tersimpan</div>
  <?php else: ?>
  <div class="badge warn">⚠ Set API key Anthropic dulu</div>
  <?php endif; ?>

  <?php if ($error): ?>
  <div class="err"><strong>✗ Error:</strong> <?= htmlspecialchars($error) ?></div>
  <?php endif; ?>

  <?php if (!$hasKey): ?>
  <div class="field">
    <div class="lbl">Anthropic API Key <a href="https://console.anthropic.com/settings/keys" target="_blank">Buat key gratis →</a></div>
    <form method="POST">
      <div class="row">
        <input type="password" name="api_key" class="inp" placeholder="sk-ant-api03-..." required/>
        <button type="submit" name="save_key" class="savebtn">Simpan</button>
      </div>
    </form>
    <div class="hint">
      1. Buka console.anthropic.com/settings/keys<br>
      2. Klik <b>Create Key</b> → copy key (sk-ant-...)<br>
      3. Paste di atas → Simpan
    </div>
  </div>
  <hr>
  <?php else: ?>
  <p style="font-size:11px;color:var(--muted);margin-bottom:12px">
    <a href="#" onclick="document.getElementById('ck').style.display='block';return false" style="color:var(--muted)">Ganti API key</a>
  </p>
  <div id="ck" style="display:none;margin-bottom:14px">
    <form method="POST">
      <div class="row">
        <input type="password" name="api_key" class="inp" placeholder="sk-ant-api03-..." required/>
        <button type="submit" name="save_key" class="savebtn">Simpan</button>
      </div>
    </form>
  </div>
  <hr>
  <?php endif; ?>

  <!-- DROP ZONE -->
  <div class="dz" id="dz" ondragover="ev(event,1)" ondragleave="ev(event,0)" ondrop="drop(event)">
    <input type="file" id="fi" accept=".pdf" onchange="pick(this)"/>
    <div class="dz-ic">
      <svg width="22" height="22" viewBox="0 0 24 24" fill="none" stroke="#d4a030" stroke-width="2"><path d="M14 2H6a2 2 0 00-2 2v16a2 2 0 002 2h12a2 2 0 002-2V8z"/><polyline points="14 2 14 8 20 8"/><line x1="12" y1="18" x2="12" y2="12"/><line x1="9" y1="15" x2="15" y2="15"/></svg>
    </div>
    <h3>Seret PDF jadwal ke sini</h3>
    <p>atau <span>klik untuk pilih file</span> · maks 10MB</p>
  </div>
  <div class="sf" id="sf">
    <div class="sf-ic">
      <svg width="14" height="14" viewBox="0 0 24 24" fill="none" stroke="#d4a030" stroke-width="2"><path d="M14 2H6a2 2 0 00-2 2v16a2 2 0 002 2h12a2 2 0 002-2V8z"/><polyline points="14 2 14 8 20 8"/></svg>
    </div>
    <div><div class="sf-name" id="sfN">–</div><div class="sf-size" id="sfS">–</div></div>
  </div>

  <button class="btn" id="btn" <?= $hasKey?'':'disabled' ?> onclick="submit()">
    <svg width="14" height="14" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.5"><path d="M21 15v4a2 2 0 01-2 2H5a2 2 0 01-2-2v-4"/><polyline points="17 8 12 3 7 8"/><line x1="12" y1="3" x2="12" y2="15"/></svg>
    Konversi PDF dengan Claude AI
  </button>
</div>

<script>
let selFile = null;

function ev(e,on){e.preventDefault();document.getElementById('dz').classList.toggle('drag',on);}
function drop(e){e.preventDefault();document.getElementById('dz').classList.remove('drag');const f=e.dataTransfer.files[0];if(f?.type==='application/pdf')setF(f);else alert('Harap file PDF.');}
function pick(inp){if(inp.files[0])setF(inp.files[0]);}
function setF(f){
  selFile=f;
  document.getElementById('sfN').textContent=f.name;
  document.getElementById('sfS').textContent=(f.size/1024).toFixed(1)+' KB';
  document.getElementById('sf').classList.add('show');
  <?php if($hasKey): ?>document.getElementById('btn').disabled=false;<?php endif; ?>
}

function stepTo(n){
  [1,2,3,4].forEach(i=>{
    const el=document.getElementById('st'+i);
    el.className='ov-step'+(i<n?' done':i===n?' active':'');
  });
}

async function submit(){
  if(!selFile){alert('Pilih file PDF dulu.');return;}
  document.getElementById('overlay').classList.add('show');
  document.getElementById('btn').disabled=true;

  // Animasi steps saat nunggu
  const times=[0,8000,20000,40000];
  times.forEach((t,i)=>setTimeout(()=>stepTo(i+1),t));

  try {
    const fd=new FormData();
    fd.append('pdf',selFile);

    const res=await fetch('process.php',{method:'POST',body:fd});
    const data=await res.json();

    stepTo(4);
    await new Promise(r=>setTimeout(r,600));

    if(data.success){
      window.location.href='index.php';
    } else {
      document.getElementById('overlay').classList.remove('show');
      document.getElementById('btn').disabled=false;
      alert('Gagal: '+data.error);
    }
  } catch(err){
    document.getElementById('overlay').classList.remove('show');
    document.getElementById('btn').disabled=false;
    alert('Error: '+err.message);
  }
}
</script>
</body>
</html>
