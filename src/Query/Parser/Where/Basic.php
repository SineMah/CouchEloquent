<?php

namespace Sinemah\CouchEloquent\Query\Parser\Where;

use Sinemah\CouchEloquent\Contracts\WhereContract;
use Sinemah\CouchEloquent\Query\Parser\Where\DTO\Basic as DTO;

class Basic implements WhereContract
{
    protected DTO $data;

    public static function load(array $values): self
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
                $this->data->operator => $this->data->value
            ]
        ];
    }
}
