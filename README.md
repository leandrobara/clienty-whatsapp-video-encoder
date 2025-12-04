# Video Encoder PHP (FFmpeg + Docker + Railway)

Este proyecto permite subir un video desde un formulario web y descargarlo reencodeado automáticamente usando FFmpeg con un preset optimizado para compatibilidad (WhatsApp, redes sociales, celulares, navegadores).

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

## Estructura del proyecto

project-root/
│
├── Dockerfile
├── index.php
├── config.php
│
├── uploads/        (archivos subidos por el usuario)
└── outputs/        (archivos encodeados)

---

## Dependencias

No requiere instalaciones externas en tu máquina excepto Docker.

Dentro del contenedor se instalan:

- PHP 8.x
- Apache
- FFmpeg

---

## Construcción de la imagen Docker

Abrir terminal y ejecutar:

docker build -t video-encoder-php .

---

## Ejecutar el proyecto localmente

docker run --rm -p 8000:80 --name video-encoder video-encoder-php

Abrir navegador:

http://localhost:8000

---

## Modo desarrollo (sin necesidad de rebuild al modificar PHP)

docker run --rm -p 8000:80   -v "$PWD:/var/www/html"   --name video-encoder   video-encoder-php

Esto monta el código local dentro del contenedor.

---

## Preset FFmpeg utilizado

Comando exacto aplicado por el sistema:

ffmpeg -y -i input.mp4   -c:v libx264 -profile:v main -level 3.1   -b:v 1100k -maxrate 1300k -bufsize 2600k   -vf "scale=854:480:force_original_aspect_ratio=decrease,pad=ceil(iw/2)*2:ceil(ih/2)*2,setsar=1"   -pix_fmt yuv420p   -c:a aac -b:a 96k   -movflags +faststart   output.mp4

Este preset asegura:

- Compatibilidad total (WhatsApp, iOS, Android, navegadores).
- Peso reducido sin perder demasiada calidad.
- Reproducción inmediata gracias a faststart.

---

## Probar encodeo localmente

1. Levantar el servidor Docker.
2. Abrir el navegador.
3. Subir un video.
4. Descargar el archivo procesado.

---

## Verificar propiedades del video (ffprobe)

Para asegurarte de que el encodeo se aplicó correctamente:

ffprobe -hide_banner -show_streams -show_format archivo_encodeado.mp4

Valores esperados:

- codec_name=h264
- profile=Main
- level=31
- width=854, height=480
- pix_fmt=yuv420p
- bit_rate ≈ 1100000
- audio: aac con bitrate cercano a 96000

---

## Despliegue en Railway

Railway detecta automáticamente el Dockerfile y construye el contenedor.

### Entrar al entorno productivo

#### Desde la interfaz web:

1. Abrir el proyecto.
2. Seleccionar el servicio desplegado.
3. Ir a la pestaña "Shell".
4. Esto abre una terminal dentro del contenedor productivo.

#### Desde la CLI:

npm install -g @railway/cli
railway login
railway link
railway shell

---

## Dockerfile usado en este proyecto

FROM php:8.2-apache

RUN apt-get update &&     apt-get install -y ffmpeg &&     rm -rf /var/lib/apt/lists/*

# Config PHP
RUN {     echo "upload_max_filesize = 200M";     echo "post_max_size = 200M";     echo "max_execution_time = 600";     echo "memory_limit = 512M"; } > /usr/local/etc/php/conf.d/uploads.ini

RUN a2enmod rewrite

WORKDIR /var/www/html
COPY . /var/www/html

---

## Licencia

MIT — Libre para usar y modificar.

---

## Autor

Desarrollado por Leandro.
