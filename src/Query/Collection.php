<?php

namespace Sinemah\CouchEloquent\Query;

use Illuminate\Support\Collection as BaseCollection;


class Collection extends BaseCollection
{
    public function toQuery(): ?array
    {
        $result = [];
        $queryItems = 0;

        foreach($this->items as $item) {
            $boolQueries = collect($item->toQuery());

            $boolQueries->each(function(array $query) use ($item, &$result) {
                $result[$item->boolean][] = $query;
            });

            $queryItems += $boolQueries->count();
        }

        return $queryItems > 0 ? $result : null;
    }
}
