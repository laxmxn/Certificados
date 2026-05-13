<?php
if ($_SERVER['REQUEST_METHOD'] == 'POST' && isset($_FILES['csr_file'])) {
    $fileTmpPath = $_FILES['csr_file']['tmp_name'];
    $fileName = $_FILES['csr_file']['name'];
    
    // Verificar extensión
    $fileExtension = strtolower(pathinfo($fileName, PATHINFO_EXTENSION));
    if ($fileExtension != "csr") {
        die("Error: Solo se permiten archivos .csr");
    }

    $backendDir = "/home/alumnos/a00517630/public_html/Certificados/ca_backend";
    
    // IMPORTANTE: Le agregamos 2>&1 para capturar TODOS los errores de la terminal
    $comando = "cd " . escapeshellarg($backendDir) . " && bash ./firmar.sh " . escapeshellarg($fileTmpPath) . " 2>&1";
    $output = shell_exec($comando);

    // Extraemos matemáticamente solo el bloque del certificado
    if (preg_match('/-----BEGIN CERTIFICATE-----.*-----END CERTIFICATE-----/s', $output, $matches)) {
        
        $clean_cert = $matches[0] . "\n";
        $crtName = str_replace(".csr", ".crt", $fileName);
        
        // Forzamos la descarga del certificado limpio
        header('Content-Description: File Transfer');
        header('Content-Type: application/x-x509-ca-cert');
        header('Content-Disposition: attachment; filename="' . $crtName . '"');
        header('Expires: 0');
        header('Cache-Control: must-revalidate');
        header('Pragma: public');
        header('Content-Length: ' . strlen($clean_cert));
        
        echo $clean_cert;
        exit;
    } else {
        // Si no se encuentra un certificado en la salida, mostramos el error real
        echo "<h3>Error de firma detallado</h3>";
        echo "<p>No se pudo generar el certificado. Este es el error exacto que arroja el servidor:</p>";
        echo "<pre style='background:#1e1e1e; color:#00ff00; padding:15px; border-radius:5px; overflow-x:auto;'>" . htmlspecialchars($output) . "</pre>";
    }
}
?>