# Jadwal Kuliah UMK

Web app jadwal kuliah dengan AI parser PDF — di-deploy di Cloudflare Workers.

## Struktur File

```
jadwal/
├── index.html    → Halaman jadwal
├── upload.html   → Halaman upload PDF
├── worker.js     → Cloudflare Worker (proxy Anthropic API)
├── server.js     → Backend Node.js (alternatif lokal)
└── README.md
```

---

## Deploy ke Cloudflare Workers (Recommended)

### Masalah CORS
Browser **tidak bisa** langsung memanggil `api.anthropic.com` — akan diblokir CORS.
Solusinya: gunakan **Cloudflare Worker** sebagai proxy server-side.

### Langkah 1 — Deploy Worker proxy

1. Buka https://dash.cloudflare.com → Workers & Pages → Create
2. Pilih "Hello World" template → beri nama, misal: `jadwal-proxy`
3. Klik Edit code → hapus isi default → paste seluruh isi `worker.js`
4. Klik Deploy

### Langkah 2 — Set API Key di Worker

Di halaman Worker: Settings → Variables → Add variable

| Variable name       | Value              |
|---------------------|--------------------|
| ANTHROPIC_API_KEY   | sk-ant-xxxxxxxxx   |

Klik Encrypt lalu Save and deploy.

### Langkah 3 — Konfigurasi URL proxy di upload.html

Buka `upload.html`, cari baris:

```js
const PROXY_URL = '/api/parse';
```

Jika Worker proxy di subdomain terpisah (misal jadwal-proxy.nama.workers.dev), ganti:
```js
const PROXY_URL = 'https://jadwal-proxy.nama.workers.dev';
```

Jika Worker dan halaman di domain yang sama, biarkan `/api/parse`.

---

## Jalankan Lokal dengan Node.js

```bash
npm install express
export ANTHROPIC_API_KEY=sk-ant-xxxxxxxxxx
node server.js
# Buka http://localhost:3000
```

---

## Arsitektur

```
Browser (upload.html)
  └─► POST /api/parse ──► Worker / server.js (proxy)
                              └─► POST api.anthropic.com (server-side, bebas CORS)
```
