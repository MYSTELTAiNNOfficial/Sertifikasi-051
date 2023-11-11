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
    $id_customer = $_POST['id_customer'];
    $nama_customer = $_POST['nama_customer'] ?? null;
    $alamat = $_POST['alamat']?? null;
    $no_telepon = $_POST['no_telepon']?? null;
} else if (!empty($data)) {
    $id_customer = $data->id_customer;
    $nama_customer = $data->nama_customer?? null;
    $alamat = $data->alamat?? null;
    $no_telepon = $data->no_telepon?? null;
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
            $query = $conn->prepare("UPDATE customer SET nama_customer = ?, alamat = ?, no_telepon = ? WHERE id = ?");
            $query->bind_param("ssss", $nama_customer, $alamat, $no_telepon, $id_customer);
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
