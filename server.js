const express = require('express');
const path    = require('path');

const app  = express();
const PORT = process.env.PORT || 3000;

// ── Serve static files (index.html, upload.html, dll) ──
app.use(express.static(path.join(__dirname)));

// ── Parse JSON body hingga 20 MB (untuk base64 PDF) ──
app.use(express.json({ limit: '20mb' }));

// ── Proxy endpoint: /api/parse ──
// Menerima { pdfBase64: "..." } dari upload.html
// Mengirim ke Anthropic API (server-side, bebas CORS)
// Mengembalikan data jadwal dalam JSON
app.post('/api/parse', async (req, res) => {
  const { pdfBase64 } = req.body;
  if (!pdfBase64) return res.status(400).json({ error: 'pdfBase64 diperlukan.' });

  const ANTHROPIC_API_KEY = process.env.ANTHROPIC_API_KEY;
  if (!ANTHROPIC_API_KEY) {
    return res.status(500).json({ error: 'ANTHROPIC_API_KEY belum diset di environment.' });
  }

  const PROMPT = `Kamu adalah parser jadwal kuliah mahasiswa Indonesia. Ekstrak semua data dari PDF jadwal kuliah ini dan kembalikan HANYA JSON murni tanpa markdown, tanpa komentar, tanpa tanda backtick.

Format JSON yang harus dikembalikan:
{
  "mahasiswa": {
    "nama": "...",
    "nim": "...",
    "prodi": "...",
    "dosenPA": "...",
    "sks": 24,
    "semester": "..."
  },
  "matakuliah": [
    {
      "no": 1,
      "kelas": "A",
      "kode": "IFT406",
      "nama": "Nama Matakuliah",
      "isPraktikum": false,
      "dosen": "Nama Dosen",
      "sks": 2,
      "jadwal": {
        "sn": null,
        "sl": null,
        "rb": { "jam": "08:00–09:39", "ruang": "J.4,11" },
        "km": null,
        "jm": null,
        "sb": null,
        "mg": null
      }
    }
  ],
  "dicetak": "tanggal cetak jika ada"
}

Aturan penting:
- Key hari: sn=Senin, sl=Selasa, rb=Rabu, km=Kamis, jm=Jumat, sb=Sabtu, mg=Minggu
- Jika hari tidak ada jadwal, nilainya null
- isPraktikum = true jika nama matakuliah mengandung kata "Praktikum"
- Ekstrak semua baris matakuliah dari tabel
- Kembalikan HANYA JSON, tidak ada teks lain sama sekali`;

  try {
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
            {
              type:   'document',
              source: { type: 'base64', media_type: 'application/pdf', data: pdfBase64 },
            },
            { type: 'text', text: PROMPT },
          ],
        }],
      }),
    });

    if (!apiRes.ok) {
      const err = await apiRes.json().catch(() => ({}));
      return res.status(apiRes.status).json({ error: err.error?.message || 'Anthropic API error.' });
    }

    const data = await apiRes.json();
    const raw  = data.content?.map(c => c.text || '').join('') || '';

    // Bersihkan markdown fences jika ada
    let jsonStr = raw.trim()
      .replace(/^```json\s*/i, '')
      .replace(/^```\s*/i, '')
      .replace(/\s*```$/i, '')
      .trim();

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

    res.json({ ok: true, data: parsed });

  } catch (err) {
    console.error('[/api/parse]', err.message);
    res.status(500).json({ error: err.message });
  }
});

// ── Fallback: semua route ke index.html ──
app.get('*', (_, res) => res.sendFile(path.join(__dirname, 'index.html')));

app.listen(PORT, () => console.log(`Server berjalan di http://localhost:${PORT}`));
