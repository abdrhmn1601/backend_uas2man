<?php
include '../../config/koneksi.php';
/**
 * @var $connection PDO
 */

/*
 * Validate http method
 */
if($_SERVER['REQUEST_METHOD'] !== 'DELETE'){
    http_response_code(400);
    $reply['error'] = 'DELETE method required';
    echo json_encode($reply);
    exit();
}

/**
 * Get input data from RAW data
 */
$data = file_get_contents('php://input');
$res = [];
parse_str($data, $res);
$kode_mata_pelajaran = $res['kode_mata_pelajaran'] ?? '';

/**
 *
 * Cek apakah kode matapelajaran tersedia
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
        $reply['error'] = 'Data tidak ditemukan kode_mata_pelajaran '.$kode_mata_pelajaran;
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
 * Hapus data
 */
try{
    $queryCheck = "DELETE FROM mata_pelajaran where kode_mata_pelajaran = :kode_mata_pelajaran";
    $statement = $connection->prepare($queryCheck);
    $statement->bindValue(':kode_mata_pelajaran', $kode_mata_pelajaran);
    $statement->execute();
}catch (Exception $exception){
    $reply['error'] = $exception->getMessage();
    echo json_encode($reply);
    http_response_code(400);
    exit(0);
}

/*
 * Send output
 */
$reply['status'] = true;
echo json_encode($reply);