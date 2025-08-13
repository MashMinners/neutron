<?php

declare(strict_types=1);

namespace Engine\Database;

use PDO;

class MySQLDbConnector implements IConnector
{
    private static $_instance;

    private static $_options = [
        PDO::ATTR_ERRMODE            => PDO::ERRMODE_EXCEPTION,
        PDO::ATTR_DEFAULT_FETCH_MODE => PDO::FETCH_ASSOC,
        PDO::ATTR_EMULATE_PREPARES   => false,
    ];

    public function __construct(){}

    public static function connect() : PDO
    {
        if (empty(self::$_instance)){
            $configs = require 'configs/database.php';
            self::$_instance = new
            PDO(
                "mysql:host=".$configs['db_host'].'; 
                      port='.$configs['db_port'].'; 
                      dbname='.$configs['db_name'].';
                      charset='.$configs['db_charset'],
                $configs['db_user'],
                $configs['db_pass'],
                self::$_options);
        }
        return self::$_instance;
    }
}