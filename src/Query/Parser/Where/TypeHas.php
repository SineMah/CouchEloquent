<?php

namespace Sinemah\CouchEloquent\Query\Parser\Where;

use Illuminate\Support\Arr;
use Sinemah\CouchEloquent\Contracts\WhereContract;
use Sinemah\CouchEloquent\Query\Parser\Where\DTO\Has as DTO;

class TypeHas implements WhereContract
{
    protected DTO $data;

    public static function load(array $values): TypeHas
    {
        return new self($values);
    }

    private function __construct(array $values)
    {
        $this->data = new DTO(
            [
                'type' => $values['type'],
                'callback' => $values['value'],
                'column' => $values['column'],
            ]
        );
    }

    public function toQuery(): ?array
    {
        return [
            $this->data->column => [
                '$elemMatch' =>  $this->data->callback->toQuery()['selector'] ?? null,
            ],
        ];
    }
}
