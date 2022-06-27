<?php
include '../../config/koneksi.php';
/**
 * @var $connection PDO
 */

/*
 * Validate http method
 */
if($_SERVER['REQUEST_METHOD'] !== 'POST'){
    http_response_code(400);
    $reply['error'] = 'POST method required';
    echo json_encode($reply);
    exit();
}
/**
 * Get input data POST
 */
$nip = $_POST['nip'] ?? '';
$nama_guru = $_POST['nama_guru'] ?? '';
$no_hp = $_POST['no_hp'] ?? '';
$alamat = $_POST['alamat'] ?? '';
$jenkel = $_POST['jenkel'] ?? '';
$agama = $_POST['agama'] ?? '';
$mata_pelajaran = $_POST['mata_pelajaran'] ?? '';
/**
 * Validation empty fields
 */
$isValidated = true;
if(empty($nip)){
    $reply['error'] = 'NIP harus diisi';
    $isValidated = false;
}
if(empty($nama_guru)){
    $reply['error'] = 'NAMA Guru harus diisi';
    $isValidated = false;
}
if(empty($no_hp)){
    $reply['error'] = 'No_HP harus diisi';
    $isValidated = false;
}
if(empty($alamat)){
    $reply['error'] = 'ALAMAT harus diisi';
    $isValidated = false;
}
if(empty($jenkel)){
    $reply['error'] = 'Jenkel harus diisi';
    $isValidated = false;
}
if(empty($agama)){
    $reply['error'] = 'AGAMA harus diisi';
    $isValidated = false;
}
if(empty($mata_pelajaran)){
    $reply['error'] = 'MATA PELAJARAN harus diisi';
    $isValidated = false;
}
/*
 * Jika filter gagal
 */
if(!$isValidated){
    echo json_encode($reply);
    http_response_code(400);
    exit(0);
}
/**
 * Method OK
 * Validation OK
 * Prepare query
 */
try{
    $query = "INSERT INTO guru (nip, nama_guru, no_hp, alamat, jenkel, agama, mata_pelajaran) 
VALUES (:nip, :nama_guru, :no_hp, :alamat, :jenkel, :agama, :mata_pelajaran)";
    $statement = $connection->prepare($query);
    /**
     * Bind params
     */
    $statement->bindValue(":nip", $nip);
    $statement->bindValue(":nama_guru", $nama_guru);
    $statement->bindValue(":no_hp", $no_hp);
    $statement->bindValue(":alamat", $alamat);
    $statement->bindValue(":jenkel", $jenkel);
    $statement->bindValue(":agama", $agama);
    $statement->bindValue(":mata_pelajaran", $mata_pelajaran);
    /**
     * Execute query
     */
    $isOk = $statement->execute();
}catch (Exception $exception){
    $reply['error'] = $exception->getMessage();
    echo json_encode($reply);
    http_response_code(400);
    exit(0);
}
/**
 * If not OK, add error info
 * HTTP Status code 400: Bad request
 * @see https://developer.mozilla.org/en-US/docs/Web/HTTP/Status#client_error_responses
 */
if(!$isOk){
    $reply['error'] = $statement->errorInfo();
    http_response_code(400);
}

/*
 * Get last data
 */
$getResult = "SELECT * FROM guru WHERE nip = :nip";
$stm = $connection->prepare($getResult);
$stm->bindValue(':nip', $nip);
$stm->execute();
$result = $stm->fetch(PDO::FETCH_ASSOC);

/*
 * Get Matapelajaran
 */
$stmMatapelajaran = $connection->prepare("SELECT * FROM mata_pelajaran where kode_mata_pelajaran = :kode_mata_pelajaran");
$stmMatapelajaran->bindValue(':kode_mata_pelajaran', $result['mata_pelajaran']);
$stmMatapelajaran->execute();
$resultMatapelajaran = $stmMatapelajaran->fetch(PDO::FETCH_ASSOC);
/*
 * Default mata pelajaran 'Tidak diketahui'
 */
$mata_pelajaran = [
    'kode_mata_pelajaran' => $result['mata_pelajaran'],
    'nama' => 'Tidak diketahui'
];
if ($resultMatapelajaran) {
    $mata_pelajaran = [
        'kode_mata_pelajaran' => $resultMatapelajaran['kode_mata_pelajaran'],
        'nama_matapelajaran' => $resultMatapelajaran['nama_matapelajaran']
    ];
}

/*
 * Transform result
 */
$dataFinal = [
    'nip' => $result['nip'],
    'nama_guru' => $result['nama_guru'],
    'no_hp' => $result['no_hp'],
    'alamat' => $result['alamat'],
    'jenkel' => $result['jenkel'],
    'agama' => $result['agama'],
    'mata_pelajaran' => $mata_pelajaran
];

/**
 * Show output to client
 * Set status info true
 */
$reply['data'] = $dataFinal;
$reply['status'] = $isOk;
header('Content-Type: application/json');
echo json_encode($reply);