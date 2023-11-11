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
            $query = $conn->prepare("SELECT * FROM customer");
            $query->execute();
            $result = $query->get_result();
            if (!empty($result)) {
                $response['err'] = "false";
                $response['message'] = "Data Found";
                $response["data"] = array();
                while ($data = $result->fetch_assoc()) {
                    $res = array(
                        "id" => $data['id'],
                        "nama_customer" => $data['nama_customer'],
                        "alamat" => $data['alamat'],
                        "no_telepon" => $data['no_telepon'],
                    );
                    array_push($response["data"], $res);
                }
            } else {
                $response['err'] = "true";
                $response['message'] = "Data Not Found";
            }

            $query->close();
            $conn->close();
        } else {
            http_response_code(401);
            $response['err'] = "true";
            $response['message'] = "Not Authorized!";
        }
    }
}



echo json_encode($response);
