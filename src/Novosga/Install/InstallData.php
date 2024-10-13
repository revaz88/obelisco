<?php

namespace Novosga\Install;

/**
 * Clase para guardar los datos de la instalación en la sesión.
 *
 * 
 */
class InstallData
{
    const SESSION_KEY = 'SGA_INSTALL_DATA';

    public static $dbTypes = array(
        'pgsql' => array(
            'label' => 'PDO PgSQL',
            'rdms' => 'PostgreSQL',
            'version' => '1.0.2',
            'port' => '5432',
            'driver' => array(
                'linux' => 'pdo_pgsql',
                'win' => 'pdo_pgsql',
            ),
        ),
        'mysql' => array(
            'label' => 'PDO MySQL',
            'rdms' => 'MySQL',
            'version' => '1.0.0',
            'port' => '3306',
            'driver' => array(
                'linux' => 'pdo_mysql',
                'win' => 'pdo_mysql',
            ),
        ),
    );

    public static $dbFields = array(
        'driver' => 'Debera escribir el tipo de base de datos.',
        'host' => 'Debera escribir la dirección de la base datos.',
        'port' => 'Debera escribir el puerto de la base de datos.',
        'user' => 'Debera escribir el usuario de la base de datos.',
        'password' => 'Debera escribir la contraseña de la base de datos.',
        'dbname' => 'Debera escribir el nombre de la base de datos.',
        'charset' => 'Debera escribir la codificación (charset) de la base de datos.',
    );

    public static $adminFields = array(
        'nome' => 'Debe escribir un nombre.',
        'sobrenome' => 'Debe escribir un apellido.',
        'login' => 'Debe escribir un nombre de usuari.',
        'senha' => 'debe escribir una contraseña.',
        'senha_2' => 'debe confirmar la contraseña.',
    );

    public $database = array();
    public $admin = array();

    public function __construct()
    {
        foreach (self::$dbFields as $k => $v) {
            $this->database[$k] = '';
        }
        foreach (self::$adminFields as $k => $v) {
            $this->admin[$k] = '';
        }
    }
}
