/**
 * server.js — Kompatibel dengan Wasmer Edge (WinterJS) DAN Node.js lokal
 *
 * Wasmer Edge  : deploy dengan `wasmer deploy`, set ANTHROPIC_API_KEY di dashboard
 * Node.js lokal: `npm install express` lalu `node server.js` (lihat bagian bawah)
 */

// ── Deteksi environment ──
const IS_WASMER = typeof addEventListener === 'function' && typeof process === 'undefined';

if (IS_WASMER) {
  // ════════════════════════════════════════
  // MODE WASMER EDGE (WinterJS — Web APIs)
  // ════════════════════════════════════════
  addEventListener('fetch', (event) => {
    event.respondWith(handleRequest(event.request));
  });

  async function handleRequest(request) {
    const url = new URL(request.url);

    // ── CORS preflight ──
    if (request.method === 'OPTIONS') {
      return new Response(null, {
        headers: {
          'Access-Control-Allow-Origin':  '*',
          'Access-Control-Allow-Methods': 'GET, POST, OPTIONS',
          'Access-Control-Allow-Headers': 'Content-Type',
        },
      });
    }

    // ── POST /api/parse ──
    if (request.method === 'POST' && url.pathname === '/api/parse') {
      return await handleParse(request);
    }

    // ── Static files ──
    const staticFiles = {
      '/':             { file: 'index.html',  type: 'text/html' },
      '/index.html':   { file: 'index.html',  type: 'text/html' },
      '/upload.html':  { file: 'upload.html', type: 'text/html' },
    };

    const staticEntry = staticFiles[url.pathname];
    if (staticEntry) {
      try {
        // WinterJS bisa baca file via fetch dengan path relatif
        const fileRes = await fetch(new URL(staticEntry.file, 'file:///'));
        const content = await fileRes.text();
        return new Response(content, {
          headers: { 'Content-Type': staticEntry.type + '; charset=utf-8' },
        });
      } catch {
        // fallback — Wasmer akan serve static files via wasmer.toml jika dikonfigurasi
        return new Response('File not found', { status: 404 });
      }
    }

    return new Response('Not Found', { status: 404 });
  }

} else {
  // ════════════════════════════════════════
  // MODE NODE.JS LOKAL (Express)
  // ════════════════════════════════════════
  const express = require('express');
  const path    = require('path');

  const app  = express();
  const PORT = process.env.PORT || 3000;

  app.use(express.static(path.join(__dirname)));
  app.use(express.json({ limit: '20mb' }));

  app.post('/api/parse', async (req, res) => {
    const { pdfBase64 } = req.body;
    if (!pdfBase64) return res.status(400).json({ error: 'pdfBase64 diperlukan.' });

    try {
      const result = await parseWithAI(pdfBase64);
      res.json(result);
    } catch (err) {
      console.error('[/api/parse]', err.message);
      res.status(500).json({ error: err.message });
    }
  });

  app.get('*', (_, res) => res.sendFile(path.join(__dirname, 'index.html')));
  app.listen(PORT, () => console.log(`Server berjalan di http://localhost:${PORT}`));
}

// ════════════════════════════════════════
// SHARED: logika parse AI (dipakai kedua mode)
// ════════════════════════════════════════
async function handleParse(request) {
  let pdfBase64;
  try {
    const body = await request.json();
    pdfBase64 = body.pdfBase64;
  } catch {
    return jsonResponse({ error: 'Body tidak valid.' }, 400);
  }

  if (!pdfBase64) return jsonResponse({ error: 'pdfBase64 diperlukan.' }, 400);

  try {
    const result = await parseWithAI(pdfBase64);
    return jsonResponse(result);
  } catch (err) {
    return jsonResponse({ error: err.message }, 500);
  }
}

async function parseWithAI(pdfBase64) {
  const ANTHROPIC_API_KEY = (typeof process !== 'undefined' ? process.env : {}).ANTHROPIC_API_KEY
    || (typeof env !== 'undefined' ? env.ANTHROPIC_API_KEY : null);

  if (!ANTHROPIC_API_KEY) {
    throw new Error('ANTHROPIC_API_KEY belum diset di environment.');
  }

  const PROMPT = `Kamu adalah parser jadwal kuliah mahasiswa Indonesia. Ekstrak semua data dari PDF jadwal kuliah ini dan kembalikan HANYA JSON murni tanpa markdown, tanpa komentar, tanpa tanda backtick.

Format JSON yang harus dikembalikan:
{
  "mahasiswa": {
    "nama": "...", "nim": "...", "prodi": "...", "dosenPA": "...", "sks": 24, "semester": "..."
  },
  "matakuliah": [{
    "no": 1, "kelas": "A", "kode": "IFT406", "nama": "Nama Matakuliah",
    "isPraktikum": false, "dosen": "Nama Dosen", "sks": 2,
    "jadwal": {
      "sn": null, "sl": null, "rb": { "jam": "08:00-09:39", "ruang": "J.4,11" },
      "km": null, "jm": null, "sb": null, "mg": null
    }
  }],
  "dicetak": "tanggal cetak jika ada"
}

Aturan: key hari sn/sl/rb/km/jm/sb/mg, null jika kosong, isPraktikum=true jika nama mengandung "Praktikum". Kembalikan HANYA JSON.`;

  const apiRes = await fetch('https://api.anthropic.com/v1/messages', {
    method: 'POST',
    headers: {
      'Content-Type':      'application/json',
      'x-api-key':         ANTHROPIC_API_KEY,
      'anthropic-version': '2023-06-01',
    },
    body: JSON.stringify({
      model:      'claude-sonnet-4-20250514',
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
    throw new Error(err.error?.message || 'Anthropic API error.');
  }

  const data = await apiRes.json();
  const raw  = data.content?.map(c => c.text || '').join('') || '';

  let jsonStr = raw.trim()
    .replace(/^```json\s*/i, '').replace(/^```\s*/i, '').replace(/\s*```$/i, '').trim();

  let parsed;
  try {
    parsed = JSON.parse(jsonStr);
  } catch {
    const match = jsonStr.match(/\{[\s\S]*\}/);
    if (match) parsed = JSON.parse(match[0]);
    else throw new Error('Format JSON tidak valid dari AI.');
  }

  if (!parsed.mahasiswa || !Array.isArray(parsed.matakuliah)) {
    throw new Error('Struktur data tidak lengkap.');
  }

  if (!parsed.dicetak) {
    parsed.dicetak = new Date().toLocaleDateString('id-ID', {
      day: '2-digit', month: 'long', year: 'numeric',
    });
  }

  return { ok: true, data: parsed };
}

function jsonResponse(data, status = 200) {
  return new Response(JSON.stringify(data), {
    status,
    headers: {
      'Content-Type':                 'application/json',
      'Access-Control-Allow-Origin':  '*',
    },
  });
}
