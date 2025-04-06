<?php

$zip = new ZipArchive();
$folderPath = __DIR__ . '/scripts/output';
$zipPath = $folderPath . '/files.zip';

if ($zip->open($zipPath, ZipArchive::CREATE | ZipArchive::OVERWRITE) !== TRUE) {
    exit("Não foi possível criar o arquivo ZIP.\n");
}

function addFolderToZip($folder, $zip, $parentFolder = '') {
    $files = scandir($folder);
    foreach ($files as $file) {
        if ($file === '.' || $file === '..') continue;

        $fullPath = $folder . '/' . $file;
        $localPath = $parentFolder . $file;

        if (is_dir($fullPath)) {
            $zip->addEmptyDir($localPath . '/');
            addFolderToZip($fullPath, $zip, $localPath . '/');
        } else {
            $zip->addFile($fullPath, $localPath);
        }
    }
}

addFolderToZip($folderPath, $zip);
$zip->close();

// Força o download
if (file_exists($zipPath)) {
    header('Content-Type: application/zip');
    header('Content-Disposition: attachment; filename="files.zip"');
    header('Content-Length: ' . filesize($zipPath));
    readfile($zipPath);
    exit;
} else {
    echo "Arquivo ZIP não encontrado.";
}
