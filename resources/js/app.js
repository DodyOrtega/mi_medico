/* ============================================================
   app.js — Navbar scroll + hamburger + carrusel
   Mi Médico — Laravel
   ============================================================ */

document.addEventListener('DOMContentLoaded', () => {

    /* ── 1. Navbar transparente → sólido al hacer scroll ── */
    const navbar = document.getElementById('navbar')
    if (document.body.classList.contains('page-inicio') && navbar) {
        window.addEventListener('scroll', () => {
            navbar.classList.toggle('scrolled', window.scrollY > 80)
        })
    }

    /* ── 2. Hamburguesa mobile ── */
    const hamburger  = document.getElementById('hamburger')
    const mobileMenu = document.getElementById('mobileMenu')
    hamburger?.addEventListener('click', () => {
        hamburger.classList.toggle('open')
        mobileMenu.classList.toggle('open')
    })

    /* ── 3. Carrusel ── */
    const slides     = document.querySelectorAll('.slide')
    const indicators = document.querySelectorAll('.indicator')
    if (!slides.length) return

    let current = 0
    let timer   = null

    const goTo = (index) => {
        slides[current].classList.remove('active')
        indicators[current]?.classList.remove('active')
        current = (index + slides.length) % slides.length
        slides[current].classList.add('active')
        indicators[current]?.classList.add('active')
    }

    const next = () => goTo(current + 1)
    const prev = () => goTo(current - 1)

    const startAuto = () => { timer = setInterval(next, 5000) }
    const stopAuto  = () => { clearInterval(timer) }

    document.querySelector('.slide-next')?.addEventListener('click', () => { stopAuto(); next(); startAuto() })
    document.querySelector('.slide-prev')?.addEventListener('click', () => { stopAuto(); prev(); startAuto() })

    indicators.forEach((dot, i) => {
        dot.addEventListener('click', () => { stopAuto(); goTo(i); startAuto() })
    })

    startAuto()
})