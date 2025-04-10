<?php

// ⚠️ Remove o session_start() se a sessão já estiver ativa
// Ou use esta verificação:
if (session_status() === PHP_SESSION_NONE) {
    session_start();
}

// Evita qualquer saída anterior ao JSON
ob_start();

header('Content-Type: application/json');

$dest = './scripts/files/';

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

// Limpa qualquer saída que possa ter sido enviada antes
ob_end_clean();

echo json_encode($response);
