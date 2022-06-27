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

$kode_mata_pelajaran = $formData['kode_mata_pelajaran'] ?? '';
$nama_matapelajaran = $formData['nama_matapelajaran'] ?? '';
$kelas = $formData['kelas'] ?? '';
$jurusan = $formData['jurusan'] ?? '';
$kurikulum = $formData['kurikulum'] ?? '';
$nip = $formData['nip'] ?? '';
/**
 * Validation empty fields
 */
$isValidated = true;
if(empty($kode_mata_pelajaran)){
    $reply['error'] = 'Kode Mata Pelajaran harus diisi';
    $isValidated = false;
}
if(empty($nama_matapelajaran)){
    $reply['error'] = 'NAMA Mata Pelajaran harus diisi';
    $isValidated = false;
}
if(empty($kelas)){
    $reply['error'] = 'KELAS harus diisi';
    $isValidated = false;
}
if(empty($jurusan)){
    $reply['error'] = 'Jurusan harus diisi';
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
 * METHOD OK
 * Validation OK
 * Check if data is exist
 */
try{
    $queryCheck = "SELECT * FROM mata_pelajaran where kode_mata_pelajaran = :kode_mata_pelajaran";
    $statement = $connection->prepare($queryCheck);
    $statement->bindValue(':kode_mata_pelajaran', $kode_mata_pelajaran);
    $statement->execute();
    $row = $statement->rowCount();
    /**
     * Jika data tidak ditemukan
     * rowcount == 0
     */
    if($row === 0){
        $reply['error'] = 'Data tidak ditemukan Kode_mata_pelajaran '.$kode_mata_pelajaran;
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
    $query = "UPDATE mata_pelajaran SET nama_matapelajaran = :nama_matapelajaran, kelas = :kelas, jurusan = :jurusan, kurikulum = :kurikulum, nip = :nip
WHERE kode_mata_pelajaran = :kode_mata_pelajaran";
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
 * Get data
 */
$stmSelect = $connection->prepare("SELECT * FROM mata_pelajaran where kode_mata_pelajaran = :kode_mata_pelajaran");
$stmSelect->bindValue(':kode_mata_pelajaran', $kode_mata_pelajaran);
$stmSelect->execute();
$dataMatapelajaran = $stmSelect->fetch(PDO::FETCH_ASSOC);

/*
 * Ambil data guru berdasarkan kolom guru
 */
$dataFinal = [];
if($dataMatapelajaran) {
    $stmGuru = $connection->prepare("select * from guru where nip = :nip");
    $stmGuru->bindValue(':nip', $dataMatapelajaran['nip']);
    $stmGuru->execute();
    $resultGuru = $stmGuru->fetch(PDO::FETCH_ASSOC);
    /*
     * Default guru 'Tidak diketahui'
     */
    $guru = [
        'nip' => $dataMatapelajaran['nip'],
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
            'kode_mata_pelajaran' => $dataMatapelajaran['kode_mata_pelajaran'],
            'nama_matapelajaran' => $dataMatapelajaran['nama_matapelajaran'],
            'kelas' => $dataMatapelajaran['kelas'],
            'jurusan' => $dataMatapelajaran['jurusan'],
            'kurikulum' => $dataMatapelajaran['kurikulum'],
            'nip' => $dataMatapelajaran['nip'],
        ];        
   
}

/**
 * Show output to client
 */
$reply['data'] = $dataFinal;
$reply['status'] = $isOk;
header('Content-Type: application/json');
echo json_encode($reply);