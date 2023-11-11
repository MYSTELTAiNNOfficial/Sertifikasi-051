<?php
require_once('./vendor/autoload.php');
require_once('db-controller.php');

use Firebase\JWT\JWT as jwt;
use Firebase\JWT\Key as key;
use Dotenv\Dotenv;

header('Accept: */*');
header('Content-Type: application/json');
$dotenv = Dotenv::createImmutable(__DIR__);
$dotenv->load();
$json = file_get_contents('php://input');
$data = json_decode($json);

if (!empty($_POST)) {
    $id_kendaraan = $_POST['id_kendaraan'];
    $tipe_kendaraan = $_POST['tipe_kendaraan'];
    $model = $_POST['model'];
    $manufaktur = $_POST['manufaktur'];
    $tahun_rilis = $_POST['tahun_rilis'];
    $jumlah_penumpang = $_POST['jumlah_penumpang'];
    $harga = $_POST['harga'];
    $tipe_bahanbakar = $_POST['tipe_bahanbakar']  ?? null;
    $luas_bagasi = $_POST['luas_bagasi'] ?? null;
    $jumlah_roda = $_POST['jumlah_roda'] ?? null;
    $luas_area_kargo = $_POST['luas_area_kargo'] ?? null;
    $ukuran_bagasi = $_POST['ukuran_bagasi'] ?? null;
    $kapasitas_bensin = $_POST['kapasitas_bensin'] ?? null;
} else if (!empty($data)) {
    $id_kendaraan = $data->id_kendaraan;
    $tipe_kendaraan = $data->tipe_kendaraan;
    $model = $data->model;
    $manufaktur = $data->manufaktur;
    $tahun_rilis = $data->tahun_rilis;
    $jumlah_penumpang = $data->jumlah_penumpang;
    $harga = $data->harga;
    $tipe_bahanbakar = $data->tipe_bahanbakar ?? null;
    $luas_bagasi = $data->luas_bagasi ?? null;
    $jumlah_roda = $data->jumlah_roda ?? null;
    $luas_area_kargo = $data->luas_area_kargo ?? null;
    $ukuran_bagasi = $data->ukuran_bagasi ?? null;
    $kapasitas_bensin = $data->kapasitas_bensin ?? null;
} else {
    $response['err'] = "true";
    $response['message'] = "No POST Data!";
}

$headers = getallheaders();
if (!isset($headers['Authorization'])) {
    http_response_code(401);
    $response['err'] = "true";
    $response['message'] = "Not Authorized!";
} else {
    list($bearer, $token) = explode(' ', $headers['Authorization']);
    if ($bearer != "Bearer") {
        http_response_code(401);
        $response['err'] = "true";
        $response['message'] = "Not Authorized!";
    } else {
        $key = $_ENV['ACCESS_TOKEN_SECRET'];
        $decoded = jwt::decode($token, new key($key, "HS512"));
        $decoded_array = (array) $decoded;
        $id = $decoded_array['id'];
        $query = $conn->prepare("SELECT * FROM petugas WHERE id = ?");
        $query->bind_param('s', $id);
        $query->execute();
        $query->store_result();
        if ($query->num_rows > 0) {
            $query = $conn->prepare("UPDATE kendaraan SET tipe_kendaraan = ?, model = ?, manufaktur = ?, tahun_rilis = ?, jumlah_penumpang = ?, harga = ?, tipe_bahanbakar = ?, luas_bagasi = ?, jumlah_roda = ?, luas_area_kargo = ?, ukuran_bagasi = ?, kapasitas_bensin = ? WHERE id = ?");
            $query->bind_param("sssssssssssss", $tipe_kendaraan, $model, $manufaktur, $tahun_rilis, $jumlah_penumpang, $harga, $tipe_bahanbakar, $luas_bagasi, $jumlah_roda, $luas_area_kargo, $ukuran_bagasi, $kapasitas_bensin, $id_kendaraan);
            if ($query->execute()) {
                $response['err'] = "false";
                $response['message'] = "Data Updated";
            } else {
                $response['err'] = "true";
                $response['message'] = "Failed to update!";
            }
        } else {
            http_response_code(401);
            $response['err'] = "true";
            $response['message'] = "Not Authorized!";
        }
    }
}



echo json_encode($response);
