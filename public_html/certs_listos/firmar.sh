#!/bin/bash
# Este script recibe la ruta de un archivo .csr y genera un .crt firmado

CSR_FILE=$1
BASENAME=$(basename "$CSR_FILE" .csr)
OUTPUT_CRT="../public_html/certs_listos/${BASENAME}.crt"

# La CA firma el certificado. (Nota: en un entorno real, no se pasa el pass de ca.key en texto plano)
openssl x509 -req -in "$CSR_FILE" -CA ca.crt -CAkey ca.key -CAcreateserial -out "$OUTPUT_CRT" -days 365 -sha256 -passin pass:TU_CONTRASEÑA_DE_CA

echo "Firma completada: $OUTPUT_CRT"