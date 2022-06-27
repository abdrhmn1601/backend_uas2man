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
$nisn = $_POST['nisn'] ?? '';
$nama_murid = $_POST['nama_murid'] ?? '';
$alamat = $_POST['alamat'] ?? '';
$jenkel = $_POST['jenkel'] ?? '';
$agama = $_POST['agama'] ?? '';
$jurusan = $_POST['jurusan'] ?? '';
$kelas = $_POST['kelas'] ?? '';
$guru = $_POST['guru'] ?? '';

/**
 * Validation empty fields
 */
$isValidated = true;
if(empty($nisn)){
    $reply['error'] = 'NISN harus diisi';
    $isValidated = false;
}
if(empty($nama_murid)){
    $reply['error'] = 'NAMA Murid harus diisi';
    $isValidated = false;
}
if(empty($alamat)){
    $reply['error'] = 'ALAMAT harus diisi';
    $isValidated = false;
}
if(empty($jenkel)){
    $reply['error'] = 'JENIS KELAMIN harus diisi';
    $isValidated = false;
}
if(empty($agama)){
    $reply['error'] = 'AGAMA harus diisi';
    $isValidated = false;
}
if(empty($jurusan)){
    $reply['error'] = 'JURUSAN harus diisi';
    $isValidated = false;
}
if(empty($kelas)){
    $reply['error'] = 'KELAS harus diisi';
    $isValidated = false;
}
if(empty($guru)){
    $reply['error'] = 'GURU harus diisi';
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
    $query = "INSERT INTO murid (nisn, nama_murid, alamat, jenkel, agama, jurusan, kelas, guru) 
VALUES (:nisn, :nama_murid, :alamat, :jenkel, :agama, :jurusan, :kelas, :guru)";
    $statement = $connection->prepare($query);
    /**
     * Bind params
     */
    $statement->bindValue(":nisn", $nisn);
    $statement->bindValue(":nama_murid", $nama_murid);
    $statement->bindValue(":alamat", $alamat);
    $statement->bindValue(":jenkel", $jenkel);
    $statement->bindValue(":agama", $agama);
    $statement->bindValue(":jurusan", $jurusan);
    $statement->bindValue(":kelas", $kelas);
    $statement->bindValue(":guru", $guru);
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
$getResult = "SELECT * FROM murid WHERE nisn = :nisn";
$stm = $connection->prepare($getResult);
$stm->bindValue(':nisn', $nisn);
$stm->execute();
$result = $stm->fetch(PDO::FETCH_ASSOC);

/*
 * Get Guru
 */
$stmGuru = $connection->prepare("SELECT * FROM guru where nip = :nip");
$stmGuru->bindValue(':nip', $result['guru']);
$stmGuru->execute();
$resultGuru = $stmGuru->fetch(PDO::FETCH_ASSOC);
/*
 * Default guru 'Tidak diketahui'
 */
$guru = [
    'nip' => $result['guru'],
    'nama' => 'Tidak diketahui'
];
if ($resultGuru) {
    $Guru = [
        'nip' => $resultGuru['nip'],
        'nama' => $resultGuru['nama_guru']
    ];
}

/*
 * Transform result
 */
$dataFinal = [
    'nisn' => $result['nisn'],
    'nama_murid' => $result['nama_murid'],
    'alamat' => $result['alamat'],
    'jenkel' => $result['jenkel'],
    'agama' => $result['agama'],
    'jurusan' => $result['jurusan'],
    'kelas' => $result['kelas'],
    'guru' => $guru
];

/**
 * Show output to client
 * Set status info true
 */
$reply['data'] = $dataFinal;
$reply['status'] = $isOk;
header('Content-Type: application/json');
echo json_encode($reply);