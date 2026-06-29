/* ============================================================
   carrusel.js — Carrusel automático con indicadores
   ============================================================ */

document.addEventListener('DOMContentLoaded', () => {
    const slides     = document.querySelectorAll('.slide');
    const indicators = document.querySelectorAll('.indicator');
    let current = 0;
    let timer;

    const goTo = (index) => {
        slides[current].classList.remove('active');
        indicators[current].classList.remove('active');
        current = (index + slides.length) % slides.length;
        slides[current].classList.add('active');
        indicators[current].classList.add('active');
    };

    const next = () => goTo(current + 1);
    const prev = () => goTo(current - 1);

    const startAuto = () => { timer = setInterval(next, 5000); };
    const stopAuto  = () => { clearInterval(timer); };

    document.querySelector('.slide-next')?.addEventListener('click', () => { stopAuto(); next(); startAuto(); });
    document.querySelector('.slide-prev')?.addEventListener('click', () => { stopAuto(); prev(); startAuto(); });

    indicators.forEach((dot, i) => {
        dot.addEventListener('click', () => { stopAuto(); goTo(i); startAuto(); });
    });

    startAuto();
});