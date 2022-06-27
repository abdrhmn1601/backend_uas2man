<?php
include '../../config/koneksi.php';
/**
 * @var $connection PDO
 */
try{
    /**
     * Prepare query guru limit 50 rows
     */
    $statement = $connection->prepare("select * from guru");
    $isOk = $statement->execute();
    $resultsGuru = $statement->fetchAll(PDO::FETCH_ASSOC);

    /*
     * Ambil data matapelajaran
     */
    $stmMatapelajaran = $connection->prepare("select * from mata_pelajaran");
    $isOk = $stmMatapelajaran->execute();
    $resultMatapelajaran = $stmMatapelajaran->fetchAll(PDO::FETCH_ASSOC);

    /*
     * Transoform hasil query dari table guru dan matapelajaran
     * Gabungkan data berdasarkan kolom kode matapelajaran
     * Jika kode matapelajaran tidak ditemukan, default "tidak diketahui'
     */
    $finalResults = [];
    $idsMatapelajaran = array_column($resultMatapelajaran, 'kode_mata_pelajaran');
    foreach ($resultsGuru as $guru) {
        /*
         * Default matapelajaran 'Tidak diketahui'
         */
        $matapelajaran = [
            'kode_mata_pelajaran' => $guru['mata_pelajaran'],
            'nama_matapelajaran' => 'Tidak diketahui'
        ];
        /*
         * Cari matapelajaran berdasarkan kode matapelajaran
         */
        $findByIdMatapelajaran = array_search($guru['mata_pelajaran'], $idsMatapelajaran);

        /*
         * Jika kode matapelajaran ditemukan
         */
        if ($findByIdMatapelajaran !== false) {
            $findDataMatapelajaran = $resultMatapelajaran[$findByIdMatapelajaran];
            $matapelajaran = [
                'kode_mata_pelajaran' => $findDataMatapelajaran['kode_mata_pelajaran'],
                'nama_matapelajaran' => $findDataMatapelajaran['nama_matapelajaran']
            ];
        }
        

            $finalResults[] = [
                'nip' => $guru['nip'],
                'nama_guru' => $guru['nama_guru'],
                'no_hp' => $guru['no_hp'],
                'alamat' => $guru['alamat'],
                'jenkel' => $guru['jenkel'],
                'agama' => $guru['agama'],
                'mata_pelajaran' => $matapelajaran 
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