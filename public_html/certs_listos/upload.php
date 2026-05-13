<?php
if ($_SERVER['REQUEST_METHOD'] == 'POST' && isset($_FILES['csr_file'])) {
    $fileTmpPath = $_FILES['csr_file']['tmp_name']; // Ruta temporal generada por PHP (siempre tiene permisos)
    $fileName = $_FILES['csr_file']['name'];
    
    // Verificar que sea un archivo .csr
    $fileExtension = strtolower(pathinfo($fileName, PATHINFO_EXTENSION));
    if ($fileExtension != "csr") {
        die("Error: Solo se permiten archivos .csr");
    }

    // Ruta absoluta del backend
    $backendDir = "/home/alumnos/a00517630/public_html/Certificados/ca_backend";
    $scriptPath = $backendDir . "/firmar.sh";
    
    if (!file_exists($scriptPath)) {
        die("<p style='color:red;'>Error de ruta: PHP no encuentra el script.</p>");
    }

    // Nombre del archivo final .crt
    $crtName = str_replace(".csr", ".crt", $fileName);
    // Ruta donde queremos que OpenSSL guarde el certificado final
    $rutaFinalAbsoluta = "/home/alumnos/a00517630/public_html/Certificados/public_html/certs_listos/" . $crtName;

    // Ejecutar el script bash. Le pasamos el archivo temporal y la ruta donde queremos el .crt
    $comando = "cd " . escapeshellarg($backendDir) . " && bash ./firmar.sh " . escapeshellarg($fileTmpPath) . " " . escapeshellarg($rutaFinalAbsoluta) . " 2>&1";
    $output = shell_exec($comando);

    $rutaFinalRelativa = "certs_listos/" . $crtName;

    echo "<h3>Proceso de la CA</h3>";
    
    if (file_exists($rutaFinalAbsoluta)) {
        echo "<p style='color:green;'>Tu certificado ha sido firmado exitosamente.</p>";
        echo "<a href='" . $rutaFinalRelativa . "' class='btn btn-success' download>Descargar Certificado Firmado (.crt)</a>";
    } else {
        echo "<p style='color:red;'><strong>Error crítico:</strong> El certificado no se pudo generar.</p>";
        echo "<p>Revisa la salida de la consola del servidor para encontrar el problema:</p>";
        echo "<pre style='background:#1e1e1e; color:#00ff00; padding:15px; border-radius:5px; overflow-x:auto;'>" . htmlspecialchars($output) . "</pre>";
    }
}
?>