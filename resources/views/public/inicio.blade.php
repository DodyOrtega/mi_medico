@extends('layouts.public')
@section('titulo', 'Inicio')

@section('contenido')

{{-- ═══ CARRUSEL ═══ --}}
<section class="hero">
    <div class="carrusel">

        @foreach($slides as $i => $slide)
        <div class="slide {{ $i === 0 ? 'active' : '' }}" data-slide="{{ $i }}">
            <img src="{{ asset('assets/images/' . $slide['imagen']) }}"
                 alt="{{ $slide['titulo'] }}"
                 loading="{{ $i === 0 ? 'eager' : 'lazy' }}">
            <div class="caption">
                <h2>{{ $slide['titulo'] }}</h2>
                <a href="{{ $slide['link'] }}" class="caption-btn">
                    Más información <i class="fas fa-chevron-right"></i>
                </a>
            </div>
        </div>
        @endforeach

        <button class="slide-prev" aria-label="Anterior">
            <i class="fas fa-chevron-left"></i>
        </button>
        <button class="slide-next" aria-label="Siguiente">
            <i class="fas fa-chevron-right"></i>
        </button>

        <div class="indicators">
            @foreach($slides as $i => $slide)
                <span class="indicator {{ $i === 0 ? 'active' : '' }}"
                      data-slide="{{ $i }}"></span>
            @endforeach
        </div>

    </div>
</section>

{{-- ═══ BIENVENIDA ═══ --}}
<section class="bienvenida">
    <div class="container">
        <h1>Bienvenido a Mi Médico</h1>
        <blockquote>
            "Tu salud, más cerca de ti. Consulta a tu médico desde casa."
        </blockquote>
        <p>
            Una plataforma que conecta personal de salud y pacientes
            de manera rápida, segura y personalizada.
        </p>
        <a href="{{ route('telemedicina') }}" class="btn-primary">
            Conoce nuestros servicios <i class="fas fa-arrow-right"></i>
        </a>
    </div>
</section>

@endsection