<?php

declare(strict_types=1);

namespace Engine\Database\QueryBuilder;

/**
 * Максимально быстрый для постройки конструктор запросов
 */
class QueryBuilder
{
    private string $query;
    private array $keys = [];

    public function __construct(string $sql){
        $this->query = $sql;
    }

    public function add(string $sql) : void {
        $this->query .= $sql;
    }

    public function orderBy(string $field, string $direction = 'DESC') : void {
        $this->query .= 'ORDER BY '.$field.' '.$direction;
    }

    public function limit(int $limit) : void {
        $this->query .= ' LIMIT '.$limit;
    }

    public function addKey(string $key, string|int|null $value) {
        $this->keys[$key] = $value;
    }

    public function query() : string {
        return $this->query;
    }

    public function keys() : array {
        return $this->keys;
    }

}