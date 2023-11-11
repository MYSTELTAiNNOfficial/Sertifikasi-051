<?php
require('db-controller.php');
header('Accept: */*');
header('Content-Type: application/json');

$json = file_get_contents('php://input');
$data = json_decode($json);

if (!empty($data)) {
    $nama_petugas = $data->nama_petugas;
    $notelp_petugas = $data->notelp_petugas;
    $email = $data->email;
    $password = $data->password;
} else if (!empty($_POST)) {
    $nama_petugas = $_POST['nama_petugas'];
    $notelp_petugas = $_POST['notelp_petugas'];
    $email = $_POST['email'];
    $password = $_POST['password'];
} else {
    $nama_petugas = null;
    $notelp_petugas = null;
    $email = null;
    $password = null;
}

$query = $conn->prepare("SELECT id FROM petugas WHERE email = ?");
$query->bind_param('s', $email);
$query->execute();
$query->store_result();

if ($query->num_rows > 0) {
    $response['err'] = true;
    $response['message'] = 'User already registered';
    $query->close();
} else {
    $password = password_hash($password, PASSWORD_DEFAULT);
    $query = $conn->prepare("INSERT INTO petugas (id, email, password, nama_petugas, notelp_petugas) VALUES (id,?,?,?,?)");
    $query->bind_param("ssss", $email, $password, $nama_petugas, $notelp_petugas);
    if ($query->execute()) {
        $response['err'] = false;
        $response['message'] = "Register Successful!";
        $query->close();
    }
}
$conn->close();
echo json_encode($response);
?>