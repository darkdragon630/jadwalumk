const express = require('express');
const path    = require('path');

const app  = express();
const PORT = process.env.PORT || 8080;

// Serve static files
app.use(express.static(path.join(__dirname)));

// Parse JSON body up to 20MB (for base64 PDF)
app.use(express.json({ limit: '20mb' }));

// Proxy endpoint: browser -> here -> Anthropic (no CORS issue)
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
            { type: 'document', source: { type: 'base64', media_type: 'application/pdf', data: pdfBase64 } },
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

    res.json({ ok: true, data: parsed });

  } catch (err) {
    console.error('[/api/parse]', err.message);
    res.status(500).json({ error: err.message });
  }
});

// Fallback to index.html
app.get('*', (_, res) => res.sendFile(path.join(__dirname, 'index.html')));

app.listen(PORT, () => console.log(`Server running on port ${PORT}`));
