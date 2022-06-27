<?php
include '../../config/koneksi.php';
/**
 * @var $connection PDO
 */

/*
 * Validate http method
 */
if($_SERVER['REQUEST_METHOD'] !== 'PATCH'){
    header('Content-Type: application/json');
    http_response_code(400);
    $reply['error'] = 'PATCH method required';
    echo json_encode($reply);
    exit();
}
/**
 * Get input data PATCH
 */
$formData = [];
parse_str(file_get_contents('php://input'), $formData);

$nip = $formData['nip'] ?? '';
$nama_guru = $formData['nama_guru'] ?? '';
$no_hp = $formData['no_hp'] ?? '';
$alamat = $formData['alamat'] ?? '';
$jenkel = $formData['jenkel'] ?? '';
$agama = $formData['agama'] ?? '';
$mata_pelajaran = $formData['mata_pelajaran'] ?? '';

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
    $reply['error'] = 'NO HP harus diisi';
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
 * METHOD OK
 * Validation OK
 * Check if data is exist
 */
try{
    $queryCheck = "SELECT * FROM guru where nip = :nip";
    $statement = $connection->prepare($queryCheck);
    $statement->bindValue(':nip', $nip);
    $statement->execute();
    $row = $statement->rowCount();
    /**
     * Jika data tidak ditemukan
     * rowcount == 0
     */
    if($row === 0){
        $reply['error'] = 'Data tidak ditemukan NIP '.$nip;
        echo json_encode($reply);
        http_response_code(400);
        exit(0);
    }
}catch (Exception $exception){
    $reply['error'] = $exception->getMessage();
    echo json_encode($reply);
    http_response_code(400);
    exit(0);
}

/**
 * Prepare query
 */
try{
    $fields = [];
    $query = "UPDATE guru SET nama_guru = :nama_guru, no_hp = :no_hp, alamat = :alamat, jenkel = :jenkel, agama = :agama, mata_pelajaran = :mata_pelajaran
WHERE nip = :nip";
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
 * Get data
 */
$stmSelect = $connection->prepare("SELECT * FROM guru where nip = :nip");
$stmSelect->bindValue(':nip', $nip);
$stmSelect->execute();
$dataGuru = $stmSelect->fetch(PDO::FETCH_ASSOC);

/*
 * Ambil data mata pelajaran berdasarkan mata pelajaran
 */
$dataFinal = [];
if($dataGuru) {
    $stmMatapelajaran = $connection->prepare("select * from mata_pelajaran where kode_mata_pelajaran = :kode_mata_pelajaran");
    $stmMatapelajaran->bindValue(':kode_mata_pelajaran', $dataGuru['mata_pelajaran']);
    $stmMatapelajaran->execute();
    $resultMatapelajaran = $stmMatapelajaran->fetch(PDO::FETCH_ASSOC);
    /*
     * Default guru 'Tidak diketahui'
     */
    $mata_pelajaran = [
        'kode_mata_pelajaran' => $dataGuru['mata_pelajaran'],
        'nama' => 'Tidak diketahui'
    ];
    if ($resultMatapelajaran) {
        $mata_pelajaran = [
            'kode_mata_pelajaran' => $resultMatapelajaran['kode_mata_pelajaran'],
            'nama_matapelajaran' => $resultMatapelajaran['nama_matapelajaran']
        ];
    }
    
        $dataFinal = [
            'nip' => $dataGuru['nip'],
            'nama_guru' => $dataGuru['nama_guru'],
            'no_hp' => $dataGuru['no_hp'],
            'alamat' => $dataGuru['alamat'],
            'jenkel' => $dataGuru['jenkel'],
            'agama' => $dataGuru['agama'],
            'mata_pelajaran' => $mata_pelajaran
        ];        
   
}

/**
 * Show output to client
 */
$reply['data'] = $dataFinal;
$reply['status'] = $isOk;
header('Content-Type: application/json');
echo json_encode($reply);