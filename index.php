<?php
    require_once __DIR__ . '/config.php';
?>

<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <title>Video a MP4 optimizado para WhatsApp</title>
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <link rel="icon" type="image/svg+xml" href="favicon.svg">
    <link rel="stylesheet" href="styles.css">
</head>
<body>
    <div class="app-card">
        <h1>Video a MP4 optimizado</h1>
        <p class="intro">
            Cargá un video de hasta <span class="limit-tag"><?php echo htmlspecialchars($maxFileSizeMb); ?> MB</span><br />
            Una vez finalizada la subida, el sistema procesará el archivo y disparará automáticamente la descarga de la versión optimizada para WhatsApp.<br />
            El tiempo de procesamiento puede llevar unos minutos.
        </p>

        <form
            class="upload-form"
            action="process_video.php"
            method="post"
            enctype="multipart/form-data"
            data-max-mb="<?php echo htmlspecialchars($maxFileSizeMb); ?>"
        >
            <label class="input-label" for="video-input">Elegí tu archivo</label>
            <input
                id="video-input"
                type="file"
                name="video"
                accept=".mp4,.mov,.m4v,.mkv,.webm,.avi,.mpg,.mpeg,video/*"
                required
            >
            <button type="submit">Subir y convertir</button>
        </form>

    </div>
    <div class="loader-overlay" id="loader-overlay" aria-hidden="true">
        <div class="spinner" aria-hidden="true"></div>
        <span>Procesando tu video…</span>
    </div>
    <script src="app.js" defer></script>
</body>
</html>
