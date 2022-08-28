<?php

namespace Sinemah\CouchEloquent\Query\Parser\Where;

class CollectionOr extends CollectionAnd
{
    public string $boolean = '$or';
}