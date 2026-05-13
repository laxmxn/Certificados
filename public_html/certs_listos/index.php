<!DOCTYPE html>
<html lang="es">
<head>
    <title>Autoridad de Registro Universitaria</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
</head>
<body class="container mt-5">
    <h2>Solicitud de Firma de Certificado (CSR)</h2>
    <p>Sube tu archivo .csr generado desde tu computadora para que la CA lo firme.</p>
    
    <form action="upload.php" method="POST" enctype="multipart/form-data">
        <div class="mb-3">
            <label for="csr_file" class="form-label">Archivo .csr:</label>
            <input type="file" class="form-control" name="csr_file" id="csr_file" required>
        </div>
        <button type="submit" class="btn btn-primary">Enviar a la CA</button>
    </form>
</body>
</html>