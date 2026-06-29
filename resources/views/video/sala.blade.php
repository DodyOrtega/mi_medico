<!DOCTYPE html>
<html lang="es">
<head>
<meta charset="UTF-8">
<meta name="viewport" content="width=device-width, initial-scale=1.0">
<meta name="csrf-token" content="{{ csrf_token() }}">
<title>Videollamada – MiMedico</title>
<link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.5.0/css/all.min.css">
<style>
*{box-sizing:border-box;margin:0;padding:0;}
body{background:#0f172a;color:#f8fafc;font-family:'Segoe UI',sans-serif;height:100vh;overflow:hidden;display:flex;flex-direction:column;}

/* ── Topbar ── */
.topbar{display:flex;align-items:center;justify-content:space-between;padding:10px 18px;background:rgba(0,0,0,.45);backdrop-filter:blur(8px);z-index:20;flex-shrink:0;}
.topbar__brand{display:flex;align-items:center;gap:10px;font-weight:700;font-size:1rem;color:#38bdf8;}
.topbar__brand i{font-size:1.2rem;}
.topbar__otro{font-size:.9rem;color:#94a3b8;}
.topbar__timer{font-variant-numeric:tabular-nums;color:#38bdf8;font-weight:600;font-size:.95rem;}

/* ── Video área ── */
.video-area{flex:1;position:relative;display:flex;overflow:hidden;}
#videoRemoto{width:100%;height:100%;object-fit:cover;background:#1e293b;}
.video-local-wrap{position:absolute;bottom:80px;right:18px;width:180px;height:130px;border-radius:12px;overflow:hidden;box-shadow:0 4px 20px rgba(0,0,0,.5);border:2px solid rgba(255,255,255,.15);cursor:move;z-index:15;}
#videoLocal{width:100%;height:100%;object-fit:cover;background:#0f172a;transform:scaleX(-1);}
.sin-video{position:absolute;inset:0;display:flex;flex-direction:column;align-items:center;justify-content:center;gap:10px;color:rgba(255,255,255,.35);font-size:.85rem;}
.sin-video i{font-size:2.5rem;}
.avatar-remoto{position:absolute;inset:0;display:flex;flex-direction:column;align-items:center;justify-content:center;gap:14px;display:none;}
.avatar-remoto__circulo{width:100px;height:100px;border-radius:50%;background:#1e40af;display:flex;align-items:center;justify-content:center;font-size:2.5rem;font-weight:700;color:#fff;}

/* ── Controles ── */
.controles{display:flex;align-items:center;justify-content:center;gap:14px;padding:14px 20px;background:rgba(0,0,0,.55);backdrop-filter:blur(8px);flex-shrink:0;position:relative;z-index:20;}
.ctrl-btn{width:50px;height:50px;border-radius:50%;border:none;cursor:pointer;display:flex;align-items:center;justify-content:center;font-size:1.05rem;transition:transform .15s,background .15s;background:#334155;color:#f8fafc;}
.ctrl-btn:hover{transform:scale(1.1);}
.ctrl-btn.activo{background:#1e40af;}
.ctrl-btn.apagado{background:#475569;}
.ctrl-btn--fin{background:#dc2626;width:56px;height:56px;font-size:1.2rem;}
.ctrl-btn--fin:hover{background:#b91c1c;}
.ctrl-label{font-size:.6rem;color:#94a3b8;text-align:center;margin-top:2px;}
.ctrl-wrap{display:flex;flex-direction:column;align-items:center;}

/* ── Chat panel ── */
.chat-panel{position:absolute;right:0;top:0;bottom:0;width:320px;background:#1e293b;display:flex;flex-direction:column;transform:translateX(100%);transition:transform .25s ease;z-index:25;border-left:1px solid rgba(255,255,255,.08);}
.chat-panel.abierto{transform:translateX(0);}
.chat-header{padding:14px 16px;background:#0f172a;border-bottom:1px solid rgba(255,255,255,.08);display:flex;align-items:center;justify-content:space-between;font-weight:600;font-size:.9rem;}
.chat-mensajes{flex:1;overflow-y:auto;padding:12px;display:flex;flex-direction:column;gap:10px;}
.chat-msg{max-width:85%;padding:8px 12px;border-radius:12px;font-size:.83rem;line-height:1.4;}
.chat-msg.propio{align-self:flex-end;background:#1e40af;border-bottom-right-radius:3px;}
.chat-msg.otro{align-self:flex-start;background:#334155;border-bottom-left-radius:3px;}
.chat-msg__nombre{font-size:.7rem;color:#94a3b8;margin-bottom:3px;}
.chat-msg__hora{font-size:.65rem;opacity:.6;text-align:right;margin-top:3px;}
.chat-input{display:flex;gap:8px;padding:10px 12px;border-top:1px solid rgba(255,255,255,.08);}
.chat-input input{flex:1;background:#0f172a;border:1px solid rgba(255,255,255,.12);border-radius:8px;padding:8px 12px;color:#f8fafc;font-size:.85rem;outline:none;}
.chat-input input:focus{border-color:#38bdf8;}
.chat-input button{width:36px;height:36px;border-radius:8px;background:#1e40af;border:none;color:#fff;cursor:pointer;display:flex;align-items:center;justify-content:center;}
.chat-input button:hover{background:#1d4ed8;}

/* ── Estado / overlay ── */
.overlay{position:absolute;inset:0;background:rgba(0,0,0,.7);display:flex;flex-direction:column;align-items:center;justify-content:center;gap:16px;z-index:30;}
.overlay__icon{font-size:3rem;animation:pulse 1.5s infinite;}
.overlay__texto{font-size:1.1rem;color:#94a3b8;}
.overlay__titulo{font-size:1.3rem;font-weight:700;}
@keyframes pulse{0%,100%{opacity:1;}50%{opacity:.4;}}

/* ── Notif estado ── */
.notif-estado{position:absolute;top:70px;left:50%;transform:translateX(-50%);background:#1e3a5f;border:1px solid #38bdf8;border-radius:8px;padding:8px 18px;font-size:.82rem;z-index:40;transition:opacity .3s;pointer-events:none;}
</style>
</head>
<body>

{{-- Topbar --}}
<div class="topbar">
    <div class="topbar__brand"><i class="fas fa-video"></i> MiMedico — Videollamada</div>
    <div class="topbar__otro" id="lblOtro">{{ $otroNombre }}</div>
    <div class="topbar__timer" id="timer">00:00</div>
</div>

{{-- Video área --}}
<div class="video-area" id="videoArea">
    {{-- Video remoto --}}
    <video id="videoRemoto" autoplay playsinline></video>

    {{-- Overlay: esperando --}}
    <div class="overlay" id="overlayEspera">
        @if($esCaller)
            <i class="fas fa-phone overlay__icon" style="color:#38bdf8;"></i>
            <div class="overlay__titulo">Llamando a {{ $otroNombre }}...</div>
            <div class="overlay__texto">Esperando que acepte la llamada</div>
        @else
            <i class="fas fa-spinner fa-spin overlay__icon" style="color:#38bdf8;"></i>
            <div class="overlay__titulo">Conectando...</div>
            <div class="overlay__texto">Estableciendo conexión segura</div>
        @endif
    </div>

    {{-- Video local --}}
    <div class="video-local-wrap" id="localWrap">
        <video id="videoLocal" autoplay playsinline muted></video>
    </div>

    {{-- Chat panel --}}
    <div class="chat-panel" id="chatPanel">
        <div class="chat-header">
            <span><i class="fas fa-comments"></i> Chat</span>
            <span style="cursor:pointer;color:#94a3b8;" onclick="toggleChat()"><i class="fas fa-times"></i></span>
        </div>
        <div class="chat-mensajes" id="chatMensajes"></div>
        <div class="chat-input">
            <input type="text" id="chatInput" placeholder="Escribe un mensaje..." maxlength="400"
                   onkeydown="if(event.key==='Enter') enviarMensaje()">
            <button onclick="enviarMensaje()"><i class="fas fa-paper-plane"></i></button>
        </div>
    </div>

    {{-- Notificación de estado --}}
    <div class="notif-estado" id="notifEstado" style="opacity:0;"></div>
</div>

{{-- Controles --}}
<div class="controles">
    <div class="ctrl-wrap">
        <button class="ctrl-btn activo" id="btnMic" onclick="toggleMic()"><i class="fas fa-microphone"></i></button>
        <div class="ctrl-label">Micrófono</div>
    </div>
    <div class="ctrl-wrap">
        <button class="ctrl-btn activo" id="btnCam" onclick="toggleCam()"><i class="fas fa-video"></i></button>
        <div class="ctrl-label">Cámara</div>
    </div>
    @if($rolSistema !== 'paciente')
    <div class="ctrl-wrap">
        <button class="ctrl-btn" id="btnPantalla" onclick="compartirPantalla()"><i class="fas fa-desktop"></i></button>
        <div class="ctrl-label">Pantalla</div>
    </div>
    @endif
    <div class="ctrl-wrap">
        <button class="ctrl-btn" id="btnChat" onclick="toggleChat()"><i class="fas fa-comments"></i></button>
        <div class="ctrl-label">Chat</div>
    </div>
    <div class="ctrl-wrap">
        <button class="ctrl-btn ctrl-btn--fin" onclick="terminarLlamada()"><i class="fas fa-phone-slash"></i></button>
        <div class="ctrl-label">Terminar</div>
    </div>
</div>

<script>
const ROOM     = '{{ $roomId }}';
const ES_CALLER = {{ $esCaller ? 'true' : 'false' }};
const ROL      = ES_CALLER ? 'caller' : 'callee';
const MI_NOMBRE = '{{ addslashes($miNombre) }}';
const MI_CEDULA = '{{ $miCedula }}';
const CSRF     = document.querySelector('meta[name="csrf-token"]').content;

const ICE_CFG = {
    iceServers: [
        { urls: 'stun:stun.l.google.com:19302' },
        { urls: 'stun:stun1.l.google.com:19302' },
        @if(config('video.turn_url'))
        {
            urls:       "{{ config('video.turn_url') }}",
            username:   "{{ config('video.turn_username') }}",
            credential: "{{ config('video.turn_credential') }}",
        },
        @endif
    ]
};

let pc, localStream, screenStream;
let micActivo = true, camActivo = true, pantallaActiva = false;
let chatAbierto = false;
let iceOffset = 0, chatOffset = 0;
let pollTimer, timerInterval, segundos = 0;

// ── Arrastrar video local ──────────────────────────────────────────
(function() {
    const wrap = document.getElementById('localWrap');
    let drag = false, ox, oy;
    wrap.addEventListener('mousedown', e => { drag=true; ox=e.clientX-wrap.offsetLeft; oy=e.clientY-wrap.offsetTop; });
    document.addEventListener('mousemove', e => { if(drag){ wrap.style.left=e.clientX-ox+'px'; wrap.style.right='auto'; wrap.style.top=e.clientY-oy+'px'; wrap.style.bottom='auto'; }});
    document.addEventListener('mouseup', ()=>{ drag=false; });
})();

// ── Iniciar ───────────────────────────────────────────────────────
async function iniciar() {
    try {
        localStream = await navigator.mediaDevices.getUserMedia({ video: true, audio: true });
        document.getElementById('videoLocal').srcObject = localStream;
    } catch(e) {
        mostrarNotif('⚠️ Sin acceso a cámara/micrófono', 4000);
        localStream = new MediaStream();
    }

    pc = new RTCPeerConnection(ICE_CFG);

    localStream.getTracks().forEach(t => pc.addTrack(t, localStream));

    pc.ontrack = e => {
        const remoto = document.getElementById('videoRemoto');
        remoto.srcObject = e.streams[0];
        document.getElementById('overlayEspera').style.display = 'none';
        iniciarTimer();
        mostrarNotif('✅ Conectado', 2000);
    };

    pc.onicecandidate = e => {
        if (e.candidate) enviarCandidate(e.candidate);
    };

    pc.onconnectionstatechange = () => {
        if (['disconnected','failed','closed'].includes(pc.connectionState)) {
            mostrarNotif('⚠️ Conexión perdida', 3000);
        }
    };

    if (ES_CALLER) {
        const offer = await pc.createOffer();
        await pc.setLocalDescription(offer);
        await apiPost('/video/signal', { room: ROOM, tipo: 'offer', sdp: pc.localDescription });
    }

    pollTimer = setInterval(poll, 1000);
}

// ── Polling de señalización ───────────────────────────────────────
async function poll() {
    try {
        const resp = await fetch(`/video/poll?room=${ROOM}&rol=${ROL}&ice_offset=${iceOffset}&chat_offset=${chatOffset}`);
        const data = await resp.json();

        if (data.status === 'ended') {
            clearInterval(pollTimer);
            mostrarFin();
            return;
        }

        // Callee recibe offer
        if (!ES_CALLER && data.offer && !pc.currentRemoteDescription) {
            await pc.setRemoteDescription(new RTCSessionDescription(data.offer));
            const answer = await pc.createAnswer();
            await pc.setLocalDescription(answer);
            await apiPost('/video/signal', { room: ROOM, tipo: 'answer', sdp: pc.localDescription });
            document.getElementById('overlayEspera').style.display = 'none';
        }

        // Caller recibe answer
        if (ES_CALLER && data.answer && !pc.currentRemoteDescription) {
            await pc.setRemoteDescription(new RTCSessionDescription(data.answer));
        }

        // ICE candidates
        for (const c of (data.candidates || [])) {
            try { await pc.addIceCandidate(new RTCIceCandidate(c)); } catch(_){}
        }
        iceOffset = data.ice_total;

        // Chat nuevos mensajes
        for (const msg of (data.chat || [])) {
            renderMensaje(msg);
        }
        if ((data.chat || []).length > 0) {
            chatOffset = data.chat_total;
            if (!chatAbierto) {
                document.getElementById('btnChat').classList.add('activo');
            }
        }

    } catch(_) {}
}

async function enviarCandidate(candidate) {
    await apiPost('/video/candidate', { room: ROOM, rol: ROL, candidate });
}

// ── Controles ─────────────────────────────────────────────────────
function toggleMic() {
    micActivo = !micActivo;
    localStream.getAudioTracks().forEach(t => t.enabled = micActivo);
    const btn = document.getElementById('btnMic');
    btn.innerHTML = micActivo ? '<i class="fas fa-microphone"></i>' : '<i class="fas fa-microphone-slash"></i>';
    btn.classList.toggle('activo', micActivo);
    btn.classList.toggle('apagado', !micActivo);
}

function toggleCam() {
    camActivo = !camActivo;
    localStream.getVideoTracks().forEach(t => t.enabled = camActivo);
    const btn = document.getElementById('btnCam');
    btn.innerHTML = camActivo ? '<i class="fas fa-video"></i>' : '<i class="fas fa-video-slash"></i>';
    btn.classList.toggle('activo', camActivo);
    btn.classList.toggle('apagado', !camActivo);
}

async function compartirPantalla() {
    const btn = document.getElementById('btnPantalla');
    if (pantallaActiva) {
        // Volver a cámara
        screenStream.getTracks().forEach(t => t.stop());
        pantallaActiva = false;
        btn.classList.remove('activo');
        btn.innerHTML = '<i class="fas fa-desktop"></i>';

        const camTrack = localStream.getVideoTracks()[0];
        if (camTrack) {
            const sender = pc.getSenders().find(s => s.track && s.track.kind === 'video');
            if (sender) sender.replaceTrack(camTrack);
            document.getElementById('videoLocal').srcObject = localStream;
        }
        mostrarNotif('Cámara restaurada', 2000);
    } else {
        try {
            screenStream = await navigator.mediaDevices.getDisplayMedia({ video: true, audio: true });
            pantallaActiva = true;
            btn.classList.add('activo');
            btn.innerHTML = '<i class="fas fa-stop-circle"></i>';

            const screenTrack = screenStream.getVideoTracks()[0];
            const sender = pc.getSenders().find(s => s.track && s.track.kind === 'video');
            if (sender) sender.replaceTrack(screenTrack);
            document.getElementById('videoLocal').srcObject = screenStream;

            screenTrack.onended = () => compartirPantalla(); // usuario cierra compartir
            mostrarNotif('Compartiendo pantalla', 2000);
        } catch(e) {
            mostrarNotif('No se pudo compartir pantalla', 3000);
        }
    }
}

function toggleChat() {
    chatAbierto = !chatAbierto;
    document.getElementById('chatPanel').classList.toggle('abierto', chatAbierto);
    document.getElementById('btnChat').classList.toggle('activo', chatAbierto);
    document.getElementById('btnChat').innerHTML = chatAbierto
        ? '<i class="fas fa-comments" style="color:#38bdf8"></i>'
        : '<i class="fas fa-comments"></i>';
    if (chatAbierto) {
        document.getElementById('chatInput').focus();
        document.getElementById('chatMensajes').scrollTop = 9999;
    }
}

async function enviarMensaje() {
    const input = document.getElementById('chatInput');
    const texto = input.value.trim();
    if (!texto) return;
    input.value = '';

    await apiPost('/video/mensaje', { room: ROOM, texto });
}

function renderMensaje(msg) {
    const esPropio = msg.cedula === MI_CEDULA;
    const div = document.createElement('div');
    div.className = 'chat-msg ' + (esPropio ? 'propio' : 'otro');
    div.innerHTML = (!esPropio ? `<div class="chat-msg__nombre">${msg.nombre}</div>` : '') +
        `<div>${msg.texto}</div><div class="chat-msg__hora">${msg.hora}</div>`;
    document.getElementById('chatMensajes').appendChild(div);
    document.getElementById('chatMensajes').scrollTop = 9999;
}

async function terminarLlamada() {
    clearInterval(pollTimer);
    clearInterval(timerInterval);
    if (pc) pc.close();
    if (localStream) localStream.getTracks().forEach(t => t.stop());
    if (screenStream) screenStream.getTracks().forEach(t => t.stop());
    await apiPost('/video/terminar', { room: ROOM });
    window.location.href = history.length > 1 ? 'javascript:history.back()' : '/';
}

function mostrarFin() {
    clearInterval(timerInterval);
    if (pc) pc.close();
    if (localStream) localStream.getTracks().forEach(t => t.stop());
    const overlay = document.getElementById('overlayEspera');
    overlay.innerHTML = '<i class="fas fa-phone-slash" style="color:#dc2626;font-size:3rem;"></i>' +
        '<div style="font-size:1.2rem;font-weight:700;">Llamada finalizada</div>' +
        '<div style="color:#94a3b8;">Redirigiendo...</div>';
    overlay.style.display = 'flex';
    setTimeout(() => { history.back(); }, 2500);
}

function iniciarTimer() {
    timerInterval = setInterval(() => {
        segundos++;
        const m = String(Math.floor(segundos/60)).padStart(2,'0');
        const s = String(segundos%60).padStart(2,'0');
        document.getElementById('timer').textContent = `${m}:${s}`;
    }, 1000);
}

function mostrarNotif(texto, ms=2500) {
    const el = document.getElementById('notifEstado');
    el.textContent = texto;
    el.style.opacity = '1';
    setTimeout(() => { el.style.opacity = '0'; }, ms);
}

async function apiPost(url, body) {
    return fetch(url, {
        method: 'POST',
        headers: { 'Content-Type': 'application/json', 'X-CSRF-TOKEN': CSRF },
        body: JSON.stringify(body),
    }).then(r => r.json()).catch(()=>{});
}

// ── Arrancar ──────────────────────────────────────────────────────
window.addEventListener('load', iniciar);
window.addEventListener('beforeunload', () => {
    if (pc) pc.close();
    if (localStream) localStream.getTracks().forEach(t => t.stop());
});
</script>
</body>
</html>
