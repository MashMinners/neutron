<?php

namespace Engine\Database;

interface IConnector
{
    public static function connect() : \PDO;

}