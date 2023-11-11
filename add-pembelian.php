<?php

# Credit for upload image = https://www.youtube.com/watch?v=DOmHg-pDv9U


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
    $id_kendaraan = $_POST['id_kendaraan'];;
} else if (!empty($data)) {
    $id_customer = $data->id_customer;
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
            $query = $conn->prepare("INSERT INTO pembelian (id, id_customer, id_kendaraan) VALUES (null,?,?)");
            $query->bind_param("ss", $id_customer, $id_kendaraan);
            if ($query->execute()) {
                $response['err'] = "false";
                $response['message'] = "Data Created";
            } else {
                $response['err'] = "true";
                $response['message'] = "Data Not Created";
            }
        } else {
            http_response_code(401);
            $response['err'] = "true";
            $response['message'] = "Not Authorized!";
        }
    }
}



echo json_encode($response);
