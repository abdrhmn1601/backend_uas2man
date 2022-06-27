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
$nisn = $_GET['nisn'] ?? '';

if(empty($nisn)){
    $reply['error'] = 'NISN tidak boleh kosong';
    echo json_encode($reply);
    http_response_code(400);
    exit(0);
}

try{
    $queryCheck = "SELECT * FROM murid where nisn = :nisn";
    $statement = $connection->prepare($queryCheck);
    $statement->bindValue(':nisn', $nisn);
    $statement->execute();
    $dataMurid = $statement->fetch(PDO::FETCH_ASSOC);

    /*
     * Ambil data guru berdasarkan kolom guru
     */
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
         * Transoform hasil query dari table buku dan kategori
         * Gabungkan data berdasarkan kolom id kategori
         * Jika id kategori tidak ditemukan, default "tidak diketahui'
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
    $reply['error'] = 'Data tidak ditemukan NISN '.$nisn;
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