<?php

namespace Sinemah\CouchEloquent\Query\Parser\Where\DTO;

use Sinemah\CouchEloquent\Query\Parser\DynamicDTO;
use Sinemah\CouchEloquent\Query\Parser\Traits\HasAttributes;

class Between extends DynamicDTO
{
    use HasAttributes;

    public string $type;
    public mixed $column;
    public mixed $values;
    public bool $not;
}
