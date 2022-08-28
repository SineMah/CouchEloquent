<?php

namespace Sinemah\CouchEloquent\Query\Parser;

use Illuminate\Support\Arr;
use Illuminate\Support\Str;
use Sinemah\CouchEloquent\Query\Collection;
use Sinemah\CouchEloquent\Query\Parser\Where\CollectionAnd;
use Sinemah\CouchEloquent\Query\Parser\Where\CollectionOr;

class WhereFactory
{
    protected CollectionAnd $and;
    protected CollectionOr $or;

    public static function load(array $wheres): Collection
    {
        return (new self($wheres))->solve();
    }

    private function __construct(protected array $wheres)
    {
        $this->and = new CollectionAnd();
        $this->or = new CollectionOr();
    }

    public function solve(): Collection
    {
        $this->resolve();
        $collection = new Collection();

        $collection->add($this->and);
        $collection->add($this->or);

        return $collection;
    }

    protected function resolve(): void
    {
        collect($this->wheres)
            ->each(function(array $where) {
                $property = Arr::get($where, 'boolean', 'and');

                if(property_exists($this, $property)) {
                    $class = '\\Sinemah\\CouchEloquent\\Query\\Parser\\Where\\' . ucfirst(
                        Str::camel(Arr::get($where, 'type'))
                    );
                    $this->$property->add($class::load($where));
                }
            });
    }
}
