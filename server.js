// ═══════════════════════════════════════════════════════
//  Wasmer Edge – WinterJS Worker
//  Single-file: inline HTML + API proxy ke Anthropic
// ═══════════════════════════════════════════════════════

// ── Inline HTML pages ──────────────────────────────────
const INDEX_HTML = String.raw`<!DOCTYPE html>
<html lang="id">
<head>
<meta charset="UTF-8">
<meta name="viewport" content="width=device-width, initial-scale=1.0">
<title>Jadwal Kuliah</title>
<link href="https://fonts.googleapis.com/css2?family=Plus+Jakarta+Sans:wght@400;500;600;700;800&display=swap" rel="stylesheet">
<style>
* { margin: 0; padding: 0; box-sizing: border-box; }
:root {
  --navy:#0d1f3c; --blue:#1a3a6b; --gold:#d4a030;
  --bg:#f0f2f7; --white:#ffffff; --border:#e2e6f0;
  --text:#1a2540; --muted:#7a84a0;
  --c0:#2563a8; --c1:#16736b; --c2:#7c3d9e; --c3:#b85c00;
  --c4:#1e7a45; --c5:#9c2c2c; --c6:#3d5fa8; --c7:#5a4a9e; --c8:#0d6b7a;
}
body { font-family:'Plus Jakarta Sans',sans-serif; background:var(--bg); color:var(--text); min-height:100vh; font-size:14px; }
.page { max-width:1120px; margin:0 auto; padding:32px 24px 60px; }

/* HEADER */
.header-card {
  background:var(--navy); border-radius:20px; padding:30px 36px;
  display:flex; align-items:center; gap:22px; margin-bottom:18px;
  position:relative; overflow:hidden;
}
.header-card::after {
  content:''; position:absolute; right:-50px; top:-50px;
  width:240px; height:240px; border-radius:50%;
  background:radial-gradient(circle,rgba(212,160,48,.12) 0%,transparent 65%);
  pointer-events:none;
}
.univ-logo {
  width:52px; height:52px; background:var(--gold); border-radius:13px;
  display:flex; align-items:center; justify-content:center;
  font-size:17px; font-weight:800; color:var(--navy); flex-shrink:0;
}
.univ-info h1 { font-size:16px; font-weight:700; color:#fff; line-height:1.35; }
.univ-info p { font-size:11.5px; font-weight:600; color:rgba(212,160,48,.85); margin-top:4px; text-transform:uppercase; letter-spacing:.08em; }
.header-right { margin-left:auto; text-align:right; }
.sem-label { font-size:10.5px; font-weight:600; color:rgba(255,255,255,.4); text-transform:uppercase; letter-spacing:.1em; }
.sem-value { font-size:15px; font-weight:700; color:var(--gold); margin-top:2px; }
.upload-btn {
  display:inline-flex; align-items:center; gap:7px;
  background:rgba(255,255,255,.1); border:1px solid rgba(255,255,255,.2);
  color:#fff; text-decoration:none; font-size:12px; font-weight:600;
  padding:8px 16px; border-radius:8px; margin-top:10px;
  transition:background .2s;
}
.upload-btn:hover { background:rgba(255,255,255,.18); }

/* INFO */
.info-grid { display:grid; grid-template-columns:2fr 1fr 1.5fr 1.5fr .7fr; gap:10px; margin-bottom:26px; }
.info-card { background:var(--white); border:1px solid var(--border); border-radius:13px; padding:15px 18px; }
.lbl { font-size:10px; font-weight:700; color:var(--muted); text-transform:uppercase; letter-spacing:.1em; margin-bottom:5px; }
.val { font-size:13.5px; font-weight:700; color:var(--text); line-height:1.3; }
.info-card.sks-card { background:var(--navy); border-color:var(--navy); display:flex; flex-direction:column; align-items:center; justify-content:center; text-align:center; }
.sks-card .lbl { color:rgba(255,255,255,.4); }
.sks-card .val { font-size:28px; font-weight:800; color:var(--gold); }

/* SEC HEADER */
.sec-h { display:flex; align-items:center; gap:12px; margin-bottom:12px; }
.sec-h h2 { font-size:14px; font-weight:700; white-space:nowrap; }
.sec-line { flex:1; height:1px; background:var(--border); }
.sec-badge { background:var(--navy); color:#fff; font-size:10.5px; font-weight:700; padding:3px 10px; border-radius:20px; }

/* TABLE */
.tbl-wrap { overflow-x:auto; margin-bottom:28px; border-radius:16px; box-shadow:0 1px 18px rgba(13,31,60,.08); }
table { width:100%; border-collapse:collapse; background:var(--white); border-radius:16px; overflow:hidden; min-width:840px; }
thead tr.h1 { background:var(--navy); }
thead th { color:#fff; font-weight:700; font-size:10.5px; text-transform:uppercase; letter-spacing:.07em; white-space:nowrap; padding:13px 14px; text-align:left; border-right:1px solid rgba(255,255,255,.06); }
thead th:last-child { border-right:none; }
thead th.tc { text-align:center; }
thead th.day-th { background:#0f2a56; color:rgba(212,160,48,.85); font-size:10px; text-align:center; padding:13px 6px; }
thead th.sub-kode, thead th.sub-nama { background:#14264e; font-size:9.5px; color:rgba(255,255,255,.4); font-weight:600; padding:7px 14px; }
tbody tr { border-bottom:1px solid var(--border); transition:background .12s; }
tbody tr:last-child { border-bottom:none; }
tbody tr:hover { background:#f5f7fd; }
tbody td { padding:11px 14px; vertical-align:middle; border-right:1px solid var(--border); font-size:13px; }
tbody td:last-child { border-right:none; }
tbody td.tc { text-align:center; }
.num { color:var(--muted); font-weight:700; font-size:12px; }
.kls { display:inline-flex; align-items:center; justify-content:center; width:26px; height:26px; border-radius:7px; background:var(--navy); color:#fff; font-size:12px; font-weight:700; }
.kode-tag { font-size:10px; font-weight:700; color:var(--muted); background:var(--bg); border:1px solid var(--border); padding:2px 6px; border-radius:5px; display:inline-block; margin-bottom:3px; letter-spacing:.03em; }
.mk-name { font-size:13px; font-weight:600; color:var(--text); line-height:1.3; }
.prak-tag { font-size:9px; font-weight:700; text-transform:uppercase; letter-spacing:.06em; color:var(--c1); background:#e6f5f3; padding:2px 6px; border-radius:4px; display:inline-block; margin-top:3px; }
.dosen { font-size:12px; font-weight:500; color:#3a4a6a; line-height:1.4; }
.sks-num { font-size:17px; font-weight:800; color:var(--gold); display:block; text-align:center; }
.sched-pill { display:inline-block; border-radius:8px; padding:6px 8px; text-align:center; min-width:76px; }
.s-time { font-size:11px; font-weight:700; color:#fff; line-height:1.25; }
.s-room { font-size:9.5px; font-weight:500; color:rgba(255,255,255,.72); margin-top:2px; }
td.day-td { text-align:center; padding:7px 5px; }
td.empty { text-align:center; color:#d0d5e5; font-size:14px; }

/* WEEKLY */
.week-grid { display:grid; grid-template-columns:repeat(6,1fr); gap:10px; margin-bottom:32px; }
.day-col { background:var(--white); border:1px solid var(--border); border-radius:13px; overflow:hidden; }
.day-head { background:var(--navy); color:#fff; text-align:center; font-size:11px; font-weight:700; padding:10px 6px; text-transform:uppercase; letter-spacing:.06em; }
.day-body { padding:9px 8px; display:flex; flex-direction:column; gap:7px; min-height:52px; }
.wk-card { border-radius:9px; padding:9px 10px; }
.wt { font-size:9.5px; font-weight:600; color:rgba(255,255,255,.65); margin-bottom:3px; }
.wn { font-size:11.5px; font-weight:700; color:#fff; line-height:1.3; }
.wr { font-size:9.5px; color:rgba(255,255,255,.62); margin-top:3px; }
.no-cls { color:var(--muted); font-size:11px; text-align:center; padding:16px 0; font-style:italic; }

/* FOOTER */
.footer-row { display:flex; justify-content:space-between; align-items:center; flex-wrap:wrap; gap:10px; padding-top:20px; border-top:1px solid var(--border); }
.fl { font-size:12px; color:var(--muted); }
.fl strong { color:var(--text); }
.fr { text-align:right; font-size:12px; color:var(--muted); }
.fr strong { display:block; color:var(--text); font-weight:700; font-size:13px; }

@keyframes up { from{opacity:0;transform:translateY(12px)} to{opacity:1;transform:translateY(0)} }
.header-card{animation:up .4s ease both}
.info-card{animation:up .4s ease both}
.tbl-wrap{animation:up .4s .18s ease both}
.day-col{animation:up .35s ease both}

@media(max-width:760px){
  .info-grid{grid-template-columns:1fr 1fr}
  .sks-card{grid-column:span 2}
  .week-grid{grid-template-columns:repeat(3,1fr)}
  .header-card{flex-wrap:wrap}
  .header-right{margin-left:0;text-align:left}
}
</style>
</head>
<body>
<div class="page" id="app">
  <div style="text-align:center;padding:80px 0;color:var(--muted)">Memuat data jadwal...</div>
</div>

<script>
const COLORS = ['--c0','--c1','--c2','--c3','--c4','--c5','--c6','--c7','--c8'];
const DAYS_KEY = ['sn','sl','rb','km','jm','sb','mg'];
const DAYS_LABEL = ['Sn','Sl','Rb','Km','Jm','Sb','Mg'];
const DAYS_FULL = ['Senin','Selasa','Rabu','Kamis','Jumat','Sabtu'];

const DEFAULT_DATA = {
  mahasiswa: {
    nama: "Muhammad Burhanudin Syaifullah Azmi",
    nim: "202451022",
    prodi: "Teknik Informatika – S1",
    dosenPA: "Ahmad Jazuli, S.Kom., M.Kom",
    sks: 24,
    semester: "Genap 2025/2026"
  },
  matakuliah: [
    { no:1, kelas:"E", kode:"IFT406", nama:"Praktikum Pemrograman Mobile", isPraktikum:true,  dosen:"Aditya Akbar Riadis, Kom., M.Kom.", sks:1, jadwal:{sn:null,sl:null,rb:null,km:null,jm:{jam:"08:00–10:29",ruang:"I.3,04"},sb:null,mg:null} },
    { no:2, kelas:"A", kode:"IFT408", nama:"Pengenalan Pola",               isPraktikum:false, dosen:"Endang Supriyatis, Kom, M.Kom",       sks:2, jadwal:{sn:null,sl:null,rb:{jam:"08:00–09:39",ruang:"J.4,11"},km:null,jm:null,sb:null,mg:null} },
    { no:3, kelas:"B", kode:"IFT410", nama:"Praktikum Pengenalan Pola",     isPraktikum:true,  dosen:"Endang Supriyatis, Kom, M.Kom",       sks:1, jadwal:{sn:{jam:"10:30–12:59",ruang:"I.3,06"},sl:null,rb:null,km:null,jm:null,sb:null,mg:null} },
    { no:4, kelas:"E", kode:"IFT416", nama:"Sistem Basis Data",             isPraktikum:false, dosen:"Dr. Anastasya Latubessys, Kom., M.Cs",sks:2, jadwal:{sn:null,sl:null,rb:{jam:"09:40–11:19",ruang:"J.4,11"},km:null,jm:null,sb:null,mg:null} },
    { no:5, kelas:"F", kode:"IFT418", nama:"Praktikum Sistem Basis Data",   isPraktikum:true,  dosen:"Dr. Anastasya Latubessys, Kom., M.Cs",sks:1, jadwal:{sn:null,sl:null,rb:null,km:{jam:"10:30–12:59",ruang:"I.2,02"},jm:null,sb:null,mg:null} },
    { no:6, kelas:"C", kode:"IFT420", nama:"Manajemen Proyek",              isPraktikum:false, dosen:"Alvin Rainaldy Hakims, Kom., M.Kom.", sks:3, jadwal:{sn:null,sl:null,rb:{jam:"13:00–15:29",ruang:"J.4,10"},km:null,jm:null,sb:null,mg:null} },
    { no:7, kelas:"A", kode:"TIT302", nama:"Pemrograman Web",               isPraktikum:false, dosen:"Wibowo Harry Sugihartos, Kom., M.Kom.",sks:3,jadwal:{sn:null,sl:{jam:"08:00–10:29",ruang:"J.4,11"},rb:null,km:null,jm:null,sb:null,mg:null} },
    { no:8, kelas:"C", kode:"IFT414", nama:"Praktikum Pemrograman Web",     isPraktikum:true,  dosen:"Wibowo Harry Sugihartos, Kom., M.Kom.",sks:1,jadwal:{sn:null,sl:null,rb:null,km:null,jm:{jam:"13:00–15:29",ruang:"I.3,03"},sb:null,mg:null} },
    { no:9, kelas:"F", kode:"IFT404", nama:"Pemrograman Mobile",            isPraktikum:false, dosen:"Aditya Akbar Riadis, Kom., M.Kom.", sks:3, jadwal:{sn:null,sl:null,rb:null,km:{jam:"08:00–10:29",ruang:"J.4,12"},jm:null,sb:null,mg:null} },
  ],
  dicetak: "05 Maret 2026, 09.05"
};

function getColor(idx) {
  return `var(${COLORS[idx % COLORS.length]})`;
}

function buildWeekly(mks) {
  // group by day
  const days = { sn:[], sl:[], rb:[], km:[], jm:[], sb:[] };
  mks.forEach((mk, i) => {
    ['sn','sl','rb','km','jm','sb'].forEach(d => {
      if (mk.jadwal[d]) days[d].push({ ...mk.jadwal[d], nama: mk.nama, kode: mk.kode, color: getColor(i) });
    });
  });
  // sort each day by time
  Object.values(days).forEach(arr => arr.sort((a,b) => a.jam.localeCompare(b.jam)));
  return days;
}

function render(data) {
  const m = data.mahasiswa;
  const mks = data.matakuliah;
  const weekly = buildWeekly(mks);

  // TABLE ROWS
  const rows = mks.map((mk, i) => {
    const color = getColor(i);
    const dayCells = DAYS_KEY.map(d => {
      if (mk.jadwal[d]) {
        return `<td class="day-td"><div class="sched-pill" style="background:${color}"><div class="s-time">${mk.jadwal[d].jam}</div><div class="s-room">${mk.jadwal[d].ruang}</div></div></td>`;
      }
      return `<td class="empty">·</td>`;
    }).join('');
    return `
    <tr>
      <td class="tc num">${mk.no}</td>
      <td class="tc"><span class="kls">${mk.kelas}</span></td>
      <td><span class="kode-tag">${mk.kode}</span></td>
      <td>
        <div class="mk-name">${mk.nama}</div>
        ${mk.isPraktikum ? '<span class="prak-tag">Praktikum</span>' : ''}
      </td>
      <td><div class="dosen">${mk.dosen}</div></td>
      <td><span class="sks-num">${mk.sks}</span></td>
      ${dayCells}
    </tr>`;
  }).join('');

  // WEEKLY COLUMNS
  const dayNames = ['sn','sl','rb','km','jm','sb'];
  const dayLabels = ['Senin','Selasa','Rabu','Kamis','Jumat','Sabtu'];
  const weekCols = dayNames.map((d, di) => {
    const cards = weekly[d].length
      ? weekly[d].map(s => `
        <div class="wk-card" style="background:${s.color}">
          <div class="wt">${s.jam}</div>
          <div class="wn">${s.nama}</div>
          <div class="wr">${s.kode} · ${s.ruang}</div>
        </div>`).join('')
      : '<div class="no-cls">Tidak ada kelas</div>';
    return `
    <div class="day-col">
      <div class="day-head">${dayLabels[di]}</div>
      <div class="day-body">${cards}</div>
    </div>`;
  }).join('');

  const now = new Date();
  const printed = data.dicetak || now.toLocaleDateString('id-ID',{day:'2-digit',month:'long',year:'numeric'});

  document.getElementById('app').innerHTML = `
    <div class="header-card">
      <div class="univ-logo">UMK</div>
      <div class="univ-info">
        <h1>Universitas Muria Kudus — Fakultas Teknik</h1>
        <p>Jadwal Kuliah &nbsp;·&nbsp; ${m.prodi}</p>
      </div>
      <div class="header-right">
        <div class="sem-label">Semester</div>
        <div class="sem-value">${m.semester}</div>
        <a href="upload.html" class="upload-btn">
          <svg width="13" height="13" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.5"><path d="M21 15v4a2 2 0 01-2 2H5a2 2 0 01-2-2v-4"/><polyline points="17 8 12 3 7 8"/><line x1="12" y1="3" x2="12" y2="15"/></svg>
          Upload Jadwal Baru
        </a>
      </div>
    </div>

    <div class="info-grid">
      <div class="info-card"><div class="lbl">Nama Mahasiswa</div><div class="val">${m.nama}</div></div>
      <div class="info-card"><div class="lbl">NIM</div><div class="val">${m.nim}</div></div>
      <div class="info-card"><div class="lbl">Program Studi</div><div class="val">${m.prodi}</div></div>
      <div class="info-card"><div class="lbl">Dosen PA</div><div class="val">${m.dosenPA}</div></div>
      <div class="info-card sks-card"><div class="lbl">Beban SKS</div><div class="val">${m.sks}</div></div>
    </div>

    <div class="sec-h">
      <h2>Daftar Matakuliah</h2>
      <div class="sec-line"></div>
      <div class="sec-badge">${mks.length} Matakuliah</div>
    </div>

    <div class="tbl-wrap">
      <table>
        <thead>
          <tr class="h1">
            <th rowspan="2" class="tc" style="width:38px">No.</th>
            <th rowspan="2" class="tc" style="width:50px">Kelas</th>
            <th class="sub-kode" style="width:76px">Kode</th>
            <th class="sub-nama">Nama Matakuliah</th>
            <th rowspan="2" style="width:185px">Dosen</th>
            <th rowspan="2" class="tc" style="width:46px">SKS</th>
            <th class="day-th" style="width:88px">Sn</th>
            <th class="day-th" style="width:88px">Sl</th>
            <th class="day-th" style="width:88px">Rb</th>
            <th class="day-th" style="width:88px">Km</th>
            <th class="day-th" style="width:88px">Jm</th>
            <th class="day-th" style="width:50px">Sb</th>
            <th class="day-th" style="width:50px">Mg</th>
          </tr>
        </thead>
        <tbody>${rows}</tbody>
      </table>
    </div>

    <div class="sec-h">
      <h2>Ringkasan Mingguan</h2>
      <div class="sec-line"></div>
    </div>
    <div class="week-grid">${weekCols}</div>

    <div class="footer-row">
      <div class="fl">Dicetak: <strong>${printed}</strong> &nbsp;·&nbsp; Sumber: Kanal UMK</div>
      <div class="fr"><strong>${m.dosenPA}</strong>Dosen PA</div>
    </div>
  `;
}

// Load data: localStorage first, then default
let data = DEFAULT_DATA;
try {
  const saved = localStorage.getItem('jadwal_data');
  if (saved) data = JSON.parse(saved);
} catch(e) {}
render(data);
</script>
</body>
</html>
`;

const UPLOAD_HTML = String.raw`<!DOCTYPE html>
<html lang="id">
<head>
<meta charset="UTF-8">
<meta name="viewport" content="width=device-width, initial-scale=1.0">
<title>Upload Jadwal – UMK</title>
<link href="https://fonts.googleapis.com/css2?family=Plus+Jakarta+Sans:wght@400;500;600;700;800&display=swap" rel="stylesheet">
<style>
* { margin:0; padding:0; box-sizing:border-box; }
:root {
  --navy:#0d1f3c; --gold:#d4a030; --gold-light:#f5c842;
  --bg:#f0f2f7; --white:#fff; --border:#e2e6f0;
  --text:#1a2540; --muted:#7a84a0;
  --green:#1e7a45; --red:#9c2c2c;
}
body { font-family:'Plus Jakarta Sans',sans-serif; background:var(--bg); color:var(--text); min-height:100vh; display:flex; flex-direction:column; align-items:center; justify-content:center; padding:32px 16px; }

.card {
  background:var(--white); border:1px solid var(--border); border-radius:24px;
  padding:48px 44px; max-width:520px; width:100%;
  box-shadow:0 8px 48px rgba(13,31,60,.10);
  animation: up .45s ease both;
}
@keyframes up { from{opacity:0;transform:translateY(18px)} to{opacity:1;transform:translateY(0)} }

/* TOP */
.top { display:flex; align-items:center; gap:14px; margin-bottom:32px; }
.logo { width:46px; height:46px; background:var(--navy); border-radius:12px; display:flex; align-items:center; justify-content:center; font-size:15px; font-weight:800; color:var(--gold); flex-shrink:0; }
.top-text h1 { font-size:17px; font-weight:800; color:var(--navy); }
.top-text p { font-size:12px; color:var(--muted); margin-top:2px; }
.back-link { margin-left:auto; font-size:12px; font-weight:600; color:var(--muted); text-decoration:none; display:flex; align-items:center; gap:5px; transition:color .2s; }
.back-link:hover { color:var(--navy); }

/* DROP ZONE */
.drop-zone {
  border:2px dashed var(--border); border-radius:16px;
  padding:40px 24px; text-align:center; cursor:pointer;
  transition:all .2s; background:var(--bg); position:relative;
}
.drop-zone.drag { border-color:var(--gold); background:#fdf8ee; }
.drop-zone input { position:absolute; inset:0; opacity:0; cursor:pointer; width:100%; height:100%; }
.dz-icon { width:52px; height:52px; background:var(--navy); border-radius:14px; display:flex; align-items:center; justify-content:center; margin:0 auto 14px; }
.dz-icon svg { color:var(--gold); }
.dz-title { font-size:15px; font-weight:700; color:var(--text); margin-bottom:4px; }
.dz-sub { font-size:12.5px; color:var(--muted); }
.dz-sub span { color:var(--navy); font-weight:600; }

/* FILE PREVIEW */
.file-preview {
  display:none; align-items:center; gap:12px;
  background:#f0f5ff; border:1px solid #c8d8ff; border-radius:12px;
  padding:14px 16px; margin-top:14px;
}
.file-preview.show { display:flex; }
.fp-icon { width:36px; height:36px; background:var(--navy); border-radius:9px; display:flex; align-items:center; justify-content:center; flex-shrink:0; }
.fp-name { font-size:13px; font-weight:700; color:var(--text); }
.fp-size { font-size:11px; color:var(--muted); margin-top:2px; }
.fp-remove { margin-left:auto; background:none; border:none; cursor:pointer; color:var(--muted); transition:color .2s; padding:4px; }
.fp-remove:hover { color:var(--red); }

/* BUTTON */
.btn {
  width:100%; padding:15px; border-radius:12px; border:none; cursor:pointer;
  font-family:'Plus Jakarta Sans',sans-serif; font-size:14px; font-weight:700;
  background:var(--navy); color:#fff; margin-top:20px;
  transition:all .2s; display:flex; align-items:center; justify-content:center; gap:8px;
}
.btn:hover:not(:disabled) { background:#1a3260; transform:translateY(-1px); box-shadow:0 6px 20px rgba(13,31,60,.2); }
.btn:disabled { opacity:.5; cursor:not-allowed; transform:none; box-shadow:none; }
.btn.success { background:var(--green); }

/* STATUS */
.status-box {
  display:none; margin-top:18px; border-radius:12px; padding:16px 18px;
  font-size:13px; line-height:1.5;
}
.status-box.show { display:block; }
.status-box.info { background:#eef4ff; border:1px solid #c0d4ff; color:#1a3a8f; }
.status-box.error { background:#fff0f0; border:1px solid #ffc0c0; color:#8b0000; }
.status-box.ok { background:#edfaf3; border:1px solid #a0e0c0; color:#0d5c32; }

/* PROGRESS */
.progress-wrap { display:none; margin-top:16px; }
.progress-wrap.show { display:block; }
.progress-label { font-size:12px; font-weight:600; color:var(--muted); margin-bottom:8px; display:flex; justify-content:space-between; }
.progress-bar { height:6px; background:var(--border); border-radius:3px; overflow:hidden; }
.progress-fill { height:100%; background:linear-gradient(90deg, var(--gold), var(--gold-light)); border-radius:3px; transition:width .4s ease; width:0%; }

/* STEPS */
.steps { margin-top:28px; border-top:1px solid var(--border); padding-top:20px; display:flex; gap:0; }
.step { flex:1; text-align:center; position:relative; }
.step:not(:last-child)::after { content:''; position:absolute; top:14px; left:60%; width:80%; height:2px; background:var(--border); z-index:0; }
.step.done:not(:last-child)::after { background:var(--gold); }
.step-circle { width:28px; height:28px; border-radius:50%; background:var(--border); color:var(--muted); font-size:11px; font-weight:700; display:flex; align-items:center; justify-content:center; margin:0 auto 6px; position:relative; z-index:1; transition:all .3s; }
.step.done .step-circle { background:var(--gold); color:var(--navy); }
.step.active .step-circle { background:var(--navy); color:#fff; box-shadow:0 0 0 3px rgba(13,31,60,.15); }
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
    <a href="index.html" class="back-link">
      <svg width="13" height="13" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.5"><polyline points="15 18 9 12 15 6"/></svg>
      Kembali
    </a>
  </div>

  <!-- STEPS -->
  <div class="steps">
    <div class="step active" id="s1">
      <div class="step-circle">1</div>
      <div class="step-label">Pilih PDF</div>
    </div>
    <div class="step" id="s2">
      <div class="step-circle">2</div>
      <div class="step-label">Proses AI</div>
    </div>
    <div class="step" id="s3">
      <div class="step-circle">3</div>
      <div class="step-label">Selesai</div>
    </div>
  </div>

  <!-- DROP ZONE -->
  <div style="margin-top:24px">
    <div class="drop-zone" id="dropZone">
      <input type="file" id="fileInput" accept=".pdf" />
      <div class="dz-icon">
        <svg width="24" height="24" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><path d="M14 2H6a2 2 0 00-2 2v16a2 2 0 002 2h12a2 2 0 002-2V8z"/><polyline points="14 2 14 8 20 8"/><line x1="12" y1="18" x2="12" y2="12"/><line x1="9" y1="15" x2="15" y2="15"/></svg>
      </div>
      <div class="dz-title">Seret PDF jadwal ke sini</div>
      <div class="dz-sub">atau <span>klik untuk memilih file</span></div>
    </div>

    <div class="file-preview" id="filePreview">
      <div class="fp-icon">
        <svg width="18" height="18" viewBox="0 0 24 24" fill="none" stroke="#d4a030" stroke-width="2"><path d="M14 2H6a2 2 0 00-2 2v16a2 2 0 002 2h12a2 2 0 002-2V8z"/><polyline points="14 2 14 8 20 8"/></svg>
      </div>
      <div>
        <div class="fp-name" id="fpName">–</div>
        <div class="fp-size" id="fpSize">–</div>
      </div>
      <button class="fp-remove" id="fpRemove" title="Hapus file">
        <svg width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.5"><line x1="18" y1="6" x2="6" y2="18"/><line x1="6" y1="6" x2="18" y2="18"/></svg>
      </button>
    </div>
  </div>

  <!-- PROGRESS -->
  <div class="progress-wrap" id="progressWrap">
    <div class="progress-label"><span id="progressLabel">Membaca PDF...</span><span id="progressPct">0%</span></div>
    <div class="progress-bar"><div class="progress-fill" id="progressFill"></div></div>
  </div>

  <!-- STATUS -->
  <div class="status-box" id="statusBox"></div>

  <!-- BUTTON -->
  <button class="btn" id="uploadBtn" disabled onclick="processUpload()">
    <svg width="15" height="15" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.5"><path d="M21 15v4a2 2 0 01-2 2H5a2 2 0 01-2-2v-4"/><polyline points="17 8 12 3 7 8"/><line x1="12" y1="3" x2="12" y2="15"/></svg>
    Proses &amp; Tampilkan Jadwal
  </button>
</div>

<script>
let selectedFile = null;

// ── Drag & Drop ──
const dz = document.getElementById('dropZone');
const fi = document.getElementById('fileInput');

dz.addEventListener('dragover', e => { e.preventDefault(); dz.classList.add('drag'); });
dz.addEventListener('dragleave', () => dz.classList.remove('drag'));
dz.addEventListener('drop', e => {
  e.preventDefault(); dz.classList.remove('drag');
  const f = e.dataTransfer.files[0];
  if (f && f.type === 'application/pdf') setFile(f);
  else showStatus('error', 'Harap upload file PDF yang valid.');
});
fi.addEventListener('change', () => { if (fi.files[0]) setFile(fi.files[0]); });

document.getElementById('fpRemove').addEventListener('click', clearFile);

function setFile(f) {
  selectedFile = f;
  document.getElementById('fpName').textContent = f.name;
  document.getElementById('fpSize').textContent = (f.size / 1024).toFixed(1) + ' KB';
  document.getElementById('filePreview').classList.add('show');
  document.getElementById('uploadBtn').disabled = false;
  clearStatus();
  setStep(1);
}

function clearFile() {
  selectedFile = null;
  fi.value = '';
  document.getElementById('filePreview').classList.remove('show');
  document.getElementById('uploadBtn').disabled = true;
  clearStatus();
  setStep(0);
}

// ── Steps ──
function setStep(n) {
  [1,2,3].forEach(i => {
    const el = document.getElementById('s'+i);
    el.classList.remove('active','done');
    if (i < n+1) el.classList.add('done');
    if (i === n+1) el.classList.add('active');
  });
}

// ── Status ──
function showStatus(type, msg) {
  const el = document.getElementById('statusBox');
  el.className = 'status-box show ' + type;
  el.innerHTML = msg;
}
function clearStatus() {
  const el = document.getElementById('statusBox');
  el.className = 'status-box';
  el.innerHTML = '';
}

// ── Progress ──
function setProgress(pct, label) {
  document.getElementById('progressWrap').classList.add('show');
  document.getElementById('progressFill').style.width = pct + '%';
  document.getElementById('progressPct').textContent = pct + '%';
  if (label) document.getElementById('progressLabel').textContent = label;
}
function hideProgress() {
  document.getElementById('progressWrap').classList.remove('show');
}

// ── Main Process ──
async function processUpload() {
  if (!selectedFile) return;

  const btn = document.getElementById('uploadBtn');
  btn.disabled = true;
  clearStatus();
  setStep(2);
  setProgress(10, 'Membaca file PDF...');

  try {
    // Convert PDF to base64
    const base64 = await fileToBase64(selectedFile);
    setProgress(30, 'Mengirim ke AI untuk dianalisis...');

    // Kirim ke proxy server (hindari CORS)
    const response = await fetch('/api/parse', {
      method: 'POST',
      headers: { 'Content-Type': 'application/json' },
      body: JSON.stringify({ pdfBase64: base64 })
    });

    setProgress(70, 'Memproses hasil dari AI...');

    const result = await response.json();

    if (!response.ok || result.error) {
      throw new Error(result.error || 'Gagal memproses PDF di server.');
    }

    setProgress(85, 'Memvalidasi data jadwal...');

    const parsed = result.data;

    if (!parsed.mahasiswa || !Array.isArray(parsed.matakuliah)) {
      throw new Error('Data tidak lengkap. Pastikan PDF adalah jadwal kuliah UMK yang valid.');
    }

    setProgress(100, 'Menyimpan data...');

    // Save to localStorage
    localStorage.setItem('jadwal_data', JSON.stringify(parsed));

    setStep(3);
    showStatus('ok', `
      <strong>✓ Jadwal berhasil diproses!</strong><br>
      Ditemukan <strong>${parsed.matakuliah.length} matakuliah</strong> untuk <strong>${parsed.mahasiswa.nama}</strong> — ${parsed.mahasiswa.semester}.<br>
      <span style="color:#555">Mengalihkan ke halaman jadwal...</span>
    `);
    document.getElementById('uploadBtn').classList.add('success');
    document.getElementById('uploadBtn').innerHTML = '✓ Berhasil — Mengalihkan...';

    setTimeout(() => { window.location.href = 'index.html'; }, 2000);

  } catch(err) {
    hideProgress();
    setStep(1);
    showStatus('error', `<strong>Gagal memproses PDF.</strong><br>${err.message}`);
    btn.disabled = false;
  }
}

function fileToBase64(file) {
  return new Promise((res, rej) => {
    const r = new FileReader();
    r.onload = () => res(r.result.split(',')[1]);
    r.onerror = () => rej(new Error('Gagal membaca file.'));
    r.readAsDataURL(file);
  });
}
</script>
</body>
</html>
`;

// ── Anthropic prompt ───────────────────────────────────
const PROMPT = `Kamu adalah parser jadwal kuliah mahasiswa Indonesia. Ekstrak semua data dari PDF jadwal kuliah ini dan kembalikan HANYA JSON murni tanpa markdown, tanpa komentar, tanpa tanda backtick.

Format JSON:
{
  "mahasiswa": { "nama":"...","nim":"...","prodi":"...","dosenPA":"...","sks":24,"semester":"..." },
  "matakuliah": [{
    "no":1,"kelas":"A","kode":"IFT406","nama":"Nama MK","isPraktikum":false,
    "dosen":"Nama Dosen","sks":2,
    "jadwal":{"sn":null,"sl":null,"rb":{"jam":"08:00-09:39","ruang":"J.4,11"},"km":null,"jm":null,"sb":null,"mg":null}
  }],
  "dicetak":"tanggal cetak"
}

Aturan: sn=Senin sl=Selasa rb=Rabu km=Kamis jm=Jumat sb=Sabtu mg=Minggu. Hari tanpa jadwal=null. isPraktikum=true jika nama mengandung "Praktikum". Kembalikan HANYA JSON.`;

// ── Route handler ──────────────────────────────────────
addEventListener('fetch', event => {
  event.respondWith(handleRequest(event.request));
});

async function handleRequest(request) {
  const url  = new URL(request.url);
  const path = url.pathname;

  // POST /api/parse – proxy ke Anthropic
  if (path === '/api/parse' && request.method === 'POST') {
    return handleParse(request);
  }

  // Static pages
  const html = { 'Content-Type': 'text/html; charset=utf-8' };
  if (path === '/' || path === '/index.html' || path === '/index') {
    return new Response(INDEX_HTML, { headers: html });
  }
  if (path === '/upload.html' || path === '/upload') {
    return new Response(UPLOAD_HTML, { headers: html });
  }

  return new Response('Not Found', { status: 404 });
}

async function handleParse(request) {
  const j = { 'Content-Type': 'application/json', 'Access-Control-Allow-Origin': '*' };
  try {
    const { pdfBase64 } = await request.json();
    if (!pdfBase64) return new Response(JSON.stringify({ error: 'pdfBase64 diperlukan.' }), { status: 400, headers: j });

    const apiKey = (typeof process !== 'undefined' && process.env?.ANTHROPIC_API_KEY) || '';
    if (!apiKey) return new Response(JSON.stringify({ error: 'ANTHROPIC_API_KEY belum diset di environment.' }), { status: 500, headers: j });

    const apiRes = await fetch('https://api.anthropic.com/v1/messages', {
      method: 'POST',
      headers: {
        'Content-Type': 'application/json',
        'x-api-key': apiKey,
        'anthropic-version': '2023-06-01',
      },
      body: JSON.stringify({
        model: 'claude-sonnet-4-20250514',
        max_tokens: 4000,
        messages: [{
          role: 'user',
          content: [
            { type: 'document', source: { type: 'base64', media_type: 'application/pdf', data: pdfBase64 } },
            { type: 'text', text: PROMPT },
          ],
        }],
      }),
    });

    if (!apiRes.ok) {
      const err = await apiRes.json().catch(() => ({}));
      return new Response(JSON.stringify({ error: err.error?.message || 'Anthropic API error.' }), { status: 500, headers: j });
    }

    const data = await apiRes.json();
    let raw = (data.content || []).map(c => c.text || '').join('').trim();
    raw = raw.replace(/^```json\s*/i,'').replace(/^```\s*/i,'').replace(/\s*```$/i,'').trim();

    let parsed;
    try { parsed = JSON.parse(raw); }
    catch { const m = raw.match(/\{[\s\S]*\}/); parsed = m ? JSON.parse(m[0]) : null; }

    if (!parsed || !parsed.mahasiswa || !Array.isArray(parsed.matakuliah)) {
      throw new Error('Struktur data tidak lengkap. Pastikan PDF adalah jadwal kuliah UMK yang valid.');
    }
    if (!parsed.dicetak) {
      parsed.dicetak = new Date().toLocaleDateString('id-ID', { day:'2-digit', month:'long', year:'numeric' });
    }

    return new Response(JSON.stringify({ ok: true, data: parsed }), { headers: j });
  } catch (err) {
    return new Response(JSON.stringify({ error: err.message }), { status: 500, headers: j });
  }
}
