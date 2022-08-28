<?php

namespace Sinemah\CouchEloquent\Query\Parser\Where;

use Sinemah\CouchEloquent\Contracts\WhereContract;
use Sinemah\CouchEloquent\Query\Parser\Where\DTO\Basic as DTO;

class TypeLte implements WhereContract
{
    protected DTO $data;

    public static function load(array $values): self
    {
        return new self($values);
    }

    private function __construct(array $values)
    {
        $this->data = new DTO($values);
    }

    public function toQuery(): array
    {
        return [
            $this->data->column => [
                '$lte' => $this->data->value
            ]
        ];
    }
}
