<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>@yield('titulo', 'Panel del Paciente') – MiMedico</title>
    <link rel="icon" type="image/png" href="{{ asset('assets/images/logo_transparente_casa.png') }}">
    @vite(['resources/css/app.css', 'resources/js/app.js'])
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.5.0/css/all.min.css">
    <link rel="stylesheet" href="{{ asset('assets/css/paciente.css') }}">
    <script src="https://cdn.jsdelivr.net/npm/sweetalert2@11"></script>
    @stack('styles')
</head>
<body class="paciente-body">

    <header class="paciente-topbar">
        <div class="paciente-topbar__inner">
            <a href="{{ route('paciente.dashboard') }}" class="paciente-topbar__brand">
                <img src="{{ asset('assets/images/logo-normal.png') }}" alt="MiMedico">
                <span>@yield('seccion_titulo', 'Panel del Paciente')</span>
            </a>

            <nav class="paciente-topbar__actions">
                @hasSection('volver')
                    <a href="@yield('volver')" class="paciente-topbar__link">
                        <i class="fas fa-arrow-left"></i><span>Regresar</span>
                    </a>
                @endif

                {{-- NOTIFICACIONES --}}
                <div class="notif-wrap" id="notifWrap">
                    <button class="notif-btn" id="notifBtn" onclick="toggleNotif()">
                        <i class="fas fa-bell"></i>
                        <span class="notif-badge" id="notifBadge" style="display:none">0</span>
                    </button>
                    <div class="notif-dropdown" id="notifDropdown" style="display:none">
                        <div class="notif-dropdown__header">
                            <span><i class="fas fa-bell"></i> Notificaciones</span>
                        </div>
                        <div class="notif-lista" id="notifLista">
                            <div class="notif-empty">
                                <i class="fas fa-check-circle"></i>
                                <p>Sin notificaciones nuevas</p>
                            </div>
                        </div>
                    </div>
                </div>

                {{-- EMERGENCIA --}}
                <button class="paciente-topbar__emergency" onclick="abrirEmergencia()" title="Solicitar emergencia">
                    <i class="fas fa-ambulance"></i>
                </button>

                <span class="paciente-topbar__user">
                    <i class="fas fa-user"></i>
                    {{ session('usuario_nombre', 'Paciente') }}
                </span>

                <a href="{{ route('paciente.perfil') }}" class="paciente-topbar__link">
                    <i class="fas fa-user-edit"></i>
                    <span>Mi Perfil</span>
                </a>

                <form method="POST" action="{{ route('logout') }}" class="paciente-topbar__logout-form">
                    @csrf
                    <button type="submit" class="paciente-topbar__link paciente-topbar__link--danger">
                        <i class="fas fa-right-from-bracket"></i><span>Cerrar sesión</span>
                    </button>
                </form>
            </nav>
        </div>
    </header>

    <main class="paciente-main">
        @yield('contenido')
    </main>

    <footer class="paciente-footer">
        <p>&copy; {{ date('Y') }} Mi Médico. Todos los derechos reservados.</p>
        <p>Desarrollado por Dody Ortega, Jefferson Anchundia, Erick Plaza, Kevin Soledispa</p>
    </footer>

    {{-- MODAL EMERGENCIA --}}
    <div id="modalEmergencia" style="display:none;position:fixed;inset:0;background:rgba(0,0,0,.5);z-index:3000;align-items:center;justify-content:center;">
        <div style="background:#fff;border-radius:16px;width:100%;max-width:480px;overflow:hidden;box-shadow:0 16px 48px rgba(0,0,0,.2);">
            <div style="background:linear-gradient(135deg,#dc3545,#c82333);color:#fff;padding:16px 20px;display:flex;justify-content:space-between;align-items:center;">
                <h4 style="margin:0;font-size:1rem;"><i class="fas fa-ambulance"></i> Solicitar Emergencia</h4>
                <button onclick="cerrarEmergencia()" style="background:none;border:none;color:#fff;font-size:1.4rem;cursor:pointer;line-height:1;">&times;</button>
            </div>
            <div style="padding:20px;">
                <p style="color:#5a6268;font-size:.875rem;margin:0 0 14px;">Describe brevemente tu situación. El personal médico será notificado de inmediato.</p>
                <div>
                    <label style="display:block;font-size:.85rem;font-weight:600;color:#2c3e50;margin-bottom:6px;">Descripción <span style="color:#dc3545">*</span></label>
                    <textarea id="descripcionEmergencia" rows="3" maxlength="500"
                        style="width:100%;padding:9px 12px;border:1px solid #dee2e6;border-radius:8px;font-size:.875rem;resize:vertical;"
                        placeholder="Ej: Dolor en el pecho, dificultad para respirar..."></textarea>
                    <small style="color:#5a6268;"><span id="charCount">0</span>/500</small>
                </div>
            </div>
            <div style="display:flex;justify-content:flex-end;gap:10px;padding:14px 20px;border-top:1px solid #f0f0f0;">
                <button onclick="cerrarEmergencia()" style="padding:8px 18px;border-radius:8px;border:1px solid #dee2e6;background:#fff;color:#5a6268;cursor:pointer;">Cancelar</button>
                <button onclick="enviarEmergencia()" style="padding:8px 18px;border-radius:8px;border:none;background:#dc3545;color:#fff;cursor:pointer;font-weight:600;">
                    <i class="fas fa-ambulance"></i> Solicitar
                </button>
            </div>
        </div>
    </div>

    {{-- Panel de llamada entrante (visible en todas las páginas del paciente) --}}
    <div id="panelLlamada" style="display:none;position:fixed;inset:0;background:rgba(0,0,0,.75);z-index:9999;align-items:center;justify-content:center;">
        <div style="background:#1e293b;border-radius:20px;padding:40px 36px;text-align:center;max-width:360px;width:90%;box-shadow:0 20px 60px rgba(0,0,0,.5);">
            <div style="font-size:3.5rem;color:#38bdf8;animation:ring 1s infinite;">📞</div>
            <div style="font-size:1.3rem;font-weight:700;color:#f8fafc;margin:16px 0 6px;">Llamada entrante</div>
            <div id="lblCaller" style="color:#94a3b8;font-size:.95rem;margin-bottom:28px;">Tu médico te está llamando</div>
            <div style="display:flex;gap:16px;justify-content:center;">
                <button onclick="rechazarLlamada()" style="padding:12px 28px;border-radius:50px;background:#dc2626;border:none;color:#fff;font-weight:700;font-size:1rem;cursor:pointer;">
                    <i class="fas fa-phone-slash"></i> Rechazar
                </button>
                <button onclick="aceptarLlamada()" style="padding:12px 28px;border-radius:50px;background:#16a34a;border:none;color:#fff;font-weight:700;font-size:1rem;cursor:pointer;">
                    <i class="fas fa-phone"></i> Aceptar
                </button>
            </div>
        </div>
    </div>
    <style>
    @keyframes ring{0%,100%{transform:rotate(0);}20%{transform:rotate(-15deg);}40%{transform:rotate(15deg);}60%{transform:rotate(-10deg);}80%{transform:rotate(10deg);}}
    </style>

    <script>
    // ── Llamada entrante global ─────────────────────────────────
    let _roomEntrante = null;

    async function _sondearLlamada() {
        try {
            const resp = await fetch('/video/entrante').then(r => r.json());
            if (resp.room) {
                _roomEntrante = resp.room;
                document.getElementById('lblCaller').textContent = resp.caller + ' te está llamando';
                document.getElementById('panelLlamada').style.display = 'flex';
            } else {
                if (_roomEntrante) {
                    document.getElementById('panelLlamada').style.display = 'none';
                    _roomEntrante = null;
                }
            }
        } catch(_) {}
    }

    function aceptarLlamada() {
        if (_roomEntrante) window.location.href = '/video/sala/' + _roomEntrante;
    }

    function rechazarLlamada() {
        document.getElementById('panelLlamada').style.display = 'none';
        _roomEntrante = null;
    }

    document.addEventListener('DOMContentLoaded', function() {
        _sondearLlamada();
        setInterval(_sondearLlamada, 3000);
    });

    // ── Notificaciones ─────────────────────────────────────────
    function cargarNotificaciones() {
        const badge = document.getElementById('notifBadge');
        const lista = document.getElementById('notifLista');
        // Paciente no tiene endpoint de notif aún — mostrar vacío
        badge.style.display = 'none';
        lista.innerHTML = `<div class="notif-empty"><i class="fas fa-check-circle"></i><p>Sin notificaciones nuevas</p></div>`;
    }

    function toggleNotif() {
        const dd = document.getElementById('notifDropdown');
        dd.style.display = dd.style.display === 'none' ? 'block' : 'none';
        if (dd.style.display === 'block') cargarNotificaciones();
    }

    document.addEventListener('click', function(e) {
        const wrap = document.getElementById('notifWrap');
        if (wrap && !wrap.contains(e.target)) {
            document.getElementById('notifDropdown').style.display = 'none';
        }
    });

    document.addEventListener('DOMContentLoaded', cargarNotificaciones);

    // ── Emergencia ─────────────────────────────────────────────
    function abrirEmergencia() {
        const m = document.getElementById('modalEmergencia');
        m.style.display = 'flex';
    }
    function cerrarEmergencia() {
        document.getElementById('modalEmergencia').style.display = 'none';
        document.getElementById('descripcionEmergencia').value = '';
        document.getElementById('charCount').textContent = '0';
    }

    document.getElementById('descripcionEmergencia')?.addEventListener('input', function() {
        document.getElementById('charCount').textContent = this.value.length;
    });

    function enviarEmergencia() {
        const descripcion = document.getElementById('descripcionEmergencia').value.trim();

        if (descripcion.length < 3) {
            Swal.fire('Campo requerido', 'Describe brevemente tu situación (mínimo 3 caracteres).', 'warning');
            return;
        }

        fetch('{{ route("paciente.emergencia") }}', {
            method: 'POST',
            headers: {
                'Content-Type': 'application/json',
                'Accept': 'application/json',
                'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]')?.content || '{{ csrf_token() }}'
            },
            body: JSON.stringify({ descripcion })
        })
        .then(r => {
            if (!r.ok && r.status === 419) throw new Error('sesion_expirada');
            return r.json();
        })
        .then(data => {
            cerrarEmergencia();
            if (data.success) {
                Swal.fire('Emergencia Registrada', data.message, 'success');
            } else {
                const msg = data.message || (data.errors ? Object.values(data.errors).flat().join(' ') : 'No se pudo registrar.');
                Swal.fire('Error', msg, 'error');
            }
        })
        .catch(e => {
            const msg = e.message === 'sesion_expirada'
                ? 'Tu sesión expiró. Recarga la página e intenta de nuevo.'
                : 'No se pudo conectar con el servidor. Verifica tu conexión.';
            Swal.fire('Error', msg, 'error');
        });
    }
    </script>

    @stack('scripts')
</body>
</html>