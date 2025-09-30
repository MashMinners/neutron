<?php

namespace Application\IntersectionsFinder\Models;

use Engine\Database\IConnector;

class SickNoteIntersectionsFinder
{
    public function __construct(IConnector $connector){
        $this->pdo = $connector::connect();
    }

    private function findIntersections(){
        $query = ("");
        $stmt = $this->pdo->prepare($query);
        $stmt->execute();
        $result = $stmt->fetchAll();
        return $result;
    }

}