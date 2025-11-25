#!/bin/bash

# Confirmar que el usuario esté seguro de querer limpiar el servidor
echo "Este script eliminará archivos temporales y limpiará el caché. ¿Estás seguro de que quieres continuar? (y/n)"
read confirmacion
if [ "$confirmacion" != "y" ]; then
    echo "Operación cancelada."
    exit 0
fi

# Limpiar el caché de APT (solo en sistemas basados en Debian/Ubuntu)
echo "Limpiando caché de APT..."
sudo apt-get clean
sudo apt-get autoclean
sudo apt-get autoremove --purge -y

# Limpiar archivos de log viejos
echo "Limpiando archivos de log viejos..."
sudo find /var/log -type f -name "*.log" -exec truncate -s 0 {} \;

# Eliminar archivos temporales
echo "Eliminando archivos temporales..."
sudo rm -rf /tmp/*
sudo rm -rf ~/.cache/*

# Limpiar archivos huérfanos en el sistema
echo "Eliminando archivos huérfanos..."
sudo apt-get autoremove -y

# Limpiar el directorio de los paquetes descargados
echo "Limpiando paquetes descargados..."
sudo apt-get clean

# Ver espacio libre después de la limpieza
echo "Espacio libre después de la limpieza:"
df -h

echo "Limpieza completada."
