<?php
require_once "conexion.php";

$resultado = $conexion->query("SELECT * FROM pacientes ORDER BY id DESC");

$versiones = [];
while ($fila_version = $resultado->fetch()) {
    $versiones[] = $fila_version["id"] . "|" . $fila_version["ubicacion"];
}
$version_pantalla = md5(implode(";", $versiones));

if (isset($_GET["actualizacion"])) {
    header("Content-Type: application/json; charset=utf-8");
    $cambios = [];
    $consulta_cambios = $conexion->query("SELECT id, nombre, ubicacion FROM pacientes ORDER BY id DESC");
    while ($fila_cambio = $consulta_cambios->fetch()) {
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

while ($paciente = $resultado->fetch()) {
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
            <div class="lista-pacientes" data-ubicacion="Recepción">
                <?php mostrarPacientes($recepcion); ?>
            </div>
        </div>

        <div class="columna">
            <h3>Sala de preparación</h3>
            <div class="lista-pacientes" data-ubicacion="Sala de preparación">
                <?php mostrarPacientes($preparacion); ?>
            </div>
        </div>

        <div class="columna">
            <h3>Cirugía</h3>
            <div class="lista-pacientes" data-ubicacion="Cirugía">
                <?php mostrarPacientes($cirugia); ?>
            </div>
        </div>

        <div class="columna">
            <h3>Sala de recuperación</h3>
            <div class="lista-pacientes" data-ubicacion="Sala de recuperación">
                <?php mostrarPacientes($recuperacion); ?>
            </div>
        </div>
    </div>

    <div class="control-sonido">
        <button type="button" id="activarSonido" class="boton-sonido">🔊 Activar sonido</button>
        <span id="estadoSonido">Toca el botón para que suene cuando aparezca un paciente.</span>
    </div>
    <p class="actualizacion">La pantalla se actualiza automáticamente cada 5 segundos.</p>
</main>

<script>
let pacientesActuales = <?php
    $estado_js = [];
    $resultado_js = $conexion->query("SELECT id, nombre, ubicacion FROM pacientes ORDER BY id DESC");
    while ($p_js = $resultado_js->fetch()) {
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
let sonidoActivado = false;
let reproduciendo = false;
const colaCambios = [];

function obtenerAudioContext() {
    const Ctx = window.AudioContext || window.webkitAudioContext;
    if (!Ctx) return null;
    audioContext = audioContext || new Ctx();
    return audioContext;
}

// Chrome/Edge/Safari solo permiten sonido y voz después de que el usuario
// toque la página. Este desbloqueo debe ocurrir dentro de un clic/tecla.
function desbloquearAudio() {
    try {
        const ctx = obtenerAudioContext();
        if (ctx && ctx.state === "suspended") ctx.resume();
        if (ctx) {
            const buffer = ctx.createBuffer(1, 1, 22050);
            const fuente = ctx.createBufferSource();
            fuente.buffer = buffer;
            fuente.connect(ctx.destination);
            fuente.start(0);
        }
        if ("speechSynthesis" in window) {
            const silencio = new SpeechSynthesisUtterance(" ");
            silencio.volume = 0;
            window.speechSynthesis.speak(silencio);
        }
        return true;
    } catch (e) {
        console.error("No se pudo desbloquear el audio:", e);
        return false;
    }
}

function activarSonido() {
    if (!desbloquearAudio()) {
        estadoSonido.textContent = "El navegador no permite activar el sonido.";
        return;
    }
    sonidoActivado = true;
    localStorage.setItem("sonidoPacientes", "1");
    botonSonido.textContent = "🔊 Sonido activado";
    botonSonido.classList.remove("pendiente");
    estadoSonido.textContent = "Sonará y dirá el nombre cada vez que aparezca un paciente en pantalla.";
    sonarDosVeces();
}

function sonarDosVeces() {
    const ctx = audioContext;
    if (!ctx || ctx.state !== "running") return;
    const ahora = ctx.currentTime;

    [0, 0.22].forEach((tiempo) => {
        const oscilador = ctx.createOscillator();
        const ganancia = ctx.createGain();
        oscilador.type = "sine";
        oscilador.frequency.value = 760;
        ganancia.gain.setValueAtTime(0.0001, ahora + tiempo);
        ganancia.gain.exponentialRampToValueAtTime(0.18, ahora + tiempo + 0.02);
        ganancia.gain.exponentialRampToValueAtTime(0.0001, ahora + tiempo + 0.16);
        oscilador.connect(ganancia);
        ganancia.connect(ctx.destination);
        oscilador.start(ahora + tiempo);
        oscilador.stop(ahora + tiempo + 0.17);
    });
}

let vocesDisponibles = [];
function cargarVoces() {
    if ("speechSynthesis" in window) vocesDisponibles = window.speechSynthesis.getVoices();
}
if ("speechSynthesis" in window) {
    cargarVoces();
    window.speechSynthesis.onvoiceschanged = cargarVoces;
}

function elegirVozLatina() {
    const voces = vocesDisponibles;
    return voces.find(v => /^es[-_]CO$/i.test(v.lang)) ||
        voces.find(v => /^es[-_]MX$/i.test(v.lang)) ||
        voces.find(v => /^es[-_]419$/i.test(v.lang)) ||
        voces.find(v => /^es[-_]US$/i.test(v.lang)) ||
        voces.find(v => /^es/i.test(v.lang) && /lat|latin|américa|america|colombia|mexico/i.test(v.name)) ||
        voces.find(v => /^es/i.test(v.lang));
}

function hablarUnaVez(texto) {
    return new Promise((fin) => {
        if (!("speechSynthesis" in window)) {
            setTimeout(fin, 700);
            return;
        }
        const voz = elegirVozLatina();
        const mensaje = new SpeechSynthesisUtterance(texto);
        mensaje.lang = voz ? voz.lang : "es-419";
        if (voz) mensaje.voice = voz;
        mensaje.rate = 0.85;
        mensaje.pitch = 1;
        mensaje.volume = 1;

        let terminado = false;
        const terminar = () => {
            if (terminado) return;
            terminado = true;
            setTimeout(fin, 250);
        };
        mensaje.onend = terminar;
        mensaje.onerror = terminar;
        // Algunos navegadores nunca disparan onend; se evita que la cola se quede trabada.
        setTimeout(terminar, 8000);

        window.speechSynthesis.cancel();
        window.speechSynthesis.resume();
        window.speechSynthesis.speak(mensaje);
    });
}

async function procesarCola() {
    if (reproduciendo) return;
    reproduciendo = true;
    try {
        while (colaCambios.length > 0) {
            const cambio = colaCambios.shift();
            const texto = String(cambio.nombre || "").toUpperCase() + ", en " + String(cambio.ubicacion || "");
            sonarDosVeces();
            await new Promise(r => setTimeout(r, 600));
            await hablarUnaVez(texto);
            await hablarUnaVez(texto);
        }
    } catch (e) {
        console.error("Error de sonido/voz:", e);
    } finally {
        reproduciendo = false;
    }
}

function normalizarUbicacion(ubicacion) {
    const limpia = String(ubicacion || "").trim();
    return limpia === "Sala de recuperación 2" ? "Sala de recuperación" : limpia;
}

// Se actualiza la pantalla sin recargar la página: recargar borra el permiso
// de audio que el navegador dio al tocar el botón y por eso dejaba de sonar.
function pintarPacientes(pacientes) {
    document.querySelectorAll(".lista-pacientes[data-ubicacion]").forEach((contenedor) => {
        const lista = pacientes.filter(p => normalizarUbicacion(p.ubicacion) === contenedor.dataset.ubicacion);
        contenedor.replaceChildren();
        if (lista.length === 0) {
            const vacio = document.createElement("p");
            vacio.className = "sin-pacientes";
            vacio.textContent = "No hay pacientes";
            contenedor.appendChild(vacio);
            return;
        }
        lista.forEach((p) => {
            const tarjeta = document.createElement("div");
            tarjeta.className = "paciente";
            const nombre = document.createElement("strong");
            nombre.className = "nombre-paciente";
            nombre.textContent = p.nombre;
            tarjeta.appendChild(nombre);
            contenedor.appendChild(tarjeta);
        });
    });
}

function detectarCambios(nuevosPacientes) {
    const cambiosUbicacion = [];
    nuevosPacientes.forEach(nuevo => {
        const anterior = pacientesActuales.find(p => Number(p.id) === Number(nuevo.id));
        const esNuevo = !anterior;
        const cambioUbicacion = anterior && normalizarUbicacion(anterior.ubicacion) !== normalizarUbicacion(nuevo.ubicacion);
        if (esNuevo || cambioUbicacion) {
            cambiosUbicacion.push({ id: nuevo.id, nombre: nuevo.nombre, ubicacion: normalizarUbicacion(nuevo.ubicacion) });
        }
    });

    const huboCambio = JSON.stringify(pacientesActuales) !== JSON.stringify(nuevosPacientes);
    pacientesActuales = nuevosPacientes;

    if (huboCambio) pintarPacientes(nuevosPacientes);
    if (cambiosUbicacion.length > 0 && sonidoActivado) {
        colaCambios.push(...cambiosUbicacion);
        procesarCola();
    }
}

botonSonido.addEventListener("click", activarSonido);

// Si ya se había activado antes, el navegador igual exige un toque tras abrir
// la página: el primer clic o tecla en cualquier parte reactiva el sonido.
if (localStorage.getItem("sonidoPacientes") === "1") {
    botonSonido.textContent = "🔊 Toca para reactivar el sonido";
    botonSonido.classList.add("pendiente");
    estadoSonido.textContent = "El navegador pide un toque en la pantalla para permitir el sonido.";
    const reactivar = (evento) => {
        if (evento.target === botonSonido) return;
        activarSonido();
    };
    document.addEventListener("pointerdown", reactivar, { once: true });
    document.addEventListener("keydown", reactivar, { once: true });
}

setInterval(async () => {
    try {
        const respuesta = await fetch("pacientes.php?actualizacion=1&_=" + Date.now(), { cache: "no-store" });
        if (!respuesta.ok) return;
        const datos = await respuesta.json();
        detectarCambios(datos.pacientes);
    } catch (e) {
        console.error("Error al actualizar pacientes:", e);
    }
}, 5000);
</script>
</body>
</html>
