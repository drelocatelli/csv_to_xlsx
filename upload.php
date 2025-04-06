<?php

$dest = './scripts/files/';

# Cria a pasta se não existir
if (!is_dir($dest)) {
    mkdir($dest, 0777, true);
}

$response = [];

if (isset($_FILES['files'])) {
    foreach ($_FILES['files']['tmp_name'] as $key => $tmp_name) {
        $name = basename($_FILES['files']['name'][$key]);
        $outputDest = $dest . $name;

        if (move_uploaded_file($tmp_name, $outputDest)) {
            $response[] = [
                'file' => $name,
                'status' => 'success',
                'message' => "Arquivo $name enviado com sucesso"
            ];
        } else {
            $response[] = [
                'file' => $name,
                'status' => 'error',
                'message' => "Erro ao mover o arquivo $name"
            ];
        }
    }
}

echo json_encode($response);
