<?php
if ($_SERVER['REQUEST_METHOD'] == 'POST' && isset($_FILES['llave_privada']) && isset($_FILES['certificado'])) {
    
    // Archivos temporales subidos por el usuario
    $keyTmpPath = $_FILES['llave_privada']['tmp_name'];
    $crtTmpPath = $_FILES['certificado']['tmp_name'];
    $password = $_POST['password']; // Contraseña para proteger el p12
    
    // Generamos una ruta temporal segura en el servidor para el archivo de salida
    $p12Salida = tempnam(sys_get_temp_dir(), 'p12_') . '.p12';

    // Ejecutamos el comando exacto que pediste
    $comando = "openssl pkcs12 -export -out " . escapeshellarg($p12Salida) . 
               " -inkey " . escapeshellarg($keyTmpPath) . 
               " -in " . escapeshellarg($crtTmpPath) . 
               " -passout pass:" . escapeshellarg($password) . " 2>&1";
               
    $output = shell_exec($comando);

    // Si el archivo se creó correctamente y tiene peso (no está vacío)
    if (file_exists($p12Salida) && filesize($p12Salida) > 0) {
        
        $p12_binario = file_get_contents($p12Salida);
        
        // Forzamos la descarga del .p12
        header('Content-Type: application/x-pkcs12');
        header('Content-Disposition: attachment; filename="certificado_final.p12"');
        header('Content-Length: ' . strlen($p12_binario));
        
        echo $p12_binario;
        
        // ¡MUY IMPORTANTE! Borramos el archivo temporal para no dejar la llave privada en el servidor
        unlink($p12Salida);
        exit;
        
    } else {
        $error = "No se pudo empaquetar el certificado. Asegúrate de que los archivos corresponden entre sí. Error de OpenSSL: " . htmlspecialchars($output);
    }
}
?>
<!DOCTYPE html>
<html lang="es">
<head>
    <title>Empaquetador de Certificados (PKCS#12)</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
</head>
<body class="container mt-5">
    <h2>Empaquetador de Certificados para Thunderbird</h2>
    <p>Sube tu Llave Privada (.key) y tu Certificado Firmado (.crt) para unirlos en un archivo seguro <b>.p12</b>.</p>
    
    <?php if(isset($error)) echo "<div class='alert alert-danger'>$error</div>"; ?>

    <form action="auto.php" method="POST" enctype="multipart/form-data" class="card p-4 shadow-sm" style="max-width: 600px;">
        
        <div class="mb-3">
            <label class="form-label text-danger fw-bold">1. Tu Llave Privada (.key):</label>
            <input type="file" class="form-control" name="llave_privada" required>
        </div>
        
        <div class="mb-3">
            <label class="form-label text-success fw-bold">2. Tu Certificado Firmado por la CA (.crt):</label>
            <input type="file" class="form-control" name="certificado" required>
        </div>
        
        <div class="mb-3">
            <label class="form-label fw-bold">3. Contraseña de Exportación:</label>
            <input type="password" class="form-control" name="password" required>
            <small class="text-muted">Inventa una contraseña. La necesitarás para importar el archivo resultante a Thunderbird.</small>
        </div>
        
        <button type="submit" class="btn btn-primary w-100 mt-2">Empaquetar y Descargar .p12</button>
    </form>
</body>
</html>