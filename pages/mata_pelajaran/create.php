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
$kode_mata_pelajaran = $_POST['kode_mata_pelajaran'] ?? '';
$nama_matapelajaran = $_POST['nama_matapelajaran'] ?? '';
$kelas = $_POST['kelas'] ?? '';
$jurusan = $_POST['jurusan'] ?? '';
$kurikulum = $_POST['kurikulum'] ?? '';
$nip = $_POST['nip'] ?? '';
/**
 * Validation empty fields
 */
$isValidated = true;
if(empty($kode_mata_pelajaran)){
    $reply['error'] = 'KODE MATA PELAJARAN harus diisi';
    $isValidated = false;
}
if(empty($nama_matapelajaran)){
    $reply['error'] = 'NAMA MATA PELAJARAN harus diisi';
    $isValidated = false;
}
if(empty($kelas)){
    $reply['error'] = 'KELAS harus diisi';
    $isValidated = false;
}
if(empty($jurusan)){
    $reply['error'] = 'JURUSAN harus diisi';
    $isValidated = false;
}
if(empty($kurikulum)){
    $reply['error'] = 'KURIKULUM harus diisi';
    $isValidated = false;
}
if(empty($nip)){
    $reply['error'] = 'NIP harus diisi';
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
    $query = "INSERT INTO mata_pelajaran (kode_mata_pelajaran, nama_matapelajaran, kelas, jurusan, kurikulum, nip) 
VALUES (:kode_mata_pelajaran, :nama_matapelajaran, :kelas, :jurusan, :kurikulum, :nip)";
    $statement = $connection->prepare($query);
    /**
     * Bind params
     */
    $statement->bindValue(":kode_mata_pelajaran", $kode_mata_pelajaran);
    $statement->bindValue(":nama_matapelajaran", $nama_matapelajaran);
    $statement->bindValue(":kelas", $kelas);
    $statement->bindValue(":jurusan", $jurusan);
    $statement->bindValue(":kurikulum", $kurikulum);
    $statement->bindValue(":nip", $nip);
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
$getResult = "SELECT * FROM mata_pelajaran WHERE kode_mata_pelajaran = :kode_mata_pelajaran";
$stm = $connection->prepare($getResult);
$stm->bindValue(':kode_mata_pelajaran', $kode_mata_pelajaran);
$stm->execute();
$result = $stm->fetch(PDO::FETCH_ASSOC);

/*
 * Get Guru
 */
$stmGuru = $connection->prepare("SELECT * FROM guru where nip = :nip");
$stmGuru->bindValue(':nip', $result['nip']);
$stmGuru->execute();
$resultGuru = $stmGuru->fetch(PDO::FETCH_ASSOC);
/*
 * Default guru 'Tidak diketahui'
 */
$guru = [
    'nip' => $result['nip'],
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
    'kode_mata_pelajaran' => $result['kode_mata_pelajaran'],
    'nama_matapelajaran' => $result['nama_matapelajaran'],
    'kelas' => $result['kelas'],
    'jurusan' => $result['jurusan'],
    'kurikulum' => $result['kurikulum'],
    'nip' => $result['nip'],
];

/**
 * Show output to client
 * Set status info true
 */
$reply['data'] = $dataFinal;
$reply['status'] = $isOk;
header('Content-Type: application/json');
echo json_encode($reply);