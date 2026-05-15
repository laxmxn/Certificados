<?php
// Tutorial.php

// Lógica segura para descargar la llave PÚBLICA de la CA (ca.crt)
if (isset($_GET['descargar_ca'])) {
    // Apuntamos al archivo .crt, NUNCA al .key
    $ca_path = __DIR__ . '/../ca_backend/ca.crt';
    
    if (file_exists($ca_path)) {
        header('Content-Type: application/x-x509-ca-cert');
        header('Content-Disposition: attachment; filename="CA_Universidad.crt"');
        header('Content-Length: ' . filesize($ca_path));
        readfile($ca_path);
        exit;
    } else {
        die("Error: No se encontró el certificado público de la Autoridad en el servidor.");
    }
}

$cert_file = $_GET['cert'] ?? '';
$cert_url = !empty($cert_file) ? "certs_listos/" . htmlspecialchars($cert_file) : "#";
?>
<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Configuración de Thunderbird</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
</head>
<body class="bg-light">

<div class="container mt-5 mb-5">
    
    <?php if(!empty($cert_file)): ?>
    <div class="alert alert-success shadow-sm" role="alert">
        <h4 class="alert-heading">¡Identidad Validada y Certificado Generado!</h4>
        <p>Tu par de claves y certificado han sido firmados por nuestra CA con éxito. Descarga tus archivos antes de continuar.</p>
        <hr>
        <div class="d-flex gap-3">
            <a href="?descargar_ca=1" class="btn btn-warning fw-bold text-dark">1. Descargar Llave Pública (CA Raíz)</a>
            <a href="<?= $cert_url ?>" download="mi_identidad.p12" class="btn btn-primary fw-bold">2. Descargar Mi Certificado (.p12)</a>
        </div>
    </div>
    <?php endif; ?>

    <div class="card shadow border-dark">
        <div class="card-header bg-dark text-white">
            <h3 class="mb-0">Paso Final: Configurar Mozilla Thunderbird</h3>
        </div>
        <div class="card-body">
            <div class="accordion" id="accordionThunderbird">
                
                <div class="accordion-item">
                    <h2 class="accordion-header">
                        <button class="accordion-button" type="button" data-bs-toggle="collapse" data-bs-target="#paso1">
                            <strong>Paso 1: Confiar en la Autoridad Certificadora</strong>
                        </button>
                    </h2>
                    <div id="paso1" class="accordion-collapse collapse show" data-bs-parent="#accordionThunderbird">
                        <div class="accordion-body">
                            <div class="mb-3">
                                <a href="?descargar_ca=1" class="btn btn-sm btn-outline-warning fw-bold text-dark">
                                    ⬇Descargar CA_Universidad.crt
                                </a>
                            </div>
                            <ol>
                                <li>En Thunderbird, ve a <strong>Ajustes > Privacidad y Seguridad</strong>.</li>
                                <li>Baja hasta <strong>Certificados</strong> y haz clic en <strong>Administrar certificados</strong>.</li>
                                <li>Ve a la pestaña <strong>Autoridades</strong> y haz clic en <strong>Importar</strong>.</li>
                                <li>Selecciona el archivo <code>CA_Universidad.crt</code> que acabas de descargar.</li>
                                <li class="text-danger fw-bold">¡CRÍTICO! Marca la casilla "Confiar en esta CA para identificar usuarios de correo electrónico".</li>
                            </ol>
                        </div>
                    </div>
                </div>

                <div class="accordion-item">
                    <h2 class="accordion-header">
                        <button class="accordion-button collapsed" type="button" data-bs-toggle="collapse" data-bs-target="#paso2">
                            <strong>Paso 2: Importar tu Identidad</strong>
                        </button>
                    </h2>
                    <div id="paso2" class="accordion-collapse collapse" data-bs-parent="#accordionThunderbird">
                        <div class="accordion-body">
                            <ol>
                                <li>En la misma ventana, ve a la pestaña <strong>Sus certificados</strong>.</li>
                                <li>Haz clic en <strong>Importar</strong> y selecciona tu archivo <code>.p12</code>.</li>
                                <li>Ingresa la contraseña que definiste en el paso anterior.</li>
                            </ol>
                        </div>
                    </div>
                </div>

                <div class="accordion-item">
                    <h2 class="accordion-header">
                        <button class="accordion-button collapsed" type="button" data-bs-toggle="collapse" data-bs-target="#paso3">
                            <strong>Paso 3: Activar Cifrado y Firma S/MIME</strong>
                        </button>
                    </h2>
                    <div id="paso3" class="accordion-collapse collapse" data-bs-parent="#accordionThunderbird">
                        <div class="accordion-body">
                            <ol>
                                <li>Ve a <strong>Configuración de la cuenta</strong> (clic derecho en tu correo en el panel izquierdo).</li>
                                <li>Selecciona <strong>Cifrado de extremo a extremo</strong>.</li>
                                <li>En la sección S/MIME, haz clic en <strong>Seleccionar</strong> y escoge tu certificado.</li>
                                <li>Acepta usarlo tanto para firma como para cifrado.</li>
                            </ol>
                        </div>
                    </div>
                </div>

            </div>
        </div>
    </div>
</div>

<script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>
</body>
</html>