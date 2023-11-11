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
    $nama = $_POST['nama_customer'];
    $alamat = $_POST['alamat'];
    $notelp = $_POST['no_telepon'];
} else if (!empty($data)) {
    $nama_customer = $data->nama_customer;
    $alamat = $data->alamat;
    $no_telepon = $data->no_telepon;
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
            $query = $conn->prepare("INSERT INTO customer (id, nama_customer, alamat, no_telepon) VALUES (null,?,?,?)");
            $query->bind_param("sss", $nama_customer, $alamat, $no_telepon);
            if ($query->execute()) {
                $response['err'] = "false";
                $response['message'] = "Data Created";
            } else {
                $response['err'] = "true";
                $response['message'] = "Failed to save!";
            }
        } else {
            http_response_code(401);
            $response['err'] = "true";
            $response['message'] = "Not Authorized!";
        }
    }
}



echo json_encode($response);
