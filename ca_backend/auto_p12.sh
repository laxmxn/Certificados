#!/bin/bash
# Recibe los datos del usuario desde PHP
NOMBRE=$1
CORREO=$2
PASS_P12=$3

# Carpeta temporal aislada para este proceso
TEMP_DIR="/tmp/pki_$$"
mkdir -p "$TEMP_DIR"

# 1. Generar la llave privada del usuario
openssl genrsa -out "$TEMP_DIR/user.key" 2048 2>/dev/null

# 2. Generar el CSR con los datos pasados
openssl req -new -key "$TEMP_DIR/user.key" -out "$TEMP_DIR/user.csr" -subj "/C=MX/ST=CDMX/L=CDMX/O=Anahuac/OU=Ingenieria/CN=$NOMBRE/emailAddress=$CORREO" 2>/dev/null

# 3. Firmar el certificado con tu CA
SERIAL=$(date +%s)
openssl x509 -req -in "$TEMP_DIR/user.csr" -CA ca.crt -CAkey ca.key -set_serial $SERIAL -out "$TEMP_DIR/user.crt" -days 365 -sha256 -passin pass:TU_NUEVA_CONTRASEÑA 2>/dev/null

# 4. Empaquetar todo en un .p12 protegido con la contraseña que eligió el usuario
openssl pkcs12 -export -out "$TEMP_DIR/user.p12" -inkey "$TEMP_DIR/user.key" -in "$TEMP_DIR/user.crt" -certfile ca.crt -passout pass:"$PASS_P12" 2>/dev/null

# 5. Imprimir el archivo P12 codificado en base64 para que PHP lo descargue de forma segura
cat "$TEMP_DIR/user.p12" | base64

# 6. Borrar la carpeta temporal para que no queden rastros de la llave en el servidor
rm -rf "$TEMP_DIR"