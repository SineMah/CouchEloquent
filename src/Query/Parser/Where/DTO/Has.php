<?php

namespace Sinemah\CouchEloquent\Query\Parser\Where\DTO;

use Sinemah\CouchEloquent\Query\Builder;
use Sinemah\CouchEloquent\Query\Parser\DynamicDTO;
use Sinemah\CouchEloquent\Query\Parser\Traits\HasAttributes;

class Has extends DynamicDTO
{
    use HasAttributes;

    public string $type;
    public Builder $callback;
    public string $column;
}
