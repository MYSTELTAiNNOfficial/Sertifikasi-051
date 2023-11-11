<?php

require_once('db-controller.php');
header('Accept: */*');
header('Content-Type: application/json');

$json = file_get_contents('php://input');
$data = json_decode($json);

if(!empty($_POST)) {
    $rid = $_POST["id"];
} else if (!empty($data)) {
    $rid = $data->id;
} else {
    $rid = 0;
}

$query = $conn->prepare('SELECT * FROM resto WHERE id = ?');
$query->bind_param('i', $rid);
$query->execute();

$result = $query->get_result();
$data = $result->fetch_assoc();

if(!empty($data)){
    $obj = array(
        'nama_resto' => $data['nama_resto'],
        'img' => 'https://tugaskuliahku.xyz/api/IMG/'.$data['path_foto'],
        'rating' => $data['rating_resto'],
        'detail' => $data['detail_resto'],
        'latitude' => $data['latitude_map'],
        'longitude' => $data['longitude_map'],
        'fav' => $data['is_favorite']
    );
    $response["resto"] = $obj;
}

$query->close();
$conn->close();

echo json_encode($response);


?>