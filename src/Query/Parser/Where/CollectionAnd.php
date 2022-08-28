<?php

namespace Sinemah\CouchEloquent\Query\Parser\Where;

use Illuminate\Support\Collection;
use Sinemah\CouchEloquent\Contracts\WhereContract;
//use Sinemah\CouchEloquent\Query\Collection;

class CollectionAnd extends Collection
{
    public string $boolean = '$and';

    public function toQuery(): array
    {
        return collect($this->items)
            ->map(fn (WhereContract $item) => $item->toQuery())
            ->toArray();
    }
}