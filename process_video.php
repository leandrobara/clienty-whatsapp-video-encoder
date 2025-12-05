<?php

require_once __DIR__ . '/config.php';

// Aseguramos carpetas necesarias
if (!is_dir($uploadDir)) {
    mkdir($uploadDir, 0755, true);
}
if (!is_dir($outputDir)) {
    mkdir($outputDir, 0755, true);
}

if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    http_response_code(405);
    exit('Método no permitido.');
}

if (!isset($_FILES['video']) || $_FILES['video']['error'] !== UPLOAD_ERR_OK) {
    http_response_code(400);
    exit('Error al subir el archivo.');
}

$file = $_FILES['video'];

// Validar tamaño
if ($file['size'] > $maxFileSizeMb * 1024 * 1024) {
    http_response_code(400);
    exit('El archivo es demasiado grande. Límite: ' . $maxFileSizeMb . ' MB.');
}

// Validar tipo MIME básico
$finfo = finfo_open(FILEINFO_MIME_TYPE);
$mime  = finfo_file($finfo, $file['tmp_name']);
finfo_close($finfo);

if (strpos($mime, 'video/') !== 0) {
    http_response_code(400);
    exit('El archivo subido no parece ser un video válido.');
}

$jobId       = uniqid('vid_', true);
$inputPath   = $uploadDir . '/' . $jobId . '_input.mp4';
$outputPath  = $outputDir . '/' . $jobId . '_output_whatsapp.mp4';

if (!move_uploaded_file($file['tmp_name'], $inputPath)) {
    http_response_code(500);
    exit('No se pudo guardar el archivo subido.');
}

$filter = 'scale=854:480:force_original_aspect_ratio=decrease,'
        . 'pad=ceil(iw/2)*2:ceil(ih/2)*2,setsar=1';

$originalName = pathinfo($file['name'], PATHINFO_FILENAME);
$safeOriginalName = preg_replace('/[^A-Za-z0-9-_]/', '_', $originalName);
if ($safeOriginalName === '' || $safeOriginalName === null) {
    $safeOriginalName = 'video';
}
// Microtime-based suffix to avoid colisiones en descargas simultáneas
$hashSource = str_replace('.', '', sprintf('%.6f', microtime(true)));
$hash = substr($hashSource, -8);
$downloadName = $hash . '-' . $safeOriginalName . '.mp4';

$cmd = sprintf(
    'ffmpeg -y -i %s ' .
    '-c:v libx264 -profile:v main -level 3.1 ' .
    '-b:v 1100k -maxrate 1300k -bufsize 2600k ' .
    '-vf %s ' .
    '-pix_fmt yuv420p ' .
    '-c:a aac -b:a 96k ' .
    '-movflags +faststart ' .
    '%s 2>&1',
    escapeshellarg($inputPath),
    escapeshellarg($filter),
    escapeshellarg($outputPath)
);

$output = [];
$returnVar = 0;
exec($cmd, $output, $returnVar);

@unlink($inputPath);

if ($returnVar !== 0 || !file_exists($outputPath)) {
    file_put_contents(__DIR__ . '/ffmpeg_error.log', implode("\n", $output) . "\n\n", FILE_APPEND);
    http_response_code(500);
    exit('Ocurrió un error al procesar el video. Revisá ffmpeg_error.log en el servidor.');
}

header('Content-Type: video/mp4');
header('Content-Disposition: attachment; filename="' . $downloadName . '"');
header('Content-Length: ' . filesize($outputPath));

readfile($outputPath);

@unlink($outputPath);
exit;
