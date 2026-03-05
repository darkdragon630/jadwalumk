/**
 * Cloudflare Worker — Proxy untuk Anthropic API
 * 
 * Deploy:
 *   1. Buat Worker baru di https://dash.cloudflare.com → Workers & Pages → Create
 *   2. Paste isi file ini ke editor Worker
 *   3. Di Settings → Variables → tambahkan ANTHROPIC_API_KEY = sk-ant-xxxxx
 *   4. Deploy
 * 
 * Opsional (jika ingin jadwal & Worker dalam satu domain):
 *   - Tambahkan custom route: jadwalumk.burhanjepara41.workers.dev/api/parse → Worker ini
 *   - Atau deploy sebagai subdomain terpisah, lalu update PROXY_URL di upload.html
 */

const CORS_HEADERS = {
  'Access-Control-Allow-Origin': '*',
  'Access-Control-Allow-Methods': 'POST, OPTIONS',
  'Access-Control-Allow-Headers': 'Content-Type',
};

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
        "rb": { "jam": "08:00-09:39", "ruang": "J.4,11" },
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

export default {
  async fetch(request, env) {
    // ── Handle CORS preflight ──
    if (request.method === 'OPTIONS') {
      return new Response(null, { headers: CORS_HEADERS });
    }

    // ── Hanya terima POST ──
    if (request.method !== 'POST') {
      return new Response('Method not allowed', { status: 405, headers: CORS_HEADERS });
    }

    // ── Ambil pdfBase64 dari body ──
    let pdfBase64;
    try {
      const body = await request.json();
      pdfBase64 = body.pdfBase64;
    } catch {
      return Response.json({ error: 'Body JSON tidak valid.' }, { status: 400, headers: CORS_HEADERS });
    }

    if (!pdfBase64) {
      return Response.json({ error: 'pdfBase64 diperlukan.' }, { status: 400, headers: CORS_HEADERS });
    }

    // ── Cek API Key ──
    const ANTHROPIC_API_KEY = env.ANTHROPIC_API_KEY;
    if (!ANTHROPIC_API_KEY) {
      return Response.json(
        { error: 'ANTHROPIC_API_KEY belum diset di environment Worker.' },
        { status: 500, headers: CORS_HEADERS }
      );
    }

    // ── Kirim ke Anthropic API ──
    let apiRes;
    try {
      apiRes = await fetch('https://api.anthropic.com/v1/messages', {
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
    } catch (err) {
      return Response.json(
        { error: 'Gagal menghubungi Anthropic API: ' + err.message },
        { status: 502, headers: CORS_HEADERS }
      );
    }

    if (!apiRes.ok) {
      const err = await apiRes.json().catch(() => ({}));
      return Response.json(
        { error: err.error?.message || 'Anthropic API error.' },
        { status: apiRes.status, headers: CORS_HEADERS }
      );
    }

    // ── Parse response ──
    const data = await apiRes.json();
    const raw  = data.content?.map(c => c.text || '').join('') || '';

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
      if (match) {
        try { parsed = JSON.parse(match[0]); }
        catch { return Response.json({ error: 'Format JSON tidak valid dari AI.' }, { status: 500, headers: CORS_HEADERS }); }
      } else {
        return Response.json({ error: 'Format JSON tidak valid dari AI.' }, { status: 500, headers: CORS_HEADERS });
      }
    }

    if (!parsed.mahasiswa || !Array.isArray(parsed.matakuliah)) {
      return Response.json({ error: 'Struktur data tidak lengkap.' }, { status: 500, headers: CORS_HEADERS });
    }

    if (!parsed.dicetak) {
      parsed.dicetak = new Date().toLocaleDateString('id-ID', {
        day: '2-digit', month: 'long', year: 'numeric',
      });
    }

    return Response.json(
      { ok: true, data: parsed },
      { headers: CORS_HEADERS }
    );
  },
};
