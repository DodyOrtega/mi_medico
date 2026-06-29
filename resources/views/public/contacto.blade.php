@extends('layouts.public')
@section('titulo', 'Contacto – MiMedico')
@section('contenido')

<section class="contacto-section">
    <div class="container">
        <h2 class="section-title">Contacto – Fundación Mi Médico</h2>
        <p class="section-sub">¿Preguntas? Escríbenos y te responderemos en menos de 24 h.</p>

        <div class="contacto-grid">
            <div class="contacto-card">
                <h3>Datos de contacto</h3>
                <p><i class="fas fa-envelope"></i> {{ $datosContacto['email'] }}</p>
                <p><i class="fas fa-phone"></i> {{ $datosContacto['telefono'] }}</p>
                <p><i class="fas fa-map-marker-alt"></i> {{ $datosContacto['direccion'] }}</p>
                <div class="redes">
                    <p><strong>Síguenos</strong></p>
                    <div class="redes-icons">
                        @foreach($redesSociales as $red)
                        <a href="{{ $red['url'] }}" target="_blank">
                            <img src="{{ asset('assets/images/icons/' . $red['icono']) }}" alt="{{ $red['nombre'] }}">
                        </a>
                        @endforeach
                    </div>
                </div>
            </div>

            <div class="contacto-card" style="display:flex;flex-direction:column;align-items:center;justify-content:center;text-align:center;gap:20px;">
                <div>
                    <i class="fab fa-whatsapp" style="font-size:3.5rem;color:#25d366;"></i>
                    <h3 style="margin-top:12px;">Escríbenos por WhatsApp</h3>
                    <p style="color:#64748b;font-size:.93rem;margin-top:6px;">
                        Respuesta rápida en horario de atención.<br>Lunes a Viernes · 08:00 – 18:00
                    </p>
                </div>
                @php $waNumero = preg_replace('/\D/', '', $datosContacto['telefono']); @endphp
                <a href="https://wa.me/{{ $waNumero }}?text={{ urlencode('Hola, quiero contactarme con la Fundación Mi Médico.') }}"
                   target="_blank"
                   style="display:inline-flex;align-items:center;gap:10px;background:#25d366;color:#fff;font-weight:700;font-size:1rem;padding:14px 28px;border-radius:50px;text-decoration:none;box-shadow:0 4px 14px rgba(37,211,102,.35);transition:background .2s;"
                   onmouseover="this.style.background='#1ebe5d'"
                   onmouseout="this.style.background='#25d366'">
                    <i class="fab fa-whatsapp" style="font-size:1.3rem;"></i>
                    Abrir WhatsApp
                </a>
                <p style="font-size:.8rem;color:#94a3b8;">{{ $datosContacto['telefono'] }}</p>
            </div>
        </div>

        <div class="mapa-wrap">
            <iframe src="{{ $mapaEmbed }}"
                width="100%" height="400" style="border:0; border-radius:8px;"
                allowfullscreen loading="lazy" referrerpolicy="no-referrer-when-downgrade">
            </iframe>
        </div>
    </div>
</section>

@endsection
@push('styles')
<link rel="stylesheet" href="{{ asset('assets/css/contacto.css') }}">
@endpush
