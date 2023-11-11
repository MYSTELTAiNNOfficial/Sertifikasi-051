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

if (!empty($_GET)) {
    $id_kendaraan = $_GET['id_kendaraan'];
} else if (!empty($data)) {
    $id_kendaraan = $data->id_kendaraan;
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
            $query = $conn->prepare("SELECT * FROM kendaraan WHERE id = ?");
            $query->bind_param("s", $id_kendaraan);
            $query->execute();
            $query->store_result();
            if ($query->num_rows > 0) {
                $query->bind_result($id, $tipe_kendaraan, $model, $manufaktur, $tahun_rilis, $jumlah_penumpang, $harga, $tipe_bahanbakar, $luas_bagasi, $jumlah_roda, $luas_area_kargo, $ukuran_bagasi, $kapasitas_bensin);
                $query->fetch();
                $response['err'] = "false";
                $response['message'] = "Data Found";
                $response['data'] = array(
                    "id" => $id,
                    "tipe_kendaraan" => $tipe_kendaraan,
                    "model" => $model,
                    "manufaktur" => $manufaktur,
                    "tahun_rilis" => $tahun_rilis,
                    "jumlah_penumpang" => $jumlah_penumpang,
                    "harga" => $harga,
                    "tipe_bahanbakar" => $tipe_bahanbakar,
                    "luas_bagasi" => $luas_bagasi,
                    "jumlah_roda" => $jumlah_roda,
                    "luas_area_kargo" => $luas_area_kargo,
                    "ukuran_bagasi" => $ukuran_bagasi,
                    "kapasitas_bensin" => $kapasitas_bensin
                );
            } else {
                $response['err'] = "true";
                $response['message'] = "Data Not Found";
            }
        } else {
            http_response_code(401);
            $response['err'] = "true";
            $response['message'] = "Not Authorized!";
        }
    }
}



echo json_encode($response);
