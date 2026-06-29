@extends('layouts.public')

@section('titulo', 'Términos y Condiciones – MiMedico')

@section('contenido')

{{-- ═══════════════════════════════════════════
     HERO
════════════════════════════════════════════ --}}
<section class="terminos-hero">
    <div class="terminos-hero__inner">
        <span class="terminos-hero__badge">
            <i class="fas fa-file-shield"></i> Documento legal
        </span>
        <h1 class="terminos-hero__title">Términos y Condiciones</h1>
        <p class="terminos-hero__meta">
            Última actualización: {{ $ultimaActualizacion }} &nbsp;·&nbsp;
            Versión {{ $version }}
        </p>
    </div>
    <div class="terminos-hero__wave" aria-hidden="true">
        <svg viewBox="0 0 1440 60" preserveAspectRatio="none" xmlns="http://www.w3.org/2000/svg">
            <path d="M0,30 C720,60 720,0 1440,30 L1440,60 L0,60 Z" fill="#f4f8fb"/>
        </svg>
    </div>
</section>

{{-- ═══════════════════════════════════════════
     CONTENIDO LEGAL
════════════════════════════════════════════ --}}
<section class="terminos-body">
    <div class="container">
        <div class="terminos-layout">

            {{-- Índice lateral --}}
            <aside class="terminos-nav" aria-label="Índice de secciones">
                <h3 class="terminos-nav__title">
                    <i class="fas fa-list-ul"></i> Contenido
                </h3>
                <ol class="terminos-nav__list">
                    @foreach ($secciones as $i => $seccion)
                    <li>
                        <a href="#seccion-{{ $i + 1 }}" class="terminos-nav__link">
                            {{ $i + 1 }}. {{ $seccion['titulo'] }}
                        </a>
                    </li>
                    @endforeach
                </ol>
            </aside>

            {{-- Cuerpo del documento --}}
            <div class="terminos-content">

                <div class="terminos-intro">
                    <i class="fas fa-circle-info terminos-intro__icon"></i>
                    <p>
                        Al usar los servicios de <strong>Fundación Mi Médico</strong>,
                        aceptas estos términos en su totalidad. Te recomendamos leerlos
                        detenidamente antes de continuar.
                    </p>
                </div>

                @foreach ($secciones as $i => $seccion)
                <div id="seccion-{{ $i + 1 }}" class="terminos-seccion">
                    <h2 class="terminos-seccion__titulo">
                        <span class="terminos-seccion__num">{{ $i + 1 }}</span>
                        {{ $seccion['titulo'] }}
                    </h2>
                    <div class="terminos-seccion__body">
                        {!! $seccion['contenido'] !!}
                    </div>
                </div>
                @endforeach

                {{-- Firma / aceptación --}}
                <div class="terminos-firma">
                    <i class="fas fa-check-circle terminos-firma__icon"></i>
                    <p>
                        Al registrarte o usar nuestros servicios confirmas haber leído,
                        entendido y aceptado estos Términos y Condiciones.
                    </p>
                    <p class="terminos-firma__org">
                        <strong>Fundación Mi Médico</strong> — Ecuador
                    </p>
                </div>

            </div>{{-- /terminos-content --}}

        </div>{{-- /terminos-layout --}}
    </div>
</section>

@endsection

@push('styles')
<link rel="stylesheet" href="{{ asset('assets/css/terminos.css') }}">
@endpush

@push('scripts')
{{-- Resaltar sección activa al hacer scroll --}}
<script>
(function () {
    const links  = document.querySelectorAll('.terminos-nav__link');
    const sects  = document.querySelectorAll('.terminos-seccion');
    if (!links.length) return;

    const observer = new IntersectionObserver(entries => {
        entries.forEach(entry => {
            if (entry.isIntersecting) {
                links.forEach(l => l.classList.remove('active'));
                const active = document.querySelector(
                    `.terminos-nav__link[href="#${entry.target.id}"]`
                );
                if (active) active.classList.add('active');
            }
        });
    }, { rootMargin: '-20% 0px -60% 0px' });

    sects.forEach(s => observer.observe(s));
})();
</script>
@endpush