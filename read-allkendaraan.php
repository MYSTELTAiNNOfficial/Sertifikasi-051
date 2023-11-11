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
            $query->execute();
            $result = $query->get_result();
            if (!empty($result)) {
                $response['err'] = "false";
                $response['message'] = "Data Found";
                $response["data"] = array();
                while ($data = $result->fetch_assoc()) {
                    $res = array(
                        "id" => $data['id'],
                        "tipe_kendaraan" => $data['tipe_kendaraan'],
                        "model" => $data['model'],
                        "manufaktur" => $data['manufaktur'],
                        "tahun_rilis" => $data['tahun_rilis'],
                        "jumlah_penumpang" => $data['jumlah_penumpang'],
                        "harga" => $data['harga'],
                        "tipe_bahanbakar" => $data['tipe_bahanbakar'],
                        "luas_bagasi" => $data['luas_bagasi'],
                        "jumlah_roda" => $data['jumlah_roda'],
                        "luas_area_kargo" => $data['luas_area_kargo'],
                        "ukuran_bagasi" => $data['ukuran_bagasi'],
                        "kapasitas_bensin" => $data['kapasitas_bensin'],
                    );
                    array_push($response["data"], $res);
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
