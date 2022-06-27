<?php
include '../../config/koneksi.php';
/**
 * @var $connection PDO
 */
try{
    /**
     * Prepare query murid limit 50 rows
     */
    $statement = $connection->prepare("select * from murid");
    $isOk = $statement->execute();
    $resultsMurid = $statement->fetchAll(PDO::FETCH_ASSOC);

    /*
     * Ambil data guru
     */
    $stmGuru = $connection->prepare("select * from Guru");
    $isOk = $stmGuru->execute();
    $resultGuru = $stmGuru->fetchAll(PDO::FETCH_ASSOC);

    /*
     * Transoform hasil query dari table murid dan guru
     * Gabungkan data berdasarkan kolom nip guru
     * Jika nip guru tidak ditemukan, default "tidak diketahui'
     */
    $finalResults = [];
    $idsGuru = array_column($resultGuru, 'nip');
    foreach ($resultsMurid as $murid) {
        /*
         * Default guru 'Tidak diketahui'
         */
        $guru = [
            'nip' => $murid['guru'],
            'nama' => 'Tidak diketahui'
        ];
        /*
         * Cari guru berdasarkan nip
         */
        $findByIdGuru = array_search($murid['guru'], $idsGuru);

        /*
         * Jika nip ditemukan
         */
        if ($findByIdGuru !== false) {
            $findDataGuru = $resultGuru[$findByIdGuru];
            $guru = [
                'nip' => $findDataGuru['nip'],
                'nama' => $findDataGuru['nama_guru']
            ];
        }
    

        

        /*
         * Transoform hasil query dari table mahasiswa dan matakuliah
         * Gabungkan data berdasarkan kolom kodemk
         * Jika kodemk tidak ditemukan, default "tidak diketahui'
         */
        

            $finalResults[] = [
                'nisn' => $murid['nisn'],
                'nama_murid' => $murid['nama_murid'],
                'alamat' => $murid['alamat'],
                'jenkel' => $murid['jenkel'],
                'agama' => $murid['agama'],
                'jurusan' => $murid['jurusan'],
                'kelas' => $murid['kelas'],
                'guru' => $guru,
                
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