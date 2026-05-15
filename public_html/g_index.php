<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Generación de Llave y CSR</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
    <script src="https://cdnjs.cloudflare.com/ajax/libs/forge/1.3.1/forge.min.js"></script>
</head>
<body class="bg-light">

<div class="container mt-5 mb-5">
    <div class="card shadow border-primary">
        <div class="card-header bg-primary text-white">
            <h3 class="mb-0">Paso 1: Verificación de Identidad y Generación de Llaves</h3>
        </div>
        <div class="card-body">
            <div class="alert alert-info">
                <strong>Importante:</strong> Este proceso generará tu Llave Privada y tu Solicitud de Certificado (CSR). Tu llave privada se genera localmente en tu dispositivo por seguridad.
            </div>

            <form id="generacionForm">
                <div class="row">
                    <div class="col-md-6 mb-3">
                        <label for="fullName" class="form-label fw-bold">Nombre Completo</label>
                        <input type="text" class="form-control" id="fullName" placeholder="Ej. Luis Fernando Gómez Ibarra" required>

                        <label for="email" class="form-label fw-bold mt-3">Correo Electrónico Institucional</label>
                        <input type="email" class="form-control" id="email" placeholder="usuario@anahuac.mx" required>
                        
                        <label for="id_document" class="form-label fw-bold mt-3">Sube tu Credencial Universitaria (PDF/Imagen)</label>
                        <input class="form-control" type="file" id="id_document" accept="image/*,.pdf" required>
                    </div>

                    <div class="col-md-6 mb-3 d-flex flex-column align-items-center">
                        <label class="form-label fw-bold">Verificación Facial con Cámara</label>
                        <video id="webcam" width="100%" height="240" autoplay class="border rounded bg-dark mb-2"></video>
                        <canvas id="canvas" width="320" height="240" class="d-none border rounded mb-2"></canvas>
                        
                        <button type="button" id="captureBtn" class="btn btn-warning w-100">Tomar Foto de Verificación</button>
                    </div>
                </div>

                <hr>
                
                <button type="button" class="btn btn-success w-100 fw-bold fs-5" id="generateBtn">
                    Validar Identidad y Generar Archivos (.key y .csr)
                </button>

                <div id="loadingDiv" class="text-center mt-3 d-none">
                    <div class="spinner-border text-primary" role="status"></div>
                    <p class="mt-2 text-primary fw-bold">Generando par de claves RSA de 2048 bits... Por favor, no cierres la ventana.</p>
                </div>
            </form>

            <div id="downloadArea" class="mt-4 d-none p-4 bg-light border rounded">
                <h4 class="text-success text-center mb-3">¡Archivos Generados con Éxito!</h4>
                
                <div class="alert alert-danger border-danger">
                    <h5 class="alert-heading fw-bold">ADVERTENCIA CRÍTICA DE SEGURIDAD</h5>
                    <p class="mb-0"><strong>NUNCA compartas tu Llave Privada (.key) con nadie</strong>, ni siquiera con los administradores del sistema, profesores o personal de soporte. Si alguien obtiene este archivo, podrá suplantar tu identidad digital para firmar y descifrar correos a tu nombre.</p>
                </div>

                <p class="text-center">Por favor, descarga ambos archivos y guarda tu Llave Privada en un lugar seguro.</p>
                
                <div class="d-flex justify-content-center gap-3">
                    <button id="btnDescargarKey" class="btn btn-danger fw-bold">1. Descargar Mi Llave Privada (.key)</button>
                    <button id="btnDescargarCsr" class="btn btn-primary fw-bold">2. Descargar Mi Solicitud (.csr)</button>
                </div>
            </div>

        </div>
    </div>
</div>

<script>
    // Variables para la cámara
    const video = document.getElementById('webcam');
    const canvas = document.getElementById('canvas');
    const captureBtn = document.getElementById('captureBtn');
    let fotoCapturada = false;

    // Variables de Criptografía
    let pemPrivateKey = null;
    let pemCsr = null;

    // Iniciar cámara web
    navigator.mediaDevices.getUserMedia({ video: true })
        .then(stream => { video.srcObject = stream; })
        .catch(err => { alert("Por favor, permite el acceso a la cámara para validar tu identidad."); });

    // Capturar foto
    captureBtn.addEventListener('click', () => {
        const context = canvas.getContext('2d');
        context.drawImage(video, 0, 0, canvas.width, canvas.height);
        
        video.classList.add('d-none');
        canvas.classList.remove('d-none');
        captureBtn.textContent = "Foto Capturada ✓";
        captureBtn.classList.replace('btn-warning', 'btn-success');
        fotoCapturada = true;
    });

    // Proceso de Generación
    document.getElementById('generateBtn').addEventListener('click', () => {
        const fullName = document.getElementById('fullName').value.trim();
        const email = document.getElementById('email').value.trim();
        const idDocument = document.getElementById('id_document').files.length;

        // Validación actualizada para requerir el nombre
        if (!fullName || !email || idDocument === 0 || !fotoCapturada) {
            alert("Completa todos los campos (Nombre y Correo), sube tu credencial y toma la foto de verificación.");
            return;
        }

        // Mostrar UI de carga y ocultar botón
        document.getElementById('generateBtn').classList.add('d-none');
        document.getElementById('loadingDiv').classList.remove('d-none');

        // setTimeout para permitir que el DOM se actualice y muestre el spinner
        setTimeout(() => {
            // Generar par de claves RSA
            forge.pki.rsa.generateKeyPair({bits: 2048, workers: -1}, function(err, keypair) {
                if (err) {
                    alert("Error al generar las llaves.");
                    document.getElementById('generateBtn').classList.remove('d-none');
                    document.getElementById('loadingDiv').classList.add('d-none');
                    return;
                }

                // Crear el CSR
                const csr = forge.pki.createCertificationRequest();
                csr.publicKey = keypair.publicKey;
                
                // Configurar los atributos del CSR (AQUÍ SE INYECTA EL NOMBRE)
                csr.setSubject([
                    { name: 'countryName', value: 'MX' },
                    { name: 'organizationName', value: 'Universidad Anahuac' },
                    { name: 'commonName', value: fullName }, // <-- El nombre de la persona va aquí
                    { name: 'emailAddress', value: email }   // <-- El correo va aquí
                ]);

                // Firmar el CSR con la nueva llave privada
                csr.sign(keypair.privateKey, forge.md.sha256.create());

                // Convertir a formato PEM (texto)
                pemPrivateKey = forge.pki.privateKeyToPem(keypair.privateKey);
                pemCsr = forge.pki.certificationRequestToPem(csr);

                // Ocultar spinner y mostrar zona de descarga
                document.getElementById('loadingDiv').classList.add('d-none');
                document.getElementById('downloadArea').classList.remove('d-none');
            });
        }, 500);
    });

    // Función auxiliar para descargar archivos
    function descargarArchivo(contenido, nombreArchivo) {
        const blob = new Blob([contenido], { type: 'text/plain' });
        const url = window.URL.createObjectURL(blob);
        const a = document.createElement('a');
        a.href = url;
        a.download = nombreArchivo;
        document.body.appendChild(a);
        a.click();
        document.body.removeChild(a);
        window.URL.revokeObjectURL(url);
    }

    // Eventos de descarga
    document.getElementById('btnDescargarKey').addEventListener('click', () => {
        // Limpiamos el nombre para usarlo en el nombre del archivo (cambia espacios por guiones bajos)
        const safeName = document.getElementById('fullName').value.trim().replace(/\s+/g, '_');
        descargarArchivo(pemPrivateKey, `${safeName}_privada.key`);
    });

    document.getElementById('btnDescargarCsr').addEventListener('click', () => {
        const safeName = document.getElementById('fullName').value.trim().replace(/\s+/g, '_');
        descargarArchivo(pemCsr, `${safeName}_solicitud.csr`);
    });
</script>
</body>
</html>