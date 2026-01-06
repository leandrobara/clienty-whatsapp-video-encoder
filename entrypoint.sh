#!/bin/bash
set -e

echo "Fixing Apache MPM configuration..."

# Deshabilitar MPMs incompatibles
a2dismod mpm_event mpm_worker 2>/dev/null || true

# Borrar symlinks por si quedaron activos
rm -f /etc/apache2/mods-enabled/mpm_event.* \
      /etc/apache2/mods-enabled/mpm_worker.* || true

# Habilitar el único MPM válido para mod_php
a2enmod mpm_prefork

# Validar configuración antes de arrancar
apache2ctl -t

echo "Starting Apache..."
exec "$@"
