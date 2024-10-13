<?php

namespace Novosga;

/**
 * Security.
 *
 * 
 */
class Security
{
    public static function passEncode($pass)
    {
        return md5($pass);
    }
}
