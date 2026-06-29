<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>@yield('titulo', 'Panel del Médico') – MiMedico</title>
    <link rel="icon" type="image/png" href="{{ asset('assets/images/logo_transparente_casa.png') }}">
    @vite(['resources/css/app.css', 'resources/js/app.js'])
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.5.0/css/all.min.css">
    <link rel="stylesheet" href="{{ asset('assets/css/medico.css') }}">
    <script src="https://cdn.jsdelivr.net/npm/sweetalert2@11"></script>
    @stack('styles')
</head>
<body class="medico-body">

    <header class="medico-topbar">
        <div class="medico-topbar__inner">
            <a href="{{ route('medico.dashboard') }}" class="medico-topbar__brand">
                <img src="{{ asset('assets/images/logo-normal.png') }}" alt="MiMedico">
                <span>@yield('seccion_titulo', 'Panel del Médico')</span>
            </a>

            <nav class="medico-topbar__actions">
                @hasSection('volver')
                    <a href="@yield('volver')" class="medico-topbar__link">
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
                            <button onclick="marcarTodas()" class="notif-leer-todas">Marcar todas leídas</button>
                        </div>
                        <div class="notif-lista" id="notifLista">
                            <div class="notif-empty">
                                <i class="fas fa-check-circle"></i>
                                <p>Sin notificaciones nuevas</p>
                            </div>
                        </div>
                    </div>
                </div>

                <span class="medico-topbar__user">
                    <i class="fas fa-user-md"></i>
                    {{ session('usuario_nombre', 'Médico') }}
                </span>

                <a href="{{ route('medico.perfil.index') }}" class="medico-topbar__link">
                    <i class="fas fa-user-edit"></i>
                    <span>Mi Perfil</span>
                </a>

                <form method="POST" action="{{ route('logout') }}" class="medico-topbar__logout-form">
                    @csrf
                    <button type="submit" class="medico-topbar__link medico-topbar__link--danger">
                        <i class="fas fa-right-from-bracket"></i><span>Cerrar sesión</span>
                    </button>
                </form>
            </nav>
        </div>
    </header>

    <main class="medico-main">
        @yield('contenido')
    </main>

    <footer class="medico-footer">
        <p>&copy; {{ date('Y') }} Mi Médico. Todos los derechos reservados.</p>
        <p>Desarrollado por Dody Ortega, Jefferson Anchundia, Erick Plaza, Kevin Soledispa</p>
    </footer>

    <script src="{{ asset('assets/js/public.js') }}"></script>

    <script>
    // ── Notificaciones ────────────────────────────────────────────
    function cargarNotificaciones() {
        fetch('{{ route("medico.notif.index") }}')
            .then(r => r.json())
            .then(data => {
                const badge  = document.getElementById('notifBadge');
                const lista  = document.getElementById('notifLista');

                badge.style.display = data.total > 0 ? 'flex' : 'none';
                badge.textContent   = data.total;

                if (data.total === 0) {
                    lista.innerHTML = `<div class="notif-empty">
                        <i class="fas fa-check-circle"></i><p>Sin notificaciones nuevas</p>
                    </div>`;
                    return;
                }

                lista.innerHTML = data.notificaciones.map(n => `
                    <div class="notif-item notif-item--${n.color}" data-key="${n.key}">
                        <div class="notif-item__icon"><i class="fas fa-${n.icono}"></i></div>
                        <div class="notif-item__body">
                            <span class="notif-item__titulo">${n.titulo}</span>
                            <p class="notif-item__msg">${n.mensaje}</p>
                            <span class="notif-item__tiempo">${n.tiempo}</span>
                            ${n.es_emerg ? `<button class="notif-item__atender" onclick="atenderEmergencia(${n.id_agenda}, '${n.key}')"><i class="fas fa-check"></i> Atender</button>` : ''}
                        </div>
                        <button class="notif-item__cerrar" onclick="marcarLeida('${n.key}')">
                            <i class="fas fa-times"></i>
                        </button>
                    </div>`).join('');
            })
            .catch(() => {});
    }

    function toggleNotif() {
        const dd = document.getElementById('notifDropdown');
        dd.style.display = dd.style.display === 'none' ? 'block' : 'none';
        if (dd.style.display === 'block') cargarNotificaciones();
    }

    function marcarLeida(key) {
        fetch('{{ route("medico.notif.leida") }}', {
            method: 'POST',
            headers: {'Content-Type':'application/json','X-CSRF-TOKEN':'{{ csrf_token() }}'},
            body: JSON.stringify({key})
        }).then(() => cargarNotificaciones());
    }

    function marcarTodas() {
        fetch('{{ route("medico.notif.todas") }}', {
            method: 'POST',
            headers: {'X-CSRF-TOKEN':'{{ csrf_token() }}'}
        }).then(() => cargarNotificaciones());
    }

    function atenderEmergencia(idAgenda, key) {
        fetch('{{ route("medico.emergencia.atender") }}', {
            method: 'POST',
            headers: {'Content-Type':'application/json','X-CSRF-TOKEN':'{{ csrf_token() }}'},
            body: JSON.stringify({id_agenda: idAgenda})
        })
        .then(r => r.json())
        .then(data => {
            if (data.ok && data.room) {
                window.location.href = '/video/sala/' + data.room;
            } else if (!data.ok) {
                alert(data.msg || 'Esta emergencia ya fue atendida.');
            }
        })
        .catch(() => {});
    }

    document.addEventListener('click', function(e) {
        const wrap = document.getElementById('notifWrap');
        if (wrap && !wrap.contains(e.target))
            document.getElementById('notifDropdown').style.display = 'none';
    });

    document.addEventListener('DOMContentLoaded', function() {
        cargarNotificaciones();
        setInterval(cargarNotificaciones, 30000);
    });
    </script>

    @stack('scripts')
</body>
</html>
