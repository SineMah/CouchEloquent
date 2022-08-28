<?php

namespace Sinemah\CouchEloquent\Query\Parser\Query;

use Sinemah\CouchEloquent\Query\Parser\DynamicDTO;
use Sinemah\CouchEloquent\Query\Parser\Traits\HasAttributes;

class DTO extends DynamicDTO
{
    use HasAttributes;

    public ?array $selector;
}
