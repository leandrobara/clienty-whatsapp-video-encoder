# Video Encoder PHP (FFmpeg + Docker + Railway)

Este proyecto permite subir un video desde un formulario web y descargarlo reencodeado en formato .mp4 automáticamente usando FFmpeg con un preset optimizado para compatibilidad (WhatsApp, redes sociales, celulares, navegadores).
El sistema funciona con PHP + Apache + FFmpeg, corre dentro de Docker y puede desplegarse fácilmente en Railway.

---

## Funcionalidades

- Subida de videos mediante formulario HTML.
- Validación de tamaño máximo y tipo MIME.
- Reencodeo automático usando FFmpeg.
- Preset de conversión:
  - libx264 (H.264)
  - Profile Main, Level 3.1
  - Bitrate ~1.1 Mbps
  - Resolución 854x480 manteniendo aspecto
  - Pixel format yuv420p (compatibilidad universal)
  - Audio AAC (96 kbps)
  - movflags faststart para reproducción instantánea
- Descarga automática del video convertido.
- Limpieza de archivos temporales.
- Log de errores de FFmpeg: ffmpeg_error.log

---

## Dependencias

Dentro del contenedor Docker se instalan:

- PHP 8.x
- Apache
- FFmpeg

---

## Construcción de la imagen Docker

```
docker build -t video-encoder-php .
```

---

## Modo desarrollo (sin necesidad de rebuild al modificar PHP)

```
docker run --rm -p 8000:80 \
  -v /var/www/html/projects/video-encoder-php:/var/www/html \
  --name video-encoder \
  video-encoder-php
```

Abrir navegador:

http://localhost:8000

---

## Preset FFmpeg utilizado

Comando exacto aplicado por el sistema:

```
ffmpeg -y -i input.mp4  
-c:v libx264
-profile:v main
-level 3.1
-b:v 1100k 
-maxrate 1300k -bufsize 2600k   
-vf "scale=854:480:force_original_aspect_ratio=decrease,pad=ceil(iw/2)*2:ceil(ih/2)*2,setsar=1"   
-pix_fmt yuv420p   
-c:a aac -b:a 96k   -movflags +faststart   output.mp4
```

Este preset asegura:

- Compatibilidad total (WhatsApp, iOS, Android, navegadores).
- Peso reducido sin perder demasiada calidad.
- Reproducción inmediata gracias a faststart.

---

## Probar encodeo localmente

- Levantar el servidor Docker.
- Abrir el navegador.
- Subir un video.
- Descargar el archivo procesado.

---

## Despliegue en Railway

Railway detecta automáticamente el Dockerfile y construye el contenedor.

---

## Licencia

MIT — Libre para usar y modificar.
