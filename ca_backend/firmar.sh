#!/bin/bash
# Este script recibe la ruta temporal del .csr y la ruta absoluta donde guardar el .crt

CSR_FILE=$1
OUTPUT_CRT=$2

# La CA firma el certificado usando las rutas proporcionadas
openssl x509 -req -in "$CSR_FILE" -CA ca.crt -CAkey ca.key -CAcreateserial -out "$OUTPUT_CRT" -days 365 -sha256 -passin pass:Luis28052005

echo "Firma intentada para: $OUTPUT_CRT"