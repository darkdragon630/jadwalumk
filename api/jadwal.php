<?php
define('API_REQUEST', true);
header('Content-Type: application/json');
header('Access-Control-Allow-Origin: *');
header('Access-Control-Allow-Methods: GET, POST, OPTIONS');
header('Access-Control-Allow-Headers: Content-Type');
if ($_SERVER['REQUEST_METHOD'] === 'OPTIONS') { http_response_code(204); exit; }

require_once __DIR__ . '/../config/database.php';

$action = $_GET['action'] ?? '';

try {
    $db = getDB();

    // GET: ambil jadwal
    if ($action === 'get') {
        $mhs = $db->query("SELECT * FROM mahasiswa ORDER BY id DESC LIMIT 1")->fetch();
        if (!$mhs) { echo json_encode(['success' => true, 'data' => null]); exit; }

        $stmt = $db->prepare("SELECT * FROM matakuliah WHERE mahasiswa_id = ? ORDER BY no_urut");
        $stmt->execute([$mhs['id']]);
        $matakuliah = [];
        foreach ($stmt->fetchAll() as $mk) {
            $matakuliah[] = [
                'no'          => (int)$mk['no_urut'],
                'kelas'       => $mk['kelas'],
                'kode'        => $mk['kode'],
                'nama'        => $mk['nama'],
                'isPraktikum' => (bool)$mk['is_praktikum'],
                'dosen'       => $mk['dosen'],
                'sks'         => (int)$mk['sks'],
                'jadwal' => [
                    'sn' => $mk['jadwal_sn'] ? json_decode($mk['jadwal_sn'], true) : null,
                    'sl' => $mk['jadwal_sl'] ? json_decode($mk['jadwal_sl'], true) : null,
                    'rb' => $mk['jadwal_rb'] ? json_decode($mk['jadwal_rb'], true) : null,
                    'km' => $mk['jadwal_km'] ? json_decode($mk['jadwal_km'], true) : null,
                    'jm' => $mk['jadwal_jm'] ? json_decode($mk['jadwal_jm'], true) : null,
                    'sb' => $mk['jadwal_sb'] ? json_decode($mk['jadwal_sb'], true) : null,
                    'mg' => $mk['jadwal_mg'] ? json_decode($mk['jadwal_mg'], true) : null,
                ],
            ];
        }
        echo json_encode(['success' => true, 'data' => [
            'mahasiswa'  => [
                'nama'    => $mhs['nama'],
                'nim'     => $mhs['nim'],
                'prodi'   => $mhs['prodi'],
                'dosenPA' => $mhs['dosen_pa'],
                'sks'     => (int)$mhs['sks'],
                'semester'=> $mhs['semester'],
            ],
            'matakuliah' => $matakuliah,
            'dicetak'    => $mhs['dicetak'],
        ]]);
        exit;
    }

    // POST: simpan jadwal
    if ($_SERVER['REQUEST_METHOD'] === 'POST' && $action === 'save') {
        $body = json_decode(file_get_contents('php://input'), true);
        if (!isset($body['mahasiswa'], $body['matakuliah'])) {
            http_response_code(400);
            echo json_encode(['success' => false, 'error' => 'Data tidak valid.']);
            exit;
        }
        $m = $body['mahasiswa'];
        $db->beginTransaction();
        $db->exec("DELETE FROM mahasiswa");
        $db->prepare("INSERT INTO mahasiswa (nama,nim,prodi,dosen_pa,sks,semester,dicetak) VALUES (?,?,?,?,?,?,?)")
           ->execute([$m['nama'], $m['nim'], $m['prodi'], $m['dosenPA'], (int)$m['sks'], $m['semester'], $body['dicetak'] ?? date('d F Y')]);
        $mhsId = $db->lastInsertId();

        $ins = $db->prepare("INSERT INTO matakuliah
            (mahasiswa_id,no_urut,kelas,kode,nama,is_praktikum,dosen,sks,
             jadwal_sn,jadwal_sl,jadwal_rb,jadwal_km,jadwal_jm,jadwal_sb,jadwal_mg)
            VALUES (?,?,?,?,?,?,?,?,?,?,?,?,?,?,?)");

        foreach ($body['matakuliah'] as $mk) {
            $j = $mk['jadwal'] ?? [];
            $enc = function($k) use ($j) {
                return (!empty($j[$k]) && is_array($j[$k])) ? json_encode($j[$k]) : null;
            };
            $ins->execute([
                $mhsId, (int)$mk['no'], $mk['kelas'], $mk['kode'],
                $mk['nama'], $mk['isPraktikum'] ? 1 : 0, $mk['dosen'], (int)$mk['sks'],
                $enc('sn'), $enc('sl'), $enc('rb'), $enc('km'),
                $enc('jm'), $enc('sb'), $enc('mg'),
            ]);
        }
        $db->commit();
        echo json_encode(['success' => true]);
        exit;
    }

    // POST: simpan API key
    if ($_SERVER['REQUEST_METHOD'] === 'POST' && $action === 'save_key') {
        $body = json_decode(file_get_contents('php://input'), true);
        $key  = trim($body['key'] ?? '');
        if (!str_starts_with($key, 'AIza')) {
            http_response_code(400);
            echo json_encode(['success' => false, 'error' => 'Format API key tidak valid.']);
            exit;
        }
        $db->prepare("INSERT INTO settings (`key`,`value`) VALUES ('gemini_api_key',?)
                      ON DUPLICATE KEY UPDATE `value`=?")->execute([$key, $key]);
        echo json_encode(['success' => true]);
        exit;
    }

    // GET: cek ada API key
    if ($action === 'has_key') {
        $row = $db->query("SELECT `value` FROM settings WHERE `key`='gemini_api_key'")->fetch();
        echo json_encode(['success' => true, 'has_key' => (bool)$row]);
        exit;
    }

    // GET: ambil API key (dipakai upload.php server-side)
    if ($action === 'get_key') {
        $row = $db->query("SELECT `value` FROM settings WHERE `key`='gemini_api_key'")->fetch();
        if (!$row) { echo json_encode(['success' => false, 'error' => 'API key belum diset.']); exit; }
        echo json_encode(['success' => true, 'key' => $row['value']]);
        exit;
    }

    http_response_code(404);
    echo json_encode(['success' => false, 'error' => 'Action tidak ditemukan.']);

} catch (Throwable $e) {
    http_response_code(500);
    echo json_encode(['success' => false, 'error' => $e->getMessage()]);
}
