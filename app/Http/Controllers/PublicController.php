<?php

namespace App\Http\Controllers;

use App\Http\Controllers\Admin\ConfiguracionController;
use Illuminate\Http\Request;

/**
 * PublicController
 *
 * Maneja todas las páginas del sitio público.
 * Regla: CERO HTML aquí. Solo lógica y datos → los pasa a las vistas Blade.
 */
class PublicController extends Controller
{
    // ─────────────────────────────────────────────
    // INICIO
    // ─────────────────────────────────────────────
    public function inicio()
    {
        $slides = [
        [
            'imagen'    => 'carrusel/carrusel2.jpeg',
            'titulo'    => 'Tu salud, nuestra prioridad',
            'subtitulo' => 'Consultas médicas online 24/7 desde cualquier dispositivo.',
            'link'      => route('telemedicina'),
        ],
        [
            'imagen'    => 'carrusel/carrusel3.jpeg',
            'titulo'    => 'Médicos certificados',
            'subtitulo' => 'Conectamos pacientes con especialistas de confianza.',
            'link'      => route('nosotros'),
        ],
        [
            'imagen'    => 'carrusel/carrusel4.jpeg',
            'titulo'    => 'Historia clínica digital',
            'subtitulo' => 'Accede a tu historial en cualquier momento y lugar.',
            'link'      => route('contacto'),
        ],
    ];
        return view('public.inicio', compact('slides'));
    }

    // ─────────────────────────────────────────────
    // QUIÉNES SOMOS
    // ─────────────────────────────────────────────
    public function nosotros()
    {
        $misionVision = [
            [
                'tipo'   => 'mision',
                'imagen' => 'mision.png',
                'titulo' => 'Misión',
                'texto'  => 'Permitir el acceso oportuno para atenciones de salud mediante consultas
                             médicas virtuales, gestión digital de historias clínicas y herramientas
                             de tele-seguimiento, garantizando calidad, confidencialidad y trato humano.',
            ],
            [
                'tipo'   => 'vision',
                'imagen' => 'vision.png',
                'titulo' => 'Visión',
                'texto'  => 'Ser la plataforma líder de telemedicina en Latinoamérica, reduciendo
                             barreras geográficas y tiempos de espera, y mejorando los indicadores
                             de salud de la población vulnerable a través de la innovación tecnológica.',
            ],
        ];

        $valores = [
            [
                'icono'       => 'video.png',
                'titulo'      => 'Videoconsulta',
                'descripcion' => 'Comunicación cifrada end-to-end sin descargas, desde cualquier navegador.',
            ],
            [
                'icono'       => 'alta.png',
                'titulo'      => 'Alta Online',
                'descripcion' => 'Registro rápido, firma digital y consentimientos informados sin papeleos.',
            ],
            [
                'icono'       => 'history.png',
                'titulo'      => 'Historia Clínica Personalizada',
                'descripcion' => 'Fichas clínicas adaptadas a cada especialidad y acceso multi-dispositivo.',
            ],
            [
                'icono'       => 'recipe.png',
                'titulo'      => 'Receta Electrónica',
                'descripcion' => 'Prescripciones certificadas y válidas en farmacias con cumplimiento legal.',
            ],
        ];

        return view('public.nosotros', compact('misionVision', 'valores'));
    }

    // ─────────────────────────────────────────────
    // TELEMEDICINA
    // ─────────────────────────────────────────────
    public function telemedicina()
    {
        // Tarjetas de características principales
        $caracteristicas = [
            [
                'icono'       => 'video',
                'color'       => 'azul',
                'titulo'      => 'Atención médica en línea',
                'descripcion' => 'Atención de salud 24/7/365 desde cualquier dispositivo. Sin filas, sin esperas.',
            ],
            [
                'icono'       => 'file-medical',
                'color'       => 'morado',
                'titulo'      => 'Historial clínico digital',
                'descripcion' => 'Toda tu información médica centralizada, accesible y protegida con encriptación avanzada.',
            ],
            [
                'icono'       => 'stethoscope',
                'color'       => 'verde',
                'titulo'      => 'Especialistas certificados',
                'descripcion' => 'Red de médicos verificados en múltiples especialidades disponibles para ti.',
            ],
        ];

        // Pasos del proceso
        $pasos = [
            [
                'numero'      => '01',
                'icono'       => 'user-plus',
                'titulo'      => 'Créate una cuenta',
                'descripcion' => 'Regístrate en minutos con tu email o número de cédula.',
            ],
            [
                'numero'      => '02',
                'icono'       => 'calendar-check',
                'titulo'      => 'Agenda tu cita',
                'descripcion' => 'Escoge el especialista, fecha y hora que mejor te convengan.',
            ],
            [
                'numero'      => '03',
                'icono'       => 'video',
                'titulo'      => 'Conéctate a la consulta',
                'descripcion' => 'Desde el navegador, sin descargas. Solo necesitas internet.',
            ],
            [
                'numero'      => '04',
                'icono'       => 'file-prescription',
                'titulo'      => 'Recibe tu receta',
                'descripcion' => 'El médico emite tu receta electrónica al instante.',
            ],
        ];

        // Beneficios de la sección "Citas flexibles"
        $beneficios = [
            [
                'icono'       => 'clock',
                'color'       => 'azul',
                'titulo'      => 'Horarios flexibles',
                'descripcion' => 'Disponibilidad 24/7/365 incluyendo fines de semana.',
            ],
            [
                'icono'       => 'prescription-bottle-medical',
                'color'       => 'verde',
                'titulo'      => 'Recetas electrónicas',
                'descripcion' => 'Disposición inmediata para ser vista en la farmacia que tú elijas.',
            ],
            [
                'icono'       => 'shield-halved',
                'color'       => 'morado',
                'titulo'      => 'Seguridad garantizada',
                'descripcion' => 'Encriptación médica y cumplimiento de estándares internacionales.',
            ],
        ];

        return view('public.telemedicina', compact('caracteristicas', 'pasos', 'beneficios'));
    }

    // ─────────────────────────────────────────────
    // CONTACTO
    // ─────────────────────────────────────────────
    public function contacto()
    {
        $general = ConfiguracionController::leerConfig()['general'] ?? [];
        $datosContacto = [
            'email'     => $general['email_contacto']    ?? 'globalhealthsas@gmail.com',
            'telefono'  => $general['telefono_contacto'] ?? '+593 97 932 8112',
            'direccion' => $general['direccion']         ?? 'Calle César Chávez y 5ta transversal',
        ];

        $redesSociales = [
            [
                'nombre' => 'Facebook',
                'icono'  => 'facebook.png',
                'url'    => 'https://www.facebook.com/fundacionmimedico',
            ],
            [
                'nombre' => 'Instagram',
                'icono'  => 'instagram.png',
                'url'    => 'https://www.instagram.com/p/CDtlT0pHueZ/?igsh=ODNycjRraDVqZG5q',
            ],
            [
                'nombre' => 'WhatsApp',
                'icono'  => 'whatsapp.png',
                'url'    => 'https://wa.me/+593979328112',
            ],
        ];

        $mapaEmbed = 'https://www.google.com/maps/embed?pb=!1m17!1m12!1m3!1d867.6294880277713'
                   . '!2d-80.45149698441199!3d-1.048254535313996!2m3!1f0!2f0!3f0!3m2!1i1024'
                   . '!2i768!4f13.1!3m2!1m1!2zMcKwMDInNTMuOSJTIDgwwrAyNycwMi40Ilc!5e0!3m2'
                   . '!1ses!2sec!4v1761326707563!5m2!1ses!2sec';

        return view('public.contacto', compact('datosContacto', 'redesSociales', 'mapaEmbed'));
    }

    // ─────────────────────────────────────────────
    // TÉRMINOS Y CONDICIONES
    // ─────────────────────────────────────────────
    public function terminos()
    {
        $ultimaActualizacion = '1 de junio de 2025';
        $version             = '2.0';

        /*
         * Cada sección tiene:
         *   titulo   → aparece en el índice lateral y como encabezado
         *   contenido → HTML seguro (puedes usar <p>, <ul>, <strong>)
         *
         * Regla: si el texto legal crece, muévelo a un archivo de configuración
         * o a la base de datos. Por ahora centralizado aquí.
         */
        $secciones = [
            [
                'titulo'    => 'Aceptación de los Términos',
                'contenido' => '<p>Al acceder o utilizar los servicios de <strong>Fundación Mi Médico</strong>
                                ("la Plataforma"), usted acepta quedar legalmente vinculado por estos
                                Términos y Condiciones. Si no está de acuerdo con alguna parte de estos
                                términos, no podrá utilizar nuestros servicios.</p>',
            ],
            [
                'titulo'    => 'Descripción del Servicio',
                'contenido' => '<p>Mi Médico es una plataforma de telemedicina que conecta a pacientes
                                con profesionales de la salud certificados. Los servicios incluyen:</p>
                                <ul>
                                    <li>Consultas médicas virtuales por videollamada</li>
                                    <li>Gestión de historias clínicas digitales</li>
                                    <li>Emisión de recetas electrónicas</li>
                                    <li>Agendamiento y seguimiento de citas</li>
                                    <li>Servicios de teleseguimiento y paramedicina</li>
                                </ul>',
            ],
            [
                'titulo'    => 'Registro y Cuentas de Usuario',
                'contenido' => '<p>Para acceder a los servicios debe crear una cuenta con información
                                veraz y actualizada. Usted es responsable de mantener la confidencialidad
                                de sus credenciales y de todas las actividades realizadas desde su cuenta.</p>
                                <p>Nos reservamos el derecho de suspender o cancelar cuentas que
                                incumplan estos términos o que presenten información falsa.</p>',
            ],
            [
                'titulo'    => 'Privacidad y Datos de Salud',
                'contenido' => '<p>La información de salud que usted comparte en la Plataforma es
                                tratada con el más alto nivel de confidencialidad, conforme a la
                                legislación ecuatoriana vigente y estándares internacionales de
                                seguridad de datos médicos.</p>
                                <p>No compartimos sus datos con terceros sin su consentimiento expreso,
                                salvo en los casos exigidos por ley.</p>',
            ],
            [
                'titulo'    => 'Responsabilidades del Paciente',
                'contenido' => '<ul>
                                    <li>Proporcionar información médica precisa y completa.</li>
                                    <li>Asistir puntualmente a las consultas agendadas.</li>
                                    <li>Cancelar citas con al menos 2 horas de anticipación.</li>
                                    <li>No compartir las credenciales de acceso.</li>
                                    <li>Reportar cualquier incidente de seguridad de forma inmediata.</li>
                                </ul>',
            ],
            [
                'titulo'    => 'Limitación de Responsabilidad',
                'contenido' => '<p>Mi Médico es una plataforma tecnológica que facilita la conexión
                                entre pacientes y profesionales de la salud. La responsabilidad clínica
                                recae íntegramente en el profesional médico que presta el servicio.</p>
                                <p>No somos responsables de diagnósticos, tratamientos ni decisiones
                                médicas tomadas fuera de la Plataforma.</p>',
            ],
            [
                'titulo'    => 'Modificaciones a los Términos',
                'contenido' => '<p>Nos reservamos el derecho de modificar estos Términos y Condiciones
                                en cualquier momento. Los cambios entrarán en vigor al ser publicados
                                en la Plataforma. El uso continuado del servicio después de dichos
                                cambios implica la aceptación de los nuevos términos.</p>',
            ],
            [
                'titulo'    => 'Legislación Aplicable',
                'contenido' => '<p>Estos Términos se rigen por las leyes de la República del Ecuador.
                                Cualquier disputa será resuelta ante los tribunales competentes de
                                la ciudad de Manta, provincia de Manabí.</p>
                                <p>Para consultas sobre estos términos puede contactarnos en:
                                <strong>globalhealthsas@gmail.com</strong></p>',
            ],
        ];

        return view('public.terminos', compact('ultimaActualizacion', 'version', 'secciones'));
    }
}