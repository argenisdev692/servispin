<?php
/**
 * Script de Verificación de Seguridad
 *
 * INSTRUCCIONES:
 * 1. Sube este archivo a la raíz de tu proyecto en el servidor
 * 2. Accede desde el navegador: https://servispin.net/check_security.php
 * 3. Revisa los resultados
 * 4. ¡ELIMINA ESTE ARCHIVO después de usarlo!
 */
header('Content-Type: text/html; charset=utf-8');

?>
<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Verificación de Seguridad - SERVISPIN</title>
    <style>
        body {
            font-family: 'Segoe UI', Tahoma, Geneva, Verdana, sans-serif;
            max-width: 1200px;
            margin: 40px auto;
            padding: 20px;
            background: #f5f5f5;
        }
        .container {
            background: white;
            padding: 30px;
            border-radius: 10px;
            box-shadow: 0 2px 10px rgba(0,0,0,0.1);
        }
        h1 {
            color: #0ea5e9;
            border-bottom: 3px solid #0ea5e9;
            padding-bottom: 10px;
        }
        h2 {
            color: #1e40af;
            margin-top: 30px;
        }
        .check {
            padding: 15px;
            margin: 10px 0;
            border-radius: 5px;
            border-left: 4px solid;
        }
        .success {
            background: #d1fae5;
            border-color: #10b981;
            color: #065f46;
        }
        .warning {
            background: #fef3c7;
            border-color: #f59e0b;
            color: #92400e;
        }
        .danger {
            background: #fee2e2;
            border-color: #ef4444;
            color: #991b1b;
        }
        .info {
            background: #dbeafe;
            border-color: #3b82f6;
            color: #1e3a8a;
        }
        code {
            background: #f3f4f6;
            padding: 2px 6px;
            border-radius: 3px;
            font-family: 'Courier New', monospace;
        }
        .icon {
            font-weight: bold;
            margin-right: 10px;
        }
        .delete-warning {
            background: #fee2e2;
            border: 2px solid #ef4444;
            padding: 20px;
            border-radius: 10px;
            margin-top: 30px;
            text-align: center;
        }
        ul {
            margin: 10px 0;
            padding-left: 30px;
        }
        li {
            margin: 5px 0;
        }
    </style>
</head>
<body>
    <div class="container">
        <h1>🛡️ Verificación de Seguridad - SERVISPIN</h1>
        
        <div class="delete-warning">
            <strong>⚠️ IMPORTANTE: ELIMINA ESTE ARCHIVO DESPUÉS DE USARLO</strong><br>
            Este script revela información sensible sobre tu servidor.
        </div>

        <h2>📁 Archivos Sensibles</h2>
        <?php
        $sensitiveFiles = [
            '.env' => 'Archivo de configuración principal',
            '.env.production' => 'Archivo de configuración de producción',
            '.env.example' => 'Ejemplo de configuración',
            'composer.json' => 'Dependencias de PHP',
            'package.json' => 'Dependencias de Node',
            '.git/config' => 'Configuración de Git',
        ];

foreach ($sensitiveFiles as $file => $description) {
    $fullPath = __DIR__.'/'.$file;
    $exists = file_exists($fullPath);

    if ($exists) {
        $permissions = substr(sprintf('%o', fileperms($fullPath)), -3);
        $isReadable = is_readable($fullPath);

        if ($permissions == '600' || $permissions == '400') {
            echo "<div class='check success'><span class='icon'>✅</span><strong>{$file}</strong> - {$description}<br>Permisos: {$permissions} (Seguro)</div>";
        } else {
            echo "<div class='check danger'><span class='icon'>❌</span><strong>{$file}</strong> - {$description}<br>Permisos: {$permissions} (INSEGURO - Cambiar a 600)<br><code>chmod 600 {$file}</code></div>";
        }
    } else {
        echo "<div class='check info'><span class='icon'>ℹ️</span><strong>{$file}</strong> - No existe</div>";
    }
}
?>

        <h2>🌐 Accesibilidad Web</h2>
        <div class="check info">
            <strong>Prueba estos URLs desde tu navegador:</strong>
            <ul>
                <li><a href="https://servispin.net/.env" target="_blank">https://servispin.net/.env</a> - Debe dar 403 o 404</li>
                <li><a href="https://servispin.net/.env.production" target="_blank">https://servispin.net/.env.production</a> - Debe dar 403 o 404</li>
                <li><a href="https://servispin.net/composer.json" target="_blank">https://servispin.net/composer.json</a> - Debe dar 403 o 404</li>
                <li><a href="https://servispin.net/.git/config" target="_blank">https://servispin.net/.git/config</a> - Debe dar 403 o 404</li>
            </ul>
        </div>

        <h2>📂 Configuración de Directorios</h2>
        <?php
echo "<div class='check info'>";
echo "<span class='icon'>📍</span><strong>Document Root:</strong> ".$_SERVER['DOCUMENT_ROOT'].'<br>';
echo '<strong>Script Path:</strong> '.__DIR__.'<br>';
echo '<strong>Public Path:</strong> '.__DIR__.'/public';

if ($_SERVER['DOCUMENT_ROOT'] === __DIR__.'/public') {
    echo "<div class='check success' style='margin-top:10px'><span class='icon'>✅</span>Document Root está correctamente configurado apuntando a /public</div>";
} elseif ($_SERVER['DOCUMENT_ROOT'] === __DIR__) {
    echo "<div class='check danger' style='margin-top:10px'><span class='icon'>❌</span>PELIGRO: Document Root apunta a la raíz del proyecto. Debe apuntar a /public</div>";
} else {
    echo "<div class='check warning' style='margin-top:10px'><span class='icon'>⚠️</span>Verificar configuración del Document Root</div>";
}
echo '</div>';
?>

        <h2>🔒 Archivos .htaccess</h2>
        <?php
$htaccessFiles = [
    '.htaccess' => 'Raíz del proyecto',
    'public/.htaccess' => 'Directorio public',
];

foreach ($htaccessFiles as $file => $location) {
    $fullPath = __DIR__.'/'.$file;
    $exists = file_exists($fullPath);

    if ($exists) {
        $content = file_get_contents($fullPath);
        $hasEnvProtection = (strpos($content, '.env') !== false || strpos($content, 'Files .env') !== false);

        if ($hasEnvProtection) {
            echo "<div class='check success'><span class='icon'>✅</span><strong>{$file}</strong> ({$location})<br>Contiene protección para .env</div>";
        } else {
            echo "<div class='check warning'><span class='icon'>⚠️</span><strong>{$file}</strong> ({$location})<br>No contiene protección específica para .env</div>";
        }
    } else {
        echo "<div class='check danger'><span class='icon'>❌</span><strong>{$file}</strong> ({$location})<br>No existe - Crear archivo .htaccess</div>";
    }
}
?>

        <h2>⚙️ Configuración PHP</h2>
        <?php
echo "<div class='check info'>";
echo "<span class='icon'>🔧</span>";
echo '<strong>PHP Version:</strong> '.phpversion().'<br>';
echo '<strong>expose_php:</strong> '.(ini_get('expose_php') ? 'On (cambiar a Off)' : 'Off ✅').'<br>';
echo '<strong>display_errors:</strong> '.(ini_get('display_errors') ? 'On (cambiar a Off en producción)' : 'Off ✅').'<br>';
echo '<strong>allow_url_fopen:</strong> '.(ini_get('allow_url_fopen') ? 'On' : 'Off').'<br>';
echo '</div>';
?>

        <h2>✅ Recomendaciones Inmediatas</h2>
        <div class="check warning">
            <strong>Acciones prioritarias:</strong>
            <ol>
                <li><strong>Cambiar permisos de .env:</strong> <code>chmod 600 .env</code></li>
                <li><strong>Verificar Document Root:</strong> Debe apuntar a <code>/public</code></li>
                <li><strong>Probar URLs sensibles:</strong> Todos deben dar error 403/404</li>
                <li><strong>Eliminar archivos innecesarios:</strong> <code>.git/</code>, <code>tests/</code>, <code>README.md</code> en producción</li>
                <li><strong>ELIMINAR ESTE ARCHIVO:</strong> <code>rm check_security.php</code></li>
            </ol>
        </div>

        <h2>📞 Siguiente Paso</h2>
        <div class="check info">
            <p>Si encontraste problemas, contacta a tu proveedor de hosting con esta información:</p>
            <ul>
                <li>Solicita que el Document Root apunte a <code>/public_html/public</code> o <code>/tu-proyecto/public</code></li>
                <li>Pide que verifiquen que los archivos .htaccess estén activos</li>
                <li>Solicita cambiar permisos del archivo .env a 600</li>
            </ul>
        </div>

        <div class="delete-warning">
            <h3>🚨 ACCIÓN REQUERIDA 🚨</h3>
            <p><strong>ELIMINA ESTE ARCHIVO INMEDIATAMENTE DESPUÉS DE REVISAR LOS RESULTADOS</strong></p>
            <p>Desde SSH: <code>rm <?php echo __FILE__; ?></code></p>
            <p>O desde File Manager de cPanel</p>
        </div>
    </div>
</body>
</html>
