<?php

namespace Sinemah\CouchEloquent\Query\Parser\Where;

use Sinemah\CouchEloquent\Contracts\WhereContract;
use Sinemah\CouchEloquent\Query\Parser\Where\DTO\Basic as DTO;

class TypeIn implements WhereContract
{
    protected DTO $data;

    public static function load(array $values): TypeIn
    {
        return new self($values);
    }

    private function __construct(array $values)
    {
        if($values['value'] === null && $values['operator']) {
            $values['value'] = $values['operator'];
            $values['operator'] = '$eq';
        }

        $this->data = new DTO($values);
    }

    public function toQuery(): array
    {
        return [
            $this->data->column => [
                '$in' => $this->data->value
            ]
        ];
    }
}
