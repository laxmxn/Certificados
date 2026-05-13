#!/bin/bash
CSR_FILE=$1
TEMP_CRT="/tmp/cert_${RANDOM}.crt"
# Generamos un número de serie único basado en el reloj del servidor
SERIAL_NUM=$(date +%s) 

# Firmamos usando el serial dinámico. Asegúrate de poner tu nueva contraseña
openssl x509 -req -in "$CSR_FILE" -CA ca.crt -CAkey ca.key -set_serial $SERIAL_NUM -out "$TEMP_CRT" -days 365 -sha256 -passin pass:anahuac123

# Si el archivo se creó con éxito, lo imprimimos
if [ -f "$TEMP_CRT" ]; then
    cat "$TEMP_CRT"
    rm -f "$TEMP_CRT"
fi