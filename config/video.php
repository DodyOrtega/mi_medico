<?php

return [
    /*
    |--------------------------------------------------------------------------
    | Servidor TURN para WebRTC
    |--------------------------------------------------------------------------
    | Si se deja en blanco, solo se usan los STUN de Google (funciona en
    | la misma red / LAN). Para producción en internet, configura un servidor
    | TURN para garantizar conectividad en redes con NAT estricto.
    |
    | Opción gratuita: https://www.metered.ca/tools/openrelay/
    |   TURN_URL=turn:openrelay.metered.ca:80
    |   TURN_USERNAME=openrelayproject
    |   TURN_CREDENTIAL=openrelayproject
    |
    | Opción auto-alojada (Coturn en el mismo servidor):
    |   TURN_URL=turn:tu-dominio.com:3478
    |   TURN_USERNAME=mimedico
    |   TURN_CREDENTIAL=contraseña-segura
    */
    'turn_url'        => env('TURN_URL', ''),
    'turn_username'   => env('TURN_USERNAME', ''),
    'turn_credential' => env('TURN_CREDENTIAL', ''),
];
