<?php
require_once('./vendor/autoload.php');
require_once('db-controller.php');
use Firebase\JWT\JWT as jwt;
use Dotenv\Dotenv;

header('Accept: */*');
header('Content-Type: application/json');
$dotenv = Dotenv::createImmutable(__DIR__);
$dotenv->load();
$json = file_get_contents('php://input');
$data = json_decode($json);

if (!empty($data)) {
    $email = $data->email;
    $password = $data->password;
} else if (!empty($_POST)) {
    $email = $_POST['email'];
    $password = $_POST['password'];
} else {
    $email = null;
    $password = null;
}
$unhashed_password = $password;
$query = $conn->prepare("SELECT * FROM petugas WHERE email = ?");
$query->bind_param('s', $email);
$query->execute();
$query->store_result();
if ($query->num_rows > 0) {
    $query->bind_result($id, $email,$password, $nama_petugas, $notelp_petugas);
    $query->fetch();
    if (password_verify($unhashed_password, $password)) {
        $key = $_ENV['ACCESS_TOKEN_SECRET'];
        $expTime = time() + 60 * 60 * 24 * 60;
        $payload = array(
            "id" => $id,
            "email" => $email,
            "nama_petugas" => $nama_petugas,
            "notelp_petugas" => $notelp_petugas,
            'exp' => $expTime
        );
        $jwt = jwt::encode($payload, $key, "HS512");
        $response["id"] = $id;
        $response["user"] = $nama_petugas;
        $response["token"] = $jwt;
        $response['err'] = false;
        $response['message'] = "Login Successful!";
    } else {
        $response["id"] = 0;
        $response["user"] = ""; 
        $response['err'] = true;
        $response['message'] = "Email or Password incorrect!";
    }
} else {
    $response["id"] = 0;
    $response["user"] = ""; 
    $response['err'] = true;
    $response['message'] = "Email or Password incorrect!";
}

$query->close();
$conn->close();

echo json_encode($response);
?>