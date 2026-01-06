FROM php:8.2-apache

# Instalar FFmpeg
RUN apt-get update && \
    apt-get install -y ffmpeg && \
    rm -rf /var/lib/apt/lists/*

# Configuración de PHP para permitir archivos grandes y más tiempo de ejecución
RUN { \
      echo "upload_max_filesize = 200M"; \
      echo "post_max_size = 200M"; \
      echo "max_execution_time = 600"; \
      echo "memory_limit = 512M"; \
    } > /usr/local/etc/php/conf.d/uploads.ini

# Habilitar mod_rewrite (por si algún día lo necesitás)
RUN a2enmod rewrite

# Copiar el código al DocumentRoot de Apache
WORKDIR /var/www/html
COPY . /var/www/html

# Apache ya escucha en el puerto 80
EXPOSE 80

CMD ["apache2-foreground"]
