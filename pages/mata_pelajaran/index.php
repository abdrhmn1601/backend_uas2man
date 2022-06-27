<?php
include '../../config/koneksi.php';
/**
 * @var $connection PDO
 */
try{
    /**
     * Prepare query matapelajaran limit 50 rows
     */
    $statement = $connection->prepare("select * from mata_pelajaran");
    $isOk = $statement->execute();
    $resultsMatapelajaran = $statement->fetchAll(PDO::FETCH_ASSOC);

    /*
     * Ambil data guru
     */
    $stmGuru = $connection->prepare("select * from guru");
    $isOk = $stmGuru->execute();
    $resultGuru = $stmGuru->fetchAll(PDO::FETCH_ASSOC);

    /*
     * Transform hasil query dari table matapelajaran dan guru
     * Gabungkan data berdasarkan kolom nip
     * Jika kode nip tidak ditemukan, default "tidak diketahui'
     */
    $finalResults = [];
    $idsGuru = array_column($resultGuru, 'nip');
    foreach ($resultsMatapelajaran as $matapelajaran) {
        /*
         * Default guru 'Tidak diketahui'
         */
        $guru = [
            'nip' => $matapelajaran['nip'],
            'nama_guru' => 'Tidak diketahui'
        ];
        /*
         * Cari guru berdasarkan NIP
         */
        $findByIdGuru = array_search($matapelajaran['nip'], $idsGuru);

        /*
         * Jika kode matapelajaran ditemukan
         */
        if ($findByIdGuru !== false) {
            $findDataGuru = $resultGuru[$findByIdGuru];
            $guru = [
                'nip' => $findDataGuru['nip'],
                'nama_guru' => $findDataGuru['nama_guru']
            ];
        }
            $finalResults[] = [
                'kode_mata_pelajaran' => $matapelajaran['kode_mata_pelajaran'],
                'nama_matapelajaran' => $matapelajaran['nama_matapelajaran'],
                'kelas' => $matapelajaran['kelas'],
                'jurusan' => $matapelajaran['jurusan'],
                'kurikulum' => $matapelajaran['kurikulum'],
                'nip' => $guru
            ];
    }
        $reply['data'] = $finalResults;
    
}catch (Exception $exception){
    $reply['error'] = $exception->getMessage();
    echo json_encode($reply);
    http_response_code(400);
    exit(0);
}

if(!$isOk){
    $reply['error'] = $statement->errorInfo();
    http_response_code(400);
}
/*
 * Query OK
 * set status == true
 * Output JSON
 */
$reply['status'] = true;
header('Content-Type: application/json');
echo json_encode($reply);