<?php

namespace Sinemah\CouchEloquent\Query\Parser\Where\DTO;

use Sinemah\CouchEloquent\Query\Parser\DynamicDTO;
use Sinemah\CouchEloquent\Query\Parser\Traits\HasAttributes;

class Basic extends DynamicDTO
{
    use HasAttributes;

    public string $type;
    public ?string $operator;
    public mixed $column;
    public mixed $value;
    public string $boolean;
    public bool $not;
}
