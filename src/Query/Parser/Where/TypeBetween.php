<?php

namespace Sinemah\CouchEloquent\Query\Parser\Where;

use Sinemah\CouchEloquent\Contracts\WhereContract;
use Sinemah\CouchEloquent\Query\Parser\Where\DTO\Between as DTO;

class TypeBetween implements WhereContract
{
    protected DTO $data;

    public static function load(array $values): TypeBetween
    {
        return new self($values);
    }

    private function __construct(array $values)
    {
        $this->data = new DTO($values);
    }

    public function toQuery(): array
    {
        $values = collect($this->data->values);
        $and = new CollectionAnd(
            [
                TypeLte::load(['value' => $values->last(), 'column' => $this->data->column]),
                TypeGte::load(['value' => $values->first(), 'column' => $this->data->column]),
            ]
        );
//        dd($and->toQuery(), $and->boolean);
//        $and->add();
        return [$and->boolean => $and->toQuery()];
    }
}
