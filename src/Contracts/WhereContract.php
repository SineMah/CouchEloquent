<?php

namespace Sinemah\CouchEloquent\Contracts;

interface WhereContract
{
    public static function load(array $values): WhereContract;
}