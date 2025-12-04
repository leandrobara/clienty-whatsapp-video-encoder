# Video encoder para WhatsApp

Aplicación sencilla en PHP + JavaScript que toma un video del usuario, lo transcodifica con FFmpeg a un perfil optimizado para WhatsApp (H.264 + AAC a 480p) y devuelve el archivo listo para descargar.

## Requisitos

- PHP 7.4+ con extensiones `fileinfo` y `mbstring`.
- FFmpeg instalado y disponible en el `PATH`.
- Servidor web con soporte para PHP (Apache, Nginx + PHP-FPM, etc.).

## Puesta en marcha

1. Clonar este repositorio en el servidor web.
2. Verificar que `uploads/` y `outputs/` existan o dejar que el script las cree (el proceso se encarga y limpia los archivos temporales al terminar).
3. Revisar `config.php`:
   - `maxFileSizeMb` define el límite de subida (20 MB por defecto).
   - `uploadDir` y `outputDir` apuntan a las carpetas temporales.
4. Asegurarse de que el binario `ffmpeg` esté instalado (`ffmpeg -version`) y tenga permisos de ejecución para el usuario del servidor.

Una vez desplegado, acceder a `index.php` desde el navegador para usar el formulario.

## Flujo de trabajo

1. El usuario selecciona un video en el formulario (`index.php` + `app.js`).
2. El frontend valida extensión y peso, muestra un loader y envía la petición vía `fetch`.
3. `process_video.php` valida tamaño/MIME, genera archivos temporales y ejecuta FFmpeg con los parámetros definidos.
4. Al finalizar, envía el MP4 resultante con `Content-Disposition: attachment; filename="output_whatsapp.mp4"`.
5. El frontend fuerza la descarga automáticamente y se eliminan los archivos temporales (`uploads/` y `outputs/` quedan limpios).

Si FFmpeg falla, se guarda la salida en `ffmpeg_error.log` para diagnosticar.

## Personalización

- **Tamaño máximo**: editar `$maxFileSizeMb` en `config.php`.
- **Calidad/bitrate**: ajustar los parámetros de FFmpeg dentro de `process_video.php`.
- **Extensiones permitidas**: modificar el arreglo `allowedExtensions` en `app.js` y el atributo `accept` del input en `index.php`.

## Desarrollo local

```bash
php -S localhost:8000
# Abrir http://localhost:8000/index.php
```

Recordá que FFmpeg debe estar instalado localmente para poder convertir los videos.

## Docker

Este repo incluye un `Dockerfile` basado en `php:8.2-apache` con FFmpeg instalado y la config necesaria para manejar subidas grandes.

```bash
docker build -t video-encoder .
docker run --rm -p 8080:80 video-encoder
# Abrir http://localhost:8080
```

El contenedor ya expone el sitio en el puerto 80 y trae Apache + PHP listos para usar.
