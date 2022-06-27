<?php
include '../../config/koneksi.php';
/**
 * @var $connection PDO
 */

/*
 * Validate http method
 */
if($_SERVER['REQUEST_METHOD'] !== 'GET'){
    header('Content-Type: application/json');
    http_response_code(400);
    $reply['error'] = 'DELETE method required';
    echo json_encode($reply);
    exit();
}

$dataFinal = [];
$nip = $_GET['nip'] ?? '';

if(empty($nip)){
    $reply['error'] = 'NIP tidak boleh kosong';
    echo json_encode($reply);
    http_response_code(400);
    exit(0);
}

try{
    $queryCheck = "SELECT * FROM guru where nip = :nip";
    $statement = $connection->prepare($queryCheck);
    $statement->bindValue(':nip', $nip);
    $statement->execute();
    $dataGuru = $statement->fetch(PDO::FETCH_ASSOC);

    /*
     * Ambil data matapelajaran berdasarkan kolom matapelajaran
     */
    if($dataGuru) {
        $stmMatapelajaran = $connection->prepare("select * from mata_pelajaran where kode_mata_pelajaran = :kode_mata_pelajaran");
        $stmMatapelajaran->bindValue(':kode_mata_pelajaran', $dataGuru['mata_pelajaran']);
        $stmMatapelajaran->execute();
        $resultMatapelajaran = $stmMatapelajaran->fetch(PDO::FETCH_ASSOC);
        /*
         * Default mata pelajaran 'Tidak diketahui'
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
}catch (Exception $exception){
    $reply['error'] = $exception->getMessage();
    echo json_encode($reply);
    http_response_code(400);
    exit(0);
}

/*
 * Show response
 */
if(!$dataFinal){
    $reply['error'] = 'Data tidak ditemukan NIP '.$nip;
    echo json_encode($reply);
    http_response_code(400);
    exit(0);
}

/*
 * Otherwise show data
 */
$reply['status'] = true;
$reply['data'] = $dataFinal;
echo json_encode($reply);