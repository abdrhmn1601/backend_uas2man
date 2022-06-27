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
$kode_mata_pelajaran = $_GET['kode_mata_pelajaran'] ?? '';

if(empty($kode_mata_pelajaran)){
    $reply['error'] = 'KODE MATA PELAJARAN tidak boleh kosong';
    echo json_encode($reply);
    http_response_code(400);
    exit(0);
}

try{
    $queryCheck = "SELECT * FROM mata_pelajaran where kode_mata_pelajaran = :kode_mata_pelajaran";
    $statement = $connection->prepare($queryCheck);
    $statement->bindValue(':kode_mata_pelajaran', $kode_mata_pelajaran);
    $statement->execute();
    $dataMatapelajaran = $statement->fetch(PDO::FETCH_ASSOC);

    /*
     * Ambil data guru berdasarkan kolom guru
     */
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
         * Transform hasil query dari table buku dan kategori
         * Gabungkan data berdasarkan kolom id kategori
         * Jika id kategori tidak ditemukan, default "tidak diketahui'
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
    $reply['error'] = 'Data tidak ditemukan kode_mata_pelajaran '.$kode_mata_pelajaran;
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