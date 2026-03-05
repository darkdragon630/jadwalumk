# Jadwal Kuliah UMK

Web app jadwal kuliah dengan AI parser PDF.

## Struktur File

```
jadwal/
├── index.html      → Halaman jadwal (jadwal/index)
├── upload.html     → Halaman upload PDF (jadwal/upload)
├── server.js       → Backend proxy (wajib dijalankan)
├── package.json
└── README.md
```

## Setup & Jalankan

### 1. Install dependencies
```bash
npm install
```

### 2. Set API Key
Tambahkan environment variable `ANTHROPIC_API_KEY`:

```bash
# Linux / macOS
export ANTHROPIC_API_KEY=sk-ant-xxxxxxxxxx

# Windows (CMD)
set ANTHROPIC_API_KEY=sk-ant-xxxxxxxxxx

# Windows (PowerShell)
$env:ANTHROPIC_API_KEY="sk-ant-xxxxxxxxxx"
```

### 3. Jalankan server
```bash
npm start
```

### 4. Buka di browser
```
http://localhost:3000/index.html   → Jadwal
http://localhost:3000/upload.html  → Upload PDF baru
```

## Deploy ke Wasmer / VPS

### Wasmer Edge
Tambahkan `ANTHROPIC_API_KEY` di **Environment Variables** pada dashboard Wasmer, 
lalu deploy dengan:
```bash
wasmer deploy
```

### VPS (Ubuntu/Debian)
```bash
# Install Node.js
curl -fsSL https://deb.nodesource.com/setup_20.x | sudo -E bash -
sudo apt-get install -y nodejs

# Clone / upload files ke server
npm install

# Jalankan dengan PM2 agar tetap hidup
npm install -g pm2
ANTHROPIC_API_KEY=sk-ant-xxx pm2 start server.js --name jadwal-umk
pm2 save
```

## Cara Pakai

1. Buka `http://domain-kamu/index.html` → tampil jadwal semester sekarang
2. Klik **"Upload Jadwal Baru"** → menuju `/upload.html`
3. Drag & drop atau pilih PDF jadwal semester baru dari Kanal UMK
4. Klik **"Proses & Tampilkan Jadwal"** → AI membaca PDF, ekstrak data
5. Otomatis redirect ke `index.html` dengan jadwal baru yang sudah tampil rapi

## Kenapa ada server.js?

Browser memblokir request langsung ke `api.anthropic.com` (CORS policy).
`server.js` bertindak sebagai proxy — request dari browser dikirim ke `/api/parse`,
lalu server yang meneruskannya ke Anthropic API (server-side, bebas CORS).
