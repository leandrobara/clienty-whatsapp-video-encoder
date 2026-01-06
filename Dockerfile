FROM php:8.2-apache

# Instalar FFmpeg
RUN apt-get update && \
    apt-get install -y ffmpeg && \
    rm -rf /var/lib/apt/lists/*

# ✅ ANTI "More than one MPM loaded"
# 1) apaga todo lo que pueda estar habilitado
# 2) borra symlinks por si quedaron "pegados"
# 3) habilita solo prefork
RUN set -eux; \
    a2dismod mpm_event mpm_worker mpm_prefork || true; \
    rm -f /etc/apache2/mods-enabled/mpm_*.load /etc/apache2/mods-enabled/mpm_*.conf || true; \
    a2enmod mpm_prefork; \
    apache2ctl -M | grep -E "mpm_" || true; \
    apache2ctl -t

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

CMD ["bash", "-lc", "\
set -eux; \
echo '--- mods-enabled mpm ---'; ls -la /etc/apache2/mods-enabled | grep -i mpm || true; \
echo '--- conf-enabled containing mpm ---'; grep -Rni \"mpm_\" /etc/apache2/conf-enabled /etc/apache2/apache2.conf /etc/apache2/mods-enabled || true; \
echo '--- apache -M (mpm) ---'; apache2ctl -M | grep -i mpm || true; \
echo '--- apache config test ---'; apache2ctl -t; \
echo '--- starting apache ---'; exec apache2-foreground \
"]
