<?php

namespace Sinemah\CouchEloquent\Query\Parser\Where;

use Sinemah\CouchEloquent\Contracts\WhereContract;
use Sinemah\CouchEloquent\Query\Parser\Where\DTO\Nested as DTO;

class Nested implements WhereContract
{
    protected DTO $data;

    public static function load(array $values): Nested
    {
        return new self($values);
    }

    private function __construct(array $values)
    {
        $this->data = new DTO([
            'type' => 'nested',
            'boolean' => 'and',
            'callback' => $values['value'] ?? null,
        ]);
    }

    public function toQuery(): ?array
    {
        return $this->data->callback->toQuery()['selector'] ?? null;
    }
}
