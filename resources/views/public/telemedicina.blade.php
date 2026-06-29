@extends('layouts.public')

@section('titulo', 'Telemedicina – MiMedico')

@section('contenido')

{{-- ═══════════════════════════════════════════
     HERO — Telemedicina
════════════════════════════════════════════ --}}
<section class="tele-hero">
    <div class="tele-hero__inner">
        <span class="tele-hero__badge">
            <i class="fas fa-satellite-dish"></i> Salud digital
        </span>
        <h1 class="tele-hero__title">
            Telemedicina: <br>
            <span class="tele-hero__accent">Atención de salud online.</span>
        </h1>
        <p class="tele-hero__sub">
            Transformamos los servicios de salud tradicional en experiencias
            digitales accesibles, rápidas y seguras.
        </p>
        <a href="{{ route('login') }}" class="btn btn--primary btn--lg">
            <i class="fas fa-play"></i> Comenzar consulta ahora
        </a>
    </div>
    <div class="tele-hero__wave" aria-hidden="true">
        <svg viewBox="0 0 1440 80" preserveAspectRatio="none" xmlns="http://www.w3.org/2000/svg">
            <path d="M0,0 C480,80 960,0 1440,60 L1440,80 L0,80 Z" fill="#f4f8fb"/>
        </svg>
    </div>
</section>

{{-- ═══════════════════════════════════════════
     CARACTERÍSTICAS PRINCIPALES
════════════════════════════════════════════ --}}
<section class="tele-features">
    <div class="container">
        <h2 class="section-title">¿Qué ofrecemos?</h2>
        <p class="section-sub">Todo lo que necesitas para tu atención médica, en un solo lugar</p>

        <div class="tele-features__grid">
            @foreach ($caracteristicas as $item)
            <div class="tele-feature-card tele-feature-card--{{ $item['color'] }}">
                <div class="tele-feature-card__icon">
                    <i class="fas fa-{{ $item['icono'] }}"></i>
                </div>
                <h3 class="tele-feature-card__title">{{ $item['titulo'] }}</h3>
                <p class="tele-feature-card__text">{{ $item['descripcion'] }}</p>
            </div>
            @endforeach
        </div>
    </div>
</section>

{{-- ═══════════════════════════════════════════
     CÓMO FUNCIONA — PASOS
════════════════════════════════════════════ --}}
<section class="tele-pasos">
    <div class="container">
        <h2 class="section-title">¿Cómo funciona?</h2>
        <p class="section-sub">En 4 pasos simples ya tienes tu consulta médica</p>

        <div class="tele-pasos__grid">
            @foreach ($pasos as $paso)
            <div class="tele-paso">
                <div class="tele-paso__num">{{ $paso['numero'] }}</div>
                <div class="tele-paso__icon">
                    <i class="fas fa-{{ $paso['icono'] }}"></i>
                </div>
                <h3 class="tele-paso__title">{{ $paso['titulo'] }}</h3>
                <p class="tele-paso__text">{{ $paso['descripcion'] }}</p>
            </div>
            @endforeach
        </div>
    </div>
</section>

{{-- ═══════════════════════════════════════════
     BENEFICIOS — CITAS FLEXIBLES
════════════════════════════════════════════ --}}
<section class="tele-beneficios">
    <div class="container">
        <div class="tele-beneficios__card">
            <h2 class="tele-beneficios__title">Citas flexibles y seguras</h2>

            <div class="tele-beneficios__grid">
                @foreach ($beneficios as $b)
                <div class="tele-beneficio">
                    <div class="tele-beneficio__icon tele-beneficio__icon--{{ $b['color'] }}">
                        <i class="fas fa-{{ $b['icono'] }}"></i>
                    </div>
                    <h4 class="tele-beneficio__title">{{ $b['titulo'] }}</h4>
                    <p class="tele-beneficio__text">{{ $b['descripcion'] }}</p>
                </div>
                @endforeach
            </div>
        </div>
    </div>
</section>

{{-- ═══════════════════════════════════════════
     CTA FINAL
════════════════════════════════════════════ --}}
<section class="cta-tele">
    <div class="container cta-tele__inner">
        <h2 class="cta-tele__title">Tu salud no puede esperar</h2>
        <p class="cta-tele__sub">
            Consulta con un médico certificado hoy mismo, sin salir de casa.
        </p>
        <a href="{{ route('login') }}" class="btn btn--white btn--lg">
            <i class="fas fa-stethoscope"></i> Iniciar consulta
        </a>
    </div>
</section>

@endsection

@push('styles')
<link rel="stylesheet" href="{{ asset('assets/css/telemedicina.css') }}">
@endpush