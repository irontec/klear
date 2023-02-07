<?php

/**
 * Clase respuesta Simple para peticiones desde klear.request.js
 * Respuestas simples de acciones concretas
 * No requieren carga de ficheros extra
 *
 * @author jabi
 */
class Klear_Model_RedirectResponse extends Klear_Model_SimpleResponse
{
    final const RESPONSE_TYPE = 'redirect';
}
