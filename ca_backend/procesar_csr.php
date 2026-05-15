<?php
header('Content-Type: application/json');

// 1. Recibir los datos del frontend (POST)
$csr_pem = $_POST['csr'] ?? '';
$foto_base64 = $_POST['foto'] ?? '';
$email = $_POST['email'] ?? 'usuario_desconocido';

// Manejo del archivo subido (la credencial)
$credencial = $_FILES['id_document'] ?? null;

// Validación básica
if (empty($csr_pem)) {
    echo json_encode(['success' => false, 'error' => 'No se recibió el CSR.']);
    exit;
}

// 2. Cargar la Autoridad Certificadora (CA)
// Rutas a los archivos de tu CA (¡Deben estar fuera de la carpeta pública!)
$ca_cert_path = '/var/pki_segura/ca_raiz.crt';
$ca_key_path = '/var/pki_segura/ca_raiz.key';
$ca_key_pass = 'password_de_tu_ca'; // La contraseña que le pusiste a la llave de la CA

// Cargar el certificado de la CA
$cacert = file_get_contents($ca_cert_path);
// Cargar la llave privada de la CA
$privkey = array(file_get_contents($ca_key_path), $ca_key_pass);

// 3. Firmar el CSR para emitir el certificado
// Configuramos la validez (ej. 365 días) y el algoritmo de hash (SHA-256)
$usercert = openssl_csr_sign(
    $csr_pem, 
    $cacert, 
    $privkey, 
    365, 
    array('digest_alg' => 'sha256'), 
    time()
);

if (!$usercert) {
    echo json_encode(['success' => false, 'error' => 'Error al firmar: ' . openssl_error_string()]);
    exit;
}

// 4. Exportar el certificado firmado a formato PEM (texto)
openssl_x509_export($usercert, $certout);

// --- SECCIÓN DE EVIDENCIAS (Opcional pero recomendada) ---
// Aquí guardarías la foto base64 y el $credencial['tmp_name'] en una base de datos MySQL 
// o en una carpeta del servidor vinculada al $email para auditorías futuras.
// ---------------------------------------------------------

// 5. Devolver el certificado firmado al Frontend
echo json_encode([
    'success' => true,
    'mensaje' => 'Certificado emitido exitosamente.',
    'certificado_crt' => $certout
]);
?>

<script>

    // Función hipotética que se llama después de hacer el fetch() a procesar_csr.php
function empaquetarYDescargarP12(pemPrivateKey, pemCertificadoFirmado, passwordUsuario) {
    // 1. Leer las llaves en formato forge
    const privateKey = forge.pki.privateKeyFromPem(pemPrivateKey);
    const cert = forge.pki.certificateFromPem(pemCertificadoFirmado);

    // 2. Crear el contenedor PKCS#12 (el archivo .p12)
    const p12Asn1 = forge.pkcs12.toPkcs12Asn1(
        privateKey, [cert], passwordUsuario,
        { generateLocalKeyId: true, friendlyName: 'Mi Certificado Universitario' }
    );

    // 3. Convertir a binario
    const p12Der = forge.asn1.toDer(p12Asn1).getBytes();

    // 4. Forzar la descarga en el navegador
    const blob = new Blob([forge.util.encode64(p12Der)], {type: 'application/x-pkcs12'});
    const url = window.URL.createObjectURL(blob);
    const a = document.createElement('a');
    a.href = "data:application/x-pkcs12;base64," + forge.util.encode64(p12Der);
    a.download = "mi_certificado.p12";
    document.body.appendChild(a);
    a.click();
    document.body.removeChild(a);
}
</script>>