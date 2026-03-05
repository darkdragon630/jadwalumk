<?php
require_once __DIR__ . '/config/database.php';

$db     = getDB();
$keyRow = $db->query("SELECT `value` FROM settings WHERE `key`='anthropic_api_key'")->fetch();
$hasKey = (bool)$keyRow;
$error  = '';
$processing = false;

// ── Simpan API key ──
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['save_key'])) {
    $key = trim($_POST['api_key'] ?? '');
    if (!str_starts_with($key, 'sk-ant-')) {
        $error = 'Format API key tidak valid (harus diawali sk-ant-...)';
    } else {
        $db->prepare("INSERT INTO settings (`key`,`value`) VALUES ('anthropic_api_key',?) ON DUPLICATE KEY UPDATE `value`=?")
           ->execute([$key, $key]);
        $hasKey = true;
        $keyRow = ['value' => $key];
        header('Location: upload.php');
        exit;
    }
}

// ── Proses upload PDF → Anthropic → simpan HTML ──
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_FILES['pdf'])) {
    $processing = true;

    do {
        // Validasi file
        if ($_FILES['pdf']['error'] !== UPLOAD_ERR_OK) {
            $error = 'Upload gagal. Error code: ' . $_FILES['pdf']['error']; break;
        }
        if ($_FILES['pdf']['type'] !== 'application/pdf') {
            $error = 'Harap upload file PDF.'; break;
        }
        if ($_FILES['pdf']['size'] > 10 * 1024 * 1024) {
            $error = 'File terlalu besar (maks 10MB).'; break;
        }

        // Ambil API key
        $keyRow2 = $db->query("SELECT `value` FROM settings WHERE `key`='anthropic_api_key'")->fetch();
        if (!$keyRow2) { $error = 'API key belum diset.'; break; }
        $apiKey = $keyRow2['value'];

        // PDF → base64
        $pdfData = file_get_contents($_FILES['pdf']['tmp_name']);
        $b64     = base64_encode($pdfData);

        $prompt = 'Kamu diberikan sebuah file PDF berisi jadwal kuliah mahasiswa dari Universitas Muria Kudus (UMK), Fakultas Teknik.

TUGASMU:
Baca isi PDF ini secara menyeluruh, lalu konversi seluruh isinya menjadi satu halaman HTML yang bersih dan rapi — dengan tampilan yang MIRIP dengan dokumen PDF aslinya.

KETENTUAN TAMPILAN:
- Gunakan font "Plus Jakarta Sans" dari Google Fonts
- Warna tema: navy dark (#0d1f3c) untuk header, emas (#d4a030) untuk aksen
- Background halaman: #f0f2f7
- Kartu/section berbackground putih dengan border-radius dan shadow halus
- Header halaman berisi: logo "UMK", nama universitas, fakultas, nama mahasiswa, NIM, prodi, semester, dosen PA, total SKS
- Tabel jadwal harus menampilkan semua kolom dari PDF: No, Kelas, Kode MK, Nama MK, Dosen, SKS, dan kolom hari (Sn/Sl/Rb/Km/Jm/Sb/Mg)
- Jadwal yang ada di kolom hari tampilkan sebagai "pill" berwarna dengan jam dan kode ruang
- Setiap baris matakuliah punya warna pill berbeda (gunakan palet warna yang kontras dan menarik)
- Jika nama mengandung "Praktikum", tambahkan badge kecil "PRAKTIKUM"
- Tambahkan section "Ringkasan Mingguan" — grid 6 kolom (Senin–Sabtu), tiap kolom berisi kartu jadwal hari itu
- Footer: tanggal cetak dari PDF + nama Dosen PA
- Tambahkan link "Upload Jadwal Baru" yang mengarah ke upload.php
- Responsive untuk mobile

KETENTUAN DATA:
- Salin SEMUA data persis dari PDF — nama, NIM, kode MK, nama MK, dosen, jam, ruang — tanpa dikarang
- Baca kolom hari tabel dari kiri ke kanan: Sn=Senin, Sl=Selasa, Rb=Rabu, Km=Kamis, Jm=Jumat, Sb=Sabtu, Mg=Minggu
- Kolom hari kosong = tidak ada jadwal di hari itu
- Jumlah baris tabel HARUS sama dengan jumlah matakuliah di PDF
- Ringkasan Mingguan harus konsisten dengan data tabel

OUTPUT:
Kembalikan HANYA satu file HTML lengkap, dari <!DOCTYPE html> sampai </html>.
Tidak ada markdown, tidak ada backtick, tidak ada penjelasan teks di luar HTML.';

        // Panggil Anthropic API (server-side, tidak ada CORS)
        $payload = json_encode([
            'model'      => 'claude-haiku-4-5-20251001',
            'max_tokens' => 8000,
            'messages'   => [[
                'role'    => 'user',
                'content' => [
                    [
                        'type'   => 'document',
                        'source' => [
                            'type'       => 'base64',
                            'media_type' => 'application/pdf',
                            'data'       => $b64,
                        ],
                    ],
                    [
                        'type' => 'text',
                        'text' => $prompt,
                    ],
                ],
            ]],
        ]);

        $ch = curl_init('https://api.anthropic.com/v1/messages');
        curl_setopt_array($ch, [
            CURLOPT_RETURNTRANSFER => true,
            CURLOPT_POST           => true,
            CURLOPT_POSTFIELDS     => $payload,
            CURLOPT_TIMEOUT        => 120,
            CURLOPT_HTTPHEADER     => [
                'Content-Type: application/json',
                'x-api-key: ' . $apiKey,
                'anthropic-version: 2023-06-01',
            ],
        ]);

        $resp     = curl_exec($ch);
        $httpCode = curl_getinfo($ch, CURLINFO_HTTP_CODE);
        $curlErr  = curl_error($ch);
        curl_close($ch);

        if ($curlErr) { $error = 'cURL error: ' . $curlErr; break; }

        $result = json_decode($resp, true);

        if ($httpCode !== 200) {
            $msg = $result['error']['message'] ?? "HTTP $httpCode";
            $error = "Anthropic API error: $msg"; break;
        }

        $html = $result['content'][0]['text'] ?? '';
        // Bersihkan markdown wrapper
        $html = trim($html);
        $html = preg_replace('/^```html\s*/i', '', $html);
        $html = preg_replace('/^```\s*/i',     '', $html);
        $html = preg_replace('/\s*```$/i',     '', $html);
        $html = trim($html);

        if (stripos($html, '<html') === false && stripos($html, '<!doctype') === false) {
            $error = 'Output bukan HTML valid. Respons: ' . substr($html, 0, 300); break;
        }

        // Simpan ke DB
        $db->prepare("INSERT INTO settings (`key`,`value`) VALUES ('jadwal_html',?) ON DUPLICATE KEY UPDATE `value`=?")
           ->execute([$html, $html]);

        // Redirect ke halaman jadwal
        header('Location: index.php');
        exit;

    } while (false);

    $processing = false;
}
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
:root{--navy:#0d1f3c;--gold:#d4a030;--gold-light:#f5c842;--bg:#f0f2f7;--white:#fff;--border:#e2e6f0;--text:#1a2540;--muted:#7a84a0;--green:#1e7a45;--red:#9c2c2c}
body{font-family:'Plus Jakarta Sans',sans-serif;background:var(--bg);color:var(--text);min-height:100vh;display:flex;align-items:center;justify-content:center;padding:32px 16px}
.card{background:var(--white);border:1px solid var(--border);border-radius:24px;padding:44px 40px;max-width:500px;width:100%;box-shadow:0 8px 48px rgba(13,31,60,.10);animation:up .4s ease both}
@keyframes up{from{opacity:0;transform:translateY(16px)}to{opacity:1;transform:translateY(0)}}
.top{display:flex;align-items:center;gap:14px;margin-bottom:24px}
.logo{width:46px;height:46px;background:var(--navy);border-radius:12px;display:flex;align-items:center;justify-content:center;font-size:15px;font-weight:800;color:var(--gold);flex-shrink:0}
.top-text h1{font-size:17px;font-weight:800;color:var(--navy)}
.top-text p{font-size:12px;color:var(--muted);margin-top:2px}
.back{margin-left:auto;font-size:12px;font-weight:600;color:var(--muted);text-decoration:none;display:flex;align-items:center;gap:5px}
.badge{display:inline-flex;align-items:center;gap:6px;font-size:11px;font-weight:700;padding:5px 12px;border-radius:20px;margin-bottom:18px}
.badge.ok{background:#edfaf3;border:1px solid #a0e0c0;color:#0d5c32}
.badge.warn{background:#fff8e0;border:1px solid #f5c842;color:#7a5800}
.api-sec{margin-bottom:16px}
.api-lbl{font-size:11px;font-weight:700;color:var(--muted);text-transform:uppercase;letter-spacing:.08em;margin-bottom:7px;display:flex;align-items:center;justify-content:space-between}
.api-lbl a{font-size:11px;font-weight:700;color:#1a73e8;text-decoration:none;text-transform:none;letter-spacing:0}
.row{display:flex;gap:8px}
.inp{flex:1;padding:11px 14px;border:1.5px solid var(--border);border-radius:10px;font-family:'Plus Jakarta Sans',sans-serif;font-size:13px;color:var(--text);background:var(--bg);outline:none}
.inp:focus{border-color:var(--gold);background:var(--white)}
.savebtn{padding:11px 16px;background:var(--navy);color:#fff;border:none;border-radius:10px;font-family:'Plus Jakarta Sans',sans-serif;font-size:12px;font-weight:700;cursor:pointer;white-space:nowrap}
.hint{background:#f0f5ff;border:1px solid #c8d8ff;border-radius:10px;padding:11px 14px;margin-top:10px;font-size:12px;color:#1a3a8f;line-height:1.7}
.hint strong{display:block;margin-bottom:3px}
.hint ol{padding-left:16px}
hr{border:none;border-top:1px solid var(--border);margin:18px 0}
.dz{border:2px dashed var(--border);border-radius:16px;padding:36px 24px;text-align:center;cursor:pointer;background:var(--bg);transition:all .2s;position:relative}
.dz:hover{border-color:var(--gold);background:#fdf8ee}
.dz input{position:absolute;inset:0;opacity:0;cursor:pointer;width:100%;height:100%}
.dz-icon{width:48px;height:48px;background:var(--navy);border-radius:13px;display:flex;align-items:center;justify-content:center;margin:0 auto 12px}
.dz-title{font-size:14px;font-weight:700;color:var(--text);margin-bottom:3px}
.dz-sub{font-size:12px;color:var(--muted)}
.dz-sub span{color:var(--navy);font-weight:600}
.selected-file{display:flex;align-items:center;gap:10px;background:#f0f5ff;border:1px solid #c8d8ff;border-radius:10px;padding:12px 14px;margin-top:10px}
.sf-icon{width:30px;height:30px;background:var(--navy);border-radius:8px;display:flex;align-items:center;justify-content:center;flex-shrink:0}
.sf-name{font-size:13px;font-weight:700;color:var(--text)}
.sf-size{font-size:11px;color:var(--muted);margin-top:1px}
.btn{width:100%;padding:15px;border-radius:12px;border:none;cursor:pointer;font-family:'Plus Jakarta Sans',sans-serif;font-size:14px;font-weight:700;background:var(--navy);color:#fff;margin-top:16px;display:flex;align-items:center;justify-content:center;gap:8px;transition:all .2s}
.btn:hover{background:#1a3260;transform:translateY(-1px)}
.btn:disabled{opacity:.5;cursor:not-allowed;transform:none}
.error-box{background:#fff0f0;border:1px solid #ffc0c0;color:#8b0000;border-radius:12px;padding:14px 16px;font-size:13px;margin-top:14px;line-height:1.6}
.loading-overlay{display:none;position:fixed;inset:0;background:rgba(13,31,60,.7);z-index:999;align-items:center;justify-content:center;flex-direction:column;gap:20px}
.loading-overlay.show{display:flex}
.spinner{width:48px;height:48px;border:4px solid rgba(212,160,48,.3);border-top-color:var(--gold);border-radius:50%;animation:spin 1s linear infinite}
@keyframes spin{to{transform:rotate(360deg)}}
.loading-text{color:#fff;font-family:'Plus Jakarta Sans',sans-serif;font-size:14px;font-weight:600}
.loading-sub{color:rgba(255,255,255,.6);font-family:'Plus Jakarta Sans',sans-serif;font-size:12px;margin-top:-10px}
</style>
</head>
<body>

<!-- Loading overlay — muncul saat proses ke Anthropic -->
<div class="loading-overlay" id="loadingOverlay">
  <div class="spinner"></div>
  <div class="loading-text">Membaca PDF dengan Claude AI...</div>
  <div class="loading-sub">Proses ini membutuhkan ~20–60 detik</div>
</div>

<div class="card">
  <div class="top">
    <div class="logo">UMK</div>
    <div class="top-text">
      <h1>Upload Jadwal</h1>
      <p>Universitas Muria Kudus — Fakultas Teknik</p>
    </div>
    <a href="index.php" class="back">
      <svg width="13" height="13" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.5"><polyline points="15 18 9 12 15 6"/></svg>
      Kembali
    </a>
  </div>

  <?php if ($hasKey): ?>
  <div class="badge ok">
    <svg width="11" height="11" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.5"><polyline points="20 6 9 17 4 12"/></svg>
    Claude (Anthropic) API key tersimpan
  </div>
  <?php else: ?>
  <div class="badge warn">
    ⚠ Set Anthropic API key dulu
  </div>
  <?php endif; ?>

  <?php if (!$hasKey): ?>
  <!-- FORM SIMPAN API KEY -->
  <div class="api-sec">
    <div class="api-lbl">
      Anthropic API Key
      <a href="https://console.anthropic.com/settings/keys" target="_blank">Buka Console →</a>
    </div>
    <form method="POST">
      <div class="row">
        <input type="password" name="api_key" class="inp" placeholder="sk-ant-api03-..." autocomplete="off" required/>
        <button type="submit" name="save_key" class="savebtn">Simpan</button>
      </div>
    </form>
    <div class="hint">
      <strong>🔑 Cara dapat API key:</strong>
      <ol>
        <li>Klik "Buka Console →" di atas</li>
        <li>Login → klik <b>"Create Key"</b></li>
        <li>Copy key (sk-ant-...) → paste di sini → Simpan</li>
      </ol>
      <div style="margin-top:8px;padding-top:8px;border-top:1px solid #c8d8ff;font-size:11px;color:#1a3a8f">
        ✓ Free tier: 5 request/menit · Model: Claude Haiku (cepat &amp; akurat)
      </div>
    </div>
  </div>
  <hr>
  <?php else: ?>
  <p style="font-size:11px;color:var(--muted);margin-bottom:14px">
    <a href="#" onclick="document.getElementById('changeKey').style.display='block';this.style.display='none';return false" style="color:var(--muted)">Ganti API key</a>
  </p>
  <div id="changeKey" style="display:none;margin-bottom:16px">
    <form method="POST">
      <div class="row">
        <input type="password" name="api_key" class="inp" placeholder="sk-ant-api03-..." autocomplete="off" required/>
        <button type="submit" name="save_key" class="savebtn">Simpan</button>
      </div>
    </form>
  </div>
  <hr>
  <?php endif; ?>

  <?php if ($error): ?>
  <div class="error-box"><strong>✗ Gagal.</strong><br><?= htmlspecialchars($error) ?></div>
  <?php endif; ?>

  <!-- FORM UPLOAD PDF -->
  <form method="POST" enctype="multipart/form-data" id="uploadForm">
    <div class="dz" onclick="document.getElementById('pdfInput').click()">
      <input type="file" name="pdf" id="pdfInput" accept=".pdf" style="display:none" onchange="showFile(this)"/>
      <div class="dz-icon">
        <svg width="22" height="22" viewBox="0 0 24 24" fill="none" stroke="#d4a030" stroke-width="2"><path d="M14 2H6a2 2 0 00-2 2v16a2 2 0 002 2h12a2 2 0 002-2V8z"/><polyline points="14 2 14 8 20 8"/><line x1="12" y1="18" x2="12" y2="12"/><line x1="9" y1="15" x2="15" y2="15"/></svg>
      </div>
      <div class="dz-title">Klik untuk pilih PDF jadwal</div>
      <div class="dz-sub">atau seret file ke sini · <span>maks 10MB</span></div>
    </div>

    <div class="selected-file" id="selectedFile" style="display:none">
      <div class="sf-icon">
        <svg width="15" height="15" viewBox="0 0 24 24" fill="none" stroke="#d4a030" stroke-width="2"><path d="M14 2H6a2 2 0 00-2 2v16a2 2 0 002 2h12a2 2 0 002-2V8z"/><polyline points="14 2 14 8 20 8"/></svg>
      </div>
      <div>
        <div class="sf-name" id="sfName">–</div>
        <div class="sf-size" id="sfSize">–</div>
      </div>
    </div>

    <button type="submit" class="btn" id="submitBtn" <?= $hasKey ? '' : 'disabled' ?> onclick="startLoading()">
      <svg width="14" height="14" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.5"><path d="M21 15v4a2 2 0 01-2 2H5a2 2 0 01-2-2v-4"/><polyline points="17 8 12 3 7 8"/><line x1="12" y1="3" x2="12" y2="15"/></svg>
      Konversi PDF dengan Claude AI
    </button>
  </form>
</div>

<script>
function showFile(input) {
  if (!input.files[0]) return;
  const f = input.files[0];
  document.getElementById('sfName').textContent = f.name;
  document.getElementById('sfSize').textContent = (f.size/1024).toFixed(1) + ' KB';
  document.getElementById('selectedFile').style.display = 'flex';
}
function startLoading() {
  const f = document.getElementById('pdfInput').files[0];
  if (!f) { alert('Pilih file PDF terlebih dahulu.'); return false; }
  document.getElementById('loadingOverlay').classList.add('show');
  document.getElementById('submitBtn').disabled = true;
}

// Drag & drop
const dz = document.querySelector('.dz');
dz.addEventListener('dragover', e => { e.preventDefault(); dz.style.borderColor='#d4a030'; });
dz.addEventListener('dragleave', () => dz.style.borderColor='');
dz.addEventListener('drop', e => {
  e.preventDefault(); dz.style.borderColor='';
  const f = e.dataTransfer.files[0];
  if (f?.type === 'application/pdf') {
    const dt = new DataTransfer();
    dt.items.add(f);
    document.getElementById('pdfInput').files = dt.files;
    showFile(document.getElementById('pdfInput'));
  } else alert('Harap upload file PDF.');
});
</script>
</body>
</html>
