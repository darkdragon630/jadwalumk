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

    // POST: simpan API key
    if ($_SERVER['REQUEST_METHOD'] === 'POST' && $action === 'save_key') {
        $body = json_decode(file_get_contents('php://input'), true);
        $key  = trim($body['key'] ?? '');
        if (!str_starts_with($key, 'AIza')) {
            http_response_code(400); echo json_encode(['success'=>false,'error'=>'Format API key tidak valid.']); exit;
        }
        $db->prepare("INSERT INTO settings (`key`,`value`) VALUES ('gemini_api_key',?) ON DUPLICATE KEY UPDATE `value`=?")->execute([$key,$key]);
        echo json_encode(['success'=>true]); exit;
    }

    // GET: ambil API key
    if ($action === 'get_key') {
        $row = $db->query("SELECT `value` FROM settings WHERE `key`='gemini_api_key'")->fetch();
        if (!$row) { echo json_encode(['success'=>false,'error'=>'API key belum diset.']); exit; }
        echo json_encode(['success'=>true,'key'=>$row['value']]); exit;
    }

    // POST: simpan HTML jadwal
    if ($_SERVER['REQUEST_METHOD'] === 'POST' && $action === 'save_html') {
        $body = json_decode(file_get_contents('php://input'), true);
        $html = trim($body['html'] ?? '');
        if (strlen($html) < 500) {
            http_response_code(400); echo json_encode(['success'=>false,'error'=>'HTML terlalu pendek / kosong.']); exit;
        }
        $db->prepare("INSERT INTO settings (`key`,`value`) VALUES ('jadwal_html',?) ON DUPLICATE KEY UPDATE `value`=?")->execute([$html,$html]);
        echo json_encode(['success'=>true]); exit;
    }

    // GET: cek ada HTML tersimpan
    if ($action === 'has_html') {
        $row = $db->query("SELECT `value` FROM settings WHERE `key`='jadwal_html'")->fetch();
        echo json_encode(['success'=>true,'has_html'=>(bool)$row]); exit;
    }

    http_response_code(404);
    echo json_encode(['success'=>false,'error'=>'Action tidak ditemukan.']);

} catch (Throwable $e) {
    http_response_code(500);
    echo json_encode(['success'=>false,'error'=>$e->getMessage()]);
}
