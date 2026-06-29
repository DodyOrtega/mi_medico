@extends('layouts.public')

@section('titulo', 'Quiénes Somos – MiMedico')

@section('contenido')

{{-- ═══════════════════════════════════════════
     HERO — Quiénes Somos
════════════════════════════════════════════ --}}
<section class="quienes-hero">
    <div class="quienes-hero__inner">
        <span class="quienes-hero__badge">
            <i class="fas fa-heart-pulse"></i> Sobre nosotros
        </span>
        <h1 class="quienes-hero__title">Quiénes Somos</h1>
        <p class="quienes-hero__sub">
            En Mi Médico conectamos pacientes y profesionales de la salud a través
            de la tecnología, haciendo la atención médica más accesible, rápida y segura.
        </p>
    </div>
    <div class="quienes-hero__wave" aria-hidden="true">
        <svg viewBox="0 0 1440 80" preserveAspectRatio="none" xmlns="http://www.w3.org/2000/svg">
            <path d="M0,40 C360,80 1080,0 1440,40 L1440,80 L0,80 Z" fill="#f4f8fb"/>
        </svg>
    </div>
</section>

{{-- ═══════════════════════════════════════════
     MISIÓN Y VISIÓN
════════════════════════════════════════════ --}}
<section class="mv-section">
    <div class="container">
        <div class="mv-grid">

            @foreach ($misionVision as $card)
            <div class="mv-card mv-card--{{ $card['tipo'] }}">
                <div class="mv-card__icon-wrap">
                    <img src="{{ asset('assets/images/' . $card['imagen']) }}"
                         alt="{{ $card['titulo'] }}"
                         class="mv-card__icon">
                </div>
                <h2 class="mv-card__title">{{ $card['titulo'] }}</h2>
                <p class="mv-card__text">{{ $card['texto'] }}</p>
            </div>
            @endforeach

        </div>
    </div>
</section>

{{-- ═══════════════════════════════════════════
     CARACTERÍSTICAS / VALORES
════════════════════════════════════════════ --}}
<section class="valores-section">
    <div class="container">
        <h2 class="section-title">Lo que nos define</h2>
        <p class="section-sub">Tecnología y trato humano al servicio de tu salud</p>

        <div class="valores-grid">
            @foreach ($valores as $valor)
            <div class="valor-card">
                <div class="valor-card__img-wrap">
                    <img src="{{ asset('assets/images/icons/' . $valor['icono']) }}"
                         alt="{{ $valor['titulo'] }}"
                         class="valor-card__img">
                </div>
                <h3 class="valor-card__title">{{ $valor['titulo'] }}</h3>
                <p class="valor-card__text">{{ $valor['descripcion'] }}</p>
            </div>
            @endforeach
        </div>
    </div>
</section>

{{-- ═══════════════════════════════════════════
     CTA FINAL
════════════════════════════════════════════ --}}
<section class="cta-nosotros">
    <div class="container cta-nosotros__inner">
        <h2 class="cta-nosotros__title">¿Listo para cuidar tu salud?</h2>
        <p class="cta-nosotros__sub">
            Únete a miles de pacientes que ya gestionan sus consultas con Mi Médico.
        </p>
        <div class="cta-nosotros__btns">
            <a href="{{ route('telemedicina') }}" class="btn btn--primary">
                <i class="fas fa-video"></i> Conoce la telemedicina
            </a>
            <a href="{{ route('contacto') }}" class="btn btn--outline-white">
                <i class="fas fa-envelope"></i> Contáctanos
            </a>
        </div>
    </div>
</section>

@endsection

@push('styles')
<link rel="stylesheet" href="{{ asset('assets/css/nosotros.css') }}">
@endpush