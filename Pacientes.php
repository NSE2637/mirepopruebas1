<?php
require_once "conexion.php";

$resultado = $conexion->query("SELECT * FROM pacientes ORDER BY id DESC");

$versiones = [];
while ($fila_version = $resultado->fetch_assoc()) {
    $versiones[] = $fila_version["id"] . "|" . $fila_version["ubicacion"];
}
$version_pantalla = md5(implode(";", $versiones));

if (isset($_GET["actualizacion"])) {
    header("Content-Type: application/json; charset=utf-8");
    $cambios = [];
    $consulta_cambios = $conexion->query("SELECT id, nombre, ubicacion FROM pacientes ORDER BY id DESC");
    while ($fila_cambio = $consulta_cambios->fetch_assoc()) {
        $cambios[] = [
            "id" => (int)$fila_cambio["id"],
            "nombre" => $fila_cambio["nombre"],
            "ubicacion" => $fila_cambio["ubicacion"]
        ];
    }
    echo json_encode(["pacientes" => $cambios], JSON_UNESCAPED_UNICODE | JSON_UNESCAPED_SLASHES);
    exit;
}

$resultado = $conexion->query("SELECT * FROM pacientes ORDER BY id DESC");

$recepcion = [];
$preparacion = [];
$cirugia = [];
$recuperacion = [];

while ($paciente = $resultado->fetch_assoc()) {
    $ubicacionPaciente = trim((string)$paciente["ubicacion"]);
    if ($ubicacionPaciente === "Sala de recuperación 2") {
        $ubicacionPaciente = "Sala de recuperación";
    }
    $paciente["ubicacion"] = $ubicacionPaciente;

    switch ($ubicacionPaciente) {
        case "Recepción":
            $recepcion[] = $paciente;
            break;
        case "Sala de preparación":
            $preparacion[] = $paciente;
            break;
        case "Cirugía":
            $cirugia[] = $paciente;
            break;
        case "Sala de recuperación":
            $recuperacion[] = $paciente;
            break;
    }
}

function mostrarPacientes($lista) {
    if (empty($lista)) {
        echo '<p class="sin-pacientes">No hay pacientes</p>';
        return;
    }

    foreach ($lista as $paciente) {
        echo '<div class="paciente">';
        echo '<strong class="nombre-paciente">' . htmlspecialchars($paciente["nombre"]) . '</strong>';
        echo '</div>';
    }
}
?>
<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
        <title>Estado de pacientes</title>
    <link rel="stylesheet" href="css/style.css">
</head>
<body class="pantalla-publica">
<header>
    <div class="header-contenido">
        <img src="img/logo.png" alt="Logo" class="logo">
        <h1>Sistema de Gestión de Pacientes</h1>
    </div>
</header>

<main class="contenedor">
    <div class="encabezado publico">
        <div>
            <br>
            <h2>Lista de pacientes</h2>
            <p>Consulta dónde se encuentra cada paciente.</p>
        </div>
    </div>

    <div class="ubicaciones">
        <div class="columna">
            <h3>Recepción</h3>
            <div class="lista-pacientes">
                <?php mostrarPacientes($recepcion); ?>
            </div>
        </div>

        <div class="columna">
            <h3>Sala de preparación</h3>
            <div class="lista-pacientes">
                <?php mostrarPacientes($preparacion); ?>
            </div>
        </div>

        <div class="columna">
            <h3>Cirugía</h3>
            <div class="lista-pacientes">
                <?php mostrarPacientes($cirugia); ?>
            </div>
        </div>

        <div class="columna">
            <h3>Sala de recuperación</h3>
            <div class="lista-pacientes">
                <?php mostrarPacientes($recuperacion); ?>
            </div>
        </div>
    </div>

    <div class="control-sonido">
        <button type="button" id="activarSonido" class="boton-sonido">🔊 Activar sonido</button>
        <span id="estadoSonido">El sonido avisará cuando haya un cambio.</span>
    </div>
    <p class="actualizacion">La pantalla se actualiza automáticamente cada 5 segundos.</p>
</main>

<script>
let pacientesActuales = <?php
    $estado_js = [];
    $resultado_js = $conexion->query("SELECT id, nombre, ubicacion FROM pacientes ORDER BY id DESC");
    while ($p_js = $resultado_js->fetch_assoc()) {
        $estado_js[] = [
            "id" => (int)$p_js["id"],
            "nombre" => $p_js["nombre"],
            "ubicacion" => $p_js["ubicacion"]
        ];
    }
    echo json_encode($estado_js, JSON_UNESCAPED_UNICODE | JSON_UNESCAPED_SLASHES);
?>;

const botonSonido = document.getElementById("activarSonido");
const estadoSonido = document.getElementById("estadoSonido");
let audioContext = null;
let sonidoActivado = localStorage.getItem("sonidoPacientes") === "1";

function prepararSonido() {
    try {
        audioContext = audioContext || new (window.AudioContext || window.webkitAudioContext)();
        if (audioContext.state === "suspended") audioContext.resume();
        sonidoActivado = true;
        localStorage.setItem("sonidoPacientes", "1");
        botonSonido.textContent = "🔊 Sonido activado";
        estadoSonido.textContent = "Avisará y dirá el nombre cuando cambie de ubicación.";
    } catch (e) {
        estadoSonido.textContent = "El navegador no permite activar el sonido.";
    }
}

function sonarDosVeces() {
    if (!audioContext) return;
    const ahora = audioContext.currentTime;

    // Dos avisos sonoros antes de cada anuncio de voz.
    [0, 0.22].forEach((tiempo) => {
        const oscilador = audioContext.createOscillator();
        const ganancia = audioContext.createGain();
        oscilador.type = "sine";
        oscilador.frequency.value = 760;
        ganancia.gain.setValueAtTime(0.0001, ahora + tiempo);
        ganancia.gain.exponentialRampToValueAtTime(0.18, ahora + tiempo + 0.02);
        ganancia.gain.exponentialRampToValueAtTime(0.0001, ahora + tiempo + 0.16);
        oscilador.connect(ganancia);
        ganancia.connect(audioContext.destination);
        oscilador.start(ahora + tiempo);
        oscilador.stop(ahora + tiempo + 0.17);
    });
}

function hablarCambio(cambio) {
    return new Promise((resolver) => {
        const nombre = String(cambio.nombre || "").toUpperCase();
        const ubicacion = String(cambio.ubicacion || "");
        const texto = nombre + ", en " + ubicacion ;

        if (!("speechSynthesis" in window)) {
            setTimeout(resolver, 700);
            return;
        }

        // Voz en español latinoamericano. Se intenta primero una voz
        // disponible de Colombia/México/Latinoamérica y, si no existe,
        // se usa cualquier voz en español disponible en el navegador.
        const voces = window.speechSynthesis.getVoices();
        const vozLatina =
            voces.find(v => /^es-CO$/i.test(v.lang)) ||
            voces.find(v => /^es-MX$/i.test(v.lang)) ||
            voces.find(v => /^es-419$/i.test(v.lang)) ||
            voces.find(v => /^es-/.test(v.lang) && /lat|latin|américa|america|colombia|mexico/i.test(v.name + " " + v.lang)) ||
            voces.find(v => /^es-/i.test(v.lang)) ||
            voces.find(v => /^es$/i.test(v.lang));

        const hablarUnaVez = () => new Promise((fin) => {
            const mensaje = new SpeechSynthesisUtterance(texto);
            mensaje.lang = vozLatina ? vozLatina.lang : "es-419";
            mensaje.rate = 0.85;
            mensaje.pitch = 1;
            mensaje.volume = 1;
            if (vozLatina) mensaje.voice = vozLatina;
            mensaje.onend = () => setTimeout(fin, 250);
            mensaje.onerror = () => setTimeout(fin, 250);
            window.speechSynthesis.speak(mensaje);
        });

        // Repetir el anuncio exactamente dos veces.
        (async () => {
            await hablarUnaVez();
            await hablarUnaVez();
            resolver();
        })();
    });
}

async function reproducirCambiosEnOrden(cambios) {
    if (!sonidoActivado || cambios.length === 0) return;

    try {
        audioContext = audioContext || new (window.AudioContext || window.webkitAudioContext)();
        if (audioContext.state === "suspended") await audioContext.resume();

        // Se procesan uno por uno: si cambian 3 pacientes al mismo tiempo,
        // se anuncian consecutivamente y no se pisan las voces.
        for (const cambio of cambios) {
            sonarDosVeces();
            await new Promise(r => setTimeout(r, 600));
            await hablarCambio(cambio);
        }
    } catch (e) {
        console.error("Error de sonido/voz:", e);
    }
}

function detectarCambios(nuevosPacientes) {
    const cambiosUbicacion = [];

    nuevosPacientes.forEach(nuevo => {
        const anterior = pacientesActuales.find(p => Number(p.id) === Number(nuevo.id));
        if (anterior && anterior.ubicacion !== nuevo.ubicacion) {
            cambiosUbicacion.push({
                id: nuevo.id,
                nombre: nuevo.nombre,
                ubicacion: nuevo.ubicacion
            });
        }
    });

    const huboCambio = JSON.stringify(pacientesActuales) !== JSON.stringify(nuevosPacientes);
    pacientesActuales = nuevosPacientes;

    if (cambiosUbicacion.length > 0) {
        // Todos los cambios detectados en la misma actualización entran en una cola.
        reproducirCambiosEnOrden(cambiosUbicacion).then(() => {
            window.location.reload();
        });
    } else if (huboCambio) {
        setTimeout(() => window.location.reload(), 500);
    }
}


botonSonido.addEventListener("click", prepararSonido);

if (sonidoActivado) {
    botonSonido.textContent = "🔊 Sonido activado";
    estadoSonido.textContent = "Avisará y dirá el nombre cuando cambie de ubicación.";
}

setInterval(async () => {
    try {
        const respuesta = await fetch("Pacientes.php?actualizacion=1&_=" + Date.now(), { cache: "no-store" });
        const datos = await respuesta.json();
        detectarCambios(datos.pacientes);
    } catch (e) {}
}, 5000);
</script>
</body>
</html>
