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
*{margin:0;padding:0;box-sizing:border-box}
:root{--navy:#0d1f3c;--gold:#d4a030;--gl:#f5c842;--bg:#f0f2f7;--white:#fff;--border:#e2e6f0;--text:#1a2540;--muted:#7a84a0;--green:#1e7a45;--red:#9c2c2c}
body{font-family:'Plus Jakarta Sans',sans-serif;background:var(--bg);color:var(--text);min-height:100vh;display:flex;flex-direction:column;align-items:center;justify-content:center;padding:32px 16px}
.card{background:var(--white);border:1px solid var(--border);border-radius:24px;padding:44px 40px;max-width:520px;width:100%;box-shadow:0 8px 48px rgba(13,31,60,.10);animation:up .45s ease both}
@keyframes up{from{opacity:0;transform:translateY(18px)}to{opacity:1;transform:translateY(0)}}
.top{display:flex;align-items:center;gap:14px;margin-bottom:24px}
.logo{width:46px;height:46px;background:var(--navy);border-radius:12px;display:flex;align-items:center;justify-content:center;font-size:15px;font-weight:800;color:var(--gold);flex-shrink:0}
.top-text h1{font-size:17px;font-weight:800;color:var(--navy)}
.top-text p{font-size:12px;color:var(--muted);margin-top:2px}
.back-link{margin-left:auto;font-size:12px;font-weight:600;color:var(--muted);text-decoration:none;display:flex;align-items:center;gap:5px}
.badge{display:inline-flex;align-items:center;gap:6px;font-size:11px;font-weight:700;padding:5px 12px;border-radius:20px;margin-bottom:20px}
.badge.green{background:#edfaf3;border:1px solid #a0e0c0;color:#0d5c32}
.badge.blue{background:#e8f0fe;border:1px solid #aac4ff;color:#1a3a8f}
.api-section{margin-bottom:18px}
.api-label{font-size:11px;font-weight:700;color:var(--muted);text-transform:uppercase;letter-spacing:.08em;margin-bottom:7px;display:flex;align-items:center;justify-content:space-between}
.api-label a{font-size:11px;font-weight:700;color:#1a73e8;text-decoration:none;text-transform:none;letter-spacing:0}
.api-input-wrap{display:flex;gap:8px}
.api-input{flex:1;padding:11px 14px;border:1.5px solid var(--border);border-radius:10px;font-family:'Plus Jakarta Sans',sans-serif;font-size:13px;color:var(--text);background:var(--bg);outline:none;transition:border .2s}
.api-input:focus{border-color:var(--gold);background:var(--white)}
.api-input.saved{border-color:#a0e0c0;background:#f0fdf7}
.api-save-btn{padding:11px 16px;background:var(--navy);color:#fff;border:none;border-radius:10px;font-family:'Plus Jakarta Sans',sans-serif;font-size:12px;font-weight:700;cursor:pointer;white-space:nowrap}
.api-status{font-size:11px;margin-top:6px;min-height:16px}
.api-status.ok{color:var(--green)}
.api-status.err{color:var(--red)}
.hint{background:#f0f5ff;border:1px solid #c8d8ff;border-radius:10px;padding:12px 14px;margin-bottom:18px;font-size:12px;color:#1a3a8f;line-height:1.7}
.hint strong{display:block;margin-bottom:4px}
.hint ol{padding-left:16px}
.divider{border:none;border-top:1px solid var(--border);margin:18px 0}
.drop-zone{border:2px dashed var(--border);border-radius:16px;padding:36px 24px;text-align:center;cursor:pointer;background:var(--bg);position:relative;transition:all .2s}
.drop-zone.drag{border-color:var(--gold);background:#fdf8ee}
.drop-zone input{position:absolute;inset:0;opacity:0;cursor:pointer;width:100%;height:100%}
.dz-icon{width:48px;height:48px;background:var(--navy);border-radius:13px;display:flex;align-items:center;justify-content:center;margin:0 auto 12px}
.dz-title{font-size:14px;font-weight:700;color:var(--text);margin-bottom:3px}
.dz-sub{font-size:12px;color:var(--muted)}
.dz-sub span{color:var(--navy);font-weight:600}
.fp{display:none;align-items:center;gap:12px;background:#f0f5ff;border:1px solid #c8d8ff;border-radius:12px;padding:13px 15px;margin-top:12px}
.fp.show{display:flex}
.fp-icon{width:34px;height:34px;background:var(--navy);border-radius:9px;display:flex;align-items:center;justify-content:center;flex-shrink:0}
.fp-name{font-size:13px;font-weight:700;color:var(--text)}
.fp-size{font-size:11px;color:var(--muted);margin-top:2px}
.fp-rm{margin-left:auto;background:none;border:none;cursor:pointer;color:var(--muted);padding:4px}
.btn{width:100%;padding:15px;border-radius:12px;border:none;cursor:pointer;font-family:'Plus Jakarta Sans',sans-serif;font-size:14px;font-weight:700;background:var(--navy);color:#fff;margin-top:18px;transition:all .2s;display:flex;align-items:center;justify-content:center;gap:8px}
.btn:hover:not(:disabled){background:#1a3260;transform:translateY(-1px);box-shadow:0 6px 20px rgba(13,31,60,.2)}
.btn:disabled{opacity:.5;cursor:not-allowed}
.btn.success{background:var(--green)}
.status-box{display:none;margin-top:16px;border-radius:12px;padding:14px 16px;font-size:13px;line-height:1.6;word-break:break-word}
.status-box.show{display:block}
.status-box.error{background:#fff0f0;border:1px solid #ffc0c0;color:#8b0000}
.status-box.ok{background:#edfaf3;border:1px solid #a0e0c0;color:#0d5c32}
.prog-wrap{display:none;margin-top:14px}
.prog-wrap.show{display:block}
.prog-label{font-size:12px;font-weight:600;color:var(--muted);margin-bottom:7px;display:flex;justify-content:space-between}
.prog-bar{height:6px;background:var(--border);border-radius:3px;overflow:hidden}
.prog-fill{height:100%;background:linear-gradient(90deg,var(--gold),var(--gl));border-radius:3px;transition:width .4s ease;width:0%}
.steps{margin-top:22px;border-top:1px solid var(--border);padding-top:18px;display:flex}
.step{flex:1;text-align:center;position:relative}
.step:not(:last-child)::after{content:'';position:absolute;top:14px;left:60%;width:80%;height:2px;background:var(--border);z-index:0}
.step.done:not(:last-child)::after{background:var(--gold)}
.step-c{width:28px;height:28px;border-radius:50%;background:var(--border);color:var(--muted);font-size:11px;font-weight:700;display:flex;align-items:center;justify-content:center;margin:0 auto 6px;position:relative;z-index:1;transition:all .3s}
.step.done .step-c{background:var(--gold);color:var(--navy)}
.step.active .step-c{background:var(--navy);color:#fff;box-shadow:0 0 0 3px rgba(13,31,60,.15)}
.step-lbl{font-size:10px;font-weight:600;color:var(--muted);text-transform:uppercase;letter-spacing:.06em}
.step.done .step-lbl,.step.active .step-lbl{color:var(--text)}
</style>
</head>
<body>
<div class="card">
  <div class="top">
    <div class="logo">UMK</div>
    <div class="top-text"><h1>Upload Jadwal Baru</h1><p>Universitas Muria Kudus — Fakultas Teknik</p></div>
    <a href="index.php" class="back-link">
      <svg width="13" height="13" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.5"><polyline points="15 18 9 12 15 6"/></svg>
      Kembali
    </a>
  </div>

  <?php if ($hasKey): ?>
  <div class="badge green">
    <svg width="11" height="11" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.5"><polyline points="20 6 9 17 4 12"/></svg>
    API key tersimpan — siap dipakai
  </div>
  <?php else: ?>
  <div class="badge blue">
    <svg width="11" height="11" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.5"><circle cx="12" cy="12" r="10"/><line x1="12" y1="8" x2="12" y2="12"/><line x1="12" y1="16" x2="12.01" y2="16"/></svg>
    Set API key Gemini dulu
  </div>
  <?php endif; ?>

  <div class="api-section" <?= $hasKey ? 'style="display:none"' : '' ?> id="apiSection">
    <div class="api-label">
      Google Gemini API Key
      <a href="https://aistudio.google.com/app/apikey" target="_blank">Dapatkan gratis →</a>
    </div>
    <div class="api-input-wrap">
      <input type="password" id="apiKeyInput" class="api-input" placeholder="AIzaSy..." autocomplete="off"/>
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
      <input type="password" id="apiKeyInput2" class="api-input" placeholder="AIzaSy..." autocomplete="off"/>
      <button class="api-save-btn" onclick="saveApiKey2()">Simpan</button>
    </div>
    <div class="api-status" id="apiStatus2"></div>
  </div>
  <?php endif; ?>

  <hr class="divider">

  <div class="steps">
    <div class="step active" id="s1"><div class="step-c">1</div><div class="step-lbl">Pilih PDF</div></div>
    <div class="step" id="s2"><div class="step-c">2</div><div class="step-lbl">Generate</div></div>
    <div class="step" id="s3"><div class="step-c">3</div><div class="step-lbl">Tersimpan</div></div>
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
    <div class="fp" id="filePreview">
      <div class="fp-icon">
        <svg width="17" height="17" viewBox="0 0 24 24" fill="none" stroke="#d4a030" stroke-width="2"><path d="M14 2H6a2 2 0 00-2 2v16a2 2 0 002 2h12a2 2 0 002-2V8z"/><polyline points="14 2 14 8 20 8"/></svg>
      </div>
      <div><div class="fp-name" id="fpName">–</div><div class="fp-size" id="fpSize">–</div></div>
      <button class="fp-rm" id="fpRm">
        <svg width="15" height="15" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.5"><line x1="18" y1="6" x2="6" y2="18"/><line x1="6" y1="6" x2="18" y2="18"/></svg>
      </button>
    </div>
  </div>

  <div class="prog-wrap" id="progWrap">
    <div class="prog-label"><span id="progLabel">Memproses...</span><span id="progPct">0%</span></div>
    <div class="prog-bar"><div class="prog-fill" id="progFill"></div></div>
  </div>
  <div class="status-box" id="statusBox"></div>

  <button class="btn" id="uploadBtn" disabled onclick="processUpload()">
    <svg width="15" height="15" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.5"><path d="M21 15v4a2 2 0 01-2 2H5a2 2 0 01-2-2v-4"/><polyline points="17 8 12 3 7 8"/><line x1="12" y1="3" x2="12" y2="15"/></svg>
    Generate &amp; Simpan Jadwal
  </button>
</div>

<script>
const HAS_KEY = <?= $hasKey ? 'true' : 'false' ?>;
const GEMINI_MODEL = 'gemini-2.5-pro';
let selectedFile = null;

async function saveApiKey() { await _saveKey(document.getElementById('apiKeyInput'), document.getElementById('apiStatus'), 'apiSection'); }
async function saveApiKey2() { await _saveKey(document.getElementById('apiKeyInput2'), document.getElementById('apiStatus2'), 'changeKey'); }
async function _saveKey(inp, statusEl, hideId) {
  const key = inp ? inp.value.trim() : '';
  if (!key.startsWith('AIza')) { _st(statusEl,'err','✗ Format tidak valid'); return; }
  const r = await fetch('api/jadwal.php?action=save_key',{method:'POST',headers:{'Content-Type':'application/json'},body:JSON.stringify({key})});
  const d = await r.json();
  if (d.success) {
    _st(statusEl,'ok','✓ Tersimpan di server');
    const el = document.getElementById(hideId); if(el) el.style.display='none';
    updateBtn(true);
  } else _st(statusEl,'err','✗ '+d.error);
}
function _st(el,t,m){if(el){el.className='api-status '+t;el.textContent=m;}}

const dz=document.getElementById('dropZone'), fi=document.getElementById('fileInput');
dz.addEventListener('dragover',e=>{e.preventDefault();dz.classList.add('drag');});
dz.addEventListener('dragleave',()=>dz.classList.remove('drag'));
dz.addEventListener('drop',e=>{e.preventDefault();dz.classList.remove('drag');const f=e.dataTransfer.files[0];if(f?.type==='application/pdf')setFile(f);else showStatus('error','Harap upload file PDF.');});
fi.addEventListener('change',()=>{if(fi.files[0])setFile(fi.files[0]);});
document.getElementById('fpRm').addEventListener('click',clearFile);

function setFile(f){selectedFile=f;document.getElementById('fpName').textContent=f.name;document.getElementById('fpSize').textContent=(f.size/1024).toFixed(1)+' KB';document.getElementById('filePreview').classList.add('show');clearStatus();setStep(1);updateBtn();}
function clearFile(){selectedFile=null;fi.value='';document.getElementById('filePreview').classList.remove('show');clearStatus();setStep(0);updateBtn();}
function updateBtn(k){const ok=k!==undefined?k:HAS_KEY;document.getElementById('uploadBtn').disabled=!(selectedFile&&ok);}
function setStep(n){[1,2,3].forEach(i=>{const e=document.getElementById('s'+i);e.classList.remove('active','done');if(i<n+1)e.classList.add('done');if(i===n+1)e.classList.add('active');});}
function showStatus(t,m){const e=document.getElementById('statusBox');e.className='status-box show '+t;e.innerHTML=m;}
function clearStatus(){document.getElementById('statusBox').className='status-box';}
function setProgress(p,l){document.getElementById('progWrap').classList.add('show');document.getElementById('progFill').style.width=p+'%';document.getElementById('progPct').textContent=Math.round(p)+'%';if(l)document.getElementById('progLabel').textContent=l;}
function hideProgress(){document.getElementById('progWrap').classList.remove('show');}
updateBtn();

async function processUpload() {
  if (!selectedFile) return;
  const btn = document.getElementById('uploadBtn');
  btn.disabled = true; clearStatus(); setStep(2);
  setProgress(10, 'Membaca PDF...');

  try {
    const keyRes = await fetch('api/jadwal.php?action=get_key');
    const keyData = await keyRes.json();
    if (!keyData.success) throw new Error('API key belum diset.');
    const apiKey = keyData.key;

    const base64 = await fileToBase64(selectedFile);
    setProgress(25, 'Mengirim ke Gemini...');

    const PROMPT = `Kamu diberikan file PDF jadwal kuliah dari Universitas Muria Kudus (UMK).

Konversi PDF ini menjadi halaman HTML yang tampil PERSIS seperti PDF-nya — struktur, layout, dan semua data harus sama.

DESAIN:
- Font: Plus Jakarta Sans (import dari Google Fonts)
- Warna: background #f0f2f7, card putih, header navy #0d1f3c, aksen gold #d4a030
- Header halaman: logo "UMK" (gold di navy), nama universitas, prodi, semester
- Kartu info mahasiswa: nama, NIM, prodi, dosen PA, total SKS
- Tabel jadwal lengkap dengan kolom: No, Kls, Kode, Nama MK, Dosen, SKS, lalu kolom hari Sn Sl Rb Km Jm Sb Mg
- Sel jadwal yang ADA: kotak berwarna dengan jam dan kode ruang (warna berbeda tiap baris)
- Sel jadwal KOSONG: titik "·" abu-abu
- Di bawah tabel: ringkasan mingguan (grid 6 kolom per hari, kartu per matakuliah)
- Footer: tanggal cetak dan nama dosen PA
- Tombol "Upload Jadwal Baru" dengan href="upload.php"
- Responsive mobile

DATA:
- Salin SEMUA data dari PDF persis apa adanya: nama, NIM, prodi, dosen PA, SKS, semester, tanggal cetak
- Salin SEMUA baris matakuliah, tidak boleh ada yang terlewat
- Jadwal hari dibaca dari kolom tabel PDF (Sn=Senin, Sl=Selasa, Rb=Rabu, Km=Kamis, Jm=Jumat, Sb=Sabtu, Mg=Minggu)

OUTPUT: Hanya HTML dari <!DOCTYPE html> sampai </html>. Tanpa markdown, tanpa penjelasan.`;

    const apiUrl = `https://generativelanguage.googleapis.com/v1beta/models/${GEMINI_MODEL}:generateContent?key=${apiKey}`;
    const response = await fetch(apiUrl, {
      method: 'POST',
      headers: {'Content-Type':'application/json'},
      body: JSON.stringify({
        contents: [{parts: [
          {inline_data: {mime_type:'application/pdf', data: base64}},
          {text: PROMPT}
        ]}],
        generationConfig: {temperature: 0, maxOutputTokens: 16000}
      })
    });

    setProgress(70, 'Menerima HTML dari Gemini...');

    if (!response.ok) {
      const err = await response.json().catch(()=>({}));
      const msg = err.error?.message || `HTTP ${response.status}`;
      if (msg.includes('quota') || msg.includes('RESOURCE_EXHAUSTED'))
        throw new Error('Quota Gemini habis. Coba lagi besok atau ganti API key.');
      throw new Error(msg);
    }

    const aiResp = await response.json();
    let html = aiResp.candidates?.[0]?.content?.parts?.[0]?.text || '';

    // Bersihkan markdown wrapper kalau ada
    html = html.trim()
      .replace(/^```html\s*/i,'').replace(/^```\s*/i,'').replace(/\s*```$/i,'').trim();

    if (!html.toLowerCase().includes('<html')) {
      throw new Error('Output bukan HTML valid. Coba upload ulang PDF.');
    }

    setProgress(88, 'Menyimpan ke server...');

    const saveRes = await fetch('api/jadwal.php?action=save_html', {
      method: 'POST',
      headers: {'Content-Type':'application/json'},
      body: JSON.stringify({html})
    });
    const saveData = await saveRes.json();
    if (!saveData.success) throw new Error('Gagal simpan: ' + saveData.error);

    setProgress(100, 'Selesai!');
    setStep(3);
    showStatus('ok','<strong>✓ Jadwal berhasil disimpan!</strong><br>Mengalihkan ke halaman jadwal...');
    btn.classList.add('success'); btn.innerHTML = '✓ Berhasil — Mengalihkan...';
    setTimeout(()=>{ window.location.href='index.php'; }, 1800);

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
