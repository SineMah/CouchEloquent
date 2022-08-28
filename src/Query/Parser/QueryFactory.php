<?php

namespace Sinemah\CouchEloquent\Query\Parser;

use Sinemah\CouchEloquent\Query\Collection;
use Sinemah\CouchEloquent\Query\Parser\Query\DTO;

class QueryFactory
{
    protected DTO $data;

    public static function load(Collection $wheres): QueryFactory
    {
        return new self($wheres->toQuery());
    }

    private function __construct(?array $selector)
    {
        $this->data = new DTO(['selector' => $selector]);
    }

    public function toQuery(): ?array
    {
        return $this->data->selector === null ? null : ['selector' => $this->data->selector];
    }
}
