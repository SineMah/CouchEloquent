<?php

namespace Sinemah\CouchEloquent\Relations;

use Illuminate\Support\Collection as BaseCollection;
use Sinemah\CouchEloquent\Eloquent\Model;

class Collection extends BaseCollection
{
    public ?string $model = null;
    public ?string $name = null;

    public static function load(string $name, string $model, Model $instance): self
    {
        $collection = new self();

        $instance->$name;

        $collection->name = $name;
        $collection->model = $model;

        return $collection;
    }
}
