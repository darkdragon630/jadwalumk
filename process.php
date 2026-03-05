<?php
/**
 * process.php — dipanggil via AJAX, proses PDF ke Anthropic
 * Set timeout tinggi di sini saja, bukan di upload.php
 */
set_time_limit(0);
ini_set('max_execution_time', 0);

define('API_REQUEST', true);
header('Content-Type: application/json');

require_once __DIR__ . '/config/database.php';
$db = getDB();

// Ambil API key
$keyRow = $db->query("SELECT `value` FROM settings WHERE `key`='anthropic_api_key'")->fetch();
if (!$keyRow) {
    echo json_encode(['success'=>false,'error'=>'API key belum diset.']); exit;
}
$apiKey = $keyRow['value'];

// Ambil PDF dari POST
if (!isset($_FILES['pdf']) || $_FILES['pdf']['error'] !== UPLOAD_ERR_OK) {
    echo json_encode(['success'=>false,'error'=>'File PDF tidak diterima. Error: '.($_FILES['pdf']['error']??'unknown')]); exit;
}
if ($_FILES['pdf']['size'] > 10*1024*1024) {
    echo json_encode(['success'=>false,'error'=>'File terlalu besar (maks 10MB).']); exit;
}

// PDF → base64
$b64 = base64_encode(file_get_contents($_FILES['pdf']['tmp_name']));

$prompt = 'Kamu diberikan sebuah file PDF berisi jadwal kuliah mahasiswa dari Universitas Muria Kudus (UMK), Fakultas Teknik.

TUGASMU:
Baca isi PDF ini secara menyeluruh, lalu konversi seluruh isinya menjadi satu halaman HTML yang bersih dan rapi — dengan tampilan yang MIRIP dengan dokumen PDF aslinya.

KETENTUAN TAMPILAN:
- Gunakan font "Plus Jakarta Sans" dari Google Fonts
- Warna tema: navy dark (#0d1f3c) untuk header, emas (#d4a030) untuk aksen
- Background halaman: #f0f2f7
- Kartu/section berbackground putih dengan border-radius dan shadow halus
- Header: logo "UMK", nama universitas, fakultas, nama mahasiswa, NIM, prodi, semester, dosen PA, total SKS
- Tabel jadwal: kolom No, Kelas, Kode MK, Nama MK, Dosen, SKS, lalu kolom hari Sn/Sl/Rb/Km/Jm/Sb/Mg
- Jadwal di kolom hari tampilkan sebagai pill berwarna berisi jam dan kode ruang
- Setiap baris matakuliah punya warna pill berbeda
- Nama mengandung "Praktikum" → badge kecil "PRAKTIKUM"
- Section "Ringkasan Mingguan": grid 6 kolom Senin–Sabtu, tiap kolom berisi kartu jadwal
- Footer: tanggal cetak dari PDF + nama Dosen PA
- Link "Upload Jadwal Baru" mengarah ke upload.php
- Responsive mobile

KETENTUAN DATA:
- Salin SEMUA data persis dari PDF tanpa dikarang
- Kolom hari kiri ke kanan: Sn=Senin, Sl=Selasa, Rb=Rabu, Km=Kamis, Jm=Jumat, Sb=Sabtu, Mg=Minggu
- Kolom hari kosong = tidak ada jadwal
- Jumlah baris tabel = jumlah matakuliah di PDF
- Ringkasan Mingguan konsisten dengan tabel

OUTPUT: HANYA HTML dari <!DOCTYPE html> sampai </html>. Tanpa markdown, backtick, atau teks lain.';

$payload = json_encode([
    'model'      => 'claude-haiku-4-5-20251001',
    'max_tokens' => 6000,
    'messages'   => [[
        'role'    => 'user',
        'content' => [
            ['type'=>'document','source'=>['type'=>'base64','media_type'=>'application/pdf','data'=>$b64]],
            ['type'=>'text','text'=>$prompt],
        ],
    ]],
]);

$ch = curl_init('https://api.anthropic.com/v1/messages');
curl_setopt_array($ch, [
    CURLOPT_RETURNTRANSFER => true,
    CURLOPT_POST           => true,
    CURLOPT_POSTFIELDS     => $payload,
    CURLOPT_TIMEOUT        => 180,
    CURLOPT_CONNECTTIMEOUT => 30,
    CURLOPT_HTTPHEADER     => [
        'Content-Type: application/json',
        'x-api-key: '.$apiKey,
        'anthropic-version: 2023-06-01',
    ],
]);

$resp     = curl_exec($ch);
$httpCode = curl_getinfo($ch, CURLINFO_HTTP_CODE);
$curlErr  = curl_error($ch);
curl_close($ch);

if ($curlErr) { echo json_encode(['success'=>false,'error'=>'cURL: '.$curlErr]); exit; }

$result = json_decode($resp, true);
if ($httpCode !== 200) {
    $msg = $result['error']['message'] ?? "HTTP $httpCode";
    echo json_encode(['success'=>false,'error'=>"Anthropic: $msg"]); exit;
}

$html = trim($result['content'][0]['text'] ?? '');
$html = preg_replace('/^```html\s*/i','',$html);
$html = preg_replace('/^```\s*/i','',$html);
$html = preg_replace('/\s*```$/i','',$html);
$html = trim($html);

if (stripos($html,'<html')===false && stripos($html,'<!doctype')===false) {
    echo json_encode(['success'=>false,'error'=>'Output bukan HTML. Respons: '.substr($html,0,200)]); exit;
}

$db->prepare("INSERT INTO settings (`key`,`value`) VALUES ('jadwal_html',?) ON DUPLICATE KEY UPDATE `value`=?")
   ->execute([$html,$html]);

echo json_encode(['success'=>true]);
