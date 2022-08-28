<?php

namespace Sinemah\CouchEloquent\Query\Parser;

use Sinemah\CouchEloquent\Query\Parser\Traits\HasAttributes;

class DynamicDTO
{
    use HasAttributes;

    public function __construct(array $values = [])
    {
        $this->loadAttributes($values);
    }
}
