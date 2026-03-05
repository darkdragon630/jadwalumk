<?php
require_once __DIR__ . '/config/database.php';
$db     = getDB();
$keyRow = $db->query("SELECT `value` FROM settings WHERE `key`='gemini_api_key'")->fetch();
$hasKey = (bool)$keyRow;
?>
<!DOCTYPE html>
<html lang="id">
<head>
<meta charset="UTF-8">
<meta name="viewport" content="width=device-width, initial-scale=1.0">
<title>Upload Jadwal – UMK</title>
<link href="https://fonts.googleapis.com/css2?family=Plus+Jakarta+Sans:wght@400;500;600;700;800&display=swap" rel="stylesheet">
<style>
* { margin:0; padding:0; box-sizing:border-box; }
:root { --navy:#0d1f3c; --gold:#d4a030; --gold-light:#f5c842; --bg:#f0f2f7; --white:#fff; --border:#e2e6f0; --text:#1a2540; --muted:#7a84a0; --green:#1e7a45; --red:#9c2c2c; }
body { font-family:'Plus Jakarta Sans',sans-serif; background:var(--bg); color:var(--text); min-height:100vh; display:flex; flex-direction:column; align-items:center; justify-content:center; padding:32px 16px; }
.card { background:var(--white); border:1px solid var(--border); border-radius:24px; padding:44px 40px; max-width:520px; width:100%; box-shadow:0 8px 48px rgba(13,31,60,.10); animation:up .45s ease both; }
@keyframes up { from{opacity:0;transform:translateY(18px)} to{opacity:1;transform:translateY(0)} }
.top { display:flex; align-items:center; gap:14px; margin-bottom:24px; }
.logo { width:46px; height:46px; background:var(--navy); border-radius:12px; display:flex; align-items:center; justify-content:center; font-size:15px; font-weight:800; color:var(--gold); flex-shrink:0; }
.top-text h1 { font-size:17px; font-weight:800; color:var(--navy); }
.top-text p { font-size:12px; color:var(--muted); margin-top:2px; }
.back-link { margin-left:auto; font-size:12px; font-weight:600; color:var(--muted); text-decoration:none; display:flex; align-items:center; gap:5px; }
.badge { display:inline-flex; align-items:center; gap:6px; font-size:11px; font-weight:700; padding:5px 12px; border-radius:20px; margin-bottom:20px; }
.badge.green { background:#edfaf3; border:1px solid #a0e0c0; color:#0d5c32; }
.badge.blue { background:#e8f0fe; border:1px solid #aac4ff; color:#1a3a8f; }
.api-section { margin-bottom:18px; }
.api-label { font-size:11px; font-weight:700; color:var(--muted); text-transform:uppercase; letter-spacing:.08em; margin-bottom:7px; display:flex; align-items:center; justify-content:space-between; }
.api-label a { font-size:11px; font-weight:700; color:#1a73e8; text-decoration:none; text-transform:none; letter-spacing:0; }
.api-input-wrap { display:flex; gap:8px; }
.api-input { flex:1; padding:11px 14px; border:1.5px solid var(--border); border-radius:10px; font-family:'Plus Jakarta Sans',sans-serif; font-size:13px; color:var(--text); background:var(--bg); outline:none; }
.api-input:focus { border-color:var(--gold); background:var(--white); }
.api-input.saved { border-color:#a0e0c0; background:#f0fdf7; }
.api-save-btn { padding:11px 16px; background:var(--navy); color:#fff; border:none; border-radius:10px; font-family:'Plus Jakarta Sans',sans-serif; font-size:12px; font-weight:700; cursor:pointer; white-space:nowrap; }
.api-status { font-size:11px; margin-top:6px; min-height:16px; }
.api-status.ok { color:var(--green); }
.api-status.err { color:var(--red); }
.hint { background:#f0f5ff; border:1px solid #c8d8ff; border-radius:10px; padding:12px 14px; margin-bottom:18px; font-size:12px; color:#1a3a8f; line-height:1.7; }
.hint strong { display:block; margin-bottom:4px; }
.hint ol { padding-left:16px; }
.divider { border:none; border-top:1px solid var(--border); margin:18px 0; }
.drop-zone { border:2px dashed var(--border); border-radius:16px; padding:36px 24px; text-align:center; cursor:pointer; background:var(--bg); position:relative; transition:all .2s; }
.drop-zone.drag { border-color:var(--gold); background:#fdf8ee; }
.drop-zone input { position:absolute; inset:0; opacity:0; cursor:pointer; width:100%; height:100%; }
.dz-icon { width:48px; height:48px; background:var(--navy); border-radius:13px; display:flex; align-items:center; justify-content:center; margin:0 auto 12px; }
.dz-title { font-size:14px; font-weight:700; color:var(--text); margin-bottom:3px; }
.dz-sub { font-size:12px; color:var(--muted); }
.dz-sub span { color:var(--navy); font-weight:600; }
.file-preview { display:none; align-items:center; gap:12px; background:#f0f5ff; border:1px solid #c8d8ff; border-radius:12px; padding:13px 15px; margin-top:12px; }
.file-preview.show { display:flex; }
.fp-icon { width:34px; height:34px; background:var(--navy); border-radius:9px; display:flex; align-items:center; justify-content:center; flex-shrink:0; }
.fp-name { font-size:13px; font-weight:700; color:var(--text); }
.fp-size { font-size:11px; color:var(--muted); margin-top:2px; }
.fp-remove { margin-left:auto; background:none; border:none; cursor:pointer; color:var(--muted); padding:4px; }
.btn { width:100%; padding:15px; border-radius:12px; border:none; cursor:pointer; font-family:'Plus Jakarta Sans',sans-serif; font-size:14px; font-weight:700; background:var(--navy); color:#fff; margin-top:18px; transition:all .2s; display:flex; align-items:center; justify-content:center; gap:8px; }
.btn:hover:not(:disabled) { background:#1a3260; transform:translateY(-1px); }
.btn:disabled { opacity:.5; cursor:not-allowed; }
.btn.success { background:var(--green); }
.status-box { display:none; margin-top:16px; border-radius:12px; padding:14px 16px; font-size:13px; line-height:1.6; word-break:break-word; }
.status-box.show { display:block; }
.status-box.error { background:#fff0f0; border:1px solid #ffc0c0; color:#8b0000; }
.status-box.ok { background:#edfaf3; border:1px solid #a0e0c0; color:#0d5c32; }
.progress-wrap { display:none; margin-top:14px; }
.progress-wrap.show { display:block; }
.progress-label { font-size:12px; font-weight:600; color:var(--muted); margin-bottom:7px; display:flex; justify-content:space-between; }
.progress-bar { height:6px; background:var(--border); border-radius:3px; overflow:hidden; }
.progress-fill { height:100%; background:linear-gradient(90deg,var(--gold),var(--gold-light)); border-radius:3px; transition:width .4s ease; width:0%; }
.steps { margin-top:22px; border-top:1px solid var(--border); padding-top:18px; display:flex; }
.step { flex:1; text-align:center; position:relative; }
.step:not(:last-child)::after { content:''; position:absolute; top:14px; left:60%; width:80%; height:2px; background:var(--border); z-index:0; }
.step.done:not(:last-child)::after { background:var(--gold); }
.step-circle { width:28px; height:28px; border-radius:50%; background:var(--border); color:var(--muted); font-size:11px; font-weight:700; display:flex; align-items:center; justify-content:center; margin:0 auto 6px; position:relative; z-index:1; transition:all .3s; }
.step.done .step-circle { background:var(--gold); color:var(--navy); }
.step.active .step-circle { background:var(--navy); color:#fff; }
.step-label { font-size:10px; font-weight:600; color:var(--muted); text-transform:uppercase; letter-spacing:.06em; }
.step.done .step-label, .step.active .step-label { color:var(--text); }
</style>
</head>
<body>
<div class="card">
  <div class="top">
    <div class="logo">UMK</div>
    <div class="top-text">
      <h1>Upload Jadwal Baru</h1>
      <p>Universitas Muria Kudus — Fakultas Teknik</p>
    </div>
    <a href="index.php" class="back-link">
      <svg width="13" height="13" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.5"><polyline points="15 18 9 12 15 6"/></svg>
      Kembali
    </a>
  </div>

  <?php if ($hasKey): ?>
  <div class="badge green">✓ API key tersimpan — siap dipakai</div>
  <?php else: ?>
  <div class="badge blue">⚠ Set API key Gemini dulu</div>
  <?php endif; ?>

  <div class="api-section" <?= $hasKey ? 'style="display:none"' : '' ?> id="apiSection">
    <div class="api-label">
      Google Gemini API Key
      <a href="https://aistudio.google.com/app/apikey" target="_blank">Dapatkan gratis →</a>
    </div>
    <div class="api-input-wrap">
      <input type="password" id="apiKeyInput" class="api-input" placeholder="AIzaSy..." autocomplete="off" />
      <button class="api-save-btn" onclick="saveApiKey()">Simpan</button>
    </div>
    <div class="api-status" id="apiStatus"></div>
    <div class="hint">
      <strong>🔑 Cara dapat API key gratis:</strong>
      <ol>
        <li>Klik "Dapatkan gratis →" di atas (pakai Gmail biasa)</li>
        <li>Klik "Create API key" → copy → paste di sini → Simpan</li>
      </ol>
    </div>
  </div>
  <?php if ($hasKey): ?>
  <p style="font-size:11px;color:var(--muted);margin-bottom:16px;">
    <a href="#" onclick="document.getElementById('changeKey').style.display='block';return false;" style="color:var(--muted);">Ganti API key</a>
  </p>
  <div id="changeKey" style="display:none;margin-bottom:16px;">
    <div class="api-input-wrap">
      <input type="password" id="apiKeyInput" class="api-input" placeholder="AIzaSy..." autocomplete="off"/>
      <button class="api-save-btn" onclick="saveApiKey()">Simpan</button>
    </div>
    <div class="api-status" id="apiStatus"></div>
  </div>
  <?php endif; ?>

  <hr class="divider">

  <div class="steps">
    <div class="step active" id="s1"><div class="step-circle">1</div><div class="step-label">Pilih PDF</div></div>
    <div class="step" id="s2"><div class="step-circle">2</div><div class="step-label">Generate</div></div>
    <div class="step" id="s3"><div class="step-circle">3</div><div class="step-label">Tersimpan</div></div>
  </div>

  <div style="margin-top:20px">
    <div class="drop-zone" id="dropZone">
      <input type="file" id="fileInput" accept=".pdf"/>
      <div class="dz-icon">
        <svg width="22" height="22" viewBox="0 0 24 24" fill="none" stroke="#d4a030" stroke-width="2"><path d="M14 2H6a2 2 0 00-2 2v16a2 2 0 002 2h12a2 2 0 002-2V8z"/><polyline points="14 2 14 8 20 8"/><line x1="12" y1="18" x2="12" y2="12"/><line x1="9" y1="15" x2="15" y2="15"/></svg>
      </div>
      <div class="dz-title">Seret PDF jadwal ke sini</div>
      <div class="dz-sub">atau <span>klik untuk memilih file</span></div>
    </div>
    <div class="file-preview" id="filePreview">
      <div class="fp-icon">
        <svg width="17" height="17" viewBox="0 0 24 24" fill="none" stroke="#d4a030" stroke-width="2"><path d="M14 2H6a2 2 0 00-2 2v16a2 2 0 002 2h12a2 2 0 002-2V8z"/><polyline points="14 2 14 8 20 8"/></svg>
      </div>
      <div><div class="fp-name" id="fpName">–</div><div class="fp-size" id="fpSize">–</div></div>
      <button class="fp-remove" id="fpRemove">
        <svg width="15" height="15" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.5"><line x1="18" y1="6" x2="6" y2="18"/><line x1="6" y1="6" x2="18" y2="18"/></svg>
      </button>
    </div>
  </div>

  <div class="progress-wrap" id="progressWrap">
    <div class="progress-label"><span id="progressLabel">Memproses...</span><span id="progressPct">0%</span></div>
    <div class="progress-bar"><div class="progress-fill" id="progressFill"></div></div>
  </div>
  <div class="status-box" id="statusBox"></div>

  <button class="btn" id="uploadBtn" disabled onclick="processUpload()">
    <svg width="15" height="15" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.5"><path d="M21 15v4a2 2 0 01-2 2H5a2 2 0 01-2-2v-4"/><polyline points="17 8 12 3 7 8"/><line x1="12" y1="3" x2="12" y2="15"/></svg>
    Generate &amp; Simpan Jadwal
  </button>
</div>

<script>
const HAS_KEY = <?= $hasKey ? 'true' : 'false' ?>;
const GEMINI_MODEL = 'gemini-2.5-flash';
let selectedFile = null;

async function saveApiKey() {
  const inp = document.getElementById('apiKeyInput');
  const key = inp ? inp.value.trim() : '';
  if (!key.startsWith('AIza')) { setApiStatus('err','✗ Format tidak valid'); return; }
  const r = await fetch('api/jadwal.php?action=save_key',{method:'POST',headers:{'Content-Type':'application/json'},body:JSON.stringify({key})});
  const d = await r.json();
  if (d.success) {
    setApiStatus('ok','✓ Tersimpan');
    ['apiSection','changeKey'].forEach(id => { const el=document.getElementById(id); if(el) el.style.display='none'; });
    updateBtn(true);
  } else setApiStatus('err','✗ '+d.error);
}
function setApiStatus(t,m){const e=document.getElementById('apiStatus');if(e){e.className='api-status '+t;e.textContent=m;}}

const dz=document.getElementById('dropZone'),fi=document.getElementById('fileInput');
dz.addEventListener('dragover',e=>{e.preventDefault();dz.classList.add('drag');});
dz.addEventListener('dragleave',()=>dz.classList.remove('drag'));
dz.addEventListener('drop',e=>{e.preventDefault();dz.classList.remove('drag');const f=e.dataTransfer.files[0];if(f?.type==='application/pdf')setFile(f);else showStatus('error','Harap upload file PDF.');});
fi.addEventListener('change',()=>{if(fi.files[0])setFile(fi.files[0]);});
document.getElementById('fpRemove').addEventListener('click',clearFile);

function setFile(f){selectedFile=f;document.getElementById('fpName').textContent=f.name;document.getElementById('fpSize').textContent=(f.size/1024).toFixed(1)+' KB';document.getElementById('filePreview').classList.add('show');clearStatus();setStep(1);updateBtn();}
function clearFile(){selectedFile=null;fi.value='';document.getElementById('filePreview').classList.remove('show');clearStatus();setStep(0);updateBtn();}
function updateBtn(k){const ok=k!==undefined?k:HAS_KEY;document.getElementById('uploadBtn').disabled=!(selectedFile&&ok);}
function setStep(n){[1,2,3].forEach(i=>{const e=document.getElementById('s'+i);e.classList.remove('active','done');if(i<n+1)e.classList.add('done');if(i===n+1)e.classList.add('active');});}
function showStatus(t,m){const e=document.getElementById('statusBox');e.className='status-box show '+t;e.innerHTML=m;}
function clearStatus(){document.getElementById('statusBox').className='status-box';}
function setProgress(p,l){document.getElementById('progressWrap').classList.add('show');document.getElementById('progressFill').style.width=p+'%';document.getElementById('progressPct').textContent=Math.round(p)+'%';if(l)document.getElementById('progressLabel').textContent=l;}
function hideProgress(){document.getElementById('progressWrap').classList.remove('show');}
updateBtn();

// ═══════════════════════════════════════════
async function processUpload() {
  if (!selectedFile) return;
  const btn = document.getElementById('uploadBtn');
  btn.disabled = true; clearStatus(); setStep(2);
  setProgress(10, 'Membaca PDF...');

  try {
    // Ambil API key dari server
    const keyRes = await fetch('api/jadwal.php?action=get_key');
    const keyData = await keyRes.json();
    if (!keyData.success) throw new Error('API key belum diset.');
    const apiKey = keyData.key;

    const base64 = await fileToBase64(selectedFile);
    setProgress(28, 'Mengirim ke Gemini AI...');

    // ─── PROMPT ────────────────────────────────────────────────
    // Strategi: Gemini HANYA disuruh baca PDF dan tulis ulang
    // isinya ke dalam HTML yang sudah kita desain penuh.
    // Kita tidak suruh dia "parse JSON" atau "ikuti template" —
    // kita suruh dia "ini PDF, ini CSS kita, buatkan HTML-nya".
    const PROMPT = `Kamu adalah konverter PDF jadwal kuliah UMK ke HTML.

PDF yang diberikan adalah jadwal kuliah dari Universitas Muria Kudus (UMK) Fakultas Teknik.
Baca PDF tersebut dengan teliti, lalu hasilkan SATU FILE HTML LENGKAP yang menampilkan jadwal tersebut.

=== DESAIN YANG HARUS DIHASILKAN ===

HTML harus menggunakan desain ini persis (copy CSS ini ke dalam <style>):

<!DOCTYPE html>
<html lang="id">
<head>
<meta charset="UTF-8">
<meta name="viewport" content="width=device-width,initial-scale=1.0">
<title>Jadwal Kuliah – [NAMA MAHASISWA DARI PDF]</title>
<link href="https://fonts.googleapis.com/css2?family=Plus+Jakarta+Sans:wght@400;500;600;700;800&display=swap" rel="stylesheet">
<style>
*{margin:0;padding:0;box-sizing:border-box}
:root{--navy:#0d1f3c;--gold:#d4a030;--bg:#f0f2f7;--white:#fff;--border:#e2e6f0;--text:#1a2540;--muted:#7a84a0}
body{font-family:'Plus Jakarta Sans',sans-serif;background:var(--bg);color:var(--text);font-size:14px;padding:32px 24px 60px}
.wrap{max-width:1100px;margin:0 auto}
.header{background:var(--navy);border-radius:16px;padding:28px 32px;display:flex;align-items:center;gap:18px;margin-bottom:16px}
.hlogo{width:48px;height:48px;background:var(--gold);border-radius:12px;display:flex;align-items:center;justify-content:center;font-size:15px;font-weight:800;color:var(--navy);flex-shrink:0}
.hinfo h1{font-size:15px;font-weight:700;color:#fff}
.hinfo p{font-size:11px;font-weight:600;color:rgba(212,160,48,.8);margin-top:3px;text-transform:uppercase;letter-spacing:.08em}
.hright{margin-left:auto;text-align:right}
.hright .sem{font-size:14px;font-weight:700;color:var(--gold)}
.hright .semlab{font-size:10px;color:rgba(255,255,255,.4);text-transform:uppercase;letter-spacing:.1em}
.upload-btn{display:inline-flex;align-items:center;gap:6px;background:rgba(255,255,255,.1);border:1px solid rgba(255,255,255,.2);color:#fff;text-decoration:none;font-size:11px;font-weight:600;padding:7px 14px;border-radius:8px;margin-top:8px}
.infogrid{display:grid;grid-template-columns:2fr 1fr 1.5fr 1.5fr 60px;gap:10px;margin-bottom:24px}
.icard{background:var(--white);border:1px solid var(--border);border-radius:12px;padding:14px 16px}
.icard .lbl{font-size:10px;font-weight:700;color:var(--muted);text-transform:uppercase;letter-spacing:.1em;margin-bottom:4px}
.icard .val{font-size:13px;font-weight:700;color:var(--text);line-height:1.3}
.icard.sks{background:var(--navy);display:flex;flex-direction:column;align-items:center;justify-content:center;text-align:center}
.icard.sks .lbl{color:rgba(255,255,255,.4)}
.icard.sks .val{font-size:26px;font-weight:800;color:var(--gold)}
.sec{display:flex;align-items:center;gap:10px;margin-bottom:10px}
.sec h2{font-size:13px;font-weight:700;white-space:nowrap}
.sec hr{flex:1;border:none;border-top:1px solid var(--border)}
.sec .badge{background:var(--navy);color:#fff;font-size:10px;font-weight:700;padding:2px 9px;border-radius:20px}
.tbl-wrap{overflow-x:auto;border-radius:14px;box-shadow:0 1px 16px rgba(13,31,60,.07);margin-bottom:28px}
table{width:100%;border-collapse:collapse;background:var(--white);border-radius:14px;overflow:hidden;min-width:820px}
thead tr.r1{background:var(--navy)}
thead th{color:#fff;font-weight:700;font-size:10px;text-transform:uppercase;letter-spacing:.07em;padding:12px 12px;text-align:left;border-right:1px solid rgba(255,255,255,.06)}
thead th.tc{text-align:center}
thead th.day{background:#0f2a56;color:rgba(212,160,48,.85);font-size:9.5px;text-align:center;padding:12px 5px}
thead th.sub{background:#14264e;font-size:9px;color:rgba(255,255,255,.4);font-weight:600;padding:6px 12px}
tbody tr{border-bottom:1px solid var(--border)}
tbody tr:last-child{border-bottom:none}
tbody tr:hover{background:#f5f7fd}
tbody td{padding:10px 12px;vertical-align:middle;border-right:1px solid var(--border);font-size:13px}
tbody td:last-child{border-right:none}
tbody td.tc{text-align:center}
.num{color:var(--muted);font-weight:700;font-size:12px}
.kls{display:inline-flex;align-items:center;justify-content:center;width:26px;height:26px;border-radius:7px;background:var(--navy);color:#fff;font-size:12px;font-weight:700}
.kode{font-size:10px;font-weight:700;color:var(--muted);background:var(--bg);border:1px solid var(--border);padding:2px 6px;border-radius:5px;display:inline-block;margin-bottom:2px}
.mknama{font-size:13px;font-weight:600;line-height:1.3}
.prak{font-size:9px;font-weight:700;text-transform:uppercase;color:#16736b;background:#e6f5f3;padding:2px 6px;border-radius:4px;display:inline-block;margin-top:2px}
.dos{font-size:12px;color:#3a4a6a;line-height:1.4}
.sksn{font-size:17px;font-weight:800;color:var(--gold);display:block;text-align:center}
.pill{display:inline-block;border-radius:8px;padding:6px 8px;text-align:center;min-width:74px}
.pill .pt{font-size:11px;font-weight:700;color:#fff;line-height:1.2}
.pill .pr{font-size:9px;color:rgba(255,255,255,.7);margin-top:2px}
td.dtd{text-align:center;padding:6px 4px}
td.emp{text-align:center;color:#d0d5e5}
.wgrid{display:grid;grid-template-columns:repeat(6,1fr);gap:10px;margin-bottom:28px}
.dcol{background:var(--white);border:1px solid var(--border);border-radius:12px;overflow:hidden}
.dhead{background:var(--navy);color:#fff;text-align:center;font-size:10px;font-weight:700;padding:9px 5px;text-transform:uppercase;letter-spacing:.06em}
.dbody{padding:8px;display:flex;flex-direction:column;gap:6px;min-height:48px}
.wcard{border-radius:8px;padding:8px 10px}
.wt{font-size:9px;font-weight:600;color:rgba(255,255,255,.6);margin-bottom:2px}
.wn{font-size:11px;font-weight:700;color:#fff;line-height:1.3}
.wr{font-size:9px;color:rgba(255,255,255,.6);margin-top:2px}
.nocls{color:var(--muted);font-size:11px;text-align:center;padding:14px 0;font-style:italic}
.foot{display:flex;justify-content:space-between;align-items:center;flex-wrap:wrap;gap:8px;padding-top:18px;border-top:1px solid var(--border);font-size:12px;color:var(--muted)}
.foot strong{color:var(--text)}
@media(max-width:700px){.infogrid{grid-template-columns:1fr 1fr}.icard.sks{grid-column:span 2}.wgrid{grid-template-columns:repeat(3,1fr)}.header{flex-wrap:wrap}}
</style>
</head>
<body>
<div class="wrap">

  <!-- HEADER -->
  <div class="header">
    <div class="hlogo">UMK</div>
    <div class="hinfo">
      <h1>Universitas Muria Kudus — Fakultas Teknik</h1>
      <p>Jadwal Kuliah · [PRODI DARI PDF]</p>
    </div>
    <div class="hright">
      <div class="semlab">Semester</div>
      <div class="sem">[SEMESTER DARI PDF]</div>
      <a href="upload.php" class="upload-btn">↑ Upload Jadwal Baru</a>
    </div>
  </div>

  <!-- INFO MAHASISWA -->
  <div class="infogrid">
    <div class="icard"><div class="lbl">Nama</div><div class="val">[NAMA DARI PDF]</div></div>
    <div class="icard"><div class="lbl">NIM</div><div class="val">[NIM DARI PDF]</div></div>
    <div class="icard"><div class="lbl">Program Studi</div><div class="val">[PRODI DARI PDF]</div></div>
    <div class="icard"><div class="lbl">Dosen PA</div><div class="val">[DOSEN PA DARI PDF]</div></div>
    <div class="icard sks"><div class="lbl">SKS</div><div class="val">[TOTAL SKS DARI PDF]</div></div>
  </div>

  <!-- TABEL JADWAL -->
  <div class="sec"><h2>Tabel Jadwal</h2><hr><span class="badge">[JUMLAH MK] Matakuliah</span></div>
  <div class="tbl-wrap">
    <table>
      <thead>
        <tr class="r1">
          <th class="tc" style="width:38px" rowspan="2">No.</th>
          <th class="tc" style="width:42px" rowspan="2">Kls</th>
          <th colspan="2">Matakuliah</th>
          <th rowspan="2">Dosen</th>
          <th class="tc" style="width:42px" rowspan="2">SKS</th>
          <th colspan="7" class="tc" style="text-align:center">Jadwal</th>
        </tr>
        <tr class="r1">
          <th class="sub" style="width:65px">Kode</th>
          <th class="sub">Nama</th>
          <th class="day" style="width:85px">Sn</th>
          <th class="day" style="width:85px">Sl</th>
          <th class="day" style="width:85px">Rb</th>
          <th class="day" style="width:85px">Km</th>
          <th class="day" style="width:85px">Jm</th>
          <th class="day" style="width:48px">Sb</th>
          <th class="day" style="width:48px">Mg</th>
        </tr>
      </thead>
      <tbody>
        <!-- ISI SETIAP BARIS DARI PDF, CONTOH FORMAT SATU BARIS: -->
        <!--
        <tr>
          <td class="tc num">1</td>
          <td class="tc"><span class="kls">E</span></td>
          <td><span class="kode">IFT406</span></td>
          <td><div class="mknama">PRAKTIKUM PEMROGRAMAN MOBILE</div><span class="prak">PRAKTIKUM</span></td>
          <td><div class="dos">ADITYA AKBAR RIADIS, Kom., M.Kom.</div></td>
          <td><span class="sksn">1</span></td>
          <td class="emp">·</td>
          <td class="emp">·</td>
          <td class="emp">·</td>
          <td class="emp">·</td>
          <td class="dtd"><div class="pill" style="background:#2563a8"><div class="pt">08:00–10:29</div><div class="pr">I.3,04</div></div></td>
          <td class="emp">·</td>
          <td class="emp">·</td>
        </tr>
        -->
        <!-- TULIS SEMUA BARIS DARI PDF DI SINI -->
      </tbody>
    </table>
  </div>

  <!-- RINGKASAN MINGGUAN -->
  <div class="sec"><h2>Ringkasan Mingguan</h2><hr></div>
  <div class="wgrid">
    <!-- Buat 6 kolom hari: SENIN, SELASA, RABU, KAMIS, JUMAT, SABTU -->
    <!-- Contoh kolom dengan isi: -->
    <!--
    <div class="dcol">
      <div class="dhead">SENIN</div>
      <div class="dbody">
        <div class="wcard" style="background:#7c3d9e">
          <div class="wt">10:30–12:59</div>
          <div class="wn">PRAKTIKUM PENGENALAN POLA</div>
          <div class="wr">IFT410 · I.3,06</div>
        </div>
      </div>
    </div>
    -->
    <!-- Kolom tanpa jadwal: -->
    <!--
    <div class="dcol">
      <div class="dhead">SABTU</div>
      <div class="dbody"><div class="nocls">Tidak ada kelas</div></div>
    </div>
    -->
  </div>

  <!-- FOOTER -->
  <div class="foot">
    <div>Dicetak: <strong>[TANGGAL CETAK DARI PDF]</strong> · Sumber: Kanal UMK</div>
    <div style="text-align:right"><strong>[NAMA DOSEN PA]</strong><br>Dosen PA</div>
  </div>

</div>
</body>
</html>

=== INSTRUKSI PENGISIAN ===

BAGIAN HEADER & INFO:
- Ganti semua [TEKS DALAM KURUNG] dengan data dari PDF
- Nama, NIM, Prodi, Dosen PA, SKS, Semester → persis dari PDF

BAGIAN TABEL (PALING PENTING):
- Tulis SETIAP baris matakuliah dari PDF sebagai <tr>
- Urutan kolom jadwal: Sn=Senin, Sl=Selasa, Rb=Rabu, Km=Kamis, Jm=Jumat, Sb=Sabtu, Mg=Minggu
- Kolom hari ADA jadwal → <td class="dtd"><div class="pill" style="background:WARNA"><div class="pt">JAM</div><div class="pr">RUANG</div></div></td>
- Kolom hari KOSONG → <td class="emp">·</td>
- Warna pill per baris (urutan): #2563a8, #16736b, #7c3d9e, #b85c00, #1e7a45, #9c2c2c, #3d5fa8, #5a4a9e, #0d6b7a (lalu ulang dari awal)
- isPraktikum → tampilkan <span class="prak">PRAKTIKUM</span>
- Format jam: gunakan "–" (en dash), contoh: 08:00–10:29

BAGIAN RINGKASAN MINGGUAN:
- Buat tepat 6 div.dcol untuk: SENIN, SELASA, RABU, KAMIS, JUMAT, SABTU
- Isi setiap kolom berdasarkan jadwal yang ada di tabel di atas
- Sortir per hari berdasarkan jam mulai (terkecil duluan)
- Warna wcard sama dengan warna pill di tabel

OUTPUT:
- Kembalikan HANYA HTML lengkap dari <!DOCTYPE html> sampai </html>
- JANGAN ada markdown, backtick, atau teks penjelasan
- JANGAN ada tag kosong tanpa isi
- Pastikan HTML valid dan semua data dari PDF sudah tercantum`;
    // ─────────────────────────────────────────────────────────

    const apiUrl = `https://generativelanguage.googleapis.com/v1beta/models/${GEMINI_MODEL}:generateContent?key=${apiKey}`;
    const response = await fetch(apiUrl, {
      method: 'POST',
      headers: { 'Content-Type': 'application/json' },
      body: JSON.stringify({
        contents: [{ parts: [
          { inline_data: { mime_type: 'application/pdf', data: base64 } },
          { text: PROMPT }
        ]}],
        generationConfig: { temperature: 0, maxOutputTokens: 16000 }
      })
    });

    setProgress(72, 'Menerima HTML dari AI...');

    if (!response.ok) {
      const err = await response.json().catch(() => ({}));
      const msg = err.error?.message || `HTTP ${response.status}`;
      if (msg.includes('quota') || msg.includes('RESOURCE_EXHAUSTED'))
        throw new Error('Quota Gemini habis. Coba lagi besok atau ganti API key.');
      throw new Error(msg);
    }

    const aiResp = await response.json();
    let html = aiResp.candidates?.[0]?.content?.parts?.[0]?.text || '';

    // Bersihkan kalau Gemini wrap dengan markdown
    html = html.trim()
      .replace(/^```html\s*/i,'').replace(/^```\s*/i,'').replace(/\s*```$/i,'').trim();

    if (!html.toLowerCase().includes('<!doctype') && !html.toLowerCase().includes('<html')) {
      throw new Error('Output AI bukan HTML valid. Respons: ' + html.slice(0,200));
    }

    setProgress(88, 'Menyimpan ke server...');

    const saveRes = await fetch('api/jadwal.php?action=save_html', {
      method: 'POST',
      headers: { 'Content-Type': 'application/json' },
      body: JSON.stringify({ html })
    });
    const saveData = await saveRes.json();
    if (!saveData.success) throw new Error('Gagal simpan: ' + saveData.error);

    setProgress(100, 'Selesai!');
    setStep(3);
    showStatus('ok','<strong>✓ Jadwal berhasil disimpan!</strong><br>Mengalihkan ke halaman jadwal...');
    btn.classList.add('success'); btn.innerHTML = '✓ Berhasil — Mengalihkan...';
    setTimeout(() => { window.location.href = 'index.php'; }, 1800);

  } catch(err) {
    hideProgress(); setStep(1);
    showStatus('error','<strong>Gagal.</strong><br>'+err.message);
    btn.disabled = false;
  }
}

function fileToBase64(file) {
  return new Promise((res,rej)=>{
    const r=new FileReader();
    r.onload=()=>res(r.result.split(',')[1]);
    r.onerror=()=>rej(new Error('Gagal membaca file.'));
    r.readAsDataURL(file);
  });
}
</script>
</body>
</html>
