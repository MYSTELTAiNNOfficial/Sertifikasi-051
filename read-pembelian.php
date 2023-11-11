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
    $id_customer = $_GET['id_customer'];
} else if (!empty($data)) {
    $id_customer = $data->id_customer;
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
            $query = $conn->prepare("SELECT * FROM pembelian WHERE id_customer = ?");
            $query->bind_param("s", $id_customer);
            $query->execute();
            $result = $query->get_result();
            if (!empty($result)) {
                $response['err'] = "false";
                $response['message'] = "Data Found";
                $response["pembelian"] = array();
                while ($data = $result->fetch_assoc()) {
                    $query = $conn->prepare("SELECT * FROM kendaraan WHERE id = ?");
                    $query->bind_param("s", $data['id_kendaraan']);
                    $query->execute();
                    $query->store_result();
                    if ($query->num_rows > 0) {
                        $query->bind_result($id, $tipe_kendaraan, $model, $manufaktur, $tahun_rilis, $jumlah_penumpang, $harga, $tipe_bahanbakar, $luas_bagasi, $jumlah_roda, $luas_area_kargo, $ukuran_bagasi, $kapasitas_bensin);
                        $query->fetch();
                        $res = array(
                            "id" => $id,
                            "tipe_kendaraan" => $tipe_kendaraan,
                            "model" => $model,
                            "manufaktur" => $manufaktur,
                            "tahun_rilis" => $tahun_rilis,
                            "jumlah_penumpang" => $jumlah_penumpang,
                            "harga" => (int)$harga,
                        );
                        array_push($response["pembelian"], $res);
                    }
                }
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
