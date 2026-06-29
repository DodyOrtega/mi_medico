<?php
define('WAHA_KEY', 'mimedico_waha_2024');

function wahaPost(string $url, array $body = []): void {
    $ch = curl_init($url);
    curl_setopt_array($ch, [
        CURLOPT_RETURNTRANSFER => true,
        CURLOPT_POST           => true,
        CURLOPT_POSTFIELDS     => json_encode($body),
        CURLOPT_HTTPHEADER     => ['X-Api-Key: ' . WAHA_KEY, 'Content-Type: application/json'],
        CURLOPT_TIMEOUT        => 10,
    ]);
    curl_exec($ch);
    curl_close($ch);
}

// Desconectar número actual y mostrar QR para el nuevo
if (isset($_GET['logout'])) {
    wahaPost('http://waha:3000/api/sessions/default/logout');
    wahaPost('http://waha:3000/api/sessions/default/start');
    header('Location: /waha_qr.php');
    exit;
}

// Obtiene el QR en tiempo real desde WAHA
$ch = curl_init('http://waha:3000/api/default/auth/qr?format=image');
curl_setopt_array($ch, [
    CURLOPT_RETURNTRANSFER => true,
    CURLOPT_HTTPHEADER     => ['X-Api-Key: mimedico_waha_2024'],
    CURLOPT_TIMEOUT        => 10,
]);
$png = curl_exec($ch);
$status = curl_getinfo($ch, CURLINFO_HTTP_CODE);
curl_close($ch);

// Si el QR no está disponible, verificar estado de sesión
if ($status !== 200 || str_starts_with($png, '{')) {
    $ch2 = curl_init('http://waha:3000/api/sessions/default');
    curl_setopt_array($ch2, [
        CURLOPT_RETURNTRANSFER => true,
        CURLOPT_HTTPHEADER     => ['X-Api-Key: mimedico_waha_2024'],
    ]);
    $info = json_decode(curl_exec($ch2), true);
    curl_close($ch2);
    $estado = $info['status'] ?? 'DESCONOCIDO';
    $me     = $info['me']['pushname'] ?? null;
?>
<!DOCTYPE html>
<html lang="es">
<head><meta charset="UTF-8"><title>WhatsApp – MiMedico</title>
<meta http-equiv="refresh" content="5">
<style>body{font-family:sans-serif;display:flex;flex-direction:column;align-items:center;justify-content:center;min-height:100vh;margin:0;background:#f0fdf4;}</style>
</head>
<body>
<?php if ($estado === 'WORKING'): ?>
    <h2 style="color:#15803d">✅ WhatsApp Conectado</h2>
    <p>Número: <strong><?= htmlspecialchars($me ?? 'vinculado') ?></strong></p>
    <p>Las notificaciones están activas. Puedes cerrar esta página.</p>
    <a href="/waha_qr.php?logout=1"
       style="margin-top:16px;padding:10px 22px;background:#fee2e2;color:#dc2626;border:1px solid #fca5a5;border-radius:8px;text-decoration:none;font-weight:600;">
       🔄 Cambiar número
    </a>
<?php else: ?>
    <h2 style="color:#b45309">⏳ Estado: <?= htmlspecialchars($estado) ?></h2>
    <p>Recargando...</p>
<?php endif; ?>
</body></html>
<?php
    exit;
}

$b64 = base64_encode($png);
?>
<!DOCTYPE html>
<html lang="es">
<head>
<meta charset="UTF-8">
<title>Escanea el QR – MiMedico WhatsApp</title>
<meta http-equiv="refresh" content="55">
<style>
body{font-family:sans-serif;display:flex;flex-direction:column;align-items:center;justify-content:center;min-height:100vh;margin:0;background:#f0fdf4;}
h2{color:#15803d;margin-bottom:4px;}
p{color:#555;max-width:380px;text-align:center;margin:4px 0;}
img{border:6px solid #15803d;border-radius:12px;margin:18px 0;display:block;}
.note{background:#fef9c3;border:1px solid #eab308;padding:10px 18px;border-radius:8px;font-size:13px;text-align:center;max-width:360px;}
</style>
</head>
<body>
<h2>📱 Conecta WhatsApp a MiMedico</h2>
<p>Abre WhatsApp en el teléfono de la clínica →<br>
<strong>Dispositivos vinculados → Vincular dispositivo</strong></p>
<img src="data:image/png;base64,<?= $b64 ?>" width="280" height="280" alt="QR WhatsApp">
<div class="note">
  ⏱ Esta página se recarga automáticamente cada 55 segundos.<br>
  Una vez escaneado correctamente, esta página mostrará "✅ Conectado".
</div>
</body>
</html>
