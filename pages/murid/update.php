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

$nisn = $formData['nisn'] ?? '';
$nama_murid = $formData['nama_murid'] ?? '';
$alamat = $formData['alamat'] ?? '';
$jenkel = $formData['jenkel'] ?? '';
$agama = $formData['agama'] ?? '';
$jurusan = $formData['jurusan'] ?? '';
$kelas = $formData['kelas'] ?? '';
$guru = $formData['guru'] ?? '';

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
 * METHOD OK
 * Validation OK
 * Check if data is exist
 */
try{
    $queryCheck = "SELECT * FROM murid where nisn = :nisn";
    $statement = $connection->prepare($queryCheck);
    $statement->bindValue(':nisn', $nisn);
    $statement->execute();
    $row = $statement->rowCount();
    /**
     * Jika data tidak ditemukan
     * rowcount == 0
     */
    if($row === 0){
        $reply['error'] = 'Data tidak ditemukan NISN '.$nisn;
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
    $query = "UPDATE murid SET nama_murid = :nama_murid, alamat = :alamat, jenkel = :jenkel, agama = :agama, jurusan = :jurusan, kelas = :kelas, guru = :guru 
WHERE nisn = :nisn";
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
 * Get data
 */
$stmSelect = $connection->prepare("SELECT * FROM murid where nisn = :nisn");
$stmSelect->bindValue(':nisn', $nisn);
$stmSelect->execute();
$dataMurid = $stmSelect->fetch(PDO::FETCH_ASSOC);

/*
 * Ambil data guru berdasarkan kolom guru
 */
$dataFinal = [];
if($dataMurid) {
    $stmGuru = $connection->prepare("select * from guru where nip = :nip");
    $stmGuru->bindValue(':nip', $dataMurid['guru']);
    $stmGuru->execute();
    $resultGuru = $stmGuru->fetch(PDO::FETCH_ASSOC);
    /*
     * Default guru 'Tidak diketahui'
     */
    $guru = [
        'nip' => $dataMurid['guru'],
        'nama' => 'Tidak diketahui'
    ];
    if ($resultGuru) {
        $guru = [
            'nip' => $resultGuru['nip'],
            'nama' => $resultGuru['nama_guru']
        ];
    }
    
        
        /*
         * Transoform hasil query dari table mahasiswa dan matakuliah
         * Gabungkan data berdasarkan kolom kodemk
         * Jika kodemk tidak ditemukan, default "tidak diketahui'
         */
        $dataFinal = [
            'nisn' => $dataMurid['nisn'],
            'nama_murid' => $dataMurid['nama_murid'],
            'alamat' => $dataMurid['alamat'],
            'jenkel' => $dataMurid['jenkel'],
            'agama' => $dataMurid['agama'],
            'jurusan' => $dataMurid['jurusan'],
            'kelas' => $dataMurid['kelas'],
            'guru' => $guru
        ];        
   
}

/**
 * Show output to client
 */
$reply['data'] = $dataFinal;
$reply['status'] = $isOk;
header('Content-Type: application/json');
echo json_encode($reply);